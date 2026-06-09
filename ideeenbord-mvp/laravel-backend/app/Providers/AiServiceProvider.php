<?php

namespace App\Providers;

use App\Services\Ai\Contracts\AiClient;
use App\Services\Ai\Drivers\ClaudeClient;
use App\Services\Ai\Drivers\OpenAiClient;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

class AiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AiClient::class, function ($app) {
            $config = $app['config']['ai'];

            return match ($config['provider']) {
                'claude' => new ClaudeClient(
                    apiKey: $config['claude']['api_key'],
                    reportModel: $config['claude']['report_model'],
                    seedModel: $config['claude']['seed_model'],
                    maxTokens: $config['claude']['max_tokens'],
                ),
                'openai' => new OpenAiClient(
                    apiKey: $config['openai']['api_key'],
                    baseUrl: $config['openai']['base_url'],
                    reportModel: $config['openai']['report_model'],
                    seedModel: $config['openai']['seed_model'],
                    maxTokens: $config['openai']['max_tokens'],
                ),
                default => throw new InvalidArgumentException(
                    "Unsupported AI provider [{$config['provider']}]. Use 'claude' or 'openai'."
                ),
            };
        });
    }
}
