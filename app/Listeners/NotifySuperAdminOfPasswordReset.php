<?php

namespace App\Listeners;

use App\Models\User;
use App\Notifications\PasswordResetNotification;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Notification;

class NotifySuperAdminOfPasswordReset
{
    public function handle(PasswordReset $event): void
    {
        $superAdmins = User::where('role', 'super_admin')
            ->where('id', '!=', $event->user->id)
            ->get();

        if ($superAdmins->isEmpty()) {
            return;
        }

        Notification::send($superAdmins, new PasswordResetNotification($event->user));
    }
}