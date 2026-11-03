<?php

namespace App\Services;

use App\Exceptions\AssistantUnavailableException;
use App\Models\AiUsageEvent;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Petit client pour l'API OpenAI Chat Completions, partagé par les fonctionnalités IA
 * (chatbot de dépôt, assistant de planning des dons). Toute erreur devient une
 * AssistantUnavailableException, que les contrôleurs transforment en message lisible.
 */
class OpenAiClient
{
    /**
     * Envoie une requête et retourne le message de l'assistant (choices[0].message).
     *
     * @param  array  $payload  messages, tools, response_format... (le modèle est ajouté automatiquement)
     */
    public function chat(array $payload): array
    {
        if (! config('services.openai.key')) {
            throw new AssistantUnavailableException('OPENAI_API_KEY manquante.');
        }

        $feature = $payload['_usage_feature'] ?? $this->featureFor($payload);
        unset($payload['_usage_feature']);

        try {
            $response = Http::withToken(config('services.openai.key'))
                ->baseUrl(config('services.openai.base_url'))
                ->timeout(config('services.openai.timeout'))
                ->acceptJson()
                ->post('/chat/completions', [
                    'model' => config('services.openai.model'),
                    'max_completion_tokens' => 2000,
                    ...$payload,
                ]);
        } catch (ConnectionException $e) {
            $this->recordUsage($feature, null, null, false);
            Log::warning('API OpenAI injoignable', ['error' => $e->getMessage()]);
            throw new AssistantUnavailableException('API OpenAI injoignable.', previous: $e);
        }

        $this->recordUsage($feature, $response, $response->json('usage', []), $response->successful());

        if ($response->failed()) {
            Log::warning('Erreur API OpenAI', ['status' => $response->status(), 'body' => $response->json('error')]);
            if ($response->status() === 429) {
                throw new AssistantUnavailableException('Le service IA a atteint sa limite d’utilisation ou son crédit API est épuisé. Réessayez plus tard ou vérifiez la facturation OpenAI.');
            }
            throw new AssistantUnavailableException("Erreur API OpenAI ({$response->status()}).");
        }

        return $response->json('choices.0.message') ?? throw new AssistantUnavailableException('Réponse OpenAI vide.');
    }

    /**
     * Variante "sorties structurées" : la réponse respecte le schéma JSON donné et est décodée.
     */
    public function json(array $messages, string $schemaName, array $schema, int $maxTokens = 2000): array
    {
        $message = $this->chat([
            'max_completion_tokens' => $maxTokens,
            'messages' => $messages,
            'response_format' => [
                'type' => 'json_schema',
                'json_schema' => ['name' => $schemaName, 'strict' => true, 'schema' => $schema],
            ],
        ]);

        if (! empty($message['refusal'])) {
            throw new AssistantUnavailableException('Le modèle a refusé la demande.');
        }

        $data = json_decode($message['content'] ?? '', true);

        return is_array($data) ? $data : throw new AssistantUnavailableException('Réponse JSON invalide.');
    }

    private function featureFor(array $payload): string
    {
        $schema = $payload['response_format']['json_schema']['name'] ?? null;
        if (is_string($schema)) {
            return match (true) {
                str_contains($schema, 'repair_quality') => 'repair_quality',
                str_contains($schema, 'repair_alternatives') => 'repair_alternatives',
                str_contains($schema, 'repair') => 'repair_assistant',
                str_contains($schema, 'upcycling') => 'upcycling_ideas',
                str_contains($schema, 'donation') => 'donation_planning',
                default => 'other',
            };
        }

        $toolNames = collect($payload['tools'] ?? [])
            ->map(fn (array $tool) => $tool['function']['name'] ?? '')
            ->all();

        return match (true) {
            in_array('search_workshops', $toolNames, true) => 'repair_assistant',
            in_array('recommend_deposit_points', $toolNames, true) => 'deposit_assistant',
            default => 'other',
        };
    }

    private function recordUsage(string $feature, ?Response $response, ?array $usage, bool $succeeded): void
    {
        $inputTokens = $usage['prompt_tokens'] ?? $usage['input_tokens'] ?? null;
        $outputTokens = $usage['completion_tokens'] ?? $usage['output_tokens'] ?? null;
        $context = [
            'feature' => $feature,
            'user_id' => Auth::id(),
            'cached' => false,
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'status_code' => $response?->status(),
            'succeeded' => $succeeded,
        ];

        AiUsageEvent::create([
            'feature' => $feature,
            'user_id' => Auth::id(),
            'model' => config('services.openai.model'),
            'status_code' => $response?->status(),
            'succeeded' => $succeeded,
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
        ]);

        Log::info('Appel OpenAI', $context);
    }
}
