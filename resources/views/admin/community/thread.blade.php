@extends('layouts.admin')
@section('title', $thread->title)
@section('content')
@php($avatar = fn($m) => $m ? $m->photoUrl() : null)
<style>
  .tv-back{color:#6c6577;text-decoration:none;font-size:13.5px}
  .tv-cat{font-size:11.5px;font-weight:700;color:#2f6f76;text-transform:uppercase;letter-spacing:.06em;margin-top:8px}
  .tv-title{font-family:Oswald,sans-serif;font-size:22px;margin:3px 0 16px}
  .post{background:var(--paper,#fff);border-radius:12px;padding:16px 18px;box-shadow:var(--shadow,0 6px 20px rgba(0,0,0,.06));margin-bottom:12px}
  .post.op{border-left:4px solid #f0d79a}
  .p-head{display:flex;gap:11px;align-items:center;margin-bottom:9px}
  .av{width:38px;height:38px;border-radius:50%;object-fit:cover;flex:none;background:#FFC078;display:grid;place-items:center;font-family:Oswald,sans-serif;font-weight:700;color:#1F2933}
  .p-name{font-weight:700;font-size:14px}.p-when{font-size:12px;color:#8a7d68}
  .p-body{font-size:14.5px;line-height:1.6;color:#2a2530;white-space:pre-line}
  .p-del{margin-left:auto}
  .p-del button{background:#fff;border:1px solid #f0c2c8;color:#a4283a;border-radius:7px;padding:5px 11px;cursor:pointer;font-size:12px;font-weight:600}
  .rh{font-family:Oswald,sans-serif;font-size:14px;color:#8a7d68;margin:18px 0 10px}
</style>

@if(session('status'))<div class="status">{{ session('status') }}</div>@endif

<a class="tv-back" href="{{ route('admin.community') }}">← Community</a>
<div class="tv-cat">{{ $thread->categoryLabel() }}</div>
<h1 class="tv-title">{{ $thread->title }}</h1>

<div class="post op">
  <div class="p-head">
    @if($avatar($thread->author))<img class="av" src="{{ $avatar($thread->author) }}" alt="">@else<span class="av">{{ $thread->author?->initials() }}</span>@endif
    <div>
      <div class="p-name">{{ $thread->author?->display_name ?? 'Member' }}</div>
      <div class="p-when">{{ $thread->created_at?->format('j M Y, g:ia') }}</div>
    </div>
    <span class="p-del"><form method="POST" action="{{ route('admin.community.thread.delete', $thread) }}" onsubmit="return confirm('Delete this entire thread?')">@csrf @method('DELETE')<button type="submit">Delete thread</button></form></span>
  </div>
  <div class="p-body">{{ $thread->body }}</div>
</div>

<div class="rh">{{ $thread->replies->count() }} {{ \Illuminate\Support\Str::plural('reply', $thread->replies->count()) }}</div>

@foreach($thread->replies as $reply)
  <div class="post">
    <div class="p-head">
      @if($avatar($reply->author))<img class="av" src="{{ $avatar($reply->author) }}" alt="">@else<span class="av">{{ $reply->author?->initials() }}</span>@endif
      <div>
        <div class="p-name">{{ $reply->author?->display_name ?? 'Member' }}</div>
        <div class="p-when">{{ $reply->created_at?->format('j M Y, g:ia') }}</div>
      </div>
      <span class="p-del"><form method="POST" action="{{ route('admin.community.reply.delete', $reply) }}" onsubmit="return confirm('Delete this reply?')">@csrf @method('DELETE')<button type="submit">Delete</button></form></span>
    </div>
    <div class="p-body">{{ $reply->body }}</div>
  </div>
@endforeach
@endsection
