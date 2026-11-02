<?php

namespace App\Support;

use App\Models\MaterialBatch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

// Filtres partagés par la liste des lots, l'export Excel et le rapport PDF.
class UpcyclingFilters
{
    public static function fromRequest(Request $request): array
    {
        return array_filter($request->validate([
            'material_type' => ['nullable', 'string', 'max:255'],
            'quality_grade' => ['nullable', 'string', 'max:10'],
        ]), fn ($value) => $value !== null && $value !== '');
    }

    public static function batches(array $filters): Builder
    {
        return MaterialBatch::query()
            ->when($filters['material_type'] ?? null, fn ($q, $type) => $q->where('material_type', $type))
            ->when($filters['quality_grade'] ?? null, fn ($q, $grade) => $q->where('quality_grade', $grade));
    }

    // Valeurs proposées dans les listes déroulantes de filtre
    public static function options(): array
    {
        return [
            'materialTypes' => MaterialBatch::distinct()->orderBy('material_type')->pluck('material_type'),
            'qualityGrades' => MaterialBatch::distinct()->orderBy('quality_grade')->pluck('quality_grade'),
        ];
    }
}
