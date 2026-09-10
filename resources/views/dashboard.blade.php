@extends('layouts.portal')
@section('title', 'Dashboard')
@section('content')

@php $wb = \App\Models\Setting::get('welcome_banner', []); @endphp
@if(!empty($wb['title']) || !empty($wb['copy']) || !empty($wb['image']))
  <div class="welcome-banner {{ !empty($wb['image']) ? 'has-img' : '' }}">
    <div class="wb-text">
      @if(!empty($wb['title']))<h3>{{ $wb['title'] }}</h3>@endif
      @if(!empty($wb['copy']))<p>{!! nl2br(e($wb['copy'])) !!}</p>@endif
    </div>
    @if(!empty($wb['image']))<img class="wb-img" src="{{ $wb['image'] }}" alt="" onerror="this.style.display='none'">@endif
  </div>
@endif

@unless($user->details_complete)
  <div class="banner">
    <span class="bic">📝</span>
    <div><b>Complete your details</b><div class="bs">Finish your property profile so RMC can set up your membership.</div></div>
    <a class="btn btn-primary sm" href="{{ route('details.show') }}">Complete now →</a>
  </div>
@endunless

<div class="dp-note" style="margin-bottom:20px">🔒 All data you provide is stored securely and is never shared in a way that identifies your property. Where the collective uses your data — for benchmarking, group tenders or reporting — it is aggregated and anonymised so individual properties cannot be identified.</div>

@php $done = $tasks->filter(fn ($s) => $user->sectionComplete($s['id']))->count(); @endphp
<div class="section-title"><h3>Complete your registration</h3><div class="rule"></div><span class="sub">{{ $user->overallPct() }}% overall · {{ $done }} of {{ $tasks->count() }} tasks</span></div>
<div style="height:10px;background:#efe4d2;border-radius:6px;overflow:hidden;margin-bottom:16px"><div style="height:100%;width:{{ $user->overallPct() }}%;background:linear-gradient(90deg,var(--teal),var(--aqua))"></div></div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px;margin-bottom:26px">
  @foreach($tasks as $s)
    @php $pct = $user->sectionPct($s['id']); $ok = $pct >= 100; @endphp
    <a href="{{ route('registration.index', ['open' => $s['id']]) }}" style="text-decoration:none;color:inherit;background:#fff;border:1px solid {{ $ok ? '#e7ddc8' : '#f0d9a0' }};border-radius:12px;padding:13px 12px;text-align:center;display:block;transition:border-color .12s" onmouseover="this.style.borderColor='#2f6f76'" onmouseout="this.style.borderColor='{{ $ok ? '#e7ddc8' : '#f0d9a0' }}'">
      <div style="font-size:22px">{{ $s['icon'] }}</div>
      <div style="font-size:12.5px;font-weight:700;margin-top:5px;line-height:1.25">{{ $s['id'] }}. {{ $s['title'] }}</div>
      <div style="margin-top:7px">
        @if($ok)<span class="flag current">✓ Done</span>
        @elseif($s['priority'] ?? false)<span class="flag due">This week</span>
        @else<span class="flag none">{{ $pct }}%</span>@endif
      </div>
    </a>
  @endforeach
</div>

@php
  $tools = [];
  if (\App\Models\Setting::bool('module_ai_assist')) $tools[] = ['✨','AI Assist','Ask Claude for help — copy, guest replies, ideas', route('tools.ai-assist')];
  $tools[] = ['🩺','Health Check','Free OTA, SEO & Google audits', route('health')];
  $tools[] = ['💬','Chat Widget','Add a guest chat widget to your site', route('tools.chat-widget')];
  $tools[] = ['💼','Jobs','Post and manage your job listings', route('jobs.index')];
  if (config('rmc.features.documents')) $tools[] = ['📚','Resource Library','SOP templates that prefill with your details', route('tools.documents')];
  if (config('rmc.features.suppliers')) $tools[] = ['📇','Suppliers','Members-only deals and offers', route('tools.suppliers')];
  if (\App\Models\Setting::bool('module_roundtable')) $tools[] = ['🎙️','Monthly Roundtable','Join the monthly collective call', route('tools.roundtable')];
  if (\App\Models\Setting::bool('module_community')) $tools[] = ['👥','Community','The member directory and forum', route('tools.community')];
@endphp

<div class="section-title"><h3>Your tools</h3><div class="rule"></div><span class="sub">Everything in one place</span></div>
<div class="grid g3">
  @foreach($tools as [$icon,$name,$desc,$url])
    <a class="card" href="{{ $url }}" style="padding:18px;text-decoration:none;color:inherit;display:block;transition:transform .12s,box-shadow .12s" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
      <div style="font-size:26px">{{ $icon }}</div>
      <h3 style="font-size:16px;margin:10px 0 3px">{{ $name }}</h3>
      <div class="sub" style="font-size:12.5px;min-height:32px">{{ $desc }}</div>
      <div style="margin-top:8px;color:#2f6f76;font-weight:700;font-size:13.5px">Open →</div>
    </a>
  @endforeach
</div>
@endsection
