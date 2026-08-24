<?php

namespace App\Http\Controllers;

use App\Models\CommunityMember;
use App\Models\ForumReply;
use App\Models\ForumThread;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * The member Community — a directory + forum. A property must join (create a
 * profile) before it can see or take part. Everything past the join screen is
 * members-only.
 */
class CommunityController extends Controller
{
    private function guard(): void
    {
        abort_unless(Setting::bool('module_community'), 404);
    }

    /** This property's community membership, or null. */
    private function member(): ?CommunityMember
    {
        return CommunityMember::where('user_id', $this->currentProperty()->id)->first();
    }

    /** Require membership for member-only actions. */
    private function requireMember(): CommunityMember
    {
        $m = $this->member();
        abort_if(!$m, 403);
        return $m;
    }

    /* ---------------------------------------------------------- join / home */

    public function index(Request $r)
    {
        $this->guard();

        if (!($me = $this->member())) {
            return view('tools.community.join', [
                'prop'  => $this->currentProperty(),
                'title' => Setting::get('community_title', 'The Community'),
                'intro' => Setting::get('community_body', ''),
                'count' => CommunityMember::count(),
            ]);
        }

        $category = (string) $r->query('category');
        $q = ForumThread::with('author')->orderByDesc('pinned')->orderByDesc('last_reply_at')->latest();
        if (array_key_exists($category, config('rmc.forum_categories'))) {
            $q->where('category', $category);
        }

        return view('tools.community.home', [
            'me'        => $me,
            'threads'   => $q->paginate(20)->withQueryString(),
            'category'  => array_key_exists($category, config('rmc.forum_categories')) ? $category : '',
            'counts'    => ForumThread::selectRaw('category, count(*) c')->groupBy('category')->pluck('c', 'category'),
            'members'   => CommunityMember::count(),
        ]);
    }

    public function join(Request $r)
    {
        $this->guard();
        if ($this->member()) {
            return redirect()->route('tools.community');
        }

        $prop = $this->currentProperty();
        $data = $r->validate([
            'display_name' => ['required', 'string', 'max:120'],
            'town'         => ['nullable', 'string', 'max:120'],
            'headline'     => ['nullable', 'string', 'max:140'],
            'bio'          => ['nullable', 'string', 'max:2000'],
            'website'      => ['nullable', 'string', 'max:200'],
            'avatar'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $avatarPath = $r->hasFile('avatar') ? $r->file('avatar')->store('community-avatars', 'local') : null;

        CommunityMember::create([
            'user_id'      => $prop->id,
            'account_id'   => $prop->accountId(),
            'display_name' => $data['display_name'],
            'town'         => $data['town'] ?? $prop->loc,
            'headline'     => $data['headline'] ?? null,
            'bio'          => $data['bio'] ?? null,
            'website'      => $data['website'] ?? null,
            'avatar_path'  => $avatarPath,
        ]);

        return redirect()->route('tools.community')->with('flash', "You're in — welcome to the community.");
    }

    /* -------------------------------------------------------------- profile */

    public function profileEdit()
    {
        $this->guard();
        return view('tools.community.profile', ['me' => $this->requireMember()]);
    }

    public function profileUpdate(Request $r)
    {
        $this->guard();
        $me = $this->requireMember();

        $data = $r->validate([
            'display_name' => ['required', 'string', 'max:120'],
            'town'         => ['nullable', 'string', 'max:120'],
            'headline'     => ['nullable', 'string', 'max:140'],
            'bio'          => ['nullable', 'string', 'max:2000'],
            'website'      => ['nullable', 'string', 'max:200'],
            'avatar'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        if ($r->hasFile('avatar')) {
            if ($me->avatar_path) {
                Storage::disk('local')->delete($me->avatar_path);
            }
            $data['avatar_path'] = $r->file('avatar')->store('community-avatars', 'local');
        }
        unset($data['avatar']);
        $me->update($data);

        return redirect()->route('tools.community.directory')->with('flash', 'Profile updated.');
    }

    public function avatar(CommunityMember $member)
    {
        $this->guard();
        $this->requireMember();
        abort_unless($member->avatar_path && Storage::disk('local')->exists($member->avatar_path), 404);
        return Storage::disk('local')->response($member->avatar_path);
    }

    /* ------------------------------------------------------------ directory */

    public function directory(Request $r)
    {
        $this->guard();
        $me = $this->requireMember();

        $kw = trim((string) $r->query('q'));
        $q = CommunityMember::query()->latest();
        if ($kw !== '') {
            $q->where(fn ($w) => $w->where('display_name', 'like', "%{$kw}%")->orWhere('town', 'like', "%{$kw}%"));
        }

        return view('tools.community.directory', [
            'me'      => $me,
            'members' => $q->paginate(40)->withQueryString(),
            'kw'      => $kw,
        ]);
    }

    /* ---------------------------------------------------------------- forum */

    public function threadCreate()
    {
        $this->guard();
        $this->requireMember();
        return view('tools.community.thread-create');
    }

    public function threadStore(Request $r)
    {
        $this->guard();
        $me = $this->requireMember();

        $data = $r->validate([
            'category' => ['required', Rule::in(array_keys(config('rmc.forum_categories')))],
            'title'    => ['required', 'string', 'max:160'],
            'body'     => ['required', 'string', 'max:8000'],
        ]);

        $thread = ForumThread::create($data + [
            'community_member_id' => $me->id,
            'last_reply_at'       => now(),
        ]);

        return redirect()->route('tools.community.thread', $thread)->with('flash', 'Posted.');
    }

    public function threadShow(ForumThread $thread)
    {
        $this->guard();
        $me = $this->requireMember();
        $thread->load(['author', 'replies.author']);

        return view('tools.community.thread', ['thread' => $thread, 'me' => $me]);
    }

    public function replyStore(Request $r, ForumThread $thread)
    {
        $this->guard();
        $me = $this->requireMember();
        abort_if($thread->locked, 403);

        $data = $r->validate(['body' => ['required', 'string', 'max:8000']]);

        DB::transaction(function () use ($thread, $me, $data) {
            ForumReply::create(['forum_thread_id' => $thread->id, 'community_member_id' => $me->id, 'body' => $data['body']]);
            $thread->increment('replies_count');
            $thread->update(['last_reply_at' => now()]);
        });

        return redirect()->route('tools.community.thread', $thread)->withFragment('latest');
    }

    public function threadDelete(ForumThread $thread)
    {
        $this->guard();
        $me = $this->requireMember();
        abort_unless($thread->community_member_id === $me->id, 403);
        $thread->delete();

        return redirect()->route('tools.community')->with('flash', 'Thread removed.');
    }

    public function replyDelete(ForumReply $reply)
    {
        $this->guard();
        $me = $this->requireMember();
        abort_unless($reply->community_member_id === $me->id, 403);
        $thread = $reply->thread;
        $reply->delete();
        if ($thread) {
            $thread->decrement('replies_count');
        }

        return back()->with('flash', 'Reply removed.');
    }
}
