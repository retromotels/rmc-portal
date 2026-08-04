<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $page ? "{$page->title} · {$site->name}" : $site->name }}</title>
@if($indexable)
<meta name="description" content="{{ $site->tagline ?: $site->description ?: $site->name }}">
<link rel="canonical" href="{{ $site->urlFor($page, false) }}">
@else
<meta name="robots" content="noindex, nofollow">
@endif
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Anton&family=Archivo:wght@700;800&family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
<style>
:root{
  --roy-rust:#8a2c25;
  --roy-cream:#efe9df;
  --roy-pink:#e8b8a8;
  --roy-accent:{{ $site->accent() ?: '#8a2c25' }};
}
*{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{
  background:var(--roy-cream);
  color:var(--roy-rust);
  font-family:'Space Mono',monospace;
  font-size:16px;
  line-height:1.6;
  -webkit-font-smoothing:antialiased;
}
a{color:inherit}
img{max-width:100%;display:block}

/* preview bar */
.roy-preview{
  background:var(--roy-rust);
  color:var(--roy-cream);
  font-family:'Space Mono',monospace;
  font-size:12px;
  letter-spacing:.12em;
  text-transform:uppercase;
  text-align:center;
  padding:8px 12px;
}

/* theme nav */
.roy-nav{
  display:flex;
  gap:26px;
  justify-content:center;
  align-items:center;
  flex-wrap:wrap;
  padding:26px 20px 0;
}
.roy-nav a{
  font-family:'Space Mono',monospace;
  font-size:13px;
  letter-spacing:.14em;
  text-transform:uppercase;
  text-decoration:none;
  color:var(--roy-cream);
  padding-bottom:3px;
  border-bottom:2px solid transparent;
}
.roy-nav a.roy-active{border-bottom-color:var(--roy-cream)}
.roy-nav a:hover{opacity:.8}

/* HERO */
.roy-hero{
  position:relative;
  min-height:100vh;
  background:var(--roy-rust);
  color:var(--roy-cream);
  display:flex;
  flex-direction:column;
  padding:0 20px 60px;
}
.roy-hero-top{
  position:relative;
  z-index:2;
}
.roy-book-box{
  position:absolute;
  top:24px;
  right:24px;
  z-index:5;
  font-family:'Space Mono',monospace;
  font-size:12px;
  font-weight:700;
  letter-spacing:.18em;
  text-transform:uppercase;
  color:var(--roy-cream);
  text-decoration:none;
  border:2px solid var(--roy-cream);
  padding:12px 16px;
}
.roy-book-box:hover{background:var(--roy-cream);color:var(--roy-rust)}
.roy-hero-center{
  flex:1;
  display:flex;
  flex-direction:column;
  align-items:center;
  justify-content:center;
  text-align:center;
  padding:40px 0;
}
.roy-monogram{
  font-family:'Space Mono',monospace;
  font-style:italic;
  font-size:20px;
  letter-spacing:.3em;
  color:var(--roy-pink);
  margin-bottom:18px;
}
.roy-wordmark{
  font-family:'Anton','Archivo',sans-serif;
  font-weight:800;
  color:var(--roy-cream);
  font-size:clamp(60px,13vw,140px);
  line-height:.9;
  letter-spacing:-.01em;
  text-transform:uppercase;
  max-width:14ch;
}
.roy-tagline{
  font-family:'Space Mono',monospace;
  font-style:italic;
  font-size:clamp(14px,2.2vw,20px);
  color:var(--roy-pink);
  margin-top:24px;
  max-width:40ch;
}
.roy-chip{
  display:inline-block;
  margin-top:22px;
  font-family:'Space Mono',monospace;
  font-size:12px;
  letter-spacing:.14em;
  text-transform:uppercase;
  color:var(--roy-cream);
  border:1px solid var(--roy-pink);
  border-radius:999px;
  padding:6px 14px;
}

/* generic section */
.roy-section{
  max-width:1080px;
  margin:0 auto;
  padding:70px 24px;
}
.roy-label{
  font-family:'Space Mono',monospace;
  font-size:12px;
  font-weight:700;
  letter-spacing:.2em;
  text-transform:uppercase;
  color:var(--roy-rust);
  margin-bottom:20px;
  display:block;
}
.roy-heading{
  font-family:'Archivo',sans-serif;
  font-weight:800;
  font-size:clamp(28px,5vw,52px);
  line-height:1;
  text-transform:uppercase;
  margin-bottom:24px;
}
.roy-desc{
  font-family:'Space Mono',monospace;
  font-size:16px;
  max-width:60ch;
  white-space:pre-line;
}

/* booking widget */
.roy-book{
  background:var(--roy-cream);
  border-top:3px solid var(--roy-rust);
  border-bottom:3px solid var(--roy-rust);
}
.roy-book-inner{
  max-width:1080px;
  margin:0 auto;
  padding:56px 24px;
}
.roy-book-grid{
  display:flex;
  flex-wrap:wrap;
  gap:20px;
  align-items:flex-end;
}
.roy-field{display:flex;flex-direction:column;gap:8px}
.roy-field label{
  font-family:'Space Mono',monospace;
  font-size:11px;
  font-weight:700;
  letter-spacing:.16em;
  text-transform:uppercase;
}
.roy-field input,.roy-field select{
  font-family:'Space Mono',monospace;
  font-size:15px;
  color:var(--roy-rust);
  background:var(--roy-cream);
  border:2px solid var(--roy-rust);
  padding:11px 12px;
  min-width:160px;
}
.roy-btn{
  font-family:'Space Mono',monospace;
  font-size:15px;
  font-weight:700;
  letter-spacing:.1em;
  text-transform:uppercase;
  background:var(--roy-rust);
  color:var(--roy-cream);
  border:2px solid var(--roy-rust);
  padding:12px 22px;
  cursor:pointer;
}
.roy-btn:hover{background:#701f1a;border-color:#701f1a}
.roy-price{
  font-family:'Space Mono',monospace;
  font-size:13px;
  letter-spacing:.08em;
  margin-top:16px;
}

/* gallery */
.roy-gallery{
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(220px,1fr));
  gap:14px;
}
.roy-gallery img{
  width:100%;
  height:100%;
  object-fit:cover;
  aspect-ratio:4/3;
  border-radius:6px;
}

/* amenities */
.roy-amenities{
  list-style:none;
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(200px,1fr));
  gap:10px;
}
.roy-amenities li{
  font-family:'Space Mono',monospace;
  font-size:13px;
  letter-spacing:.1em;
  text-transform:uppercase;
  border-bottom:1px dotted var(--roy-rust);
  padding:8px 0;
}

/* map */
.roy-map iframe{
  width:100%;
  height:420px;
  border:3px solid var(--roy-rust);
  border-radius:6px;
}

/* contact */
.roy-contact-list{
  font-family:'Space Mono',monospace;
  font-size:15px;
  line-height:2;
}
.roy-contact-list a{text-decoration:underline}

/* internal page */
.roy-page-header{
  background:var(--roy-rust);
  color:var(--roy-cream);
  padding:80px 24px;
}
.roy-page-header .roy-inner{max-width:1080px;margin:0 auto}
.roy-page-title{
  font-family:'Anton','Archivo',sans-serif;
  font-weight:800;
  font-size:clamp(40px,8vw,96px);
  line-height:.92;
  text-transform:uppercase;
  color:var(--roy-cream);
}
.roy-back{
  display:inline-block;
  margin-top:30px;
  font-family:'Space Mono',monospace;
  font-size:13px;
  letter-spacing:.12em;
  text-transform:uppercase;
  text-decoration:underline;
}

/* footer */
.roy-footer{
  background:var(--roy-rust);
  color:var(--roy-cream);
  padding:70px 24px;
}
.roy-footer-inner{max-width:1080px;margin:0 auto;text-align:center}
.roy-footer-wordmark{
  font-family:'Anton','Archivo',sans-serif;
  font-weight:800;
  font-size:clamp(40px,9vw,110px);
  line-height:.9;
  text-transform:uppercase;
  color:var(--roy-cream);
  margin-bottom:26px;
}
.roy-footer-contact{
  font-family:'Space Mono',monospace;
  font-size:14px;
  line-height:2;
}
.roy-footer-contact a{text-decoration:underline}
.roy-footer-book{
  display:inline-block;
  margin-top:28px;
  font-family:'Space Mono',monospace;
  font-size:12px;
  font-weight:700;
  letter-spacing:.18em;
  text-transform:uppercase;
  color:var(--roy-cream);
  text-decoration:none;
  border:2px solid var(--roy-cream);
  padding:12px 18px;
}
.roy-footer-book:hover{background:var(--roy-cream);color:var(--roy-rust)}
.roy-footer-credit{
  font-family:'Space Mono',monospace;
  font-size:12px;
  letter-spacing:.08em;
  color:var(--roy-pink);
  margin-top:30px;
}
.roy-footer-official{
  display:block;
  margin-top:12px;
  font-family:'Space Mono',monospace;
  font-size:12px;
  letter-spacing:.1em;
  text-transform:uppercase;
  color:var(--roy-cream);
  text-decoration:underline;
}
</style>
</head>
<body>

@if($preview)
<div class="roy-preview">🔒 Private preview — not public</div>
@endif

@if($indexable)
@include('sites.partials.rmc-header')
@endif

@if($site->menuPages()->count() > 0)
<nav class="roy-nav">
  <a href="{{ $site->urlFor(null, $preview) }}" class="{{ !$page ? 'roy-active' : '' }}">Home</a>
  @foreach($site->menuPages() as $mp)
    <a href="{{ $site->urlFor($mp, $preview) }}" class="{{ ($page && $page->slug === $mp->slug) ? 'roy-active' : '' }}">{{ $mp->title }}</a>
  @endforeach
</nav>
@endif

@php
  $bookTarget = $site->booking_url ?: $site->source_url;
@endphp

@if(is_null($page))
{{-- ============ HOME ============ --}}
<header class="roy-hero">
  <div class="roy-hero-top">
    @if($site->menuPages()->count() === 0)
    <nav class="roy-nav">
      <a href="{{ $site->urlFor(null, $preview) }}" class="roy-active">Home</a>
    </nav>
    @endif
    <a class="roy-book-box" href="#roy-book">Book</a>
  </div>
  <div class="roy-hero-center">
    @php
      $initials = collect(explode(' ', trim($site->name)))->filter()->map(fn($w)=>mb_substr($w,0,1))->take(3)->implode('');
    @endphp
    @if($initials)
    <div class="roy-monogram">{{ $initials }}</div>
    @endif
    <h1 class="roy-wordmark">{{ $site->name }}</h1>
    <p class="roy-tagline">{{ $site->tagline ?: "You're always welcome" }}</p>
    @if($site->locationLabel())
    <span class="roy-chip">◑ {{ $site->locationLabel() }}</span>
    @endif
  </div>
</header>

<section id="roy-book" class="roy-book">
  <div class="roy-book-inner">
    <span class="roy-label">Book your stay</span>
    <div class="roy-book-grid">
      <div class="roy-field">
        <label for="ci">Check in</label>
        <input type="date" id="ci">
      </div>
      <div class="roy-field">
        <label for="co">Check out</label>
        <input type="date" id="co">
      </div>
      <div class="roy-field">
        <label for="ad">Guests</label>
        <select id="ad">
          @for($g=1;$g<=8;$g++)
          <option value="{{ $g }}">{{ $g }} {{ $g === 1 ? 'guest' : 'guests' }}</option>
          @endfor
        </select>
      </div>
      <button class="roy-btn" id="bookBtn" data-book="{{ $bookTarget }}">Book →</button>
    </div>
    @if($site->price_from)
    <p class="roy-price">From {{ $site->price_from }}</p>
    @endif
  </div>
</section>
@include('sites.partials.booking-script')

@if($site->description)
<section class="roy-section">
  <span class="roy-label">About</span>
  <p class="roy-desc">{{ $site->description }}</p>
</section>
@endif

@php $gallery = $site->gallery(); @endphp
@if(!empty($gallery))
<section class="roy-section">
  <span class="roy-label">Gallery</span>
  <div class="roy-gallery">
    @foreach($gallery as $img)
    <img src="{{ $img }}" alt="{{ $site->name }}" loading="lazy" onerror="this.style.display='none'">
    @endforeach
  </div>
</section>
@endif

@if(!empty($site->amenities))
<section class="roy-section">
  <span class="roy-label">Amenities</span>
  <ul class="roy-amenities">
    @foreach($site->amenities as $am)
    <li>{{ $am }}</li>
    @endforeach
  </ul>
</section>
@endif

@if($site->mapQuery())
<section class="roy-section roy-map">
  <span class="roy-label">Find us</span>
  <iframe src="https://www.google.com/maps?q={{ urlencode($site->mapQuery()) }}&output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Map"></iframe>
</section>
@endif

<section class="roy-section">
  <span class="roy-label">Contact</span>
  <div class="roy-contact-list">
    @if($site->address){{ $site->address }}<br>@endif
    @if($site->locationLabel()){{ $site->locationLabel() }}<br>@endif
    @if($site->phone)<a href="tel:{{ $site->phone }}">{{ $site->phone }}</a><br>@endif
    @if($site->email)<a href="mailto:{{ $site->email }}">{{ $site->email }}</a>@endif
  </div>
</section>

@else
{{-- ============ INTERNAL PAGE ============ --}}
<header class="roy-page-header">
  <div class="roy-inner">
    <h1 class="roy-page-title">{{ $page->title }}</h1>
  </div>
</header>

<section class="roy-section">
  <div class="roy-desc">{!! nl2br(e($page->body)) !!}</div>

  @if(!empty($page->images))
  <div class="roy-gallery" style="margin-top:40px">
    @foreach($page->images as $img)
    <img src="{{ $img }}" alt="{{ $page->title }}" loading="lazy" onerror="this.style.display='none'">
    @endforeach
  </div>
  @endif

  <a class="roy-back" href="{{ $site->urlFor(null, $preview) }}">← Home</a>
  &nbsp;·&nbsp;
  <a class="roy-back" href="{{ $site->urlFor(null, $preview) }}#roy-book">Check availability</a>
</section>
@endif

<footer class="roy-footer">
  <div class="roy-footer-inner">
    <div class="roy-footer-wordmark">{{ $site->name }}</div>
    <div class="roy-footer-contact">
      @if($site->address)<div>{{ $site->address }}</div>@endif
      @if($site->phone)<a href="tel:{{ $site->phone }}">{{ $site->phone }}</a>@endif
      @if($site->email)<div><a href="mailto:{{ $site->email }}">{{ $site->email }}</a></div>@endif
    </div>
    <a class="roy-footer-book" href="{{ $site->urlFor(null, $preview) }}#roy-book">Book</a>
    <div class="roy-footer-credit">Presented by the Retro Motel Collective</div>
    @if($indexable && $bookTarget)
    <a class="roy-footer-official" href="{{ $bookTarget }}" target="_blank" rel="noopener">Official website</a>
    @endif
  </div>
</footer>

</body>
</html>
