<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommunityMember;
use App\Models\ForumReply;
use App\Models\ForumThread;
use App\Models\Setting;

/**
 * Head-office view of the member Community: who's joined and every conversation
 * happening, with the ability to remove threads/replies (moderation).
 */
class CommunityAdminController extends Controller
{
    private function guard(): void
    {
        abort_unless(Setting::bool('module_community'), 404);
    }

    public function index()
    {
        $this->guard();

        return view('admin.community.index', [
            'members' => CommunityMember::withCount('threads')->latest()->get(),
            'threads' => ForumThread::with('author')->orderByDesc('last_reply_at')->paginate(30),
            'stats'   => [
                'members' => CommunityMember::count(),
                'threads' => ForumThread::count(),
                'replies' => ForumReply::count(),
            ],
        ]);
    }

    public function thread(ForumThread $thread)
    {
        $this->guard();
        $thread->load(['author', 'replies.author']);

        return view('admin.community.thread', ['thread' => $thread]);
    }

    public function deleteThread(ForumThread $thread)
    {
        $this->guard();
        $thread->delete();

        return redirect()->route('admin.community')->with('status', 'Thread removed.');
    }

    public function deleteReply(ForumReply $reply)
    {
        $this->guard();
        $thread = $reply->thread;
        $reply->delete();
        if ($thread) {
            $thread->decrement('replies_count');
        }

        return back()->with('status', 'Reply removed.');
    }
}
