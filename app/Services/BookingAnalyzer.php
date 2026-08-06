<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Best-effort analyzer for a Booking.com (or similar OTA) listing URL.
 * Booking.com aggressively blocks automated requests, so this degrades
 * gracefully: whatever it can read is surfaced and used to pre-tick
 * checklist items; the rest is left for the admin to verify manually.
 */
class BookingAnalyzer
{
    /** @return array{pulled: array, ticks: array<string,bool>} */
    public function analyze(string $url): array
    {
        $pulled = [
            'name' => null, 'description' => null, 'image_count' => 0,
            'rating' => null, 'review_count' => null, 'price' => null,
            'address' => null, 'stars' => null,
            'ok' => false, 'blocked' => false, 'error' => null,
        ];

        try {
            $resp = Http::withHeaders([
                'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
                'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en-AU,en;q=0.9',
            ])->timeout(20)->get($url);

            $status = $resp->status();
            $html = $resp->body();

            if ($status === 403 || $status === 429 || $status >= 500) {
                $pulled['blocked'] = true;
                $pulled['error'] = "The listing returned HTTP {$status} — Booking.com likely blocked the automated request. Complete the checklist manually.";
                return ['pulled' => $pulled, 'ticks' => []];
            }
            if (!$resp->successful()) {
                $pulled['error'] = "Fetch returned HTTP {$status}.";
                return ['pulled' => $pulled, 'ticks' => []];
            }
        } catch (\Throwable $e) {
            $pulled['error'] = 'Could not reach the URL: ' . $e->getMessage();
            return ['pulled' => $pulled, 'ticks' => []];
        }

        $meta = $this->metaTags($html);
        $pulled['name']        = $this->clean($meta['og:title'] ?? $this->titleTag($html));
        $pulled['description'] = $this->clean($meta['og:description'] ?? $meta['description'] ?? null);

        // JSON-LD (schema.org Hotel/LodgingBusiness) — Booking often embeds this.
        foreach ($this->jsonLd($html) as $node) {
            $this->applyJsonLd($node, $pulled);
        }

        // Photo count: JSON-LD images + og:image + booking CDN images in the HTML.
        $imgs = [];
        if (!empty($meta['og:image'])) $imgs[$meta['og:image']] = true;
        if (preg_match_all('#https?://[^"\'\s]+?bstatic\.com/[^"\'\s]+?\.(?:jpe?g|png|webp)#i', $html, $m)) {
            foreach ($m[0] as $u) $imgs[$u] = true;
        }
        $pulled['image_count'] = max($pulled['image_count'], count($imgs));

        // Review score fallback (e.g. data-testid review score like 8.7)
        if ($pulled['rating'] === null && preg_match('/"reviewScore"\s*:\s*"?([0-9]+(?:\.[0-9]+)?)"?/i', $html, $rm)) {
            $pulled['rating'] = (float) $rm[1];
        }
        if ($pulled['review_count'] === null && preg_match('/"reviewCount"\s*:\s*"?([0-9,]+)"?/i', $html, $cm)) {
            $pulled['review_count'] = (int) str_replace(',', '', $cm[1]);
        }

        $pulled['ok'] = true;

        // ---- auto-tick only what we can positively confirm ----
        $ticks = [];
        if ($pulled['image_count'] > 0)                 $ticks['photos_present'] = true;
        if ($pulled['image_count'] >= 24)               $ticks['photos_count'] = true;
        if (!empty($pulled['name']))                    $ticks['name_ok'] = true;
        if (mb_strlen((string) $pulled['description']) >= 120) $ticks['desc_present'] = true;
        if (!empty($pulled['price']))                   $ticks['price_visible'] = true;
        if (!empty($pulled['address']))                 $ticks['map_pin'] = true;
        if ($pulled['rating'] !== null)                 $ticks['review_present'] = true;
        if ($pulled['rating'] !== null && $pulled['rating'] >= 8.0) $ticks['review_good'] = true;

        return ['pulled' => $pulled, 'ticks' => $ticks];
    }

    /* ---------- helpers ---------- */

    private function applyJsonLd($node, array &$out): void
    {
        if (!is_array($node)) return;
        if (isset($node['@graph']) && is_array($node['@graph'])) {
            foreach ($node['@graph'] as $n) $this->applyJsonLd($n, $out);
            return;
        }
        $type = $node['@type'] ?? '';
        $type = is_array($type) ? implode(' ', $type) : (string) $type;
        if (!preg_match('/Hotel|Lodging|Resort|BedAndBreakfast|Motel|Apartment|Place|Product/i', $type)) return;

        if (empty($out['name']) && !empty($node['name'])) $out['name'] = $this->clean($node['name']);
        if (empty($out['description']) && !empty($node['description'])) $out['description'] = $this->clean($node['description']);

        if (!empty($node['image'])) {
            $imgs = is_array($node['image']) ? $node['image'] : [$node['image']];
            $out['image_count'] = max($out['image_count'], count(array_filter($imgs)));
        }

        $rating = $node['aggregateRating'] ?? null;
        if (is_array($rating)) {
            if (isset($rating['ratingValue'])) $out['rating'] = (float) $rating['ratingValue'];
            if (isset($rating['reviewCount'])) $out['review_count'] = (int) $rating['reviewCount'];
        }

        $star = $node['starRating'] ?? null;
        if (is_array($star) && isset($star['ratingValue'])) $out['stars'] = (float) $star['ratingValue'];

        $addr = $node['address'] ?? null;
        if (is_array($addr)) {
            $out['address'] = $this->clean(trim(collect([
                $addr['streetAddress'] ?? null, $addr['addressLocality'] ?? null,
                $addr['addressRegion'] ?? null, $addr['addressCountry'] ?? null,
            ])->filter()->implode(', ')));
        } elseif (is_string($addr) && empty($out['address'])) {
            $out['address'] = $this->clean($addr);
        }

        $offers = $node['offers'] ?? $node['priceRange'] ?? null;
        if (is_string($node['priceRange'] ?? null)) $out['price'] = $this->clean($node['priceRange']);
        if (is_array($offers) && isset($offers['price'])) $out['price'] = $this->clean(($offers['priceCurrency'] ?? '') . ' ' . $offers['price']);
    }

    private function jsonLd(string $html): array
    {
        $blocks = [];
        if (preg_match_all('/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html, $m)) {
            foreach ($m[1] as $raw) {
                $data = json_decode(trim($raw), true);
                if (is_array($data)) $blocks[] = $data;
            }
        }
        return $blocks;
    }

    private function metaTags(string $html): array
    {
        $tags = [];
        if (preg_match_all('/<meta\b[^>]*>/i', $html, $m)) {
            foreach ($m[0] as $tag) {
                $key = $this->attr($tag, 'property') ?: $this->attr($tag, 'name');
                $val = $this->attr($tag, 'content');
                if ($key && $val !== null) $tags[strtolower($key)] = $val;
            }
        }
        return $tags;
    }

    private function titleTag(string $html): ?string
    {
        return preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $m) ? trim(html_entity_decode($m[1])) : null;
    }

    private function attr(string $tag, string $name): ?string
    {
        return preg_match('/\b' . preg_quote($name, '/') . '\s*=\s*("([^"]*)"|\'([^\']*)\')/i', $tag, $m)
            ? ($m[2] !== '' ? $m[2] : ($m[3] ?? '')) : null;
    }

    private function clean($v): ?string
    {
        if ($v === null) return null;
        $v = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags((string) $v))));
        return $v === '' ? null : Str::limit($v, 600, '');
    }
}
