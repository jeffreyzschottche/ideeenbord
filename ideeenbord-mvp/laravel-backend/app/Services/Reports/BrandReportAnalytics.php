<?php

namespace App\Services\Reports;

use App\Models\Brand;
use App\Models\MainQuestion;
use App\Models\MainQuestionResponse;
use App\Models\Quiz;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Computes the quantitative analytics for a brand report. Pure data — no AI.
 *
 * Optionally scoped to a [from, to] period. When a period is given the metrics
 * for the *previous* equal-length window are computed too, so the report can
 * show period-over-period deltas.
 */
class BrandReportAnalytics
{
    public function build(Brand $brand, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $current = $this->window($brand, $from, $to);

        $comparison = null;
        if ($from && $to) {
            $length = $from->diffInDays($to) + 1;
            $prevTo = (clone $from)->subDay()->endOfDay();
            $prevFrom = (clone $prevTo)->subDays($length - 1)->startOfDay();
            $comparison = $this->comparison($brand, $prevFrom, $prevTo, $current['totals']);
        }

        return array_merge($current, [
            'brand' => [
                'title' => $brand->title,
                'category' => $brand->category,
                'subscription' => $brand->subscription,
            ],
            'period' => [
                'type' => $from || $to ? 'range' : 'all',
                'start' => $from?->toDateString(),
                'end' => $to?->toDateString(),
                'label' => $this->periodLabel($from, $to),
            ],
            'generated_at' => Carbon::now()->toIso8601String(),
            'comparison' => $comparison,
        ]);
    }

    /**
     * All metrics for a single [from, to] window.
     */
    private function window(Brand $brand, ?Carbon $from, ?Carbon $to): array
    {
        $ideas = $this->scope($brand->ideas(), $from, $to)->get();
        $responses = $this->scope(MainQuestionResponse::where('brand_id', $brand->id), $from, $to)->get();
        $quizzes = $this->scope(Quiz::where('brand_id', $brand->id), $from, $to)->get();
        $participants = $this->participants($ideas, $responses);

        return [
            'totals' => $this->totals($brand, $ideas, $responses, $quizzes, $participants),
            'status_distribution' => $this->statusDistribution($ideas),
            'top_ideas_by_likes' => $this->topIdeas($ideas, 'likes'),
            'top_ideas_by_dislikes' => $this->topIdeas($ideas, 'dislikes'),
            'idea_samples' => $this->ideaSamples($ideas),
            'ideas_over_time' => $this->ideasOverTime($ideas),
            'categories' => $this->categories($ideas),
            'main_questions' => $this->mainQuestions($responses),
            'quizzes' => $this->quizzes($quizzes),
            'demographics' => $this->demographics($participants),
        ];
    }

    /**
     * A trimmed metric set for the previous window plus deltas vs the current totals.
     */
    private function comparison(Brand $brand, Carbon $from, Carbon $to, array $currentTotals): array
    {
        $ideas = $this->scope($brand->ideas(), $from, $to)->get();
        $responses = $this->scope(MainQuestionResponse::where('brand_id', $brand->id), $from, $to)->get();
        $quizzes = $this->scope(Quiz::where('brand_id', $brand->id), $from, $to)->get();
        $participants = $this->participants($ideas, $responses);
        $prev = $this->totals($brand, $ideas, $responses, $quizzes, $participants);

        $delta = function (string $key) use ($currentTotals, $prev) {
            $now = (float) ($currentTotals[$key] ?? 0);
            $was = (float) ($prev[$key] ?? 0);
            $pct = $was != 0 ? round((($now - $was) / abs($was)) * 100, 1) : null;

            return ['now' => $now, 'previous' => $was, 'change' => $now - $was, 'change_pct' => $pct];
        };

        return [
            'previous_period' => [
                'start' => $from->toDateString(),
                'end' => $to->toDateString(),
            ],
            'previous_totals' => $prev,
            'deltas' => [
                'ideas' => $delta('ideas'),
                'participants' => $delta('participants'),
                'net_sentiment' => $delta('net_sentiment'),
                'main_question_responses' => $delta('main_question_responses'),
            ],
        ];
    }

    /**
     * Apply a created_at window to a query builder/relation when a range is set.
     */
    private function scope($query, ?Carbon $from, ?Carbon $to)
    {
        if ($from) {
            $query->where('created_at', '>=', $from);
        }
        if ($to) {
            $query->where('created_at', '<=', $to);
        }

        return $query;
    }

    private function totals(Brand $brand, Collection $ideas, Collection $responses, Collection $quizzes, Collection $participants): array
    {
        $likes = (int) $ideas->sum('likes');
        $dislikes = (int) $ideas->sum('dislikes');
        $quizParticipants = $quizzes->sum(fn ($q) => is_array($q->participants) ? count($q->participants) : 0);
        $engagementRate = $participants->count() > 0
            ? round(($likes + $dislikes + $responses->count()) / $participants->count(), 2)
            : 0;

        return [
            'ideas' => $ideas->count(),
            'idea_likes' => $likes,
            'idea_dislikes' => $dislikes,
            'net_sentiment' => $likes - $dislikes,
            'sentiment_ratio' => ($likes + $dislikes) > 0 ? round($likes / ($likes + $dislikes) * 100, 1) : null,
            'participants' => $participants->count(),
            'main_question_responses' => $responses->count(),
            'engagement_per_participant' => $engagementRate,
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

    private function topIdeas(Collection $ideas, string $by, int $limit = 8): array
    {
        return $ideas->sortByDesc($by)->take($limit)->map(fn ($idea) => [
            'title' => $idea->title,
            'likes' => (int) $idea->likes,
            'dislikes' => (int) $idea->dislikes,
            'status' => $idea->status,
        ])->values()->all();
    }

    /**
     * Compact, period-scoped idea samples (title + short description) for the AI,
     * ordered by engagement so the most relevant ideas are included first.
     */
    private function ideaSamples(Collection $ideas, int $limit = 40): array
    {
        return $ideas
            ->sortByDesc(fn ($i) => (int) $i->likes + (int) $i->dislikes)
            ->take($limit)
            ->map(fn ($i) => [
                'title' => $i->title,
                'description' => mb_substr((string) $i->description, 0, 300),
                'likes' => (int) $i->likes,
                'dislikes' => (int) $i->dislikes,
                'status' => $i->status,
            ])
            ->values()
            ->all();
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
            // Begrens het aantal unieke antwoorden zodat de AI-payload begrensd
            // blijft als het aantal reacties groeit (context window).
            $answers = $group->groupBy('answer')
                ->map(fn ($g, $answer) => ['answer' => (string) $answer, 'count' => $g->count()])
                ->sortByDesc('count')
                ->take(25)
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
    private function participants(Collection $ideas, Collection $responses): Collection
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

    /**
     * The months/date-bounds that actually contain activity for this brand, so
     * the UI can offer real choices instead of empty inputs. Combines idea and
     * main-question-response timestamps.
     *
     * @return array{first: ?string, last: ?string, months: array<int, array{value:string,label:string,ideas:int}>}
     */
    public function availableMonths(Brand $brand): array
    {
        $ideaDates = $brand->ideas()->pluck('created_at')->filter();
        $responseDates = MainQuestionResponse::where('brand_id', $brand->id)->pluck('created_at')->filter();
        $all = $ideaDates->merge($responseDates);

        if ($all->isEmpty()) {
            return ['first' => null, 'last' => null, 'months' => []];
        }

        $parsed = $all->map(fn ($d) => Carbon::parse($d));
        $ideaByMonth = $ideaDates
            ->map(fn ($d) => Carbon::parse($d)->format('Y-m'))
            ->countBy();

        $months = $parsed
            ->map(fn ($d) => $d->format('Y-m'))
            ->unique()
            ->sortDesc()
            ->map(fn ($ym) => [
                'value' => $ym,
                'label' => Carbon::createFromFormat('Y-m', $ym)->translatedFormat('F Y'),
                'ideas' => (int) ($ideaByMonth[$ym] ?? 0),
            ])
            ->values()
            ->all();

        return [
            'first' => $parsed->min()->toDateString(),
            'last' => $parsed->max()->toDateString(),
            'months' => $months,
        ];
    }

    private function periodLabel(?Carbon $from, ?Carbon $to): string
    {
        if (! $from && ! $to) {
            return 'Volledige historie';
        }

        $fmt = fn (?Carbon $d) => $d ? $d->translatedFormat('j M Y') : '…';

        return $fmt($from).' – '.$fmt($to);
    }
}
