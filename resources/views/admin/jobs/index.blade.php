@extends('layouts.admin')
@section('title', 'Jobs')
@section('content')
<style>
  .jt-tabs{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:16px}
  .jt-tabs a{padding:8px 14px;border-radius:9px;border:1px solid #e2d6c2;background:#fff;text-decoration:none;color:#4a4453;font-size:13px;font-weight:600}
  .jt-tabs a.on{background:#2f6f76;color:#fff;border-color:#2f6f76}
  .jt-tabs a .n{opacity:.7;font-weight:400}
  .ja-card{background:var(--paper,#fff);border-radius:13px;padding:16px 18px;box-shadow:var(--shadow,0 6px 20px rgba(0,0,0,.06));margin-bottom:12px}
  .ja-top{display:flex;justify-content:space-between;align-items:baseline;flex-wrap:wrap;gap:8px}
  .ja-title{font-family:Oswald,sans-serif;font-size:17px;margin:0}
  .ja-prop{font-size:12.5px;color:#8a7d68}
  .ja-meta{font-size:12.5px;color:#8a7d68;margin:4px 0}
  .ja-desc{font-size:13.5px;line-height:1.55;white-space:pre-line;margin:10px 0;padding:10px 0;border-top:1px solid #efe4d2;border-bottom:1px solid #efe4d2;color:#3a3540;max-height:170px;overflow:auto}
  .ja-act{display:flex;gap:9px;align-items:center;flex-wrap:wrap;margin-top:11px}
  .ja-approve{background:#2e8b57;color:#fff;border:none;border-radius:9px;padding:9px 18px;font-weight:700;cursor:pointer;font-family:Oswald,sans-serif;letter-spacing:.4px}
  .ja-reject{background:#fff;color:#a4283a;border:1px solid #f0c2c8;border-radius:9px;padding:9px 14px;cursor:pointer;font-weight:600}
  .ja-reason{padding:8px 10px;border:1px solid #e2d6c2;border-radius:8px;font-size:13px;flex:1;min-width:160px;font-family:inherit}
  .ja-flag{font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;text-transform:uppercase;letter-spacing:.4px}
  .f-pending{background:#fdf0d5;color:#9a6a10}.f-approved{background:#dff3e6;color:#2e7d4f}.f-rejected{background:#fbe4e4;color:#a4283a}.f-closed{background:#eee;color:#777}
  .ja-apps{font-size:12.5px;color:#33507a}
  .ja-empty{background:var(--paper,#fff);border-radius:13px;padding:34px;text-align:center;color:#8a7d68;box-shadow:var(--shadow,0 6px 20px rgba(0,0,0,.06))}
</style>

@if(session('status'))<div class="status">{{ session('status') }}</div>@endif

<div class="jt-tabs">
  @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'closed' => 'Closed'] as $k => $lbl)
    <a href="{{ route('admin.jobs', ['status' => $k]) }}" class="{{ $status === $k ? 'on' : '' }}">{{ $lbl }} <span class="n">{{ $counts[$k] ?? 0 }}</span></a>
  @endforeach
</div>

@forelse($jobs as $job)
  <div class="ja-card">
    <div class="ja-top">
      <div>
        <h3 class="ja-title">{{ $job->title }}</h3>
        <div class="ja-prop">{{ $job->employerName() }}@if($job->location) · {{ $job->location }}@endif</div>
      </div>
      <span class="ja-flag f-{{ $job->status }}">{{ $job->status }}</span>
    </div>
    <div class="ja-meta">{{ $job->typeLabel() }}@if($job->departmentLabel()) · {{ $job->departmentLabel() }}@endif @if($job->pay) · {{ $job->pay }}@endif @if($job->closes_at) · closes {{ $job->closes_at->format('j M Y') }}@endif · submitted {{ $job->created_at?->format('j M') }}</div>
    <div class="ja-desc">{{ $job->description }}</div>

    @if($job->applications_count)
      <a class="ja-apps" href="{{ route('admin.jobs.applicants', $job) }}">{{ $job->applications_count }} applicant{{ $job->applications_count === 1 ? '' : 's' }} →</a>
    @endif

    <div class="ja-act">
      @if($job->status !== 'approved')
        <form method="POST" action="{{ route('admin.jobs.approve', $job) }}">@csrf<button class="ja-approve" type="submit">✓ Approve &amp; publish</button></form>
      @endif
      @if($job->status === 'pending')
        <form method="POST" action="{{ route('admin.jobs.reject', $job) }}" style="display:flex;gap:8px;flex:1;flex-wrap:wrap">@csrf
          <input class="ja-reason" name="reject_reason" placeholder="Reason (optional, shown to property)">
          <button class="ja-reject" type="submit">Reject</button>
        </form>
      @endif
      @if($job->status === 'rejected' && $job->reject_reason)
        <span class="ja-meta">Reason: {{ $job->reject_reason }}</span>
      @endif
    </div>
  </div>
@empty
  <div class="ja-empty">No {{ $status }} jobs.</div>
@endforelse

@if($jobs->hasPages())
  <div style="display:flex;gap:14px;align-items:center;justify-content:center;margin-top:18px">
    @if(!$jobs->onFirstPage())<a href="{{ $jobs->previousPageUrl() }}" style="text-decoration:none;color:#2f6f76;font-weight:700">← Prev</a>@endif
    <span style="font-size:13px;color:#8a7d68">Page {{ $jobs->currentPage() }} of {{ $jobs->lastPage() }}</span>
    @if($jobs->hasMorePages())<a href="{{ $jobs->nextPageUrl() }}" style="text-decoration:none;color:#2f6f76;font-weight:700">Next →</a>@endif
  </div>
@endif
@endsection
