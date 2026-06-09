<?php

namespace App\Console\Commands;

use App\Services\Ai\ContentBotService;
use Illuminate\Console\Command;

class AiEnsureBotCommand extends Command
{
    protected $signature = 'ai:ensure-bot';

    protected $description = 'Create (or fetch) the dedicated content-bot user account.';

    public function handle(ContentBotService $bots): int
    {
        $bot = $bots->ensureBot();
        $this->info("Bot account ready: @{$bot->username} (id {$bot->id}).");

        return self::SUCCESS;
    }
}
