@extends('layouts.guest')
@section('content')
<div class="auth-screen">
  <div class="sun"></div>
  <div class="auth-card">
    <div class="auth-sign">
      @include('partials.logo', ['lg' => true])
      <div class="neon aqua" style="font-size:20px;margin-top:10px">COLLECTIVE</div>
      <span class="vac">● ACTIVATE</span>
    </div>
    <p class="mini" style="margin-bottom:14px">Activate the account for <b>{{ $property->motel ?: $property->name }}</b> — accept the policies and set a password.</p>
    <form method="POST" action="{{ route('claim.store', $token) }}">
      @csrf
      <label class="fld"><span>Create a password</span><input name="password" type="password" autocomplete="new-password" required></label>
      <label class="fld"><span>Confirm password</span><input name="password_confirmation" type="password" autocomplete="new-password" required></label>
      <div class="policy-box">
        <label class="switchrow"><input type="checkbox" name="accept_privacy" value="1"> I have read and accept the Privacy &amp; Data Protection Policy</label>
        <label class="switchrow"><input type="checkbox" name="accept_terms" value="1"> I agree to the Terms of Membership</label>
        <label class="switchrow"><input type="checkbox" name="accept_authority" value="1"> I authorise RMC to act on my behalf (Member Authority)</label>
      </div>
      @foreach($errors->all() as $e)<div class="err">{{ $e }}</div>@endforeach
      <button class="btn btn-primary btn-block" type="submit">Activate &amp; log in →</button>
    </form>
  </div>
</div>
@endsection
