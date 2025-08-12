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
            'diet'      => ['nullable','string','max:80'],
            'count'     => ['required','integer','min:1','max:8'],
            'fridge_id' => ['nullable','integer'],
        ]);

        $user  = $request->user();
        $diet  = $data['diet']  ?? null;
        $count = $data['count'] ?? 5;

        if (!empty($data['fridge_id'])) {
            $fridge = Fridge::where('id', $data['fridge_id'])
                ->where('user_id', $user->id)->firstOrFail();

            $items = $fridge->items()->with('product:id,name')->get();
        } else {
            $items = $user->fridges()->with(['items.product:id,name'])->get()
                ->flatMap(fn($f) => $f->items);
        }

        $pantry = $items->map(function ($it) {
            $name = $it->product?->name ?? 'Unbekanntes Produkt';
            $qty  = (int)($it->quantity ?? 1);
            return "{$qty}× {$name}";
        })->values()->all();

        if ($request->boolean('dry')) {
            return response()->json([
                'recipes' => $this->localFallback($pantry, $count, $diet),
                'source'  => 'local-demo',
            ]);
        }

        $key = config('services.openai.key');
        if (!$key) {
            return response()->json([
                'recipes' => $this->localFallback($pantry, $count, $diet),
                'source'  => 'local-no-key',
            ]);
        }

        $system = "Du bist ein hilfreicher Küchenassistent. Antworte auf Deutsch. Gib deine Antwort ausschließlich als JSON zurück.";
        $userMsg = trim("
Verfügbare Zutaten (aus dem Kühlschrank):
- " . implode("\n- ", $pantry) . "

Erzeuge {$count} Rezepte, die möglichst viele vorhandene Zutaten nutzen.
Wenn Zutaten fehlen, führe sie unter 'missing_ok' (max. 4 sinnvolle Ergänzungen) auf.
".($diet ? "Ernährungsvorgaben: {$diet}." : "")."
Achte auf realistische Zeiten & klare, kurze Schritte.

Antworte **nur** mit einem JSON-Objekt der Form:
{
  \"recipes\": [
    {
      \"title\": string,
      \"time_minutes\": integer,
      \"difficulty\": string,
      \"servings\": integer,
      \"uses\": [string, ...],
      \"missing_ok\": [string, ...],
      \"steps\": [string, ...],
      \"notes\": string
    }
  ]
}
        ");

        $endpoint = 'https://api.openai.com/v1/chat/completions';
        $model    = 'gpt-4o-mini';

        $payloadJsonMode = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user',   'content' => $userMsg],
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature' => 0.7,
            'max_tokens'  => 1200,
        ];

        try {
            $resp = Http::timeout(45)
                ->acceptJson()
                ->withToken($key)
                ->post($endpoint, $payloadJsonMode);

            if (!$resp->ok()) {
                Log::warning('OpenAI JSON-mode failed', [
                    'status' => $resp->status(),
                    'body'   => $resp->body(),
                ]);

                if ($resp->status() === 429) {
                    return response()->json([
                        'recipes' => $this->localFallback($pantry, $count, $diet),
                        'source'  => 'local-quota',
                    ]);
                }

                $payloadPlain = [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user',   'content' => $userMsg],
                    ],
                    'temperature' => 0.7,
                    'max_tokens'  => 1200,
                ];

                $resp2 = Http::timeout(45)
                    ->acceptJson()
                    ->withToken($key)
                    ->post($endpoint, $payloadPlain);

                if (!$resp2->ok()) {
                    if ($resp2->status() === 429) {
                        return response()->json([
                            'recipes' => $this->localFallback($pantry, $count, $diet),
                            'source'  => 'local-quota',
                        ]);
                    }

                    Log::error('OpenAI fallback failed', [
                        'status' => $resp2->status(),
                        'body'   => $resp2->body(),
                    ]);
                    return response()->json([
                        'error'   => 'OpenAI request failed',
                        'details' => $resp2->json() ?: ['status' => $resp2->status(), 'raw' => $resp2->body()],
                    ], 502);
                }

                $content = data_get($resp2->json(), 'choices.0.message.content', '');
                $json    = is_string($content) ? json_decode($content, true) : null;
                if (!is_array($json) && is_string($content) && preg_match('/\{.*\}/s', $content, $m)) {
                    $json = json_decode($m[0], true);
                }

                if (!is_array($json) || !isset($json['recipes']) || !is_array($json['recipes'])) {
                    return response()->json([
                        'error'   => 'Malformed response from model (fallback)',
                        'details' => ['snippet' => mb_substr((string)$content, 0, 400)],
                    ], 502);
                }

                return ['recipes' => array_values($json['recipes']), 'source' => 'openai-fallback'];
            }

            $content = data_get($resp->json(), 'choices.0.message.content', '');
            $json    = is_string($content) ? json_decode($content, true) : null;
            if (!is_array($json) && is_string($content) && preg_match('/\{.*\}/s', $content, $m)) {
                $json = json_decode($m[0], true);
            }

            if (!is_array($json) || !isset($json['recipes']) || !is_array($json['recipes'])) {
                return response()->json([
                    'error'   => 'Malformed response from model',
                    'details' => ['snippet' => mb_substr((string)$content, 0, 400)],
                ], 502);
            }

            return ['recipes' => array_values($json['recipes']), 'source' => 'openai-json'];

        } catch (\Throwable $e) {
            Log::error('OpenAI HTTP exception', ['message' => $e->getMessage()]);
            return response()->json([
                'recipes' => $this->localFallback($pantry, $count, $diet),
                'source'  => 'local-exception',
            ]);
        }
    }

    /**
     * Very simple local fallback: creates plausible recipes from pantry.
     */
    private function localFallback(array $pantry, int $count, ?string $diet): array
    {
        $names = array_map(function ($s) {
            return preg_replace('/^\s*\d+\s*×\s*/u', '', $s);
        }, $pantry);

        if (empty($names)) {
            $names = ['Kartoffeln', 'Zwiebeln', 'Eier', 'Milch'];
        }

        $recipes = [];
        for ($i = 0; $i < $count; $i++) {
            $uses = array_slice($names, $i % max(1,count($names)), 4);
            if (count($uses) < 2) {
                $uses = array_slice($names, 0, min(4, count($names)));
            }
            $title = ($diet ? "{$diet} " : "") . ($uses[0] ?? 'Küchenmix') . " & " . ($uses[1] ?? 'Gemüse') . " – Pfanne";

            $recipes[] = [
                'title'        => $title,
                'time_minutes' => 15 + ($i * 5) % 20,
                'difficulty'   => ['leicht','mittel','mittel','leicht','leicht'][$i % 5],
                'servings'     => 2 + ($i % 3),
                'uses'         => $uses,
                'missing_ok'   => ['Salz','Pfeffer'],
                'steps'        => [
                    'Zutaten waschen und klein schneiden.',
                    'Pfanne erhitzen, etwas Öl hinzugeben.',
                    'Zutaten anbraten, würzen und gelegentlich wenden.',
                    'Mit Beilage servieren.',
                ],
                'notes'        => 'Lokaler Vorschlag (keine KI).',
            ];
        }
        return $recipes;
    }
}
