@extends('layouts.portal')
@section('title', 'Community')
@section('content')
@php($avatar = function($m){ return $m && $m->avatar_path ? route('tools.community.avatar', $m) : null; })
<style>
  .ch-top{display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:12px;margin-bottom:16px}
  .ch-h{font-family:Oswald,sans-serif;font-size:26px;margin:0}
  .ch-sub{font-size:13px;color:#8a7d68;margin-top:2px}
  .ch-actions{display:flex;gap:9px;flex-wrap:wrap}
  .btn-d{background:#fff;border:1px solid #e2d6c2;border-radius:9px;padding:10px 15px;font-weight:700;text-decoration:none;color:#3a3540;font-size:13.5px}
  .btn-r{background:#e0491d;color:#fff;border-radius:9px;padding:10px 16px;font-weight:700;text-decoration:none;font-size:13.5px}
  .cats{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px}
  .cat{padding:7px 13px;border-radius:20px;border:1px solid #e2d6c2;background:#fff;font-size:13px;font-weight:600;text-decoration:none;color:#4a4453}
  .cat.on{background:#2f6f76;color:#fff;border-color:#2f6f76}
  .cat .n{opacity:.6;font-weight:400}
  .tr{display:flex;gap:14px;align-items:flex-start;background:#fff;border:1px solid #ece1cd;border-radius:13px;padding:15px 17px;box-shadow:0 5px 16px rgba(0,0,0,.04);margin-bottom:11px;text-decoration:none;color:inherit}
  .tr:hover{border-color:#FFC078}
  .av{width:42px;height:42px;border-radius:50%;object-fit:cover;flex:none;background:#FFC078;display:grid;place-items:center;font-family:Oswald,sans-serif;font-weight:700;color:#1F2933}
  .tr-main{flex:1;min-width:0}
  .tr-title{font-family:Oswald,sans-serif;font-size:16.5px;margin:0 0 2px;display:flex;align-items:center;gap:7px}
  .pin{font-size:11px;background:#fdf0d5;color:#9a6a10;border-radius:20px;padding:2px 8px;font-weight:700}
  .lock{font-size:11px;color:#8a7d68}
  .tr-meta{font-size:12.5px;color:#8a7d68}
  .tr-cat{font-size:11px;font-weight:700;color:#2f6f76}
  .tr-r{flex:none;text-align:center;color:#8a7d68;font-size:12.5px;align-self:center}
  .tr-r b{display:block;font-family:Oswald,sans-serif;font-size:18px;color:#3a3540}
  .empty{background:#fff;border:1px solid #ece1cd;border-radius:13px;padding:36px;text-align:center;color:#8a7d68}
  .pager{display:flex;gap:14px;align-items:center;justify-content:center;margin-top:16px;font-size:13px}
  .pager a{color:#2f6f76;font-weight:700;text-decoration:none}
</style>

@if(session('flash'))<div class="status">{{ session('flash') }}</div>@endif

<div class="ch-top">
  <div>
    <h1 class="ch-h">👥 Community</h1>
    <div class="ch-sub">{{ $members }} {{ \Illuminate\Support\Str::plural('member', $members) }} · welcome, {{ $me->display_name }}</div>
  </div>
  <div class="ch-actions">
    <a class="btn-d" href="{{ route('tools.community.directory') }}">📇 Member directory</a>
    <a class="btn-d" href="{{ route('tools.community.profile') }}">Edit profile</a>
    <a class="btn-r" href="{{ route('tools.community.thread.create') }}">+ New post</a>
  </div>
</div>

<div class="cats">
  <a class="cat {{ !$category ? 'on' : '' }}" href="{{ route('tools.community') }}">All</a>
  @foreach(config('rmc.forum_categories') as $k => $lbl)
    <a class="cat {{ $category === $k ? 'on' : '' }}" href="{{ route('tools.community', ['category' => $k]) }}">{{ $lbl }} <span class="n">{{ $counts[$k] ?? 0 }}</span></a>
  @endforeach
</div>

@forelse($threads as $t)
  <a class="tr" href="{{ route('tools.community.thread', $t) }}">
    @if($avatar($t->author))<img class="av" src="{{ $avatar($t->author) }}" alt="">@else<span class="av">{{ $t->author?->initials() }}</span>@endif
    <div class="tr-main">
      <h3 class="tr-title">{{ $t->title }} @if($t->pinned)<span class="pin">Pinned</span>@endif @if($t->locked)<span class="lock">🔒</span>@endif</h3>
      <div class="tr-meta"><span class="tr-cat">{{ $t->categoryLabel() }}</span> · {{ $t->author?->display_name ?? 'Member' }} · {{ $t->last_reply_at?->diffForHumans() }}</div>
    </div>
    <div class="tr-r"><b>{{ $t->replies_count }}</b>{{ \Illuminate\Support\Str::plural('reply', $t->replies_count) }}</div>
  </a>
@empty
  <div class="empty">No posts here yet — <a href="{{ route('tools.community.thread.create') }}" style="color:#e0491d;font-weight:700">start the conversation</a>.</div>
@endforelse

@if($threads->hasPages())
  <div class="pager">
    @if(!$threads->onFirstPage())<a href="{{ $threads->previousPageUrl() }}">← Newer</a>@endif
    <span style="color:#8a7d68">Page {{ $threads->currentPage() }} of {{ $threads->lastPage() }}</span>
    @if($threads->hasMorePages())<a href="{{ $threads->nextPageUrl() }}">Older →</a>@endif
  </div>
@endif
@endsection
