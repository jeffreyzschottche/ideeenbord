<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default AI provider
    |--------------------------------------------------------------------------
    | Which driver the AiClient resolves to. Supported: "claude", "openai".
    */
    'provider' => env('AI_PROVIDER', 'claude'),

    'claude' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        // Model used for the heavy analytical work (brand reports).
        'report_model' => env('AI_REPORT_MODEL', 'claude-opus-4-8'),
        // Cheaper/faster model used for content seeding & interactions.
        'seed_model' => env('AI_SEED_MODEL', 'claude-haiku-4-5'),
        'max_tokens' => (int) env('AI_MAX_TOKENS', 16000),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        'report_model' => env('OPENAI_MODEL', 'gpt-4o'),
        'seed_model' => env('OPENAI_SEED_MODEL', 'gpt-4o-mini'),
        'max_tokens' => (int) env('AI_MAX_TOKENS', 16000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Content bots (Seed-AI / Interactie-AI)
    |--------------------------------------------------------------------------
    */
    'bots' => [
        'enabled' => (bool) env('AI_BOTS_ENABLED', false),
        // The username of the dedicated bot account that seeded content is
        // attributed to (created via `php artisan ai:ensure-bot`).
        'account_username' => env('AI_BOT_USERNAME', 'ideeenbord-bot'),
        'seed_brands_per_day' => (int) env('AI_SEED_BRANDS_PER_DAY', 5),
        'seed_ideas_per_brand' => (int) env('AI_SEED_IDEAS_PER_BRAND', 2),
        'interactions_per_day' => (int) env('AI_INTERACTIONS_PER_DAY', 30),
    ],
];
