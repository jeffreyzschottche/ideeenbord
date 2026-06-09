<?php

namespace App\Services\Reports;

use App\Models\Brand;
use App\Models\BrandReport;
use App\Services\Ai\Contracts\AiClient;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

class BrandReportService
{
    public function __construct(
        private readonly AiClient $ai,
        private readonly BrandReportAnalytics $analytics,
    ) {
    }

    public function generate(Brand $brand): BrandReport
    {
        $report = BrandReport::create([
            'brand_id' => $brand->id,
            'title' => 'Rapport '.Carbon::now()->translatedFormat('j F Y'),
            'status' => 'processing',
            'provider' => config('ai.provider'),
        ]);

        try {
            $metrics = $this->analytics->build($brand);
            $ai = $this->aiSections($brand, $metrics);

            $report->update([
                'metrics' => $metrics,
                'ai' => $ai,
                'model' => config('ai.'.config('ai.provider').'.report_model'),
                'status' => 'completed',
                'generated_at' => Carbon::now(),
            ]);
        } catch (Throwable $e) {
            Log::error('Brand report generation failed', ['brand' => $brand->id, 'error' => $e->getMessage()]);
            $report->update(['status' => 'failed', 'error' => $e->getMessage()]);
        }

        return $report->refresh();
    }

    private function aiSections(Brand $brand, array $metrics): array
    {
        $samples = $brand->ideas()
            ->latest('id')
            ->limit(60)
            ->get(['title', 'description', 'likes', 'dislikes', 'status'])
            ->map(fn ($i) => [
                'title' => $i->title,
                'description' => mb_substr((string) $i->description, 0, 300),
                'likes' => (int) $i->likes,
                'dislikes' => (int) $i->dislikes,
                'status' => $i->status,
            ])->all();

        $system = <<<'SYS'
        Je bent senior data-analist en merkstrateeg voor Ideeënbord — het platform waar
        consumenten ideeën, wensen en verbeterpunten delen met merken, en waar merken
        daar écht naar luisteren. Je schrijft heldere, professionele en activerende
        analyses in het Nederlands (je-vorm, geen jargon, geen emoji).

        Regels:
        - Baseer je uitsluitend op de meegeleverde data. Verzin nooit cijfers.
        - Wees concreet en bruikbaar: een merkeigenaar moet morgen kunnen handelen.
        - Als data ontbreekt of dun is, benoem dat eerlijk in plaats van te speculeren.
        SYS;

        $payload = [
            'merk' => $metrics['brand'],
            'kerncijfers' => $metrics['totals'],
            'statusverdeling_ideeen' => $metrics['status_distribution'],
            'top_ideeen_likes' => $metrics['top_ideas_by_likes'],
            'top_ideeen_dislikes' => $metrics['top_ideas_by_dislikes'],
            'hoofdvraag_inzichten' => $metrics['main_questions'],
            'demografie' => $metrics['demographics'],
            'idee_voorbeelden' => $samples,
        ];

        $prompt = "Analyseer de onderstaande data van het merk \"{$brand->title}\" "
            ."(categorie: {$brand->category}) op Ideeënbord en lever een uitgebreid, "
            ."gestructureerd rapport volgens het gevraagde JSON-schema.\n\n"
            ."DATA (JSON):\n".json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return $this->ai->json($system, $prompt, $this->schema(), ['purpose' => 'report']);
    }

    private function schema(): array
    {
        $stringArray = ['type' => 'array', 'items' => ['type' => 'string']];

        return [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                'executive_summary' => ['type' => 'string'],
                'key_findings' => $stringArray,
                'idea_themes' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'properties' => [
                            'theme' => ['type' => 'string'],
                            'description' => ['type' => 'string'],
                            'sentiment' => ['type' => 'string', 'enum' => ['positief', 'negatief', 'gemengd']],
                        ],
                        'required' => ['theme', 'description', 'sentiment'],
                    ],
                ],
                'audience_insight' => ['type' => 'string'],
                'opportunities' => $stringArray,
                'risks' => $stringArray,
                'recommendations' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'properties' => [
                            'title' => ['type' => 'string'],
                            'detail' => ['type' => 'string'],
                            'priority' => ['type' => 'string', 'enum' => ['hoog', 'middel', 'laag']],
                        ],
                        'required' => ['title', 'detail', 'priority'],
                    ],
                ],
                'suggested_main_question' => ['type' => 'string'],
            ],
            'required' => [
                'executive_summary', 'key_findings', 'idea_themes', 'audience_insight',
                'opportunities', 'risks', 'recommendations', 'suggested_main_question',
            ],
        ];
    }
}
