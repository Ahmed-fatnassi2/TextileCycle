<?php

namespace App\Http\Controllers\WorkshopOwner;

use App\Http\Controllers\Controller;
use App\Models\RepairPhoto;
use App\Models\RepairRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RepairRequestController extends Controller
{
    public function dashboard(Request $request): View
    {
        $workshops = $request->user()->ownedWorkshops()->orderBy('name')->get();
        $workshopIds = $workshops->modelKeys();
        $statusCounts = collect(RepairRequest::statuses())->mapWithKeys(
            fn (string $status) => [$status => RepairRequest::whereIn('workshop_id', $workshopIds)->where('status', $status)->count()]
        );
        $recentRepairs = RepairRequest::with(['user', 'workshop'])
            ->whereIn('workshop_id', $workshopIds)
            ->latest()
            ->take(6)
            ->get();

        return view('workshop-owner.dashboard', compact('workshops', 'statusCounts', 'recentRepairs'));
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(RepairRequest::statuses())],
        ]);
        $workshops = $request->user()->ownedWorkshops()->orderBy('name')->get();
        $workshopIds = $workshops->modelKeys();
        $repairRequests = RepairRequest::with(['user', 'workshop'])
            ->whereIn('workshop_id', $workshopIds)->when(
                $filters['search'] ?? null,
                fn ($query, $search) => $query->where('item_description', 'like', "%{$search}%")
            )->when(
                $filters['status'] ?? null,
                fn ($query, $status) => $query->where('status', $status)
            )->latest()->paginate(15)->withQueryString();

        return view('workshop-owner.repairs.index', compact('workshops', 'repairRequests'));
    }

    public function show(Request $request, RepairRequest $repairRequest): View
    {
        $this->assertWorkshopOwnership($request, $repairRequest);
        $repairRequest->load(['user', 'photos']);

        return view('workshop-owner.repairs.show', compact('repairRequest'));
    }

    public function quote(Request $request, RepairRequest $repairRequest): RedirectResponse
    {
        $this->assertWorkshopOwnership($request, $repairRequest);
        abort_unless(in_array($repairRequest->status, [RepairRequest::STATUS_PENDING, RepairRequest::STATUS_QUOTE_REFUSED], true), 422);

        $data = $request->validate([
            'estimated_cost' => ['required', 'numeric', 'min:0', 'max:100000', 'decimal:0,2'],
            'estimated_completion_date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $repairRequest->update([
            ...$data,
            'status' => RepairRequest::STATUS_QUOTE_PROPOSED,
        ]);
        $this->logRepairActivity($repairRequest, 'Devis proposé', 'Un devis a été proposé par l’atelier.', $request->user());

        return redirect()->route('workshop-owner.repairs.show', $repairRequest)->with('success', 'Le devis a été envoyé au citoyen.');
    }

    public function updateStatus(Request $request, RepairRequest $repairRequest): RedirectResponse
    {
        $this->assertWorkshopOwnership($request, $repairRequest);

        $nextStatuses = match ($repairRequest->status) {
            RepairRequest::STATUS_QUOTE_ACCEPTED => [RepairRequest::STATUS_IN_PROGRESS],
            RepairRequest::STATUS_IN_PROGRESS => [RepairRequest::STATUS_READY_FOR_PICKUP],
            default => [],
        };
        abort_if($nextStatuses === [], 422);

        $rules = [
            'status' => ['required', Rule::in($nextStatuses)],
            'after_photos' => ['nullable', 'array', 'max:3'],
            'after_photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
        if ($repairRequest->status === RepairRequest::STATUS_IN_PROGRESS) {
            $rules['cost'] = ['required', 'numeric', 'min:0', 'max:100000', 'decimal:0,2'];
        }
        $data = $request->validate($rules);

        $repairRequest->update(array_filter([
            'status' => $data['status'],
            'cost' => $data['cost'] ?? null,
        ], fn ($value) => $value !== null));
        foreach ($request->file('after_photos', []) as $photo) {
            $repairRequest->photos()->create([
                'path' => $photo->store('repair-photos', 'public'),
                'type' => RepairPhoto::TYPE_AFTER,
            ]);
        }

        $description = $data['status'] === RepairRequest::STATUS_IN_PROGRESS
            ? 'La réparation a commencé.'
            : 'La réparation est prête, le citoyen peut venir la récupérer.';
        $this->logRepairActivity($repairRequest, $data['status'], $description, $request->user());

        return redirect()->route('workshop-owner.repairs.show', $repairRequest)->with('success', 'Le statut de la réparation a été mis à jour.');
    }

    private function assertWorkshopOwnership(Request $request, RepairRequest $repairRequest): void
    {
        abort_unless(
            $request->user()->ownedWorkshops()->whereKey($repairRequest->workshop_id)->exists(),
            404
        );
    }

    private function logRepairActivity(RepairRequest $repairRequest, string $action, string $description, User $actor): void
    {
        if ($repairRequest->user) {
            User::logActivity($repairRequest->user, $action, $description, $actor, ['repair_request_id' => $repairRequest->id]);
        }
    }
}