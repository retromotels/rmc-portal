<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteView extends Model
{
    public $timestamps = false;

    protected $fillable = ['site_id', 'kind', 'unlocked', 'ip', 'user_agent', 'created_at'];

    protected function casts(): array
    {
        return [
            'unlocked'   => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
