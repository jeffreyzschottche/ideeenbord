<?php

namespace App\Services\Ai;

use App\Models\Brand;
use App\Models\Idea;
use App\Models\User;
use App\Services\Ai\Contracts\AiClient;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Drives the content bots:
 *  - Seed-AI: places realistic ideas at brands so pages aren't empty.
 *  - Interactie-AI: hands out likes/dislikes to stimulate engagement.
 *  - AI-helper: same as Seed-AI but targeted + instructable on demand.
 *
 * All bot content is attributed to a single dedicated `is_bot` account.
 */
class ContentBotService
{
    private const MAX_IDEAS_PER_BRAND = 5;

    public function __construct(private readonly AiClient $ai)
    {
    }

    public function ensureBot(): User
    {
        $username = config('ai.bots.account_username', 'ideeenbord-bot');

        return User::firstOrCreate(
            ['username' => $username],
            [
                'name' => 'Ideeënbord',
                'email' => $username.'@bots.ideeenbord.nl',
                'password' => Hash::make(Str::random(40)),
                'email_verified_at' => now(),
                'role' => 'user',
                'is_bot' => true,
            ]
        );
    }

    /**
     * Seed ideas at brands.
     *
     * @param  int[]  $brandIds  Specific brands to target (empty = random selection).
     * @return array{brands:int,ideas:int,details:array<int,array{brand:string,created:int}>}
     */
    public function seedIdeas(?int $brands = null, ?int $perBrand = null, array $brandIds = [], string $instruction = ''): array
    {
        $bot = $this->ensureBot();
        $brands ??= (int) config('ai.bots.seed_brands_per_day', 5);
        $perBrand ??= (int) config('ai.bots.seed_ideas_per_brand', 2);

        $targets = ! empty($brandIds)
            ? Brand::whereIn('id', $brandIds)->get()
            : Brand::where('accepted', true)->inRandomOrder()->limit($brands)->get();

        $totalIdeas = 0;
        $details = [];

        foreach ($targets as $brand) {
            $existing = Idea::where('brand_id', $brand->id)->where('user_id', $bot->id)->count();
            $room = self::MAX_IDEAS_PER_BRAND - $existing;
            if ($room <= 0) {
                continue;
            }

            $want = min($perBrand, $room);
            $ideas = $this->generateIdeas($brand, $want, $instruction);
            $created = 0;

            foreach ($ideas as $item) {
                $idea = Idea::create([
                    'brand_id' => $brand->id,
                    'user_id' => $bot->id,
                    'title' => Str::limit((string) ($item['title'] ?? ''), 120, ''),
                    'description' => (string) ($item['description'] ?? ''),
                    'category' => $item['category'] ?? null,
                ]);
                $bot->created_posts = [...($bot->created_posts ?? []), $idea->id];
                $created++;
                $totalIdeas++;
            }

            $bot->save();
            $details[] = ['brand' => $brand->title, 'created' => $created];
        }

        return ['brands' => count($details), 'ideas' => $totalIdeas, 'details' => $details];
    }

    /**
     * Hand out likes/dislikes to existing ideas (not the bot's own).
     *
     * @return array{liked:int,disliked:int}
     */
    public function distributeInteractions(?int $count = null): array
    {
        $bot = $this->ensureBot();
        $count ??= (int) config('ai.bots.interactions_per_day', 30);

        $seen = array_merge($bot->liked_posts ?? [], $bot->disliked_posts ?? []);

        $ideas = Idea::where('user_id', '!=', $bot->id)
            ->whereNotIn('id', $seen ?: [0])
            ->inRandomOrder()
            ->limit($count)
            ->get();

        $liked = 0;
        $disliked = 0;

        foreach ($ideas as $idea) {
            // Weight towards likes (positive reinforcement).
            if (random_int(1, 100) <= 75) {
                $idea->increment('likes');
                $bot->liked_posts = [...($bot->liked_posts ?? []), $idea->id];
                $liked++;
            } else {
                $idea->increment('dislikes');
                $bot->disliked_posts = [...($bot->disliked_posts ?? []), $idea->id];
                $disliked++;
            }
        }

        $bot->save();

        return ['liked' => $liked, 'disliked' => $disliked];
    }

    /**
     * Generate idea drafts for a brand via the AI provider.
     *
     * @return array<int,array{title:string,description:string,category:?string}>
     */
    private function generateIdeas(Brand $brand, int $count, string $instruction = ''): array
    {
        $system = <<<'SYS'
        Je bent een gewone Nederlandse consument die op Ideeënbord ideeën, wensen en
        verbeterpunten achterlaat voor merken. Schrijf authentiek en gevarieerd: soms
        een concreet productidee, soms een wens, soms een verbeterpunt of klacht.
        Gebruik natuurlijke spreektaal (je-vorm), geen marketingtaal, geen emoji.
        Verzin geen onwaarheden over het merk.
        SYS;

        $extra = $instruction !== '' ? "\n\nExtra instructie: {$instruction}" : '';

        $prompt = "Bedenk {$count} realistische, onderling verschillende ideeën voor het merk "
            ."\"{$brand->title}\" (categorie: {$brand->category}). Varieer in toon en invalshoek."
            .$extra;

        $schema = [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                'ideas' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'properties' => [
                            'title' => ['type' => 'string'],
                            'description' => ['type' => 'string'],
                            'category' => ['type' => 'string'],
                        ],
                        'required' => ['title', 'description', 'category'],
                    ],
                ],
            ],
            'required' => ['ideas'],
        ];

        $result = $this->ai->json($system, $prompt, $schema, ['purpose' => 'seed']);

        return array_slice($result['ideas'] ?? [], 0, $count);
    }
}
