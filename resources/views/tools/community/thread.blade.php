@extends('layouts.portal')
@section('title', $thread->title)
@section('content')
@php($avatar = function($m){ return $m && $m->avatar_path ? route('tools.community.avatar', $m) : null; })
<style>
  .tv{max-width:720px}
  .tv-back{color:#6c6577;text-decoration:none;font-size:13.5px}
  .tv-cat{font-size:11.5px;font-weight:700;color:#2f6f76;text-transform:uppercase;letter-spacing:.06em;margin-top:8px}
  .tv-title{font-family:Cormorant Garamond,serif;font-size:30px;font-weight:700;margin:3px 0 14px}
  .pin{font-size:11px;background:#fdf0d5;color:#9a6a10;border-radius:20px;padding:2px 9px;font-weight:700;vertical-align:middle}
  .post{background:#fff;border:1px solid #ece1cd;border-radius:14px;padding:18px 20px;box-shadow:0 5px 16px rgba(0,0,0,.04);margin-bottom:13px}
  .post.op{border-color:#f0d79a;background:#fffdf6}
  .p-head{display:flex;gap:12px;align-items:center;margin-bottom:10px}
  .av{width:40px;height:40px;border-radius:50%;object-fit:cover;flex:none;background:#FFC078;display:grid;place-items:center;font-family:Oswald,sans-serif;font-weight:700;color:#1F2933}
  .p-name{font-weight:700;font-size:14px}
  .p-when{font-size:12px;color:#8a7d68}
  .p-body{font-size:14.5px;line-height:1.6;color:#2a2530;white-space:pre-line}
  .p-del{margin-left:auto}
  .p-del button{background:none;border:none;color:#b3a68f;cursor:pointer;font-size:12px;text-decoration:underline}
  .replies-h{font-family:Oswald,sans-serif;font-size:15px;color:#8a7d68;margin:20px 0 10px}
  .reply-form{background:#fff;border:1px solid #ece1cd;border-radius:14px;padding:16px 18px;box-shadow:0 5px 16px rgba(0,0,0,.04)}
  .reply-form textarea{width:100%;padding:11px 13px;border:1.5px solid #e2d6c2;border-radius:9px;font:inherit;font-size:14.5px;min-height:90px;box-sizing:border-box;margin-bottom:10px}
  .reply-form button{background:#2f6f76;color:#fff;border:none;border-radius:9px;padding:11px 20px;font-weight:700;cursor:pointer}
  .locked{background:#f6efe2;border:1px solid #e2d6c2;border-radius:12px;padding:14px 16px;font-size:13.5px;color:#8a7d68;text-align:center}
  .err{background:#fbe4e4;color:#a4283a;border-radius:9px;padding:10px 12px;font-size:13px;margin-bottom:12px}
</style>

@if(session('flash'))<div class="status">{{ session('flash') }}</div>@endif

<div class="tv">
  <a class="tv-back" href="{{ route('tools.community') }}">← Community</a>
  <div class="tv-cat">{{ $thread->categoryLabel() }}</div>
  <h1 class="tv-title">{{ $thread->title }} @if($thread->pinned)<span class="pin">Pinned</span>@endif</h1>

  <div class="post op">
    <div class="p-head">
      @if($avatar($thread->author))<img class="av" src="{{ $avatar($thread->author) }}" alt="">@else<span class="av">{{ $thread->author?->initials() }}</span>@endif
      <div>
        <div class="p-name">{{ $thread->author?->display_name ?? 'Member' }}</div>
        <div class="p-when">{{ $thread->created_at?->format('j M Y, g:ia') }}</div>
      </div>
      @if($thread->community_member_id === $me->id)
        <span class="p-del"><form method="POST" action="{{ route('tools.community.thread.delete', $thread) }}" onsubmit="return confirm('Delete this whole thread?')">@csrf @method('DELETE')<button type="submit">Delete</button></form></span>
      @endif
    </div>
    <div class="p-body">{{ $thread->body }}</div>
  </div>

  <div class="replies-h">{{ $thread->replies->count() }} {{ \Illuminate\Support\Str::plural('reply', $thread->replies->count()) }}</div>

  @foreach($thread->replies as $reply)
    <div class="post" @if($loop->last) id="latest" @endif>
      <div class="p-head">
        @if($avatar($reply->author))<img class="av" src="{{ $avatar($reply->author) }}" alt="">@else<span class="av">{{ $reply->author?->initials() }}</span>@endif
        <div>
          <div class="p-name">{{ $reply->author?->display_name ?? 'Member' }}</div>
          <div class="p-when">{{ $reply->created_at?->diffForHumans() }}</div>
        </div>
        @if($reply->community_member_id === $me->id)
          <span class="p-del"><form method="POST" action="{{ route('tools.community.reply.delete', $reply) }}" onsubmit="return confirm('Delete this reply?')">@csrf @method('DELETE')<button type="submit">Delete</button></form></span>
        @endif
      </div>
      <div class="p-body">{{ $reply->body }}</div>
    </div>
  @endforeach

  @if($thread->locked)
    <div class="locked">🔒 This thread is locked — no new replies.</div>
  @else
    <div class="replies-h">Add your reply</div>
    <form class="reply-form" method="POST" action="{{ route('tools.community.reply', $thread) }}">
      @csrf
      @if($errors->any())<div class="err">{{ $errors->first() }}</div>@endif
      <textarea name="body" placeholder="Write a reply…" required></textarea>
      <button type="submit">Post reply</button>
    </form>
  @endif
</div>
@endsection
