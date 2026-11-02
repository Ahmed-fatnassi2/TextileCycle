<?php

namespace App\Exports\Sheets;

use App\Models\UpcycledProduct;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UpcycledProductsSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles
{
    // "Prix (TND)" et "Lot ID" deviennent prix_tnd et lot_id à l'import
    public const HEADINGS = ['ID', 'Nom', 'Description', 'Prix (TND)', 'Stock', 'Lot ID', 'Matière du lot', 'Valeur du stock (TND)'];

    public function __construct(private Collection $products, private Collection $batches)
    {
    }

    public function collection(): Collection
    {
        return $this->products;
    }

    public function headings(): array
    {
        return self::HEADINGS;
    }

    /** @param UpcycledProduct $product */
    public function map($product): array
    {
        return [
            $product->id,
            $product->name,
            $product->description,
            (float) $product->price,
            $product->stock,
            $product->material_batch_id,
            $this->batches->firstWhere('id', $product->material_batch_id)?->material_type,
            round((float) $product->price * $product->stock, 2),
        ];
    }

    public function title(): string
    {
        return 'Produits';
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
