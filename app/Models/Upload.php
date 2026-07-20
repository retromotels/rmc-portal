<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Upload extends Model
{
    protected $fillable = ['user_id', 'section', 'field', 'original_name', 'path', 'size', 'mime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function humanSize(): string
    {
        $b = (int) $this->size;
        if ($b < 1024) return $b . ' B';
        if ($b < 1048576) return round($b / 1024) . ' KB';
        return round($b / 1048576, 1) . ' MB';
    }
}
