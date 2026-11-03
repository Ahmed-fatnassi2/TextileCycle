@extends('layouts.front')
@section('title', 'Suivi de réparation')
@section('content')
<section class="mx-auto max-w-4xl px-4 py-12">
	<a href="{{ route('repairs.index') }}" class="text-sm text-emerald-700">← Mes réparations</a>
	<div class="mt-4 rounded-xl bg-white p-6 shadow">
		<div class="flex flex-wrap justify-between gap-3">
			<div>
				<h1 class="text-2xl font-bold">{{ $repairRequest->workshop->name }}</h1>
				<p class="mt-1 text-gray-600">{{ $repairRequest->item_description }}</p>
			</div>
			<span class="h-fit rounded bg-emerald-100 px-3 py-1 text-sm font-semibold text-emerald-800">{{ $repairRequest->status }}</span>
		</div>
		<dl class="mt-6 grid gap-4 text-sm sm:grid-cols-3">
			<div><dt class="text-gray-500">Problème</dt><dd>{{ $repairRequest->problem_type }}</dd></div>
			<div><dt class="text-gray-500">Devis</dt><dd>{{ $repairRequest->estimated_cost ? $repairRequest->estimated_cost.' TND' : 'En attente' }}</dd></div>
			<div><dt class="text-gray-500">Fin estimée</dt><dd>{{ $repairRequest->estimated_completion_date?->format('d/m/Y') ?? 'À confirmer' }}</dd></div>
		</dl>

		@if($repairRequest->status === \App\Models\RepairRequest::STATUS_QUOTE_PROPOSED)
			<div id="quote-actions" class="mt-6 rounded-lg bg-amber-50 p-4">
				<p class="font-medium text-amber-900">Votre atelier vous a proposé un devis.</p>
				<form method="POST" action="{{ route('repairs.quote', $repairRequest) }}" class="mt-3 flex flex-wrap gap-3">
					@csrf
					<button name="decision" value="accept" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm text-white">Accepter le devis</button>
					<button name="decision" value="refuse" class="rounded-lg border border-red-300 px-4 py-2 text-sm text-red-700">Refuser</button>
				</form>
			</div>
		@endif

		@if($repairRequest->status === \App\Models\RepairRequest::STATUS_READY_FOR_PICKUP)
			<div class="mt-6 rounded-lg bg-emerald-50 p-4">
				<p class="font-medium text-emerald-900">Votre réparation est prête à être récupérée.</p>
				<form method="POST" action="{{ route('repairs.pickup', $repairRequest) }}" class="mt-3">
					@csrf
					<button class="min-h-10 bg-emerald-800 px-4 text-sm font-semibold text-white hover:bg-emerald-900">Confirmer le retrait</button>
				</form>
			</div>
		@endif
		@if(in_array($repairRequest->status, [\App\Models\RepairRequest::STATUS_READY_FOR_PICKUP, \App\Models\RepairRequest::STATUS_REPAIRED, \App\Models\RepairRequest::STATUS_COLLECTED], true) && $repairRequest->co2_saved_kg !== null && $repairRequest->waste_saved_kg !== null)
			<div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4">
				<h2 class="font-semibold text-emerald-950">Votre certificat de réparation responsable est disponible.</h2>
				<p class="mt-1 text-sm text-emerald-900">Il récapitule la réparation et son impact environnemental estimé à titre indicatif.</p>
				<a href="{{ route('repairs.certificate', $repairRequest) }}" target="_blank" rel="noreferrer" class="mt-3 inline-flex min-h-10 items-center rounded bg-emerald-800 px-4 text-sm font-semibold text-white hover:bg-emerald-900">Voir ou imprimer le certificat</a>
			</div>
		@endif
	</div>
	@if($repairRequest->quality_score && in_array($repairRequest->status, [\App\Models\RepairRequest::STATUS_READY_FOR_PICKUP, \App\Models\RepairRequest::STATUS_REPAIRED, \App\Models\RepairRequest::STATUS_COLLECTED], true))
		<div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-5"><h2 class="font-bold">Avis automatique sur la qualité : {{ $repairRequest->quality_score }}/5</h2><p class="mt-2">{{ $repairRequest->quality_note }}</p><p class="mt-2 text-xs text-amber-900">Cet avis automatique est indicatif et ne constitue pas une garantie.</p></div>
	@endif
	@if($repairRequest->status === \App\Models\RepairRequest::STATUS_QUOTE_PROPOSED && $repairRequest->estimated_cost >= config('ai.upcycle_cost_threshold'))
		<div class="upcycle-demo-card mt-6 overflow-hidden rounded-2xl border-2 border-amber-300 bg-gradient-to-br from-amber-50 via-orange-50 to-white shadow-lg">
			<div class="h-1.5 bg-gradient-to-r from-amber-400 via-orange-500 to-emerald-500"></div>
			<div class="p-5 sm:p-7">
				<div class="flex flex-wrap items-start justify-between gap-4"><div><p class="text-xs font-bold uppercase tracking-[.16em] text-amber-800">♻ Alternative au devis</p><h2 class="mt-2 text-xl font-black text-slate-900 sm:text-2xl">Cette réparation coûte {{ number_format((float) $repairRequest->estimated_cost, 2, ',', ' ') }} TND</h2><p class="mt-2 max-w-2xl text-sm text-slate-700">Avant de décider, comparez trois choix possibles pour cette pièce. Le seuil d’affichage est {{ number_format((float) config('ai.upcycle_cost_threshold'), 0) }} TND.</p></div><span class="rounded-full border border-violet-200 bg-white px-3 py-1.5 text-xs font-bold text-violet-800 shadow-sm">Conseils IA · OpenAI</span></div>
				<div class="mt-5 grid gap-3 md:grid-cols-3">
					<a href="{{ route('repairs.show', $repairRequest) }}#quote-actions" class="rounded-xl border border-white bg-white/90 p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-md"><span class="text-2xl">🧵</span><h3 class="mt-2 font-bold text-slate-900">Réparer</h3><p class="mt-1 text-sm text-slate-600">Consultez le devis et acceptez-le si cette réparation vous convient.</p></a>
					<a href="{{ route('products.ideas') }}" class="rounded-xl border border-white bg-white/90 p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-md"><span class="text-2xl">✨</span><h3 class="mt-2 font-bold text-slate-900">Upcycler</h3><p class="mt-1 text-sm text-slate-600">Explorez des idées pour donner une nouvelle fonction à la pièce.</p></a>
					<a href="{{ route('deposits.index') }}" class="rounded-xl border border-white bg-white/90 p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-md"><span class="text-2xl">🤝</span><h3 class="mt-2 font-bold text-slate-900">Donner</h3><p class="mt-1 text-sm text-slate-600">Trouvez un point de collecte pour favoriser le réemploi.</p></a>
				</div>
				<div class="mt-5 flex flex-wrap items-center gap-3">
					<button type="button" id="load-alternatives" class="upcycle-ai-button inline-flex min-h-11 items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold text-white shadow-md focus:outline-none focus:ring-4 focus:ring-amber-200 disabled:opacity-70"><span aria-hidden="true">✦</span><span data-alternatives-label>Voir les alternatives avec l’assistant</span></button>
					<p class="text-xs text-slate-600">L’assistant personnalise le conseil. Les trois choix restent visibles si l’IA est indisponible.</p>
				</div>
				<p id="alternatives-status" class="mt-3 min-h-5 text-sm font-semibold text-violet-900" aria-live="polite"></p>
				<div id="alternatives-list" class="mt-3 grid gap-3 md:grid-cols-3"></div>
			</div>
		</div>
		<script>
		(() => { const button = document.getElementById('load-alternatives'), list = document.getElementById('alternatives-list'), status = document.getElementById('alternatives-status'); button.addEventListener('click', async function () { this.disabled = true; this.querySelector('[data-alternatives-label]').textContent = 'Préparation des conseils…'; status.textContent = 'L’assistant prépare un conseil pour chaque choix.'; const render = options => { list.replaceChildren(); (options || []).forEach(option => { const card = document.createElement('article'), title = document.createElement('p'), text = document.createElement('p'), link = document.createElement('a'); card.className = 'rounded-xl border border-violet-200 bg-white p-4 shadow-sm'; title.className = 'text-xs font-bold uppercase tracking-wide text-violet-800'; title.textContent = option.title; text.className = 'mt-2 text-sm leading-6 text-slate-700'; text.textContent = option.text; link.className = 'mt-3 inline-block text-sm font-semibold text-emerald-800 underline'; link.href = option.url; link.textContent = 'En savoir plus →'; card.append(title, text, link); list.appendChild(card); }); }; try { const response = await window.axios.post(@json(route('repairs.alternatives', $repairRequest))); render(response.data.options); status.textContent = response.data.fallback ? 'Mode automatique : les options sont affichées sans réponse IA.' : 'Conseils générés par l’IA. Les liens viennent de TexTileCycle.'; } catch (error) { const data = error.response?.data; render(data?.options); status.textContent = data?.fallback ? 'Quota atteint : options automatiques affichées.' : (data?.message || 'Options momentanément indisponibles.'); } finally { this.disabled = false; this.querySelector('[data-alternatives-label]').textContent = 'Actualiser les conseils'; } }); })();
		</script>
		<style>.upcycle-demo-card{animation:upcycle-in .45s ease-out both}.upcycle-ai-button{background:linear-gradient(110deg,#92400e,#d97706 55%,#047857);background-size:180% 100%;transition:transform .2s ease,box-shadow .2s ease,background-position .3s ease}.upcycle-ai-button:hover:not(:disabled){transform:translateY(-2px);background-position:100% 0;box-shadow:0 10px 24px rgb(146 64 14 / 22%)}@keyframes upcycle-in{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}@media(prefers-reduced-motion:reduce){.upcycle-demo-card{animation:none}.upcycle-ai-button{transition:none}}</style>
	@endif
	@if(isset($messages) && $messages->isNotEmpty())<section class="mt-5 rounded-xl bg-white p-6 shadow"><h2 class="font-bold">Messages de l’atelier</h2>@foreach($messages as $message)<p class="mt-3 whitespace-pre-line">{{ $message->description }}</p>@endforeach</section>@endif

	<div class="mt-6 rounded-xl bg-white p-6 shadow">
		<h2 class="mb-4 text-xl font-bold">Photos du vêtement</h2>
		<div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
			@forelse($repairRequest->photos as $photo)
				<a href="{{ route('repairs.photos.show', [$repairRequest, $photo]) }}" target="_blank" rel="noreferrer">
					<img src="{{ route('repairs.photos.show', [$repairRequest, $photo]) }}" alt="{{ $photo->type }} réparation" class="h-40 w-full rounded-lg object-cover">
					<p class="mt-1 text-xs text-gray-500">{{ $photo->type }}</p>
				</a>
			@empty
				<p class="text-gray-500">Aucune photo ajoutée.</p>
			@endforelse
		</div>
	</div>
</section>
@endsection
