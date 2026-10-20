@extends('emails.layout')

@section('content')
    <h1 style="margin:0 0 16px;font-size:22px">{{ $activity }}</h1>
    <p>Une nouvelle action concernant le module Associations et Dons nécessite votre attention.</p>
    <table role="presentation" cellspacing="0" cellpadding="0" style="margin:20px 0">
        <tr><td style="padding:6px 18px 6px 0;color:#64748b">Élément</td><td style="padding:6px 0;font-weight:bold">{{ $name }}</td></tr>
        @if($reference)
            <tr><td style="padding:6px 18px 6px 0;color:#64748b">Référence</td><td style="padding:6px 0">{{ $reference }}</td></tr>
        @endif
        <tr><td style="padding:6px 18px 6px 0;color:#64748b">Statut</td><td style="padding:6px 0">{{ $status }}</td></tr>
        <tr><td style="padding:6px 18px 6px 0;color:#64748b">Date</td><td style="padding:6px 0">{{ now()->format('d/m/Y à H:i') }}</td></tr>
    </table>
    <p>Connectez-vous au BackOffice pour consulter les détails et traiter cette demande :</p>
    <p><a href="{{ $detailsUrl }}" style="display:inline-block;padding:12px 18px;background:#064e3b;color:#fff;text-decoration:none;font-weight:bold">Ouvrir dans le BackOffice</a></p>
@endsection
