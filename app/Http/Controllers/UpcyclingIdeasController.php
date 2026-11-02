<?php

namespace App\Http\Controllers;

use App\Exceptions\AssistantUnavailableException;
use App\Services\UpcyclingIdeaGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

// Public : "Que créer avec ma matière ?" (IA) — idées à faire soi-même + produits du catalogue.
class UpcyclingIdeasController extends Controller
{
    public function index(): View
    {
        return view('front.products.ideas');
    }

    public function suggest(Request $request, UpcyclingIdeaGenerator $generator): JsonResponse
    {
        $data = $request->validate([
            'material' => ['required', 'string', 'min:2', 'max:100'],
            // Pagination dynamique : idées déjà affichées
            'exclude' => ['nullable', 'array', 'max:'.UpcyclingIdeaGenerator::MAX_EXCLUDED],
            'exclude.*' => ['string', 'max:80'],
        ], [
            'exclude.max' => 'Nombre maximum d’idées atteint pour cette recherche.',
            'material.required' => 'Décrivez la matière que vous avez (ex. « 2 vieux jeans »).',
            'material.max' => 'Décrivez votre matière en 100 caractères maximum.',
        ]);

        try {
            return response()->json($generator->forVisitor($data['material'], $data['exclude'] ?? []));
        } catch (AssistantUnavailableException) {
            return response()->json([
                'message' => 'Les idées sont momentanément indisponibles. Réessayez dans un instant.',
            ], 503);
        }
    }
}
