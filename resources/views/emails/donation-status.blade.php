@extends('emails.layout')

@section('content')
    <h1 style="margin:0 0 16px;font-size:22px">Mise à jour de votre demande de don</h1>
    <p>Le statut de votre demande <strong>{{ $reference }}</strong> a changé.</p>
    <table role="presentation" cellspacing="0" cellpadding="0" style="margin:20px 0">
        <tr><td style="padding:6px 18px 6px 0;color:#64748b">Statut</td><td style="padding:6px 0;font-weight:bold">{{ $status }}</td></tr>
        <tr><td style="padding:6px 18px 6px 0;color:#64748b">Catégorie</td><td style="padding:6px 0">{{ $category }}</td></tr>
        <tr><td style="padding:6px 18px 6px 0;color:#64748b">Quantité</td><td style="padding:6px 0">{{ $quantity }} pièces</td></tr>
        <tr><td style="padding:6px 18px 6px 0;color:#64748b">Date souhaitée</td><td style="padding:6px 0">{{ $plannedDate->format('d/m/Y') }}</td></tr>
        <tr><td style="padding:6px 18px 6px 0;color:#64748b">Date de mise à jour</td><td style="padding:6px 0">{{ $changedAt->format('d/m/Y à H:i') }}</td></tr>
    </table>
    <p>Consultez l’historique détaillé après connexion à votre compte :</p>
    <p><a href="{{ $trackingUrl }}" style="display:inline-block;padding:12px 18px;background:#064e3b;color:#fff;text-decoration:none;font-weight:bold">Suivre mon don</a></p>
@endsection
