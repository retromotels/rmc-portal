@extends('layouts.admin')
@section('title', 'Images — ' . ($property->motel ?: $property->name))
@section('content')
<style>
  .panel{background:var(--paper,#fff);border-radius:14px;padding:18px 20px;box-shadow:var(--shadow,0 6px 20px rgba(0,0,0,.06));margin-bottom:16px;max-width:960px}
  .fld{display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap}
  .fld label{flex:1;min-width:220px;font-size:12.5px;font-weight:600;color:#4a4453}
  .fld input{width:100%;padding:10px 11px;border:1px solid #e2d6c2;border-radius:9px;font-size:14px;margin-top:5px}
  .save{background:var(--coral,#ee6a5a);color:#fff;border:none;border-radius:10px;padding:11px 20px;font-weight:700;font-family:Oswald,sans-serif;letter-spacing:.4px;cursor:pointer}
  .lite{border:1px solid #e2d6c2;background:#fff;border-radius:9px;padding:10px 14px;font-weight:600;font-size:13px;color:#4a4453;text-decoration:none}
  .gal{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:12px}
  .gal figure{margin:0;border-radius:10px;overflow:hidden;position:relative;background:#eee}
  .gal img{width:100%;height:120px;object-fit:cover;display:block}
  .gal a.dl{position:absolute;right:6px;bottom:6px;background:rgba(0,0,0,.6);color:#fff;font-size:11px;padding:3px 7px;border-radius:6px;text-decoration:none}
</style>

<p style="margin:0 0 14px"><a class="lite" href="{{ route('admin.motel', $property) }}">← Back to {{ $property->motel ?: $property->name }}</a></p>
@if(session('status'))<div class="status">{{ session('status') }}</div>@endif

<div class="panel">
  <h3 style="font-family:Oswald;margin:0 0 4px">Pull images</h3>
  <p style="font-size:12.5px;color:#8a7d68;margin:0 0 12px">We’ll harvest photos from this property’s website{{ $website ? ' ('.$website.')' : ' — none on file yet' }} and store them here. Add an optional extra URL (e.g. their Booking.com listing) to pull from that too. Note: some sites (incl. Booking.com) block automated access.</p>
  <form method="POST" action="{{ route('admin.images.pull', $property) }}">
    @csrf
    <div class="fld">
      <label>Extra URL (optional)<input name="extra_url" type="url" placeholder="https://www.booking.com/hotel/…"></label>
      <button class="save" type="submit">Pull images →</button>
    </div>
  </form>
</div>

<div class="panel">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
    <h3 style="font-family:Oswald;margin:0">Stored images ({{ count($files) }})</h3>
    @if(count($files))<a class="lite" href="{{ route('admin.images.zip', $property) }}">Download all (zip)</a>@endif
  </div>
  @if(count($files))
    <div class="gal">
      @foreach($files as $f)
        <figure>
          <img src="{{ route('admin.images.raw', [$property, $f]) }}" loading="lazy" alt="">
          <a class="dl" href="{{ route('admin.images.download', [$property, $f]) }}">Save</a>
        </figure>
      @endforeach
    </div>
  @else
    <div style="font-size:13px;color:#8a7d68">No images stored yet. Use “Pull images” above.</div>
  @endif
</div>
@endsection
