<?php

namespace App\Services\Ai\Contracts;

interface AiClient
{
    /**
     * Run a single completion and return the model's text response.
     *
     * @param  array{model?:string,max_tokens?:int,thinking?:bool,temperature?:float}  $options
     */
    public function complete(string $system, string $prompt, array $options = []): string;

    /**
     * Run a completion constrained to a JSON schema and return the decoded array.
     *
     * @param  array<string,mixed>  $schema  A JSON-schema object describing the response.
     * @param  array{model?:string,max_tokens?:int}  $options
     * @return array<string,mixed>
     */
    public function json(string $system, string $prompt, array $schema, array $options = []): array;

    /**
     * Whether this driver is configured with a usable API key.
     */
    public function isConfigured(): bool;
}
