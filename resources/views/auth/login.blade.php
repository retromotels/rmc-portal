@extends('layouts.guest')
@section('content')
<div class="auth-screen">
  <div class="sun"></div>
  <div class="auth-card">
    <div class="auth-sign">
      <div class="neon">RETRO MOTEL</div>
      <div class="neon aqua" style="font-size:22px">COLLECTIVE</div>
      <span class="vac">● VACANCY</span>
    </div>
    <form method="POST" action="{{ route('login') }}">
      @csrf
      <label class="fld"><span>Email</span><input name="email" type="email" value="{{ old('email') }}" autocomplete="email" required></label>
      <label class="fld"><span>Password</span><input name="password" type="password" autocomplete="current-password" required></label>
      <label class="switchrow" style="margin-top:12px"><input type="checkbox" name="remember"> Remember me</label>
      @error('email')<div class="err">{{ $message }}</div>@enderror
      <button class="btn btn-primary btn-block" type="submit">Log in</button>
    </form>
    <div class="mini" style="margin-top:16px">New operator? <a href="{{ route('register') }}">Join the collective →</a></div>
    <div class="mini"><b>RMC staff:</b> use your head-office login.</div>
  </div>
</div>
@endsection
