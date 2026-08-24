@extends('layouts.portal')
@section('title', 'Member directory')
@section('content')
@php($avatar = function($m){ return $m && $m->avatar_path ? route('tools.community.avatar', $m) : null; })
<style>
  .cd-top{display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:12px;margin-bottom:16px}
  .cd-h{font-family:Oswald,sans-serif;font-size:26px;margin:0}
  .cd-back{color:#6c6577;text-decoration:none;font-size:13.5px}
  .cd-form{display:flex;gap:9px;margin-bottom:18px}
  .cd-form input{flex:1;max-width:340px;padding:10px 13px;border:1.5px solid #e2d6c2;border-radius:9px;font:inherit;font-size:14px;background:#fff}
  .cd-form button{background:#2f6f76;color:#fff;border:none;border-radius:9px;padding:10px 16px;font-weight:700;cursor:pointer}
  .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(270px,1fr));gap:16px}
  .mc{background:#fff;border:1px solid #ece1cd;border-radius:14px;padding:18px 20px;box-shadow:0 5px 16px rgba(0,0,0,.04)}
  .mc-head{display:flex;gap:13px;align-items:center;margin-bottom:10px}
  .av{width:52px;height:52px;border-radius:50%;object-fit:cover;flex:none;background:#FFC078;display:grid;place-items:center;font-family:Oswald,sans-serif;font-weight:700;font-size:19px;color:#1F2933}
  .mc-name{font-family:Oswald,sans-serif;font-size:17px;margin:0}
  .mc-town{font-size:12.5px;color:#2f6f76;font-weight:600}
  .mc-headline{font-size:13px;color:#4a4453;font-weight:600;margin-bottom:6px}
  .mc-bio{font-size:13px;color:#6a6152;line-height:1.5;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden}
  .mc-web{display:inline-block;margin-top:9px;font-size:12.5px;color:#e0491d;font-weight:700;text-decoration:none}
  .me-badge{font-size:10.5px;font-weight:800;background:#dff3e6;color:#2e7d4f;border-radius:20px;padding:2px 8px;margin-left:6px}
  .empty{background:#fff;border:1px solid #ece1cd;border-radius:14px;padding:36px;text-align:center;color:#8a7d68}
  .pager{display:flex;gap:14px;align-items:center;justify-content:center;margin-top:18px;font-size:13px}
  .pager a{color:#2f6f76;font-weight:700;text-decoration:none}
</style>

@if(session('flash'))<div class="status">{{ session('flash') }}</div>@endif

<a class="cd-back" href="{{ route('tools.community') }}">← Community</a>
<div class="cd-top">
  <h1 class="cd-h">📇 Member directory</h1>
  <a class="mc-web" href="{{ route('tools.community.profile') }}" style="margin:0">Edit my profile →</a>
</div>

<form class="cd-form" method="GET" action="{{ route('tools.community.directory') }}">
  <input type="text" name="q" value="{{ $kw }}" placeholder="Search by name or town…">
  <button type="submit">Search</button>
</form>

<div class="grid">
  @forelse($members as $m)
    <div class="mc">
      <div class="mc-head">
        @if($avatar($m))<img class="av" src="{{ $avatar($m) }}" alt="">@else<span class="av">{{ $m->initials() }}</span>@endif
        <div>
          <h3 class="mc-name">{{ $m->display_name }}@if($m->id === $me->id)<span class="me-badge">You</span>@endif</h3>
          @if($m->town)<div class="mc-town">{{ $m->town }}</div>@endif
        </div>
      </div>
      @if($m->headline)<div class="mc-headline">{{ $m->headline }}</div>@endif
      @if($m->bio)<div class="mc-bio">{{ $m->bio }}</div>@endif
      @if($m->website)<a class="mc-web" href="{{ \Illuminate\Support\Str::startsWith($m->website, 'http') ? $m->website : 'https://'.$m->website }}" target="_blank" rel="noopener">Visit website ↗</a>@endif
    </div>
  @empty
    <div class="empty">No members match that search.</div>
  @endforelse
</div>

@if($members->hasPages())
  <div class="pager">
    @if(!$members->onFirstPage())<a href="{{ $members->previousPageUrl() }}">← Prev</a>@endif
    <span style="color:#8a7d68">Page {{ $members->currentPage() }} of {{ $members->lastPage() }}</span>
    @if($members->hasMorePages())<a href="{{ $members->nextPageUrl() }}">Next →</a>@endif
  </div>
@endif
@endsection
