@extends('layouts.portal')
@section('title', 'Website Checker')
@section('content')
@php
    $ringColor = fn ($s) => $s >= 80 ? 'var(--teal)' : ($s >= 55 ? 'var(--mustard)' : 'var(--coral)');
@endphp

<div class="chk-hero">
  <h2>How healthy is your website?</h2>
  <p>Enter your web address for an instant, plain-English audit — your score plus the exact fixes to improve your Google ranking, speed, tracking and security.</p>
  <form class="chk-form" method="GET" action="{{ route('checker') }}">
    <input name="url" placeholder="yourmotel.com.au" value="{{ $result['url'] ?? $default }}">
    <button class="btn btn-primary" type="submit">Audit</button>
  </form>
  <div class="sub" style="color:#cfeeee;margin-top:12px">🔍 SEO &amp; mobile · ⚡ Speed · 📊 Analytics · 🔒 Security &amp; SSL</div>
</div>

@if($result)
  <div class="card" style="margin-bottom:18px;display:flex;align-items:center;gap:24px;flex-wrap:wrap">
    <div class="score-ring" style="background:conic-gradient({{ $ringColor($result['overall']) }} {{ $result['overall'] * 3.6 }}deg,#efe4d2 0)">
      <div style="width:96px;height:96px;border-radius:50%;background:var(--paper);display:flex;flex-direction:column;align-items:center;justify-content:center">
        <span style="font-family:Oswald;font-weight:700;font-size:38px;color:{{ $ringColor($result['overall']) }}">{{ $result['overall'] }}</span>
        <span class="sub" style="font-size:10px">/ 100</span>
      </div>
    </div>
    <div style="flex:1;min-width:220px">
      <div class="lbl">Overall health</div>
      <h3 style="font-size:20px;margin:4px 0">{{ $result['overall'] >= 80 ? 'Looking strong' : ($result['overall'] >= 55 ? 'Room to improve' : 'Needs attention') }}</h3>
      <div class="sub">Audit for <b>{{ $result['url'] }}</b>. {{ $result['live'] ? 'Speed measured with live Google PageSpeed data.' : 'Preview scores — add a PageSpeed key for live data, or run the full audit.' }}</div>
      <a class="btn btn-teal sm" href="http://auditmywebsite.com.au/" target="_blank" rel="noopener" style="margin-top:10px">Run the full live audit →</a>
    </div>
  </div>

  <div class="grid g2">
    @foreach($result['cats'] as $c)
      <div class="chk-cat">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px">
          <span style="font-size:22px">{{ $c['icon'] }}</span>
          <span style="font-family:Oswald;font-weight:600;font-size:16px;flex:1">{{ $c['name'] }}</span>
          <span style="font-family:Oswald;font-weight:700;font-size:20px;color:{{ $ringColor($c['score']) }}">{{ $c['score'] }}</span>
        </div>
        <div class="chk-bar"><div style="height:100%;width:{{ $c['score'] }}%;background:{{ $ringColor($c['score']) }}"></div></div>
        @foreach($c['good'] as $g)<div class="chk-find"><span>✅</span><span>{{ $g }}</span></div>@endforeach
        @foreach($c['bad'] as $b)<div class="chk-find"><span>🔧</span><span>{{ $b }}</span></div>@endforeach
      </div>
    @endforeach
  </div>
@else
  <div class="card"><div class="sub">Enter your website address above and hit Audit to see your SEO, speed, analytics and security scores.</div></div>
@endif
@endsection
