<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OutboxEmail;

class OutboxController extends Controller
{
    public function index()
    {
        return view('admin.outbox.index', [
            'emails' => OutboxEmail::orderByDesc('created_at')->limit(200)->get(),
        ]);
    }

    public function show(OutboxEmail $outbox)
    {
        return view('admin.outbox.show', ['email' => $outbox]);
    }
}
