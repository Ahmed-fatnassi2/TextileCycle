<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', Rule::in([User::ROLE_USER, User::ROLE_ADMIN, User::ROLE_WORKSHOP_OWNER])],
        ]);

        $query = User::query()->withCount(['repairRequests', 'activities']);
        if ($search = $filters['search'] ?? null) {
            $query->where(fn ($builder) => $builder->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        }
        if ($role = $filters['role'] ?? null) {
            $query->where('role', $role);
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in([User::ROLE_USER, User::ROLE_ADMIN, User::ROLE_WORKSHOP_OWNER])],
        ]);

        $user = User::create($data);
        User::logActivity($user, 'Compte créé', 'Compte créé par un administrateur.', $request->user(), ['role' => $user->role]);

        return redirect()->route('admin.users.index')->with('success', 'Utilisateur créé.');
    }

    public function show(User $user): View
    {
        $user->load(['activities' => fn ($query) => $query->with('actor')->latest()->take(30)]);

        return view('admin.users.show', compact('user'));
    }

    public function update(UpdateUserRoleRequest $request, User $user): RedirectResponse
    {
        abort_if($user->is($request->user()), 422, 'Vous ne pouvez pas modifier votre propre rôle.');
        if ($user->ownedWorkshops()->exists() && $request->validated('role') !== User::ROLE_WORKSHOP_OWNER) {
            return back()->withErrors(['role' => 'Désaffectez d’abord ce propriétaire de son atelier.']);
        }

        $oldRole = $user->role;
        $user->update($request->validated());
        User::logActivity($user, 'Rôle modifié', "Rôle modifié de {$oldRole} vers {$user->role} par un administrateur.", $request->user(), ['old_role' => $oldRole, 'new_role' => $user->role]);

        return back()->with('success', 'Rôle utilisateur mis à jour.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_if($user->is($request->user()), 422, 'Vous ne pouvez pas supprimer votre propre compte.');
        User::logActivity($user, 'Compte supprimé', "Compte de {$user->name} supprimé par un administrateur.", $request->user());
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Utilisateur supprimé.');
    }
}
