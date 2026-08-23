@extends('jobs.public.layout')
@section('title', $job->title)
@section('head')
<style>
  .detail{max-width:760px;margin:0 auto;padding:36px 22px 10px}
  .back{font-size:13.5px;color:var(--ink-soft);text-decoration:none}
  .d-prop{font-size:15px;color:var(--rust-ink);font-weight:700;margin-top:14px}
  .d-title{font-family:var(--serif);font-size:clamp(30px,5vw,46px);font-weight:700;line-height:1.05;margin:4px 0 12px}
  .d-badges{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:18px}
  .badge{font-size:12.5px;font-weight:700;padding:5px 12px;border-radius:20px;background:var(--butter);color:var(--rust-ink)}
  .badge.alt{background:var(--sky);color:#0f4658}.badge.pay{background:var(--mint);color:#15603f}.badge.date{background:var(--pink);color:#8a2f27}
  .d-body{background:#fff;border:1px solid var(--bone);border-radius:16px;padding:26px 28px;font-size:15.5px;line-height:1.7;color:var(--ink-soft);white-space:pre-line;box-shadow:0 8px 24px rgba(31,41,51,.06)}
  .d-apply{margin:22px 0 8px;display:flex;gap:12px;align-items:center;flex-wrap:wrap}
  .d-apply .btn{padding:14px 26px;font-size:15px}
  .d-note{font-size:13px;color:var(--ink-soft)}
</style>
@endsection
@section('content')
<div class="detail">
  <a class="back" href="{{ route('jobs.board') }}">← All jobs</a>

  @if(session('applied'))
    <div class="flash">✓ Your application for <b>{{ session('applied') }}</b> has been sent. The property will be in touch. You can track it under <a href="{{ route('seeker.dashboard') }}" style="color:#2e7d4f;font-weight:700">My applications</a>.</div>
  @endif

  <div class="d-prop">{{ $job->property->motel ?: 'Retro Motel' }}@if($job->location) · {{ $job->location }}@endif</div>
  <h1 class="d-title">{{ $job->title }}</h1>
  <div class="d-badges">
    <span class="badge">{{ $job->typeLabel() }}</span>
    @if($job->departmentLabel())<span class="badge alt">{{ $job->departmentLabel() }}</span>@endif
    @if($job->pay)<span class="badge pay">{{ $job->pay }}</span>@endif
    @if($job->closes_at)<span class="badge date">Closes {{ $job->closes_at->format('j M Y') }}</span>@endif
  </div>

  <div class="d-body">{{ $job->description }}</div>

  <div class="d-apply">
    <a class="btn btn-rust" href="{{ route('jobs.apply', $job->slug) }}">Apply for this role →</a>
    @auth('seeker')
      <span class="d-note">Applying as {{ auth('seeker')->user()->name }}.</span>
    @else
      <span class="d-note">You'll create a quick account to apply.</span>
    @endauth
  </div>
</div>
@endsection
