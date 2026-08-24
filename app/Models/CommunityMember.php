<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunityMember extends Model
{
    protected $fillable = [
        'user_id', 'account_id', 'display_name', 'town', 'headline', 'bio', 'website', 'avatar_path',
    ];

    public function threads()
    {
        return $this->hasMany(ForumThread::class);
    }

    public function property()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim($this->display_name)) ?: [];
        $a = mb_substr($parts[0] ?? '', 0, 1);
        $b = count($parts) > 1 ? mb_substr(end($parts), 0, 1) : '';
        return strtoupper($a . $b) ?: '·';
    }
}
