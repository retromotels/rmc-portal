@extends('layouts.guest')
@section('content')
<div class="auth-screen">
  <div class="sun"></div>
  <div class="auth-card">
    <div class="auth-sign">
      @include('partials.logo', ['lg' => true])
    </div>
    @if(session('status'))<div class="status">{{ session('status') }}</div>@endif
    <p class="mini" style="margin-bottom:14px">Enter your account email and we’ll send you a link to reset your password.</p>
    <form method="POST" action="{{ route('password.email') }}">
      @csrf
      <label class="fld"><span>Email</span><input name="email" type="email" value="{{ old('email') }}" autocomplete="email" required></label>
      @error('email')<div class="err">{{ $message }}</div>@enderror
      <button class="btn btn-primary btn-block" type="submit">Send reset link</button>
    </form>
    <div class="mini" style="margin-top:16px"><a href="{{ route('login') }}">← Back to log in</a></div>
  </div>
</div>
@endsection
