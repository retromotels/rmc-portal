@extends('layouts.admin')
@section('title', 'Edit microsite')
@section('content')
<style>
  .ed-wrap{display:grid;grid-template-columns:1fr;gap:18px;max-width:960px}
  .panel{background:var(--paper,#fff);border-radius:14px;padding:18px 20px;box-shadow:var(--shadow,0 6px 20px rgba(0,0,0,.06))}
  .panel h3{font-family:Oswald,sans-serif;font-size:16px;margin:0 0 12px;letter-spacing:.3px}
  .row2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
  .row3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px}
  .fld{display:block;margin-bottom:12px}
  .fld span{display:block;font-weight:600;font-size:12.5px;margin-bottom:5px;color:#4a4453}
  .fld input,.fld select,.fld textarea{width:100%;padding:10px 11px;border:1px solid #e2d6c2;border-radius:9px;font-size:14px;font-family:inherit}
  .fld textarea{resize:vertical}
  .share{display:flex;flex-wrap:wrap;gap:10px;align-items:center}
  .pill{background:#f6efe4;border:1px solid #e7dcc8;border-radius:9px;padding:8px 12px;font-size:13px}
  .pill code{font-weight:700;color:#b23b2e}
  .cpy{cursor:pointer;border:1px solid #e2d6c2;background:#fff;border-radius:8px;padding:7px 10px;font-size:12.5px;font-weight:600}
  .gal{display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:10px}
  .gal figure{margin:0;border-radius:10px;overflow:hidden;position:relative;border:2px solid transparent}
  .gal img{width:100%;height:82px;object-fit:cover;display:block;background:#eee}
  .gal .hbtn{position:absolute;left:6px;bottom:6px;font-size:11px;font-weight:700;background:rgba(0,0,0,.6);color:#fff;border:none;border-radius:6px;padding:3px 7px;cursor:pointer}
  .gal figure.is-hero{border-color:var(--coral,#ee6a5a)}
  .gal figure.is-hero:after{content:'HERO';position:absolute;right:6px;top:6px;background:var(--coral,#ee6a5a);color:#fff;font-size:10px;font-weight:700;padding:2px 6px;border-radius:5px}
  .save{background:var(--coral,#ee6a5a);color:#fff;border:none;border-radius:10px;padding:12px 24px;font-weight:700;font-family:Oswald,sans-serif;letter-spacing:.5px;cursor:pointer}
  .lite{border:1px solid #e2d6c2;background:#fff;border-radius:9px;padding:9px 14px;font-weight:600;font-size:13px;color:#4a4453;text-decoration:none;cursor:pointer}
  .switch{display:inline-flex;align-items:center;gap:9px;font-weight:600}
  .log{width:100%;border-collapse:collapse;font-size:13px}
  .log th,.log td{text-align:left;padding:7px 8px;border-bottom:1px solid #efe7d8}
  .log th{color:#8a7d68;font-weight:600;font-size:11.5px;text-transform:uppercase;letter-spacing:.4px}
  .ok{color:#1c7a45;font-weight:700}
</style>

@if(session('status'))<div class="status">{{ session('status') }}</div>@endif
@foreach($errors->all() as $e)<div class="status" style="background:#fdeceb;color:#b23b2e">{{ $e }}</div>@endforeach

<div class="ed-wrap">

  {{-- Share / publish --}}
  <div class="panel">
    <h3>Share &amp; publish</h3>
    <div class="share" style="margin-bottom:12px">
      <span class="pill">🔒 Private preview: <a href="{{ $site->previewUrl() }}" target="_blank" id="pvLink">{{ $site->previewUrl() }}</a></span>
      <span class="pill">Password: <code id="pvPwd">{{ $site->preview_password }}</code></span>
      <button type="button" class="cpy" onclick="copyText('{{ $site->previewUrl() }}\nPassword: {{ $site->preview_password }}')">Copy link + password</button>
    </div>
    <div class="share">
      <form method="POST" action="{{ route('admin.sites.publish', $site) }}">@csrf
        <button class="switch lite" type="submit">
          @if($site->published) 🟢 Public page is LIVE — turn OFF @else ⚪ Publish public page (indexable) @endif
        </button>
      </form>
      @if($site->publicUrl())
        <span class="pill">🌐 Public: <a href="{{ $site->publicUrl() }}" target="_blank">{{ $site->publicUrl() }}</a></span>
      @endif
    </div>
    <p style="font-size:12.5px;color:#8a7d68;margin:10px 0 0">The preview link is <b>not indexed</b> by search engines and requires the password. The public page is indexable and shows a booking button that deep-links to the property’s site.</p>
  </div>

  {{-- Content form --}}
  <form method="POST" action="{{ route('admin.sites.update', $site) }}">
    @csrf @method('PUT')

    <div class="panel">
      <h3>Theme &amp; headline</h3>
      <div class="row2">
        <label class="fld"><span>Theme</span>
          <select name="theme">
            @foreach(config('rmc.site_themes') as $key => $t)
              <option value="{{ $key }}" @selected($site->theme === $key)>{{ $t['label'] }} — {{ $t['blurb'] }}</option>
            @endforeach
          </select>
        </label>
        <label class="fld"><span>Price from (e.g. “$189/night”)</span><input name="price_from" value="{{ old('price_from', $site->price_from) }}"></label>
      </div>
      <label class="fld"><span>Property name</span><input name="name" value="{{ old('name', $site->name) }}" required></label>
      <label class="fld"><span>Tagline</span><input name="tagline" value="{{ old('tagline', $site->tagline) }}" placeholder="A short line under the name"></label>
      <label class="fld"><span>Description</span><textarea name="description" rows="4">{{ old('description', $site->description) }}</textarea></label>
    </div>

    <div class="panel">
      <h3>Location &amp; contact</h3>
      <label class="fld"><span>Street address</span><input name="address" value="{{ old('address', $site->address) }}"></label>
      <div class="row3">
        <label class="fld"><span>City / town</span><input name="city" value="{{ old('city', $site->city) }}"></label>
        <label class="fld"><span>State / region</span><input name="region" value="{{ old('region', $site->region) }}"></label>
        <label class="fld"><span>Country</span><input name="country" value="{{ old('country', $site->country) }}"></label>
      </div>
      <div class="row2">
        <label class="fld"><span>Latitude (for map)</span><input name="lat" value="{{ old('lat', $site->lat) }}"></label>
        <label class="fld"><span>Longitude (for map)</span><input name="lng" value="{{ old('lng', $site->lng) }}"></label>
      </div>
      <div class="row2">
        <label class="fld"><span>Phone</span><input name="phone" value="{{ old('phone', $site->phone) }}"></label>
        <label class="fld"><span>Email</span><input name="email" value="{{ old('email', $site->email) }}"></label>
      </div>
      <label class="fld"><span>Booking button URL (where the date selector sends guests)</span><input name="booking_url" value="{{ old('booking_url', $site->booking_url) }}" placeholder="https://the-property.com/book"></label>
    </div>

    <div class="panel">
      <h3>Imagery</h3>
      <label class="fld"><span>Hero image URL</span><input name="hero_image" id="heroInput" value="{{ old('hero_image', $site->hero_image) }}"></label>
      @if($site->images)
        <p style="font-size:12.5px;color:#8a7d68;margin:0 0 8px">Pulled images — click “Hero” to feature one. Remove any you don’t want from the box below.</p>
        <div class="gal" id="gal">
          @foreach($site->images as $img)
            <figure data-url="{{ $img }}"><img src="{{ $img }}" loading="lazy" onerror="this.closest('figure').style.opacity=.3"><button type="button" class="hbtn" onclick="setHero('{{ $img }}')">Hero</button></figure>
          @endforeach
        </div>
      @endif
      <label class="fld" style="margin-top:12px"><span>Gallery image URLs (one per line)</span><textarea name="images_text" rows="6">{{ old('images_text', implode("\n", $site->images ?? [])) }}</textarea></label>
      <label class="fld"><span>Amenities (one per line)</span><textarea name="amenities_text" rows="4" placeholder="Free Wi-Fi&#10;Pool&#10;Pet friendly">{{ old('amenities_text', implode("\n", $site->amenities ?? [])) }}</textarea></label>
    </div>

    <div class="panel" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
      <button class="save" type="submit">Save changes</button>
      <a class="lite" href="{{ $site->previewUrl() }}" target="_blank">Open preview ↗</a>
      <span style="flex:1"></span>
      <label class="switch" style="font-size:13px"><input type="checkbox" name="published" value="1" @checked($site->published)> Keep public page live</label>
    </div>
  </form>

  {{-- Rescrape --}}
  <div class="panel">
    <h3>Source</h3>
    <div class="share">
      <span class="pill">Pulled from: <a href="{{ $site->source_url }}" target="_blank">{{ $site->source_url }}</a></span>
      <form method="POST" action="{{ route('admin.sites.rescrape', $site) }}">@csrf<button class="lite" type="submit">↻ Re-pull content</button></form>
    </div>
  </div>

  {{-- Access log --}}
  <div class="panel">
    <h3>Preview access log</h3>
    @php $hits = $site->views->where('kind','preview'); @endphp
    @if($hits->isEmpty())
      <p style="font-size:13px;color:#8a7d68;margin:0">No one has opened the preview yet.</p>
    @else
      <table class="log">
        <thead><tr><th>When</th><th>Result</th><th>IP</th><th>Device</th></tr></thead>
        <tbody>
        @foreach($hits->take(30) as $v)
          <tr>
            <td>{{ $v->created_at?->format('j M Y, g:ia') }}</td>
            <td>@if($v->unlocked)<span class="ok">✓ Entered password</span>@else opened gate @endif</td>
            <td>{{ $v->ip }}</td>
            <td>{{ Str::limit($v->user_agent, 42) }}</td>
          </tr>
        @endforeach
        </tbody>
      </table>
    @endif
  </div>
</div>

<script>
  function setHero(u){ document.getElementById('heroInput').value = u; markHero(u); }
  function markHero(u){ document.querySelectorAll('#gal figure').forEach(f => f.classList.toggle('is-hero', f.dataset.url === u)); }
  markHero(document.getElementById('heroInput').value);
  document.getElementById('heroInput').addEventListener('input', e => markHero(e.target.value));
  function copyText(t){ navigator.clipboard?.writeText(t).then(()=>{}, ()=>{}); }
</script>
@endsection
