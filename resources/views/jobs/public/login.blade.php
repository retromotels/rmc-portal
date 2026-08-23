@extends('jobs.public.layout')
@section('title', 'Log in')
@section('head')
<style>
  .auth{max-width:420px;margin:52px auto;padding:0 22px}
  .auth h1{font-family:var(--serif);font-size:34px;font-weight:700;margin-bottom:16px}
  .card{background:#fff;border:1px solid var(--bone);border-radius:16px;padding:24px;box-shadow:0 8px 24px rgba(31,41,51,.06)}
  .fld{display:block;margin-bottom:14px}
  .fld span{display:block;font-size:12.5px;font-weight:700;margin-bottom:5px;color:var(--ink-soft)}
  .fld input{width:100%;padding:12px 14px;border:1.5px solid var(--bone);border-radius:9px;font:inherit;font-size:15px;background:var(--paper)}
  .rem{display:flex;align-items:center;gap:7px;font-size:13.5px;color:var(--ink-soft);margin-bottom:14px}
  .btn-full{width:100%;padding:13px;font-size:15px}
  .swap{font-size:13.5px;color:var(--ink-soft);text-align:center;margin-top:14px}
  .swap a{color:var(--rust);font-weight:700}
  .err{background:#fbe4e4;color:#a4283a;border-radius:9px;padding:10px 12px;font-size:13px;margin-bottom:14px}
</style>
@endsection
@section('content')
<div class="auth">
  <h1>Welcome back</h1>
  <div class="card">
    @if($errors->any())<div class="err">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('seeker.login') }}">
      @csrf
      <label class="fld"><span>Email</span><input type="email" name="email" value="{{ old('email') }}" required></label>
      <label class="fld"><span>Password</span><input type="password" name="password" required></label>
      <label class="rem"><input type="checkbox" name="remember" value="1"> Remember me</label>
      <button class="btn btn-rust btn-full" type="submit">Log in</button>
    </form>
    <p class="swap">New here? <a href="{{ route('seeker.register') }}">Create an account</a></p>
  </div>
</div>
@endsection
