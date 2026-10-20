<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
<<<<<<< HEAD
use App\Http\Requests\StoreMaterialBatchRequest;
use App\Http\Requests\UpdateMaterialBatchRequest;
use App\Models\MaterialBatch;
use Illuminate\Http\RedirectResponse;
=======
use App\Models\MaterialBatch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
>>>>>>> origin/Donation/Association-"aziz"
use Illuminate\View\View;

class MaterialBatchController extends Controller
{
    public function index(): View
    {
        $materialBatches = MaterialBatch::withCount('upcycledProducts')
            ->latest()
            ->paginate(10);

        return view('admin.material-batches.index', compact('materialBatches'));
    }

    public function create(): View
    {
        return view('admin.material-batches.create');
    }

<<<<<<< HEAD
    public function store(StoreMaterialBatchRequest $request): RedirectResponse
    {
        MaterialBatch::create($request->validated());
=======
    public function store(Request $request): RedirectResponse
    {
        MaterialBatch::create($request->validate([
            'material_type' => ['required', 'string', 'max:255'],
            'weight' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
            'quality_grade' => ['required', 'string', 'max:255'],
        ]));
>>>>>>> origin/Donation/Association-"aziz"

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

<<<<<<< HEAD
    public function update(UpdateMaterialBatchRequest $request, MaterialBatch $materialBatch): RedirectResponse
    {
        $materialBatch->update($request->validated());
=======
    public function update(Request $request, MaterialBatch $materialBatch): RedirectResponse
    {
        $materialBatch->update($request->validate([
            'material_type' => ['required', 'string', 'max:255'],
            'weight' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
            'quality_grade' => ['required', 'string', 'max:255'],
        ]));
>>>>>>> origin/Donation/Association-"aziz"

        return redirect()->route('admin.material-batches.index')
            ->with('success', 'Lot de matière mis à jour.');
    }

    public function destroy(MaterialBatch $materialBatch): RedirectResponse
    {
        $materialBatch->delete();

        return redirect()->route('admin.material-batches.index')
            ->with('success', 'Lot de matière supprimé.');
    }
<<<<<<< HEAD
}
=======
}
>>>>>>> origin/Donation/Association-"aziz"
