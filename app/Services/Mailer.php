<?php

namespace App\Services;

use App\Models\OutboxEmail;
use Illuminate\Support\Facades\Http;

/**
 * Delivers an outbox email through SendGrid. If a SendGrid dynamic-template id
 * is configured for the email's type, it sends via that template (so the design
 * is editable in the SendGrid dashboard) with the email's meta as the template
 * data; otherwise it sends the rendered HTML. If sending isn't enabled the row
 * simply stays 'queued' for preview.
 */
class Mailer
{
    public static function deliver(OutboxEmail $email): bool
    {
        if (!config('rmc.mail_live') || !config('rmc.sendgrid.key')) {
            return false;
        }

        $from = config('rmc.mail_from');
        $templateId = config('rmc.sendgrid.templates.' . $email->template);

        $personalization = ['to' => [['email' => $email->to_email, 'name' => $email->to_name]]];

        $payload = [
            'from' => ['email' => $from['address'], 'name' => $from['name']],
        ];

        if ($templateId) {
            $payload['template_id'] = $templateId;
            $personalization['dynamic_template_data'] = ($email->meta ?? []) + ['subject' => $email->subject];
        } else {
            $payload['subject'] = $email->subject;
            $payload['content'] = [['type' => 'text/html', 'value' => $email->body]];
        }

        $payload['personalizations'] = [$personalization];

        try {
            $resp = Http::withToken(config('rmc.sendgrid.key'))
                ->timeout(20)
                ->post('https://api.sendgrid.com/v3/mail/send', $payload);

            if ($resp->successful()) {
                $email->update(['status' => 'sent', 'sent_at' => now()]);
                return true;
            }
            $email->update(['status' => 'failed', 'meta' => ($email->meta ?? []) + ['error' => 'HTTP ' . $resp->status()]]);
            return false;
        } catch (\Throwable $e) {
            $email->update(['status' => 'failed', 'meta' => ($email->meta ?? []) + ['error' => $e->getMessage()]]);
            return false;
        }
    }
}
