<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    protected $fillable = [
        'job_listing_id', 'job_seeker_id', 'name', 'email', 'phone',
        'message', 'cv_path', 'status',
    ];

    public function job()
    {
        return $this->belongsTo(JobListing::class, 'job_listing_id');
    }

    public function seeker()
    {
        return $this->belongsTo(JobSeeker::class, 'job_seeker_id');
    }
}
