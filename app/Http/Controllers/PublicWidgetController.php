<?php

namespace App\Http\Controllers;

use App\Models\ChatWidget;

class PublicWidgetController extends Controller
{
    /**
     * Serve the self-contained widget loader JS for a property's token.
     * Config is injected fresh each request, so editing + saving in the
     * portal updates the widget on the property's website automatically.
     */
    public function js(string $token)
    {
        $w = ChatWidget::where('token', $token)->first();
        $cfg = ($w && $w->enabled) ? ($w->config ?? []) : null;

        $js = view('widget.loader', ['cfg' => $cfg])->render();

        return response($js, 200)
            ->header('Content-Type', 'application/javascript; charset=utf-8')
            ->header('Cache-Control', 'public, max-age=120')
            ->header('Access-Control-Allow-Origin', '*');
    }
}
