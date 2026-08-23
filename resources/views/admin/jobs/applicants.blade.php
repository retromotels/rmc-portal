@extends('layouts.admin')
@section('title', 'Applicants')
@section('content')
<style>
  .ap-back{color:#6c6577;text-decoration:none;font-size:13.5px}
  .ap-h{font-family:Oswald,sans-serif;font-size:20px;margin:8px 0 3px}
  .ap-sub{font-size:13px;color:#8a7d68;margin-bottom:16px}
  .ap-card{background:var(--paper,#fff);border-radius:12px;padding:15px 17px;box-shadow:var(--shadow,0 6px 20px rgba(0,0,0,.06));margin-bottom:11px}
  .ap-top{display:flex;justify-content:space-between;align-items:baseline;flex-wrap:wrap;gap:6px}
  .ap-name{font-weight:700;font-size:15px}.ap-when{font-size:12px;color:#8a7d68}
  .ap-contact{font-size:13px;color:#4a4453;margin:5px 0}.ap-contact a{color:#2f6f76}
  .ap-msg{font-size:13.5px;line-height:1.55;white-space:pre-line;margin-top:8px;padding-top:8px;border-top:1px solid #efe4d2}
  .ap-empty{background:var(--paper,#fff);border-radius:12px;padding:30px;text-align:center;color:#8a7d68;box-shadow:var(--shadow,0 6px 20px rgba(0,0,0,.06))}
</style>

<a class="ap-back" href="{{ route('admin.jobs') }}">← Jobs</a>
<h2 class="ap-h">{{ $job->title }}</h2>
<div class="ap-sub">{{ $job->property->motel ?: $job->property->name }}@if($job->location) · {{ $job->location }}@endif · {{ $job->applications->count() }} applicant{{ $job->applications->count() === 1 ? '' : 's' }}</div>

@forelse($job->applications->sortByDesc('created_at') as $app)
  <div class="ap-card">
    <div class="ap-top"><span class="ap-name">{{ $app->name }}</span><span class="ap-when">{{ $app->created_at?->format('j M Y, g:ia') }}</span></div>
    <div class="ap-contact">✉ <a href="mailto:{{ $app->email }}">{{ $app->email }}</a>@if($app->phone) · ☎ {{ $app->phone }}@endif</div>
    @if($app->message)<div class="ap-msg">{{ $app->message }}</div>@endif
  </div>
@empty
  <div class="ap-empty">No applications yet.</div>
@endforelse
@endsection
