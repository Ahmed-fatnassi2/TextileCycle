<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpcycledProductRequest;
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

    // Les paramètres d'URL (?name=...&material_batch_id=...) préremplissent le formulaire : utilisé par le studio d'idées IA
    public function create(Request $request): View
    {
        $materialBatches = MaterialBatch::orderBy('material_type')->get();
        $upcycledProduct = new UpcycledProduct($request->only(['name', 'description', 'price', 'stock', 'material_batch_id']));

        return view('admin.upcycled-products.create', compact('materialBatches', 'upcycledProduct'));
    }

    public function store(UpcycledProductRequest $request): RedirectResponse
    {
        UpcycledProduct::create($request->validated());

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

    public function update(UpcycledProductRequest $request, UpcycledProduct $upcycledProduct): RedirectResponse
    {
        $upcycledProduct->update($request->validated());

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