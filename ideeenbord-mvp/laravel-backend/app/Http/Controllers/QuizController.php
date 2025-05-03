<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuizController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'quiz_questions' => 'required|array',
            'quiz_answers' => 'required|array',
        ]);

        $user = auth('brand_owner')->user();
        $brand = $user->brand;

        $quiz = Quiz::create([
            'brand_id' => $brand->id,
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']),
            'quiz_questions' => $validated['quiz_questions'],
            'quiz_answers' => $validated['quiz_answers'],
        ]);

        return response()->json(['quiz' => $quiz], 201);
    }

    public function update(Request $request, Quiz $quiz)
    {
        $this->authorizeOwner($quiz);
        $quiz->update($request->only(['title', 'quiz_questions', 'quiz_answers']));
        return response()->json(['quiz' => $quiz]);
    }

    public function submit(Request $request, Quiz $quiz)
    {
        $request->validate(['answers' => 'required|array']);
        $user = $request->user();

        // Check of user al meedeed
        $participants = $quiz->participants ?? [];
        if (collect($participants)->pluck('user_id')->contains($user->id)) {
            return response()->json(['message' => 'Je hebt al meegedaan.'], 409);
        }

        $participants[] = [
            'user_id' => $user->id,
            'answers' => $request->answers
        ];

        $quiz->participants = $participants;
        $quiz->save();

        return response()->json(['message' => 'Deelname opgeslagen.']);
    }

    public function close(Quiz $quiz)
    {
        $this->authorizeOwner($quiz);
        $quiz->status = 'closed';
        $quiz->save();
        return response()->json(['message' => 'Quiz gesloten.']);
    }

    public function selectWinner(Request $request, Quiz $quiz)
    {
        $this->authorizeOwner($quiz);
//         $request->validate(['winner_id' => 'required|integer']);
// $quiz->winner_id = $request->winner_id;
$request->validate(['winner_id' => 'required|integer']);
$quiz->winner_id = $request->winner_id;
        $quiz->save();
        return response()->json(['message' => 'Winnaar geselecteerd.']);
    }

    public function show(Quiz $quiz)
    {
        return response()->json($quiz);
    }

    private function authorizeOwner(Quiz $quiz)
    {
        $user = auth('brand_owner')->user();
        if ($quiz->brand->brand_owner_id !== $user->id) {
            abort(403, 'Geen toegang tot deze quiz.');
        }
    }
    public function activeForBrand(Brand $brand)
{
    $quiz = Quiz::where('brand_id', $brand->id)
        ->where('status', 'open')
        ->latest()
        ->first();

    if (!$quiz) {
        return response()->json(['message' => 'Geen actieve quiz.'], 404);
    }

    return response()->json($quiz);
}

public function getParticipants(Brand $brand)
{
    $quiz = Quiz::where('brand_id', $brand->id)->where('status', 'open')->latest()->first();
    if (!$quiz) {
        return response()->json(['message' => 'Geen actieve quiz'], 404);
    }

    return response()->json($quiz->participants ?? []);
}

}
