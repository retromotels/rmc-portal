<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VetCheck extends Model
{
    protected $fillable = [
        'user_id', 'property_id', 'property_name', 'handle', 'followers', 'following',
        'posts', 'avg_likes', 'avg_comments', 'posts_per_week', 'based_location',
        'account_type', 'engagement_rate', 'score', 'verdict_tag', 'verdict_heading',
        'verdict_body', 'dimensions', 'suggested_reply', 'raw_input', 'provider',
    ];

    protected function casts(): array
    {
        return [
            'dimensions' => 'array',
            'raw_input'  => 'array',
            'engagement_rate' => 'float',
            'posts_per_week'  => 'float',
        ];
    }

    /** Verdict colour band from the score. */
    public function band(): string
    {
        return $this->score >= 66 ? 'good' : ($this->score >= 40 ? 'warn' : 'bad');
    }
}
