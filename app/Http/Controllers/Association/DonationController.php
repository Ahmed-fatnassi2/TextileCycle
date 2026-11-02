<?php

namespace App\Http\Controllers\Association;

use App\Http\Controllers\Controller;
use App\Models\Association;
use App\Models\Donation;
use App\Services\DonationScheduler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DonationController extends Controller
{
    public function __construct(private DonationScheduler $scheduler)
    {
    }

    public function index(Request $request): View
    {
        $association = $this->approvedAssociation($request);
        $donations = $association->donations()->latest('donation_date')->paginate(12);

        return view('front.donations.index', compact('donations'));
    }

    public function create(Request $request): View
    {
        $association = $this->approvedAssociation($request);

        // Adresse de la dernière demande proposée par défaut
        $last = $association->donations()->whereNotNull('delivery_address')->latest()->first();

        return view('front.donations.create', [
            'defaults' => ['delivery_address' => $last?->delivery_address, 'delivery_city' => $last?->delivery_city],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $association = $this->approvedAssociation($request);
        $association->donations()->create($this->validatedData($request));

        return redirect()->route('association.donations.index')->with('success', 'Votre demande de don a été enregistrée.');
    }

    public function show(Request $request, Donation $donation): View
    {
        $association = $this->approvedAssociation($request);
        abort_unless($donation->association_id === $association->id, 404);

        return view('front.donations.show', compact('donation'));
    }

    public function edit(Request $request, Donation $donation): View
    {
        $association = $this->approvedAssociation($request);
        abort_unless($donation->association_id === $association->id, 404);
        abort_unless($donation->status === Donation::STATUS_PENDING, 403);

        return view('front.donations.edit', compact('donation'));
    }

    public function update(Request $request, Donation $donation): RedirectResponse
    {
        $association = $this->approvedAssociation($request);
        abort_unless($donation->association_id === $association->id, 404);
        abort_unless($donation->status === Donation::STATUS_PENDING, 403);

        $donation->update($this->validatedData($request, $donation));

        return redirect()->route('association.donations.show', $donation)->with('success', 'Demande de don mise à jour.');
    }

    public function destroy(Request $request, Donation $donation): RedirectResponse
    {
        $association = $this->approvedAssociation($request);
        abort_unless($donation->association_id === $association->id, 404);
        abort_unless($donation->status === Donation::STATUS_PENDING, 403);

        $donation->delete();

        return redirect()->route('association.donations.index')->with('success', 'Demande de don supprimée.');
    }

    private function approvedAssociation(Request $request): Association
    {
        $association = $request->user()->association;
        abort_unless($association?->status === Association::STATUS_APPROVED, 403, 'Votre association doit être approuvée.');

        return $association;
    }

    private function validatedData(Request $request, ?Donation $donation = null): array
    {
        $data = $request->validate([
            ...Donation::detailRules(),
            'donation_date' => ['required', 'date', 'after_or_equal:today'],
        ], Donation::detailMessages());

        // Même capacité de livraison que le calendrier admin
        if ($conflict = $this->scheduler->conflictFor($data['donation_date'], (int) $data['quantity_items'], $donation?->id)) {
            throw ValidationException::withMessages(['donation_date' => $conflict.' Choisissez une autre date.']);
        }

        return $data;
    }
}