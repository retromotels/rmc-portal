<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Pulls name / description / imagery / address / geo from a property's
 * existing website so an admin can spin up a microsite from it. Everything
 * returned is a best-effort guess the admin can then curate.
 */
class SiteScraper
{
    public function scrape(string $url): array
    {
        $out = [
            'name' => null, 'tagline' => null, 'description' => null,
            'address' => null, 'city' => null, 'region' => null, 'country' => null,
            'lat' => null, 'lng' => null, 'phone' => null, 'email' => null,
            'booking_url' => $url, 'price_from' => null,
            'hero_image' => null, 'images' => [], 'amenities' => [],
            'ok' => false, 'error' => null,
        ];

        try {
            $resp = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; RMCSiteBuilder/1.0; +https://retromotels.com)',
                'Accept'     => 'text/html,application/xhtml+xml',
            ])->timeout(25)->retry(1, 500)->get($url);

            if (!$resp->ok()) {
                $out['error'] = 'Fetch returned HTTP ' . $resp->status();
                return $out;
            }
            $html = $resp->body();
        } catch (\Throwable $e) {
            $out['error'] = 'Could not reach the URL: ' . $e->getMessage();
            return $out;
        }

        $base = $this->baseUrl($url);

        // ---- meta / og ----
        $og = $this->metaTags($html);
        $out['name']        = $this->clean($og['og:site_name'] ?? $og['og:title'] ?? $this->titleTag($html));
        $out['tagline']     = $this->clean($og['og:title'] ?? null);
        $out['description'] = $this->clean($og['og:description'] ?? $og['description'] ?? null);

        // ---- JSON-LD (Hotel / LodgingBusiness / LocalBusiness) ----
        foreach ($this->jsonLdBlocks($html) as $node) {
            $this->applyJsonLd($node, $out);
        }

        // ---- images ----
        $imgs = [];
        foreach ($this->collectImageUrls($html, $og) as $src) {
            $abs = $this->absolute($src, $base);
            if ($abs && $this->looksLikePhoto($abs)) $imgs[$abs] = true;
        }
        $images = array_slice(array_keys($imgs), 0, 18);
        $out['images']     = $images;
        $out['hero_image'] = $this->absolute($og['og:image'] ?? null, $base) ?: ($images[0] ?? null);

        // ---- contact ----
        if (!$out['phone']) $out['phone'] = $this->firstMatch('/tel:([+0-9 ()\-]{6,})/i', $html);
        if (!$out['email']) $out['email'] = $this->firstMatch('/mailto:([^"?\s>]+@[^"?\s>]+)/i', $html);

        $out['ok'] = true;
        return $out;
    }

    /* ---------------- helpers ---------------- */

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

    private function jsonLdBlocks(string $html): array
    {
        $blocks = [];
        if (preg_match_all('/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html, $m)) {
            foreach ($m[1] as $raw) {
                $data = json_decode(trim($raw), true);
                if (!is_array($data)) continue;
                // flatten @graph and arrays of nodes
                if (isset($data['@graph']) && is_array($data['@graph'])) {
                    foreach ($data['@graph'] as $n) $blocks[] = $n;
                } elseif (array_is_list($data)) {
                    foreach ($data as $n) $blocks[] = $n;
                } else {
                    $blocks[] = $data;
                }
            }
        }
        return $blocks;
    }

    private function applyJsonLd($node, array &$out): void
    {
        if (!is_array($node)) return;
        $type = $node['@type'] ?? '';
        $type = is_array($type) ? implode(' ', $type) : (string) $type;
        $isPlace = preg_match('/Hotel|Lodging|Resort|BedAndBreakfast|LocalBusiness|Motel|Place|Organization/i', $type);
        if (!$isPlace) return;

        if (empty($out['name']) && !empty($node['name']))        $out['name'] = $this->clean($node['name']);
        if (empty($out['description']) && !empty($node['description'])) $out['description'] = $this->clean($node['description']);
        if (empty($out['phone']) && !empty($node['telephone']))  $out['phone'] = $this->clean($node['telephone']);
        if (empty($out['price_from']) && !empty($node['priceRange'])) $out['price_from'] = $this->clean($node['priceRange']);

        $addr = $node['address'] ?? null;
        if (is_array($addr)) {
            $out['address'] = $out['address'] ?: $this->clean($addr['streetAddress'] ?? null);
            $out['city']    = $out['city']    ?: $this->clean($addr['addressLocality'] ?? null);
            $out['region']  = $out['region']  ?: $this->clean($addr['addressRegion'] ?? null);
            $out['country'] = $out['country'] ?: $this->clean($addr['addressCountry'] ?? null);
        } elseif (is_string($addr) && empty($out['address'])) {
            $out['address'] = $this->clean($addr);
        }

        $geo = $node['geo'] ?? null;
        if (is_array($geo)) {
            $out['lat'] = $out['lat'] ?? ($geo['latitude'] ?? null);
            $out['lng'] = $out['lng'] ?? ($geo['longitude'] ?? null);
        }
    }

    private function collectImageUrls(string $html, array $og): array
    {
        $urls = [];
        foreach (['og:image', 'og:image:secure_url', 'twitter:image'] as $k) {
            if (!empty($og[$k])) $urls[] = $og[$k];
        }
        if (preg_match_all('/<img\b[^>]*>/i', $html, $m)) {
            foreach ($m[0] as $tag) {
                foreach (['data-src', 'data-lazy-src', 'src'] as $a) {
                    $v = $this->attr($tag, $a);
                    if ($v) { $urls[] = $v; break; }
                }
                // srcset — take the last (largest) candidate
                if ($ss = $this->attr($tag, 'srcset')) {
                    $parts = explode(',', $ss);
                    $last = trim(end($parts));
                    $urls[] = explode(' ', $last)[0];
                }
            }
        }
        // CSS background images
        if (preg_match_all('/background(?:-image)?\s*:\s*url\((["\']?)([^"\')]+)\1\)/i', $html, $bm)) {
            foreach ($bm[2] as $u) $urls[] = $u;
        }
        return $urls;
    }

    private function looksLikePhoto(string $url): bool
    {
        $u = strtolower($url);
        if (Str::startsWith($u, 'data:')) return false;
        if (preg_match('/\.(svg|gif|ico)(\?|$)/', $u)) return false;
        if (preg_match('/(logo|icon|sprite|favicon|pixel|spacer|placeholder|avatar|badge|payment|footer)/', $u)) return false;
        return preg_match('/\.(jpe?g|png|webp|avif)(\?|$)/', $u) || Str::contains($u, ['/wp-content/', '/images/', '/img/', 'cdn']);
    }

    private function absolute(?string $src, string $base): ?string
    {
        if (!$src) return null;
        $src = trim(html_entity_decode($src));
        if ($src === '' || Str::startsWith($src, 'data:')) return null;
        if (Str::startsWith($src, '//')) return 'https:' . $src;
        if (preg_match('#^https?://#i', $src)) return $src;
        if (Str::startsWith($src, '/')) return rtrim($base, '/') . $src;
        return rtrim($base, '/') . '/' . ltrim($src, '/');
    }

    private function baseUrl(string $url): string
    {
        $p = parse_url($url);
        $scheme = $p['scheme'] ?? 'https';
        $host = $p['host'] ?? '';
        return $host ? "{$scheme}://{$host}" : $url;
    }

    private function attr(string $tag, string $name): ?string
    {
        return preg_match('/\b' . preg_quote($name, '/') . '\s*=\s*("([^"]*)"|\'([^\']*)\')/i', $tag, $m)
            ? ($m[2] !== '' ? $m[2] : ($m[3] ?? '')) : null;
    }

    private function firstMatch(string $re, string $s): ?string
    {
        return preg_match($re, $s, $m) ? trim($m[1]) : null;
    }

    private function clean($v): ?string
    {
        if ($v === null) return null;
        $v = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags((string) $v))));
        return $v === '' ? null : Str::limit($v, 1000, '');
    }
}
