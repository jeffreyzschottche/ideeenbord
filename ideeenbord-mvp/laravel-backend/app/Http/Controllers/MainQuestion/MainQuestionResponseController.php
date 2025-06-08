<?php

namespace App\Http\Controllers\MainQuestion;

use Illuminate\Http\Request;
use App\Models\MainQuestionResponse;
use App\Models\Brand;
use App\Models\MainQuestion;
use App\Http\Controllers\Controller;


class MainQuestionResponseController extends Controller
{
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
