@extends('layouts.admin')
@section('title', 'Check a listing')
@section('content')
<style>
  .fld{display:block;margin-bottom:14px;max-width:680px}
  .fld span{display:block;font-weight:600;font-size:13px;margin-bottom:5px}
  .fld input,.fld select{width:100%;padding:11px 12px;border:1px solid #e2d6c2;border-radius:10px;font-size:14px}
  .cbtn{background:var(--coral,#ee6a5a);color:#fff;border:none;border-radius:10px;padding:13px 22px;font-weight:700;font-family:Oswald,sans-serif;letter-spacing:.5px;cursor:pointer}
</style>

@foreach($errors->all() as $e)<div class="status" style="background:#fdeceb;color:#b23b2e">{{ $e }}</div>@endforeach

<form method="POST" action="{{ route('admin.listings.store') }}">
  @csrf
  <label class="fld"><span>Booking.com property URL</span>
    <input type="url" name="url" value="{{ old('url') }}" placeholder="https://www.booking.com/hotel/…" required>
  </label>
  <label class="fld"><span>Link to a registered motel (optional)</span>
    <select name="user_id">
      <option value="">— not linked —</option>
      @foreach($motels as $m)
        <option value="{{ $m->id }}">{{ $m->motel ?: $m->name }} ({{ $m->email }})</option>
      @endforeach
    </select>
  </label>
  <button class="cbtn" type="submit">Analyze listing →</button>
  <p style="font-size:12.5px;color:#8a7d68;max-width:680px;margin-top:14px">We’ll try to read the page and auto-tick what we can (name, photos, review score, price). Booking.com often blocks automated reads — if so, the full checklist is still ready for you to work through manually.</p>
</form>
@endsection
