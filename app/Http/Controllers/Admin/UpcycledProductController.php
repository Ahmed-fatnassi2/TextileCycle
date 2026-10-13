<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaterialBatch;
use App\Models\UpcycledProduct;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UpcycledProductController extends Controller
{
    public function index(): View
    {
        $upcycledProducts = UpcycledProduct::with('materialBatch')
            ->latest()
            ->paginate(10);

        return view('admin.upcycled-products.index', compact('upcycledProducts'));
    }

    public function create(): View
    {
        $materialBatches = MaterialBatch::orderBy('material_type')->get();

        return view('admin.upcycled-products.create', compact('materialBatches'));
    }

    public function store(Request $request): RedirectResponse
    {
        UpcycledProduct::create($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'stock' => ['required', 'integer', 'min:0'],
            'material_batch_id' => ['required', 'integer', 'exists:material_batches,id'],
        ]));

        return redirect()->route('admin.upcycled-products.index')
            ->with('success', 'Produit upcyclé créé avec succès.');
    }

    public function show(UpcycledProduct $upcycledProduct): View
    {
        $upcycledProduct->load('materialBatch');

        return view('admin.upcycled-products.show', compact('upcycledProduct'));
    }

    public function edit(UpcycledProduct $upcycledProduct): View
    {
        $materialBatches = MaterialBatch::orderBy('material_type')->get();

        return view('admin.upcycled-products.edit', compact('upcycledProduct', 'materialBatches'));
    }

    public function update(Request $request, UpcycledProduct $upcycledProduct): RedirectResponse
    {
        $upcycledProduct->update($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'stock' => ['required', 'integer', 'min:0'],
            'material_batch_id' => ['required', 'integer', 'exists:material_batches,id'],
        ]));

        return redirect()->route('admin.upcycled-products.index')
            ->with('success', 'Produit upcyclé mis à jour.');
    }

    public function destroy(UpcycledProduct $upcycledProduct): RedirectResponse
    {
        $upcycledProduct->delete();

        return redirect()->route('admin.upcycled-products.index')
            ->with('success', 'Produit upcyclé supprimé.');
    }
}