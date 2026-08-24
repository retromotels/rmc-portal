<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobPurchase extends Model
{
    protected $fillable = [
        'employer_id', 'tier', 'credits', 'amount', 'currency',
        'status', 'stripe_session_id', 'note',
    ];

    public function employer()
    {
        return $this->belongsTo(Employer::class);
    }
}
