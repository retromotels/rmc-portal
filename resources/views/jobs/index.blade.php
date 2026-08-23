@extends('layouts.portal')
@section('title', 'Jobs')
@section('content')
<style>
  .jb-head{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:16px}
  .jb-lead{font-size:14px;color:#6c6577;line-height:1.6;max-width:640px}
  .jb-card{background:var(--paper,#fff);border-radius:13px;padding:16px 18px;box-shadow:var(--shadow,0 6px 20px rgba(0,0,0,.06));margin-bottom:12px;display:flex;gap:14px;align-items:flex-start;flex-wrap:wrap}
  .jb-main{flex:1;min-width:220px}
  .jb-title{font-family:Oswald,sans-serif;font-size:17px;margin:0 0 3px}
  .jb-meta{font-size:12.5px;color:#8a7d68}
  .jb-flag{font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;text-transform:uppercase;letter-spacing:.4px}
  .f-pending{background:#fdf0d5;color:#9a6a10}
  .f-approved{background:#dff3e6;color:#2e7d4f}
  .f-rejected{background:#fbe4e4;color:#a4283a}
  .f-closed{background:#eee;color:#777}
  .jb-actions{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
  .jb-actions a,.jb-actions button{font-size:12.5px;border:1px solid #e2d6c2;background:#fff;border-radius:8px;padding:7px 11px;cursor:pointer;text-decoration:none;color:#4a4453;font-family:inherit}
  .jb-actions .danger{color:#a4283a;border-color:#f0c2c8}
  .jb-new{background:var(--coral,#ee6a5a);color:#fff;border:none;border-radius:10px;padding:11px 20px;font-weight:700;font-family:Oswald,sans-serif;letter-spacing:.5px;text-decoration:none}
  .jb-empty{background:var(--paper,#fff);border-radius:13px;padding:34px;text-align:center;color:#8a7d68;box-shadow:var(--shadow,0 6px 20px rgba(0,0,0,.06))}
  .apps-pill{font-size:12px;background:#eef4ff;color:#33507a;border-radius:20px;padding:3px 10px;font-weight:600}
</style>

@if(session('status'))<div class="status">{{ session('status') }}</div>@endif

<div class="jb-head">
  <p class="jb-lead">Post jobs for your property. Listings are reviewed by head office, then appear on the public board at <b>jobs.retromotels.com</b>. Applications come straight back to you here.</p>
  <a class="jb-new" href="{{ route('jobs.create') }}">+ Post a job</a>
</div>

@forelse($jobs as $job)
  <div class="jb-card">
    <div class="jb-main">
      <h3 class="jb-title">{{ $job->title }}</h3>
      <div class="jb-meta">{{ $job->typeLabel() }}@if($job->departmentLabel()) · {{ $job->departmentLabel() }}@endif @if($job->location) · {{ $job->location }}@endif @if($job->pay) · {{ $job->pay }}@endif</div>
      @if($job->status === 'rejected' && $job->reject_reason)<div class="jb-meta" style="color:#a4283a;margin-top:4px">Head office note: {{ $job->reject_reason }}</div>@endif
    </div>
    <span class="jb-flag f-{{ $job->status }}">{{ $job->status }}</span>
    <div class="jb-actions">
      <a href="{{ route('jobs.applicants', $job) }}"><span class="apps-pill">{{ $job->applications_count }} applicant{{ $job->applications_count === 1 ? '' : 's' }}</span></a>
      <a href="{{ route('jobs.edit', $job) }}">Edit</a>
      @if($job->status !== 'closed')
        <form method="POST" action="{{ route('jobs.close', $job) }}" style="display:inline">@csrf<button type="submit">Close</button></form>
      @endif
      <form method="POST" action="{{ route('jobs.destroy', $job) }}" style="display:inline" onsubmit="return confirm('Delete this job and its applications?')">@csrf @method('DELETE')<button type="submit" class="danger">Delete</button></form>
    </div>
  </div>
@empty
  <div class="jb-empty">You haven't posted any jobs yet. Click <b>Post a job</b> to advertise a role — it'll show on the public board once approved.</div>
@endforelse
@endsection
