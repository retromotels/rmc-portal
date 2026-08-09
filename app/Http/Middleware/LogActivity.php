<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;

class LogActivity
{
    private const LABELS = [
        'dashboard'          => 'Dashboard',
        'registration.index' => 'Property setup',
        'health'             => 'Health check',
        'about'              => 'About Us',
        'faq'                => 'FAQ',
        'account'            => 'Account',
        'details.show'       => 'Complete details',
        'properties.add'     => 'Add property',
    ];

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $u = $request->user();
        if ($u && !$u->isAdmin() && $request->isMethod('get') && !$request->ajax()) {
            $name = optional($request->route())->getName();
            if ($name && isset(self::LABELS[$name])) {
                ActivityLog::create([
                    'user_id'    => $u->id,
                    'account_id' => $u->accountId(),
                    'path'       => $request->path(),
                    'label'      => self::LABELS[$name],
                    'ip'         => $request->ip(),
                    'created_at' => now(),
                ]);
            }
        }

        return $response;
    }
}
