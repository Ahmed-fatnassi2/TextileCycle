<?php

namespace App\Http\Controllers;

use App\Models\Association;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssociationRequestController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if ($request->user()->association) {
            return redirect()->route('association.status');
        }

        return view('front.associations.create');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if($request->user()->association()->exists(), 409);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:40'],
            'needs_description' => ['required', 'string', 'max:5000'],
        ]);

        $request->user()->association()->create([
            ...$data,
            'status' => Association::STATUS_PENDING,
        ]);

        return redirect()
            ->route('association.status')
            ->with('success', 'Votre demande a été envoyée. Elle sera examinée par notre équipe.');
    }

    public function status(Request $request): View|RedirectResponse
    {
        $association = $request->user()->association;

        if (! $association) {
            return redirect()->route('associations.create');
        }

        return view('front.associations.status', compact('association'));
    }
}