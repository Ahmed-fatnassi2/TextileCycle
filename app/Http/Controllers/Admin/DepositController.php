<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDepositRequest;
use App\Http\Requests\UpdateDepositRequest;
use App\Models\Deposit;
use App\Models\DepositPoint;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepositController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(Deposit::statuses())],
            'deposit_point_id' => ['nullable', 'integer', 'exists:deposit_points,id'],
        ]);

        // Requête avec jointure (eager loading)
        $query = Deposit::with(['user', 'depositPoint']);

        // Recherche par nom du citoyen ou par code QR
        if ($search = $filters['search'] ?? null) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"))
                  ->orWhere('code', 'like', '%'.strtoupper($search).'%');
            });
        }

        if ($status = $filters['status'] ?? null) {
            $query->where('status', $status);
        }

        if ($pointId = $filters['deposit_point_id'] ?? null) {
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
        $deposit->load(['user', 'depositPoint', 'checkedInBy']);

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