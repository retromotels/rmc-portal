@extends('layouts.portal')
@section('title', $supplier->name)
@section('content')
<style>
  .vb-back{color:#6c6577;text-decoration:none;font-size:13.5px}
  .vb-wrap{max-width:720px}
  .vb-head{display:flex;gap:16px;align-items:center;margin:12px 0 6px}
  .vb-tile{width:56px;height:56px;border-radius:13px;display:grid;place-items:center;font-family:Oswald,sans-serif;font-weight:700;font-size:26px;color:#fff;background:#2f6f76}
  .vb-cat{font-size:11.5px;font-weight:800;letter-spacing:.4px;text-transform:uppercase;color:#2f6f76}
  .vb-name{font-family:Oswald,sans-serif;font-size:26px;margin:0}
  .vb-card{background:#fff;border:1px solid #ece1cd;border-radius:14px;padding:22px;box-shadow:0 6px 20px rgba(0,0,0,.05);margin-top:14px}
  .vb-desc{font-size:14.5px;line-height:1.6;color:#3a3540;white-space:pre-line}
  .vb-offer{background:#fbf6ec;border:1px dashed #d8c7a4;border-radius:12px;padding:16px 18px;margin-top:16px}
  .vb-oh{font-family:Oswald,sans-serif;font-size:16px;margin:0 0 8px}
  .code{display:inline-flex;align-items:center;gap:10px;background:#fff;border:1.5px solid #2f6f76;border-radius:9px;padding:10px 14px;font-family:ui-monospace,monospace;font-weight:700;font-size:16px;letter-spacing:1px;color:#22555a}
  .btn-go{display:inline-block;background:#2f6f76;color:#fff;border-radius:9px;padding:11px 20px;font-weight:700;text-decoration:none;font-family:Oswald,sans-serif;letter-spacing:.4px}
  .req textarea{width:100%;padding:11px 13px;border:1.5px solid #e2d6c2;border-radius:9px;font:inherit;font-size:14px;min-height:90px;box-sizing:border-box;margin-bottom:10px}
  .btn-req{background:#e0491d;color:#fff;border:none;border-radius:9px;padding:11px 20px;font-weight:700;cursor:pointer;font-family:Oswald,sans-serif;letter-spacing:.4px}
  .vb-terms{font-size:12.5px;color:#8a7d68;margin-top:14px;white-space:pre-line}
  .vb-save{background:none;border:1px solid #e2d6c2;border-radius:9px;padding:9px 14px;font-weight:700;cursor:pointer;font-size:13px}
  .muted{color:#8a7d68;font-size:12.5px}
</style>

@if(session('status'))<div class="status">{{ session('status') }}</div>@endif

<div class="vb-wrap">
  <a class="vb-back" href="{{ route('tools.suppliers') }}">← Suppliers</a>
  <div class="vb-head">
    <div class="vb-tile">{{ strtoupper(substr($supplier->name, 0, 1)) }}</div>
    <div>
      <div class="vb-cat">{{ $supplier->categoryLabel() }}</div>
      <h1 class="vb-name">{{ $supplier->name }}</h1>
    </div>
    <form method="POST" action="{{ route('tools.suppliers.save', $supplier) }}" style="margin-left:auto">@csrf
      <button class="vb-save" type="submit">{{ $isSaved ? '★ Saved' : '☆ Save' }}</button>
    </form>
  </div>

  <div class="vb-card">
    @if($supplier->description)<div class="vb-desc">{{ $supplier->description }}</div>@endif

    <div class="vb-offer">
      @if($supplier->offer_headline)<div class="vb-oh">{{ $supplier->offer_headline }}</div>@endif

      @if($supplier->offer_type === 'code' && $supplier->discount_code)
        <p class="muted" style="margin-bottom:8px">Use this code with {{ $supplier->name }}:</p>
        <span class="code">{{ $supplier->discount_code }}</span>
        @if($supplier->link_url)<div style="margin-top:12px"><a class="btn-go" href="{{ $supplier->link_url }}" target="_blank" rel="noopener">{{ $supplier->link_label ?: 'Go to offer' }} →</a></div>@endif

      @elseif($supplier->offer_type === 'link' && $supplier->link_url)
        <a class="btn-go" href="{{ $supplier->link_url }}" target="_blank" rel="noopener">{{ $supplier->link_label ?: 'Open offer' }} →</a>

      @else
        <p class="muted" style="margin-bottom:10px">Request this offer and head office will set it up on your behalf.</p>
        <form class="req" method="POST" action="{{ route('tools.suppliers.request', $supplier) }}">@csrf
          <textarea name="message" placeholder="Anything to add? (optional)"></textarea>
          <button class="btn-req" type="submit">Request via head office</button>
        </form>
      @endif
    </div>

    @if($supplier->terms)<div class="vb-terms"><strong>Terms:</strong> {{ $supplier->terms }}</div>@endif
    @if($supplier->website)<div style="margin-top:12px"><a class="muted" href="{{ $supplier->website }}" target="_blank" rel="noopener">Visit website ↗</a></div>@endif
  </div>
</div>
@endsection
