<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LifetimeAccessReviewed extends Notification
{
    use Queueable;

    public function __construct(public string $status, public ?string $note = null) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $title = match ($this->status) {
            'approved' => 'Lifetime Pro is active',
            'revoked' => 'Lifetime Pro access changed',
            default => 'Lifetime request reviewed',
        };
        $message = match ($this->status) {
            'approved' => 'Your request was approved. Lifetime Pro access is now active on your account.',
            'revoked' => 'Lifetime Pro access was revoked by an administrator.'.($this->note ? ' Reason: '.$this->note : ''),
            default => 'Your request was not approved this time.'.($this->note ? ' Admin note: '.$this->note : ''),
        };
        return [
            'title' => $title,
            'message' => $message,
            'status' => $this->status,
        ];
    }
}
