<?php

namespace App\Console\Commands;

use App\Services\Ai\ContentBotService;
use Illuminate\Console\Command;

class AiDistributeInteractionsCommand extends Command
{
    protected $signature = 'ai:distribute-interactions {--count= : Number of ideas to interact with}';

    protected $description = 'Interactie-AI: hand out likes/dislikes to stimulate engagement.';

    public function handle(ContentBotService $bots): int
    {
        $count = $this->option('count') !== null ? (int) $this->option('count') : null;
        $result = $bots->distributeInteractions($count);

        $this->info("Gave {$result['liked']} likes and {$result['disliked']} dislikes.");

        return self::SUCCESS;
    }
}
