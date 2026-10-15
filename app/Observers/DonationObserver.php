<?php

namespace App\Observers;

use App\Models\Donation;
use Illuminate\Support\Facades\Auth;

class DonationObserver
{
    public function created(Donation $donation): void
    {
        $this->recordStatus($donation, null, $donation->status);
    }

    public function updated(Donation $donation): void
    {
        if (! $donation->wasChanged('status')) {
            return;
        }

        $this->recordStatus($donation, $donation->getOriginal('status'), $donation->status);
    }

    private function recordStatus(Donation $donation, ?string $from, string $to): void
    {
        $donation->statusHistory()->create([
            'from_status' => $from,
            'to_status' => $to,
            'changed_by' => Auth::id(),
        ]);
    }
}
