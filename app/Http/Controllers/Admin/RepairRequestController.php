<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRepairRequestRequest;
use App\Http\Requests\UpdateRepairRequestRequest;
use App\Models\RepairRequest;
use App\Models\Workshop;
use App\Models\User;
use App\Models\RepairPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class RepairRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = RepairRequest::with(['workshop', 'user']);
        if ($search = $request->input('search')) { $query->where('item_description', 'like', "%{$search}%"); }
        foreach (['status', 'problem_type', 'workshop_id'] as $filter) { if ($value = $request->input($filter)) { $query->where($filter, $value); } }
        $repairRequests = $query->latest()->paginate(15)->withQueryString();
        $workshops = Workshop::orderBy('name')->get();
        return view('admin.repair-requests.index', compact('repairRequests', 'workshops'));
    }

    public function create() { $workshops = Workshop::orderBy('name')->get(); $users = User::orderBy('name')->get(); return view('admin.repair-requests.create', compact('workshops', 'users')); }
    public function store(StoreRepairRequestRequest $request) { $repairRequest = RepairRequest::create(Arr::except($request->validated(), ['photos', 'after_photos'])); $this->storePhotos($request, $repairRequest, 'photos', RepairPhoto::TYPE_BEFORE); $this->storePhotos($request, $repairRequest, 'after_photos', RepairPhoto::TYPE_AFTER); return redirect()->route('admin.repair-requests.index')->with('success', 'Demande de réparation créée avec succès.'); }
    public function show(RepairRequest $repairRequest) { $repairRequest->load(['workshop', 'user', 'photos']); return view('admin.repair-requests.show', compact('repairRequest')); }
    public function edit(RepairRequest $repairRequest) { $workshops = Workshop::orderBy('name')->get(); $users = User::orderBy('name')->get(); return view('admin.repair-requests.edit', compact('repairRequest', 'workshops', 'users')); }
    public function update(UpdateRepairRequestRequest $request, RepairRequest $repairRequest) { $repairRequest->update(Arr::except($request->validated(), ['photos', 'after_photos'])); $this->storePhotos($request, $repairRequest, 'photos', RepairPhoto::TYPE_BEFORE); $this->storePhotos($request, $repairRequest, 'after_photos', RepairPhoto::TYPE_AFTER); return redirect()->route('admin.repair-requests.index')->with('success', 'Demande de réparation mise à jour.'); }
    public function destroy(RepairRequest $repairRequest) { $repairRequest->delete(); return redirect()->route('admin.repair-requests.index')->with('success', 'Demande de réparation supprimée.'); }

    private function storePhotos(Request $request, RepairRequest $repairRequest, string $field, string $type): void
    {
        foreach ($request->file($field, []) as $photo) {
            $repairRequest->photos()->create(['path' => $photo->store('repair-photos', 'public'), 'type' => $type]);
        }
    }
}
