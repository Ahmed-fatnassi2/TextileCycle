<?php

namespace App\Actions;

use App\Mail\AdminModuleActivityNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyModuleAdmins
{
    public function __invoke(
        string $activity,
        string $name,
        string $status,
        string $detailsUrl,
        ?string $reference = null,
    ): void {
        $admins = User::query()->where('role', 'admin')->get(['id', 'email']);

        if ($admins->isEmpty()) {
            Log::error('Cannot notify admins: no admin users are configured.', [
                'activity' => $activity,
                'name' => $name,
            ]);

            return;
        }

        foreach ($admins as $admin) {
            Mail::to($admin->email)->queue(new AdminModuleActivityNotification(
                $activity,
                $name,
                $status,
                $detailsUrl,
                $reference,
            ));
        }
    }
}
