<?php

namespace App\Actions;

use App\Mail\DonationStatusNotification;
use App\Models\Donation;
use Illuminate\Support\Facades\Mail;

class NotifyDonationStatusChanged
{
    public function __invoke(Donation $donation): void
    {
        $association = $donation->association()->with('user')->firstOrFail();

        Mail::to($association->user->email)->queue(new DonationStatusNotification(
            $donation,
            $donation->status,
            now(),
            route('donations.track', $donation->tracking_token),
        ));
    }
}
