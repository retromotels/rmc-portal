@extends('layouts.portal')
@section('title', 'Resource Library')
@section('content')
<style>
  .dh1{font-family:Oswald,sans-serif;font-size:26px;margin:0 0 3px}
  .dsub{color:#8a7d68;font-size:14px;margin-bottom:20px}
  .dgrid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px}
  .dcard{background:#fff;border:1px solid #ece1cd;border-radius:14px;padding:18px 20px;box-shadow:0 6px 20px rgba(0,0,0,.05);display:flex;flex-direction:column}
  .dcat{font-size:11px;font-weight:800;letter-spacing:.5px;text-transform:uppercase;color:#2f6f76;margin-bottom:7px}
  .dtitle{font-family:Oswald,sans-serif;font-size:19px;margin:0 0 7px}
  .ddesc{font-size:13.5px;color:#6a6152;line-height:1.55;flex:1}
  .dopen{margin-top:14px;background:#2f6f76;color:#fff;border-radius:9px;padding:10px 16px;font-weight:700;text-decoration:none;text-align:center;font-family:Oswald,sans-serif;letter-spacing:.4px}
  .dempty{background:#fff;border:1px solid #ece1cd;border-radius:14px;padding:40px;text-align:center;color:#8a7d68}
</style>

<h1 class="dh1">Resource Library</h1>
<p class="dsub">Ready-to-use templates and SOPs, prefilled with your property's details. Open one, tweak it, and download a Word copy.</p>

<div class="dgrid">
  @forelse($documents as $doc)
    <div class="dcard">
      @if($doc->category)<div class="dcat">{{ $doc->category }}</div>@endif
      <h2 class="dtitle">{{ $doc->title }}</h2>
      <p class="ddesc">{{ $doc->description }}</p>
      <a class="dopen" href="{{ route('tools.documents.show', $doc) }}">Open &amp; personalise →</a>
    </div>
  @empty
    <div class="dempty">No documents yet — head office is preparing the library.</div>
  @endforelse
</div>
@endsection
