<?php

namespace App\Services\Ai\Drivers;

use Anthropic\Client;
use App\Services\Ai\Contracts\AiClient;
use RuntimeException;

/**
 * Anthropic Claude driver, built on the official anthropic-ai/sdk.
 *
 * Note: Claude 4.x models (e.g. claude-opus-4-8) use adaptive thinking and do
 * not accept temperature/top_p — so we never forward sampling params here.
 */
class ClaudeClient implements AiClient
{
    private ?Client $client = null;

    public function __construct(
        private readonly ?string $apiKey,
        private readonly string $reportModel,
        private readonly string $seedModel,
        private readonly int $maxTokens,
    ) {
    }

    public function isConfigured(): bool
    {
        return ! empty($this->apiKey);
    }

    public function complete(string $system, string $prompt, array $options = []): string
    {
        $message = $this->client()->messages->create(
            maxTokens: $options['max_tokens'] ?? $this->maxTokens,
            messages: [['role' => 'user', 'content' => $prompt]],
            model: $this->resolveModel($options),
            system: $system,
            thinking: ($options['thinking'] ?? true) ? ['type' => 'adaptive'] : ['type' => 'disabled'],
        );

        return $this->extractText($message);
    }

    public function json(string $system, string $prompt, array $schema, array $options = []): array
    {
        $message = $this->client()->messages->create(
            maxTokens: $options['max_tokens'] ?? $this->maxTokens,
            messages: [['role' => 'user', 'content' => $prompt]],
            model: $this->resolveModel($options),
            system: $system,
            outputConfig: ['format' => ['type' => 'json_schema', 'schema' => $schema]],
        );

        $text = $this->extractText($message);
        $decoded = json_decode($text, true);

        if (! is_array($decoded)) {
            throw new RuntimeException('Claude returned a non-JSON structured response.');
        }

        return $decoded;
    }

    private function resolveModel(array $options): string
    {
        if (! empty($options['model'])) {
            return $options['model'];
        }

        return ($options['purpose'] ?? 'report') === 'seed'
            ? $this->seedModel
            : $this->reportModel;
    }

    private function extractText(object $message): string
    {
        $text = '';
        foreach ($message->content as $block) {
            if (($block->type ?? null) === 'text') {
                $text .= $block->text;
            }
        }

        return trim($text);
    }

    private function client(): Client
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('ANTHROPIC_API_KEY is not configured.');
        }

        return $this->client ??= new Client(apiKey: $this->apiKey);
    }
}
