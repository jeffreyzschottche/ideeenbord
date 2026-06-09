<?php

namespace App\Services\Reports;

use App\Models\Brand;
use App\Models\Idea;
use App\Models\MainQuestion;
use App\Models\MainQuestionResponse;
use App\Models\Quiz;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Computes the quantitative analytics for a brand report. Pure data — no AI.
 */
class BrandReportAnalytics
{
    public function build(Brand $brand): array
    {
        $ideas = $brand->ideas()->get();
        $responses = MainQuestionResponse::where('brand_id', $brand->id)->get();
        $quizzes = Quiz::where('brand_id', $brand->id)->get();
        $participants = $this->participants($brand, $ideas, $responses);

        return [
            'brand' => [
                'title' => $brand->title,
                'category' => $brand->category,
                'subscription' => $brand->subscription,
            ],
            'generated_at' => Carbon::now()->toIso8601String(),
            'totals' => $this->totals($brand, $ideas, $responses, $quizzes, $participants),
            'status_distribution' => $this->statusDistribution($ideas),
            'top_ideas_by_likes' => $this->topIdeas($ideas, 'likes'),
            'top_ideas_by_dislikes' => $this->topIdeas($ideas, 'dislikes'),
            'ideas_over_time' => $this->ideasOverTime($ideas),
            'categories' => $this->categories($ideas),
            'main_questions' => $this->mainQuestions($responses),
            'quizzes' => $this->quizzes($quizzes),
            'demographics' => $this->demographics($participants),
        ];
    }

    private function totals(Brand $brand, Collection $ideas, Collection $responses, Collection $quizzes, Collection $participants): array
    {
        $likes = (int) $ideas->sum('likes');
        $dislikes = (int) $ideas->sum('dislikes');
        $quizParticipants = $quizzes->sum(fn ($q) => is_array($q->participants) ? count($q->participants) : 0);

        return [
            'ideas' => $ideas->count(),
            'idea_likes' => $likes,
            'idea_dislikes' => $dislikes,
            'net_sentiment' => $likes - $dislikes,
            'participants' => $participants->count(),
            'main_question_responses' => $responses->count(),
            'quizzes' => $quizzes->count(),
            'quiz_participants' => (int) $quizParticipants,
            'rating_count' => (int) ($brand->rating_count ?? 0),
            'avg_rating' => $brand->rating_count > 0
                ? round($brand->rating_sum / $brand->rating_count, 2)
                : null,
        ];
    }

    private function statusDistribution(Collection $ideas): array
    {
        $counts = ['pending' => 0, 'in_progress' => 0, 'completed' => 0, 'rejected' => 0];
        foreach ($ideas as $idea) {
            $status = $idea->status ?? 'pending';
            $counts[$status] = ($counts[$status] ?? 0) + 1;
        }

        return $counts;
    }

    private function topIdeas(Collection $ideas, string $by, int $limit = 5): array
    {
        return $ideas->sortByDesc($by)->take($limit)->map(fn ($idea) => [
            'title' => $idea->title,
            'likes' => (int) $idea->likes,
            'dislikes' => (int) $idea->dislikes,
            'status' => $idea->status,
        ])->values()->all();
    }

    private function ideasOverTime(Collection $ideas): array
    {
        return $ideas
            ->groupBy(fn ($idea) => optional($idea->created_at)->format('Y-m') ?? 'onbekend')
            ->map(fn ($group, $month) => [
                'month' => $month,
                'count' => $group->count(),
                'likes' => (int) $group->sum('likes'),
                'dislikes' => (int) $group->sum('dislikes'),
            ])
            ->sortKeys()
            ->values()
            ->all();
    }

    private function categories(Collection $ideas): array
    {
        return $ideas
            ->filter(fn ($i) => ! empty($i->category))
            ->groupBy('category')
            ->map(fn ($group, $cat) => ['category' => $cat, 'count' => $group->count()])
            ->sortByDesc('count')
            ->take(10)
            ->values()
            ->all();
    }

    private function mainQuestions(Collection $responses): array
    {
        $byQuestion = $responses->groupBy('main_question_id');
        $questions = MainQuestion::whereIn('id', $byQuestion->keys()->filter())->get()->keyBy('id');

        return $byQuestion->map(function ($group, $questionId) use ($questions) {
            $answers = $group->groupBy('answer')
                ->map(fn ($g, $answer) => ['answer' => (string) $answer, 'count' => $g->count()])
                ->sortByDesc('count')
                ->values()
                ->all();

            return [
                'question' => $questions[$questionId]->text ?? 'Onbekende vraag',
                'responses' => $group->count(),
                'answers' => $answers,
            ];
        })->values()->all();
    }

    private function quizzes(Collection $quizzes): array
    {
        return $quizzes->map(fn ($quiz) => [
            'title' => $quiz->title,
            'status' => $quiz->status,
            'prize' => $quiz->prize,
            'participants' => is_array($quiz->participants) ? count($quiz->participants) : 0,
            'has_winner' => ! empty($quiz->winner_id),
        ])->values()->all();
    }

    /**
     * Unique participant users: anyone who posted an idea or answered a main question.
     */
    private function participants(Brand $brand, Collection $ideas, Collection $responses): Collection
    {
        $userIds = $ideas->pluck('user_id')
            ->merge($responses->pluck('user_id'))
            ->filter()
            ->unique()
            ->values();

        if ($userIds->isEmpty()) {
            return collect();
        }

        return User::whereIn('id', $userIds)->get([
            'id', 'gender', 'birthdate', 'education_level', 'education',
            'job', 'sector', 'city', 'relationship_status',
        ]);
    }

    private function demographics(Collection $participants): array
    {
        return [
            'gender' => $this->distribution($participants, 'gender'),
            'age_brackets' => $this->ageBrackets($participants),
            'education' => $this->distribution($participants, 'education_level'),
            'sector' => $this->distribution($participants, 'sector', 8),
            'cities' => $this->distribution($participants, 'city', 8),
            'relationship' => $this->distribution($participants, 'relationship_status'),
        ];
    }

    private function distribution(Collection $participants, string $field, int $limit = 12): array
    {
        return $participants
            ->map(fn ($p) => $p->{$field})
            ->filter(fn ($v) => ! empty($v))
            ->groupBy(fn ($v) => $v)
            ->map(fn ($group, $label) => ['label' => (string) $label, 'count' => $group->count()])
            ->sortByDesc('count')
            ->take($limit)
            ->values()
            ->all();
    }

    private function ageBrackets(Collection $participants): array
    {
        $brackets = ['<18' => 0, '18-24' => 0, '25-34' => 0, '35-44' => 0, '45-54' => 0, '55+' => 0];

        foreach ($participants as $p) {
            if (empty($p->birthdate)) {
                continue;
            }
            $age = Carbon::parse($p->birthdate)->age;
            $key = match (true) {
                $age < 18 => '<18',
                $age < 25 => '18-24',
                $age < 35 => '25-34',
                $age < 45 => '35-44',
                $age < 55 => '45-54',
                default => '55+',
            };
            $brackets[$key]++;
        }

        return collect($brackets)
            ->map(fn ($count, $label) => ['label' => $label, 'count' => $count])
            ->values()
            ->all();
    }
}
