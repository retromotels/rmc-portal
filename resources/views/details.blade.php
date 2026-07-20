@extends('layouts.guest')
@section('content')
<div class="auth-screen" style="align-items:flex-start;padding-top:48px">
  <div class="sun"></div>
  <div class="auth-card" style="max-width:640px;position:relative;z-index:2">
    <div style="text-align:center;margin-bottom:8px"><div class="neon" style="font-size:22px">COMPLETE YOUR DETAILS</div></div>
    <p class="sub" style="text-align:center">Tell us about your property so RMC can set up your membership. We reuse this for OTA packs and supplier tenders.</p>

    <form method="POST" action="{{ route('details.save') }}">
      @csrf
      <div class="section-title"><h3>{{ $A['icon'] }} Property profile</h3><div class="rule"></div></div>
      @foreach($A['fields'] as $f)
        @include('partials.field', ['f' => $f, 'data' => $user->sectionData('A')])
      @endforeach

      <div class="section-title"><h3>{{ $B['icon'] }} Contacts &amp; ownership</h3><div class="rule"></div></div>
      @foreach($B['fields'] as $f)
        @include('partials.field', ['f' => $f, 'data' => $user->sectionData('B')])
      @endforeach

      <div class="dp-note" style="margin-top:16px">🔒 No credit card details needed at this stage. Your tier is selected automatically from your room count and can be changed later.</div>
      @foreach($errors->all() as $e)<div class="err">{{ $e }}</div>@endforeach
      <button class="btn btn-primary btn-block" type="submit">Save &amp; enter the portal ✓</button>
    </form>
    <div class="mini" style="margin-top:14px"><a href="{{ route('dashboard') }}">I'll finish this later →</a></div>
  </div>
</div>
@endsection
