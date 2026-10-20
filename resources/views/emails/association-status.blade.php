@extends('emails.layout')

@section('content')
    <h1 style="margin:0 0 16px;font-size:22px">Mise à jour de votre demande</h1>
    <p>La demande de l’association <strong>{{ $association->name }}</strong> a été mise à jour.</p>
    <p>Nouveau statut : <strong>{{ $association->status }}</strong>.</p>
    <p>Connectez-vous pour consulter les informations de votre demande :</p>
    <p><a href="{{ $statusUrl }}" style="display:inline-block;padding:12px 18px;background:#064e3b;color:#fff;text-decoration:none;font-weight:bold">Consulter ma demande</a></p>
@endsection
