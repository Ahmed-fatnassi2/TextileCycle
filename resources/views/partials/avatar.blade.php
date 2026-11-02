{{-- Avatar : photo de profil si elle existe, sinon l'initiale. Paramètres : $user, $class (taille / texte) --}}
@if($user->profilePhotoUrl())
    <img src="{{ $user->profilePhotoUrl() }}" alt="Photo de {{ $user->name }}" class="{{ $class ?? 'h-8 w-8' }} shrink-0 rounded-full object-cover">
@else
    <span class="{{ $class ?? 'h-8 w-8 text-sm' }} grid shrink-0 place-items-center rounded-full bg-emerald-900 font-bold text-lime-200">{{ $user->initial() }}</span>
@endif
