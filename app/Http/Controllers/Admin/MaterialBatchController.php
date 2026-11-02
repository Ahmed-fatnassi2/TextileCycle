<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMaterialBatchRequest;
use App\Http\Requests\UpdateMaterialBatchRequest;
use App\Models\MaterialBatch;
use App\Support\UpcyclingFilters;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MaterialBatchController extends Controller
{
    public function index(Request $request): View
    {
        $filters = UpcyclingFilters::fromRequest($request);
        $materialBatches = UpcyclingFilters::batches($filters)
            ->withCount('upcycledProducts')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.material-batches.index', [
            'materialBatches' => $materialBatches,
            'filters' => $filters,
            ...UpcyclingFilters::options(),
        ]);
    }

    public function create(): View
    {
        return view('admin.material-batches.create');
    }

    public function store(StoreMaterialBatchRequest $request): RedirectResponse
    {
        MaterialBatch::create($request->validated());

        return redirect()->route('admin.material-batches.index')
            ->with('success', 'Lot de matière créé avec succès.');
    }

    public function show(MaterialBatch $materialBatch): View
    {
        $materialBatch->load('upcycledProducts');

        return view('admin.material-batches.show', compact('materialBatch'));
    }

    public function edit(MaterialBatch $materialBatch): View
    {
        return view('admin.material-batches.edit', compact('materialBatch'));
    }

    public function update(UpdateMaterialBatchRequest $request, MaterialBatch $materialBatch): RedirectResponse
    {
        $materialBatch->update($request->validated());

        return redirect()->route('admin.material-batches.index')
            ->with('success', 'Lot de matière mis à jour.');
    }

    public function destroy(MaterialBatch $materialBatch): RedirectResponse
    {
        $materialBatch->delete();

        return redirect()->route('admin.material-batches.index')
            ->with('success', 'Lot de matière supprimé.');
    }
}
