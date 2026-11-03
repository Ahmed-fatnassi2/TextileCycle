<?php

namespace App\Exports;

use App\Models\MaterialBatch;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

// Modèle Excel à remplir pour l'import : en-têtes + une ligne d'exemple.
class ImportTemplateExport implements FromArray, WithTitle, ShouldAutoSize, WithStyles
{
    public function __construct(private string $type)
    {
    }

    public function array(): array
    {
        if ($this->type === 'lots') {
            return [
                ['ID', 'Type matière', 'Poids (kg)', 'Qualité'],
                ['', 'Jean', 24.5, 'A'],
            ];
        }

        return [
            ['ID', 'Nom', 'Description', 'Prix (TND)', 'Stock', 'Lot ID'],
            ['', 'Sac cabas en jean', 'Sac solide cousu à partir de jeans récupérés.', 39.9, 10, MaterialBatch::min('id') ?? 1],
        ];
    }

    public function title(): string
    {
        return $this->type === 'lots' ? 'Lots' : 'Produits';
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
