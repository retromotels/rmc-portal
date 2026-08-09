<?php

namespace App\Services;

use App\Models\OutboxEmail;
use App\Models\User;
use Illuminate\Support\Facades\View;

/**
 * Central mailer. Every message is recorded in the outbox_emails table (so the
 * admin can preview it) and then handed to Mailer::deliver(), which sends it via
 * SendGrid when live sending is enabled. The meta array doubles as the dynamic
 * data for SendGrid dynamic templates.
 */
class Outbox
{
    public static function queue(string $template, string $toEmail, ?string $toName, string $subject, string $bodyHtml, array $meta = []): OutboxEmail
    {
        $email = OutboxEmail::create([
            'template' => $template,
            'to_email' => $toEmail,
            'to_name'  => $toName,
            'subject'  => $subject,
            'body'     => $bodyHtml,
            'meta'     => $meta,
            'status'   => 'queued',
        ]);

        Mailer::deliver($email);

        return $email;
    }

    public static function welcome(User $u): void
    {
        self::queue(
            'welcome', $u->email, $u->name,
            'Welcome to the Retro Motel Collective',
            View::make('emails.welcome', ['user' => $u])->render(),
            ['user_id' => $u->id, 'name' => $u->name, 'motel' => $u->motel, 'dashboard_url' => url('/dashboard')]
        );
    }

    public static function adminNewSignup(User $u): void
    {
        $body = View::make('emails.admin_new_signup', ['user' => $u])->render();
        $data = [
            'user_id' => $u->id, 'motel' => $u->motel, 'name' => $u->name, 'email' => $u->email,
            'registered' => $u->created_at?->format('j M Y, g:ia'), 'admin_url' => url('/admin/motels'),
        ];
        foreach (config('rmc.admin_emails', []) as $admin) {
            self::queue('admin_new_signup', $admin, null, 'New property signed up: ' . ($u->motel ?: $u->name), $body, $data);
        }
    }

    public static function pendingReminder(User $u): void
    {
        self::queue(
            'pending_reminder', $u->email, $u->name,
            'Finish setting up your Retro Motel Collective profile',
            View::make('emails.pending_reminder', ['user' => $u])->render(),
            ['user_id' => $u->id, 'name' => $u->name, 'motel' => $u->motel, 'login_url' => url('/login')]
        );
    }

    public static function passwordReset(User $u, string $url): void
    {
        self::queue(
            'password_reset', $u->email, $u->name,
            'Reset your Retro Motel Collective password',
            View::make('emails.password_reset', ['user' => $u, 'url' => $url])->render(),
            ['user_id' => $u->id, 'name' => $u->name, 'reset_url' => $url]
        );
    }

    public static function healthRequest(User $u, string $label): void
    {
        $body = View::make('emails.health_request', ['user' => $u, 'label' => $label])->render();
        $data = ['user_id' => $u->id, 'label' => $label, 'motel' => $u->motel, 'name' => $u->name, 'email' => $u->email];
        foreach (config('rmc.admin_emails', []) as $admin) {
            self::queue('health_request', $admin, null, 'Health-check request: ' . $label . ' — ' . ($u->motel ?: $u->name), $body, $data);
        }
    }
}
