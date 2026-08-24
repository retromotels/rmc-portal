<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierRequest extends Model
{
    protected $fillable = [
        'supplier_id', 'user_id', 'property_id', 'property_name',
        'contact_email', 'message', 'status',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
