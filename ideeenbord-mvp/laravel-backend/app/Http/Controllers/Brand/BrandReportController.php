<?php

namespace App\Http\Controllers\Brand;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\BrandReport;
use App\Services\Ai\Contracts\AiClient;
use App\Services\Reports\BrandReportService;
use Illuminate\Http\JsonResponse;

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
            ->get(['id', 'title', 'status', 'provider', 'model', 'generated_at', 'created_at']);

        return response()->json(['reports' => $reports]);
    }

    /** Generate a new AI report for a brand. */
    public function store(Brand $brand, AiClient $ai): JsonResponse
    {
        $this->authorizeBrand($brand);

        if (! $ai->isConfigured()) {
            return response()->json([
                'message' => 'De AI-provider is nog niet geconfigureerd. Voeg een API-key toe in de serverinstellingen.',
            ], 422);
        }

        $report = $this->service->generate($brand);

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

    private function authorizeBrand(Brand $brand): void
    {
        $owner = auth('brand_owner')->user();

        abort_if(! $owner || $brand->brand_owner_id !== $owner->id, 403, 'Geen toegang tot dit merk.');
    }
}
