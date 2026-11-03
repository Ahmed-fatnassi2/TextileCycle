<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Certificat de réparation responsable</title>
    <style>
        body{margin:0;background:#f1f5f9;color:#0f172a;font:16px/1.6 system-ui,sans-serif}
        main{box-sizing:border-box;max-width:760px;margin:40px auto;padding:48px;background:white;border:1px solid #d1d5db}
        .eyebrow{color:#047857;font-size:.8rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase}
        h1{margin:.5rem 0 0;font-size:2rem;line-height:1.2}
        .intro{color:#475569}
        dl{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px;margin:32px 0}
        dt{color:#64748b;font-size:.85rem}
        dd{margin:4px 0 0;font-weight:650}
        .impact{padding:20px;border-radius:12px;background:#ecfdf5}
        .impact strong{color:#065f46;font-size:1.25rem}
        .note{color:#64748b;font-size:.8rem}
        button{margin-top:24px;padding:10px 16px;border:0;border-radius:6px;background:#065f46;color:#fff;font:inherit;cursor:pointer}
        @media(max-width:600px){main{margin:0;padding:24px}dl{grid-template-columns:1fr}}
        @media print{body{background:white}main{max-width:none;margin:0;padding:24px;border:0}button{display:none}}
    </style>
</head>
<body>
<main>
    <p class="eyebrow">TexTileCycle · Réparation et réemploi</p>
    <h1>Certificat de réparation responsable</h1>
    <p class="intro">Cette attestation récapitule une réparation réalisée dans une démarche de prolongation de la vie des textiles.</p>

    <dl>
        <div><dt>Article</dt><dd>{{ $repairRequest->item_description }}</dd></div>
        <div><dt>Type de réparation</dt><dd>{{ $repairRequest->problem_type }}</dd></div>
        <div><dt>Atelier</dt><dd>{{ $repairRequest->workshop->name }}</dd></div>
        <div><dt>Statut</dt><dd>{{ $repairRequest->status }}</dd></div>
        <div><dt>Montant facturé</dt><dd>{{ $repairRequest->cost !== null ? number_format((float) $repairRequest->cost, 2, ',', ' ').' TND' : 'Non renseigné' }}</dd></div>
        <div><dt>Date de réalisation</dt><dd>{{ $repairRequest->ready_at?->format('d/m/Y') ?? 'Non renseignée' }}</dd></div>
    </dl>

    <section class="impact">
        <p><strong>{{ number_format((float) $repairRequest->co2_saved_kg, 2, ',', ' ') }} kg CO₂e</strong> d’émissions évitées estimées</p>
        <p><strong>{{ number_format((float) $repairRequest->waste_saved_kg, 2, ',', ' ') }} kg</strong> de textile préservés estimés</p>
        <p class="note">Estimations indicatives calculées à partir de la catégorie de l’article. Elles ne constituent ni une mesure environnementale certifiée ni une garantie.</p>
    </section>

    <p class="note">Référence de réparation : #{{ $repairRequest->id }} · Certificat consulté le {{ now()->format('d/m/Y') }}</p>
    <button type="button" onclick="window.print()">Imprimer ou enregistrer en PDF</button>
</main>
</body>
</html>
