<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingAudit extends Model
{
    protected $fillable = [
        'user_id', 'platform', 'url', 'property_name', 'pulled', 'checks', 'score',
    ];

    protected function casts(): array
    {
        return [
            'pulled' => 'array',
            'checks' => 'array',
            'score'  => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /* -------- checklist helpers -------- */

    public static function checklist(): array
    {
        return config('rmc.listing_checklist', []);
    }

    /** Flat list of every item key across all groups. */
    public static function allItems(): array
    {
        $out = [];
        foreach (self::checklist() as $items) {
            foreach ($items as $it) $out[$it['key']] = $it;
        }
        return $out;
    }

    public function statusOf(string $key): ?string
    {
        return $this->checks[$key]['status'] ?? null;
    }

    public function noteOf(string $key): string
    {
        return $this->checks[$key]['note'] ?? '';
    }

    /** Recompute the score: % of applicable items marked ok. */
    public function recomputeScore(): void
    {
        $items = self::allItems();
        $applicable = 0;
        $ok = 0;
        foreach ($items as $key => $_) {
            $s = $this->statusOf($key);
            if ($s === 'na') continue;
            $applicable++;
            if ($s === 'ok') $ok++;
        }
        $this->score = $applicable ? (int) round($ok / $applicable * 100) : 0;
    }

    public function counts(): array
    {
        $items = self::allItems();
        $c = ['ok' => 0, 'no' => 0, 'na' => 0, 'todo' => 0, 'total' => count($items)];
        foreach ($items as $key => $_) {
            $s = $this->statusOf($key);
            if ($s === 'ok') $c['ok']++;
            elseif ($s === 'no') $c['no']++;
            elseif ($s === 'na') $c['na']++;
            else $c['todo']++;
        }
        return $c;
    }

    public function ratingLabel(): string
    {
        if ($this->score >= 90) return 'Excellent';
        if ($this->score >= 75) return 'Good';
        if ($this->score >= 50) return 'Needs work';
        return 'Poor';
    }
}
