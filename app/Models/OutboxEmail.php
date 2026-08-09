<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutboxEmail extends Model
{
    protected $fillable = [
        'template', 'to_email', 'to_name', 'subject', 'body', 'meta', 'status', 'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'meta'    => 'array',
            'sent_at' => 'datetime',
        ];
    }
}
