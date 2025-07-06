<?php

namespace App\Http\Controllers\Quiz;
use App\Http\Controllers\Controller;


use App\Models\Quiz;
use App\Models\User;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Mail\QuizWinnerMail;
use Illuminate\Support\Facades\Mail;

/**
 * Class QuizController
 *
 * Handles all quiz-related operations including creation, submission, winner selection,
 * participant listing, and retrieval by brand or user.
 */
class QuizController extends Controller
{
    /**
     * Store a new quiz for the authenticated brand owner.
     *
     * @param Request $request The HTTP request containing quiz details.
     * @return \Illuminate\Http\JsonResponse The created quiz.
     */
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
    public function index(Request $request)
    {
        // optionele query-params
        $limit = $request->integer('limit', 5);
        $order = $request->string('order', 'desc');

        // $quizzes = Quiz::query()
        //     ->when(
        //         $order === 'asc',
        //         fn($q) => $q->orderBy('created_at', 'asc'),
        //         fn($q) => $q->orderBy('created_at', 'desc')
        //     )
        //     ->take($limit)
        //     ->get(['id', 'title', 'slug', 'brand_id']);
        $quizzes = Quiz::with('brand:id,slug,title')
            ->when(
                $order === 'asc',
                fn($q) => $q->orderBy('created_at', 'asc'),
                fn($q) => $q->orderBy('created_at', 'desc')
            )
            ->take($limit)
            ->get(['id', 'title', 'slug', 'brand_id']);

        return response()->json($quizzes);
    }

    /**
     * Update an existing quiz owned by the authenticated brand owner.
     *
     * @param Request $request The HTTP request containing updated data.
     * @param Quiz $quiz The quiz to update.
     * @return \Illuminate\Http\JsonResponse The updated quiz.
     */
    public function update(Request $request, Quiz $quiz)
    {
        $this->authorizeOwner($quiz);
        $quiz->update($request->only(['title', 'description', 'prize', 'quiz_questions', 'quiz_answers']));
        return response()->json(['quiz' => $quiz]);
    }

    /**
     * Submit a user's answers to a quiz.
     *
     * @param Request $request The HTTP request containing answers.
     * @param Quiz $quiz The quiz being submitted.
     * @return \Illuminate\Http\JsonResponse Confirmation or error.
     */
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

        $submissions = $user->quiz_submissions ?? [];
        $submissions[] = [
            'quiz_id' => $quiz->id,
            'brand_id' => $quiz->brand_id,
        ];
        $user->quiz_submissions = $submissions;
        $user->save();

        return response()->json(['message' => 'Deelname opgeslagen.']);
    }
    /**
     * Close an open quiz.
     *
     * @param Quiz $quiz The quiz to close.
     * @return \Illuminate\Http\JsonResponse Confirmation message.
     */
    public function close(Quiz $quiz)
    {
        $this->authorizeOwner($quiz);
        $quiz->status = 'closed';
        $quiz->save();
        return response()->json(['message' => 'Quiz gesloten.']);
    }
    /**
     * Select a winner for a quiz and notify all participants.
     *
     * @param Request $request The HTTP request containing winner ID.
     * @param Quiz $quiz The quiz being updated.
     * @return \Illuminate\Http\JsonResponse Confirmation message.
     */

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
            if (!$user)
                continue;

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
        return response()->json(['message' => 'Winnaar geselecteerd.']);
    }

    /**
     * Display a single quiz.
     *
     * @param Quiz $quiz The quiz to display.
     * @return \Illuminate\Http\JsonResponse The quiz data.
     */

    public function show(Quiz $quiz)
    {
        return response()->json($quiz);
    }
    /**
     * Ensure the current brand owner has access to modify the given quiz.
     *
     * @param Quiz $quiz The quiz to authorize.
     * @return void
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException If unauthorized.
     */

    private function authorizeOwner(Quiz $quiz)
    {
        $user = auth('brand_owner')->user();
        if ($quiz->brand->brand_owner_id !== $user->id) {
            abort(403, 'Geen toegang tot deze quiz.');
        }
    }
    /**
     * Retrieve the active quiz for a brand.
     *
     * @param Brand $brand The brand to filter quizzes.
     * @return \Illuminate\Http\JsonResponse The active quiz or 404.
     */
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

    /**
     * Retrieve participants of the active quiz for a brand.
     *
     * @param Brand $brand The brand to filter.
     * @return \Illuminate\Http\JsonResponse A list of participants with user names.
     */
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
    /**
     * List all quizzes for a given brand, including participant names.
     *
     * @param Brand $brand The brand to filter quizzes.
     * @return \Illuminate\Http\JsonResponse All quizzes with participant names.
     */
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
    /**
     * Retrieve participants of a specific quiz.
     *
     * @param Quiz $quiz The quiz to retrieve participants from.
     * @return \Illuminate\Http\JsonResponse A list of participants with names.
     */
    public function participantsByQuiz(Quiz $quiz)
    {
        $this->authorizeOwner($quiz);

        $participants = $quiz->participants ?? [];

        $userIds = collect($participants)->pluck('user_id')->unique()->values();

        $users = User::whereIn('id', $userIds)->get()->keyBy('id');

        $enriched = collect($participants)->map(function ($participant) use ($users) {
            $user = $users->get($participant['user_id']);
            return [
                ...$participant,
                'name' => $user ? $user->name : 'Onbekende gebruiker',
            ];
        });

        return response()->json($enriched);
    }

    /**
     * Retrieve quizzes a user has participated in, grouped by current and past.
     *
     * @param string $username The username of the user.
     * @return \Illuminate\Http\JsonResponse Grouped quizzes.
     */
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
