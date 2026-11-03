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
use App\Services\AiQuota;
use App\Services\OpenAiClient;
use App\Exceptions\AssistantUnavailableException;
use App\Exceptions\AiQuotaExceededException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

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
        $activeRepairs = RepairRequest::whereIn('workshop_id', $workshopIds)->whereIn('status', [RepairRequest::STATUS_QUOTE_ACCEPTED, RepairRequest::STATUS_IN_PROGRESS])->with('workshop')->get();
        $delayRisks = app(\App\Services\DelayRiskPredictor::class)->assess($activeRepairs);
        $atRiskCount = collect($delayRisks)->whereIn('risk', ['medium', 'high'])->count();
        $riskRepairs = $activeRepairs->filter(fn ($repair) => in_array($delayRisks[$repair->id]['risk'] ?? 'low', ['medium', 'high'], true))
            ->sortByDesc(fn ($repair) => $delayRisks[$repair->id]['risk'] === 'high' ? 2 : 1)
            ->take(5)->values();
        $impact = RepairRequest::whereIn('workshop_id', $workshopIds)->whereNotNull('co2_saved_kg')->selectRaw('count(*) as repairs, sum(co2_saved_kg) as co2, sum(waste_saved_kg) as waste')->first();
        $aiUsage = \App\Models\AiUsageEvent::query()
            ->where('user_id', $request->user()->id)
            ->whereIn('feature', ['repair_assistant', 'repair_message', 'repair_alternatives', 'repair_quality'])
            ->whereDate('created_at', today())
            ->selectRaw('count(*) as calls, sum(input_tokens) as input_tokens, sum(output_tokens) as output_tokens')
            ->first();
        $completedInPeriod = RepairRequest::query()
            ->whereIn('workshop_id', $workshopIds)
            ->whereIn('status', [
                RepairRequest::STATUS_READY_FOR_PICKUP,
                RepairRequest::STATUS_REPAIRED,
                RepairRequest::STATUS_COLLECTED,
            ])
            ->where('ready_at', '>=', now()->subDays(30))
            ->get(['started_at', 'ready_at', 'estimated_completion_date', 'quality_score']);
        $completedWithDates = $completedInPeriod->filter(fn (RepairRequest $repair) => $repair->started_at && $repair->ready_at);
        $onTimeRepairs = $completedInPeriod->filter(fn (RepairRequest $repair) => $repair->estimated_completion_date
            && $repair->ready_at->toDateString() <= $repair->estimated_completion_date->toDateString());
        $qualityScores = $completedInPeriod->pluck('quality_score')->filter();
        $repairPerformance = [
            'completed' => $completedInPeriod->count(),
            'on_time_percentage' => $completedInPeriod->whereNotNull('estimated_completion_date')->isEmpty()
                ? null
                : (int) round($onTimeRepairs->count() / $completedInPeriod->filter(fn (RepairRequest $repair) => $repair->estimated_completion_date)->count() * 100),
            'average_days' => $completedWithDates->isEmpty()
                ? null
                : round($completedWithDates->avg(fn (RepairRequest $repair) => $repair->started_at->diffInHours($repair->ready_at) / 24), 1),
            'average_quality' => $qualityScores->isEmpty() ? null : round($qualityScores->avg(), 1),
        ];

        return view('workshop-owner.dashboard', compact('workshops', 'statusCounts', 'recentRepairs', 'delayRisks', 'atRiskCount', 'riskRepairs', 'impact', 'aiUsage', 'repairPerformance'));
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
        $delayRisks = app(\App\Services\DelayRiskPredictor::class)->assess($repairRequests->getCollection()->filter(fn ($r) => in_array($r->status, [RepairRequest::STATUS_QUOTE_ACCEPTED, RepairRequest::STATUS_IN_PROGRESS], true)));

        return view('workshop-owner.repairs.index', compact('workshops', 'repairRequests', 'delayRisks'));
    }

    public function show(Request $request, RepairRequest $repairRequest): View
    {
        $this->assertWorkshopOwnership($request, $repairRequest);
        $repairRequest->load(['user', 'photos', 'workshop']);
        $delayRisk = in_array($repairRequest->status, [RepairRequest::STATUS_QUOTE_ACCEPTED, RepairRequest::STATUS_IN_PROGRESS], true)
            ? (app(\App\Services\DelayRiskPredictor::class)->assess(collect([$repairRequest]))[$repairRequest->id] ?? null)
            : null;

        return view('workshop-owner.repairs.show', compact('repairRequest', 'delayRisk'));
    }

    public function updateEstimatedDate(Request $request, RepairRequest $repairRequest): RedirectResponse
    {
        $this->assertWorkshopOwnership($request, $repairRequest);
        abort_unless(in_array($repairRequest->status, [RepairRequest::STATUS_QUOTE_ACCEPTED, RepairRequest::STATUS_IN_PROGRESS], true), 422);
        $data = $request->validate(['estimated_completion_date' => ['required', 'date']]);
        $repairRequest->update($data);
        $this->logRepairActivity($repairRequest, 'Date estimée mise à jour', 'La date estimée de fin a été modifiée par l’atelier.', $request->user());

        return back()->with('success', 'La date estimée a été mise à jour. Le niveau de risque est recalculé automatiquement.');
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

        $fields = array_filter([
            'status' => $data['status'],
            'cost' => $data['cost'] ?? null,
        ], fn ($value) => $value !== null);
        if ($data['status'] === RepairRequest::STATUS_IN_PROGRESS && !$repairRequest->started_at) $fields['started_at'] = now();
        if ($data['status'] === RepairRequest::STATUS_READY_FOR_PICKUP) $fields['ready_at'] = now();
        $repairRequest->update($fields);
        if ($repairRequest->status === RepairRequest::STATUS_READY_FOR_PICKUP) app(\App\Services\RepairImpactCalculator::class)->calculate($repairRequest);
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

    public function messageDraft(Request $request, RepairRequest $repairRequest, AiQuota $quota, OpenAiClient $openAi): JsonResponse
    {
        $this->assertWorkshopOwnership($request, $repairRequest);
        $language = $request->validate(['language' => ['required', Rule::in(['fr','ar','en'])]])['language'];
        $key = $quota->cacheKey('message', [$repairRequest->id, $repairRequest->status, $repairRequest->estimated_cost, $repairRequest->estimated_completion_date?->toDateString(), $language]);
        if ($cached = cache()->get($key)) return response()->json(['draft' => $cached, 'fallback' => false, 'cached' => true]);
        $name = explode(' ', trim($repairRequest->user?->name ?? ''))[0] ?? '';
        $facts = ['first_name' => $name, 'status' => $repairRequest->status, 'quote_tnd' => $repairRequest->estimated_cost, 'date' => $repairRequest->estimated_completion_date?->format('d/m/Y'), 'workshop' => $repairRequest->workshop?->name];
        $templates = [
            'fr' => "Bonjour {$name}, votre réparation est actuellement « {$facts['status']} ». ".($facts['quote_tnd'] ? "Devis : {$facts['quote_tnd']} TND. " : '').($facts['date'] ? "Date estimée : {$facts['date']}. " : '').'Nous restons à votre disposition. '.$facts['workshop'],
            'en' => "Hello {$name}, your repair is currently: {$facts['status']}. ".($facts['quote_tnd'] ? "Quote: {$facts['quote_tnd']} TND. " : '').($facts['date'] ? "Estimated date: {$facts['date']}. " : '').'Please contact us if needed. '.$facts['workshop'],
            'ar' => "مرحباً {$name}، حالة الإصلاح: {$facts['status']}. ".($facts['quote_tnd'] ? "التكلفة التقديرية: {$facts['quote_tnd']} دينار. " : '').($facts['date'] ? "التاريخ المتوقع: {$facts['date']}. " : '').'نحن في خدمتكم. '.$facts['workshop'],
        ];
        try {
            if (!config('ai.repair_enabled')) throw new AssistantUnavailableException();
            if (!config('services.openai.key')) throw new AssistantUnavailableException();
            $quota->consume('message', $request->user(), $request->ip());
            $response = $openAi->chat(['max_completion_tokens' => config('ai.max_tokens.message'), '_usage_feature' => 'repair_message', 'messages' => [['role' => 'system', 'content' => 'Rédige un message poli et court dans la langue demandée (maximum 70 mots). Utilise uniquement les faits fournis, sans inventer prix ou dates.'], ['role' => 'user', 'content' => json_encode(['language' => $language, 'facts' => $facts], JSON_UNESCAPED_UNICODE)]]]);
            $draft = trim((string) ($response['content'] ?? ''));
            cache()->put($key, $draft, now()->addDay());
            return response()->json(['draft' => $draft, 'fallback' => false]);
        } catch (AiQuotaExceededException $e) {
            return response()->json(['draft' => $templates[$language], 'fallback' => true, 'message' => 'Modèle automatique utilisé.', 'remaining' => $e->remaining], 429);
        } catch (AssistantUnavailableException) {
            return response()->json(['draft' => $templates[$language], 'fallback' => true, 'message' => 'Modèle automatique utilisé.']);
        }
    }

    public function sendMessage(Request $request, RepairRequest $repairRequest): RedirectResponse
    {
        $this->assertWorkshopOwnership($request, $repairRequest);
        $data = $request->validate(['message' => ['required', 'string', 'max:1000'], 'language' => ['required', Rule::in(['fr','ar','en'])]]);
        $message = strip_tags($data['message']);
        if ($repairRequest->user) User::logActivity($repairRequest->user, 'Message de l’atelier', $message, $request->user(), ['repair_request_id' => $repairRequest->id, 'language' => $data['language']]);
        return back()->with('success', 'Le message a été envoyé au citoyen.');
    }

    public function analyzeQuality(Request $request, RepairRequest $repairRequest, AiQuota $quota, OpenAiClient $openAi): JsonResponse
    {
        $this->assertWorkshopOwnership($request, $repairRequest);
        $photos = $repairRequest->photos()->whereIn('type', [RepairPhoto::TYPE_BEFORE, RepairPhoto::TYPE_AFTER])->get()->groupBy('type');
        if (($photos[RepairPhoto::TYPE_BEFORE] ?? collect())->isEmpty() || ($photos[RepairPhoto::TYPE_AFTER] ?? collect())->isEmpty()) return response()->json(['message' => 'Ajoutez au moins une photo avant et une photo après.'], 422);
        $key = $quota->cacheKey('quality', [$repairRequest->id, $repairRequest->photos->pluck('id')->all(), $repairRequest->problem_type]);
        if ($cached = cache()->get($key)) return response()->json($cached + ['cached' => true]);
        $fallback = ['score' => 3, 'note' => 'Avis automatique indisponible. Vérifiez vous-même les finitions avant publication.', 'visible_defects' => []];
        try {
            if (!config('ai.repair_enabled')) throw new AssistantUnavailableException();
            if (!config('services.openai.key')) throw new AssistantUnavailableException();
            $quota->consume('quality', $request->user(), $request->ip());
            $parts = [['type' => 'text', 'text' => 'Comparez les photos avant/après. Évaluez uniquement les éléments visibles, sans garantir la solidité. Problème : '.mb_substr($repairRequest->problem_type.' — '.$repairRequest->item_description, 0, 500)]];
            foreach ([RepairPhoto::TYPE_BEFORE, RepairPhoto::TYPE_AFTER] as $type) foreach (($photos[$type] ?? collect())->take(2) as $photo) {
                $path = Storage::disk('public')->path($photo->path);
                if (!is_file($path)) continue;
                $bytes = file_get_contents($path);
                $mime = mime_content_type($path) ?: 'image/jpeg';
                if (extension_loaded('gd')) {
                    $source = @imagecreatefromstring($bytes);
                    if ($source) { $width = imagesx($source); $height = imagesy($source); $scale = min(1, 768 / max($width, $height)); $image = imagecreatetruecolor((int) ($width * $scale), (int) ($height * $scale)); imagecopyresampled($image, $source, 0, 0, 0, 0, imagesx($image), imagesy($image), $width, $height); ob_start(); imagejpeg($image, null, 75); $bytes = ob_get_clean(); $mime = 'image/jpeg'; imagedestroy($source); imagedestroy($image); }
                } elseif (strlen($bytes) > 1048576) continue;
                $parts[] = ['type' => 'image_url', 'image_url' => ['url' => 'data:'.$mime.';base64,'.base64_encode($bytes), 'detail' => 'low']];
            }
            if (count($parts) < 3) throw new AssistantUnavailableException();
            $schema = ['type' => 'object', 'properties' => ['score' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 5], 'note' => ['type' => 'string'], 'visible_defects' => ['type' => 'array', 'items' => ['type' => 'string']]], 'required' => ['score', 'note', 'visible_defects'], 'additionalProperties' => false];
            $result = $openAi->json([['role' => 'system', 'content' => 'Réponds selon le schéma. En français, note automatique et prudente, note courte (300 caractères maximum).'], ['role' => 'user', 'content' => $parts]], 'repair_quality', $schema, config('ai.max_tokens.quality'));
            $result = ['score' => max(1, min(5, (int) $result['score'])), 'note' => mb_substr(strip_tags((string) $result['note']), 0, 300), 'visible_defects' => array_slice(array_map(fn ($v) => mb_substr(strip_tags((string) $v), 0, 100), $result['visible_defects']), 0, 5)];
            cache()->put($key, $result, now()->addDay());
            return response()->json($result + ['fallback' => false]);
        } catch (AiQuotaExceededException $e) {
            return response()->json($fallback + ['fallback' => true, 'remaining' => $e->remaining], 429);
        } catch (AssistantUnavailableException) {
            return response()->json($fallback + ['fallback' => true]);
        }
    }

    public function saveQuality(Request $request, RepairRequest $repairRequest): RedirectResponse
    {
        $this->assertWorkshopOwnership($request, $repairRequest);
        $data = $request->validate(['quality_score' => ['required', 'integer', 'between:1,5'], 'quality_note' => ['required', 'string', 'max:300']]);
        $repairRequest->update([...$data, 'quality_note' => strip_tags($data['quality_note']), 'quality_checked_at' => now()]);
        return back()->with('success', 'L’avis qualité a été enregistré.');
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
