<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\SiteView;
use Illuminate\Http\Request;

/**
 * Public-facing microsites at /motel/{key}.
 *  - {key} = a published site's slug  -> public, indexable booking page
 *  - {key} = a site's preview_token   -> private, noindex, password-gated
 */
class PublicSiteController extends Controller
{
    public function show(Request $r, string $key)
    {
        // 1) Published public page (indexable)
        $public = Site::where('published', true)->where('slug', $key)->first();
        if ($public) {
            $this->log($public, 'public', true, $r);
            return response()->view('sites.show', [
                'site' => $public, 'preview' => false, 'indexable' => true,
            ]);
        }

        // 2) Private preview by token
        $site = Site::where('preview_token', $key)->firstOrFail();

        if (!session()->get($this->unlockKey($site))) {
            $this->log($site, 'preview', false, $r);      // gate view
            return response()
                ->view('sites.gate', ['site' => $site, 'error' => null])
                ->header('X-Robots-Tag', 'noindex, nofollow');
        }

        return response()
            ->view('sites.show', ['site' => $site, 'preview' => true, 'indexable' => false])
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    public function unlock(Request $r, string $key)
    {
        $site = Site::where('preview_token', $key)->firstOrFail();
        $entered = trim((string) $r->input('password'));

        if (strcasecmp($entered, $site->preview_password) !== 0) {
            return response()
                ->view('sites.gate', ['site' => $site, 'error' => 'That password is not correct.'], 422)
                ->header('X-Robots-Tag', 'noindex, nofollow');
        }

        session()->put($this->unlockKey($site), true);
        $this->log($site, 'preview', true, $r);           // successful access
        return redirect('/motel/' . $site->preview_token);
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
