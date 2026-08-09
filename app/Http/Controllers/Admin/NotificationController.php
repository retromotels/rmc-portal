<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;

class NotificationController extends Controller
{
    public function index()
    {
        return view('admin.notifications.index', [
            'notifications' => AdminNotification::with('user')->orderByDesc('created_at')->limit(200)->get(),
        ]);
    }

    public function read(AdminNotification $notification)
    {
        $notification->update(['read_at' => now()]);
        return back();
    }

    public function readAll()
    {
        AdminNotification::whereNull('read_at')->update(['read_at' => now()]);
        return back()->with('status', 'All notifications marked read.');
    }
}
