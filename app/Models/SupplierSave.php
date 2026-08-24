<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierSave extends Model
{
    protected $fillable = ['supplier_id', 'user_id'];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
