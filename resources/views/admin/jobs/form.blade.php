@extends('layouts.admin')
@section('title', 'Add job')
@section('content')
<style>
  .jf-back{color:#6c6577;text-decoration:none;font-size:13.5px}
  .jf-h{font-family:Oswald,sans-serif;font-size:24px;margin:8px 0 3px}
  .jf-sub{font-size:13px;color:#8a7d68;margin-bottom:18px}
  .jf-card{background:var(--paper,#fff);border-radius:13px;padding:22px;box-shadow:var(--shadow,0 6px 20px rgba(0,0,0,.06));max-width:760px}
  .jf-row{display:flex;gap:14px;flex-wrap:wrap}
  .jf-fld{display:block;margin-bottom:15px;flex:1;min-width:200px}
  .jf-fld > span{display:block;font-size:12.5px;font-weight:700;margin-bottom:5px;color:#4a4453}
  .jf-fld input,.jf-fld select,.jf-fld textarea{width:100%;padding:11px 13px;border:1.5px solid #e2d6c2;border-radius:9px;font:inherit;font-size:14.5px;background:#fff;box-sizing:border-box}
  .jf-fld textarea{min-height:150px;resize:vertical}
  .jf-hint{font-size:12px;color:#8a7d68;margin-top:4px}
  .jf-err{background:#fbe4e4;color:#a4283a;border-radius:9px;padding:10px 12px;font-size:13px;margin-bottom:16px}
  .jf-save{background:#2e8b57;color:#fff;border:none;border-radius:9px;padding:12px 24px;font-weight:700;cursor:pointer;font-family:Oswald,sans-serif;letter-spacing:.5px;font-size:14px}
  .jf-note{background:#fbf6ec;border:1px solid #efe4d2;border-radius:9px;padding:11px 13px;font-size:12.5px;color:#6a6152;margin-bottom:16px}
</style>

<a class="jf-back" href="{{ route('admin.jobs') }}">← Jobs</a>
<h1 class="jf-h">Add a job</h1>
<div class="jf-sub">Posts straight to the board (published immediately). Assign a member property to have it show under their name, or leave it as a head-office / employer listing.</div>

<div class="jf-card">
  @if($errors->any())<div class="jf-err">{{ $errors->first() }}</div>@endif
  <form method="POST" action="{{ route('admin.jobs.store') }}">
    @csrf

    <label class="jf-fld"><span>Assign to a member property (optional)</span>
      <select name="property_id" id="property_id" onchange="toggleEmployer()">
        <option value="">— Head office / external employer —</option>
        @foreach($properties as $p)
          <option value="{{ $p->id }}" @selected(old('property_id') == $p->id)>{{ $p->motel ?: $p->name }}@if($p->loc) · {{ $p->loc }}@endif</option>
        @endforeach
      </select>
      <div class="jf-hint">When assigned, the listing shows the property's name and syncs its location.</div>
    </label>

    <label class="jf-fld" id="employer_fld"><span>Employer name (if not a member property)</span>
      <input type="text" name="employer" value="{{ old('employer') }}" placeholder="e.g. Sunshine Motor Inn">
    </label>

    <label class="jf-fld"><span>Job title *</span>
      <input type="text" name="title" value="{{ old('title') }}" required>
    </label>

    <div class="jf-row">
      <label class="jf-fld"><span>Employment type *</span>
        <select name="employment_type" required>
          @foreach(config('rmc.employment_types') as $k => $lbl)
            <option value="{{ $k }}" @selected(old('employment_type', 'full-time') === $k)>{{ $lbl }}</option>
          @endforeach
        </select>
      </label>
      <label class="jf-fld"><span>Department</span>
        <select name="department">
          <option value="">—</option>
          @foreach(config('rmc.job_departments') as $k => $lbl)
            <option value="{{ $k }}" @selected(old('department') === $k)>{{ $lbl }}</option>
          @endforeach
        </select>
      </label>
    </div>

    <div class="jf-row">
      <label class="jf-fld"><span>Location</span>
        <input type="text" name="location" value="{{ old('location') }}" placeholder="e.g. Byron Bay, NSW">
      </label>
      <label class="jf-fld"><span>State</span>
        <select name="state">
          <option value="">—</option>
          @foreach(config('rmc.job_states') as $code => $lbl)
            <option value="{{ $code }}" @selected(old('state') === $code)>{{ $code }} — {{ $lbl }}</option>
          @endforeach
        </select>
      </label>
    </div>

    <div class="jf-row">
      <label class="jf-fld"><span>Pay (as shown)</span>
        <input type="text" name="pay" value="{{ old('pay') }}" placeholder="e.g. $28–$34 per hour">
      </label>
      <label class="jf-fld"><span>Annual salary for filters (optional)</span>
        <input type="number" name="salary_annual" value="{{ old('salary_annual') }}" placeholder="e.g. 65000" min="0">
        <div class="jf-hint">Used by the board's pay filter. Leave blank if unknown.</div>
      </label>
    </div>

    <label class="jf-fld"><span>Description *</span>
      <textarea name="description" required>{{ old('description') }}</textarea>
    </label>

    <label class="jf-fld"><span>Closing date (optional)</span>
      <input type="date" name="closes_at" value="{{ old('closes_at') }}">
    </label>

    <button class="jf-save" type="submit">Publish job</button>
  </form>
</div>

<script>
  function toggleEmployer() {
    var assigned = document.getElementById('property_id').value !== '';
    document.getElementById('employer_fld').style.display = assigned ? 'none' : 'block';
  }
  toggleEmployer();
</script>
@endsection
