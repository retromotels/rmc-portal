<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForumThread extends Model
{
    protected $fillable = [
        'community_member_id', 'category', 'title', 'body',
        'pinned', 'locked', 'replies_count', 'last_reply_at',
    ];

    protected function casts(): array
    {
        return ['pinned' => 'boolean', 'locked' => 'boolean', 'last_reply_at' => 'datetime'];
    }

    public function author()
    {
        return $this->belongsTo(CommunityMember::class, 'community_member_id');
    }

    public function replies()
    {
        return $this->hasMany(ForumReply::class)->oldest();
    }

    public function categoryLabel(): string
    {
        return config('rmc.forum_categories.' . $this->category, ucfirst(str_replace('-', ' ', (string) $this->category)));
    }
}
