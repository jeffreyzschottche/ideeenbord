<?php

namespace App\Services\Reports;

use App\Models\Brand;
use App\Models\BrandReport;
use App\Services\Ai\Contracts\AiClient;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Builds a brand report chapter-by-chapter.
 *
 * The chapter structure is a fixed template (see sections()). Each chapter is a
 * separate, focused AI call that only receives the data slice it needs, which
 * keeps every call cheap and the output deep. Descriptive chapters run on the
 * cheaper "seed" model; the strategic synthesis chapters run on the strong
 * "report" model. A failing chapter degrades gracefully (empty) instead of
 * killing the whole report.
 */
class BrandReportService
{
    public function __construct(
        private readonly AiClient $ai,
        private readonly BrandReportAnalytics $analytics,
    ) {
    }

    public function generate(Brand $brand, ?Carbon $from = null, ?Carbon $to = null, string $periodType = 'all'): BrandReport
    {
        $report = null;

        try {
            $report = BrandReport::create([
                'brand_id' => $brand->id,
                'title' => $this->title($periodType, $from, $to),
                'status' => 'processing',
                'provider' => config('ai.provider'),
                'period_type' => $periodType,
                'period_start' => $from?->toDateString(),
                'period_end' => $to?->toDateString(),
            ]);

            $metrics = $this->analytics->build($brand, $from, $to);
            $ai = $this->compose($brand, $metrics);

            $report->update([
                'metrics' => $metrics,
                'ai' => $ai,
                'model' => config('ai.'.config('ai.provider').'.report_model'),
                'status' => 'completed',
                'generated_at' => Carbon::now(),
            ]);

            return $report->refresh();
        } catch (Throwable $e) {
            Log::error('Brand report generation failed', ['brand' => $brand->id, 'error' => $e->getMessage()]);

            if ($report) {
                $report->update(['status' => 'failed', 'error' => $e->getMessage()]);

                return $report->refresh();
            }

            return new BrandReport([
                'brand_id' => $brand->id,
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Run every chapter in the template and assemble the full AI payload.
     */
    private function compose(Brand $brand, array $metrics): array
    {
        $brief = $this->brief($brand, $metrics);
        $ai = [];

        foreach ($this->sections() as $section) {
            $ai = array_merge($ai, $this->runSection($section, $brand, $metrics, $brief, $ai));
        }

        // The whole report is worthless if not a single chapter came back.
        if (collect($ai)->every(fn ($v) => $v === '' || $v === [] || $v === null)) {
            throw new \RuntimeException('De AI leverde geen bruikbare inhoud op.');
        }

        return $ai;
    }

    private function runSection(array $section, Brand $brand, array $metrics, string $brief, array $soFar): array
    {
        $data = ($section['data'])($metrics);

        $prompt = "Merk: \"{$brand->title}\" (categorie: {$brand->category}).\n\n"
            ."CONTEXT (kerncijfers):\n{$brief}\n\n";

        if (! empty($section['use_digest'])) {
            $prompt .= "WAT EERDER IS VASTGESTELD (samenvatting van voorgaande hoofdstukken):\n"
                .$this->digest($soFar)."\n\n";
        }

        $prompt .= "RELEVANTE DATA (JSON):\n"
            .json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n\n"
            ."OPDRACHT:\n{$section['instruction']}";

        $options = ['purpose' => $section['model']]; // 'seed' (goedkoop) of 'report' (sterk)

        try {
            $result = $this->ai->json($this->system(), $prompt, $section['schema'], $options);
        } catch (Throwable $e) {
            // Eén retry; daarna lege fallback zodat de rest van het rapport doorgaat.
            Log::warning('Report section failed, retrying', ['keys' => array_keys($section['schema']['properties']), 'error' => $e->getMessage()]);
            try {
                $result = $this->ai->json($this->system(), $prompt, $section['schema'], $options);
            } catch (Throwable $e2) {
                Log::error('Report section failed permanently', ['error' => $e2->getMessage()]);
                $result = $this->defaultsFor($section['schema']);
            }
        }

        return $result;
    }

    /**
     * Shared analyst persona + rules for every chapter call.
     */
    private function system(): string
    {
        return <<<'SYS'
        Je bent senior data-analist en merkstrateeg voor Ideeënbord — het platform waar
        consumenten ideeën, wensen en verbeterpunten met merken delen, en waar merken
        daar écht naar luisteren. Je schrijft in vloeiend, professioneel Nederlands
        (je-vorm, geen jargon, geen emoji).

        Je werkt aan één hoofdstuk van een groter adviesrapport. Lever alleen dat
        hoofdstuk, volgens het JSON-schema.

        Strikte regels:
        - Baseer je UITSLUITEND op de meegeleverde data. Verzin nooit cijfers of feiten.
        - Wees concreet, diepgaand en bruikbaar: de merkeigenaar moet morgen kunnen handelen.
        - Verwijs naar concrete ideeën, cijfers en demografische segmenten uit de data.
        - Schrijf volzinnen en meerdere alinea's per tekstveld waar dat past — geen telegramstijl.
        - Bij dunne of ontbrekende data: benoem dat eerlijk en geef aan hoe meer signaal
          te verzamelen valt. Speculeer niet.
        SYS;
    }

    /**
     * Compact textual brief reused in every call (cheap, ~200 tokens).
     */
    private function brief(Brand $brand, array $metrics): string
    {
        $t = $metrics['totals'];
        $p = $metrics['period'];
        $lines = [
            "Periode: {$p['label']}.",
            "Ideeën: {$t['ideas']}, deelnemers: {$t['participants']}, hoofdvraag-antwoorden: {$t['main_question_responses']}.",
            "Likes: {$t['idea_likes']}, dislikes: {$t['idea_dislikes']}, netto sentiment: {$t['net_sentiment']}"
                .($t['sentiment_ratio'] !== null ? " ({$t['sentiment_ratio']}% positief)." : '.'),
            'Gem. beoordeling: '.($t['avg_rating'] ?? 'onbekend').", quizzes: {$t['quizzes']}.",
        ];

        if (! empty($metrics['health'])) {
            $h = $metrics['health'];
            $lines[] = "Merkgezondheid: {$h['score']}/100 ({$h['label']}).";
        }

        if (! empty($metrics['comparison']['deltas'])) {
            $d = $metrics['comparison']['deltas'];
            $fmt = fn ($x) => $x['change_pct'] === null ? 'n.v.t.' : ($x['change'] >= 0 ? '+' : '').$x['change_pct'].'%';
            $lines[] = 'T.o.v. vorige periode — ideeën: '.$fmt($d['ideas'])
                .', deelnemers: '.$fmt($d['participants'])
                .', netto sentiment: '.$fmt($d['net_sentiment']).'.';
        }

        return implode("\n", $lines);
    }

    /**
     * Short digest of chapters generated so far, for the synthesis calls.
     */
    private function digest(array $soFar): string
    {
        $parts = [];

        foreach ($soFar as $key => $value) {
            $label = ucfirst(str_replace('_', ' ', $key));

            if (is_string($value) && $value !== '') {
                $parts[] = "- {$label}: ".mb_substr($value, 0, 260);
            } elseif (is_array($value) && $value !== []) {
                $items = array_map(function ($item) {
                    if (is_string($item)) {
                        return $item;
                    }

                    return $item['theme'] ?? $item['title'] ?? $item['segment'] ?? $item['phase'] ?? null;
                }, $value);
                $items = array_filter($items);
                if ($items) {
                    $parts[] = "- {$label}: ".implode('; ', array_slice($items, 0, 6));
                }
            }
        }

        return implode("\n", $parts);
    }

    private function defaultsFor(array $schema): array
    {
        $out = [];
        foreach (($schema['properties'] ?? []) as $key => $def) {
            $out[$key] = ($def['type'] ?? 'string') === 'array' ? [] : '';
        }

        return $out;
    }

    private function title(string $periodType, ?Carbon $from, ?Carbon $to): string
    {
        return match ($periodType) {
            'monthly' => $from && $to && $from->isSameMonth($to)
                ? 'Maandrapport '.$from->translatedFormat('F Y')
                : 'Maandrapport '.($from?->translatedFormat('M Y') ?? '').' – '.($to?->translatedFormat('M Y') ?? ''),
            'custom' => 'Rapport '.($from?->translatedFormat('j M Y') ?? '').' – '.($to?->translatedFormat('j M Y') ?? ''),
            default => 'Rapport '.Carbon::now()->translatedFormat('j F Y'),
        };
    }

    /* ------------------------------------------------------------------ */
    /*  Chapter template — order matters; synthesis chapters come last.    */
    /* ------------------------------------------------------------------ */

    private function sections(): array
    {
        $stringArray = ['type' => 'array', 'items' => ['type' => 'string']];
        $obj = fn (array $props) => [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => $props,
            'required' => array_keys($props),
        ];

        return [
            // Thema's in de ideeën (beschrijvend → goedkoop model)
            [
                'model' => 'seed',
                'data' => fn ($m) => [
                    'idee_voorbeelden' => $m['idea_samples'],
                    'categorieen' => $m['categories'],
                ],
                'instruction' => 'Identificeer 4 tot 7 terugkerende thema\'s in de ideeën. '
                    .'Geef per thema een titel, een beschrijving van 2-3 zinnen, een sentiment '
                    .'(positief/negatief/gemengd) en 1-3 concrete voorbeeld-idee-titels uit de data.',
                'schema' => $obj([
                    'idea_themes' => [
                        'type' => 'array',
                        'items' => $obj([
                            'theme' => ['type' => 'string'],
                            'description' => ['type' => 'string'],
                            'sentiment' => ['type' => 'string', 'enum' => ['positief', 'negatief', 'gemengd']],
                            'example_ideas' => $stringArray,
                        ]),
                    ],
                ]),
            ],

            // Sentiment + analyse top-ideeën (beschrijvend → goedkoop)
            [
                'model' => 'seed',
                'data' => fn ($m) => [
                    'kerncijfers' => $m['totals'],
                    'statusverdeling' => $m['status_distribution'],
                    'top_likes' => $m['top_ideas_by_likes'],
                    'top_dislikes' => $m['top_ideas_by_dislikes'],
                ],
                'instruction' => 'Schrijf twee analyses. (1) sentiment_analysis: hoe staat het publiek '
                    .'tegenover het merk op basis van likes/dislikes, netto sentiment en statusverdeling? '
                    .'(2) top_ideas_analysis: waarom scoren de best ontvangen ideeën goed en de slechtst '
                    .'ontvangen slecht? Wat zegt dat over de wensen van het publiek?',
                'schema' => $obj([
                    'sentiment_analysis' => ['type' => 'string'],
                    'top_ideas_analysis' => ['type' => 'string'],
                ]),
            ],

            // Deelname + trend (beschrijvend → goedkoop)
            [
                'model' => 'seed',
                'data' => fn ($m) => [
                    'kerncijfers' => $m['totals'],
                    'ideeen_over_tijd' => $m['ideas_over_time'],
                    'vergelijking_vorige_periode' => $m['comparison'],
                    'periode' => $m['period'],
                ],
                'instruction' => 'Schrijf twee analyses. (1) participation_analysis: hoe actief is het '
                    .'publiek (deelnemers, ideeën, engagement per deelnemer)? (2) trend_analysis: hoe '
                    .'ontwikkelt de betrokkenheid zich over tijd en — indien aanwezig — ten opzichte van '
                    .'de vorige periode? Duid groei of daling expliciet. Als er geen vergelijking is, zeg dat.',
                'schema' => $obj([
                    'participation_analysis' => ['type' => 'string'],
                    'trend_analysis' => ['type' => 'string'],
                ]),
            ],

            // Doelgroep + segmenten + koopgedrag (beschrijvend → goedkoop)
            [
                'model' => 'seed',
                'data' => fn ($m) => [
                    'demografie' => $m['demographics'],
                    'datavoorkeuren' => $m['data_profile'] ?? null,
                    'kerncijfers' => $m['totals'],
                ],
                'instruction' => 'Schrijf audience_insight: een diepgaand profiel van de doelgroep op basis '
                    .'van leeftijd, geslacht, opleiding, sector en woonplaats. Definieer audience_segments: '
                    .'2-4 herkenbare doelgroepsegmenten met een korte beschrijving per segment. Schrijf ten slotte '
                    .'buying_behavior_insight: wat zeggen de optionele datavoorkeuren (politieke voorkeur, wie de '
                    .'boodschappen doet en over aankopen beslist, bestelfrequentie, en uitgaven aan technologie en '
                    .'boodschappen, huishoudgrootte) over het koopgedrag en de besteedbaarheid van deze doelgroep? '
                    .'Wees voorzichtig met kleine aantallen: noem expliciet als slechts weinig deelnemers deze data '
                    .'deelden (zie datavoorkeuren.shared_count) en trek dan geen harde conclusies.',
                'schema' => $obj([
                    'audience_insight' => ['type' => 'string'],
                    'audience_segments' => [
                        'type' => 'array',
                        'items' => $obj([
                            'segment' => ['type' => 'string'],
                            'description' => ['type' => 'string'],
                        ]),
                    ],
                    'buying_behavior_insight' => ['type' => 'string'],
                ]),
            ],

            // Persona's (beschrijvend → goedkoop)
            [
                'model' => 'seed',
                'data' => fn ($m) => [
                    'demografie' => $m['demographics'],
                    'datavoorkeuren' => $m['data_profile'] ?? null,
                    'idee_voorbeelden' => $m['idea_samples'],
                ],
                'instruction' => 'Stel 2 tot 3 concrete persona\'s op die representatief zijn voor dit publiek. '
                    .'Geef per persona: name (herkenbare naam + leeftijd, bv. "Bewuste Bo (28)"), description '
                    .'(korte achtergrondschets), demographics (bondige opsomming van leeftijd/opleiding/woonplaats/'
                    .'sector), motivation (wat drijft deze persona om ideeën te delen en wat verwacht die van het merk). '
                    .'Baseer je strikt op de data; bij dunne data: minder of bredere persona\'s en benoem de onzekerheid.',
                'schema' => $obj([
                    'personas' => [
                        'type' => 'array',
                        'items' => $obj([
                            'name' => ['type' => 'string'],
                            'description' => ['type' => 'string'],
                            'demographics' => ['type' => 'string'],
                            'motivation' => ['type' => 'string'],
                        ]),
                    ],
                ]),
            ],

            // Hoofdvraag-inzichten (beschrijvend → goedkoop)
            [
                'model' => 'seed',
                'data' => fn ($m) => ['hoofdvraag' => $m['main_questions'], 'quizzes' => $m['quizzes']],
                'instruction' => 'Schrijf main_question_insights: wat leren de antwoorden op de hoofdvraag '
                    .'(en eventuele quizzes) je over de wensen en voorkeuren van het publiek? Wees concreet '
                    .'over de meest gegeven antwoorden. Als er geen hoofdvraag-data is, benoem dat.',
                'schema' => $obj([
                    'main_question_insights' => ['type' => 'string'],
                ]),
            ],

            // Kansen, risico's, strategische vooruitblik (synthese → sterk model)
            [
                'model' => 'report',
                'use_digest' => true,
                'data' => fn ($m) => ['kerncijfers' => $m['totals']],
                'instruction' => 'Op basis van alle voorgaande hoofdstukken: lever opportunities (3-6 concrete '
                    .'kansen), risks (3-6 aandachtspunten/risico\'s) en strategic_outlook: een strategische '
                    .'vooruitblik van enkele alinea\'s over hoe het merk zich kan positioneren en ontwikkelen.',
                'schema' => $obj([
                    'opportunities' => $stringArray,
                    'risks' => $stringArray,
                    'strategic_outlook' => ['type' => 'string'],
                ]),
            ],

            // Aanbevelingen + actieplan (synthese → sterk model)
            [
                'model' => 'report',
                'use_digest' => true,
                'data' => fn ($m) => [],
                'instruction' => 'Op basis van alle voorgaande hoofdstukken: lever recommendations (4-6 concrete '
                    .'aanbevelingen met titel, uitleg, prioriteit hoog/middel/laag en verwachte impact), een '
                    .'action_plan (2-4 fasen met elk een titel, tijdsindicatie en een lijst concrete acties) en '
                    .'quick_wins: 3 concrete acties die het merk binnen één à twee weken kan uitvoeren voor '
                    .'directe impact.',
                'schema' => $obj([
                    'quick_wins' => $stringArray,
                    'recommendations' => [
                        'type' => 'array',
                        'items' => $obj([
                            'title' => ['type' => 'string'],
                            'detail' => ['type' => 'string'],
                            'priority' => ['type' => 'string', 'enum' => ['hoog', 'middel', 'laag']],
                            'expected_impact' => ['type' => 'string'],
                        ]),
                    ],
                    'action_plan' => [
                        'type' => 'array',
                        'items' => $obj([
                            'phase' => ['type' => 'string'],
                            'timeframe' => ['type' => 'string'],
                            'actions' => $stringArray,
                        ]),
                    ],
                ]),
            ],

            // Samenvatting + conclusie (synthese, als laatste → sterk model)
            [
                'model' => 'report',
                'use_digest' => true,
                'data' => fn ($m) => ['kerncijfers' => $m['totals'], 'periode' => $m['period']],
                'instruction' => 'Schrijf, als sluitstuk en op basis van alle voorgaande hoofdstukken: '
                    .'executive_summary (een uitgebreide managementsamenvatting van meerdere alinea\'s), '
                    .'key_findings (5-7 kernpunten als korte zinnen), conclusion (een afsluitende conclusie) '
                    .'en suggested_main_question (één scherpe vervolg-hoofdvraag om aan het publiek te stellen).',
                'schema' => $obj([
                    'executive_summary' => ['type' => 'string'],
                    'key_findings' => $stringArray,
                    'conclusion' => ['type' => 'string'],
                    'suggested_main_question' => ['type' => 'string'],
                ]),
            ],
        ];
    }
}
