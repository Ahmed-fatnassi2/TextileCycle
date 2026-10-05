<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Association;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AssociationController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(Association::statuses())],
            'search' => ['nullable', 'string', 'max:255'],
        ]);
        $query = Association::with('user')->withCount('donations')->latest();

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%");
            });
        }

        $associations = $query->paginate(12)->withQueryString();

        return view('admin.associations.index', compact('associations'));
    }

    public function create(): View
    {
        $users = User::whereDoesntHave('association')->orderBy('name')->get();

        return view('admin.associations.create', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id', 'unique:associations,user_id'],
            ...$this->profileRules(),
            'status' => ['required', Rule::in(Association::statuses())],
        ]);

        Association::create($data);

        return redirect()->route('admin.associations.index')->with('success', 'Association créée.');
    }

    public function show(Association $association): View
    {
        $association->load(['user', 'donations' => fn ($query) => $query->latest('donation_date')]);

        return view('admin.associations.show', compact('association'));
    }

    public function edit(Association $association): View
    {
        return view('admin.associations.edit', compact('association'));
    }

    public function update(Request $request, Association $association): RedirectResponse
    {
        $association->update($request->validate([
            ...$this->profileRules(),
            'status' => ['required', Rule::in(Association::statuses())],
        ]));

        return redirect()->route('admin.associations.show', $association)->with('success', 'Association mise à jour.');
    }

    public function destroy(Association $association): RedirectResponse
    {
        $association->delete();

        return redirect()->route('admin.associations.index')->with('success', 'Association supprimée.');
    }

    private function profileRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:40'],
            'needs_description' => ['required', 'string', 'max:5000'],
        ];
    }
}