<?php

namespace App\Http\Controllers;

use App\Models\Fridge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecipeController extends Controller
{
    public function suggest(Request $request)
    {
        $data = $request->validate([
            'diet'        => ['nullable','string','max:80'],
            'count'       => ['required','integer','min:1','max:8'],
            'fridge_id'   => ['nullable','integer'],
            'with_images' => ['nullable','boolean'],
        ]);

        $user       = $request->user();
        $diet       = $data['diet']  ?? null;
        $count      = $data['count'] ?? 3;
        $withImages = (bool)($data['with_images'] ?? false);

        // Collect items (selected fridge OR all fridges)
        $fridgeName = null;
        if (!empty($data['fridge_id'])) {
            $fridge = Fridge::where('id', $data['fridge_id'])
                ->where('user_id', $user->id)
                ->firstOrFail();
            $items = $fridge->items()->with('product:id,name')->get();
            $fridgeName = $fridge->name ?: null;
        } else {
            $items = $user->fridges()->with(['items.product:id,name'])->get()
                ->flatMap(fn($f) => $f->items);
        }

        // Build pantry lines (for prompt) and a fast membership set (for filtering)
        $pantryLines = $items->map(function ($it) {
            $name = $it->product?->name ?: 'Unbekanntes Produkt';
            $qty  = (int)max(1, $it->quantity ?? 1);
            return "- {$qty}× {$name}";
        })->values()->all();

        $pantrySet = $this->buildPantrySet($items); // canonical-name => true

        // Dry/demo path
        if ($request->boolean('dry')) {
            $recipes = $this->localFallback($pantryLines, $count, $diet);
            $recipes = $this->enforceIngredientsFromPantry($recipes, $pantrySet);
            $recipes = $this->filterShoppingAgainstPantry($recipes, $pantrySet);
            $recipes = $this->attachReferenceLinks($recipes);
            return response()->json(['recipes' => $recipes, 'source' => 'local-demo']);
        }

        // No API key → graceful local fallback
        $apiKey = config('services.openai.key');
        if (!$apiKey) {
            $recipes = $this->localFallback($pantryLines, $count, $diet);
            $recipes = $this->enforceIngredientsFromPantry($recipes, $pantrySet);
            $recipes = $this->filterShoppingAgainstPantry($recipes, $pantrySet);
            $recipes = $this->attachReferenceLinks($recipes);
            return response()->json(['recipes' => $recipes, 'source' => 'local-no-key']);
        }

        // ----- Prompt with dynamic header -----
        $pantryHeader = $fridgeName
            ? 'Verfügbare Zutaten (nur aus „' . $fridgeName . '“; Menge × Name):'
            : 'Verfügbare Zutaten (aus allen Kühlschränken; Menge × Name):';

        $prompt = $this->loadPrompt(
            resource_path('prompts/recipes_from_fridge_products.txt'),
            [
                '{{COUNT}}'         => $count,
                '{{DIET_LINE}}'     => $diet ? "Beachte die Vorgabe: {$diet}." : "Keine besonderen Ernährungs-Vorgaben.",
                '{{PANTRY_HEADER}}' => $pantryHeader,
                '{{PANTRY_LIST}}'   => implode("\n", $pantryLines) ?: "- (leer)",
            ]
        );

        // ----- JSON Schema (update: include ALL properties in required) -----
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
                        'required' => [
                            'title','summary','servings','total_minutes','difficulty',
                            'ingredients','steps','shopping','tips','image_prompt','references'
                        ],
                        'properties' => [
                            'title'         => ['type' => 'string'],
                            'summary'       => ['type' => 'string'],
                            'servings'      => ['type' => 'integer', 'minimum' => 1],
                            'total_minutes' => ['type' => 'integer', 'minimum' => 1],
                            'difficulty'    => ['type' => 'string', 'enum' => ['leicht','mittel','schwer']],
                            'ingredients'   => [
                                'type'  => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'additionalProperties' => false,
                                    'required' => ['name','amount','unit','optional'],
                                    'properties' => [
                                        'name'     => ['type' => 'string'],
                                        'amount'   => ['type' => 'number'],
                                        'unit'     => ['type' => 'string'],
                                        'optional' => ['type' => 'boolean'],
                                    ],
                                ],
                            ],
                            'steps' => [
                                'type'  => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'additionalProperties' => false,
                                    'required' => ['text','minutes'],
                                    'properties' => [
                                        'text'    => ['type' => 'string'],
                                        'minutes' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 240],
                                    ],
                                ],
                            ],
                            'shopping'     => ['type' => 'array', 'items' => ['type' => 'string']],
                            'tips'         => ['type' => 'string'],
                            'image_prompt' => ['type' => 'string'],
                            'references'   => [
                                'type'  => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'additionalProperties' => false,
                                    'required' => ['site','url'],
                                    'properties' => [
                                        'site' => ['type' => 'string'],
                                        'url'  => ['type' => 'string'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $payload = [
            'model' => 'gpt-4o-mini',
            'response_format' => [
                'type' => 'json_schema',
                'json_schema' => [
                    'name'   => 'RecipeBundle',
                    'schema' => $schema,
                    'strict' => false, // stays false, but schema now satisfies their validator anyway
                ],
            ],
            'messages' => [
                ['role'=>'system','content'=>'Du bist ein präziser, hilfreicher Küchenassistent.'],
                ['role'=>'user','content'=>$prompt],
            ],
            'temperature' => 0.7,
            'max_tokens'  => 2000,
        ];

        try {
            $resp = Http::timeout(60)
                ->asJson()
                ->acceptJson()
                ->withToken($apiKey)
                ->post('https://api.openai.com/v1/chat/completions', $payload);

            if (!$resp->ok()) {
                if ($resp->status() === 429) {
                    Log::warning('OpenAI quota/429 on chat', ['body' => $resp->body()]);
                    $recipes = $this->localFallback($pantryLines, $count, $diet);
                    $recipes = $this->enforceIngredientsFromPantry($recipes, $pantrySet);
                    $recipes = $this->filterShoppingAgainstPantry($recipes, $pantrySet);
                    $recipes = $this->attachReferenceLinks($recipes);
                    return response()->json(['recipes' => $recipes, 'source' => 'local-quota']);
                }

                Log::warning('OpenAI chat error', [
                    'status' => $resp->status(),
                    'body'   => mb_substr($resp->body(), 0, 600),
                ]);

                $recipes = $this->localFallback($pantryLines, $count, $diet);
                $recipes = $this->enforceIngredientsFromPantry($recipes, $pantrySet);
                $recipes = $this->filterShoppingAgainstPantry($recipes, $pantrySet);
                $recipes = $this->attachReferenceLinks($recipes);
                return response()->json(['recipes' => $recipes, 'source' => 'local-openai-error']);
            }

            $content = data_get($resp->json(), 'choices.0.message.content', '');
            $parsed  = is_string($content) ? json_decode($content, true) : null;

            if (!is_array($parsed) || !isset($parsed['recipes']) || !is_array($parsed['recipes'])) {
                $recipes = $this->localFallback($pantryLines, $count, $diet);
                $recipes = $this->enforceIngredientsFromPantry($recipes, $pantrySet);
                $recipes = $this->filterShoppingAgainstPantry($recipes, $pantrySet);
                $recipes = $this->attachReferenceLinks($recipes);
                return response()->json(['recipes' => $recipes, 'source' => 'local-parse-fallback']);
            }

            $recipes = $parsed['recipes'];

            // Enforce pantry-only ingredients + clean shopping
            $recipes = $this->enforceIngredientsFromPantry($recipes, $pantrySet);
            $recipes = $this->filterShoppingAgainstPantry($recipes, $pantrySet);

            // Optional images
            if ($withImages) {
                $recipes = $this->attachImages($recipes, $apiKey);
            }

            // Reference links
            $recipes = $this->attachReferenceLinks($recipes);

            return ['recipes' => array_values($recipes), 'source' => 'openai-json'];

        } catch (\Throwable $e) {
            Log::error('OpenAI HTTP exception', ['message' => $e->getMessage()]);
            $recipes = $this->localFallback($pantryLines, $count, $diet);
            $recipes = $this->enforceIngredientsFromPantry($recipes, $pantrySet);
            $recipes = $this->filterShoppingAgainstPantry($recipes, $pantrySet);
            $recipes = $this->attachReferenceLinks($recipes);
            return response()->json(['recipes' => $recipes, 'source' => 'local-exception']);
        }
    }

    private function loadPrompt(string $path, array $vars): string
    {
        $tpl = is_file($path) ? file_get_contents($path) : '';
        return strtr($tpl, $vars);
    }

    /**
     * Simple local fallback list of plausible recipes.
     * Produces objects compatible with the JSON schema.
     */
    private function localFallback(array $pantryLines, int $count, ?string $diet): array
    {
        $names = array_map(fn($l) => trim(preg_replace('/^-?\s*\d+\s*×\s*/u', '', $l)), $pantryLines);
        if (!$names) $names = ['Kartoffeln','Zwiebeln','Eier','Milch','Spinat','Tomaten'];

        $recipes = [];
        for ($i = 0; $i < $count; $i++) {
            $pick  = array_slice($names, $i % max(1, count($names)), 4) ?: array_slice($names, 0, min(4, count($names)));
            $title = trim(($diet ? "{$diet} " : "") . ($pick[0] ?? 'Küchenmix') . ' & ' . ($pick[1] ?? 'Gemüse'));

            $ings = [];
            foreach ($pick as $j => $n) {
                $ings[] = [
                    'name'     => $n,
                    'amount'   => [200,150,1,250,2,80][$j % 6],
                    'unit'     => ['g','g','Stück','ml','Stück','g'][$j % 6],
                    'optional' => false,
                ];
            }

            $recipes[] = [
                'title'         => $title,
                'summary'       => 'Schnelles, alltagstaugliches Gericht mit vorhandenen Zutaten.',
                'servings'      => 2 + ($i % 3),
                'total_minutes' => 20 + ($i % 3) * 5,
                'difficulty'    => ['leicht','mittel','mittel'][$i % 3],
                'ingredients'   => $ings,
                'steps'         => [
                    ['text' => 'Zutaten abwiegen, Gemüse waschen & in mundgerechte Stücke schneiden.', 'minutes' => 5],
                    ['text' => 'Große Pfanne mit 1 EL Öl erhitzen (mittlere Hitze), Gemüse 5–6 Min. anbraten, dabei umrühren.', 'minutes' => 6],
                    ['text' => 'Mit Salz & Pfeffer würzen, nach Bedarf 50 ml Wasser/Brühe zugeben und 3–4 Min. köcheln lassen.', 'minutes' => 4],
                    ['text' => 'Abschmecken und sofort servieren.', 'minutes' => 3],
                ],
                'shopping'     => ['Salz','Pfeffer','Knoblauch'],
                'tips'         => 'Schmeckt mit Reis, Pasta oder frischem Brot.',
                'image_prompt' => "Food photography of {$title}, appetizing, soft daylight, shallow depth of field",
                'references'   => [], // will be filled later
            ];
        }
        return $recipes;
    }

    // -------- Pantry enforcement & utilities --------

    private function canonicalName(?string $n): string
    {
        $n = mb_strtolower($n ?? '');
        $n = strtr($n, ['-' => ' ']); // treat hyphen as space
        // strip common descriptors safely (no trailing commas)
        $n = preg_replace('/\b(?:frisch(?:er|e|es)?|gehackt|geschnitten|gewürfelt|bio|reif|klein|groß|rot(?:e|er|es)?|grün(?:e|er|es)?|gelb(?:e|er|es)?|stück|stk|light|mild|scharf|geräuchert)\b/iu', ' ', $n);
        // keep only letters (incl. umlauts) and spaces
        $n = preg_replace('/[^a-zäöüß\s]/iu', ' ', $n);
        $n = trim(preg_replace('/\s+/', ' ', $n));
        return $n;
    }

    private function buildPantrySet($items): array
    {
        $set = [];
        foreach ($items as $it) {
            $name = $this->canonicalName($it->product?->name ?? '');
            if ($name !== '') $set[$name] = true;
        }
        return $set;
    }

    /**
     * Keep only ingredients that exist in the selected pantry.
     * Move any others into shopping.
     */
    private function enforceIngredientsFromPantry(array $recipes, array $pantrySet): array
    {
        foreach ($recipes as &$r) {
            $kept = [];
            $shopping = is_array($r['shopping'] ?? null) ? $r['shopping'] : [];

            foreach ((array)($r['ingredients'] ?? []) as $ing) {
                $name = is_array($ing) ? ($ing['name'] ?? '') : (string)$ing;
                $canon = $this->canonicalName($name);
                if ($canon !== '' && isset($pantrySet[$canon])) {
                    $kept[] = $ing;
                } else {
                    if ($name !== '') $shopping[] = $name;
                }
            }

            $r['ingredients'] = $kept;

            // If model provided lightweight "uses", enforce those too
            if (isset($r['uses']) && is_array($r['uses'])) {
                $r['uses'] = array_values(array_filter($r['uses'], function ($n) use ($pantrySet) {
                    return isset($pantrySet[$this->canonicalName($n)]);
                }));
            }

            // temporary; dedupe later in filterShoppingAgainstPantry
            if (!isset($r['shopping'])) $r['shopping'] = $shopping;
            else $r['shopping'] = array_merge($r['shopping'], $shopping);
        }
        return $recipes;
    }

    /**
     * Ensure shopping lists contain ONLY items not in pantry, deduped, max 4.
     * If shopping is empty, derive from ingredients that are not in pantry.
     */
    private function filterShoppingAgainstPantry(array $recipes, array $pantrySet): array
    {
        foreach ($recipes as &$r) {
            $collected = [];

            // If no shopping → derive from ingredients
            if (empty($r['shopping'])) {
                $src = [];
                if (!empty($r['ingredients'])) {
                    foreach ($r['ingredients'] as $ing) {
                        $src[] = is_array($ing) ? ($ing['name'] ?? '') : (string)$ing;
                    }
                } elseif (!empty($r['uses'])) {
                    $src = (array)$r['uses'];
                }
                foreach ($src as $name) {
                    $canon = $this->canonicalName($name);
                    if ($canon !== '' && !isset($pantrySet[$canon])) {
                        $collected[$canon] = $name;
                    }
                }
            }

            // Also keep anything model put in shopping that we don't have
            foreach ((array)($r['shopping'] ?? []) as $name) {
                $real = is_array($name) ? ($name['name'] ?? '') : $name;
                $canon = $this->canonicalName($real);
                if ($canon !== '' && !isset($pantrySet[$canon])) {
                    $collected[$canon] = $real;
                }
            }

            $r['shopping'] = array_slice(array_values($collected), 0, 4);
        }
        return $recipes;
    }

    // -------- Images & reference links --------

    private function attachImages(array $recipes, string $apiKey): array
    {
        foreach ($recipes as &$r) {
            try {
                $prompt = $r['image_prompt'] ?? ('Food photo of ' . ($r['title'] ?? 'recipe'));
                $img = Http::timeout(45)
                    ->asJson()
                    ->acceptJson()
                    ->withToken($apiKey)
                    ->post('https://api.openai.com/v1/images/generations', [
                        'model'  => 'gpt-image-1',
                        'prompt' => $prompt,
                        'size'   => '512x512',
                    ]);
                if ($img->ok()) {
                    $r['image_url'] = $img->json('data.0.url');
                }
            } catch (\Throwable $e) {
                // ignore image failures
            }
        }
        return $recipes;
    }

    private function attachReferenceLinks(array $recipes): array
    {
        foreach ($recipes as &$r) {
            $query = trim(($r['title'] ?? '') . ' ' . implode(' ',
                    array_map(fn($i) => $i['name'] ?? '', array_slice($r['ingredients'] ?? [], 0, 3))
                ));
            $q = rawurlencode($query ?: 'Rezept');

            $r['references'] = [
                ['site' => 'Chefkoch',        'url' => "https://www.chefkoch.de/suche.php?suche={$q}"],
                ['site' => 'EAT SMARTER',     'url' => "https://eatsmarter.de/suche?search={$q}"],
                ['site' => 'LECKER',          'url' => "https://www.lecker.de/suche?q={$q}"],
                ['site' => 'Essen & Trinken', 'url' => "https://www.essen-und-trinken.de/suche?q={$q}"],
            ];
        }
        return $recipes;
    }
}
