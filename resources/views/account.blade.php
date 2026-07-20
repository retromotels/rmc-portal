@extends('layouts.portal')
@section('title', 'Account')
@section('content')
<div class="grid g2">
  <div class="card">
    <h3 style="margin-bottom:14px">Property profile</h3>
    <form method="POST" action="{{ route('account.update') }}" enctype="multipart/form-data">
      @csrf
      <div style="display:flex;gap:16px;align-items:center;margin-bottom:16px">
        <div class="avatar" style="width:72px;height:72px;font-size:24px;@if($user->photo_path)background-image:url('{{ Storage::url($user->photo_path) }}')@endif">
          @unless($user->photo_path){{ strtoupper(mb_substr($user->motel ?: $user->name, 0, 2)) }}@endunless
        </div>
        <div><input type="file" name="photo" accept="image/*"><div class="sub" style="margin-top:6px">Shown to other operators in the community.</div></div>
      </div>
      <label class="fld"><span>Operator name</span><input name="name" value="{{ old('name', $user->name) }}"></label>
      <label class="fld"><span>Motel name</span><input name="motel" value="{{ old('motel', $user->motel) }}"></label>
      <label class="fld"><span>Short bio</span><textarea name="bio" rows="3">{{ old('bio', $user->bio) }}</textarea></label>
      <label class="fld"><span>Location</span><input name="loc" value="{{ old('loc', $user->loc) }}"></label>
      <label class="fld"><span>Phone</span><input name="phone" value="{{ old('phone', $user->phone) }}"></label>
      @foreach($errors->all() as $e)<div class="err">{{ $e }}</div>@endforeach
      <button class="btn btn-primary btn-block" type="submit">Save changes</button>
    </form>
  </div>

  <div>
    <div class="card" style="margin-bottom:18px">
      <h3 style="margin-bottom:12px">Membership</h3>
      <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--line)"><span class="sub">Room band</span><b>{{ $user->band()['label'] }}</b></div>
      <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--line)"><span class="sub">Tier</span><b>{{ $user->tierMeta()['name'] }}</b></div>
      <div style="display:flex;justify-content:space-between;padding:8px 0"><span class="sub">Member since</span><b>{{ $user->created_at?->format('j M Y') }}</b></div>
    </div>

    <div class="card" style="margin-bottom:18px">
      <h3 style="margin-bottom:4px">Your signed policies</h3>
      <div class="sub" style="margin-bottom:6px">Accepted at sign-up and retained on file.</div>
      @forelse($user->policyDocuments as $pd)
        <div class="policy-doc">
          <span class="pf">PDF</span>
          <div style="flex:1;min-width:0"><b style="font-family:Oswald;font-size:14px">{{ $pd->title }}</b>
            <div class="sub" style="font-size:12px">Accepted {{ $pd->accepted_at?->format('j M Y, g:ia') }}</div></div>
          <a href="{{ route('account.policy', $pd) }}">Download</a>
        </div>
      @empty
        <div class="sub">No policy records on file.</div>
      @endforelse
    </div>

    <div class="card" style="margin-bottom:18px">
      <h3 style="margin-bottom:8px">Data protection</h3>
      <p class="sub" style="font-size:13px;line-height:1.6">All data you provide is stored securely and is never shared in a way that identifies your property. Collective use is aggregated and anonymised so individual properties cannot be identified.</p>
    </div>

    <div class="card" style="border-color:#f0d9a0">
      <h3 style="margin-bottom:8px">Cancellation policy</h3>
      <p class="sub" style="font-size:13px;line-height:1.6">You may cancel your membership within 30 days. On cancellation you lose access to the portal and are removed from member rates and the collective deals negotiated on members’ behalf.</p>
      @if($user->cancel_requested_at)
        <div class="status" style="margin-top:12px">Cancellation requested {{ $user->cancel_requested_at->format('j M Y') }} — our team will be in touch.</div>
      @else
        <form method="POST" action="{{ route('account.cancel') }}" onsubmit="return confirm('Request cancellation? You will lose access and member rates.');">
          @csrf
          <button class="btn btn-ghost btn-block" style="margin-top:12px;color:var(--coral-d);border-color:#f0c9c9">Request cancellation</button>
        </form>
      @endif
    </div>
  </div>
</div>
@endsection
