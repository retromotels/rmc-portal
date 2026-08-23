<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class JobSeeker extends Authenticatable
{
    protected $fillable = ['name', 'email', 'password', 'phone'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed'];
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }
}
