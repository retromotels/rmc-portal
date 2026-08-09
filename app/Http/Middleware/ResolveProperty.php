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

        // Admin "view as property" preview across tiers.
        if ($u && $u->isAdmin() && in_array(session('admin_preview_tier'), ['standard', 'growth', 'full'], true)) {
            $tier = session('admin_preview_tier');
            $band = ['standard' => 'small', 'growth' => 'mid', 'full' => 'large'][$tier];
            $preview = new User([
                'name' => $u->name, 'role' => 'owner', 'motel' => 'Preview Property',
                'band' => $band, 'tier' => $tier, 'details_complete' => true,
            ]);
            $preview->id = 0;
            $request->attributes->set('currentProperty', $preview);
            View::share('currentProperty', $preview);
            View::share('adminPreview', $tier);
            return $next($request);
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
