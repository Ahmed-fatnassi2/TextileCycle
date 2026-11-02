<?php

namespace App\Services;

use App\Exceptions\AssistantUnavailableException;
use Illuminate\Http\Client\ConnectionException;
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
            Log::warning('API OpenAI injoignable', ['error' => $e->getMessage()]);
            throw new AssistantUnavailableException('API OpenAI injoignable.', previous: $e);
        }

        if ($response->failed()) {
            Log::warning('Erreur API OpenAI', ['status' => $response->status(), 'body' => $response->json('error')]);
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
}
