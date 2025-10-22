<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RecipeSuggester
{
    public function suggest(array $items, int $count = 5, ?string $diet = null): array
    {
        usort($items, fn($a,$b) => strcmp($a['expiry'] ?? '9999-12-31', $b['expiry'] ?? '9999-12-31'));

        $system = "You are a recipe assistant. Create practical recipes using the user's on-hand items.
- Prefer items that expire soon.
- Keep steps concise and doable for home cooks.
- If an important ingredient is missing, list it in 'shopping'.
- Respect dietary constraint if provided.";

        $user = "Here are the items (name, qty, expiry, fridge):\n" .
            collect($items)->map(function($it){
                return "- {$it['product']} ×{$it['quantity']} (expiry: " . ($it['expiry'] ?? '—') . ", fridge: {$it['fridge']})";
            })->implode("\n") .
            "\n\nPlease return {$count} recipes" . ($diet ? " suitable for: {$diet}" : "") . ".";

        $schema = [
            'type' => 'object',
            'additionalProperties' => false,
            'required' => ['recipes'],
            'properties' => [
                'recipes' => [
                    'type' => 'array',
                    'minItems' => 1,
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'properties' => [
                            'title' => ['type'=>'string'],
                            'servings' => ['type'=>'integer','minimum'=>1],
                            'time_minutes' => ['type'=>'integer','minimum'=>1],
                            'difficulty' => ['type'=>'string','enum'=>['easy','medium','hard']],
                            'uses' => ['type'=>'array','items'=>['type'=>'string']],
                            'missing_ok' => ['type'=>'array','items'=>['type'=>'string']],
                            'steps' => ['type'=>'array','items'=>['type'=>'string']],
                            'notes' => ['type'=>'string'],
                        ],
                        'required' => ['title','servings','time_minutes','difficulty','uses','steps','missing_ok','notes'],
                    ],
                ],
            ],
        ];

        $resp = Http::withToken(config('services.openai.key', env('OPENAI_API_KEY')))
            ->acceptJson()
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'response_format' => [
                    'type' => 'json_schema',
                    'json_schema' => [
                        'name'   => 'recipes_payload',
                        'schema' => $schema,
                        'strict' => true,
                    ],
                ],
                'messages' => [
                    ['role'=>'system','content'=>$system],
                    ['role'=>'user','content'=>$user],
                ],
                'temperature' => 0.7,
                'max_tokens'  => 1600,
            ]);

        if (!$resp->ok()) {
            return ['recipes' => []];
        }

        $content = data_get($resp->json(), 'choices.0.message.content', '');
        $parsed = is_string($content) ? json_decode($content, true) : null;

        return is_array($parsed) && isset($parsed['recipes']) ? $parsed : ['recipes' => []];
    }
}
