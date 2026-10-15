<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('front.profile.edit', ['user' => $request->user()]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());
        \App\Models\User::logActivity($request->user(), 'Profil modifié', 'Informations du profil mises à jour.', $request->user());

        return redirect()->route('profile.edit')->with('success', 'Votre profil a été mis à jour.');
    }
}
