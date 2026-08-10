<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ContentController extends Controller
{
    public function edit()
    {
        return view('admin.content.edit', [
            'banner' => Setting::get('welcome_banner', ['title' => '', 'copy' => '', 'image' => '']),
            'about'  => Setting::get('about', ['title' => '', 'body' => '', 'images' => []]),
            'faq'    => Setting::get('faq', []),
        ]);
    }

    public function update(Request $r)
    {
        $r->validate([
            'banner_image'    => ['nullable', 'image', 'max:6144'],
            'about_images.*'  => ['nullable', 'image', 'max:6144'],
        ]);

        /* ---- Welcome banner (with optional image) ---- */
        $banner = Setting::get('welcome_banner', []);
        $bannerImage = $banner['image'] ?? '';
        if ($r->boolean('banner_image_remove')) $bannerImage = '';
        if ($r->hasFile('banner_image')) $bannerImage = $this->storeImage($r->file('banner_image'));

        Setting::put('welcome_banner', [
            'title' => trim((string) $r->input('banner_title')),
            'copy'  => trim((string) $r->input('banner_copy')),
            'image' => $bannerImage,
        ]);

        /* ---- About Us (existing images minus removed + pasted URLs + uploads) ---- */
        $existing = Setting::get('about', [])['images'] ?? [];
        $removed  = (array) $r->input('about_remove', []);
        $keep     = array_values(array_diff($existing, $removed));
        $urls     = $this->lines((string) $r->input('about_images_urls', ''));
        $uploaded = [];
        foreach ((array) $r->file('about_images', []) as $file) {
            if ($file instanceof UploadedFile) $uploaded[] = $this->storeImage($file);
        }

        Setting::put('about', [
            'title'  => trim((string) $r->input('about_title')),
            'body'   => trim((string) $r->input('about_body')),
            'images' => array_values(array_unique(array_merge($keep, $urls, $uploaded))),
        ]);

        /* ---- FAQ ---- */
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

    /** Store an uploaded image in the public webroot and return its URL. */
    private function storeImage(UploadedFile $file): string
    {
        $dir = public_path('uploads/content');
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        $name = Str::random(24) . '.' . strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $file->move($dir, $name);
        return url('uploads/content/' . $name);
    }

    private function lines(string $text): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $text))
            ->map(fn ($l) => trim($l))->filter()->values()->all();
    }
}
