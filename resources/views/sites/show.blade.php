<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $site->name }}@if($site->locationLabel()) · {{ $site->locationLabel() }}@endif</title>
@if($indexable)
  <meta name="description" content="{{ Str::limit(strip_tags($site->description ?? $site->tagline ?? $site->name), 155) }}">
  <link rel="canonical" href="{{ $site->publicUrl() }}">
  <meta property="og:title" content="{{ $site->name }}">
  <meta property="og:description" content="{{ Str::limit(strip_tags($site->description ?? ''), 155) }}">
  @if($site->heroOrFirst())<meta property="og:image" content="{{ $site->heroOrFirst() }}">@endif
  <meta property="og:type" content="website">
@else
  <meta name="robots" content="noindex, nofollow">
@endif
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Anton&family=Cormorant+Garamond:wght@500;600;700&family=DM+Sans:wght@400;500;600;700&family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Oswald:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/microsite.css') }}">
<style>:root{--accent:{{ $site->accent() }}}</style>
</head>
<body class="theme-{{ $site->theme }}">

@if($preview)
  <div class="rmc-preview-bar">🔒 Private preview for <b>{{ $site->name }}</b> — not public, not indexed. Built by the Retro Motel Collective.</div>
@endif

<header class="hero" @if($site->heroOrFirst())style="background-image:linear-gradient(180deg,rgba(0,0,0,.15),rgba(0,0,0,.55)),url('{{ $site->heroOrFirst() }}')"@endif>
  <div class="hero-inner">
    @if($site->locationLabel())<div class="eyebrow">{{ $site->locationLabel() }}</div>@endif
    <h1>{{ $site->name }}</h1>
    @if($site->tagline)<p class="tagline">{{ $site->tagline }}</p>@endif
  </div>

  <div class="book" id="book">
    <div class="book-row">
      <label>Check-in<input type="date" id="ci"></label>
      <label>Check-out<input type="date" id="co"></label>
      <label>Guests
        <select id="ad">
          @for($i=1;$i<=8;$i++)<option value="{{ $i }}">{{ $i }} {{ Str::plural('guest',$i) }}</option>@endfor
        </select>
      </label>
      <button type="button" id="bookBtn">Check availability →</button>
    </div>
    @if($site->price_from)<div class="from">from <b>{{ $site->price_from }}</b></div>@endif
  </div>
</header>

<main>
  @if($site->description)
  <section class="about">
    <h2>About</h2>
    <p>{!! nl2br(e($site->description)) !!}</p>
  </section>
  @endif

  @if($site->gallery())
  <section class="gallery">
    <h2>Gallery</h2>
    <div class="grid">
      @foreach($site->gallery() as $img)
        <figure><img src="{{ $img }}" loading="lazy" alt="{{ $site->name }}" onerror="this.closest('figure').remove()"></figure>
      @endforeach
    </div>
  </section>
  @endif

  @if($site->amenities)
  <section class="amenities">
    <h2>Amenities</h2>
    <ul>@foreach($site->amenities as $a)<li>{{ $a }}</li>@endforeach</ul>
  </section>
  @endif

  @if($site->mapQuery())
  <section class="map">
    <h2>Where you’ll stay</h2>
    @if($site->address || $site->locationLabel())<p class="addr">{{ collect([$site->address,$site->city,$site->region,$site->country])->filter()->implode(', ') }}</p>@endif
    <div class="map-embed">
      <iframe loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="https://www.google.com/maps?q={{ urlencode($site->mapQuery()) }}&output=embed"></iframe>
    </div>
  </section>
  @endif

  <section class="cta">
    <h2>Ready to book your stay?</h2>
    <button type="button" class="cta-btn" onclick="document.getElementById('ci').scrollIntoView({behavior:'smooth',block:'center'})">Select your dates</button>
    <div class="contact">
      @if($site->phone)<span>📞 {{ $site->phone }}</span>@endif
      @if($site->email)<span>✉️ {{ $site->email }}</span>@endif
    </div>
  </section>
</main>

<footer class="foot">
  <div>{{ $site->name }}</div>
  <small>Presented by the Retro Motel Collective @if($indexable)· <a href="{{ $site->booking_url ?: $site->source_url }}" target="_blank" rel="noopener">Official website</a>@endif</small>
</footer>

<script>
  (function(){
    var ci = document.getElementById('ci'), co = document.getElementById('co');
    var t = new Date(), tm = new Date(Date.now()+864e5);
    var iso = d => d.toISOString().slice(0,10);
    ci.value = iso(t); ci.min = iso(t);
    co.value = iso(tm); co.min = iso(tm);
    ci.addEventListener('change', function(){ if(co.value <= ci.value){ var n=new Date(new Date(ci.value).getTime()+864e5); co.value=iso(n);} co.min = ci.value; });

    var base = @json($site->booking_url ?: $site->source_url);
    document.getElementById('bookBtn').addEventListener('click', function(){
      var url = base || '#';
      try {
        var u = new URL(base, window.location.href);
        u.searchParams.set('checkin', ci.value);
        u.searchParams.set('checkout', co.value);
        u.searchParams.set('adults', document.getElementById('ad').value);
        url = u.toString();
      } catch(e){}
      window.open(url, '_blank', 'noopener');
    });
  })();
</script>
</body>
</html>
