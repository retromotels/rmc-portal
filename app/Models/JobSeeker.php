<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class JobSeeker extends Authenticatable
{
    protected $fillable = ['name', 'email', 'password', 'phone', 'state'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed'];
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class);
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
