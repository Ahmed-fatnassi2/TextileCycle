<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\AssistantUnavailableException;
use App\Http\Controllers\Controller;
use App\Models\MaterialBatch;
use App\Services\UpcyclingIdeaGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

// Admin : "Idées de produits" (IA) à partir des matières en stock.
class UpcyclingIdeaController extends Controller
{
    public function index(Request $request): View
    {
        // Une ligne par matière : nombre de lots et poids total
        $materials = MaterialBatch::query()
            ->selectRaw('material_type, COUNT(*) as batches, SUM(weight) as total_kg')
            ->groupBy('material_type')
            ->orderBy('material_type')
            ->get();

        return view('admin.upcycling.ideas', [
            'materials' => $materials,
            'selected' => $request->query('material_type'),
        ]);
    }

    public function suggest(Request $request, UpcyclingIdeaGenerator $generator): JsonResponse
    {
        $data = $request->validate([
            'material_type' => ['required', 'string', 'exists:material_batches,material_type'],
            // Pagination dynamique : idées déjà affichées
            'exclude' => ['nullable', 'array', 'max:'.UpcyclingIdeaGenerator::MAX_EXCLUDED],
            'exclude.*' => ['string', 'max:80'],
        ], [
            'exclude.max' => 'Nombre maximum d’idées atteint pour cette recherche.',
            'material_type.required' => 'Choisissez une matière.',
            'material_type.exists' => 'Cette matière n’est plus en stock.',
        ]);

        try {
            return response()->json($generator->forStock($data['material_type'], $data['exclude'] ?? []));
        } catch (AssistantUnavailableException) {
            return response()->json([
                'message' => 'Les idées IA sont momentanément indisponibles. Réessayez dans un instant.',
            ], 503);
        }
    }
}
