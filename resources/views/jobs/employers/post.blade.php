@extends('jobs.public.layout')
@section('title', 'Post a job')
@section('head')
<style>
  .pj{max-width:660px;margin:36px auto;padding:0 22px}
  .pj .back{font-size:13.5px;color:var(--ink-soft);text-decoration:none}
  .pj h1{font-family:var(--serif);font-size:32px;font-weight:700;margin:10px 0 2px}
  .pj .lede{font-size:14px;color:var(--ink-soft);margin-bottom:18px}
  .card{background:#fff;border:1px solid var(--bone);border-radius:16px;padding:24px;box-shadow:0 8px 24px rgba(31,41,51,.06)}
  .fld{display:block;margin-bottom:14px}
  .fld span{display:block;font-size:12.5px;font-weight:700;margin-bottom:5px;color:var(--ink-soft)}
  .fld input,.fld select,.fld textarea{width:100%;padding:12px 14px;border:1.5px solid var(--bone);border-radius:9px;font:inherit;font-size:15px;background:var(--paper);box-sizing:border-box}
  .fld textarea{min-height:150px;resize:vertical}
  .row{display:flex;gap:12px;flex-wrap:wrap}.row .fld{flex:1;min-width:150px}
  .err{background:#fbe4e4;color:#a4283a;border-radius:9px;padding:10px 12px;font-size:13px;margin-bottom:14px}
  .btn-full{width:100%;padding:14px;font-size:15px}
</style>
@endsection
@section('content')
<div class="pj">
  <a class="back" href="{{ route('employer.dashboard') }}">← Dashboard</a>
  <h1>Post a job</h1>
  <p class="lede">This uses one credit and goes to head office for a quick review before it appears on the board.</p>
  <div class="card">
    @if($errors->any())<div class="err">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('employer.job.store') }}">
      @csrf
      <label class="fld"><span>Job title *</span><input type="text" name="title" value="{{ old('title') }}" required></label>
      <div class="row">
        <label class="fld"><span>Employment type *</span>
          <select name="employment_type" required>
            @foreach(config('rmc.employment_types') as $k => $lbl)<option value="{{ $k }}" @selected(old('employment_type','full-time')===$k)>{{ $lbl }}</option>@endforeach
          </select>
        </label>
        <label class="fld"><span>Department</span>
          <select name="department"><option value="">—</option>
            @foreach(config('rmc.job_departments') as $k => $lbl)<option value="{{ $k }}" @selected(old('department')===$k)>{{ $lbl }}</option>@endforeach
          </select>
        </label>
      </div>
      <div class="row">
        <label class="fld"><span>Location</span><input type="text" name="location" value="{{ old('location') }}" placeholder="e.g. Byron Bay, NSW"></label>
        <label class="fld"><span>State</span>
          <select name="state"><option value="">—</option>
            @foreach(config('rmc.job_states') as $code => $lbl)<option value="{{ $code }}" @selected(old('state')===$code)>{{ $code }}</option>@endforeach
          </select>
        </label>
      </div>
      <div class="row">
        <label class="fld"><span>Pay (as shown)</span><input type="text" name="pay" value="{{ old('pay') }}" placeholder="e.g. $30–$36 per hour"></label>
        <label class="fld"><span>Annual salary for filters (optional)</span><input type="number" name="salary_annual" value="{{ old('salary_annual') }}" min="0"></label>
      </div>
      <label class="fld"><span>Description *</span><textarea name="description" required>{{ old('description') }}</textarea></label>
      <label class="fld"><span>Closing date (optional)</span><input type="date" name="closes_at" value="{{ old('closes_at') }}"></label>
      <button class="btn btn-rust btn-full" type="submit">Submit for approval (uses 1 credit)</button>
    </form>
  </div>
</div>
@endsection
