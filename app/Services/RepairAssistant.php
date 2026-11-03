<?php

namespace App\Services;

use App\Exceptions\AssistantUnavailableException;
use App\Models\RepairRequest;
use App\Models\User;

class RepairAssistant
{
    public function __construct(private WorkshopAdvisor $advisor, private OpenAiClient $openAi) {}

    public function reply(array $history, ?User $user, AiQuota $quota, string $ip): array
    {
        if (!config('ai.repair_enabled')) throw new AssistantUnavailableException('L’assistant réparation est temporairement désactivé.');
        $key = $quota->cacheKey('chatbot', [$history, $user?->id ?? 'guest:'.$ip]);
        if ($cached = cache()->get($key)) return [...$cached, 'cached' => true];
        $messages = [['role' => 'system', 'content' => 'Tu es un assistant de réparation textile. Réponds brièvement dans la langue du dernier message. Utilise les outils pour connaître ateliers, prix et statuts. Ne fabrique aucun fait, prix ou atelier. Les estimations sont indicatives.'], ...array_slice($history, -10)];
        $tools = [['type' => 'function', 'function' => ['name' => 'search_workshops', 'description' => 'Chercher des ateliers réels', 'parameters' => ['type' => 'object', 'properties' => ['problem_type' => ['type' => 'string'], 'specialty_keyword' => ['type' => 'string']], 'additionalProperties' => false]]], ['type' => 'function', 'function' => ['name' => 'estimate_cost', 'description' => 'Estimer avec les tarifs historiques', 'parameters' => ['type' => 'object', 'properties' => ['problem_type' => ['type' => 'string'], 'item_description' => ['type' => 'string']], 'required' => ['problem_type'], 'additionalProperties' => false]]]];
        if ($user) $tools[] = ['type' => 'function', 'function' => ['name' => 'get_my_repair_status', 'description' => 'Consulter une réparation propre au citoyen connecté', 'parameters' => ['type' => 'object', 'properties' => ['repair_id' => ['type' => 'integer']], 'additionalProperties' => false]]];
        for ($i = 0; $i < 4; $i++) {
            if (!config('services.openai.key')) throw new AssistantUnavailableException('Clé API absente.');
            $quota->consume('chatbot', $user, $ip);
            $msg = $this->openAi->chat(['messages' => $messages, 'tools' => $tools, 'tool_choice' => 'auto', 'max_completion_tokens' => config('ai.max_tokens.chatbot'), '_usage_feature' => 'repair_assistant']);
            if (empty($msg['tool_calls'])) { $result = ['reply' => trim($msg['content'] ?? ''), 'points' => []]; cache()->put($key, $result, now()->addHours(1)); return [...$result, 'cached' => false]; }
            $messages[] = $msg;
            foreach ($msg['tool_calls'] as $call) {
                $args = json_decode($call['function']['arguments'] ?? '{}', true) ?: [];
                $result = match ($call['function']['name'] ?? '') {
                    'search_workshops' => ['workshops' => $this->advisor->search($args['problem_type'] ?? null, $args['specialty_keyword'] ?? null)],
                    'estimate_cost' => ['estimate' => $this->advisor->estimate(mb_substr((string) ($args['problem_type'] ?? ''), 0, 100))],
                    'get_my_repair_status' => $user ? $this->myStatus($user, $args['repair_id'] ?? null) : ['error' => 'Connexion requise'],
                    default => ['error' => 'Outil inconnu'],
                };
                $messages[] = ['role' => 'tool', 'tool_call_id' => $call['id'], 'content' => json_encode($result, JSON_UNESCAPED_UNICODE)];
            }
        }
        throw new AssistantUnavailableException('Réponse momentanément indisponible.');
    }

    private function myStatus(User $user, ?int $id): array
    {
        $query = RepairRequest::query()->where('user_id', $user->id)->with('workshop');
        if ($id) $query->whereKey($id);
        $repairs = $query->latest()->take(5)->get();
        return ['repairs' => $repairs->map(fn ($r) => ['id' => $r->id, 'status' => $r->status, 'estimated_cost' => $r->estimated_cost, 'estimated_date' => $r->estimated_completion_date?->toDateString(), 'workshop' => $r->workshop?->name])->all()];
    }
}
