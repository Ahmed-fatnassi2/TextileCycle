<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Association;
use App\Models\Donation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DonationController extends Controller
{
    public function index(): View
    {
        $donations = Donation::with('association')->latest('donation_date')->paginate(15);

        return view('admin.donations.index', compact('donations'));
    }

    public function create(): View
    {
        $associations = Association::where('status', Association::STATUS_APPROVED)->orderBy('name')->get();

        return view('admin.donations.create', compact('associations'));
    }

    public function store(Request $request): RedirectResponse
    {
        Donation::create($request->validate($this->rules()));

        return redirect()->route('admin.donations.index')->with('success', 'Don enregistré.');
    }

    public function show(Donation $donation): View
    {
        $donation->load('association');

        return view('admin.donations.show', compact('donation'));
    }

    public function edit(Donation $donation): View
    {
        $associations = Association::orderBy('name')->get();

        return view('admin.donations.edit', compact('donation', 'associations'));
    }

    public function update(Request $request, Donation $donation): RedirectResponse
    {
        $donation->update($request->validate($this->rules()));

        return redirect()->route('admin.donations.show', $donation)->with('success', 'Don mis à jour.');
    }

    public function destroy(Donation $donation): RedirectResponse
    {
        $donation->delete();

        return redirect()->route('admin.donations.index')->with('success', 'Don supprimé.');
    }

    private function rules(): array
    {
        return [
            'association_id' => ['required', 'exists:associations,id'],
            'quantity_items' => ['required', 'integer', 'min:1', 'max:10000'],
            'donation_date' => ['required', 'date'],
            'status' => ['required', Rule::in(Donation::statuses())],
        ];
    }
}