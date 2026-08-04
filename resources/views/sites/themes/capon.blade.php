<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $page ? $page->title . ' · ' . $site->name : $site->name }}</title>
@if($indexable)
  <meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags($page ? ($page->body ?: $site->description) : ($site->description ?: $site->tagline)), 155) }}">
  <link rel="canonical" href="{{ $site->urlFor($page, false) }}">
@else
  <meta name="robots" content="noindex, nofollow">
@endif
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
<style>
:root{
  --cap-cream:#f6efe2;
  --cap-white:#fffdf9;
  --cap-rust:#b5622f;
  --cap-ink:#3b2c1f;
  --cap-pink:#cf9f86;
  --cap-accent:{{ $site->accent() ?: '#b5622f' }};
}
*{box-sizing:border-box;}
html{scroll-behavior:smooth;}
body{margin:0;font-family:'DM Sans',system-ui,sans-serif;color:var(--cap-ink);background:var(--cap-white);line-height:1.6;-webkit-font-smoothing:antialiased;}
.cap-serif{font-family:'Fraunces',Georgia,serif;}
img{max-width:100%;display:block;}
a{color:inherit;}

/* preview bar */
.cap-preview{background:var(--cap-ink);color:#f6efe2;text-align:center;font-size:13px;letter-spacing:.04em;padding:8px 16px;}

/* header */
.cap-header{position:sticky;top:0;z-index:50;background:var(--cap-white);border-bottom:1px solid #ece1cf;}
.cap-header-inner{max-width:1180px;margin:0 auto;padding:14px 24px;display:flex;align-items:center;gap:20px;}
.cap-brand{display:flex;flex-direction:column;line-height:1.15;text-decoration:none;}
.cap-brand-name{font-family:'Fraunces',serif;font-weight:600;font-size:20px;color:var(--cap-ink);}
.cap-brand-sub{font-size:10.5px;letter-spacing:.18em;text-transform:uppercase;color:var(--cap-pink);margin-top:2px;}
.cap-nav{display:flex;align-items:center;gap:26px;margin-left:auto;}
.cap-nav a{text-decoration:none;font-size:14.5px;font-weight:500;color:var(--cap-ink);opacity:.8;transition:opacity .2s;}
.cap-nav a:hover{opacity:1;}
.cap-nav a.cap-active{opacity:1;color:var(--cap-rust);}
.cap-phone{font-size:14px;font-weight:500;text-decoration:none;color:var(--cap-ink);opacity:.8;}
.cap-pill{display:inline-block;background:var(--cap-rust);color:#fff;text-decoration:none;font-weight:700;font-size:14px;padding:11px 22px;border-radius:999px;border:none;cursor:pointer;transition:transform .15s,box-shadow .2s;box-shadow:0 4px 14px rgba(181,98,47,.28);}
.cap-pill:hover{transform:translateY(-1px);box-shadow:0 6px 18px rgba(181,98,47,.36);}
.cap-pill-ghost{background:transparent;color:#fff;border:1.5px solid rgba(255,255,255,.85);box-shadow:none;}
.cap-pill-ghost:hover{background:rgba(255,255,255,.12);}
.cap-burger{display:none;margin-left:auto;background:none;border:none;cursor:pointer;padding:8px;color:var(--cap-ink);}
.cap-burger span{display:block;width:22px;height:2px;background:currentColor;margin:4px 0;border-radius:2px;}

/* hero */
.cap-hero{position:relative;min-height:86vh;display:flex;align-items:flex-end;color:#fff;overflow:hidden;}
.cap-hero-img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;z-index:0;}
.cap-hero-cream{background:var(--cap-cream);color:var(--cap-ink);}
.cap-hero::after{content:"";position:absolute;inset:0;z-index:1;background:linear-gradient(to top,rgba(30,20,12,.72) 0%,rgba(30,20,12,.28) 45%,rgba(30,20,12,.05) 100%);}
.cap-hero-cream::after{display:none;}
.cap-hero-inner{position:relative;z-index:2;max-width:1180px;width:100%;margin:0 auto;padding:0 24px 9vh;}
.cap-eyebrow{font-size:12px;letter-spacing:.24em;text-transform:uppercase;font-weight:700;color:var(--cap-pink);margin-bottom:14px;}
.cap-hero .cap-eyebrow{color:#f0d9c8;}
.cap-hero-title{font-family:'Fraunces',serif;font-weight:500;font-size:clamp(38px,6vw,68px);line-height:1.03;margin:0 0 18px;max-width:16ch;}
.cap-hero-sub{font-size:clamp(15px,1.6vw,18px);max-width:52ch;opacity:.95;margin:0 0 28px;}
.cap-hero-btns{display:flex;flex-wrap:wrap;gap:14px;}

/* sections */
.cap-section{padding:74px 24px;}
.cap-cream-band{background:var(--cap-cream);}
.cap-wrap{max-width:1080px;margin:0 auto;}
.cap-narrow{max-width:760px;margin:0 auto;}
.cap-h2{font-family:'Fraunces',serif;font-weight:600;font-size:clamp(26px,3.4vw,40px);line-height:1.1;margin:6px 0 22px;color:var(--cap-ink);}
.cap-lead{font-size:17px;color:#5c4a39;}

/* booking widget */
.cap-book-card{background:var(--cap-white);border-radius:18px;box-shadow:0 18px 44px rgba(59,44,31,.12);padding:28px;border:1px solid #efe4d3;}
.cap-book-grid{display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:18px;align-items:end;}
.cap-field{display:flex;flex-direction:column;gap:6px;}
.cap-field label{font-size:11px;letter-spacing:.14em;text-transform:uppercase;font-weight:700;color:var(--cap-pink);}
.cap-field input,.cap-field select{font-family:inherit;font-size:15px;padding:12px 14px;border-radius:12px;border:1.5px solid #e6d9c6;background:#fff;color:var(--cap-ink);}
.cap-field input:focus,.cap-field select:focus{outline:none;border-color:var(--cap-rust);}
.cap-book-card .cap-pill{height:47px;}
.cap-price{margin-top:16px;font-size:14px;color:#7a6552;}
.cap-price b{font-family:'Fraunces',serif;color:var(--cap-rust);font-size:17px;}

/* chips */
.cap-chips{display:flex;flex-wrap:wrap;gap:10px;margin-top:6px;}
.cap-chip{background:#efe3d1;color:var(--cap-ink);border-radius:999px;padding:9px 17px;font-size:14px;font-weight:500;}

/* gallery */
.cap-gal{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;}
.cap-gal img{width:100%;height:100%;min-height:220px;object-fit:cover;border-radius:16px;}
.cap-gal figure{margin:0;}
.cap-gal figure:nth-child(6n+1){grid-column:span 2;}

/* map */
.cap-map-addr{font-size:15px;color:#5c4a39;margin-bottom:16px;}
.cap-map-frame{border-radius:18px;overflow:hidden;box-shadow:0 12px 30px rgba(59,44,31,.12);border:1px solid #efe4d3;}
.cap-map-frame iframe{display:block;width:100%;height:380px;border:0;}

/* contact card */
.cap-contact-card{background:var(--cap-white);border:1px solid #efe4d3;border-radius:18px;padding:28px;box-shadow:0 12px 30px rgba(59,44,31,.08);display:grid;gap:14px;}
.cap-contact-card a{color:var(--cap-rust);text-decoration:none;font-weight:500;}
.cap-contact-row{font-size:16px;}
.cap-contact-row span{display:block;font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:var(--cap-pink);font-weight:700;margin-bottom:2px;}

/* internal page hero */
.cap-page-hero{position:relative;min-height:42vh;display:flex;align-items:flex-end;color:#fff;overflow:hidden;}
.cap-page-hero .cap-hero-title{font-size:clamp(30px,4.5vw,52px);margin-bottom:0;}
.cap-page-body p{font-size:17px;color:#5c4a39;}
.cap-back{display:inline-flex;gap:8px;margin-top:8px;}

/* footer */
.cap-footer{background:var(--cap-cream);border-top:1px solid #ece1cf;padding:56px 24px;}
.cap-footer-inner{max-width:1080px;margin:0 auto;text-align:center;display:grid;gap:14px;justify-items:center;}
.cap-foot-name{font-family:'Fraunces',serif;font-weight:600;font-size:26px;color:var(--cap-ink);}
.cap-foot-meta{font-size:14px;color:#7a6552;}
.cap-foot-meta a{color:var(--cap-rust);}
.cap-foot-official{font-size:13px;}

@media (max-width:820px){
  .cap-nav{display:none;position:absolute;top:100%;left:0;right:0;flex-direction:column;align-items:flex-start;gap:0;background:var(--cap-white);border-bottom:1px solid #ece1cf;padding:8px 24px 18px;}
  .cap-nav.cap-open{display:flex;}
  .cap-nav a{padding:12px 0;width:100%;border-bottom:1px solid #f1e7d7;}
  .cap-nav .cap-pill,.cap-nav .cap-phone{margin-top:12px;}
  .cap-burger{display:block;}
  .cap-book-grid{grid-template-columns:1fr 1fr;}
  .cap-gal{grid-template-columns:repeat(2,1fr);}
  .cap-gal figure:nth-child(6n+1){grid-column:auto;}
}
@media (max-width:520px){
  .cap-book-grid{grid-template-columns:1fr;}
  .cap-gal{grid-template-columns:1fr;}
}
</style>
</head>
<body>

@if($preview)
  <div class="cap-preview">🔒 Private preview — not public</div>
@endif

@if($indexable)
  @include('sites.partials.rmc-header')
@endif

@php
  $menuPages = $site->menuPages();
  $heroImg = $site->heroOrFirst();
  $gallery = $site->gallery() ?: [];
@endphp

{{-- Theme nav: white sticky header --}}
<header class="cap-header">
  <div class="cap-header-inner">
    <a class="cap-brand" href="{{ $site->urlFor(null, $preview) }}">
      <span class="cap-brand-name cap-serif">{{ $site->name }}</span>
      @if($site->locationLabel())
        <span class="cap-brand-sub">{{ $site->locationLabel() }}</span>
      @endif
    </a>
    <button class="cap-burger" type="button" aria-label="Menu" onclick="document.getElementById('capNav').classList.toggle('cap-open')">
      <span></span><span></span><span></span>
    </button>
    <nav class="cap-nav" id="capNav">
      <a href="{{ $site->urlFor(null, $preview) }}" class="{{ !$page ? 'cap-active' : '' }}">Home</a>
      @if($menuPages->count())
        @foreach($menuPages as $mp)
          <a href="{{ $site->urlFor($mp, $preview) }}" class="{{ $page && $page->slug === $mp->slug ? 'cap-active' : '' }}">{{ $mp->title }}</a>
        @endforeach
      @endif
      @if($site->phone)
        <a class="cap-phone" href="tel:{{ $site->phone }}">{{ $site->phone }}</a>
      @endif
      <a class="cap-pill" href="{{ $page ? $site->urlFor(null, $preview) . '#cap-book' : '#cap-book' }}">Book Now</a>
    </nav>
  </div>
</header>

<main>
@if(is_null($page))
  {{-- ============ HOME ============ --}}
  @if($heroImg)
    <section class="cap-hero">
      <img class="cap-hero-img" src="{{ $heroImg }}" alt="{{ $site->name }}" onerror="this.style.display='none'">
      <div class="cap-hero-inner">
        <div class="cap-eyebrow">{{ $site->locationLabel() ?: 'Holiday accommodation' }}</div>
        <h1 class="cap-hero-title cap-serif">{{ $site->tagline ?: $site->name }}</h1>
        @if($site->description)
          <p class="cap-hero-sub">{{ \Illuminate\Support\Str::limit(strip_tags($site->description), 180) }}</p>
        @endif
        <div class="cap-hero-btns">
          <a class="cap-pill" href="#cap-book">Check Availability</a>
          <a class="cap-pill cap-pill-ghost" href="#cap-gallery">Explore the stay</a>
        </div>
      </div>
    </section>
  @else
    <section class="cap-hero cap-hero-cream">
      <div class="cap-hero-inner">
        <div class="cap-eyebrow">{{ $site->locationLabel() ?: 'Holiday accommodation' }}</div>
        <h1 class="cap-hero-title cap-serif">{{ $site->tagline ?: $site->name }}</h1>
        @if($site->description)
          <p class="cap-hero-sub">{{ \Illuminate\Support\Str::limit(strip_tags($site->description), 180) }}</p>
        @endif
        <div class="cap-hero-btns">
          <a class="cap-pill" href="#cap-book">Check Availability</a>
          <a class="cap-pill cap-pill-ghost" style="color:var(--cap-rust);border-color:var(--cap-rust);" href="#cap-gallery">Explore the stay</a>
        </div>
      </div>
    </section>
  @endif

  {{-- Booking widget --}}
  <section class="cap-section cap-cream-band" id="cap-book">
    <div class="cap-narrow">
      <div class="cap-eyebrow">Reserve your dates</div>
      <h2 class="cap-h2 cap-serif">Check availability</h2>
      <div class="cap-book-card">
        <div class="cap-book-grid">
          <div class="cap-field">
            <label for="ci">Check in</label>
            <input type="date" id="ci">
          </div>
          <div class="cap-field">
            <label for="co">Check out</label>
            <input type="date" id="co">
          </div>
          <div class="cap-field">
            <label for="ad">Guests</label>
            <select id="ad">
              @for($g = 1; $g <= 8; $g++)
                <option value="{{ $g }}">{{ $g }} {{ $g === 1 ? 'guest' : 'guests' }}</option>
              @endfor
            </select>
          </div>
          <button class="cap-pill" id="bookBtn" data-book="{{ $site->booking_url ?: $site->source_url }}">Check availability</button>
        </div>
        @if($site->price_from)
          <div class="cap-price">From <b>{{ $site->price_from }}</b> per night</div>
        @endif
      </div>
    </div>
  </section>
  @include('sites.partials.booking-script')

  {{-- About --}}
  @if($site->description)
    <section class="cap-section">
      <div class="cap-narrow">
        <div class="cap-eyebrow">The stay</div>
        <h2 class="cap-h2 cap-serif">About {{ $site->name }}</h2>
        <p class="cap-lead">{!! nl2br(e($site->description)) !!}</p>
      </div>
    </section>
  @endif

  {{-- Amenities --}}
  @if(!empty($site->amenities) && is_array($site->amenities))
    <section class="cap-section cap-cream-band">
      <div class="cap-wrap">
        <div class="cap-eyebrow">What's included</div>
        <h2 class="cap-h2 cap-serif">Amenities</h2>
        <div class="cap-chips">
          @foreach($site->amenities as $amenity)
            <span class="cap-chip">{{ $amenity }}</span>
          @endforeach
        </div>
      </div>
    </section>
  @endif

  {{-- Gallery --}}
  @if(!empty($gallery))
    <section class="cap-section" id="cap-gallery">
      <div class="cap-wrap">
        <div class="cap-eyebrow">A closer look</div>
        <h2 class="cap-h2 cap-serif">Gallery</h2>
        <div class="cap-gal">
          @foreach($gallery as $img)
            <figure><img src="{{ $img }}" alt="{{ $site->name }}" loading="lazy" onerror="this.style.display='none'"></figure>
          @endforeach
        </div>
      </div>
    </section>
  @endif

  {{-- Map --}}
  @if($site->mapQuery())
    <section class="cap-section cap-cream-band">
      <div class="cap-wrap">
        <div class="cap-eyebrow">Find us</div>
        <h2 class="cap-h2 cap-serif">Location</h2>
        @if($site->address)
          <div class="cap-map-addr">{{ $site->address }}{{ $site->city ? ', ' . $site->city : '' }}</div>
        @endif
        <div class="cap-map-frame">
          <iframe src="https://www.google.com/maps?q={{ urlencode($site->mapQuery()) }}&output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Map"></iframe>
        </div>
      </div>
    </section>
  @endif

  {{-- Contact --}}
  <section class="cap-section">
    <div class="cap-narrow">
      <div class="cap-eyebrow">Get in touch</div>
      <h2 class="cap-h2 cap-serif">Contact</h2>
      <div class="cap-contact-card">
        @if($site->phone)
          <div class="cap-contact-row"><span>Phone</span><a href="tel:{{ $site->phone }}">{{ $site->phone }}</a></div>
        @endif
        @if($site->email)
          <div class="cap-contact-row"><span>Email</span><a href="mailto:{{ $site->email }}">{{ $site->email }}</a></div>
        @endif
        @if($site->address || $site->locationLabel())
          <div class="cap-contact-row"><span>Address</span>{{ $site->address ?: $site->locationLabel() }}</div>
        @endif
      </div>
    </div>
  </section>

@else
  {{-- ============ INTERNAL PAGE ============ --}}
  @php
    $pageImg = (!empty($page->images) && is_array($page->images)) ? $page->images[0] : $heroImg;
  @endphp
  @if($pageImg)
    <section class="cap-page-hero">
      <img class="cap-hero-img" src="{{ $pageImg }}" alt="{{ $page->title }}" onerror="this.style.display='none'">
      <div class="cap-hero-inner">
        <div class="cap-eyebrow">{{ $site->name }}</div>
        <h1 class="cap-hero-title cap-serif">{{ $page->title }}</h1>
      </div>
    </section>
  @else
    <section class="cap-page-hero cap-hero-cream">
      <div class="cap-hero-inner">
        <div class="cap-eyebrow">{{ $site->name }}</div>
        <h1 class="cap-hero-title cap-serif">{{ $page->title }}</h1>
      </div>
    </section>
  @endif

  <section class="cap-section cap-cream-band">
    <div class="cap-narrow cap-page-body">
      <p>{!! nl2br(e($page->body)) !!}</p>
      <div class="cap-back">
        <a class="cap-pill" href="{{ $site->urlFor(null, $preview) }}">Back to Home</a>
        <a class="cap-pill cap-pill-ghost" style="color:var(--cap-rust);border-color:var(--cap-rust);" href="{{ $site->urlFor(null, $preview) }}#cap-book">Check availability</a>
      </div>
    </div>
  </section>

  @if(!empty($page->images) && is_array($page->images))
    <section class="cap-section">
      <div class="cap-wrap">
        <div class="cap-eyebrow">Gallery</div>
        <h2 class="cap-h2 cap-serif">{{ $page->title }}</h2>
        <div class="cap-gal">
          @foreach($page->images as $img)
            <figure><img src="{{ $img }}" alt="{{ $page->title }}" loading="lazy" onerror="this.style.display='none'"></figure>
          @endforeach
        </div>
      </div>
    </section>
  @endif
@endif
</main>

<footer class="cap-footer">
  <div class="cap-footer-inner">
    <div class="cap-foot-name cap-serif">{{ $site->name }}</div>
    <div class="cap-foot-meta">
      @if($site->phone)<a href="tel:{{ $site->phone }}">{{ $site->phone }}</a>@endif
      @if($site->phone && $site->email) &nbsp;·&nbsp; @endif
      @if($site->email)<a href="mailto:{{ $site->email }}">{{ $site->email }}</a>@endif
    </div>
    <div class="cap-foot-meta">Presented by the Retro Motel Collective</div>
    @if($indexable && ($site->booking_url || $site->source_url))
      <a class="cap-pill cap-foot-official" href="{{ $site->booking_url ?: $site->source_url }}" target="_blank" rel="noopener">Official website</a>
    @endif
  </div>
</footer>

</body>
</html>
