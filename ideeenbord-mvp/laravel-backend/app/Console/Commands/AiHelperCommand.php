<?php

namespace App\Console\Commands;

use App\Services\Ai\Contracts\AiClient;
use App\Services\Ai\ContentBotService;
use Illuminate\Console\Command;

/**
 * The instructable AI-helper. Let it loose on specific brands (or a number of
 * random ones) with a free-form instruction, e.g.:
 *
 *   php artisan ai:helper --count=5 --per=3 --instruction="focus op duurzaamheid"
 *   php artisan ai:helper --brand=12 --brand=34 --per=2
 */
class AiHelperCommand extends Command
{
    protected $signature = 'ai:helper
        {--brand=* : Specific brand IDs to target}
        {--count= : Number of random brands when no --brand given}
        {--per=2 : Ideas per brand}
        {--instruction= : Free-form instruction to steer the ideas}';

    protected $description = 'AI-helper: place ideas at brands on demand, with a custom instruction.';

    public function handle(ContentBotService $bots, AiClient $ai): int
    {
        if (! $ai->isConfigured()) {
            $this->error('AI provider is not configured (missing API key).');

            return self::FAILURE;
        }

        $brandIds = array_map('intval', (array) $this->option('brand'));
        $count = $this->option('count') !== null ? (int) $this->option('count') : null;
        $per = (int) $this->option('per');
        $instruction = (string) ($this->option('instruction') ?? '');

        $result = $bots->seedIdeas(
            brands: $count,
            perBrand: $per,
            brandIds: $brandIds,
            instruction: $instruction,
        );

        $this->info("Placed {$result['ideas']} ideas across {$result['brands']} brands.");
        foreach ($result['details'] as $d) {
            $this->line("  - {$d['brand']}: {$d['created']}");
        }

        return self::SUCCESS;
    }
}
