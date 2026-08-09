<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

/**
 * For an authenticated owner, resolve which property (user row) is "current"
 * and share the property list with all views. Admins are skipped.
 */
class ResolveProperty
{
    public function handle(Request $request, Closure $next)
    {
        $u = $request->user();

        if ($u && !$u->isAdmin()) {
            $props   = $u->accountProperties();
            $selected = (int) session('current_property_id');
            $current  = $props->firstWhere('id', $selected)
                ?? $props->firstWhere('id', $u->id)
                ?? $props->first()
                ?? $u;

            $request->attributes->set('currentProperty', $current);
            $request->attributes->set('accountProperties', $props);
            View::share('currentProperty', $current);
            View::share('accountProperties', $props);
        }

        return $next($request);
    }
}
