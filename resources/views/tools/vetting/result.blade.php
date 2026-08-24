@extends('layouts.portal')
@section('title', 'Vetting result · @' . $check->handle)
@section('content')
@php
  $arcColor = $check->band() === 'good' ? '#8FE2B6' : ($check->band() === 'warn' ? '#FFC078' : '#FF9C85');
  $dash = 440; $offset = $dash - ($dash * $check->score / 100);
@endphp
<style>
  .r-back{color:#6c6577;text-decoration:none;font-size:13.5px}
  .r-h{font-family:Cormorant Garamond,Georgia,serif;font-size:30px;margin:8px 0 2px;font-weight:700}
  .r-h em{color:#E0491D;font-style:italic}
  .r-sub{font-size:13px;color:#8a7d68;margin-bottom:18px}
  .verdict{display:grid;grid-template-columns:auto 1fr;gap:30px;align-items:center;background:#1F2933;color:#F8EED6;border-radius:18px;padding:28px 32px}
  @media(max-width:680px){.verdict{grid-template-columns:1fr;gap:18px;text-align:center;justify-items:center}}
  .gauge{position:relative;width:160px;height:160px}
  .gauge .val{position:absolute;inset:0;display:grid;place-items:center;text-align:center}
  .gauge .val b{font-family:Oswald,sans-serif;font-size:46px;line-height:1;display:block}
  .gauge .val small{font-size:11px;opacity:.6;letter-spacing:.1em;text-transform:uppercase}
  .vtag{display:inline-block;font-size:10px;font-weight:700;letter-spacing:.18em;text-transform:uppercase;background:{{ $arcColor }};color:#1F2933;padding:5px 12px;border-radius:20px;margin-bottom:10px}
  .verdict h3{font-family:Cormorant Garamond,Georgia,serif;font-size:26px;margin:0 0 8px;color:#FF9C85}
  .verdict p{margin:0;font-size:14.5px;opacity:.92;line-height:1.6}
  .tiles{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin:20px 0}
  .tile{background:#fff;border:1px solid #ece1cd;border-radius:12px;padding:14px 16px;box-shadow:0 4px 14px rgba(0,0,0,.04)}
  .tile .l{font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#8a7d68}
  .tile .v{font-family:Oswald,sans-serif;font-size:24px;margin:3px 0}
  .tile .n{font-size:11.5px;color:#8a7d68}
  .card{background:#fff;border:1px solid #ece1cd;border-radius:14px;padding:20px 22px;box-shadow:0 6px 20px rgba(0,0,0,.05);margin-bottom:16px}
  .card h4{font-family:Cormorant Garamond,Georgia,serif;font-size:20px;margin:0 0 12px}
  .geo .row{display:flex;align-items:center;gap:12px;margin-bottom:8px}
  .geo .place{width:180px;font-size:13.5px;flex:none}
  .geo .barwrap{flex:1;height:16px;background:#f0e7d4;border-radius:8px;overflow:hidden}
  .geo .bar{height:100%;border-radius:8px}
  .geo .pct{width:44px;text-align:right;font-size:12.5px;font-weight:700}
  .dim{margin-bottom:14px}
  .dim .t{display:flex;justify-content:space-between;font-size:13.5px;font-weight:700;margin-bottom:5px}
  .dim .track{height:9px;background:#f0e7d4;border-radius:6px;overflow:hidden}
  .dim .fill{height:100%;border-radius:6px}
  .dim .why{font-size:12.5px;color:#6a6152;margin-top:5px;line-height:1.5}
  .reply{background:#FDF6E7;border:1px solid #E0491D;border-radius:12px;padding:16px 18px}
  .reply b{display:block;font-size:11px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:#9C3A1C;margin-bottom:8px}
  .reply p{margin:0;font-size:14.5px;line-height:1.6;font-style:italic}
  .copy{margin-top:10px;background:#1F2933;color:#F8EED6;border:none;border-radius:8px;padding:9px 16px;font-weight:700;cursor:pointer;font-size:13px}
  .note{background:#FDF6E7;border:1px solid #efe4d2;border-radius:10px;padding:12px 14px;font-size:12.5px;color:#6a6152;line-height:1.55;margin-top:16px}
</style>

<a class="r-back" href="{{ route('tools.vetting') }}">← New check</a>
<h1 class="r-h">{{ '@'.$check->handle }} <em>×</em> {{ $check->property_name }}</h1>
<div class="r-sub">Checked {{ $check->created_at?->format('j M Y') }} · assisted read from public signals</div>

<div class="verdict">
  <div class="gauge">
    <svg width="160" height="160" viewBox="0 0 168 168">
      <circle cx="84" cy="84" r="70" fill="none" stroke="rgba(248,238,214,.16)" stroke-width="16"/>
      <circle cx="84" cy="84" r="70" fill="none" stroke="{{ $arcColor }}" stroke-width="16" stroke-linecap="round"
              stroke-dasharray="{{ $dash }}" stroke-dashoffset="{{ $offset }}" transform="rotate(-90 84 84)"/>
    </svg>
    <div class="val"><div><b>{{ $check->score }}</b><small>Out of 100</small></div></div>
  </div>
  <div>
    <span class="vtag">{{ $check->verdict_tag }}</span>
    <h3>{{ $check->verdict_heading }}</h3>
    <p>{{ $check->verdict_body }}</p>
  </div>
</div>

<div class="tiles">
  <div class="tile"><div class="l">Engagement rate</div><div class="v" style="color:{{ ($metrics['engagement_rate'] ?? 0) >= ($metrics['benchmark'] ?? 3.5) ? '#2E7D52' : '#C0341A' }}">{{ $metrics['engagement_rate'] ?? '—' }}%</div><div class="n">benchmark {{ $metrics['benchmark'] ?? '—' }}% at this size</div></div>
  <div class="tile"><div class="l">Avg likes</div><div class="v">{{ number_format($metrics['avg_likes'] ?? 0) }}</div><div class="n">recent posts</div></div>
  <div class="tile"><div class="l">Avg comments</div><div class="v">{{ number_format($metrics['avg_comments'] ?? 0) }}</div><div class="n">recent posts</div></div>
  <div class="tile"><div class="l">Posts / week</div><div class="v">{{ $metrics['posts_per_week'] ?? '—' }}</div><div class="n">posting rhythm</div></div>
  <div class="tile"><div class="l">Est. reach / post</div><div class="v">{{ number_format($metrics['reach_low'] ?? 0) }}–{{ number_format($metrics['reach_high'] ?? 0) }}</div><div class="n">accounts, anywhere</div></div>
  <div class="tile"><div class="l">Reach in your market</div><div class="v" style="color:{{ ($metrics['market_reach_high'] ?? 0) < 100 ? '#C0341A' : '#2E7D52' }}">{{ number_format($metrics['market_reach_low'] ?? 0) }}–{{ number_format($metrics['market_reach_high'] ?? 0) }}</div><div class="n">people who could drive to you</div></div>
</div>

@if(!empty($geo))
  <div class="card geo">
    <h4>Where their content is set</h4>
    <p style="font-size:13px;color:#8a7d68;margin:-4px 0 14px">From the location tags you entered. Green is inside your drive market.</p>
    @foreach($geo as $g)
      <div class="row">
        <div class="place">{{ $g['place'] }}</div>
        <div class="barwrap"><div class="bar" style="width:{{ $g['pct'] }}%;background:{{ $g['in_market'] ? '#8FE2B6' : '#FFB3A7' }}"></div></div>
        <div class="pct">{{ $g['pct'] }}%</div>
      </div>
    @endforeach
  </div>
@endif

<div class="card">
  <h4>Scored against {{ $check->property_name }}, not motels in general</h4>
  <p style="font-size:13px;color:#8a7d68;margin:-4px 0 16px">Five weighted dimensions. The audience map carries the most weight — a creator your guests can't become is worth little, however good the content.</p>
  @foreach($check->dimensions as $d)
    @php $col = $d['score'] >= 66 ? '#2E7D52' : ($d['score'] >= 40 ? '#B8791C' : '#C0341A'); @endphp
    <div class="dim">
      <div class="t"><span>{{ $d['label'] }} <span style="opacity:.5;font-weight:400">({{ $d['weight'] }}% of score)</span></span><b>{{ $d['score'] }}</b></div>
      <div class="track"><div class="fill" style="width:{{ $d['score'] }}%;background:{{ $col }}"></div></div>
      <div class="why">{{ $d['why'] }}</div>
    </div>
  @endforeach
</div>

<div class="reply">
  <b>Suggested reply, ready to send</b>
  <p id="reply">{{ $check->suggested_reply }}</p>
  <button class="copy" onclick="navigator.clipboard.writeText(document.getElementById('reply').innerText).then(()=>{this.textContent='Copied ✓'})">Copy reply</button>
</div>

<div class="note">
  <strong>What this can and can't see:</strong> the follower, engagement and location signals above come from what was entered off the public profile. Real audience age and location are only visible to the account holder — ask any creator you're serious about for a screenshot of their Instagram Insights audience tab to confirm the read.
</div>
@endsection
