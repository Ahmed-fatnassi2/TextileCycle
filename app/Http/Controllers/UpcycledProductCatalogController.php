<?php

namespace App\Http\Controllers;

use App\Models\MaterialBatch;
use App\Models\UpcycledProduct;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UpcycledProductCatalogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'material_type' => ['nullable', 'string', 'exists:material_batches,material_type'],
            'availability' => ['nullable', 'in:available,sold_out'],
        ]);

        $query = UpcycledProduct::with('materialBatch')->latest();

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhereHas('materialBatch', fn ($batchQuery) => $batchQuery->where('material_type', 'like', "%{$search}%"));
            });
        }

        if (! empty($filters['material_type'])) {
            $query->whereHas('materialBatch', fn ($batchQuery) => $batchQuery->where('material_type', $filters['material_type']));
        }

        if (($filters['availability'] ?? null) === 'available') {
            $query->where('stock', '>', 0);
        } elseif (($filters['availability'] ?? null) === 'sold_out') {
            $query->where('stock', 0);
        }

        $products = $query->paginate(12)->withQueryString();
        $materialTypes = MaterialBatch::query()
            ->whereHas('upcycledProducts')
            ->distinct()
            ->orderBy('material_type')
            ->pluck('material_type');

        return view('front.products.index', compact('products', 'materialTypes'));
    }

    public function show(UpcycledProduct $upcycledProduct): View
    {
        $upcycledProduct->load('materialBatch');

        return view('front.products.show', compact('upcycledProduct'));
    }
}