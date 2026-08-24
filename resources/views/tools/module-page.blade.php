@extends('layouts.portal')
@section('title', $title)
@section('content')
<style>
  .mp{max-width:680px}
  .mp-hero{background:#1F2933;color:#F8EED6;border-radius:16px;padding:30px 32px;margin-bottom:20px}
  .mp-hero .ic{font-size:34px}
  .mp-hero h1{font-family:Cormorant Garamond,serif;font-size:32px;font-weight:700;margin:8px 0 0}
  .mp-body{background:#fff;border:1px solid #ece1cd;border-radius:14px;padding:24px 26px;box-shadow:0 6px 20px rgba(0,0,0,.05);font-size:15px;line-height:1.7;color:#3a3540;white-space:pre-line}
  .mp-cta{display:inline-block;margin-top:18px;background:#e0491d;color:#fff;border-radius:10px;padding:13px 26px;font-weight:700;text-decoration:none;font-family:Oswald,sans-serif;letter-spacing:.4px}
  .mp-soon{margin-top:16px;font-size:13px;color:#8a7d68}
</style>

<div class="mp">
  <div class="mp-hero"><div class="ic">{{ $icon }}</div><h1>{{ $title }}</h1></div>
  <div class="mp-body">{{ $body }}</div>
  @if($link)
    <a class="mp-cta" href="{{ $link }}" target="_blank" rel="noopener">{{ $cta }} →</a>
  @else
    <p class="mp-soon">The link will appear here soon — check back shortly.</p>
  @endif
</div>
@endsection
