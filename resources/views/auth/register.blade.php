@extends('layouts.guest')
@section('content')
@php $policies = config('rmc.policies'); @endphp
<style>
  .pw-wrap{position:relative}
  .pw-wrap input{padding-right:46px}
  .pw-toggle{position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:6px;color:#8a7d68;display:flex;align-items:center}
  .pw-toggle svg{width:20px;height:20px}
  .policy-link{color:var(--teal-d);font-weight:600;cursor:pointer;text-decoration:underline}
  .match-err{color:var(--coral-d);font-size:13px;margin-top:8px;font-weight:500;display:none}
  #policyModal{position:fixed;inset:0;z-index:300;background:rgba(26,21,38,.6);display:none;align-items:center;justify-content:center;padding:24px}
  #policyModal.show{display:flex}
  #policyModal .pm-card{background:var(--paper);max-width:560px;width:100%;max-height:82vh;overflow:auto;border-radius:16px;padding:26px 28px;box-shadow:var(--shadow)}
  #policyModal h3{font-size:20px;margin-bottom:12px}
  #policyModal p{font-size:13.5px;line-height:1.6;margin:0 0 12px;color:#4a4453}
</style>
<div class="auth-screen">
  <div class="sun"></div>
  <div class="auth-card">
    <div class="auth-sign">
      @include('partials.logo', ['lg' => true])
      <div class="neon aqua" style="font-size:20px;margin-top:10px">COLLECTIVE</div>
      <span class="vac">● JOIN</span>
    </div>
    <form method="POST" action="{{ route('register') }}" id="regForm">
      @csrf
      <label class="fld"><span>Your name</span><input name="name" value="{{ old('name') }}" autocomplete="name" required></label>
      <label class="fld"><span>Motel name</span><input name="motel" value="{{ old('motel') }}" autocomplete="organization" required></label>
      <label class="fld"><span>Email</span><input name="email" type="email" value="{{ old('email') }}" autocomplete="email" required></label>
      <label class="fld"><span>Password</span>
        <div class="pw-wrap">
          <input name="password" type="password" id="pw" autocomplete="new-password" required>
          <button type="button" class="pw-toggle" data-target="pw" aria-label="Show password"></button>
        </div>
      </label>
      <label class="fld"><span>Confirm password</span>
        <div class="pw-wrap">
          <input name="password_confirmation" type="password" id="pw2" autocomplete="new-password" required>
          <button type="button" class="pw-toggle" data-target="pw2" aria-label="Show password"></button>
        </div>
      </label>
      <div class="match-err" id="matchErr">Passwords don't match.</div>

      <div class="policy-box">
        <label class="switchrow"><input type="checkbox" name="accept_privacy" value="1"> I have read and accept the <span class="policy-link" data-policy="privacy">Privacy &amp; Data Protection Policy</span></label>
        <label class="switchrow"><input type="checkbox" name="accept_terms" value="1"> I agree to the <span class="policy-link" data-policy="terms">Terms of Membership</span></label>
        <label class="switchrow"><input type="checkbox" name="accept_authority" value="1"> I authorise RMC to act on my behalf (<span class="policy-link" data-policy="authority">Member Authority</span>)</label>
      </div>
      <div class="dp-note">🔒 All data you provide is stored securely and is never shared in a way that identifies your property. Collective use is aggregated and anonymised so individual properties cannot be identified.</div>

      @foreach($errors->all() as $e)<div class="err">{{ $e }}</div>@endforeach
      <button class="btn btn-primary btn-block" type="submit">Create account</button>
    </form>
    <div class="mini" style="margin-top:16px">Already a member? <a href="{{ route('login') }}">Log in →</a></div>
  </div>
</div>

<div id="policyModal"><div class="pm-card"><div id="pmBody"></div><button class="btn btn-primary btn-block" id="pmClose" style="margin-top:16px">Close</button></div></div>

<script>
  var POLICIES = @json($policies);
  var EYE = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
  var EYEOFF = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';
  function esc(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}

  var modal = document.getElementById('policyModal'), pmBody = document.getElementById('pmBody');
  document.querySelectorAll('.policy-link').forEach(function(el){
    el.addEventListener('click', function(){
      var p = POLICIES[el.getAttribute('data-policy')]; if(!p) return;
      pmBody.innerHTML = '<h3>' + esc(p.title) + '</h3>' + p.body.map(function(x){ return '<p>' + esc(x) + '</p>'; }).join('');
      modal.classList.add('show');
    });
  });
  document.getElementById('pmClose').addEventListener('click', function(){ modal.classList.remove('show'); });
  modal.addEventListener('click', function(e){ if(e.target === modal) modal.classList.remove('show'); });

  document.querySelectorAll('.pw-toggle').forEach(function(btn){
    btn.innerHTML = EYE;
    btn.addEventListener('click', function(){
      var inp = document.getElementById(btn.getAttribute('data-target'));
      var reveal = inp.type === 'password';
      inp.type = reveal ? 'text' : 'password';
      btn.innerHTML = reveal ? EYEOFF : EYE;
    });
  });

  var form = document.getElementById('regForm'), pw = document.getElementById('pw'), pw2 = document.getElementById('pw2'), err = document.getElementById('matchErr');
  function checkMatch(){
    if (pw2.value && pw.value !== pw2.value){ err.style.display = 'block'; return false; }
    err.style.display = 'none'; return true;
  }
  pw2.addEventListener('input', checkMatch);
  pw.addEventListener('input', function(){ if (pw2.value) checkMatch(); });
  form.addEventListener('submit', function(e){ if(!checkMatch()){ e.preventDefault(); pw2.focus(); } });
</script>
@endsection
