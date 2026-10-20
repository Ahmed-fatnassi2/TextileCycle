<?php

namespace App\Http\Controllers\Admin;

<<<<<<< HEAD
=======
use App\Actions\NotifyDonationStatusChanged;
use App\Actions\NotifyModuleAdmins;
>>>>>>> origin/Donation/Association-"aziz"
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

<<<<<<< HEAD
    public function store(Request $request): RedirectResponse
    {
        Donation::create($request->validate($this->rules()));
=======
    public function store(Request $request, NotifyDonationStatusChanged $notifyStatus): RedirectResponse
    {
        $donation = Donation::create($request->validate($this->rules()));
        $notifyStatus($donation);
>>>>>>> origin/Donation/Association-"aziz"

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

<<<<<<< HEAD
    public function update(Request $request, Donation $donation): RedirectResponse
    {
        $donation->update($request->validate($this->rules()));

=======
    public function update(
        Request $request,
        Donation $donation,
        NotifyDonationStatusChanged $notifyStatus,
        NotifyModuleAdmins $notifyAdmins,
    ): RedirectResponse {
        $previousStatus = $donation->status;
        $donation->update($request->validate($this->rules()));

        if ($donation->status !== $previousStatus) {
            $notifyStatus($donation);
            $notifyAdmins(
                'Statut de don modifié',
                $donation->association->name,
                $donation->status,
                route('admin.donations.show', $donation),
                $donation->trackingReference(),
            );
        }

>>>>>>> origin/Donation/Association-"aziz"
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
<<<<<<< HEAD
=======
            'category' => ['sometimes', 'required', Rule::in(Donation::categories())],
>>>>>>> origin/Donation/Association-"aziz"
            'quantity_items' => ['required', 'integer', 'min:1', 'max:10000'],
            'donation_date' => ['required', 'date'],
            'status' => ['required', Rule::in(Donation::statuses())],
        ];
    }
<<<<<<< HEAD
}
=======
}
>>>>>>> origin/Donation/Association-"aziz"
