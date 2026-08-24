<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

/**
 * Admin control panel for optional member modules. Toggle visibility and edit
 * the content members see, without a deploy.
 */
class ModulesController extends Controller
{
    public function index()
    {
        return view('admin.modules.index');
    }

    public function update(Request $r)
    {
        $data = $r->validate([
            'roundtable_title' => ['nullable', 'string', 'max:120'],
            'roundtable_body'  => ['nullable', 'string', 'max:4000'],
            'roundtable_link'  => ['nullable', 'url', 'max:400'],
            'community_title'  => ['nullable', 'string', 'max:120'],
            'community_body'   => ['nullable', 'string', 'max:4000'],
            'community_link'   => ['nullable', 'url', 'max:400'],
        ]);

        foreach (['module_ai_assist', 'module_roundtable', 'module_community'] as $flag) {
            Setting::put($flag, $r->boolean($flag) ? '1' : '0');
        }
        foreach ($data as $key => $value) {
            Setting::put($key, (string) ($value ?? ''));
        }

        return back()->with('status', 'Modules saved.');
    }
}
