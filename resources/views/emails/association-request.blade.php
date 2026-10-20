@extends('emails.layout')

@section('content')
    <h1 style="margin:0 0 16px;font-size:22px">Demande d’association reçue</h1>
    <p>Bonjour,</p>
    <p>Nous avons bien reçu la demande de <strong>{{ $association->name }}</strong>. Elle est actuellement <strong>{{ $association->status }}</strong> et sera examinée par notre équipe.</p>
    <p>Vous pouvez consulter son état en vous connectant au compte utilisé pour déposer la demande :</p>
    <p><a href="{{ $statusUrl }}" style="display:inline-block;padding:12px 18px;background:#064e3b;color:#fff;text-decoration:none;font-weight:bold">Consulter ma demande</a></p>
@endsection
