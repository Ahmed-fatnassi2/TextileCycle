<?php

namespace App\Http\Controllers;

use App\Exceptions\AssistantUnavailableException;
use App\Services\DepositAssistant;
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
}
