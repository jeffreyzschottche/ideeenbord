<?php

namespace App\Http\Controllers\Brand;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\BrandReport;
use App\Services\Ai\Contracts\AiClient;
use App\Services\Reports\BrandReportAnalytics;
use App\Services\Reports\BrandReportService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandReportController extends Controller
{
    public function __construct(private readonly BrandReportService $service)
    {
    }

    /** List previously generated reports for a brand (newest first, without heavy payloads). */
    public function index(Brand $brand): JsonResponse
    {
        $this->authorizeBrand($brand);

        $reports = $brand->reports()
            ->latest()
            ->get(['id', 'title', 'status', 'provider', 'model', 'period_type', 'period_start', 'period_end', 'generated_at', 'created_at']);

        return response()->json([
            'reports' => $reports,
            'quota' => $this->quota($brand),
        ]);
    }

    /**
     * Maandelijkse rapportlimiet voor een merk. Telt geslaagde/lopende rapporten
     * in de huidige kalendermaand — reset automatisch, stapelt niet op.
     *
     * @return array{limit:int, used:int, remaining:int, resets_at:string}
     */
    private function quota(Brand $brand): array
    {
        $limit = (int) config('ai.report_monthly_limit', 10);
        $used = $brand->reports()
            ->where('status', '!=', 'failed')
            ->where('created_at', '>=', Carbon::now()->startOfMonth())
            ->count();

        return [
            'limit' => $limit,
            'used' => $used,
            'remaining' => max(0, $limit - $used),
            'resets_at' => Carbon::now()->startOfMonth()->addMonth()->toDateString(),
        ];
    }

    /** Live statistics for a brand — the same analytics the report uses, no AI, always current. */
    public function stats(Brand $brand, BrandReportAnalytics $analytics): JsonResponse
    {
        $this->authorizeBrand($brand);

        return response()->json(['stats' => $analytics->build($brand)]);
    }

    /** Months/date-bounds that actually contain data, so the UI offers real choices. */
    public function range(Brand $brand, BrandReportAnalytics $analytics): JsonResponse
    {
        $this->authorizeBrand($brand);

        return response()->json($analytics->availableMonths($brand));
    }

    /** Generate a new AI report for a brand. */
    public function store(Brand $brand, Request $request, AiClient $ai): JsonResponse
    {
        $this->authorizeBrand($brand);

        if (! $ai->isConfigured()) {
            return response()->json([
                'message' => 'De AI-provider is nog niet geconfigureerd. Voeg een API-key toe in de serverinstellingen.',
            ], 422);
        }

        // Maandlimiet: max X rapporten per kalendermaand (reset elke maand, stapelt niet op).
        $quota = $this->quota($brand);
        if ($quota['remaining'] <= 0) {
            $resets = Carbon::parse($quota['resets_at'])->translatedFormat('j F');

            return response()->json([
                'message' => "Je hebt je maandlimiet van {$quota['limit']} rapporten bereikt. Je kunt op {$resets} weer nieuwe rapporten genereren.",
                'quota' => $quota,
            ], 429);
        }

        $data = $request->validate([
            'period_type' => 'nullable|in:all,monthly,custom',
            'start' => 'nullable|date',
            'end' => 'nullable|date|after_or_equal:start',
        ]);

        $periodType = $data['period_type'] ?? 'all';
        [$from, $to] = $this->resolvePeriod($periodType, $data['start'] ?? null, $data['end'] ?? null);

        if ($periodType !== 'all' && (! $from || ! $to)) {
            return response()->json([
                'message' => 'Kies een begin- en einddatum voor dit rapport.',
            ], 422);
        }

        $report = $this->service->generate($brand, $from, $to, $periodType);

        if ($report->status === 'failed') {
            return response()->json([
                'message' => 'Het rapport kon niet worden gegenereerd.',
                'error' => $report->error,
            ], 502);
        }

        return response()->json(['report' => $report], 201);
    }

    /** Fetch a single full report. */
    public function show(BrandReport $report): JsonResponse
    {
        $this->authorizeBrand($report->brand);

        return response()->json(['report' => $report]);
    }

    /**
     * Resolve the [from, to] Carbon range for a period type.
     * monthly snaps to whole months; custom uses the raw dates.
     *
     * @return array{0: ?Carbon, 1: ?Carbon}
     */
    private function resolvePeriod(string $periodType, ?string $start, ?string $end): array
    {
        if ($periodType === 'all' || ! $start || ! $end) {
            return [null, null];
        }

        $from = Carbon::parse($start);
        $to = Carbon::parse($end);

        if ($periodType === 'monthly') {
            $from = $from->startOfMonth();
            $to = $to->endOfMonth();
        } else {
            $from = $from->startOfDay();
            $to = $to->endOfDay();
        }

        return [$from, $to];
    }

    private function authorizeBrand(Brand $brand): void
    {
        $owner = auth('brand_owner')->user();

        abort_if(! $owner || $brand->brand_owner_id !== $owner->id, 403, 'Geen toegang tot dit merk.');
    }
}
