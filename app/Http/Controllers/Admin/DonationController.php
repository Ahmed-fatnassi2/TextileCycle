<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Association;
use App\Models\Donation;
use App\Services\DonationScheduler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DonationController extends Controller
{
    public function __construct(private DonationScheduler $scheduler)
    {
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', Rule::in(Donation::CATEGORIES)],
            'status' => ['nullable', Rule::in(Donation::statuses())],
        ]);

        $donations = Donation::with('association')
            // Recherche par référence (DON-2026-0042), association ou ville
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where(fn ($q) => $q
                ->where('reference', 'like', '%'.strtoupper($search).'%')
                ->orWhere('delivery_city', 'like', "%{$search}%")
                ->orWhereHas('association', fn ($a) => $a->where('name', 'like', "%{$search}%"))))
            ->when($filters['category'] ?? null, fn ($q, $category) => $q->where('category', $category))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->latest('donation_date')
            ->paginate(15)
            ->withQueryString();

        return view('admin.donations.index', compact('donations', 'filters'));
    }

    public function create(): View
    {
        $associations = Association::where('status', Association::STATUS_APPROVED)->orderBy('name')->get();

        return view('admin.donations.create', compact('associations'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules(), Donation::detailMessages());
        $this->ensureCapacity($data);

        Donation::create($data);

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
        $data = $request->validate($this->rules(), Donation::detailMessages());
        $oldDate = $donation->donation_date->copy();

        $dateChanged = ! $oldDate->isSameDay($data['donation_date']);
        if ($dateChanged || (int) $data['quantity_items'] !== $donation->quantity_items) {
            $this->ensureCapacity($data, $donation->id);
        }

        $donation->update($data);

        $message = 'Don mis à jour.';
        if ($dateChanged && ! $this->scheduler->afterDateChange($donation->load('association.user'), $oldDate, $request->user())) {
            $message .= ' L’email à l’association n’a pas pu être envoyé.';
        }

        return redirect()->route('admin.donations.show', $donation)->with('success', $message);
    }

    public function destroy(Donation $donation): RedirectResponse
    {
        $donation->delete();

        return redirect()->route('admin.donations.index')->with('success', 'Don supprimé.');
    }

    // Refuse une date dont la capacité de livraison est déjà atteinte
    private function ensureCapacity(array $data, ?int $ignoreId = null): void
    {
        if ($conflict = $this->scheduler->conflictFor($data['donation_date'], (int) $data['quantity_items'], $ignoreId)) {
            throw ValidationException::withMessages(['donation_date' => $conflict]);
        }
    }

    private function rules(): array
    {
        return [
            'association_id' => ['required', 'exists:associations,id'],
            ...Donation::detailRules(),
            'donation_date' => ['required', 'date'],
            'status' => ['required', Rule::in(Donation::statuses())],
        ];
    }
}