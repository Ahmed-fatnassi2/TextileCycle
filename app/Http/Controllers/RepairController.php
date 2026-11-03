<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRepairRequestRequest;
use App\Models\RepairRequest;
use App\Models\User;
use App\Models\Workshop;
use App\Models\RepairPhoto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Services\AiQuota;
use App\Services\OpenAiClient;
use App\Exceptions\AssistantUnavailableException;
use App\Exceptions\AiQuotaExceededException;
use Illuminate\View\View;

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
        $data['item_category'] = app(\App\Services\RepairImpactCalculator::class)->inferCategory($data['item_category'] ?? null, $data['item_description']);
        $repairRequest = RepairRequest::create($data);
        $this->storePhotos($request, $repairRequest, 'photos', RepairPhoto::TYPE_BEFORE);
        return redirect()->route('repairs.index')->with('success', 'Votre demande a bien été envoyée à l’atelier.');
    }

    public function index(Request $request)
    {
        $repairRequests = $request->user()->repairRequests()->with(['workshop', 'photos'])->latest()->paginate(10);
        $highQuoteRepair = $request->user()->repairRequests()->with('workshop')
            ->where('status', RepairRequest::STATUS_QUOTE_PROPOSED)
            ->where('estimated_cost', '>=', config('ai.upcycle_cost_threshold'))
            ->latest()->first();
        return view('front.repairs.index', compact('repairRequests', 'highQuoteRepair'));
    }

    public function show(Request $request, RepairRequest $repairRequest)
    {
        abort_unless($repairRequest->user_id === $request->user()->id, 403);
        $repairRequest->load(['workshop', 'photos']);
        $messages = $request->user()->activities()->where('action', 'Message de l’atelier')->get()->filter(fn ($activity) => ($activity->metadata['repair_request_id'] ?? null) == $repairRequest->id);
        return view('front.repairs.show', compact('repairRequest', 'messages'));
    }

    public function certificate(Request $request, RepairRequest $repairRequest): View
    {
        abort_unless($repairRequest->user_id === $request->user()->id, 404);
        abort_unless(
            in_array($repairRequest->status, [
                RepairRequest::STATUS_READY_FOR_PICKUP,
                RepairRequest::STATUS_REPAIRED,
                RepairRequest::STATUS_COLLECTED,
            ], true) && $repairRequest->co2_saved_kg !== null && $repairRequest->waste_saved_kg !== null,
            404
        );

        $repairRequest->load('workshop');

        return view('front.repairs.certificate', compact('repairRequest'));
    }

    public function respondToQuote(Request $request, RepairRequest $repairRequest): RedirectResponse
    {
        abort_unless($repairRequest->user_id === $request->user()->id, 403);
        abort_unless($repairRequest->status === RepairRequest::STATUS_QUOTE_PROPOSED, 422);
        $decision = $request->validate(['decision' => 'required|in:accept,refuse'])['decision'];
        $repairRequest->update(['status' => $decision === 'accept' ? RepairRequest::STATUS_QUOTE_ACCEPTED : RepairRequest::STATUS_QUOTE_REFUSED]);
        User::logActivity(
            $request->user(),
            $decision === 'accept' ? 'Devis accepté' : 'Devis refusé',
            $decision === 'accept' ? 'Le citoyen a accepté le devis.' : 'Le citoyen a refusé le devis.',
            $request->user(),
            ['repair_request_id' => $repairRequest->id]
        );
        return back()->with('success', $decision === 'accept' ? 'Devis accepté. L’atelier peut commencer la réparation.' : 'Devis refusé. L’atelier en a été informé.');
    }

    public function confirmPickup(Request $request, RepairRequest $repairRequest): RedirectResponse
    {
        abort_unless($repairRequest->user_id === $request->user()->id, 404);
        abort_unless($repairRequest->status === RepairRequest::STATUS_READY_FOR_PICKUP, 422);

        $repairRequest->update(['status' => RepairRequest::STATUS_COLLECTED]);
        User::logActivity($request->user(), 'Réparation récupérée', 'Le citoyen a confirmé le retrait du vêtement.', $request->user(), ['repair_request_id' => $repairRequest->id]);

        return redirect()->route('repairs.show', $repairRequest)->with('success', 'Le retrait de votre réparation a été confirmé.');
    }

    public function alternatives(Request $request, RepairRequest $repairRequest, AiQuota $quota, OpenAiClient $openAi)
    {
        abort_unless($repairRequest->user_id === $request->user()->id, 404);
        $options = [
            ['id' => 'repair', 'title' => 'Accepter la réparation', 'url' => route('repairs.show', $repairRequest)],
            ['id' => 'upcycle', 'title' => 'Créer un objet avec cette pièce', 'url' => route('products.ideas')],
            ['id' => 'donate', 'title' => 'Donner la pièce', 'url' => route('deposits.index')],
        ];
        $key = $quota->cacheKey('upcycle', [$repairRequest->id, $repairRequest->status, $repairRequest->estimated_cost]);
        if ($cached = cache()->get($key)) return response()->json(['options' => $cached, 'cached' => true]);
        try {
            if (!config('ai.repair_enabled')) throw new AssistantUnavailableException();
            if (!config('services.openai.key')) throw new AssistantUnavailableException();
            $quota->consume('upcycle', $request->user(), $request->ip());
            $schema = ['type' => 'object', 'properties' => ['texts' => ['type' => 'array', 'items' => ['type' => 'string'], 'minItems' => 3, 'maxItems' => 3]], 'required' => ['texts'], 'additionalProperties' => false];
            $answer = $openAi->json([['role' => 'system', 'content' => 'Rédige trois options brèves en français, dans l’ordre réparation, upcycling, don. Ne crée aucun lien ni fait.'], ['role' => 'user', 'content' => json_encode(['cost' => $repairRequest->estimated_cost, 'problem' => $repairRequest->problem_type, 'options' => array_column($options, 'title')], JSON_UNESCAPED_UNICODE)]], 'repair_alternatives', $schema, config('ai.max_tokens.upcycle'));
            foreach ($options as $i => &$option) $option['text'] = mb_substr($answer['texts'][$i] ?? $option['title'], 0, 250);
            unset($option);
            cache()->put($key, $options, now()->addDay());
            return response()->json(['options' => $options, 'cached' => false]);
        } catch (AiQuotaExceededException $e) {
            foreach ($options as &$option) $option['text'] = $option['title'];
            return response()->json(['options' => $options, 'fallback' => true, 'remaining' => $e->remaining], 429);
        } catch (AssistantUnavailableException) {
            foreach ($options as &$option) $option['text'] = $option['title'];
            return response()->json(['options' => $options, 'fallback' => true]);
        }
    }

    private function storePhotos(Request $request, RepairRequest $repairRequest, string $field, string $type): void
    {
        foreach ($request->file($field, []) as $photo) {
            $repairRequest->photos()->create(['path' => $photo->store('repair-photos', 'public'), 'type' => $type]);
        }
    }
}
