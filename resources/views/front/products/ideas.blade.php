@extends('layouts.front')

@section('title', 'Que créer avec ma matière ? | TexTileCycle')

@section('content')
    <section class="border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-5xl px-5 py-12 sm:px-8 lg:py-16">
            <a href="{{ route('products.index') }}" class="text-sm font-semibold text-emerald-900 underline decoration-emerald-300 underline-offset-4">← Produits upcyclés</a>
            <p class="mt-6 text-sm font-semibold uppercase tracking-wide text-violet-700">Idées upcycling · IA</p>
            <h1 class="mt-3 font-display text-4xl leading-tight text-slate-950 sm:text-5xl">Que créer avec ma matière ?</h1>
            <p class="mt-4 max-w-2xl leading-7 text-slate-600">
                Décrivez un vêtement ou un tissu que vous avez chez vous : nous vous proposons des objets à fabriquer vous-même,
                étape par étape, et des créations de notre catalogue dans la même matière.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-5xl px-5 py-10 sm:px-8"
             data-upcycling-ideas
             data-mode="visitor"
             data-url="{{ route('products.ideas.suggest') }}">
        <form data-ideas-form class="grid gap-3 sm:grid-cols-[1fr_auto]">
            <label for="material" class="sr-only">Votre matière</label>
            <input id="material" name="material" type="text" required minlength="2" maxlength="100" autocomplete="off"
                   placeholder="Ex. : 2 vieux jeans, des draps en coton, un pull en laine troué…"
                   class="min-h-12 border border-slate-300 bg-white px-4 focus:border-violet-700 focus:outline-none focus:ring-2 focus:ring-violet-700/15">
            <button data-ideas-run class="min-h-12 bg-violet-700 px-6 font-semibold text-white transition hover:bg-violet-800 disabled:opacity-60">✨ Proposer des idées</button>
        </form>
        <div class="mt-3 flex flex-wrap gap-2 text-sm">
            @foreach(['Un vieux jean', 'Des t-shirts en coton', 'Un pull en laine troué', 'Des draps en lin'] as $example)
                <button type="button" data-ideas-example class="rounded-full border border-violet-200 px-3 py-1 text-violet-800 hover:bg-violet-50">{{ $example }}</button>
            @endforeach
        </div>

        <div data-ideas-result hidden class="mt-8"></div>
    </section>
@endsection

@push('scripts')
    @vite('resources/js/upcycling-ideas.js')
@endpush
