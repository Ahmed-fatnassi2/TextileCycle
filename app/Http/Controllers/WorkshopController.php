<?php

namespace App\Http\Controllers;

use App\Models\Workshop;

class WorkshopController extends Controller
{
    public function index()
    {
        $workshops = Workshop::withCount('repairRequests')->orderBy('name')->get();
        return view('front.workshops.index', compact('workshops'));
    }

    public function show(Workshop $workshop)
    {
<<<<<<< HEAD
        if (auth()->user()?->role === 'admin') {
            $workshop->load(['repairRequests' => fn ($query) => $query->latest()->take(10)]);
        }

=======
        $workshop->load(['repairRequests' => fn ($query) => $query->latest()->take(10)]);
>>>>>>> origin/Donation/Association-"aziz"
        return view('front.workshops.show', compact('workshop'));
    }
}
