<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepositRequest;
use App\Models\Deposit;
use App\Models\DepositPoint;
use Illuminate\Http\Request;

class DepositController extends Controller
{
    // Liste publique des points de collecte
    public function index()
    {
        $points = DepositPoint::withCount('deposits')
            ->where('state', '!=', 'Fermé')
            ->orderBy('city')
            ->get();

        return view('front.deposits.index', compact('points'));
    }

    // Formulaire de dépôt (citoyen)
    public function create()
    {
        $points = DepositPoint::where('state', 'Ouvert')->orderBy('city')->get();

        return view('front.deposits.create', compact('points'));
    }

    public function store(StoreDepositRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        Deposit::create($data);

        return redirect()
            ->route('deposits.index')
            ->with('success', 'Votre dépôt a bien été enregistré. Merci !');
    }
}