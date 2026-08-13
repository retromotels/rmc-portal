<?php

namespace App\Http\Middleware;

use App\Models\User;
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

        // Admin "view as" a specific property — sees that property's real portal.
        if ($u && $u->isAdmin() && ($pid = (int) session('admin_preview_user_id'))) {
            $preview = User::where('role', 'owner')->find($pid);
            if ($preview) {
                $props    = $preview->accountProperties();
                $selected = (int) session('current_property_id');
                $current  = $props->firstWhere('id', $selected)
                    ?? $props->firstWhere('id', $preview->id)
                    ?? $props->first()
                    ?? $preview;

                $request->attributes->set('currentProperty', $current);
                $request->attributes->set('accountProperties', $props);
                View::share('currentProperty', $current);
                View::share('accountProperties', $props);
                View::share('adminPreview', $preview);   // the real account being viewed
                return $next($request);
            }
            session()->forget('admin_preview_user_id');  // stale id — clear it
        }

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
