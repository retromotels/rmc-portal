<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        $user = $this->currentProperty()->load('registrations', 'uploads');

        // C–H are the dashboard tasks (everything not collected at sign-up).
        $tasks = collect(config('rmc.sections'))
            ->reject(fn ($s) => $s['signup'] ?? false)
            ->map(fn ($s, $id) => $s + ['id' => $id]);

        return view('dashboard', [
            'user'  => $user,
            'tasks' => $tasks,
        ]);
    }
}
