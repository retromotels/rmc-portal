<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Site extends Model
{
    protected $fillable = [
        'user_id', 'theme', 'source_url', 'name', 'tagline', 'description',
        'address', 'city', 'region', 'country', 'lat', 'lng', 'phone', 'email',
        'booking_url', 'price_from', 'hero_image', 'images', 'amenities',
        'slug', 'published', 'published_at', 'preview_token', 'preview_password',
    ];

    protected function casts(): array
    {
        return [
            'images'       => 'array',
            'amenities'    => 'array',
            'published'    => 'boolean',
            'published_at' => 'datetime',
            'lat'          => 'float',
            'lng'          => 'float',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Site $site) {
            if (empty($site->preview_token)) {
                $site->preview_token = self::freshToken();
            }
            if (empty($site->preview_password)) {
                $site->preview_password = strtoupper(Str::random(6));
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(SiteView::class);
    }

    /* -------- helpers -------- */

    public static function freshToken(): string
    {
        do {
            $t = Str::lower(Str::random(12));
        } while (self::where('preview_token', $t)->exists());
        return $t;
    }

    /** Build a unique public slug from a name. */
    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'motel';
        $slug = $base;
        $i = 2;
        while (self::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    public function gallery(): array
    {
        $imgs = collect($this->images ?? [])->filter()->values();
        if ($this->hero_image) {
            $imgs = $imgs->reject(fn ($u) => $u === $this->hero_image);
        }
        return $imgs->all();
    }

    public function heroOrFirst(): ?string
    {
        return $this->hero_image ?: (collect($this->images ?? [])->first());
    }

    public function previewUrl(): string
    {
        return url('/motel/' . $this->preview_token);
    }

    public function publicUrl(): ?string
    {
        return $this->slug ? url('/motel/' . $this->slug) : null;
    }

    public function locationLabel(): string
    {
        return trim(collect([$this->city, $this->region])->filter()->implode(', '));
    }

    /** Google Maps embed query (no API key needed). */
    public function mapQuery(): ?string
    {
        if ($this->lat && $this->lng) return $this->lat . ',' . $this->lng;
        $addr = trim(collect([$this->address, $this->city, $this->region, $this->country])->filter()->implode(', '));
        return $addr !== '' ? $addr : null;
    }

    public function accent(): string
    {
        return config("rmc.site_themes.{$this->theme}.accent", '#2f6f7e');
    }

    public function themeLabel(): string
    {
        return config("rmc.site_themes.{$this->theme}.label", ucfirst($this->theme));
    }
}
