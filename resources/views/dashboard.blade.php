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

<div class="grid g3">
  @foreach($tasks as $s)
    @php $pct = $user->sectionPct($s['id']); $ok = $pct >= 100; @endphp
    <div class="card" style="padding:18px;{{ $ok ? '' : 'border-color:#f0d9a0' }}">
      <div style="display:flex;justify-content:space-between;align-items:flex-start">
        <div style="font-size:24px">{{ $s['icon'] }}</div>
        @if($ok)<span class="flag current">✓ Done</span>
        @elseif($s['priority'] ?? false)<span class="flag due">This week</span>
        @else<span class="flag none">{{ $pct }}%</span>@endif
      </div>
      <h3 style="font-size:16px;margin:10px 0 3px">{{ $s['id'] }}. {{ $s['title'] }}</h3>
      <div class="sub" style="font-size:12.5px;min-height:32px">{{ $s['note'] ?? '' }}</div>
      <a class="btn {{ $ok ? 'btn-ghost' : 'btn-primary' }} btn-block sm" href="{{ route('registration.index', ['open' => $s['id']]) }}" style="margin-top:10px">{{ $ok ? 'Review' : 'Complete →' }}</a>
    </div>
  @endforeach
</div>
@endsection
