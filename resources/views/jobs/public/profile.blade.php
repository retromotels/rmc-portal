@extends('jobs.public.layout')
@section('title', 'My profile')
@section('head')
<style>
  .pf{max-width:760px;margin:36px auto;padding:0 22px}
  .pf h1{font-family:var(--serif);font-size:34px;font-weight:700;margin-bottom:2px}
  .pf .lede{font-size:14px;color:var(--ink-soft);margin-bottom:22px}
  .pf-card{background:#fff;border:1px solid var(--bone);border-radius:16px;padding:22px;box-shadow:0 8px 24px rgba(31,41,51,.06);margin-bottom:18px}
  .pf-card h2{font-family:var(--serif);font-size:22px;font-weight:700;margin-bottom:14px}
  .pf-head{display:flex;gap:18px;align-items:center;flex-wrap:wrap}
  .pf-av{width:84px;height:84px;border-radius:50%;object-fit:cover;border:3px solid var(--bone);background:var(--peach);display:grid;place-items:center;font-size:32px;font-weight:800;color:var(--ink)}
  .fld{display:block;margin-bottom:14px}
  .fld span{display:block;font-size:12.5px;font-weight:700;margin-bottom:5px;color:var(--ink-soft)}
  .fld input,.fld select,.fld textarea{width:100%;padding:11px 13px;border:1.5px solid var(--bone);border-radius:9px;font:inherit;font-size:14.5px;background:var(--paper);box-sizing:border-box}
  .fld textarea{min-height:96px;resize:vertical}
  .row2{display:flex;gap:14px;flex-wrap:wrap}
  .row2 .fld{flex:1;min-width:180px}
  .rz{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;border:1px solid var(--bone);border-radius:11px;padding:12px 14px;margin-bottom:10px;background:var(--paper)}
  .rz-name{font-weight:700;font-size:14px}
  .rz-meta{font-size:12px;color:var(--ink-soft)}
  .rz-actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
  .pill{font-size:11px;font-weight:800;padding:3px 10px;border-radius:20px;background:var(--mint);color:#15603f}
  .lnk{font-size:12.5px;font-weight:700;text-decoration:none;color:var(--rust)}
  .lnk.mut{color:var(--ink-soft)}
  .up{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-top:6px}
  .up input[type=file]{font-size:13px}
  .flash{background:#dff3e6;border:1px solid #a9dcbf;color:#2e7d4f;border-radius:10px;padding:12px 15px;font-size:14px;margin-bottom:16px}
  .err{background:#fbe4e4;color:#a4283a;border-radius:9px;padding:10px 12px;font-size:13px;margin-bottom:14px}
</style>
@endsection
@section('content')
<div class="pf">
  <h1>My profile</h1>
  <p class="lede">Keep your details and resumes up to date — they speed up every application.</p>

  @if(session('flash'))<div class="flash">{{ session('flash') }}</div>@endif
  @if($errors->any())<div class="err">{{ $errors->first() }}</div>@endif

  <div class="pf-card">
    <div class="pf-head">
      @if($seeker->avatar_path)
        <img class="pf-av" src="{{ route('seeker.avatar', $seeker) }}" alt="Profile photo">
      @else
        <div class="pf-av">{{ strtoupper(substr($seeker->name, 0, 1)) }}</div>
      @endif
      <form class="up" method="POST" action="{{ route('seeker.avatar.upload') }}" enctype="multipart/form-data">
        @csrf
        <div>
          <div style="font-weight:700;font-size:14px;margin-bottom:4px">Profile photo</div>
          <input type="file" name="avatar" accept="image/*" required>
        </div>
        <button class="btn btn-ghost" type="submit">Upload</button>
      </form>
    </div>
  </div>

  <div class="pf-card">
    <h2>Your details</h2>
    <form method="POST" action="{{ route('seeker.profile.update') }}">
      @csrf
      <div class="row2">
        <label class="fld"><span>Full name</span><input type="text" name="name" value="{{ old('name', $seeker->name) }}" required></label>
        <label class="fld"><span>Phone</span><input type="text" name="phone" value="{{ old('phone', $seeker->phone) }}"></label>
      </div>
      <div class="row2">
        <label class="fld"><span>State</span>
          <select name="state">
            <option value="">Prefer not to say</option>
            @foreach(config('rmc.job_states') as $code => $lbl)
              <option value="{{ $code }}" @selected(old('state', $seeker->state) === $code)>{{ $lbl }}</option>
            @endforeach
          </select>
        </label>
        <label class="fld"><span>Town / suburb</span><input type="text" name="town" value="{{ old('town', $seeker->town) }}" placeholder="e.g. Byron Bay"></label>
      </div>
      <label class="fld"><span>Headline</span><input type="text" name="headline" value="{{ old('headline', $seeker->headline) }}" placeholder="e.g. Experienced front-office all-rounder" maxlength="140"></label>
      <label class="fld"><span>About you</span><textarea name="bio" placeholder="A short intro employers will see on your applications.">{{ old('bio', $seeker->bio) }}</textarea></label>
      <button class="btn btn-rust" type="submit">Save details</button>
    </form>
  </div>

  <div class="pf-card">
    <h2>Resumes</h2>
    @forelse($seeker->resumes as $rz)
      <div class="rz">
        <div>
          <div class="rz-name">{{ $rz->original_name }} @if($rz->is_default)<span class="pill">Default</span>@endif</div>
          <div class="rz-meta">Added {{ $rz->created_at?->format('j M Y') }}@if($rz->size) · {{ number_format($rz->size / 1024) }} KB @endif</div>
        </div>
        <div class="rz-actions">
          @unless($rz->is_default)
            <form method="POST" action="{{ route('seeker.resume.default', $rz) }}">@csrf<button class="lnk" style="background:none;border:none;cursor:pointer" type="submit">Make default</button></form>
          @endunless
          <form method="POST" action="{{ route('seeker.resume.delete', $rz) }}" onsubmit="return confirm('Remove this resume?')">@csrf @method('DELETE')<button class="lnk mut" style="background:none;border:none;cursor:pointer" type="submit">Remove</button></form>
        </div>
      </div>
    @empty
      <p class="rz-meta" style="margin-bottom:12px">No resumes yet. Upload one so you can apply in a couple of clicks.</p>
    @endforelse

    <form class="up" method="POST" action="{{ route('seeker.resume.add') }}" enctype="multipart/form-data">
      @csrf
      <input type="file" name="resume" accept=".pdf,.doc,.docx" required>
      <button class="btn btn-ghost" type="submit">Add resume</button>
    </form>
    <div class="rz-meta" style="margin-top:8px">PDF or Word, up to 6 MB. Your default resume is offered automatically when you apply.</div>
  </div>
</div>
@endsection
