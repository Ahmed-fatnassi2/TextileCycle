<?php

namespace App\Services;

use App\Exceptions\AssistantUnavailableException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Chatbot "assistant de dépôt" basé sur l'API OpenAI (Chat Completions + appel d'outils).
 *
 * Déroulement : le modèle reçoit la conversation et peut appeler l'outil
 * recommend_deposit_points. Laravel exécute l'outil (DepositPointAdvisor, données réelles
 * de la base), renvoie le résultat au modèle, qui rédige la réponse finale.
 */
class DepositAssistant
{
    // Nombre maximal d'allers-retours modèle/outil pour une question
    private const MAX_ROUNDS = 4;

    public function __construct(private DepositPointAdvisor $advisor)
    {
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $history  conversation (user/assistant)
     * @param  array{latitude: float, longitude: float}|null  $position  position GPS partagée par le navigateur
     * @return array{reply: string, points: array}
     */
    public function reply(array $history, ?array $position = null): array
    {
        if (! config('services.openai.key')) {
            throw new AssistantUnavailableException('OPENAI_API_KEY manquante.');
        }

        $messages = [['role' => 'system', 'content' => $this->systemPrompt($position)], ...$history];
        $points = [];

        for ($round = 0; $round < self::MAX_ROUNDS; $round++) {
            $message = $this->complete($messages);
            $toolCalls = $message['tool_calls'] ?? [];

            if (! $toolCalls) {
                return ['reply' => trim($message['content'] ?? ''), 'points' => $points];
            }

            $messages[] = $message;

            foreach ($toolCalls as $call) {
                $result = $this->runTool($call['function']['name'] ?? '', $call['function']['arguments'] ?? '{}', $position);

                if (! empty($result['recommendations'])) {
                    $points = $result['recommendations'];
                }

                $messages[] = [
                    'role' => 'tool',
                    'tool_call_id' => $call['id'],
                    'content' => json_encode($result, JSON_UNESCAPED_UNICODE),
                ];
            }
        }

        throw new AssistantUnavailableException('Trop d’appels d’outils pour une seule question.');
    }

    private function complete(array $messages): array
    {
        try {
            $response = Http::withToken(config('services.openai.key'))
                ->baseUrl(config('services.openai.base_url'))
                ->timeout(config('services.openai.timeout'))
                ->acceptJson()
                ->post('/chat/completions', [
                    'model' => config('services.openai.model'),
                    'messages' => $messages,
                    'tools' => [$this->toolDefinition()],
                    'tool_choice' => 'auto',
                    'max_completion_tokens' => 2000,
                ]);
        } catch (ConnectionException $e) {
            Log::warning('Assistant OpenAI injoignable', ['error' => $e->getMessage()]);
            throw new AssistantUnavailableException('API OpenAI injoignable.', previous: $e);
        }

        if ($response->failed()) {
            Log::warning('Erreur API OpenAI', ['status' => $response->status(), 'body' => $response->json('error')]);
            throw new AssistantUnavailableException("Erreur API OpenAI ({$response->status()}).");
        }

        return $response->json('choices.0.message') ?? throw new AssistantUnavailableException('Réponse OpenAI vide.');
    }

    private function runTool(string $name, string $arguments, ?array $position): array
    {
        if ($name !== 'recommend_deposit_points') {
            return ['error' => "Outil inconnu : {$name}"];
        }

        $args = json_decode($arguments, true) ?: [];
        $city = isset($args['city']) && trim((string) $args['city']) !== '' ? (string) $args['city'] : null;
        $weight = isset($args['weight_kg']) && is_numeric($args['weight_kg']) ? (float) $args['weight_kg'] : null;

        // La position GPS vient du navigateur, pas du modèle : on ne lui fait pas recopier des coordonnées
        return $this->advisor->recommend(
            latitude: $city ? null : ($position['latitude'] ?? null),
            longitude: $city ? null : ($position['longitude'] ?? null),
            city: $city,
            weightKg: $weight && $weight > 0 ? $weight : null,
        );
    }

    private function toolDefinition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'recommend_deposit_points',
                'description' => 'Classe les points de collecte TexTileCycle ouverts selon la distance, le remplissage et la place restante. '
                    .'Utilise automatiquement la position GPS de l’utilisateur si elle est partagée et qu’aucune ville n’est donnée.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'city' => [
                            'type' => 'string',
                            'description' => 'Ville tunisienne mentionnée par l’utilisateur (ex. "Sousse", "La Marsa"). Omettre pour utiliser la position GPS.',
                        ],
                        'weight_kg' => [
                            'type' => 'number',
                            'description' => 'Poids approximatif des vêtements à déposer, en kg, si l’utilisateur le précise.',
                        ],
                    ],
                    'additionalProperties' => false,
                ],
            ],
        ];
    }

    // Consignes rédigées en anglais : le modèle suit alors mieux la langue de l'utilisateur
    // (avec un prompt en français, il répondait en français à une question en anglais).
    private function systemPrompt(?array $position): string
    {
        $gps = $position
            ? 'The user shared their GPS position: the tool uses it automatically when you do not pass a city.'
            : 'The user has NOT shared their GPS position.';

        $today = now()->toDateString();

        return <<<PROMPT
You are the deposit assistant of TexTileCycle, a Tunisian platform that collects and reuses clothes.
Your job: help citizens choose the best collection point ("point de collecte") and prepare their deposit.

LANGUAGE: always reply in the language of the user's latest message. English question -> English answer. Arabic or Tunisian Arabic (including Latin-script "Tounsi") -> answer in that same style. French -> French. Default to French if unsure. Translate data from the tool (e.g. "presque plein", state names) into the reply language.

Rules:
- For any question about where to deposit, call the recommend_deposit_points tool. Never invent a point, address, distance or fill level.
- {$gps} If you have neither a position nor a city, ask for the user's city or suggest clicking "Partager ma position".
- Present at most 3 points. For each: name, city, distance if known, and fill level (flag it when nearly full). Explain in one sentence why the first one is recommended.
- If the tool returns nearby unavailable points (full, under maintenance...), mention it briefly.
- If the tool returns error=unknown_city, say you do not know that city and list the available cities.
- "Déposer ici" and "Itinéraire" buttons are displayed automatically under your answer: do not write links.
- How a deposit works: the citizen books online (account required), receives a QR code, then shows it to the agent at the point, who weighs and validates the deposit.
- Accepted items: all clean, dry textiles, including worn or torn ones (they are recycled). The condition is entered when booking.
- Style: plain text, no Markdown, short and friendly.
- If the question is unrelated to depositing textiles, politely say you can only help with that.

Today's date: {$today}.
PROMPT;
    }
}
