@extends('jobs.public.layout')
@section('title', 'Apply · ' . $job->title)
@section('head')
<style>
  .apply{max-width:560px;margin:40px auto;padding:0 22px}
  .back{font-size:13.5px;color:var(--ink-soft);text-decoration:none}
  .apply h1{font-family:var(--serif);font-size:32px;font-weight:700;margin:12px 0 2px}
  .apply .for{font-size:14px;color:var(--rust-ink);font-weight:700;margin-bottom:18px}
  .card{background:#fff;border:1px solid var(--bone);border-radius:16px;padding:24px;box-shadow:0 8px 24px rgba(31,41,51,.06)}
  .fld{display:block;margin-bottom:14px}
  .fld span{display:block;font-size:12.5px;font-weight:700;margin-bottom:5px;color:var(--ink-soft)}
  .fld input,.fld textarea{width:100%;padding:12px 14px;border:1.5px solid var(--bone);border-radius:9px;font:inherit;font-size:15px;background:var(--paper)}
  .fld textarea{resize:vertical}
  .hint{font-size:12px;color:var(--ink-soft);margin:-9px 0 14px}
  .btn-full{width:100%;padding:13px;font-size:15px}
  .err{background:#fbe4e4;color:#a4283a;border-radius:9px;padding:10px 12px;font-size:13px;margin-bottom:14px}
  .done{background:#dff3e6;border:1px solid #a9dcbf;color:#2e7d4f;border-radius:12px;padding:20px;text-align:center}
</style>
@endsection
@section('content')
<div class="apply">
  <a class="back" href="{{ route('jobs.public.show', $job->slug) }}">← Back to role</a>
  <h1>Apply</h1>
  <div class="for">{{ $job->title }} · {{ $job->property->motel ?: 'Retro Motel' }}</div>

  @if($applied)
    <div class="done">✓ You've already applied for this role. The property has your details — you can see it under <a href="{{ route('seeker.dashboard') }}" style="color:#2e7d4f;font-weight:700">My applications</a>.</div>
  @else
    <div class="card">
      @if($errors->any())<div class="err">{{ $errors->first() }}</div>@endif
      <form method="POST" action="{{ route('jobs.apply', $job->slug) }}" enctype="multipart/form-data">
        @csrf
        <label class="fld"><span>Your name</span><input type="text" name="name" value="{{ old('name', $seeker->name) }}" required></label>
        <label class="fld"><span>Email</span><input type="email" name="email" value="{{ old('email', $seeker->email) }}" required></label>
        <label class="fld"><span>Phone (optional)</span><input type="text" name="phone" value="{{ old('phone', $seeker->phone) }}"></label>
        <label class="fld"><span>Message to the property</span><textarea name="message" rows="5" placeholder="A few lines about you and why you're a great fit…">{{ old('message') }}</textarea></label>
        <label class="fld"><span>CV / résumé (optional)</span><input type="file" name="cv" accept=".pdf,.doc,.docx"></label>
        <p class="hint">PDF or Word, up to 6&nbsp;MB.</p>
        <button class="btn btn-rust btn-full" type="submit">Send application</button>
      </form>
    </div>
  @endif
</div>
@endsection
