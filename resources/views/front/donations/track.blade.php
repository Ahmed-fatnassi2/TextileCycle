<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="referrer" content="no-referrer">
    <title>Suivi du don · TextileCycle</title>
    <style>
        :root { color-scheme: light; font-family: Arial, sans-serif; color: #0f172a; background: #f1f5f9; }
        * { box-sizing: border-box; }
        body { margin: 0; padding: 20px 14px; }
        main { max-width: 640px; margin: 0 auto; }
        header, section { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; }
        header { padding: 22px; color: #fff; background: #064e3b; border: 0; }
        header p { margin: 0 0 8px; color: #d1fae5; font-size: 13px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
        h1 { margin: 0; font-size: clamp(24px, 6vw, 34px); }
        .reference { margin: 10px 0 0; color: #d1fae5; font-family: monospace; font-size: 14px; overflow-wrap: anywhere; }
        section { margin-top: 16px; padding: 20px; }
        h2 { margin: 0 0 16px; font-size: 19px; }
        dl { display: grid; grid-template-columns: 1fr 1fr; gap: 18px 12px; margin: 0; }
        dt { color: #64748b; font-size: 13px; }
        dd { margin: 5px 0 0; font-size: 16px; font-weight: 700; overflow-wrap: anywhere; }
        .status { display: inline-block; margin-top: 18px; border-radius: 999px; padding: 8px 13px; background: #dcfce7; color: #14532d; font-weight: 700; }
        ol { margin: 0; padding: 0; list-style: none; }
        li { margin: 0 0 16px; padding-left: 14px; border-left: 3px solid #059669; }
        li:last-child { margin-bottom: 0; }
        li strong, li time { display: block; }
        li time { margin-top: 4px; color: #64748b; font-size: 13px; }
        footer { padding: 18px 4px; text-align: center; color: #64748b; font-size: 13px; }
        @media (max-width: 380px) { dl { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <main>
        <header>
            <p>TextileCycle · suivi du don</p>
            <h1>Informations de la demande</h1>
            <div class="reference">{{ $donation->trackingReference() }}</div>
            <span class="status">{{ $donation->status }}</span>
        </header>
        <section aria-labelledby="donation-details">
            <h2 id="donation-details">Détail du don</h2>
            <dl>
                <div><dt>Association</dt><dd>{{ $donation->association->name }}</dd></div>
                <div><dt>Catégorie</dt><dd>{{ $donation->category }}</dd></div>
                <div><dt>Quantité</dt><dd>{{ $donation->quantity_items }} pièces</dd></div>
                <div><dt>Date prévue</dt><dd>{{ $donation->donation_date->format('d/m/Y') }}</dd></div>
            </dl>
        </section>
        <section aria-labelledby="donation-history">
            <h2 id="donation-history">Historique</h2>
            <ol>
                @foreach($donation->statusHistory->sortByDesc('created_at') as $event)
                    <li><strong>{{ $event->to_status }}</strong><time datetime="{{ $event->created_at->toIso8601String() }}">{{ $event->created_at->format('d/m/Y à H:i') }}</time></li>
                @endforeach
            </ol>
        </section>
        <footer>Cette fiche est en lecture seule. Le scan du QR Code ne modifie pas le statut.</footer>
    </main>
</body>
</html>
