<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Fetches a creator's PUBLIC Instagram numbers to pre-fill the Vetting Desk.
 *
 * Instagram blocks direct server scraping, so reliable lookups go through a
 * third-party data API (RapidAPI) when RMC_IG_RAPIDAPI_KEY is set. Without a
 * key we make a best-effort public attempt (og:description meta), which
 * Instagram usually blocks — in which case the property just enters the numbers.
 *
 * Returns: ['ok' => bool, 'message' => string, 'source' => string, 'fields' => [...]]
 */
class InstagramLookup
{
    public function lookup(string $handle): array
    {
        $handle = ltrim(trim($handle), '@ ');
        if ($handle === '') {
            return $this->fail('Enter a handle first.');
        }

        if (config('rmc.vetting.ig.enabled')) {
            try {
                $viaApi = $this->viaRapidApi($handle);
                if ($viaApi) {
                    return $viaApi;
                }
            } catch (\Throwable $e) {
                Log::warning('InstagramLookup RapidAPI failed: ' . $e->getMessage());
            }
        }

        // Best-effort keyless attempt (usually blocked from server IPs).
        try {
            $viaPublic = $this->viaPublicPage($handle);
            if ($viaPublic) {
                return $viaPublic;
            }
        } catch (\Throwable $e) {
            Log::info('InstagramLookup public attempt failed: ' . $e->getMessage());
        }

        return $this->fail(
            config('rmc.vetting.ig.enabled')
                ? "Couldn't read @{$handle} automatically — please enter the numbers from their profile."
                : "Auto-lookup isn't switched on yet — enter the numbers from their public profile below."
        );
    }

    /* ------------------------------------------------------------ providers */

    private function viaRapidApi(string $handle): ?array
    {
        $host = config('rmc.vetting.ig.rapidapi_host');
        $headers = [
            'x-rapidapi-key'  => config('rmc.vetting.ig.rapidapi_key'),
            'x-rapidapi-host' => $host,
        ];

        // Profile info
        $info = Http::withHeaders($headers)->timeout(15)
            ->get("https://{$host}/v1/info", ['username_or_id_or_url' => $handle]);
        if (!$info->successful()) {
            Log::warning('InstagramLookup info HTTP ' . $info->status());
            return null;
        }
        $d = $info->json('data') ?? $info->json();

        $followers = $this->num($d, ['follower_count', 'followers', 'edge_followed_by.count']);
        $following = $this->num($d, ['following_count', 'followings', 'edge_follow.count']);
        $postsN    = $this->num($d, ['media_count', 'posts', 'edge_owner_to_timeline_media.count']);
        $private   = (bool) data_get($d, 'is_private', false);
        $fullName  = data_get($d, 'full_name');
        $bio       = data_get($d, 'biography');

        // Recent posts → avg likes/comments + locations + captions
        $avgLikes = $avgComments = null;
        $locations = [];
        $captions = [];
        $ppw = null;

        if (!$private) {
            try {
                $posts = Http::withHeaders($headers)->timeout(15)
                    ->get("https://{$host}/v1.2/posts", ['username_or_id_or_url' => $handle]);
                $items = data_get($posts->json(), 'data.items', data_get($posts->json(), 'items', []));
                if (is_array($items) && $items) {
                    $items = array_slice($items, 0, 12);
                    $likes = $comments = 0;
                    $times = [];
                    foreach ($items as $it) {
                        $likes    += (int) data_get($it, 'like_count', data_get($it, 'likes', 0));
                        $comments += (int) data_get($it, 'comment_count', data_get($it, 'comments', 0));
                        if ($loc = data_get($it, 'location.name', data_get($it, 'location'))) {
                            $locations[] = is_string($loc) ? $loc : (string) data_get($it, 'location.name');
                        }
                        if ($cap = data_get($it, 'caption.text', data_get($it, 'caption'))) {
                            $captions[] = is_string($cap) ? $cap : '';
                        }
                        if ($t = data_get($it, 'taken_at', data_get($it, 'taken_at_timestamp'))) {
                            $times[] = (int) $t;
                        }
                    }
                    $n = count($items);
                    $avgLikes = (int) round($likes / max(1, $n));
                    $avgComments = (int) round($comments / max(1, $n));
                    if (count($times) >= 2) {
                        $span = (max($times) - min($times)) / 86400 / 7; // weeks
                        $ppw = $span > 0 ? round((count($times) - 1) / $span, 1) : null;
                    }
                }
            } catch (\Throwable $e) {
                Log::info('InstagramLookup posts fetch failed: ' . $e->getMessage());
            }
        }

        if ($followers === null) {
            return null;
        }

        return [
            'ok'      => true,
            'source'  => 'api',
            'message' => $private
                ? "@{$handle} is a private account — got the headline numbers; add engagement manually."
                : "Pulled @{$handle} from Instagram. Review and adjust anything before scoring.",
            'fields'  => array_filter([
                'followers'      => $followers,
                'following'      => $following,
                'posts'          => $postsN,
                'avg_likes'      => $avgLikes,
                'avg_comments'   => $avgComments,
                'posts_per_week' => $ppw,
                'account_type'   => $private ? 'Personal' : null,
                'based_location' => null,
                'post_locations' => $locations ? implode("\n", array_slice(array_filter($locations), 0, 12)) : null,
                'captions'       => $captions ? trim(implode("\n", array_slice(array_filter($captions), 0, 6))) : null,
                'full_name'      => $fullName,
                'bio'            => $bio,
            ], fn ($v) => $v !== null && $v !== ''),
        ];
    }

    /** Best-effort: parse the public profile og:description ("X Followers, Y Following, Z Posts"). */
    private function viaPublicPage(string $handle): ?array
    {
        $resp = Http::withHeaders([
            'User-Agent'      => 'Mozilla/5.0 (compatible; RMCBot/1.0)',
            'Accept-Language' => 'en',
        ])->timeout(12)->get("https://www.instagram.com/{$handle}/");

        if (!$resp->successful()) {
            return null;
        }
        $html = $resp->body();
        if (!preg_match('/<meta[^>]+property="og:description"[^>]+content="([^"]+)"/i', $html, $m)) {
            return null;
        }
        $desc = html_entity_decode($m[1]);
        // e.g. "1,840 Followers, 640 Following, 210 Posts - See Instagram photos…"
        if (!preg_match('/([\d.,KMkm]+)\s+Followers?,\s*([\d.,KMkm]+)\s+Following,\s*([\d.,KMkm]+)\s+Posts?/i', $desc, $mm)) {
            return null;
        }

        return [
            'ok'      => true,
            'source'  => 'public',
            'message' => "Read @{$handle}'s public counts. Add avg likes/comments and post locations to score fully.",
            'fields'  => array_filter([
                'followers' => $this->parseCount($mm[1]),
                'following' => $this->parseCount($mm[2]),
                'posts'     => $this->parseCount($mm[3]),
            ]),
        ];
    }

    /* -------------------------------------------------------------- helpers */

    private function fail(string $message): array
    {
        return ['ok' => false, 'source' => 'manual', 'message' => $message, 'fields' => []];
    }

    private function num(array $d, array $keys): ?int
    {
        foreach ($keys as $k) {
            $v = data_get($d, $k);
            if (is_numeric($v)) {
                return (int) $v;
            }
        }
        return null;
    }

    private function parseCount(string $s): int
    {
        $s = strtolower(str_replace(',', '', trim($s)));
        if (str_ends_with($s, 'k')) {
            return (int) round((float) $s * 1000);
        }
        if (str_ends_with($s, 'm')) {
            return (int) round((float) $s * 1000000);
        }
        return (int) $s;
    }
}
