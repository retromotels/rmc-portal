<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $page->title }} · {{ $site->name }}</title>
@if($indexable)
  <meta name="description" content="{{ Str::limit(strip_tags($page->body ?? $site->name), 155) }}">
  <link rel="canonical" href="{{ $site->urlFor($page, false) }}">
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

@if($indexable)
  @include('sites.partials.rmc-header')
@endif
@include('sites.partials.site-nav', ['current' => $page->slug])

@php $ph = collect($page->images ?? [])->first() ?: $site->heroOrFirst(); @endphp
<header class="page-hero" @if($ph)style="background-image:linear-gradient(180deg,rgba(0,0,0,.25),rgba(0,0,0,.6)),url('{{ $ph }}')"@endif>
  <div class="page-hero-inner">
    <div class="eyebrow"><a href="{{ $site->urlFor(null, $preview) }}">{{ $site->name }}</a></div>
    <h1>{{ $page->title }}</h1>
  </div>
</header>

<main>
  @if($page->body)
    <section class="page-body"><div class="rich">{!! nl2br(e($page->body)) !!}</div></section>
  @endif

  @php $imgs = collect($page->images ?? [])->slice($page->body ? 1 : 0)->values(); @endphp
  @if($imgs->count())
  <section class="gallery">
    <div class="grid">
      @foreach($imgs as $img)
        <figure><img src="{{ $img }}" loading="lazy" alt="{{ $page->title }}" onerror="this.closest('figure').remove()"></figure>
      @endforeach
    </div>
  </section>
  @endif

  <section class="cta">
    <h2>Ready to book your stay?</h2>
    <a class="cta-btn" href="{{ $site->urlFor(null, $preview) }}#book">Check availability</a>
  </section>
</main>

<footer class="foot">
  <div>{{ $site->name }}</div>
  <small>Presented by the Retro Motel Collective @if($indexable && ($site->booking_url || $site->source_url))· <a href="{{ $site->booking_url ?: $site->source_url }}" target="_blank" rel="noopener">Official website</a>@endif</small>
</footer>

</body>
</html>
