{{-- The property's own nav: Home + mirrored internal pages --}}
@php $pages = $site->menuPages(); @endphp
@if($pages->count())
<div class="site-nav">
  <a class="site-brand" href="{{ $site->urlFor(null, $preview) }}">{{ $site->name }}</a>
  <nav class="site-menu" id="siteMenu">
    <a href="{{ $site->urlFor(null, $preview) }}" class="{{ ($current ?? 'home') === 'home' ? 'active' : '' }}">Home</a>
    @foreach($pages as $mp)
      <a href="{{ $site->urlFor($mp, $preview) }}" class="{{ ($current ?? '') === $mp->slug ? 'active' : '' }}">{{ $mp->title }}</a>
    @endforeach
  </nav>
  <button class="site-burger" type="button" aria-label="Menu" onclick="document.getElementById('siteMenu').classList.toggle('open')">&#9776;</button>
</div>
@endif
