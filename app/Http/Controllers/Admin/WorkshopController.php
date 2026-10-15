<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWorkshopRequest;
use App\Http\Requests\UpdateWorkshopRequest;
use App\Models\Workshop;
use Illuminate\Http\Request;

class WorkshopController extends Controller
{
    public function index(Request $request)
    {
        $query = Workshop::withCount('repairRequests');
        if ($search = $request->input('search')) {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('specialty', 'like', "%{$search}%")->orWhere('address', 'like', "%{$search}%"));
        }
        $workshops = $query->latest()->paginate(10)->withQueryString();
        return view('admin.workshops.index', compact('workshops'));
    }

    public function create() { return view('admin.workshops.create'); }
    public function store(StoreWorkshopRequest $request) { Workshop::create($request->validated()); return redirect()->route('admin.workshops.index')->with('success', 'Atelier créé avec succès.'); }
    public function show(Workshop $workshop) { $workshop->load(['repairRequests' => fn ($query) => $query->latest()->take(10)]); return view('admin.workshops.show', compact('workshop')); }
    public function edit(Workshop $workshop) { return view('admin.workshops.edit', compact('workshop')); }
    public function update(UpdateWorkshopRequest $request, Workshop $workshop) { $workshop->update($request->validated()); return redirect()->route('admin.workshops.index')->with('success', 'Atelier mis à jour.'); }
    public function destroy(Workshop $workshop) { $workshop->delete(); return redirect()->route('admin.workshops.index')->with('success', 'Atelier supprimé.'); }
}
