<?php

namespace App\Http\Controllers;

use App\Models\Setting;

/**
 * Renders the admin-toggleable content modules (Monthly Roundtable, Community).
 * Each is gated by its Setting flag and shows admin-edited content + a link.
 */
class ModulePageController extends Controller
{
    public function roundtable()
    {
        abort_unless(Setting::bool('module_roundtable'), 404);
        return view('tools.module-page', [
            'icon'  => '🎙️',
            'title' => Setting::get('roundtable_title', 'The Monthly Roundtable'),
            'body'  => Setting::get('roundtable_body', ''),
            'link'  => Setting::get('roundtable_link', ''),
            'cta'   => 'Join the next call',
        ]);
    }

    public function community()
    {
        abort_unless(Setting::bool('module_community'), 404);
        return view('tools.module-page', [
            'icon'  => '👥',
            'title' => Setting::get('community_title', 'The Community'),
            'body'  => Setting::get('community_body', ''),
            'link'  => Setting::get('community_link', ''),
            'cta'   => 'Join the community',
        ]);
    }
}
