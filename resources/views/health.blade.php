@extends('layouts.portal')
@section('title', 'Health Check')
@section('content')
@php
    $ringColor = fn ($s) => $s >= 80 ? 'var(--teal)' : ($s >= 55 ? 'var(--mustard)' : 'var(--coral)');
@endphp

<div class="chk-hero">
  <h2>How healthy is your website?</h2>
  <p>Enter your web address for an instant, plain-English audit — your score plus the exact fixes to improve your Google ranking, speed, tracking and security.</p>
  <form class="chk-form" method="GET" action="{{ route('health') }}">
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
      <div class="sub">Audit for <b>{{ $result['url'] }}</b>. {{ $result['live'] ? 'Speed measured with live Google PageSpeed data.' : 'Preview scores — run the full audit for live data.' }}</div>
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
@endif

<div class="section-title" style="margin-top:26px"><h3>Free health checks &amp; audits</h3><div class="rule"></div><span class="sub">Request one and our team will do the rest</span></div>
<div class="grid g3">
  @foreach(config('rmc.health_requests') as $key => $h)
    @php $done = in_array($key, $requested); @endphp
    <div class="card" style="padding:18px">
      <div style="font-size:26px">{{ $h['icon'] }}</div>
      <h3 style="font-size:16px;margin:8px 0 4px">{{ $h['label'] }}</h3>
      <div class="sub" style="font-size:12.5px;min-height:52px">{{ $h['blurb'] }}</div>
      @if($done)
        <div class="btn btn-ghost btn-block sm" style="margin-top:8px;pointer-events:none">✓ Requested</div>
      @else
        <form method="POST" action="{{ route('health.request', $key) }}">@csrf
          <button class="btn btn-primary btn-block sm" type="submit" style="margin-top:8px">Request free →</button>
        </form>
      @endif
    </div>
  @endforeach
</div>
@endsection
