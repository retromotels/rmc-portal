@extends('layouts.guest')
@section('content')
<div class="auth-screen">
  <div class="sun"></div>
  <div class="auth-card">
    <div class="auth-sign">
      @include('partials.logo', ['lg' => true])
      <div class="neon aqua" style="font-size:20px;margin-top:10px">COLLECTIVE</div>
    </div>
    <p class="mini" style="margin-bottom:14px">Choose a new password for your account.</p>
    <form method="POST" action="{{ route('password.update') }}">
      @csrf
      <input type="hidden" name="token" value="{{ $token }}">
      <label class="fld"><span>Email</span><input name="email" type="email" value="{{ $email }}" autocomplete="email" required></label>
      <label class="fld"><span>New password</span><input name="password" type="password" autocomplete="new-password" required></label>
      <label class="fld"><span>Confirm new password</span><input name="password_confirmation" type="password" autocomplete="new-password" required></label>
      @foreach($errors->all() as $e)<div class="err">{{ $e }}</div>@endforeach
      <button class="btn btn-primary btn-block" type="submit">Reset password</button>
    </form>
    <div class="mini" style="margin-top:16px"><a href="{{ route('login') }}">← Back to log in</a></div>
  </div>
</div>
@endsection
