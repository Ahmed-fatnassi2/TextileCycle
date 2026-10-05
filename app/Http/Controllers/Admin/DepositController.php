<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDepositRequest;
use App\Http\Requests\UpdateDepositRequest;
use App\Models\Deposit;
use App\Models\DepositPoint;
use App\Models\User;
use Illuminate\Http\Request;

class DepositController extends Controller
{
    public function index(Request $request)
    {
        // Requête avec jointure (eager loading)
        $query = Deposit::with(['user', 'depositPoint']);

        if ($search = $request->input('search')) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($pointId = $request->input('deposit_point_id')) {
            $query->where('deposit_point_id', $pointId);
        }

        $deposits = $query->latest('deposit_date')->paginate(15)->withQueryString();
        $points = DepositPoint::orderBy('name')->get();

        return view('admin.deposits.index', compact('deposits', 'points'));
    }

    public function create()
    {
        $users = User::orderBy('name')->get();
        $points = DepositPoint::where('state', 'Ouvert')->orderBy('name')->get();

        return view('admin.deposits.create', compact('users', 'points'));
    }

    public function store(StoreDepositRequest $request)
    {
        Deposit::create($request->validated());

        return redirect()
            ->route('admin.deposits.index')
            ->with('success', 'Dépôt enregistré avec succès.');
    }

    public function show(Deposit $deposit)
    {
        $deposit->load(['user', 'depositPoint']);

        return view('admin.deposits.show', compact('deposit'));
    }

    public function edit(Deposit $deposit)
    {
        $users = User::orderBy('name')->get();
        $points = DepositPoint::orderBy('name')->get();

        return view('admin.deposits.edit', compact('deposit', 'users', 'points'));
    }

    public function update(UpdateDepositRequest $request, Deposit $deposit)
    {
        $deposit->update($request->validated());

        return redirect()
            ->route('admin.deposits.index')
            ->with('success', 'Dépôt mis à jour.');
    }

    public function destroy(Deposit $deposit)
    {
        $deposit->delete();

        return redirect()
            ->route('admin.deposits.index')
            ->with('success', 'Dépôt supprimé.');
    }
}