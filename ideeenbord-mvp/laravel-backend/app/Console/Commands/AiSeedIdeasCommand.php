<?php

namespace App\Console\Commands;

use App\Services\Ai\Contracts\AiClient;
use App\Services\Ai\ContentBotService;
use Illuminate\Console\Command;

class AiSeedIdeasCommand extends Command
{
    protected $signature = 'ai:seed-ideas {--brands= : Number of brands to seed} {--per= : Ideas per brand}';

    protected $description = 'Seed-AI: place realistic ideas at a number of brands.';

    public function handle(ContentBotService $bots, AiClient $ai): int
    {
        if (! $ai->isConfigured()) {
            $this->error('AI provider is not configured (missing API key).');

            return self::FAILURE;
        }

        $result = $bots->seedIdeas(
            $this->option('brands') !== null ? (int) $this->option('brands') : null,
            $this->option('per') !== null ? (int) $this->option('per') : null,
        );

        $this->info("Seeded {$result['ideas']} ideas across {$result['brands']} brands.");
        foreach ($result['details'] as $d) {
            $this->line("  - {$d['brand']}: {$d['created']}");
        }

        return self::SUCCESS;
    }
}
