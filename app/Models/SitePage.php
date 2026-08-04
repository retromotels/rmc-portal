<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SitePage extends Model
{
    protected $fillable = [
        'site_id', 'title', 'slug', 'source_url', 'nav_order', 'body', 'images', 'visible',
    ];

    protected function casts(): array
    {
        return [
            'images'  => 'array',
            'visible' => 'boolean',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
