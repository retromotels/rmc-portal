<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\SiteView;
use Illuminate\Http\Request;

/**
 * Public-facing microsites at /motel/{key} and /motel/{key}/{page}.
 *  - {key} = a published site's slug  -> public, indexable
 *  - {key} = a site's preview_token   -> private, noindex, password-gated
 */
class PublicSiteController extends Controller
{
    public function show(Request $r, string $key)
    {
        $res = $this->resolve($key);

        if ($res['gate']) {
            $this->log($res['site'], 'preview', false, $r);
            return $this->gate($res['site']);
        }

        if ($res['indexable']) $this->log($res['site'], 'public', true, $r);

        return $this->renderSite($res, null);
    }

    public function page(Request $r, string $key, string $page)
    {
        $res = $this->resolve($key);

        if ($res['gate']) {
            $this->log($res['site'], 'preview', false, $r);
            return $this->gate($res['site']);
        }

        $pg = $res['site']->pages()->where('slug', $page)->first();
        abort_unless($pg, 404);
        if (!$pg->visible && $res['indexable']) abort(404);

        if ($res['indexable']) $this->log($res['site'], 'public', true, $r);

        return $this->renderSite($res, $pg);
    }

    /** Render the site with its bespoke per-theme template (falls back to the generic one). */
    private function renderSite(array $res, $page)
    {
        $site = $res['site'];
        $themeView = 'sites.themes.' . $site->theme;
        $view = view()->exists($themeView) ? $themeView : ($page ? 'sites.page' : 'sites.show');

        $resp = response()->view($view, [
            'site' => $site, 'preview' => $res['preview'], 'indexable' => $res['indexable'], 'page' => $page,
        ]);
        if ($res['preview']) $resp->header('X-Robots-Tag', 'noindex, nofollow');
        return $resp;
    }

    public function unlock(Request $r, string $key)
    {
        $site = Site::where('preview_token', $key)->firstOrFail();
        $entered = trim((string) $r->input('password'));

        if (strcasecmp($entered, $site->preview_password) !== 0) {
            return $this->gate($site, 'That password is not correct.', 422);
        }

        session()->put($this->unlockKey($site), true);
        $this->log($site, 'preview', true, $r);
        return redirect('/motel/' . $site->preview_token);
    }

    /* -------- helpers -------- */

    /** @return array{site:Site,preview:bool,indexable:bool,gate:bool} */
    private function resolve(string $key): array
    {
        $public = Site::where('published', true)->where('slug', $key)->first();
        if ($public) {
            return ['site' => $public, 'preview' => false, 'indexable' => true, 'gate' => false];
        }

        $site = Site::where('preview_token', $key)->firstOrFail();
        $unlocked = (bool) session()->get($this->unlockKey($site));
        return ['site' => $site, 'preview' => true, 'indexable' => false, 'gate' => !$unlocked];
    }

    private function render(string $view, array $data, bool $preview)
    {
        $resp = response()->view($view, $data);
        if ($preview) $resp->header('X-Robots-Tag', 'noindex, nofollow');
        return $resp;
    }

    private function gate(Site $site, ?string $error = null, int $status = 200)
    {
        return response()
            ->view('sites.gate', ['site' => $site, 'error' => $error], $status)
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    private function unlockKey(Site $site): string
    {
        return 'site_unlocked_' . $site->preview_token;
    }

    private function log(Site $site, string $kind, bool $unlocked, Request $r): void
    {
        SiteView::create([
            'site_id'    => $site->id,
            'kind'       => $kind,
            'unlocked'   => $unlocked,
            'ip'         => $r->ip(),
            'user_agent' => substr((string) $r->userAgent(), 0, 500),
            'created_at' => now(),
        ]);
    }
}
