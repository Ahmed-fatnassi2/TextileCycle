<?php

namespace App\Exports;

use App\Exports\Sheets\MaterialBatchesSheet;
use App\Exports\Sheets\UpcycledProductsSheet;
use App\Support\UpcyclingFilters;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

// Classeur Excel : une feuille "Lots", une feuille "Produits". Réimportable tel quel (colonne ID = mise à jour).
class UpcyclingExport implements WithMultipleSheets
{
    public function __construct(private array $filters = [])
    {
    }

    public function sheets(): array
    {
        $batches = UpcyclingFilters::batches($this->filters)->with('upcycledProducts')->orderBy('id')->get();

        return [
            new MaterialBatchesSheet($batches),
            new UpcycledProductsSheet($batches->flatMap->upcycledProducts->sortBy('id')->values(), $batches),
        ];
    }
}
