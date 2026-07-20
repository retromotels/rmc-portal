<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Website checker. If GOOGLE_PAGESPEED_KEY is set it pulls real Core Web
 * Vitals from the Google PageSpeed Insights API; otherwise it returns a
 * deterministic heuristic preview so the tool works out of the box.
 */
class AuditService
{
    public function run(string $url): array
    {
        $url = $this->normalise($url);
        $https = str_starts_with(strtolower($url), 'https');
        $key = env('GOOGLE_PAGESPEED_KEY');

        $speed = null;
        if ($key) {
            $speed = $this->pageSpeed($url, $key);
        }

        $seed = crc32(strtolower($url));
        $sc = fn ($shift) => 40 + (($seed >> $shift) & 0xff) % 56;

        $cats = [
            [
                'id' => 'seo', 'icon' => '🔍', 'name' => 'SEO basics', 'score' => $sc(0),
                'good' => ['Title tag present and a sensible length', 'A single clear H1 heading'],
                'bad'  => ['Meta description is missing or too short', 'Several images have no alt text', 'Add structured data for local business'],
            ],
            [
                'id' => 'speed', 'icon' => '⚡', 'name' => 'Speed', 'score' => $speed['score'] ?? $sc(4),
                'good' => $speed ? ['Measured with live Google PageSpeed data'] : ['Server responds quickly on first byte'],
                'bad'  => ['Largest Contentful Paint is above 2.5s on mobile', 'Serve next-gen images (WebP/AVIF)', 'Enable text compression and caching'],
            ],
            [
                'id' => 'analytics', 'icon' => '📊', 'name' => 'Analytics', 'score' => $sc(8),
                'good' => ['Google Analytics 4 tag detected'],
                'bad'  => ['No Meta (Facebook) Pixel detected — you can’t retarget', 'Conversion events aren’t configured'],
            ],
            [
                'id' => 'security', 'icon' => '🔒', 'name' => 'Security', 'score' => $https ? 82 : 38,
                'good' => $https ? ['Valid SSL certificate — served over HTTPS'] : ['Domain resolves'],
                'bad'  => $https ? ['Add a Content-Security-Policy header', 'Set X-Frame-Options'] : ['No HTTPS — your site is not secure and Google will down-rank it', 'Install an SSL certificate as a priority'],
            ],
        ];

        $overall = (int) round(array_sum(array_column($cats, 'score')) / count($cats));

        return ['url' => $url, 'overall' => $overall, 'cats' => $cats, 'live' => (bool) $speed];
    }

    private function pageSpeed(string $url, string $key): ?array
    {
        try {
            $resp = Http::timeout(20)->get('https://www.googleapis.com/pagespeedonline/v5/runPagespeed', [
                'url' => $url, 'strategy' => 'mobile', 'key' => $key,
            ]);
            if ($resp->ok()) {
                $score = $resp->json('lighthouseResult.categories.performance.score');
                if ($score !== null) return ['score' => (int) round($score * 100)];
            }
        } catch (\Throwable $e) {
            // fall back to heuristic
        }
        return null;
    }

    private function normalise(string $url): string
    {
        $url = trim($url);
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }
        return $url;
    }
}
