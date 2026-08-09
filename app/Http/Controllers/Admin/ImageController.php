<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Per-property image harvester: pulls photos from the property's website
 * (and an optional extra URL, e.g. a Booking.com listing) and stores them
 * on our side so the team can use them for audits and marketing.
 */
class ImageController extends Controller
{
    private function dir(User $u): string
    {
        return 'property-images/' . $u->id;
    }

    public function index(User $user)
    {
        abort_if($user->isAdmin(), 404);
        $dir = $this->dir($user);
        $files = Storage::disk('local')->exists($dir) ? Storage::disk('local')->files($dir) : [];
        return view('admin.images.index', [
            'property' => $user,
            'files'    => collect($files)->map(fn ($f) => basename($f))->all(),
            'website'  => $user->sectionData('A')['website'] ?? null,
        ]);
    }

    public function pull(Request $request, User $user)
    {
        abort_if($user->isAdmin(), 404);
        $request->validate(['extra_url' => ['nullable', 'url']]);

        $pages = [];
        if ($site = ($user->sectionData('A')['website'] ?? null)) $pages[] = $this->normalize($site);
        if ($request->filled('extra_url')) $pages[] = $request->input('extra_url');

        if (empty($pages)) {
            return back()->with('status', 'No website on file — add one below (or in the property’s setup) and try again.');
        }

        $saved = 0;
        foreach ($pages as $p) $saved += $this->grab($p, $user);

        return back()->with('status', $saved > 0
            ? "Pulled and stored {$saved} image(s)."
            : 'No images could be pulled — the site may block automated access, or have no usable photos.');
    }

    public function raw(User $user, string $file)
    {
        $path = $this->dir($user) . '/' . basename($file);
        abort_unless(Storage::disk('local')->exists($path), 404);
        return Storage::disk('local')->response($path);
    }

    public function download(User $user, string $file)
    {
        $path = $this->dir($user) . '/' . basename($file);
        abort_unless(Storage::disk('local')->exists($path), 404);
        return Storage::disk('local')->download($path);
    }

    public function zip(User $user)
    {
        $dir = $this->dir($user);
        $files = Storage::disk('local')->exists($dir) ? Storage::disk('local')->files($dir) : [];
        abort_if(empty($files), 404);

        $zipPath = storage_path('app/' . $dir . '.zip');
        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        foreach ($files as $f) $zip->addFile(storage_path('app/' . $f), basename($f));
        $zip->close();

        return response()->download($zipPath, ($user->motel ?: 'property') . '-images.zip')->deleteFileAfterSend(true);
    }

    /* -------- harvesting -------- */

    private function grab(string $pageUrl, User $user): int
    {
        try {
            $resp = Http::withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; RMCImageFetch/1.0)'])->timeout(20)->get($pageUrl);
            if (!$resp->ok()) return 0;
            $html = $resp->body();
        } catch (\Throwable $e) {
            return 0;
        }

        $base = $this->baseUrl($pageUrl);
        $urls = [];
        if (preg_match_all('/<meta[^>]+(?:property|name)=["\'](?:og:image|twitter:image)["\'][^>]+content=["\']([^"\']+)/i', $html, $om)) {
            foreach ($om[1] as $u) $urls[] = $u;
        }
        if (preg_match_all('/<img\b[^>]*?(?:data-src|src)\s*=\s*("([^"]+)"|\'([^\']+)\')/i', $html, $mm, PREG_SET_ORDER)) {
            foreach ($mm as $x) $urls[] = $x[2] !== '' ? $x[2] : ($x[3] ?? '');
        }

        $seen = [];
        $saved = 0;
        foreach ($urls as $u) {
            if ($saved >= 25) break;
            $abs = $this->absolute($u, $base);
            if (!$abs || !preg_match('/\.(jpe?g|png|webp)(\?|$)/i', $abs)) continue;
            if (preg_match('/(logo|icon|sprite|favicon|pixel|spacer|1x1|placeholder)/i', $abs)) continue;
            if (isset($seen[$abs])) continue;
            $seen[$abs] = true;

            try {
                $img = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])->timeout(15)->get($abs);
                if (!$img->ok()) continue;
                $body = $img->body();
                if (strlen($body) < 3000) continue;   // skip tiny/blank
                $ext = preg_match('/\.(png|webp)(\?|$)/i', $abs, $em) ? strtolower($em[1]) : 'jpg';
                Storage::disk('local')->put($this->dir($user) . '/' . md5($abs) . '.' . $ext, $body);
                $saved++;
            } catch (\Throwable $e) {
                // skip this image
            }
        }
        return $saved;
    }

    private function normalize(string $url): string
    {
        return preg_match('#^https?://#i', $url) ? $url : 'https://' . $url;
    }

    private function baseUrl(string $url): string
    {
        $p = parse_url($url);
        return ($p['scheme'] ?? 'https') . '://' . ($p['host'] ?? '');
    }

    private function absolute(string $src, string $base): ?string
    {
        $src = trim(html_entity_decode($src));
        if ($src === '' || str_starts_with($src, 'data:')) return null;
        if (str_starts_with($src, '//')) return 'https:' . $src;
        if (preg_match('#^https?://#i', $src)) return $src;
        if (str_starts_with($src, '/')) return rtrim($base, '/') . $src;
        return rtrim($base, '/') . '/' . ltrim($src, '/');
    }
}
