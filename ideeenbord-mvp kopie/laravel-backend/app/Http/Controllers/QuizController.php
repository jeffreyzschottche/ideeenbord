<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\User;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Mail\QuizWinnerMail;
use Illuminate\Support\Facades\Mail;

class QuizController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'prize' => 'nullable|string',
            'quiz_questions' => 'required|array',
            'quiz_answers' => 'required|array',
        ]);

        $user = auth('brand_owner')->user();
        $brand = $user->brand;

        $quiz = Quiz::create([
            'brand_id' => $brand->id,
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']),
            'description' => $validated['description'] ?? null,
            'prize' => $validated['prize'] ?? null,
            'quiz_questions' => $validated['quiz_questions'],
            'quiz_answers' => $validated['quiz_answers'],
        ]);

        return response()->json(['quiz' => $quiz], 201);
    }

    public function update(Request $request, Quiz $quiz)
    {
        $this->authorizeOwner($quiz);
        $quiz->update($request->only([  'title', 'description', 'prize', 'quiz_questions', 'quiz_answers']));
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

        // Voeg quiz_id + brand_id toe aan user.quiz_submissions
        $submissions = $user->quiz_submissions ?? [];
        $submissions[] = [
            'quiz_id' => $quiz->id,
            'brand_id' => $quiz->brand_id,
        ];
        $user->quiz_submissions = $submissions;
        $user->save();

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
        $request->validate(['winner_id' => 'required|integer']);
        $quiz->winner_id = $request->winner_id;
        $quiz->status = 'closed';
        $quiz->save();

        $winner = \App\Models\User::find($quiz->winner_id);
        if ($winner && $winner->email) {
            Mail::to($winner->email)->send(new QuizWinnerMail($quiz));
        }
        
        // Notificaties sturen
        $participants = $quiz->participants ?? [];
        
        foreach ($participants as $p) {
            $user = \App\Models\User::find($p['user_id']);
            if (!$user) continue;
        
            $message = $p['user_id'] === $quiz->winner_id
                ? "🎉 Je hebt de quiz '{$quiz->title}' gewonnen bij {$quiz->brand->title}!"
                : "❌ Je hebt helaas niet gewonnen bij de quiz '{$quiz->title}' van {$quiz->brand->title}.";
        
            $notifications = $user->notifications ?? [];
            $notifications[] = [
                'type' => 'quiz',
                'quiz_id' => $quiz->id,
                'message' => $message,
                'timestamp' => now(),
            ];
            $user->notifications = $notifications;
            $user->save();
        }
        
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

// public function getParticipants(Brand $brand)
// {
//     $quiz = Quiz::where('brand_id', $brand->id)->where('status', 'open')->latest()->first();
//     if (!$quiz) {
//         return response()->json(['message' => 'Geen actieve quiz'], 404);
//     }

//     return response()->json($quiz->participants ?? []);
// }

public function getParticipants(Brand $brand)
{
    $quiz = Quiz::where('brand_id', $brand->id)
        ->where('status', 'open')
        ->latest()
        ->first();

    if (!$quiz || !$quiz->participants || count($quiz->participants) === 0) {
        return response()->json([]);
    }

    $participants = collect($quiz->participants)->map(function ($p) {
        $user = User::find($p['user_id']);
        return [
            'user_id' => $p['user_id'],
            'name' => $user?->name ?? 'Onbekende gebruiker',
            'answers' => $p['answers'] ?? [],
        ];
    });

    return response()->json($participants);
}
public function listForBrand(Brand $brand)
{
    $quizzes = Quiz::where('brand_id', $brand->id)->latest()->get();

    // Voeg optioneel user-namen toe aan elke quiz
    foreach ($quizzes as $quiz) {
        if (is_array($quiz->participants)) {
            $userIds = collect($quiz->participants)->pluck('user_id')->unique();
            $users = \App\Models\User::whereIn('id', $userIds)->get()->keyBy('id');

            $quiz->participants = collect($quiz->participants)->map(function ($p) use ($users) {
                $p['name'] = $users[$p['user_id']]->name ?? null;
                return $p;
            })->toArray();
        }
    }

    return response()->json($quizzes);
}

// public function listForBrand(Brand $brand)
// {
//     // $this->authorize('view', $brand); // eventueel extra beveiliging
//     $quizzes = Quiz::with('participants')->where('brand_id', $brand->id)->latest()->get();
//     // $quizzes = Quiz::where('brand_id', $brand->id)->latest()->get();
//     return response()->json($quizzes);
// }
// public function participantsByQuiz(Quiz $quiz)
// {
//     // Auth check?
//     $this->authorizeOwner($quiz);

//     return response()->json($quiz->participants ?? []);
// }
public function participantsByQuiz(Quiz $quiz)
{
    $this->authorizeOwner($quiz);

    $participants = $quiz->participants ?? [];

    $userIds = collect($participants)->pluck('user_id')->unique()->values();

    // Haal gebruikers op
    $users = User::whereIn('id', $userIds)->get()->keyBy('id');

    // Voeg naam toe aan elke participant
    $enriched = collect($participants)->map(function ($participant) use ($users) {
        $user = $users->get($participant['user_id']);
        return [
            ...$participant,
            'name' => $user ? $user->name : 'Onbekende gebruiker',
        ];
    });

    return response()->json($enriched);
}
public function quizzesForUser($username)
{
    $user = User::where('username', $username)->firstOrFail();
    $submissions = $user->quiz_submissions ?? [];

    if (empty($submissions)) {
        return response()->json(['current' => [], 'past' => []]);
    }

    $quizIds = collect($submissions)->pluck('quiz_id')->toArray();

    $quizzes = Quiz::whereIn('id', $quizIds)->with('brand')->get();

    $grouped = [
        'current' => $quizzes->where('status', 'open')->values(),
        'past' => $quizzes->where('status', '!=', 'open')->values(),
    ];

    return response()->json($grouped);
}





}
