<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class JobListing extends Model
{
    protected $fillable = [
        'user_id', 'employer', 'source', 'source_ref', 'title', 'slug',
        'employment_type', 'department', 'location', 'state', 'pay', 'salary_annual',
        'description', 'status', 'reject_reason', 'approved_at', 'closes_at',
    ];

    protected function casts(): array
    {
        return ['approved_at' => 'datetime', 'closes_at' => 'date', 'salary_annual' => 'integer'];
    }

    protected static function booted(): void
    {
        static::created(function (self $j) {
            if (!$j->slug) {
                $j->slug = Str::slug($j->title) . '-' . $j->id;
                $j->saveQuietly();
            }
        });
        static::updating(function (self $j) {
            if ($j->isDirty('title')) {
                $j->slug = Str::slug($j->title) . '-' . $j->id;
            }
        });
    }

    public function property()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }

    /* ---- Scopes ---- */
    public function scopeApproved($q)
    {
        return $q->where('status', 'approved');
    }

    public function scopeLive($q)
    {
        return $q->where('status', 'approved')
            ->where(fn ($w) => $w->whereNull('closes_at')->orWhereDate('closes_at', '>=', now()->toDateString()));
    }

    public function isOpen(): bool
    {
        return $this->status === 'approved'
            && (!$this->closes_at || $this->closes_at->startOfDay()->gte(now()->startOfDay()));
    }

    /* ---- Display helpers ---- */
    /** The employer to show: the member property's motel, or the stored employer name. */
    public function employerName(): string
    {
        return $this->property?->motel ?: ($this->employer ?: 'Retro Motels');
    }

    public function typeLabel(): string
    {
        return ['full-time' => 'Full-time', 'part-time' => 'Part-time', 'casual' => 'Casual', 'contract' => 'Contract'][$this->employment_type] ?? ucfirst($this->employment_type);
    }

    public function departmentLabel(): ?string
    {
        if (!$this->department) {
            return null;
        }
        return config('rmc.job_departments.' . $this->department, ucfirst(str_replace('-', ' ', $this->department)));
    }
}
