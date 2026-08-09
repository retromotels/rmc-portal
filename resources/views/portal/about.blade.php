@extends('layouts.portal')
@section('title', 'About Us')
@section('content')
<style>
  .ab-wrap{max-width:820px}
  .ab-wrap h3{font-family:Oswald,sans-serif;font-size:26px;margin:0 0 14px;color:var(--ink,#2d2837)}
  .ab-body{font-size:16px;line-height:1.8;color:#4a4453}
  .ab-imgs{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:14px;margin-top:22px}
  .ab-imgs img{width:100%;height:220px;object-fit:cover;border-radius:14px;background:#eee}
</style>

<div class="ab-wrap">
  @if(empty($about['title']) && empty($about['body']) && empty($about['images']))
    <div class="dp-note">The About Us content hasn’t been added yet — check back soon.</div>
  @else
    @if(!empty($about['title']))<h3>{{ $about['title'] }}</h3>@endif
    @if(!empty($about['body']))<div class="ab-body">{!! nl2br(e($about['body'])) !!}</div>@endif
    @if(!empty($about['images']))
      <div class="ab-imgs">
        @foreach($about['images'] as $img)
          <img src="{{ $img }}" loading="lazy" alt="{{ $about['title'] ?? 'About' }}" onerror="this.style.display='none'">
        @endforeach
      </div>
    @endif
  @endif
</div>
@endsection
