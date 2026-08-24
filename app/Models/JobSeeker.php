<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class JobSeeker extends Authenticatable
{
    protected $fillable = ['name', 'email', 'password', 'phone', 'state', 'avatar_path', 'headline', 'bio', 'town'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed'];
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }

    public function resumes()
    {
        return $this->hasMany(JobSeekerResume::class)->latest('is_default')->latest();
    }

    public function defaultResume(): ?JobSeekerResume
    {
        return $this->resumes()->where('is_default', true)->first() ?? $this->resumes()->first();
    }

    /** The most recent application that carries an uploaded CV, if any. */
    public function latestCvApplication(): ?JobApplication
    {
        return $this->applications()->whereNotNull('cv_path')->latest()->first();
    }

    public function stateLabel(): ?string
    {
        return $this->state ? config('rmc.job_states.' . $this->state, $this->state) : null;
    }
}
