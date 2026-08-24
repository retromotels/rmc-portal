<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Scores an Instagram creator's fit against a specific property. Works on the
 * public / entered numbers (assisted mode) — the honest read the demo describes.
 * Real maths for engagement, following and audience-map; Claude enriches the
 * qualitative bits (guest-type match, verdict wording, suggested reply) when a
 * key is set, otherwise templated fallbacks keep it fully working.
 */
class VettingEngine
{
    public function analyse(array $in): array
    {
        $followers   = max(0, (int) ($in['followers'] ?? 0));
        $avgLikes    = max(0, (int) ($in['avg_likes'] ?? 0));
        $avgComments = max(0, (int) ($in['avg_comments'] ?? 0));
        $ppw         = (float) ($in['posts_per_week'] ?? 0);
        $driveMarket = (string) ($in['drive_market'] ?? '');
        $guestType   = (string) ($in['guest_type'] ?? '');
        $locations   = array_values(array_filter(array_map('trim', $in['post_locations'] ?? [])));
        $captions    = (string) ($in['captions'] ?? '');

        $engRate = $followers > 0 ? round(($avgLikes + $avgComments) / $followers * 100, 2) : 0.0;
        $benchmark = $this->benchmark($followers);

        // ---- Dimension scores (0-100) ----
        // Engagement quality: at benchmark ≈ 62, at 6% ≈ excellent. Rhythm penalty.
        $engScore = $benchmark > 0 ? min(100, round($engRate / $benchmark * 62)) : 0;
        if ($ppw < 1) {
            $engScore = (int) round($engScore * 0.7);   // posting once a fortnight or less
        } elseif ($ppw < 2) {
            $engScore = (int) round($engScore * 0.88);
        }

        // Following size: 8k+ is "moves a needle"; scales below.
        $followScore = (int) min(100, round($followers / 8000 * 100));

        // Audience map: share of recent post locations that fall in the drive market.
        [$overlapPct, $geo] = $this->marketOverlap($locations, $driveMarket);
        $mapScore = (int) round($overlapPct);

        // Audience quality: not independently measurable without private data — neutral-high,
        // clearly labelled. Drops if the numbers look bot-like (very high followers, near-zero engagement).
        $qualityScore = 80;
        if ($followers > 5000 && $engRate < 0.5) {
            $qualityScore = 45;
        }

        // Guest-type match: Claude if available, else keyword overlap.
        [$guestScore, $guestWhy] = $this->guestMatch($captions, $guestType, $overlapPct);

        $weights = config('rmc.vetting.weights');
        $dims = [
            ['key' => 'audience_map',     'label' => 'Audience map',      'weight' => $weights['audience_map'],     'score' => $mapScore,     'why' => $this->mapWhy($overlapPct, $driveMarket)],
            ['key' => 'engagement',       'label' => 'Engagement quality','weight' => $weights['engagement'],       'score' => $engScore,     'why' => $this->engWhy($engRate, $benchmark, $ppw)],
            ['key' => 'guest_match',      'label' => 'Guest-type match',  'weight' => $weights['guest_match'],      'score' => $guestScore,   'why' => $guestWhy],
            ['key' => 'following_size',   'label' => 'Following size',    'weight' => $weights['following_size'],   'score' => $followScore,  'why' => $this->followWhy($followers)],
            ['key' => 'audience_quality', 'label' => 'Audience quality',  'weight' => $weights['audience_quality'], 'score' => $qualityScore, 'why' => $qualityScore >= 70 ? 'No obvious fraud signals in the entered numbers. Note: real audience data is only visible to the account holder — ask for an Insights screenshot to confirm.' : 'The follower-to-engagement ratio looks off for a real audience. Worth a closer look before proceeding.'],
        ];

        $total = 0;
        foreach ($dims as $d) {
            $total += $d['score'] * $d['weight'];
        }
        $score = (int) round($total / 100);

        // ---- Reach estimates ----
        $reachLow  = $avgLikes > 0 ? $avgLikes * 8 : (int) round($followers * 0.10);
        $reachHigh = $avgLikes > 0 ? $avgLikes * 16 : (int) round($followers * 0.25);
        $mktLow  = (int) round($reachLow * $overlapPct / 100);
        $mktHigh = (int) round($reachHigh * $overlapPct / 100);

        // ---- Verdict wording + reply ----
        $verdict = $this->verdict($score, $dims, $in);

        return [
            'engagement_rate' => $engRate,
            'benchmark'       => $benchmark,
            'score'           => $score,
            'dimensions'      => $dims,
            'verdict_tag'     => $verdict['tag'],
            'verdict_heading' => $verdict['heading'],
            'verdict_body'    => $verdict['body'],
            'suggested_reply' => $verdict['reply'],
            'metrics' => [
                'engagement_rate' => $engRate,
                'benchmark'       => $benchmark,
                'avg_likes'       => $avgLikes,
                'avg_comments'    => $avgComments,
                'posts_per_week'  => $ppw,
                'reach_low'       => $reachLow,
                'reach_high'      => $reachHigh,
                'market_reach_low'  => $mktLow,
                'market_reach_high' => $mktHigh,
                'overlap_pct'     => round($overlapPct),
            ],
            'geo' => $geo,
        ];
    }

    /* ------------------------------------------------------------- helpers */

    private function benchmark(int $followers): float
    {
        foreach (config('rmc.vetting.benchmarks') as $ceiling => $rate) {
            if ($followers <= (int) $ceiling) {
                return (float) $rate;
            }
        }
        return 1.2;
    }

    /** Match recent post locations against the property's drive-market keywords. */
    private function marketOverlap(array $locations, string $driveMarket): array
    {
        $keywords = array_values(array_filter(array_map(
            fn ($w) => strtolower(trim($w)),
            preg_split('/[,\n;]+/', $driveMarket) ?: []
        )));

        if (empty($locations)) {
            return [0.0, []];
        }

        $counts = [];
        $matched = 0;
        foreach ($locations as $loc) {
            $l = strtolower($loc);
            $inMarket = false;
            foreach ($keywords as $kw) {
                if ($kw !== '' && str_contains($l, $kw)) {
                    $inMarket = true;
                    break;
                }
            }
            if ($inMarket) {
                $matched++;
            }
            $key = $loc;
            $counts[$key] = $counts[$key] ?? ['place' => $loc, 'n' => 0, 'in_market' => $inMarket];
            $counts[$key]['n']++;
        }

        $total = count($locations);
        $geo = array_map(function ($c) use ($total) {
            return ['place' => $c['place'], 'pct' => (int) round($c['n'] / $total * 100), 'in_market' => $c['in_market']];
        }, array_values($counts));
        usort($geo, fn ($a, $b) => $b['pct'] <=> $a['pct']);

        return [$matched / $total * 100, $geo];
    }

    private function guestMatch(string $captions, string $guestType, float $overlapPct): array
    {
        if ($captions === '' && $guestType === '') {
            return [(int) round($overlapPct * 0.7), 'Not enough content detail to judge guest-type match; leaned on the audience map.'];
        }

        if (config('rmc.ai.enabled')) {
            try {
                $ai = $this->claudeGuestMatch($captions, $guestType);
                if ($ai !== null) {
                    return $ai;
                }
            } catch (\Throwable $e) {
                Log::warning('VettingEngine Claude guestMatch failed: ' . $e->getMessage());
            }
        }

        // Keyword-overlap heuristic.
        $stop = ['the', 'and', 'for', 'with', 'our', 'who', 'fill', 'fills', 'on', 'in', 'a', 'of', 'to', 'guests', 'guest'];
        $gt = array_diff(preg_split('/[^a-z]+/', strtolower($guestType), -1, PREG_SPLIT_NO_EMPTY) ?: [], $stop);
        $cap = strtolower($captions);
        $hits = 0;
        foreach ($gt as $w) {
            if (mb_strlen($w) > 3 && str_contains($cap, $w)) {
                $hits++;
            }
        }
        $score = count($gt) ? (int) min(100, round($hits / count($gt) * 100)) : (int) round($overlapPct * 0.7);
        $why = $hits
            ? "Their captions touch on themes that overlap your guest type ({$hits} matching signal" . ($hits === 1 ? '' : 's') . ').'
            : 'Their content themes do not obviously match your guest type from what was entered.';
        return [$score, $why];
    }

    private function claudeGuestMatch(string $captions, string $guestType): ?array
    {
        $system = "You rate how well an Instagram creator's content matches a motel's ideal guest type, for a collaboration decision. "
            . "Return ONLY JSON: {\"score\": <0-100 integer>, \"why\": \"<one short sentence>\"}.";
        $user = "Property's ideal guest type:\n{$guestType}\n\nCreator's recent captions / content notes:\n" . mb_substr($captions, 0, 1500);

        $resp = Http::withHeaders([
            'x-api-key'         => config('rmc.ai.key'),
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->timeout(14)->post('https://api.anthropic.com/v1/messages', [
            'model'      => config('rmc.ai.model'),
            'max_tokens' => 200,
            'system'     => $system,
            'messages'   => [['role' => 'user', 'content' => $user]],
        ]);
        if (!$resp->successful()) {
            return null;
        }
        $text = data_get($resp->json(), 'content.0.text', '');
        if (!preg_match('/\{.*\}/s', $text, $m)) {
            return null;
        }
        $data = json_decode($m[0], true);
        if (!is_array($data) || !isset($data['score'])) {
            return null;
        }
        return [max(0, min(100, (int) $data['score'])), (string) ($data['why'] ?? '')];
    }

    private function verdict(int $score, array $dims, array $in): array
    {
        $tag = $score >= 66 ? 'Worth a conversation' : ($score >= 40 ? 'Proceed with caution' : 'Not a fit');

        // Weakest weighted dimension drives the explanation.
        usort($dims, fn ($a, $b) => ($a['score'] * $a['weight']) <=> ($b['score'] * $b['weight']));
        $weakest = $dims[0];
        $property = $in['property_name'] ?? 'your property';

        if (config('rmc.ai.enabled')) {
            try {
                $ai = $this->claudeVerdict($score, $tag, $weakest, $in);
                if ($ai !== null) {
                    return $ai + ['tag' => $tag];
                }
            } catch (\Throwable $e) {
                Log::warning('VettingEngine Claude verdict failed: ' . $e->getMessage());
            }
        }

        // Templated fallback in the RMC voice.
        $heading = $score >= 66 ? 'Worth a closer look.' : ($score >= 40 ? 'Some upside, real caveats.' : 'Lovely account. Wrong tool for this job.');
        $body = $score >= 66
            ? "This creator scores well against {$property}. The strongest signal is fit with your market and guests. Still ask for an Insights screenshot before you commit anything."
            : ($score >= 40
                ? "There is something here, but the weakest link is {$weakest['label']} ({$weakest['score']}/100). {$weakest['why']} Worth a conversation only if they can address that."
                : "Nothing dodgy here — but the fit is thin. The weakest link is {$weakest['label']} ({$weakest['score']}/100). {$weakest['why']} Say no warmly, and say why.");

        $reply = $score >= 66
            ? "Thanks so much for reaching out, and for thinking of us — we'd genuinely like to explore this. Before we lock anything in, would you mind sending a quick screenshot of your Instagram Insights audience tab? Once we've had a look we'll come back with some dates that could work."
            : "Thanks so much for reaching out, and for thinking of us. We're running our collaborations pretty tightly this year and focusing on creators whose audience sits in our drive market. From what we can see yours doesn't quite line up with ours right now, so it wouldn't be fair on either of us. If that changes — or if you're ever passing through — do get in touch and we'll look after you.";

        return compact('tag', 'heading', 'body', 'reply');
    }

    private function claudeVerdict(int $score, string $tag, array $weakest, array $in): ?array
    {
        $property = $in['property_name'] ?? 'the property';
        $system = "You are the Retro Motels Collective's vetting desk. Warm, plain-spoken, honest Australian tone. "
            . "Return ONLY JSON: {\"heading\": \"<short verdict headline>\", \"body\": \"<2-3 sentence explanation>\", \"reply\": \"<a warm ready-to-send reply to the creator, matching the verdict>\"}.";
        $user = "Score: {$score}/100 ({$tag}). Property: {$property}. Weakest dimension: {$weakest['label']} at {$weakest['score']}/100 — {$weakest['why']}. "
            . "Drive market: " . ($in['drive_market'] ?? '') . ". Guest type: " . ($in['guest_type'] ?? '') . ".";

        $resp = Http::withHeaders([
            'x-api-key'         => config('rmc.ai.key'),
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->timeout(16)->post('https://api.anthropic.com/v1/messages', [
            'model'      => config('rmc.ai.model'),
            'max_tokens' => 500,
            'system'     => $system,
            'messages'   => [['role' => 'user', 'content' => $user]],
        ]);
        if (!$resp->successful()) {
            return null;
        }
        $text = data_get($resp->json(), 'content.0.text', '');
        if (!preg_match('/\{.*\}/s', $text, $m)) {
            return null;
        }
        $data = json_decode($m[0], true);
        if (!is_array($data) || !isset($data['heading'])) {
            return null;
        }
        return [
            'heading' => (string) $data['heading'],
            'body'    => (string) ($data['body'] ?? ''),
            'reply'   => (string) ($data['reply'] ?? ''),
        ];
    }

    private function mapWhy(float $overlap, string $driveMarket): string
    {
        $o = round($overlap);
        return $o >= 50
            ? "About {$o}% of their recent posts sit in your drive market — the audience your guests actually come from."
            : "Only {$o}% of their recent posts sit in your drive market ({$driveMarket}). A creator your guests can't become is worth little, however good the content.";
    }

    private function engWhy(float $rate, float $benchmark, float $ppw): string
    {
        $rhythm = $ppw < 1 ? ' and posting roughly once a fortnight' : '';
        return "{$rate}% engagement against a {$benchmark}% benchmark for this size{$rhythm}.";
    }

    private function followWhy(int $followers): string
    {
        return $followers >= 8000
            ? number_format($followers) . ' followers — enough reach to move a needle.'
            : number_format($followers) . ' followers — small, so it needs a high engagement rate or tight local fit to be worth it.';
    }
}
