<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Notification;

class PasswordResetNotification extends Notification
{
    public function __construct(public User $user)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => 'Reset Password',
            'message' => $this->user->name . ' (' . $this->user->email . ') telah mereset password akunnya.',
            'user_id' => $this->user->id,
        ];
    }
}