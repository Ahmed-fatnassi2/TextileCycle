<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRepairRequestRequest;
use App\Models\RepairRequest;
use App\Models\Workshop;
use App\Models\RepairPhoto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class RepairController extends Controller
{
    public function create()
    {
        $workshops = Workshop::orderBy('name')->get();
        return view('front.repairs.create', compact('workshops'));
    }

    public function store(StoreRepairRequestRequest $request)
    {
        $data = Arr::except($request->validated(), 'photos');
        $data['user_id'] = $request->user()->id;
        $data['status'] = RepairRequest::STATUS_PENDING;
        $data['cost'] = 0;
        $repairRequest = RepairRequest::create($data);
        $this->storePhotos($request, $repairRequest, 'photos', RepairPhoto::TYPE_BEFORE);
        return redirect()->route('repairs.index')->with('success', 'Votre demande a bien été envoyée à l’atelier.');
    }

    public function index(Request $request)
    {
        $repairRequests = $request->user()->repairRequests()->with(['workshop', 'photos'])->latest()->paginate(10);
        return view('front.repairs.index', compact('repairRequests'));
    }

    public function show(Request $request, RepairRequest $repairRequest)
    {
        abort_unless($repairRequest->user_id === $request->user()->id, 403);
        $repairRequest->load(['workshop', 'photos']);
        return view('front.repairs.show', compact('repairRequest'));
    }

    public function respondToQuote(Request $request, RepairRequest $repairRequest): RedirectResponse
    {
        abort_unless($repairRequest->user_id === $request->user()->id, 403);
        abort_unless($repairRequest->status === RepairRequest::STATUS_QUOTE_PROPOSED, 422);
        $decision = $request->validate(['decision' => 'required|in:accept,refuse'])['decision'];
        $repairRequest->update(['status' => $decision === 'accept' ? RepairRequest::STATUS_QUOTE_ACCEPTED : RepairRequest::STATUS_QUOTE_REFUSED]);
        return back()->with('success', $decision === 'accept' ? 'Devis accepté. L’atelier peut commencer la réparation.' : 'Devis refusé. L’atelier en a été informé.');
    }

    private function storePhotos(Request $request, RepairRequest $repairRequest, string $field, string $type): void
    {
        foreach ($request->file($field, []) as $photo) {
            $repairRequest->photos()->create(['path' => $photo->store('repair-photos', 'public'), 'type' => $type]);
        }
    }
}
