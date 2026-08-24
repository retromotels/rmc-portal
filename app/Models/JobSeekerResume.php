<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobSeekerResume extends Model
{
    protected $fillable = ['job_seeker_id', 'path', 'original_name', 'size', 'is_default'];

    protected function casts(): array
    {
        return ['is_default' => 'boolean'];
    }

    public function seeker()
    {
        return $this->belongsTo(JobSeeker::class, 'job_seeker_id');
    }
}
