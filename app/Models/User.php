<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'motel', 'band', 'tier',
        'phone', 'bio', 'photo_path', 'loc', 'details_complete', 'founding',
        'cancel_requested_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at'   => 'datetime',
            'password'            => 'hashed',
            'details_complete'    => 'boolean',
            'founding'            => 'boolean',
            'cancel_requested_at' => 'datetime',
        ];
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function uploads(): HasMany
    {
        return $this->hasMany(Upload::class);
    }

    public function policyDocuments(): HasMany
    {
        return $this->hasMany(PolicyDocument::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /* ---- Portal helpers ---- */

    public function band(): array
    {
        return config('rmc.bands.' . $this->band, config('rmc.bands.small'));
    }

    public function tierMeta(): array
    {
        return config('rmc.tiers.' . $this->tier, config('rmc.tiers.standard'));
    }

    public function baseFee(): int
    {
        return (int) ($this->band()['price'][$this->tier] ?? 0);
    }

    public function isFounding(): bool
    {
        return $this->founding && config('rmc.founding.active');
    }

    public function effectiveFee(): int
    {
        $base = $this->baseFee();
        return $this->isFounding() ? (int) round($base * (1 - config('rmc.founding.pct') / 100)) : $base;
    }

    /** Answers for a section as an array. */
    public function sectionData(string $section): array
    {
        $reg = $this->registrations->firstWhere('section', $section);
        return $reg?->data ?? [];
    }

    /** Files uploaded for a section+field. */
    public function filesFor(string $section, string $field)
    {
        return $this->uploads->where('section', $section)->where('field', $field);
    }

    /** Completion % for one section (required non-file fields only). */
    public function sectionPct(string $section): int
    {
        $cfg = config("rmc.sections.$section");
        if (!$cfg) return 0;
        $data = $this->sectionData($section);
        $req = array_filter($cfg['fields'], fn ($f) => ($f['req'] ?? false) && ($f['type'] ?? '') !== 'file');

        if (empty($req)) {
            foreach ($cfg['fields'] as $f) {
                if ($f['type'] === 'file') {
                    if ($this->filesFor($section, $f['id'])->count()) return 100;
                } elseif (!empty($data[$f['id']])) {
                    return 100;
                }
            }
            return 0;
        }
        $done = 0;
        foreach ($req as $f) {
            if (!empty($data[$f['id']])) $done++;
        }
        return (int) round($done / count($req) * 100);
    }

    public function sectionComplete(string $section): bool
    {
        return $this->sectionPct($section) >= 100;
    }

    public function overallPct(): int
    {
        $sections = array_keys(config('rmc.sections'));
        if (empty($sections)) return 0;
        $sum = array_sum(array_map(fn ($s) => $this->sectionPct($s), $sections));
        return (int) round($sum / count($sections));
    }

    public static function bandFromRooms($rooms): string
    {
        $n = (int) $rooms;
        return $n <= 18 ? 'small' : ($n <= 35 ? 'mid' : 'large');
    }

    public static function tierFromBand(string $band): string
    {
        return config("rmc.band_tier.$band", 'standard');
    }
}
