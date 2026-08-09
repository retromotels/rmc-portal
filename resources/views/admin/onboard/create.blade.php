@extends('layouts.admin')
@section('title', 'Create a property')
@section('content')
<style>
  .panel{background:var(--paper,#fff);border-radius:14px;padding:18px 20px;box-shadow:var(--shadow,0 6px 20px rgba(0,0,0,.06));max-width:680px;margin-bottom:16px}
  .fld{display:block;margin-bottom:12px}
  .fld span{display:block;font-weight:600;font-size:12.5px;margin-bottom:5px;color:#4a4453}
  .fld input{width:100%;padding:10px 11px;border:1px solid #e2d6c2;border-radius:9px;font-size:14px}
  .row2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
  .save{background:var(--coral,#ee6a5a);color:#fff;border:none;border-radius:10px;padding:12px 24px;font-weight:700;font-family:Oswald,sans-serif;letter-spacing:.5px;cursor:pointer}
  .link-box{background:#f6efe4;border:1px solid #e7dcc8;border-radius:10px;padding:12px 14px;font-size:13px;word-break:break-all;margin-top:8px}
  .link-box code{color:#b23b2e}
  .pend{display:flex;justify-content:space-between;gap:12px;align-items:center;padding:9px 0;border-bottom:1px solid #efe7d8;font-size:13.5px}
  .cpy{cursor:pointer;border:1px solid #e2d6c2;background:#fff;border-radius:8px;padding:6px 10px;font-size:12px;font-weight:600}
</style>

@if(session('status'))
  <div class="status">{{ session('status') }}</div>
  @if(session('invite_link'))
    <div class="panel"><b>Activation link</b>
      <div class="link-box" id="lastLink">{{ session('invite_link') }}</div>
      <button class="cpy" style="margin-top:10px" onclick="navigator.clipboard?.writeText(document.getElementById('lastLink').innerText)">Copy link</button>
    </div>
  @endif
@endif
@foreach($errors->all() as $e)<div class="status" style="background:#fdeceb;color:#b23b2e">{{ $e }}</div>@endforeach

<div class="panel">
  <h3 style="font-family:Oswald;margin:0 0 4px">New property</h3>
  <p style="font-size:12.5px;color:#8a7d68;margin:0 0 14px">Create the property and pre-fill what you know. You’ll get a one-time activation link to send them — they just accept the policies and set a password to get in.</p>
  <form method="POST" action="{{ route('admin.onboard.store') }}">
    @csrf
    <label class="fld"><span>Property / motel name</span><input name="motel" value="{{ old('motel') }}" required></label>
    <div class="row2">
      <label class="fld"><span>Contact name</span><input name="name" value="{{ old('name') }}" required></label>
      <label class="fld"><span>Contact email</span><input name="email" type="email" value="{{ old('email') }}" required></label>
    </div>
    <label class="fld"><span>Total rooms (sets their tier)</span><input name="rooms" type="number" min="0" value="{{ old('rooms') }}" placeholder="e.g. 24"></label>
    <button class="save" type="submit">Create &amp; get link →</button>
  </form>
</div>

<div class="panel">
  <h3 style="font-family:Oswald;margin:0 0 10px">Pending activations</h3>
  @forelse($pending as $p)
    <div class="pend">
      <div><b>{{ $p->motel }}</b> · <span style="color:#8a7d68">{{ $p->email }}</span></div>
      <button class="cpy" onclick="navigator.clipboard?.writeText('{{ route('claim.show', $p->claim_token) }}')">Copy link</button>
    </div>
  @empty
    <div style="font-size:13px;color:#8a7d68">No pending activations.</div>
  @endforelse
</div>
@endsection
