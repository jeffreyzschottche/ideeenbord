<?php

namespace App\Services\Ai\Drivers;

use App\Services\Ai\Contracts\AiClient;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * OpenAI driver (Chat Completions API) via Laravel's HTTP client.
 */
class OpenAiClient implements AiClient
{
    public function __construct(
        private readonly ?string $apiKey,
        private readonly string $baseUrl,
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
        $data = $this->request($system, $prompt, $options);

        return trim($data['choices'][0]['message']['content'] ?? '');
    }

    public function json(string $system, string $prompt, array $schema, array $options = []): array
    {
        $options['response_format'] = [
            'type' => 'json_schema',
            'json_schema' => [
                'name' => 'response',
                'schema' => $schema,
                'strict' => true,
            ],
        ];

        $content = $this->complete($system, $prompt, $options);
        $decoded = json_decode($content, true);

        if (! is_array($decoded)) {
            throw new RuntimeException('OpenAI returned a non-JSON structured response.');
        }

        return $decoded;
    }

    private function request(string $system, string $prompt, array $options): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('OPENAI_API_KEY is not configured.');
        }

        $payload = [
            'model' => $this->resolveModel($options),
            'max_tokens' => $options['max_tokens'] ?? $this->maxTokens,
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $prompt],
            ],
        ];

        if (isset($options['temperature'])) {
            $payload['temperature'] = $options['temperature'];
        }
        if (isset($options['response_format'])) {
            $payload['response_format'] = $options['response_format'];
        }

        $response = Http::withToken($this->apiKey)
            ->timeout(120)
            ->acceptJson()
            ->post(rtrim($this->baseUrl, '/').'/chat/completions', $payload);

        if ($response->failed()) {
            throw new RuntimeException('OpenAI request failed: '.$response->status().' '.$response->body());
        }

        return $response->json();
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
}
