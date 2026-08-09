<?php

namespace App\Services;

use App\Models\OutboxEmail;
use App\Models\User;
use Illuminate\Support\Facades\View;

/**
 * Central mailer. Right now every message is recorded in the outbox_emails
 * table with status 'queued' so the admin can preview it. When SendGrid /
 * Twilio is connected (Phase 7), a flush step will actually send the queued
 * rows and mark them 'sent'.
 */
class Outbox
{
    public static function queue(string $template, string $toEmail, ?string $toName, string $subject, string $bodyHtml, array $meta = []): OutboxEmail
    {
        return OutboxEmail::create([
            'template'  => $template,
            'to_email'  => $toEmail,
            'to_name'   => $toName,
            'subject'   => $subject,
            'body'      => $bodyHtml,
            'meta'      => $meta,
            'status'    => 'queued',
        ]);
    }

    public static function welcome(User $u): void
    {
        self::queue(
            'welcome', $u->email, $u->name,
            'Welcome to the Retro Motel Collective',
            View::make('emails.welcome', ['user' => $u])->render(),
            ['user_id' => $u->id]
        );
    }

    public static function adminNewSignup(User $u): void
    {
        $body = View::make('emails.admin_new_signup', ['user' => $u])->render();
        foreach (config('rmc.admin_emails', []) as $admin) {
            self::queue('admin_new_signup', $admin, null, 'New property signed up: ' . ($u->motel ?: $u->name), $body, ['user_id' => $u->id]);
        }
    }

    public static function pendingReminder(User $u): void
    {
        self::queue(
            'pending_reminder', $u->email, $u->name,
            'Finish setting up your Retro Motel Collective profile',
            View::make('emails.pending_reminder', ['user' => $u])->render(),
            ['user_id' => $u->id]
        );
    }

    public static function passwordReset(User $u, string $url): void
    {
        self::queue(
            'password_reset', $u->email, $u->name,
            'Reset your Retro Motel Collective password',
            View::make('emails.password_reset', ['user' => $u, 'url' => $url])->render(),
            ['user_id' => $u->id]
        );
    }

    public static function healthRequest(User $u, string $label): void
    {
        $body = View::make('emails.health_request', ['user' => $u, 'label' => $label])->render();
        foreach (config('rmc.admin_emails', []) as $admin) {
            self::queue('health_request', $admin, null, 'Health-check request: ' . $label . ' — ' . ($u->motel ?: $u->name), $body, ['user_id' => $u->id, 'label' => $label]);
        }
    }
}
