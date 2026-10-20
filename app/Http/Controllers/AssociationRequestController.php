<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
use App\Models\Association;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
=======
use App\Actions\NotifyModuleAdmins;
use App\Mail\AssociationRequestConfirmation;
use App\Models\Association;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
>>>>>>> origin/Donation/Association-"aziz"
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

<<<<<<< HEAD
    public function store(Request $request): RedirectResponse
=======
    public function store(Request $request, NotifyModuleAdmins $notifyAdmins): RedirectResponse
>>>>>>> origin/Donation/Association-"aziz"
    {
        abort_if($request->user()->association()->exists(), 409);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:40'],
            'needs_description' => ['required', 'string', 'max:5000'],
        ]);

<<<<<<< HEAD
        $request->user()->association()->create([
=======
        $association = $request->user()->association()->create([
>>>>>>> origin/Donation/Association-"aziz"
            ...$data,
            'status' => Association::STATUS_PENDING,
        ]);

<<<<<<< HEAD
=======
        Mail::to($request->user()->email)->queue(new AssociationRequestConfirmation($association));
        $notifyAdmins(
            'Nouvelle demande d’association',
            $association->name,
            $association->status,
            route('admin.associations.show', $association),
        );

>>>>>>> origin/Donation/Association-"aziz"
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
<<<<<<< HEAD
}
=======
}
>>>>>>> origin/Donation/Association-"aziz"
