@extends('layouts.admin')
@section('title', 'New microsite')
@section('content')
<style>
  .th-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:14px;margin:10px 0 22px}
  .th-opt{position:relative;border:2px solid #e2d6c2;border-radius:14px;padding:16px;cursor:pointer;transition:.15s}
  .th-opt:hover{border-color:#c9bda6}
  .th-opt input{position:absolute;opacity:0}
  .th-opt .sw{height:44px;border-radius:9px;margin-bottom:10px}
  .th-opt b{font-family:Oswald,sans-serif;font-size:16px}
  .th-opt small{display:block;color:#8a7d68;font-size:12.5px;margin-top:2px}
  .th-opt .ref{font-size:11.5px;color:#b0a189;margin-top:6px;word-break:break-all}
  .th-opt.sel{border-color:var(--coral,#ee6a5a);box-shadow:0 0 0 3px rgba(238,106,90,.15)}
  .fld{display:block;margin-bottom:14px}
  .fld span{display:block;font-weight:600;font-size:13px;margin-bottom:5px}
  .fld input,.fld select{width:100%;padding:11px 12px;border:1px solid #e2d6c2;border-radius:10px;font-size:14px}
  .cbtn{background:var(--coral,#ee6a5a);color:#fff;border:none;border-radius:10px;padding:13px 22px;font-weight:700;font-family:Oswald,sans-serif;letter-spacing:.5px;cursor:pointer}
</style>

@foreach($errors->all() as $e)<div class="status" style="background:#fdeceb;color:#b23b2e">{{ $e }}</div>@endforeach

<form method="POST" action="{{ route('admin.sites.store') }}">
  @csrf
  <label class="fld"><span>1 · Choose a theme</span></label>
  <div class="th-grid">
    @foreach(config('rmc.site_themes') as $key => $t)
      <label class="th-opt" data-opt>
        <input type="radio" name="theme" value="{{ $key }}" {{ $loop->first ? 'checked' : '' }}>
        <div class="sw" style="background:linear-gradient(120deg,{{ $t['accent'] }},{{ $t['sand'] }})"></div>
        <b>{{ $t['label'] }}</b>
        <small>{{ $t['blurb'] }}</small>
        <div class="ref">ref: {{ $t['ref'] }}</div>
      </label>
    @endforeach
  </div>

  <label class="fld"><span>2 · Motel’s existing website URL (we’ll pull info & imagery)</span>
    <input type="url" name="source_url" value="{{ old('source_url') }}" placeholder="https://example.com" required>
  </label>

  <label class="fld"><span>3 · Link to a registered motel (optional)</span>
    <select name="user_id">
      <option value="">— not linked —</option>
      @foreach($motels as $m)
        <option value="{{ $m->id }}">{{ $m->motel ?: $m->name }} ({{ $m->email }})</option>
      @endforeach
    </select>
  </label>

  <button class="cbtn" type="submit">Pull content & build →</button>
  <span style="color:#8a7d68;font-size:13px;margin-left:12px">This fetches the URL — takes a few seconds.</span>
</form>

<script>
  const opts = document.querySelectorAll('[data-opt]');
  function sync(){ opts.forEach(o => o.classList.toggle('sel', o.querySelector('input').checked)); }
  opts.forEach(o => o.addEventListener('click', () => setTimeout(sync,0)));
  sync();
</script>
@endsection
