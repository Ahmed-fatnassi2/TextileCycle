<?php

namespace App\Http\Controllers;

use App\Exceptions\AssistantUnavailableException;
use App\Services\DepositAssistant;
use App\Services\RepairAssistant;
use App\Services\AiQuota;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssistantController extends Controller
{
    // Endpoint AJAX du chatbot. L'historique est gardé côté navigateur et renvoyé à chaque message.
    public function chat(Request $request, DepositAssistant $assistant): JsonResponse
    {
        $data = $request->validate([
            'messages' => ['required', 'array', 'min:1', 'max:12'],
            'messages.*.role' => ['required', 'in:user,assistant'],
            'messages.*.content' => ['required', 'string', 'max:1000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
        ]);

        $history = array_map(
            fn (array $m) => ['role' => $m['role'], 'content' => $m['content']],
            $data['messages'],
        );

        if (end($history)['role'] !== 'user') {
            return response()->json(['message' => 'Le dernier message doit venir de l’utilisateur.'], 422);
        }

        $position = isset($data['latitude'], $data['longitude'])
            ? ['latitude' => (float) $data['latitude'], 'longitude' => (float) $data['longitude']]
            : null;

        try {
            return response()->json($assistant->reply($history, $position));
        } catch (AssistantUnavailableException) {
            return response()->json([
                'message' => 'L’assistant est momentanément indisponible. Consultez la carte des points de collecte.',
            ], 503);
        }
    }

    public function repairChat(Request $request, RepairAssistant $assistant, AiQuota $quota): JsonResponse
    {
        $data = $request->validate(['messages' => ['required', 'array', 'min:1', 'max:12'], 'messages.*.role' => ['required', 'in:user,assistant'], 'messages.*.content' => ['required', 'string', 'max:1000']]);
        $history = array_map(fn ($m) => ['role' => $m['role'], 'content' => mb_substr($m['content'], 0, 500)], $data['messages']);
        if (end($history)['role'] !== 'user') return response()->json(['message' => 'Le dernier message doit venir de vous.'], 422);
        try {
            $result = $assistant->reply($history, $request->user(), $quota, $request->ip());
            unset($result['cached']);
            return response()->json($result);
        } catch (\App\Exceptions\AiQuotaExceededException $e) {
            return response()->json(['message' => 'Quota quotidien atteint. Réessayez demain.', 'remaining' => $e->remaining], 429);
        } catch (AssistantUnavailableException $e) {
            $message = str_contains($e->getMessage(), 'limite d’utilisation')
                ? $e->getMessage()
                : 'Je ne peux pas consulter les réparations pour le moment. Réessayez plus tard.';
            return response()->json(['message' => $message], 503);
        }
    }
}
