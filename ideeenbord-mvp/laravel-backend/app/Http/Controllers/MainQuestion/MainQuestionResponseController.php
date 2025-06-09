<?php

namespace App\Http\Controllers\MainQuestion;

use Illuminate\Http\Request;
use App\Models\MainQuestionResponse;
use App\Models\Brand;
use App\Models\MainQuestion;
use App\Http\Controllers\Controller;


/**
 * Class MainQuestionResponseController
 *
 * This controller handles the storage of user responses to main questions
 * associated with specific brands. It ensures each user can only answer a question once per brand.
 */
class MainQuestionResponseController extends Controller
{
    /**
     * Store a user's response to a main question for a specific brand.
     *
     * Validates the input, checks for duplicate responses,
     * and creates a new response record if valid.
     *
     * @param Request $request The HTTP request containing main question ID and answer.
     * @param Brand $brand The brand to which the response is associated.
     * @return \Illuminate\Http\JsonResponse JSON response confirming success or duplication.
     *
     * @throws \Illuminate\Validation\ValidationException If the request validation fails.
     */
    public function store(Request $request, Brand $brand)
    {
        $user = $request->user();
        $validated = $request->validate([
            'main_question_id' => 'required|exists:main_questions,id',
            'answer' => 'required|string|max:255',
        ]);

        $existing = MainQuestionResponse::where([
            'user_id' => $user->id,
            'brand_id' => $brand->id,
            'main_question_id' => $validated['main_question_id'],
        ])->first();

        if ($existing) {
            return response()->json(['message' => 'Je hebt deze vraag al beantwoord.'], 409);
        }

        $response = MainQuestionResponse::create([
            'user_id' => $user->id,
            'brand_id' => $brand->id,
            'main_question_id' => $validated['main_question_id'],
            'answer' => $validated['answer'],
        ]);

        return response()->json(['message' => 'Antwoord opgeslagen.', 'response' => $response]);
    }
}
