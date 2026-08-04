<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $page ? "{$page->title} · {$site->name}" : $site->name }}</title>
@if($indexable)
  <meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags($site->description ?? $site->tagline ?? $site->name), 155) }}">
  <link rel="canonical" href="{{ $site->urlFor($page, false) }}">
@else
  <meta name="robots" content="noindex, nofollow">
@endif
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo:wght@600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<style>
  :root{
    --ss-sand:#e7dcc6;
    --ss-ink:#211c15;
    --ss-accent:{{ $site->accent() }};
    --ss-line:rgba(33,28,21,.28);
  }
  *{box-sizing:border-box}
  html,body{margin:0;padding:0}
  body.ss-body{
    background:var(--ss-sand);
    color:var(--ss-ink);
    font-family:'Space Mono',ui-monospace,monospace;
    font-size:15px;
    line-height:1.6;
    -webkit-font-smoothing:antialiased;
    overflow-x:hidden;
  }
  .ss-body img{max-width:100%;display:block}
  .ss-body a{color:var(--ss-ink);text-decoration:none;border-bottom:1px solid var(--ss-line)}
  .ss-body a:hover{border-bottom-color:var(--ss-ink)}

  .ss-preview{
    background:var(--ss-ink);color:var(--ss-sand);
    font-family:'Archivo',sans-serif;font-weight:600;
    font-size:11px;letter-spacing:.18em;text-transform:uppercase;
    text-align:center;padding:8px 14px;
  }

  .ss-eyebrow{
    font-family:'Archivo',sans-serif;font-weight:700;
    font-size:11px;letter-spacing:.24em;text-transform:uppercase;
  }

  .ss-wrap{max-width:1120px;margin:0 auto;padding:0 28px}

  /* masthead */
  .ss-masthead{padding:48px 0 24px}
  .ss-mast-loc{color:var(--ss-ink);opacity:.75;margin-bottom:22px}
  .ss-mast-name{
    font-family:'Space Mono',monospace;font-weight:700;
    font-size:clamp(20px,3.4vw,30px);letter-spacing:.06em;
    text-transform:uppercase;margin:0;
  }
  .ss-mast-tag{margin:8px 0 0;max-width:560px;opacity:.85}

  /* theme nav */
  .ss-nav{
    display:flex;flex-wrap:wrap;gap:6px 22px;
    padding:16px 0;border-top:1px solid var(--ss-line);
    border-bottom:1px solid var(--ss-line);margin-top:26px;
    font-family:'Archivo',sans-serif;font-weight:600;
    font-size:11px;letter-spacing:.18em;text-transform:uppercase;
  }
  .ss-nav a{border-bottom:none;opacity:.6}
  .ss-nav a:hover{opacity:1}
  .ss-nav a.ss-active{opacity:1;border-bottom:2px solid var(--ss-accent);padding-bottom:2px}

  /* side tabs */
  .ss-tabs{position:fixed;right:0;top:38%;z-index:40;display:flex;flex-direction:column;gap:10px}
  .ss-tab{
    writing-mode:vertical-rl;text-orientation:mixed;
    background:var(--ss-sand);border:1px solid var(--ss-ink);
    border-right:none;padding:16px 9px;
    font-family:'Archivo',sans-serif;font-weight:700;
    font-size:11px;letter-spacing:.22em;text-transform:uppercase;
    cursor:pointer;color:var(--ss-ink);
  }
  .ss-tab:hover{background:var(--ss-ink);color:var(--ss-sand)}
  @media(max-width:820px){.ss-tabs{display:none}}

  /* collage */
  .ss-collage{
    display:flex;flex-wrap:wrap;align-items:flex-start;
    gap:38px 46px;padding:44px 0 20px;
  }
  .ss-piece{margin-top:0}
  .ss-piece figure{margin:0}
  .ss-piece img{
    width:100%;height:auto;
    border:1px solid var(--ss-ink);
    background:#d9cdb2;
  }
  .ss-cap{
    font-family:'Archivo',sans-serif;font-weight:600;
    font-size:10px;letter-spacing:.2em;text-transform:uppercase;
    margin-top:9px;opacity:.72;
  }
  .ss-w-s{width:200px}
  .ss-w-m{width:270px}
  .ss-w-l{width:360px}
  .ss-drop{margin-top:52px}
  .ss-rise{margin-top:-14px}
  .ss-rot-a{transform:rotate(-2deg)}
  .ss-rot-b{transform:rotate(2deg)}
  @media(max-width:640px){
    .ss-w-s,.ss-w-m,.ss-w-l{width:100%}
    .ss-drop,.ss-rise{margin-top:0}
    .ss-rot-a,.ss-rot-b{transform:none}
    .ss-collage{gap:30px 0}
  }

  /* sections */
  .ss-section{padding:34px 0;border-top:1px solid var(--ss-line)}
  .ss-label{margin-bottom:20px;opacity:.85}
  .ss-col{max-width:560px}
  .ss-col p{margin:0 0 14px}

  /* booking */
  .ss-book{padding:38px 0;border-top:1px solid var(--ss-line)}
  .ss-book-row{display:flex;flex-wrap:wrap;gap:14px 18px;align-items:flex-end;margin-top:16px}
  .ss-field{display:flex;flex-direction:column;gap:6px}
  .ss-field span{
    font-family:'Archivo',sans-serif;font-weight:600;
    font-size:10px;letter-spacing:.2em;text-transform:uppercase;opacity:.7;
  }
  .ss-input{
    font-family:'Space Mono',monospace;font-size:14px;color:var(--ss-ink);
    background:transparent;border:1px solid var(--ss-ink);
    padding:10px 12px;min-width:150px;border-radius:0;
  }
  .ss-btn{
    font-family:'Archivo',sans-serif;font-weight:700;
    font-size:12px;letter-spacing:.16em;text-transform:uppercase;
    background:transparent;color:var(--ss-ink);
    border:1px solid var(--ss-ink);padding:11px 20px;
    cursor:pointer;border-radius:0;
  }
  .ss-btn:hover{background:var(--ss-ink);color:var(--ss-sand)}
  .ss-from{margin-top:14px;opacity:.8;font-size:13px}

  /* amenities */
  .ss-amen{list-style:none;margin:0;padding:0;max-width:560px;columns:2;column-gap:34px}
  .ss-amen li{margin:0 0 8px;break-inside:avoid}
  .ss-amen li::before{content:"— ";opacity:.6}
  @media(max-width:520px){.ss-amen{columns:1}}

  /* map */
  .ss-map-frame{border:1px solid var(--ss-ink);max-width:820px}
  .ss-map-frame iframe{width:100%;height:360px;display:block;border:0;filter:grayscale(.25)}

  /* contact */
  .ss-contact p{margin:0 0 6px}

  /* internal page */
  .ss-page-title{
    font-family:'Archivo',sans-serif;font-weight:700;
    font-size:clamp(22px,4vw,38px);letter-spacing:.14em;text-transform:uppercase;
    margin:6px 0 22px;
  }
  .ss-back{margin-top:26px;display:flex;gap:22px;flex-wrap:wrap;
    font-family:'Archivo',sans-serif;font-weight:600;font-size:11px;
    letter-spacing:.18em;text-transform:uppercase}

  /* footer */
  .ss-foot{
    border-top:1px solid var(--ss-ink);
    margin-top:20px;padding:34px 0 60px;
  }
  .ss-foot-name{
    font-family:'Space Mono',monospace;font-weight:700;
    text-transform:uppercase;letter-spacing:.06em;
  }
  .ss-foot small{display:block;margin-top:8px;opacity:.75;font-size:12px}
</style>
</head>
<body class="ss-body">

@if($preview)
  <div class="ss-preview">🔒 Private preview — not public</div>
@endif

@if($indexable)
  @include('sites.partials.rmc-header')
@endif

@php
  $menuPages = $site->menuPages();
  $galleryImgs = $site->gallery() ?: [];
  $capBase = $menuPages->pluck('title')->all();
  if(empty($capBase)) $capBase = ['Rooms','Kitchen & Bar','The Pool','Stay','Grounds','Details'];
  $widths = ['ss-w-m','ss-w-l','ss-w-s','ss-w-m','ss-w-s','ss-w-l','ss-w-m'];
  $offsets = ['', 'ss-drop', 'ss-rise', 'ss-drop', '', 'ss-rise', 'ss-drop'];
  $rots = ['ss-rot-a', '', '', 'ss-rot-b', 'ss-rot-a', '', 'ss-rot-b'];
@endphp

@if($menuPages->count() > 0)
<div class="ss-wrap">
  <nav class="ss-nav">
    <a href="{{ $site->urlFor(null, $preview) }}" class="{{ !$page ? 'ss-active' : '' }}">Home</a>
    @foreach($menuPages as $mp)
      <a href="{{ $site->urlFor($mp, $preview) }}" class="{{ ($page && $page->slug === $mp->slug) ? 'ss-active' : '' }}">{{ $mp->title }}</a>
    @endforeach
  </nav>
</div>
@endif

@if(!$page)
{{-- ============ HOME ============ --}}

<div class="ss-tabs">
  <button type="button" class="ss-tab" onclick="document.getElementById('ss-book').scrollIntoView({behavior:'smooth',block:'center'})">Book a Room</button>
  @if($site->booking_url)
    <a class="ss-tab" href="{{ $site->booking_url }}" target="_blank" rel="noopener" style="text-decoration:none">Book a Table</a>
  @endif
</div>

<header class="ss-wrap ss-masthead">
  @if($site->locationLabel())<div class="ss-eyebrow ss-mast-loc">{{ $site->locationLabel() }}</div>@endif
  <h1 class="ss-mast-name">{{ $site->name }}</h1>
  @if($site->tagline)<p class="ss-mast-tag">{{ $site->tagline }}</p>@endif
</header>

@if(count($galleryImgs) > 0)
<section class="ss-wrap ss-collage" aria-label="Gallery">
  @foreach($galleryImgs as $i => $img)
    <div class="ss-piece {{ $widths[$i % count($widths)] }} {{ $offsets[$i % count($offsets)] }}">
      <figure class="{{ $rots[$i % count($rots)] }}">
        <img src="{{ $img }}" loading="lazy" alt="{{ $site->name }}" onerror="this.style.display='none'">
      </figure>
      <div class="ss-cap">{{ $capBase[$i % count($capBase)] }}</div>
    </div>
  @endforeach
</section>
@endif

<main class="ss-wrap">

  <section class="ss-book" id="ss-book">
    <div class="ss-eyebrow ss-label">Check availability</div>
    <div class="ss-book-row">
      <label class="ss-field"><span>Arrive</span><input class="ss-input" type="date" id="ci"></label>
      <label class="ss-field"><span>Depart</span><input class="ss-input" type="date" id="co"></label>
      <label class="ss-field"><span>Guests</span>
        <select class="ss-input" id="ad">
          @for($i=1;$i<=8;$i++)<option value="{{ $i }}">{{ $i }} {{ \Illuminate\Support\Str::plural('guest',$i) }}</option>@endfor
        </select>
      </label>
      <button type="button" class="ss-btn" id="bookBtn" data-book="{{ $site->booking_url ?: $site->source_url }}">Book →</button>
    </div>
    @if($site->price_from)<div class="ss-from">from {{ $site->price_from }}</div>@endif
  </section>
  @include('sites.partials.booking-script')

  @if($site->description)
  <section class="ss-section">
    <div class="ss-eyebrow ss-label">About</div>
    <div class="ss-col">{!! nl2br(e($site->description)) !!}</div>
  </section>
  @endif

  @if($site->amenities)
  <section class="ss-section">
    <div class="ss-eyebrow ss-label">Amenities</div>
    <ul class="ss-amen">
      @foreach($site->amenities as $a)<li>{{ $a }}</li>@endforeach
    </ul>
  </section>
  @endif

  @if($site->mapQuery())
  <section class="ss-section">
    <div class="ss-eyebrow ss-label">Where</div>
    @if($site->address || $site->locationLabel())
      <p class="ss-col" style="margin-bottom:16px">{{ collect([$site->address,$site->city,$site->region,$site->country])->filter()->implode(', ') }}</p>
    @endif
    <div class="ss-map-frame">
      <iframe loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="https://www.google.com/maps?q={{ urlencode($site->mapQuery()) }}&output=embed"></iframe>
    </div>
  </section>
  @endif

  <section class="ss-section ss-contact">
    <div class="ss-eyebrow ss-label">Contact</div>
    @if($site->phone)<p>{{ $site->phone }}</p>@endif
    @if($site->email)<p><a href="mailto:{{ $site->email }}">{{ $site->email }}</a></p>@endif
    @if($site->address || $site->locationLabel())<p>{{ collect([$site->address,$site->city,$site->region,$site->country])->filter()->implode(', ') }}</p>@endif
  </section>

</main>

@else
{{-- ============ INTERNAL PAGE ============ --}}
<main class="ss-wrap">
  <section class="ss-section" style="border-top:none">
    <h1 class="ss-page-title">{{ $page->title }}</h1>
    <div class="ss-col">{!! nl2br(e($page->body)) !!}</div>

    <div class="ss-back">
      <a href="{{ $site->urlFor(null, $preview) }}">← Home</a>
      <a href="{{ $site->urlFor(null, $preview) }}#ss-book">Check availability</a>
    </div>
  </section>

  @if(!empty($page->images))
  <section class="ss-collage" aria-label="Images">
    @foreach($page->images as $i => $img)
      <div class="ss-piece {{ $widths[$i % count($widths)] }} {{ $offsets[$i % count($offsets)] }}">
        <figure class="{{ $rots[$i % count($rots)] }}">
          <img src="{{ $img }}" loading="lazy" alt="{{ $page->title }}" onerror="this.style.display='none'">
        </figure>
      </div>
    @endforeach
  </section>
  @endif
</main>
@endif

<footer class="ss-wrap ss-foot">
  <div class="ss-foot-name">{{ $site->name }}</div>
  <small>
    Presented by the Retro Motel Collective
    @if($indexable && ($site->booking_url || $site->source_url))
      · <a href="{{ $site->booking_url ?: $site->source_url }}" target="_blank" rel="noopener">Official website</a>
    @endif
  </small>
</footer>

</body>
</html>
