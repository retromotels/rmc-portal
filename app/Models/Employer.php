<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Employer extends Authenticatable
{
    protected $fillable = ['company', 'name', 'email', 'password', 'phone', 'website', 'job_credits', 'stripe_customer_id'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    public function purchases()
    {
        return $this->hasMany(JobPurchase::class);
    }

    public function jobs()
    {
        return $this->hasMany(JobListing::class, 'employer_id');
    }
}
