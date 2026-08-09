<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function edit()
    {
        return view('admin.content.edit', [
            'banner' => Setting::get('welcome_banner', ['title' => '', 'copy' => '']),
            'about'  => Setting::get('about', ['title' => '', 'body' => '', 'images' => []]),
            'faq'    => Setting::get('faq', []),
        ]);
    }

    public function update(Request $r)
    {
        Setting::put('welcome_banner', [
            'title' => trim((string) $r->input('banner_title')),
            'copy'  => trim((string) $r->input('banner_copy')),
        ]);

        Setting::put('about', [
            'title'  => trim((string) $r->input('about_title')),
            'body'   => trim((string) $r->input('about_body')),
            'images' => $this->lines((string) $r->input('about_images')),
        ]);

        // FAQ: paired arrays; keep rows with a question.
        $qs = (array) $r->input('faq_q', []);
        $as = (array) $r->input('faq_a', []);
        $faq = [];
        foreach ($qs as $i => $q) {
            $q = trim((string) $q);
            $a = trim((string) ($as[$i] ?? ''));
            if ($q !== '') $faq[] = ['q' => $q, 'a' => $a];
        }
        Setting::put('faq', $faq);

        return redirect()->route('admin.content.edit')->with('status', 'Content saved.');
    }

    private function lines(string $text): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $text))
            ->map(fn ($l) => trim($l))->filter()->values()->all();
    }
}
