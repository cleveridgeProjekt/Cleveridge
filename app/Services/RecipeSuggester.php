<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RecipeSuggester
{
    public function suggest(array $items, int $count = 5, ?string $diet = null): array
    {
        usort($items, function($a, $b) {
            return strcmp($a['expiry'] ?? '9999-12-31', $b['expiry'] ?? '9999-12-31');
        });

        $system = "You are a recipe assistant. Create practical recipes using the user's on-hand items.
- Prefer items that expire soon.
- Keep steps concise and doable for home cooks.
- If an important ingredient is missing, suggest a simple substitute.
- Respect dietary constraint if provided.";

        $user = [
            "Here are the items (name, qty, expiry, fridge):\n" .
            collect($items)->map(function($it) {
                return "- {$it['product']} ×{$it['quantity']} (expiry: " . ($it['expiry'] ?? '—') . ", fridge: {$it['fridge']})";
            })->implode("\n") .
            "\n\nPlease return {$count} recipes" . ($diet ? " suitable for: {$diet}" : "") . "."
        ];

        $schema = [
            "name" => "recipes_payload",
            "schema" => [
                "type" => "object",
                "properties" => [
                    "recipes" => [
                        "type" => "array",
                        "items" => [
                            "type" => "object",
                            "properties" => [
                                "title" => ["type" => "string"],
                                "servings" => ["type" => "integer"],
                                "time_minutes" => ["type" => "integer"],
                                "difficulty" => ["type" => "string", "enum" => ["easy","medium","hard"]],
                                "uses" => ["type" => "array", "items" => ["type" => "string"]],
                                "missing_ok" => ["type" => "array", "items" => ["type" => "string"]],
                                "steps" => ["type" => "array", "items" => ["type" => "string"]],
                                "notes" => ["type" => "string"]
                            ],
                            "required" => ["title", "servings", "time_minutes", "difficulty", "uses", "steps"]
                        ]
                    ]
                ],
                "required" => ["recipes"],
                "additionalProperties" => false
            ],
            "strict" => true
        ];

        $resp = Http::withToken(config('services.openai.key', env('OPENAI_API_KEY')))
            ->acceptJson()
            ->post('https://api.openai.com/v1/responses', [
                "model" => "gpt-4o-mini",
                "response_format" => [
                    "type" => "json_schema",
                    "json_schema" => $schema
                ],
                "input" => [
                    ["role" => "system", "content" => $system],
                    ["role" => "user",   "content" => $user[0]],
                ],
            ]);

        if (!$resp->ok()) {
            return ["recipes" => []];
        }

        $body = $resp->json();

        $text = data_get($body, 'output.0.content.0.text') ??
            data_get($body, 'choices.0.message.content');

        $parsed = json_decode($text ?? "{}", true);

        return is_array($parsed) && isset($parsed['recipes'])
            ? $parsed
            : ["recipes" => []];
    }
}
