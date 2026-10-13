<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDepositPointRequest;
use App\Http\Requests\UpdateDepositPointRequest;
use App\Models\DepositPoint;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepositPointController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', Rule::in(['Ouvert', 'Plein', 'En maintenance', 'Fermé'])],
        ]);

        $query = DepositPoint::withCount('deposits');

        if ($search = $filters['search'] ?? null) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($state = $filters['state'] ?? null) {
            $query->where('state', $state);
        }

        $depositPoints = $query->latest()->paginate(10)->withQueryString();

        return view('admin.deposit-points.index', compact('depositPoints'));
    }

    public function create()
    {
        return view('admin.deposit-points.create');
    }

    public function store(StoreDepositPointRequest $request)
    {
        DepositPoint::create($request->validated());

        return redirect()
            ->route('admin.deposit-points.index')
            ->with('success', 'Point de collecte créé avec succès.');
    }

    public function show(DepositPoint $depositPoint)
    {
        $depositPoint->load(['deposits.user' => function ($q) {
            $q->latest()->take(10);
        }]);

        return view('admin.deposit-points.show', compact('depositPoint'));
    }

    public function edit(DepositPoint $depositPoint)
    {
        return view('admin.deposit-points.edit', compact('depositPoint'));
    }

    public function update(UpdateDepositPointRequest $request, DepositPoint $depositPoint)
    {
        $depositPoint->update($request->validated());

        return redirect()
            ->route('admin.deposit-points.index')
            ->with('success', 'Point de collecte mis à jour.');
    }

    public function destroy(DepositPoint $depositPoint)
    {
        $depositPoint->delete();

        return redirect()
            ->route('admin.deposit-points.index')
            ->with('success', 'Point de collecte supprimé.');
    }
}