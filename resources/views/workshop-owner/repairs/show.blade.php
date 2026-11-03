@extends('layouts.workshop-owner')

@section('title', 'Dossier de réparation')
@section('content')
    <a href="{{ route('workshop-owner.repairs.index') }}" class="text-sm font-medium text-emerald-800 underline">← Toutes les demandes</a>
    <section class="mt-4 border border-slate-200 bg-white p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-sm text-slate-500">{{ $repairRequest->user?->name }} · {{ $repairRequest->user?->email }}</p>
                <h1 class="mt-1 text-2xl font-bold">{{ $repairRequest->item_description }}</h1>
                <p class="mt-2 text-sm text-slate-600">{{ $repairRequest->problem_type }}</p>
            </div>
            <span class="border border-emerald-200 bg-emerald-50 px-3 py-1 text-sm font-semibold text-emerald-900">{{ $repairRequest->status }}</span>
        </div>

        <dl class="mt-6 grid gap-4 border-t border-slate-100 pt-5 text-sm sm:grid-cols-3">
            <div><dt class="text-slate-500">Devis proposé</dt><dd class="mt-1 font-medium">{{ $repairRequest->estimated_cost ? $repairRequest->estimated_cost.' TND' : 'En attente' }}</dd></div>
            <div><dt class="text-slate-500">Coût final</dt><dd class="mt-1 font-medium">{{ $repairRequest->cost ? $repairRequest->cost.' TND' : '—' }}</dd></div>
            <div><dt class="text-slate-500">Fin estimée</dt><dd class="mt-1 font-medium">{{ $repairRequest->estimated_completion_date?->format('d/m/Y') ?? 'À confirmer' }}</dd></div>
            <div><dt class="text-slate-500">Statut</dt><dd class="mt-1 font-medium">{{ $repairRequest->status }}</dd></div>
        </dl>
    </section>

    @if($delayRisk)
        <section class="mt-5 overflow-hidden rounded-2xl border-2 {{ $delayRisk['risk'] === 'high' ? 'border-rose-300 bg-rose-50' : ($delayRisk['risk'] === 'medium' ? 'border-orange-300 bg-orange-50' : 'border-emerald-300 bg-emerald-50') }} shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 p-5">
                <div><p class="text-xs font-bold uppercase tracking-wider text-slate-600">Prévision automatique · sans appel IA</p><h2 class="mt-1 text-lg font-bold">Risque de retard</h2><p class="mt-1 text-sm text-slate-700">{{ $delayRisk['reason'] }}</p></div>
                <span class="rounded-xl px-4 py-2 text-sm font-black {{ $delayRisk['risk'] === 'high' ? 'bg-rose-200 text-rose-950' : ($delayRisk['risk'] === 'medium' ? 'bg-orange-200 text-orange-950' : 'bg-emerald-200 text-emerald-950') }}">{{ $delayRisk['risk'] === 'high' ? '🔴 Risque élevé' : ($delayRisk['risk'] === 'medium' ? '🟠 Risque modéré' : '🟢 Risque faible') }}</span>
            </div>
            <form method="POST" action="{{ route('workshop-owner.repairs.estimate', $repairRequest) }}" class="grid gap-3 border-t border-black/5 bg-white/75 p-5 sm:grid-cols-[1fr_auto] sm:items-end">
                @csrf @method('PATCH')
                <label class="text-sm font-semibold text-slate-800">Ajuster la date estimée pour recalculer le risque
                    <input type="date" name="estimated_completion_date" value="{{ old('estimated_completion_date', $repairRequest->estimated_completion_date?->toDateString()) }}" required class="mt-1 block min-h-11 w-full rounded-lg border border-slate-300 px-3 sm:max-w-xs">
                </label>
                <button class="min-h-11 rounded-lg bg-slate-900 px-4 text-sm font-bold text-white transition hover:bg-violet-800">Mettre à jour le risque</button>
            </form>
        </section>
    @endif

    @if($repairRequest->photos->contains('type', \App\Models\RepairPhoto::TYPE_BEFORE) && $repairRequest->photos->contains('type', \App\Models\RepairPhoto::TYPE_AFTER))
        <section class="quality-ai-card relative mt-6 overflow-hidden rounded-2xl border border-amber-300 bg-gradient-to-br from-amber-50 via-white to-violet-50 p-6 shadow-lg sm:p-8">
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-amber-400 via-orange-500 to-violet-500"></div>
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[.18em] text-violet-800">✦ Analyse visuelle par IA</p>
                    <h2 class="mt-2 text-xl font-bold text-slate-900 sm:text-2xl">Comparer l’avant et l’après</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">L’IA examine les photos et propose une note ainsi qu’une observation. Vous pouvez tout vérifier et modifier avant publication.</p>
                </div>
                <span class="rounded-full border border-violet-200 bg-white/80 px-3 py-1.5 text-xs font-semibold text-violet-800 shadow-sm">OpenAI · Vision</span>
            </div>

            <div class="mt-5 grid gap-3 sm:grid-cols-3">
                <div class="rounded-xl border border-white bg-white/80 p-3 shadow-sm"><span class="text-xs font-bold text-amber-700">ÉTAPE 1</span><p class="mt-1 text-sm font-medium text-slate-800">Photos Avant + Après</p></div>
                <div class="rounded-xl border border-white bg-white/80 p-3 shadow-sm"><span class="text-xs font-bold text-violet-700">ÉTAPE 2</span><p class="mt-1 text-sm font-medium text-slate-800">L’IA compare les images</p></div>
                <div class="rounded-xl border border-white bg-white/80 p-3 shadow-sm"><span class="text-xs font-bold text-emerald-700">ÉTAPE 3</span><p class="mt-1 text-sm font-medium text-slate-800">Vous validez ou modifiez</p></div>
            </div>

            <div class="mt-5 flex flex-wrap items-center gap-3">
                <button type="button" id="analyze-quality" class="quality-ai-button inline-flex min-h-12 items-center gap-2 rounded-xl px-5 py-3 text-sm font-bold text-white shadow-md focus:outline-none focus:ring-4 focus:ring-violet-200 disabled:cursor-wait disabled:opacity-75" aria-busy="false">
                    <span data-quality-icon aria-hidden="true">✦</span>
                    <span data-quality-label>Analyser les photos avec l’IA</span>
                </button>
                <p class="text-xs text-slate-600">Avis automatique indicatif, sans garantie sur la solidité.</p>
            </div>
            <p id="quality-status" class="mt-3 min-h-5 text-sm font-medium text-violet-900" aria-live="polite"></p>

            <div id="quality-result" hidden class="mt-4 rounded-2xl border border-violet-200 bg-white p-4 shadow-md sm:p-5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div><p id="quality-result-kind" class="text-xs font-bold uppercase tracking-wide text-violet-800">Résultat de l’analyse</p><p class="mt-1 text-sm text-slate-500">Note proposée</p></div>
                    <div class="flex items-center gap-2"><span id="quality-stars" class="text-xl tracking-wide text-amber-500" aria-hidden="true"></span><span class="sr-only" id="quality-stars-label"></span><span class="text-sm font-bold text-slate-800"><span id="quality-score-preview">—</span>/5</span></div>
                </div>
                <p id="quality-note-preview" class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-700"></p>
                <div id="quality-defects-wrap" hidden class="mt-3 border-t border-slate-100 pt-3"><p class="text-xs font-bold uppercase tracking-wide text-slate-500">Éléments visibles signalés</p><ul id="quality-defects" class="mt-1 list-inside list-disc text-sm text-slate-700"></ul></div>
            </div>

            <form method="POST" action="{{ route('workshop-owner.repairs.quality.save', $repairRequest) }}" class="mt-5 grid gap-4 rounded-xl border border-slate-200 bg-white/80 p-4 sm:grid-cols-[8rem_1fr]">@csrf @method('PUT')
                <label class="text-sm font-semibold text-slate-700">Votre note / 5<input name="quality_score" id="quality-score" type="number" min="1" max="5" required value="{{ old('quality_score', $repairRequest->quality_score) }}" class="mt-1 w-full rounded-lg border border-slate-300 p-2 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-100"></label>
                <label class="text-sm font-semibold text-slate-700">Observation<textarea name="quality_note" id="quality-note" maxlength="300" required rows="3" class="mt-1 w-full rounded-lg border border-slate-300 p-3 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-100">{{ old('quality_note', $repairRequest->quality_note) }}</textarea></label>
                <button class="sm:col-span-2 justify-self-start rounded-xl border border-emerald-800 px-5 py-2.5 text-sm font-bold text-emerald-900 transition hover:bg-emerald-800 hover:text-white">Enregistrer et publier au citoyen</button>
            </form>
        </section>
        <style>
            .quality-ai-card { animation: quality-card-in .45s ease-out both; }
            .quality-ai-button { background: linear-gradient(110deg, #6d28d9, #7c3aed 45%, #d97706); background-size: 180% 100%; transition: transform .2s ease, box-shadow .2s ease, background-position .35s ease; }
            .quality-ai-button:hover:not(:disabled) { transform: translateY(-2px); background-position: 100% 0; box-shadow: 0 10px 24px rgb(109 40 217 / 25%); }
            .quality-ai-button[data-loading="true"] [data-quality-icon] { animation: quality-spin 1s linear infinite; }
            @keyframes quality-card-in { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
            @keyframes quality-spin { to { transform: rotate(360deg); } }
            @media (prefers-reduced-motion: reduce) { .quality-ai-card { animation: none; } .quality-ai-button, .quality-ai-button[data-loading="true"] [data-quality-icon] { transition: none; animation: none; } }
        </style>
        <script>
        (() => {
            const button = document.getElementById('analyze-quality');
            const score = document.getElementById('quality-score');
            const note = document.getElementById('quality-note');
            const result = document.getElementById('quality-result');
            const setResult = (data, source = 'ai') => {
                if (!data?.score) return;
                score.value = data.score;
                note.value = data.note || '';
                document.getElementById('quality-score-preview').textContent = data.score;
                document.getElementById('quality-stars').textContent = '★'.repeat(data.score) + '☆'.repeat(5 - data.score);
                document.getElementById('quality-stars-label').textContent = `Note proposée : ${data.score} sur 5`;
                document.getElementById('quality-note-preview').textContent = data.note || '';
                document.getElementById('quality-result-kind').textContent = source === 'saved' ? 'Avis déjà enregistré' : (source === 'edited' ? 'Aperçu de votre avis modifié' : (data.fallback ? 'Suggestion automatique de secours' : 'Résultat proposé par l’IA'));
                const defects = document.getElementById('quality-defects');
                defects.replaceChildren();
                (data.visible_defects || []).forEach((defect) => { const item = document.createElement('li'); item.textContent = defect; defects.appendChild(item); });
                document.getElementById('quality-defects-wrap').hidden = !data.visible_defects?.length;
                result.hidden = false;
                result.scrollIntoView({ behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'nearest' });
            };
            const refreshPreview = () => {
                if (score.value && note.value) setResult({ score: Number(score.value), note: note.value, visible_defects: [] }, 'edited');
            };
            score.addEventListener('input', refreshPreview);
            note.addEventListener('input', refreshPreview);
            button.addEventListener('click', async function () {
                this.disabled = true;
                this.dataset.loading = 'true';
                this.setAttribute('aria-busy', 'true');
                this.querySelector('[data-quality-label]').textContent = 'Comparaison des images…';
                document.getElementById('quality-status').textContent = 'Les photos Avant et Après sont envoyées pour analyse. Cela peut prendre quelques secondes.';
                try {
                    const response = await window.axios.post(@json(route('workshop-owner.repairs.quality.analyze', $repairRequest)));
                    setResult(response.data, response.data.fallback ? 'fallback' : 'ai');
                    document.getElementById('quality-status').textContent = response.data.fallback ? 'Mode de secours utilisé. Vérifiez la suggestion avant de la publier.' : 'Analyse IA terminée. Vérifiez ou modifiez la suggestion avant de la publier.';
                } catch (error) {
                    const data = error.response?.data;
                    if (data?.score) setResult(data, 'fallback');
                    document.getElementById('quality-status').textContent = data?.message || (data?.fallback ? 'Quota atteint : suggestion de secours affichée.' : 'Analyse indisponible. Réessayez plus tard.');
                } finally {
                    this.disabled = false;
                    this.dataset.loading = 'false';
                    this.setAttribute('aria-busy', 'false');
                    this.querySelector('[data-quality-label]').textContent = 'Analyser de nouveau';
                }
            });
            if (score.value && note.value) setResult({ score: Number(score.value), note: note.value, visible_defects: [] }, 'saved');
        })();
        </script>
    @endif

    <section class="mt-5 border border-slate-200 bg-white p-6">
        <h2 class="font-semibold">Message au citoyen</h2>
        <label class="mt-3 block text-sm">Langue <select id="message-language" class="ml-2 border px-2 py-1"><option value="fr">Français</option><option value="ar">العربية</option><option value="en">English</option></select></label>
        <textarea id="message-draft" dir="auto" maxlength="1000" rows="4" class="mt-3 w-full border border-slate-300 p-3"></textarea>
        <p id="message-status" class="mt-1 text-sm text-slate-600" aria-live="polite"></p>
        <button type="button" id="generate-message" class="mt-2 bg-emerald-800 px-4 py-2 text-sm text-white">Générer un message</button>
        <form method="POST" action="{{ route('workshop-owner.repairs.message', $repairRequest) }}" class="mt-3">@csrf<input type="hidden" name="language" id="message-language-hidden" value="fr"><textarea name="message" id="message-to-send" hidden></textarea><button class="border border-emerald-800 px-4 py-2 text-sm text-emerald-900">Envoyer au citoyen</button></form>
    </section>
    <script>
        document.getElementById('generate-message').addEventListener('click', async function () {
            const button = this, language = document.getElementById('message-language').value;
            button.disabled = true; document.getElementById('message-status').textContent = 'Génération…';
            try { const response = await window.axios.post(@json(route('workshop-owner.repairs.message.draft', $repairRequest)), {language}); const draft = response.data.draft; document.getElementById('message-draft').value = draft; document.getElementById('message-to-send').value = draft; document.getElementById('message-language-hidden').value = language; document.getElementById('message-status').textContent = response.data.message || 'Vous pouvez modifier le message avant envoi.'; }
            catch (error) { const data = error.response?.data; if (data?.draft) { document.getElementById('message-draft').value = data.draft; document.getElementById('message-to-send').value = data.draft; document.getElementById('message-language-hidden').value = language; } document.getElementById('message-status').textContent = data?.message || 'Génération indisponible.'; }
            finally { button.disabled = false; }
        });
        document.getElementById('message-draft').addEventListener('input', function () { document.getElementById('message-to-send').value = this.value; });
        document.getElementById('message-language').addEventListener('change', function () { document.getElementById('message-language-hidden').value = this.value; });
    </script>

    @if(in_array($repairRequest->status, [\App\Models\RepairRequest::STATUS_PENDING, \App\Models\RepairRequest::STATUS_QUOTE_REFUSED], true))
        <section class="mt-5 border border-slate-200 bg-white p-6">
            <h2 class="font-semibold">Proposer un devis</h2>
            <form method="POST" action="{{ route('workshop-owner.repairs.quote', $repairRequest) }}" class="mt-4 grid gap-4 sm:grid-cols-2">
                @csrf
                @method('PUT')
                <label class="text-sm font-medium">Montant (TND)
                    <input type="number" name="estimated_cost" min="0" max="100000" step="0.01" value="{{ old('estimated_cost', $repairRequest->estimated_cost) }}" required class="mt-1 block min-h-10 w-full border border-slate-300 px-3">
                    @error('estimated_cost')<span class="mt-1 block text-rose-700">{{ $message }}</span>@enderror
                </label>
                <label class="text-sm font-medium">Date estimée
                    <input type="date" name="estimated_completion_date" min="{{ now()->toDateString() }}" value="{{ old('estimated_completion_date', $repairRequest->estimated_completion_date?->toDateString()) }}" required class="mt-1 block min-h-10 w-full border border-slate-300 px-3">
                    @error('estimated_completion_date')<span class="mt-1 block text-rose-700">{{ $message }}</span>@enderror
                </label>
                <div class="sm:col-span-2"><button class="min-h-10 bg-emerald-800 px-4 text-sm font-semibold text-white hover:bg-emerald-900">Envoyer le devis au citoyen</button></div>
            </form>
        </section>
    @elseif($repairRequest->status === \App\Models\RepairRequest::STATUS_QUOTE_ACCEPTED)
        <form method="POST" action="{{ route('workshop-owner.repairs.status', $repairRequest) }}" class="mt-5 border border-emerald-200 bg-emerald-50 p-5">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="{{ \App\Models\RepairRequest::STATUS_IN_PROGRESS }}">
            <p class="mb-3 text-sm text-emerald-950">Le citoyen a accepté le devis. Vous pouvez commencer le travail.</p>
            <button class="min-h-10 bg-emerald-800 px-4 text-sm font-semibold text-white">Démarrer la réparation</button>
        </form>
    @elseif($repairRequest->status === \App\Models\RepairRequest::STATUS_IN_PROGRESS)
        <form method="POST" action="{{ route('workshop-owner.repairs.status', $repairRequest) }}" enctype="multipart/form-data" class="mt-5 border border-slate-200 bg-white p-6">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="{{ \App\Models\RepairRequest::STATUS_READY_FOR_PICKUP }}">
            <h2 class="font-semibold">Terminer la réparation</h2>
            <label class="mt-3 block text-sm font-medium">Coût final (TND)
                <input type="number" name="cost" min="0" max="100000" step="0.01" value="{{ old('cost', $repairRequest->estimated_cost) }}" required class="mt-1 block min-h-10 w-full max-w-xs border border-slate-300 px-3">
                @error('cost')<span class="mt-1 block text-rose-700">{{ $message }}</span>@enderror
            </label>
            <label class="mt-3 block text-sm font-medium">Photos après réparation (facultatif, 3 maximum)
                <input type="file" name="after_photos[]" accept="image/jpeg,image/png,image/webp" multiple class="mt-1 block w-full text-sm">
            </label>
            @error('after_photos')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
            <button class="mt-4 min-h-10 bg-emerald-800 px-4 text-sm font-semibold text-white">Marquer prête au retrait</button>
        </form>
    @endif

    @if($repairRequest->photos->isNotEmpty())
        <section class="mt-5 border border-slate-200 bg-white p-6">
            <h2 class="font-semibold">Photos de la demande</h2>
            <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach($repairRequest->photos as $photo)
                    <a href="{{ route('workshop-owner.repairs.photos.show', [$repairRequest, $photo]) }}" target="_blank" rel="noreferrer">
                        <img src="{{ route('workshop-owner.repairs.photos.show', [$repairRequest, $photo]) }}" alt="Photo {{ strtolower($photo->type) }}" class="aspect-square w-full object-cover">
                        <span class="mt-1 block text-xs text-slate-500">{{ $photo->type }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
@endsection
