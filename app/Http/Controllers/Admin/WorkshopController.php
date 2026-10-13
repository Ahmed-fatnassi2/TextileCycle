<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWorkshopRequest;
use App\Http\Requests\UpdateWorkshopRequest;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Http\Request;

class WorkshopController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $query = Workshop::with('owner')->withCount('repairRequests');
        if ($search = $filters['search'] ?? null) {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('specialty', 'like', "%{$search}%")->orWhere('address', 'like', "%{$search}%"));
        }
        $workshops = $query->latest()->paginate(10)->withQueryString();
        return view('admin.workshops.index', compact('workshops'));
    }

    public function create() { return view('admin.workshops.create', ['owners' => $this->availableOwners()]); }
    public function store(StoreWorkshopRequest $request) { Workshop::create($request->validated()); return redirect()->route('admin.workshops.index')->with('success', 'Atelier créé avec succès.'); }
    public function show(Workshop $workshop) { $workshop->load(['owner', 'repairRequests' => fn ($query) => $query->latest()->take(10)]); return view('admin.workshops.show', compact('workshop')); }
    public function edit(Workshop $workshop) { return view('admin.workshops.edit', ['workshop' => $workshop, 'owners' => $this->availableOwners()]); }
    public function update(UpdateWorkshopRequest $request, Workshop $workshop) { $workshop->update($request->validated()); return redirect()->route('admin.workshops.index')->with('success', 'Atelier mis à jour.'); }
    public function destroy(Workshop $workshop) { $workshop->delete(); return redirect()->route('admin.workshops.index')->with('success', 'Atelier supprimé.'); }

    private function availableOwners()
    {
        return User::where('role', User::ROLE_WORKSHOP_OWNER)
            ->orderBy('name')
            ->get();
    }
}
