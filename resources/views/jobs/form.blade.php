@extends('layouts.portal')
@section('title', $job->exists ? 'Edit job' : 'Post a job')
@section('content')
<style>
  .jf{max-width:680px}
  .panel{background:var(--paper,#fff);border-radius:14px;padding:18px 20px;box-shadow:var(--shadow,0 6px 20px rgba(0,0,0,.06));margin-bottom:16px}
  .fld{display:block;margin-bottom:14px}
  .fld span{display:block;font-weight:600;font-size:12.5px;margin-bottom:5px;color:#4a4453}
  .fld input,.fld select,.fld textarea{width:100%;padding:10px 12px;border:1px solid #e2d6c2;border-radius:9px;font-size:14px;font-family:inherit;background:#fff}
  .fld textarea{resize:vertical}
  .row2{display:flex;gap:14px;flex-wrap:wrap}
  .row2 .fld{flex:1;min-width:180px}
  .hint{font-size:12px;color:#8a7d68;margin:-8px 0 14px}
  .save{background:var(--coral,#ee6a5a);color:#fff;border:none;border-radius:10px;padding:12px 26px;font-weight:700;font-family:Oswald,sans-serif;letter-spacing:.5px;cursor:pointer}
  .cancel{color:#6c6577;text-decoration:none;margin-left:14px;font-size:13.5px}
  .err{background:#fbe4e4;color:#a4283a;border-radius:9px;padding:10px 12px;font-size:13px;margin-bottom:14px}
</style>

@if($errors->any())<div class="err">Please check the highlighted fields.</div>@endif

<form method="POST" action="{{ $job->exists ? route('jobs.update', $job) : route('jobs.store') }}" class="jf">
  @csrf
  @if($job->exists)@method('PUT')@endif

  <div class="panel">
    <label class="fld"><span>Job title</span><input type="text" name="title" value="{{ old('title', $job->title) }}" placeholder="e.g. Front Office All-Rounder" required></label>
    <div class="row2">
      <label class="fld"><span>Employment type</span>
        <select name="employment_type">
          @foreach(config('rmc.employment_types') as $k => $lbl)
            <option value="{{ $k }}" @selected(old('employment_type', $job->employment_type) === $k)>{{ $lbl }}</option>
          @endforeach
        </select>
      </label>
      <label class="fld"><span>Department (optional)</span>
        <select name="department">
          <option value="">—</option>
          @foreach(config('rmc.job_departments') as $k => $lbl)
            <option value="{{ $k }}" @selected(old('department', $job->department) === $k)>{{ $lbl }}</option>
          @endforeach
        </select>
      </label>
    </div>
    <div class="row2">
      <label class="fld"><span>Pay (optional)</span><input type="text" name="pay" value="{{ old('pay', $job->pay) }}" placeholder="e.g. $28–32/hr, or Competitive"></label>
      <label class="fld"><span>Closing date (optional)</span><input type="date" name="closes_at" value="{{ old('closes_at', optional($job->closes_at)->format('Y-m-d')) }}"></label>
    </div>
    <label class="fld"><span>Description</span><textarea name="description" rows="9" placeholder="About the role, responsibilities, who you're looking for, and how to stand out…" required>{{ old('description', $job->description) }}</textarea></label>
    <p class="hint">Your property name and location are added automatically. Submitting sends it to head office for approval before it appears on the public board.</p>
  </div>

  <div class="panel">
    <button class="save" type="submit">{{ $job->exists ? 'Save & resubmit' : 'Submit for approval' }}</button>
    <a class="cancel" href="{{ route('jobs.index') }}">Cancel</a>
  </div>
</form>
@endsection
