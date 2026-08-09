<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OutboxEmail;
use App\Services\Mailer;

class OutboxController extends Controller
{
    public function index()
    {
        return view('admin.outbox.index', [
            'emails' => OutboxEmail::orderByDesc('created_at')->limit(200)->get(),
            'live'   => (bool) config('rmc.mail_live') && (bool) config('rmc.sendgrid.key'),
        ]);
    }

    public function flush()
    {
        $sent = 0;
        foreach (OutboxEmail::where('status', 'queued')->get() as $e) {
            if (Mailer::deliver($e)) $sent++;
        }
        return back()->with('status', $sent > 0 ? "{$sent} queued email(s) sent." : 'Nothing sent — check that live sending + the SendGrid key are configured.');
    }

    public function show(OutboxEmail $outbox)
    {
        return view('admin.outbox.show', ['email' => $outbox]);
    }
}
