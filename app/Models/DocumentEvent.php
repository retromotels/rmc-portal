<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentEvent extends Model
{
    public $timestamps = false;

    protected $fillable = ['document_id', 'user_id', 'property_id', 'property_name', 'action', 'created_at'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
