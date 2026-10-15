@extends('layouts.admin')

@section('title', $user->name)
@section('page_title', 'Traçabilité utilisateur')

@section('content')
    <div class="mb-6 rounded-xl bg-white p-6 shadow"><div class="flex items-center gap-4"><span class="grid h-14 w-14 place-items-center rounded-full bg-emerald-900 text-xl font-bold text-lime-200">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span><div><h2 class="text-xl font-bold">{{ $user->name }}</h2><p class="text-gray-500">{{ $user->email }} · {{ $user->role === 'admin' ? 'Administrateur' : 'Citoyen' }}</p><p class="mt-1 text-sm text-gray-500">Inscrit le {{ $user->created_at->format('d/m/Y H:i') }} · Dernière connexion : {{ $user->last_login_at?->format('d/m/Y H:i') ?? 'jamais' }}</p></div></div></div>
    <div class="rounded-xl bg-white p-6 shadow"><div class="mb-5 flex items-center justify-between"><h3 class="text-lg font-bold">Journal d’activité</h3><a href="{{ route('admin.users.index') }}" class="text-sm text-emerald-700">← Utilisateurs</a></div><ol class="space-y-4">@forelse($user->activities as $activity)<li class="border-l-2 border-emerald-300 pl-4"><p class="font-medium text-slate-800">{{ $activity->action }}</p><p class="text-sm text-slate-600">{{ $activity->description }}</p><p class="mt-1 text-xs text-slate-400">{{ $activity->created_at->format('d/m/Y H:i') }}@if($activity->actor) · par {{ $activity->actor->name }}@endif</p></li>@empty<li class="text-gray-400">Aucune activité enregistrée pour ce compte.</li>@endforelse</ol></div>
@endsection
