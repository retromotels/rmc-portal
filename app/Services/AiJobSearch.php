<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Turns a plain-English job search ("housekeeping in SA over $50k") into the
 * board's structured filters: department, state, pay band, employment type and
 * free-text keywords.
 *
 * Uses the Claude (Anthropic) API when ANTHROPIC_API_KEY is set; otherwise falls
 * back to a built-in keyword parser so search always works. Either way the result
 * is validated against the known vocab, so nothing invalid reaches the query.
 */
class AiJobSearch
{
    public function parse(string $phrase): array
    {
        $phrase = trim($phrase);
        if ($phrase === '') {
            return $this->blank();
        }

        if (config('rmc.ai.enabled')) {
            try {
                $ai = $this->viaClaude($phrase);
                if ($ai !== null) {
                    return $ai;
                }
            } catch (\Throwable $e) {
                Log::warning('AiJobSearch Claude call failed: ' . $e->getMessage());
            }
        }

        return $this->viaKeywords($phrase);
    }

    private function blank(): array
    {
        return ['type' => '', 'dept' => '', 'state' => '', 'pay' => '', 'kw' => ''];
    }

    /* ---------------------------------------------------------------- Claude */

    private function viaClaude(string $phrase): ?array
    {
        $depts  = config('rmc.job_departments');   // key => label
        $states = array_keys(config('rmc.job_states'));
        $types  = array_keys(config('rmc.employment_types'));
        $bands  = array_keys(config('rmc.salary_bands')); // e.g. 50000, 70000...

        $system = "You convert a job-seeker's plain-English search into JSON filters for a motel/hotel job board in Australia. "
            . "Respond with ONLY a JSON object, no prose. Keys and allowed values:\n"
            . '- "department": one of ' . json_encode(array_keys($depts)) . " or null. Meanings: " . json_encode($depts) . "\n"
            . '- "state": one of ' . json_encode($states) . " (Australian state codes) or null\n"
            . '- "employment_type": one of ' . json_encode($types) . " or null\n"
            . '- "min_salary": an integer annual AUD figure the user wants to earn at least, or null (convert '
            . '\"50k\" to 50000; treat hourly like \"$30/hr\" as ~60000)' . "\n"
            . '- "keywords": a short string of any remaining role/keyword terms, or null' . "\n"
            . "Only fill a field if the query clearly implies it.";

        $resp = Http::withHeaders([
            'x-api-key'         => config('rmc.ai.key'),
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->timeout(12)->post('https://api.anthropic.com/v1/messages', [
            'model'      => config('rmc.ai.model'),
            'max_tokens' => 300,
            'system'     => $system,
            'messages'   => [['role' => 'user', 'content' => $phrase]],
        ]);

        if (!$resp->successful()) {
            Log::warning('AiJobSearch Claude HTTP ' . $resp->status() . ': ' . $resp->body());
            return null;
        }

        $text = data_get($resp->json(), 'content.0.text', '');
        if (!preg_match('/\{.*\}/s', $text, $m)) {
            return null;
        }
        $data = json_decode($m[0], true);
        if (!is_array($data)) {
            return null;
        }

        return [
            'type'  => $this->validType($data['employment_type'] ?? null),
            'dept'  => $this->validDept($data['department'] ?? null),
            'state' => $this->validState($data['state'] ?? null),
            'pay'   => $this->bandFor(is_numeric($data['min_salary'] ?? null) ? (int) $data['min_salary'] : null),
            'kw'    => $this->cleanKw($data['keywords'] ?? ''),
        ];
    }

    /* -------------------------------------------------------------- Keywords */

    private function viaKeywords(string $phrase): array
    {
        $p = ' ' . strtolower($phrase) . ' ';

        // State — full names and codes.
        $stateMap = [
            'new south wales' => 'NSW', 'nsw' => 'NSW',
            'victoria' => 'VIC', 'vic' => 'VIC',
            'queensland' => 'QLD', 'qld' => 'QLD',
            'south australia' => 'SA', ' sa ' => 'SA',
            'western australia' => 'WA', ' wa ' => 'WA',
            'tasmania' => 'TAS', 'tas' => 'TAS',
            'northern territory' => 'NT', ' nt ' => 'NT',
            'australian capital territory' => 'ACT', 'canberra' => 'ACT', 'act' => 'ACT',
        ];
        $state = '';
        foreach ($stateMap as $needle => $code) {
            $n = str_contains($needle, ' ') && !str_starts_with($needle, ' ') ? " {$needle} " : $needle;
            if (str_contains($p, $n) || str_contains($p, " {$needle} ")) {
                $state = $code;
                break;
            }
        }

        // Department — keyword groups.
        $deptMap = [
            'front-office'  => ['front office', 'reception', 'front desk', 'guest service', 'guest services', 'concierge', 'reservation', 'night audit'],
            'housekeeping'  => ['housekeep', 'room attendant', 'cleaner', 'cleaning', 'laundry', 'linen'],
            'food-beverage' => ['food', 'beverage', 'chef', 'cook', 'kitchen', 'waiter', 'waitress', 'wait staff', 'barista', 'bar ', 'restaurant', 'f&b'],
            'maintenance'   => ['maintenance', 'grounds', 'gardener', 'handyman', 'groundsperson'],
            'management'    => ['manager', 'management', 'supervisor', 'duty manager', 'gm ', 'general manager'],
        ];
        $dept = '';
        foreach ($deptMap as $key => $needles) {
            foreach ($needles as $needle) {
                if (str_contains($p, $needle)) {
                    $dept = $key;
                    break 2;
                }
            }
        }

        // Employment type.
        $type = '';
        if (str_contains($p, 'full time') || str_contains($p, 'full-time') || str_contains($p, 'fulltime')) {
            $type = 'full-time';
        } elseif (str_contains($p, 'part time') || str_contains($p, 'part-time')) {
            $type = 'part-time';
        } elseif (str_contains($p, 'casual')) {
            $type = 'casual';
        } elseif (str_contains($p, 'contract')) {
            $type = 'contract';
        }

        // Salary — "$50k", "50,000", "over 50000".
        $min = null;
        if (preg_match('/\$?\s*(\d{2,3})\s*k\b/i', $p, $m)) {
            $min = (int) $m[1] * 1000;
        } elseif (preg_match('/\$?\s*(\d{2,3})[,\s]?000\b/', $p, $m)) {
            $min = (int) $m[1] * 1000;
        } elseif (preg_match('/\$\s*(\d{2,3})\b/', $p, $m) && (int) $m[1] >= 20 && (int) $m[1] <= 99) {
            // e.g. "$30" (an hourly rate) -> rough annual
            $min = (int) $m[1] * 2000;
        }
        $pay = $this->bandFor($min);

        // Leftover keywords: strip the matched signals + filler.
        $kw = $this->leftoverKeywords($phrase, $dept, $state);

        return compact('type', 'dept', 'state', 'pay', 'kw');
    }

    private function leftoverKeywords(string $phrase, string $dept, string $state): string
    {
        $stop = ['in', 'the', 'a', 'an', 'for', 'over', 'above', 'under', 'below', 'at', 'least', 'min', 'minimum',
            'per', 'year', 'annum', 'hour', 'hr', 'pa', 'jobs', 'job', 'role', 'roles', 'work', 'position', 'positions',
            'and', 'or', 'with', 'looking', 'want', 'need', 'around', 'near', 'k', 'full', 'part', 'time', 'casual', 'contract',
            'south', 'western', 'new', 'wales', 'australia', 'territory', 'northern', 'capital', 'australian',
            'nsw', 'vic', 'qld', 'sa', 'wa', 'tas', 'nt', 'act', 'canberra', 'queensland', 'victoria', 'tasmania'];
        $words = preg_split('/[^a-z0-9&]+/i', strtolower($phrase), -1, PREG_SPLIT_NO_EMPTY);
        $keep = [];
        foreach ($words as $w) {
            if (is_numeric($w) || str_contains($w, 'k') && preg_match('/^\d+k$/', $w)) {
                continue;
            }
            if (mb_strlen($w) < 3 || in_array($w, $stop, true)) {
                continue;
            }
            $keep[] = $w;
        }
        // If we already captured a department, drop generic department words from kw.
        if ($dept) {
            $keep = array_filter($keep, fn ($w) => !in_array($w, ['housekeeping', 'reception', 'front', 'office', 'management', 'manager', 'chef', 'cook', 'kitchen', 'maintenance'], true));
        }
        return trim(implode(' ', array_slice(array_values($keep), 0, 5)));
    }

    /* ------------------------------------------------------------- Validation */

    private function validDept($v): string
    {
        $v = is_string($v) ? $v : '';
        return array_key_exists($v, config('rmc.job_departments')) ? $v : '';
    }

    private function validState($v): string
    {
        $v = is_string($v) ? strtoupper($v) : '';
        return array_key_exists($v, config('rmc.job_states')) ? $v : '';
    }

    private function validType($v): string
    {
        $v = is_string($v) ? $v : '';
        return array_key_exists($v, config('rmc.employment_types')) ? $v : '';
    }

    /** Largest configured salary band whose threshold is <= the wanted minimum. */
    private function bandFor(?int $min): string
    {
        if (!$min) {
            return '';
        }
        $bands = array_map('intval', array_keys(config('rmc.salary_bands')));
        sort($bands);
        $chosen = '';
        foreach ($bands as $b) {
            if ($min >= $b) {
                $chosen = (string) $b;
            }
        }
        return $chosen;
    }

    private function cleanKw($v): string
    {
        $v = is_string($v) ? trim($v) : '';
        return mb_substr($v, 0, 60);
    }
}
