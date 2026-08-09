<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;

class ActivityController extends Controller
{
    public function index()
    {
        return view('admin.activity.index', [
            'logs' => ActivityLog::with('user')->orderByDesc('created_at')->limit(300)->get(),
        ]);
    }
}
