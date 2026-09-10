<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Fetches readable page text via Jina Reader (renders JS, returns clean text)
 * and can crawl a site: the homepage plus a few priority internal pages, so the
 * admin AI can report on an external website.
 */
class WebFetcher
{
    /** Internal pages worth reading, by keyword priority. */
    private const PRIORITY = [
        'about', 'room', 'accommodation', 'stay', 'suite', 'rate', 'tariff', 'price',
        'book', 'reservation', 'contact', 'facilit', 'amenit', 'location', 'gallery',
        'special', 'offer', 'deal', 'package', 'dining', 'eat',
    ];

    public function normalise(string $url): string
    {
        $url = trim($url);
        if (!preg_match('~^https?://~i', $url)) {
            $url = 'https://' . $url;
        }
        return $url;
    }

    /** Fetch one page's readable text (null on failure). */
    public function fetch(string $url, int $limit = 8000): ?string
    {
        $base = rtrim((string) config('rmc.ai.scraper.base'), '/') . '/';
        // Default (markdown) output — far richer than 'text' and keeps the links
        // the crawler follows to find internal pages.
        $headers = ['Accept' => 'text/plain'];
        if ($key = config('rmc.ai.scraper.key')) {
            $headers['Authorization'] = 'Bearer ' . $key;
        }

        try {
            $resp = Http::withHeaders($headers)->timeout(35)->get($base . $this->normalise($url));
            if (!$resp->successful()) {
                Log::info('WebFetcher HTTP ' . $resp->status() . ' for ' . $url);
                return null;
            }
            $text = trim($resp->body());
            return $text === '' ? null : mb_substr($text, 0, $limit);
        } catch (\Throwable $e) {
            Log::warning('WebFetcher error for ' . $url . ': ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Crawl a site: homepage + up to (max-1) priority internal pages on the same
     * host. Returns [url => text]. Empty array if the homepage can't be read.
     */
    public function crawl(string $url, ?int $max = null): array
    {
        $max = $max ?: (int) config('rmc.ai.scraper.max_pages', 5);
        $url = $this->normalise($url);
        $host = parse_url($url, PHP_URL_HOST);
        if (!$host) {
            return [];
        }

        $home = $this->fetch($url);
        if ($home === null) {
            return [];
        }
        $pages = [$url => $home];

        // Pull same-host links out of the homepage markdown, rank by priority.
        preg_match_all('~\]\((https?://[^)\s]+)\)~i', $home, $m);
        $candidates = [];
        foreach (array_unique($m[1] ?? []) as $link) {
            $link = rtrim(preg_replace('~#.*$~', '', $link), '/'); // drop #fragments
            if ($link === '') {
                continue;
            }
            $lhost = parse_url($link, PHP_URL_HOST);
            if (!$lhost || stripos($lhost, preg_replace('~^www\.~', '', $host)) === false) {
                continue;
            }
            if (rtrim($url, '/') === $link) {
                continue;
            }
            $path = strtolower((string) parse_url($link, PHP_URL_PATH));
            // Skip images, docs and other non-page assets.
            if (preg_match('~\.(jpe?g|png|gif|webp|svg|ico|pdf|zip|mp4|mov|css|js|woff2?|ttf|eot|xml)$~', $path)) {
                continue;
            }
            $score = 0;
            foreach (self::PRIORITY as $i => $kw) {
                if (str_contains($path, $kw)) {
                    $score += (count(self::PRIORITY) - $i);
                }
            }
            $candidates[$link] = max($candidates[$link] ?? 0, $score);
        }
        arsort($candidates);

        foreach (array_keys($candidates) as $link) {
            if (count($pages) >= $max) {
                break;
            }
            if (isset($pages[$link])) {
                continue;
            }
            if ($text = $this->fetch($link, 6000)) {
                $pages[$link] = $text;
            }
        }

        return $pages;
    }
}
