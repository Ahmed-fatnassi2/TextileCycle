<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    {{-- DomPDF : CSS simple uniquement (pas de Tailwind, pas de flex/grid). DejaVu Sans gère les accents. --}}
    <style>
        @page { margin: 28px 32px 48px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1e293b; }
        h1 { font-size: 20px; margin: 0; color: #064e3b; }
        h2 { font-size: 13px; margin: 22px 0 8px; color: #064e3b; border-bottom: 1px solid #a7f3d0; padding-bottom: 4px; }
        h3 { font-size: 11px; margin: 0 0 6px; }
        .muted { color: #64748b; }
        .header { border-bottom: 3px solid #064e3b; padding-bottom: 10px; margin-bottom: 14px; }
        .brand { font-size: 10px; font-weight: bold; letter-spacing: 1px; color: #65a30d; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #ecfdf5; color: #065f46; text-align: left; font-size: 9px; text-transform: uppercase; padding: 5px 6px; }
        td { padding: 5px 6px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        .num { text-align: right; white-space: nowrap; }
        .kpis td { width: 20%; border: 1px solid #d1fae5; background: #f0fdf4; padding: 8px; text-align: center; }
        .kpi-value { font-size: 15px; font-weight: bold; color: #064e3b; }
        .kpi-label { font-size: 8px; color: #475569; text-transform: uppercase; }
        .batch { border: 1px solid #e2e8f0; padding: 10px; margin-bottom: 10px; page-break-inside: avoid; }
        .badge { background: #dcfce7; color: #166534; padding: 1px 5px; font-size: 8px; font-weight: bold; }
        .out { color: #b91c1c; }
        .footer { position: fixed; bottom: -32px; left: 0; right: 0; font-size: 8px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
    <div class="footer">TexTileCycle · {{ $title }} · généré le {{ $generatedAt->format('d/m/Y à H:i') }}</div>

    <div class="header">
        <div class="brand">TEXTILECYCLE · TRANSFORMATION & UPCYCLING</div>
        <h1>{{ $title }}</h1>
        <p class="muted" style="margin: 4px 0 0;">
            Généré le {{ $generatedAt->format('d/m/Y à H:i') }} par {{ $author }}
            @if($filters)
                · Filtres :
                @isset($filters['material_type']) matière « {{ $filters['material_type'] }} » @endisset
                @isset($filters['quality_grade']) qualité « {{ $filters['quality_grade'] }} » @endisset
            @endif
        </p>
    </div>

    <table class="kpis">
        <tr>
            <td><div class="kpi-value">{{ $stats['batches'] }}</div><div class="kpi-label">Lots de matière</div></td>
            <td><div class="kpi-value">{{ number_format($stats['weight'], 1, ',', ' ') }} kg</div><div class="kpi-label">Matière en lots</div></td>
            <td><div class="kpi-value">{{ $stats['products'] }}</div><div class="kpi-label">Produits</div></td>
            <td><div class="kpi-value">{{ $stats['units'] }}</div><div class="kpi-label">Pièces en stock</div></td>
            <td><div class="kpi-value">{{ number_format($stats['value'], 2, ',', ' ') }}</div><div class="kpi-label">Valeur du stock (TND)</div></td>
        </tr>
    </table>

    @if($byMaterial->count() > 1)
        <h2>Répartition par matière</h2>
        <table>
            <thead><tr><th>Matière</th><th class="num">Lots</th><th class="num">Poids (kg)</th><th class="num">Produits</th><th class="num">Pièces en stock</th><th class="num">Valeur (TND)</th></tr></thead>
            <tbody>
                @foreach($byMaterial as $type => $row)
                    <tr>
                        <td>{{ $type }}</td>
                        <td class="num">{{ $row['batches'] }}</td>
                        <td class="num">{{ number_format($row['weight'], 1, ',', ' ') }}</td>
                        <td class="num">{{ $row['products'] }}</td>
                        <td class="num">{{ $row['units'] }}</td>
                        <td class="num">{{ number_format($row['value'], 2, ',', ' ') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2>Détail des lots et de leurs produits</h2>
    @forelse($batches as $batch)
        <div class="batch">
            <h3>
                Lot #{{ $batch->id }} · {{ $batch->material_type }}
                <span class="badge">Qualité {{ $batch->quality_grade }}</span>
            </h3>
            <p class="muted" style="margin: 0 0 8px;">
                {{ number_format((float) $batch->weight, 2, ',', ' ') }} kg · reçu le {{ $batch->created_at?->format('d/m/Y') }}
                · {{ $batch->upcycledProducts->count() }} produit(s)
            </p>

            @if($batch->upcycledProducts->isNotEmpty())
                <table>
                    <thead><tr><th>Produit</th><th class="num">Prix (TND)</th><th class="num">Stock</th><th class="num">Valeur (TND)</th></tr></thead>
                    <tbody>
                        @foreach($batch->upcycledProducts as $product)
                            <tr>
                                <td>
                                    <strong>{{ $product->name }}</strong>
                                    @if($product->description)<br><span class="muted">{{ \Illuminate\Support\Str::limit($product->description, 140) }}</span>@endif
                                </td>
                                <td class="num">{{ number_format((float) $product->price, 2, ',', ' ') }}</td>
                                <td class="num {{ $product->stock === 0 ? 'out' : '' }}">{{ $product->stock === 0 ? 'Épuisé' : $product->stock }}</td>
                                <td class="num">{{ number_format((float) $product->price * $product->stock, 2, ',', ' ') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="muted" style="margin: 0;">Aucun produit fabriqué à partir de ce lot pour le moment.</p>
            @endif
        </div>
    @empty
        <p class="muted">Aucun lot ne correspond à ces critères.</p>
    @endforelse
</body>
</html>
