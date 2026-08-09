<?php

namespace App\Http\Controllers;

use App\Models\Setting;

class PageController extends Controller
{
    public function about()
    {
        return view('portal.about', [
            'about' => Setting::get('about', ['title' => '', 'body' => '', 'images' => []]),
        ]);
    }

    public function faq()
    {
        return view('portal.faq', [
            'faq' => Setting::get('faq', []),
        ]);
    }
}
