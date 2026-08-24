<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForumReply extends Model
{
    protected $fillable = ['forum_thread_id', 'community_member_id', 'body'];

    public function thread()
    {
        return $this->belongsTo(ForumThread::class, 'forum_thread_id');
    }

    public function author()
    {
        return $this->belongsTo(CommunityMember::class, 'community_member_id');
    }
}
