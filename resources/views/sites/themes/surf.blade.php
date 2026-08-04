{{-- Surf Hotel theme — bold minimal, high-contrast, cinematic coastal boutique. Self-contained, all CSS inline, classes prefixed sf-. --}}
@php
    $sfAccent = $site->accent() ?: '#111111';
    $sfWords = preg_split('/\s+/', trim(strip_tags($site->name)));
    $sfMono = '';
    foreach ($sfWords as $sfW) { if ($sfW !== '' && mb_strlen($sfMono) < 3) { $sfMono .= mb_strtoupper(mb_substr($sfW, 0, 1)); } }
    if ($sfMono === '') { $sfMono = 'RM'; }
    $sfBookHref = $site->booking_url ?: $site->source_url;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $page ? $page->title.' · '.$site->name : $site->name }}</title>
@if($indexable)
  <meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags($page && $page->body ? $page->body : ($site->description ?? $site->tagline ?? $site->name)), 155) }}">
  <link rel="canonical" href="{{ $site->urlFor($page, false) }}">
@else
  <meta name="robots" content="noindex, nofollow">
@endif
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Anton&family=Archivo:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
  :root{ --sf-bg:#f4f1ea; --sf-ink:#111111; --sf-accent:{{ $sfAccent }}; --sf-line:#111111; }
  *{ box-sizing:border-box; }
  html,body{ margin:0; padding:0; }
  body.sf-body{ background:var(--sf-bg); color:var(--sf-ink); font-family:'Archivo',system-ui,-apple-system,Segoe UI,Roboto,sans-serif; font-weight:400; line-height:1.6; -webkit-font-smoothing:antialiased; }
  .sf-body img{ display:block; max-width:100%; }
  a{ color:inherit; }

  /* preview bar */
  .sf-preview{ background:var(--sf-ink); color:var(--sf-bg); text-align:center; font-size:11px; letter-spacing:.22em; text-transform:uppercase; font-weight:600; padding:8px 14px; }

  /* top bar / nav */
  .sf-topbar{ display:flex; align-items:center; justify-content:space-between; gap:20px; padding:20px clamp(18px,5vw,64px); background:var(--sf-bg); border-bottom:1px solid rgba(17,17,17,.14); position:relative; z-index:5; flex-wrap:wrap; }
  .sf-mono{ font-family:'Archivo',sans-serif; font-weight:800; font-size:20px; letter-spacing:.18em; text-transform:uppercase; text-decoration:none; }
  .sf-nav{ display:flex; align-items:center; gap:clamp(14px,2.4vw,34px); flex-wrap:wrap; }
  .sf-nav a{ font-size:12px; font-weight:600; letter-spacing:.2em; text-transform:uppercase; text-decoration:none; padding-bottom:3px; border-bottom:1.5px solid transparent; }
  .sf-nav a:hover{ border-bottom-color:var(--sf-ink); }
  .sf-nav a.sf-active{ border-bottom-color:var(--sf-ink); }

  /* buttons */
  .sf-btn{ display:inline-block; border:1.5px solid var(--sf-ink); background:transparent; color:var(--sf-ink); font-family:'Archivo',sans-serif; font-weight:700; font-size:12px; letter-spacing:.2em; text-transform:uppercase; padding:14px 26px; cursor:pointer; text-decoration:none; transition:background .2s ease,color .2s ease; }
  .sf-btn:hover{ background:var(--sf-ink); color:var(--sf-bg); }

  /* hero */
  .sf-hero{ position:relative; min-height:88vh; display:flex; align-items:flex-end; overflow:hidden; background:var(--sf-bg); }
  .sf-hero-img{ position:absolute; inset:0; width:100%; height:100%; object-fit:cover; }
  .sf-hero-grad{ position:absolute; inset:0; background:linear-gradient(180deg,rgba(0,0,0,0) 45%,rgba(0,0,0,.6) 100%); pointer-events:none; }
  .sf-hero.sf-hero--plain{ align-items:center; justify-content:center; text-align:center; }
  .sf-hero-inner{ position:relative; z-index:2; padding:clamp(28px,6vw,72px); width:100%; }
  .sf-hero--img .sf-hero-inner{ color:#fff; }
  .sf-eyebrow{ font-size:12px; font-weight:700; letter-spacing:.28em; text-transform:uppercase; margin:0 0 14px; }
  .sf-hero-title{ font-family:'Anton','Archivo',sans-serif; font-weight:400; text-transform:uppercase; letter-spacing:.06em; line-height:.95; margin:0; font-size:clamp(48px,11vw,120px); }
  .sf-hero--plain .sf-hero-title{ color:var(--sf-ink); }
  .sf-hero-tag{ margin:18px 0 0; font-size:clamp(14px,1.8vw,19px); font-weight:400; letter-spacing:.04em; max-width:44ch; }
  .sf-hero--img .sf-hero-tag, .sf-hero--img .sf-eyebrow{ color:#fff; }

  /* generic section shell */
  .sf-wrap{ padding:clamp(48px,9vw,120px) clamp(18px,5vw,64px); }
  .sf-sec-eyebrow{ font-size:12px; font-weight:700; letter-spacing:.28em; text-transform:uppercase; margin:0 0 22px; color:var(--sf-ink); }
  .sf-lead{ max-width:62ch; font-size:clamp(17px,2vw,22px); line-height:1.7; margin:0; }
  .sf-h2{ font-family:'Anton','Archivo',sans-serif; font-weight:400; text-transform:uppercase; letter-spacing:.06em; line-height:1; margin:0 0 26px; font-size:clamp(30px,5vw,60px); }

  /* booking widget */
  .sf-book{ background:var(--sf-bg); border-top:1px solid rgba(17,17,17,.14); border-bottom:1px solid rgba(17,17,17,.14); padding:clamp(26px,4vw,44px) clamp(18px,5vw,64px); }
  .sf-book-row{ display:flex; align-items:flex-end; gap:clamp(14px,2vw,26px); flex-wrap:wrap; }
  .sf-field{ display:flex; flex-direction:column; gap:8px; }
  .sf-field label, .sf-flabel{ font-size:11px; font-weight:700; letter-spacing:.2em; text-transform:uppercase; }
  .sf-field input, .sf-field select{ font-family:'Archivo',sans-serif; font-size:15px; color:var(--sf-ink); background:transparent; border:1.5px solid var(--sf-ink); padding:12px 14px; min-width:150px; border-radius:0; }
  .sf-book .sf-from{ font-size:12px; letter-spacing:.16em; text-transform:uppercase; margin-left:auto; align-self:center; }
  .sf-book .sf-from b{ font-weight:800; }

  /* photo bands + gallery */
  .sf-band{ width:100%; height:70vh; min-height:340px; overflow:hidden; }
  .sf-band img{ width:100%; height:100%; object-fit:cover; }
  .sf-gallery{ display:grid; grid-template-columns:repeat(2,1fr); gap:2px; }
  .sf-gallery figure{ margin:0; height:min(72vh,640px); overflow:hidden; }
  .sf-gallery figure img{ width:100%; height:100%; object-fit:cover; transition:transform .6s ease; }
  .sf-gallery figure:hover img{ transform:scale(1.04); }
  @media(max-width:720px){ .sf-gallery{ grid-template-columns:1fr; } .sf-gallery figure{ height:60vh; } }

  /* amenities */
  .sf-amen{ list-style:none; margin:0; padding:0; columns:2; column-gap:60px; }
  .sf-amen li{ break-inside:avoid; padding:12px 0; border-bottom:1px solid rgba(17,17,17,.14); font-size:13px; font-weight:600; letter-spacing:.18em; text-transform:uppercase; }
  @media(max-width:640px){ .sf-amen{ columns:1; } }

  /* map */
  .sf-map-embed{ border:1.5px solid var(--sf-ink); width:100%; aspect-ratio:16/7; }
  .sf-map-embed iframe{ width:100%; height:100%; border:0; display:block; }
  .sf-addr{ font-size:12px; letter-spacing:.16em; text-transform:uppercase; margin:0 0 22px; }

  /* contact block */
  .sf-contact{ background:var(--sf-ink); color:var(--sf-bg); padding:clamp(48px,9vw,110px) clamp(18px,5vw,64px); }
  .sf-contact .sf-c-name{ font-family:'Anton','Archivo',sans-serif; font-weight:400; text-transform:uppercase; letter-spacing:.06em; line-height:.95; margin:0 0 26px; font-size:clamp(36px,7vw,84px); }
  .sf-contact .sf-sec-eyebrow{ color:var(--sf-bg); }
  .sf-c-lines{ display:flex; flex-direction:column; gap:8px; font-size:14px; letter-spacing:.06em; margin:0 0 32px; }
  .sf-c-lines a{ text-decoration:none; }
  .sf-c-lines a:hover{ text-decoration:underline; }
  .sf-btn--inv{ border-color:var(--sf-bg); color:var(--sf-bg); }
  .sf-btn--inv:hover{ background:var(--sf-bg); color:var(--sf-ink); }
  .sf-c-btns{ display:flex; gap:14px; flex-wrap:wrap; }

  /* footer */
  .sf-foot{ background:var(--sf-bg); padding:clamp(30px,5vw,54px) clamp(18px,5vw,64px); border-top:1px solid rgba(17,17,17,.14); display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap; }
  .sf-foot-name{ font-weight:800; letter-spacing:.2em; text-transform:uppercase; font-size:14px; }
  .sf-foot small{ font-size:11px; letter-spacing:.14em; text-transform:uppercase; }
  .sf-foot a{ text-decoration:underline; }

  /* internal page */
  .sf-page-head{ padding:clamp(56px,10vw,140px) clamp(18px,5vw,64px) clamp(28px,5vw,60px); }
  .sf-page-title{ font-family:'Anton','Archivo',sans-serif; font-weight:400; text-transform:uppercase; letter-spacing:.05em; line-height:.95; margin:0; font-size:clamp(40px,9vw,104px); }
  .sf-page-body{ max-width:64ch; font-size:clamp(16px,1.9vw,20px); line-height:1.8; padding:0 clamp(18px,5vw,64px) clamp(40px,7vw,80px); }
</style>
</head>
<body class="sf-body">

@if($preview)
  <div class="sf-preview">🔒 Private preview — not public</div>
@endif

@if($indexable)
  @include('sites.partials.rmc-header')
@endif

<div class="sf-topbar">
  <a class="sf-mono" href="{{ $site->urlFor(null, $preview) }}">{{ $sfMono }}</a>
  @if($site->menuPages()->count() > 0)
    <nav class="sf-nav">
      <a href="{{ $site->urlFor(null, $preview) }}" class="{{ !$page ? 'sf-active' : '' }}">Home</a>
      @foreach($site->menuPages() as $mp)
        <a href="{{ $site->urlFor($mp, $preview) }}" class="{{ $page && $page->slug === $mp->slug ? 'sf-active' : '' }}">{{ $mp->title }}</a>
      @endforeach
    </nav>
  @endif
</div>

@if($page)
  {{-- ===================== INTERNAL PAGE ===================== --}}
  <main>
    <header class="sf-page-head">
      <h1 class="sf-page-title">{{ $page->title }}</h1>
    </header>

    <div class="sf-page-body">{!! nl2br(e($page->body)) !!}</div>

    @if(!empty($page->images))
      @foreach($page->images as $img)
        <div class="sf-band"><img src="{{ $img }}" loading="lazy" alt="{{ $page->title }}" onerror="this.style.display='none'"></div>
      @endforeach
    @endif

    <div class="sf-wrap">
      <a class="sf-btn" href="{{ $site->urlFor(null, $preview) }}#sf-book">Check availability</a>
    </div>
  </main>

@else
  {{-- ===================== HOME ===================== --}}
  <main>
    @php $sfHero = $site->heroOrFirst(); @endphp
    <header class="sf-hero {{ $sfHero ? 'sf-hero--img' : 'sf-hero--plain' }}">
      @if($sfHero)
        <img class="sf-hero-img" src="{{ $sfHero }}" alt="{{ $site->name }}" onerror="this.style.display='none'">
        <div class="sf-hero-grad"></div>
      @endif
      <div class="sf-hero-inner">
        @if($site->locationLabel())<div class="sf-eyebrow">{{ $site->locationLabel() }}</div>@endif
        <h1 class="sf-hero-title">{{ $site->name }}</h1>
        @if($site->tagline)<p class="sf-hero-tag">{{ $site->tagline }}</p>@endif
      </div>
    </header>

    {{-- Booking widget --}}
    <section class="sf-book" id="sf-book">
      <div class="sf-book-row">
        <div class="sf-field">
          <label for="ci">Check-in</label>
          <input type="date" id="ci">
        </div>
        <div class="sf-field">
          <label for="co">Check-out</label>
          <input type="date" id="co">
        </div>
        <div class="sf-field">
          <label for="ad">Guests</label>
          <select id="ad">
            @for($i=1;$i<=8;$i++)<option value="{{ $i }}">{{ $i }} {{ \Illuminate\Support\Str::plural('guest',$i) }}</option>@endfor
          </select>
        </div>
        <button type="button" id="bookBtn" class="sf-btn" data-book="{{ $sfBookHref }}">Book a room</button>
        @if($site->price_from)<div class="sf-from">from <b>{{ $site->price_from }}</b></div>@endif
      </div>
    </section>
    @include('sites.partials.booking-script')

    {{-- Description --}}
    @if($site->description)
    <section class="sf-wrap">
      <div class="sf-sec-eyebrow">The stay</div>
      <p class="sf-lead">{!! nl2br(e($site->description)) !!}</p>
    </section>
    @endif

    {{-- Gallery as full-width bands / large tiles --}}
    @php $sfGallery = $site->gallery() ?: []; @endphp
    @if(count($sfGallery))
      @php
        $sfBands = array_slice($sfGallery, 0, 2);
        $sfRest = array_slice($sfGallery, 2);
      @endphp
      @foreach($sfBands as $img)
        <div class="sf-band"><img src="{{ $img }}" loading="lazy" alt="{{ $site->name }}" onerror="this.style.display='none'"></div>
      @endforeach
      @if(count($sfRest))
        <section class="sf-gallery">
          @foreach($sfRest as $img)
            <figure><img src="{{ $img }}" loading="lazy" alt="{{ $site->name }}" onerror="this.closest('figure').style.display='none'"></figure>
          @endforeach
        </section>
      @endif
    @endif

    {{-- Amenities --}}
    @if(!empty($site->amenities))
    <section class="sf-wrap">
      <div class="sf-sec-eyebrow">Amenities</div>
      <ul class="sf-amen">
        @foreach($site->amenities as $a)<li>{{ $a }}</li>@endforeach
      </ul>
    </section>
    @endif

    {{-- Map --}}
    @if($site->mapQuery())
    <section class="sf-wrap">
      <div class="sf-sec-eyebrow">Where you'll stay</div>
      @php $sfAddr = collect([$site->address,$site->city,$site->region,$site->country])->filter()->implode(', '); @endphp
      @if($sfAddr)<p class="sf-addr">{{ $sfAddr }}</p>@endif
      <div class="sf-map-embed">
        <iframe loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="https://www.google.com/maps?q={{ urlencode($site->mapQuery()) }}&output=embed"></iframe>
      </div>
    </section>
    @endif

    {{-- Contact --}}
    <section class="sf-contact">
      <div class="sf-sec-eyebrow">Get in touch</div>
      <div class="sf-c-name">{{ $site->name }}</div>
      <div class="sf-c-lines">
        @php $sfAddr2 = collect([$site->address,$site->city,$site->region,$site->country])->filter()->implode(', '); @endphp
        @if($sfAddr2)<span>{{ $sfAddr2 }}</span>@endif
        @if($site->phone)<a href="tel:{{ preg_replace('/[^0-9+]/','',$site->phone) }}">{{ $site->phone }}</a>@endif
        @if($site->email)<a href="mailto:{{ $site->email }}">{{ $site->email }}</a>@endif
      </div>
      @if($sfBookHref)
      <div class="sf-c-btns">
        <a class="sf-btn sf-btn--inv" href="{{ $sfBookHref }}" target="_blank" rel="noopener">Book a room</a>
        @if($site->booking_url)<a class="sf-btn sf-btn--inv" href="{{ $site->booking_url }}" target="_blank" rel="noopener">Book a table</a>@endif
      </div>
      @endif
    </section>
  </main>
@endif

<footer class="sf-foot">
  <div class="sf-foot-name">{{ $site->name }}</div>
  <small>Presented by the Retro Motel Collective@if($indexable && $sfBookHref) · <a href="{{ $sfBookHref }}" target="_blank" rel="noopener">Official website</a>@endif</small>
</footer>

</body>
</html>
