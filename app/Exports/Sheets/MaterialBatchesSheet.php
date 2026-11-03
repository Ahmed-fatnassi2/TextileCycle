<?php

namespace App\Exports\Sheets;

use App\Models\MaterialBatch;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MaterialBatchesSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles
{
    // Les en-têtes "Type matière", "Poids (kg)", "Qualité" deviennent type_matiere, poids_kg, qualite à l'import
    public const HEADINGS = ['ID', 'Type matière', 'Poids (kg)', 'Qualité', 'Nb produits', 'Créé le'];

    public function __construct(private Collection $batches)
    {
    }

    public function collection(): Collection
    {
        return $this->batches;
    }

    public function headings(): array
    {
        return self::HEADINGS;
    }

    /** @param MaterialBatch $batch */
    public function map($batch): array
    {
        return [
            $batch->id,
            $batch->material_type,
            (float) $batch->weight,
            $batch->quality_grade,
            $batch->upcycledProducts->count(),
            $batch->created_at?->format('d/m/Y'),
        ];
    }

    public function title(): string
    {
        return 'Lots';
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
