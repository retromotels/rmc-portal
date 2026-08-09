<?php

namespace App\Services;

use App\Models\AdminNotification;
use App\Models\User;

class Notify
{
    /** Raise an admin portal notification. */
    public static function admin(string $type, string $title, ?string $body = null, ?User $subject = null): void
    {
        AdminNotification::create([
            'type'    => $type,
            'title'   => $title,
            'body'    => $body,
            'user_id' => $subject?->id,
        ]);
    }
}
