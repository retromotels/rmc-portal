@extends('layouts.portal')
@section('title', 'Resource Library')
@section('content')
<style>
  .sh1{font-family:Oswald,sans-serif;font-size:26px;margin:0 0 3px}
  .ssub{color:#8a7d68;font-size:14px;margin-bottom:18px}
  .sfilters{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px}
  .schip{padding:8px 14px;border-radius:20px;border:1px solid #e2d6c2;background:#fff;font-size:13px;font-weight:600;text-decoration:none;color:#4a4453}
  .schip.on{background:#2f6f76;color:#fff;border-color:#2f6f76}
  .sgrid{display:grid;grid-template-columns:repeat(auto-fill,minmax(290px,1fr));gap:16px}
  .scard{background:#fff;border:1px solid #ece1cd;border-radius:14px;padding:18px 20px;box-shadow:0 6px 20px rgba(0,0,0,.05);display:flex;flex-direction:column;position:relative}
  .stile{width:44px;height:44px;border-radius:11px;display:grid;place-items:center;font-family:Oswald,sans-serif;font-weight:700;font-size:20px;color:#fff;background:#2f6f76;margin-bottom:11px}
  .scat{font-size:11px;font-weight:800;letter-spacing:.4px;text-transform:uppercase;color:#2f6f76;margin-bottom:5px}
  .sname{font-family:Oswald,sans-serif;font-size:18px;margin:0 0 6px}
  .soffer{display:inline-block;font-size:11.5px;font-weight:700;background:#fdf0d5;color:#9a6a10;border-radius:20px;padding:3px 10px;margin-bottom:8px}
  .ssum{font-size:13.5px;color:#6a6152;line-height:1.55;flex:1}
  .sopen{margin-top:13px;background:#2f6f76;color:#fff;border-radius:9px;padding:10px 16px;font-weight:700;text-decoration:none;text-align:center;font-family:Oswald,sans-serif;letter-spacing:.4px}
  .ssave{position:absolute;top:14px;right:14px;background:none;border:none;cursor:pointer;font-size:19px;line-height:1}
  .sempty{background:#fff;border:1px solid #ece1cd;border-radius:14px;padding:40px;text-align:center;color:#8a7d68}
</style>

@if(session('status'))<div class="status">{{ session('status') }}</div>@endif

<h1 class="sh1">Resource Library</h1>
<p class="ssub">Deals, offers and supplier resources negotiated for collective members. Save the ones you want, grab a code or link, or ask head office to set it up for you.</p>

<div class="sfilters">
  <a class="schip {{ !$category && !$saved ? 'on' : '' }}" href="{{ route('tools.suppliers') }}">All</a>
  <a class="schip {{ $saved ? 'on' : '' }}" href="{{ route('tools.suppliers', ['saved' => 1]) }}">★ Saved</a>
  @foreach(config('rmc.supplier_categories') as $k => $lbl)
    @if(($counts[$k] ?? 0) > 0)
      <a class="schip {{ $category === $k ? 'on' : '' }}" href="{{ route('tools.suppliers', ['category' => $k]) }}">{{ $lbl }}</a>
    @endif
  @endforeach
</div>

<div class="sgrid">
  @forelse($suppliers as $s)
    <div class="scard">
      <form method="POST" action="{{ route('tools.suppliers.save', $s) }}">@csrf<button class="ssave" type="submit" title="{{ $savedIds->contains($s->id) ? 'Saved' : 'Save' }}">{{ $savedIds->contains($s->id) ? '★' : '☆' }}</button></form>
      <div class="stile">{{ strtoupper(substr($s->name, 0, 1)) }}</div>
      <div class="scat">{{ $s->categoryLabel() }}</div>
      <h2 class="sname">{{ $s->name }}</h2>
      @if($s->offer_headline)<span class="soffer">{{ $s->offer_headline }}</span>@endif
      <p class="ssum">{{ $s->summary }}</p>
      <a class="sopen" href="{{ route('tools.suppliers.show', $s) }}">View offer →</a>
    </div>
  @empty
    <div class="sempty">@if($saved)You haven't saved any suppliers yet.@else No suppliers listed yet.@endif</div>
  @endforelse
</div>
@endsection
