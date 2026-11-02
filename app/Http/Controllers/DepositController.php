<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepositRequest;
use App\Models\Deposit;
use App\Models\DepositPoint;
use App\Support\QrCode;
use Illuminate\Http\Request;

class DepositController extends Controller
{
    // Liste publique + carte des points de collecte.
    // Si le navigateur envoie sa position (?lat=..&lng=..), les points sont triés du plus proche au plus loin.
    public function index(Request $request)
    {
        $position = $request->validate([
            'lat' => ['nullable', 'numeric', 'between:-90,90', 'required_with:lng'],
            'lng' => ['nullable', 'numeric', 'between:-180,180', 'required_with:lat'],
        ]);

        $points = DepositPoint::withCount('deposits')
            ->where('state', '!=', DepositPoint::STATE_FERME)
            ->orderBy('city')
            ->get();

        $userPosition = null;

        if (isset($position['lat'], $position['lng'])) {
            $userPosition = ['lat' => (float) $position['lat'], 'lng' => (float) $position['lng']];

            $points->each(function (DepositPoint $point) use ($userPosition) {
                $point->distance_km = $point->distanceTo($userPosition['lat'], $userPosition['lng']);
            });

            // Points sans coordonnées en dernier
            $points = $points->sortBy(fn ($p) => $p->distance_km ?? PHP_FLOAT_MAX)->values();
        }

        // Le plus proche qui accepte encore des dépôts
        $nearestOpen = $userPosition
            ? $points->first(fn ($p) => $p->distance_km !== null && $p->acceptsDeposits())
            : null;

        $mapPoints = $points->filter->hasCoordinates()->map(fn (DepositPoint $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'address' => $p->address,
            'city' => $p->city,
            'state' => $p->state,
            'lat' => $p->latitude,
            'lng' => $p->longitude,
            'distance' => $p->distance_km !== null ? round($p->distance_km, 1) : null,
            'depositUrl' => $p->acceptsDeposits() ? route('deposits.create', ['point' => $p->id]) : null,
        ])->values();

        return view('front.deposits.index', compact('points', 'mapPoints', 'userPosition', 'nearestOpen'));
    }

    // Formulaire de dépôt (citoyen). ?point=ID pré-sélectionne le point choisi sur la carte.
    public function create(Request $request)
    {
        $points = DepositPoint::where('state', DepositPoint::STATE_OUVERT)->orderBy('city')->get();
        $selectedPointId = $request->integer('point') ?: null;

        return view('front.deposits.create', compact('points', 'selectedPointId'));
    }

    // Le citoyen réserve un dépôt : il reçoit un QR code à présenter au point de collecte.
    public function store(StoreDepositRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $data['status'] = Deposit::STATUS_EN_ATTENTE;

        $deposit = Deposit::create($data);

        return redirect()
            ->route('deposits.mine.show', $deposit)
            ->with('success', 'Dépôt réservé ! Présentez ce QR code au point de collecte.');
    }

    // Historique des dépôts du citoyen connecté
    public function mine(Request $request)
    {
        $deposits = $request->user()->deposits()
            ->with('depositPoint')
            ->latest()
            ->paginate(10);

        $totals = [
            'count' => $request->user()->deposits()->count(),
            'pending' => $request->user()->deposits()->where('status', Deposit::STATUS_EN_ATTENTE)->count(),
            'weight' => $request->user()->deposits()
                ->whereIn('status', [Deposit::STATUS_DEPOSE, Deposit::STATUS_TRIE])
                ->sum('weight_kg'),
        ];

        return view('front.deposits.mine', compact('deposits', 'totals'));
    }

    // Détail d'un dépôt avec son QR code
    public function show(Request $request, Deposit $deposit)
    {
        $this->ensureOwner($request, $deposit);
        $deposit->load('depositPoint');

        $qrSvg = QrCode::svg($this->checkinUrl($deposit));

        return view('front.deposits.show', compact('deposit', 'qrSvg'));
    }

    // Téléchargement du QR code en SVG
    public function downloadQr(Request $request, Deposit $deposit)
    {
        $this->ensureOwner($request, $deposit);

        return response(QrCode::svg($this->checkinUrl($deposit), 600), 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="depot-'.$deposit->code.'.svg"',
        ]);
    }

    // Le QR contient l'URL de validation : l'agent peut aussi le scanner avec l'appareil photo de son téléphone.
    private function checkinUrl(Deposit $deposit): string
    {
        return route('admin.deposits.checkin', $deposit->code);
    }

    private function ensureOwner(Request $request, Deposit $deposit): void
    {
        abort_unless($deposit->user_id === $request->user()->id, 404);
    }
}
