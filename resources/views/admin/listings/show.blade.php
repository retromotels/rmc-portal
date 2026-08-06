@extends('layouts.admin')
@section('title', 'Listing scorecard')
@section('content')
<style>
  .sc-top{display:grid;grid-template-columns:1.4fr 1fr;gap:16px;margin-bottom:18px}
  @media (max-width:820px){ .sc-top{grid-template-columns:1fr} }
  .panel{background:var(--paper,#fff);border-radius:14px;padding:18px 20px;box-shadow:var(--shadow,0 6px 20px rgba(0,0,0,.06))}
  .panel h3{font-family:Oswald,sans-serif;font-size:15px;margin:0 0 12px;letter-spacing:.3px}
  .meter{height:14px;border-radius:9px;background:#eee6d6;overflow:hidden;margin:8px 0 6px}
  .meter i{display:block;height:100%;border-radius:9px;background:linear-gradient(90deg,#ee6a5a,#f0a24a)}
  .big{font-family:Oswald,sans-serif;font-size:40px;font-weight:700;line-height:1}
  .pulled dt{font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#8a7d68;margin-top:8px}
  .pulled dd{margin:2px 0 0;font-size:14px;color:#2d2837}
  .warn{background:#fdf3e6;border:1px solid #f0d9b5;color:#8a5a1a;font-size:12.5px;padding:9px 12px;border-radius:9px;margin-top:10px}
  .fld input{width:100%;padding:9px 11px;border:1px solid #e2d6c2;border-radius:9px;font-size:14px}
  .grp{margin:22px 0 8px;font-family:Oswald,sans-serif;font-size:16px;border-bottom:2px solid #efe7d8;padding-bottom:6px}
  .row{display:flex;gap:14px;align-items:flex-start;padding:11px 0;border-bottom:1px solid #f2ebdd}
  .row .lab{flex:1;min-width:0}
  .row .lab b{font-weight:600;font-size:14.5px}
  .row .lab small{display:block;color:#8a7d68;font-size:12px;margin-top:2px}
  .auto{display:inline-block;font-size:10px;font-weight:700;letter-spacing:.3px;background:#dff3e6;color:#1c7a45;padding:1px 6px;border-radius:5px;margin-left:6px;vertical-align:middle}
  .seg{display:flex;flex:none}
  .seg input{position:absolute;opacity:0;pointer-events:none}
  .seg label{cursor:pointer;border:1px solid #e2d6c2;padding:6px 11px;font-size:12.5px;font-weight:600;color:#8a7d68;background:#fff}
  .seg label:first-of-type{border-radius:8px 0 0 8px}
  .seg label:last-of-type{border-radius:0 8px 8px 0;border-left:0}
  .seg label:nth-of-type(2){border-left:0}
  .seg .i-ok:checked + label{background:#1c7a45;border-color:#1c7a45;color:#fff}
  .seg .i-no:checked + label{background:#b23b2e;border-color:#b23b2e;color:#fff}
  .seg .i-na:checked + label{background:#8a7d68;border-color:#8a7d68;color:#fff}
  .note{flex:none;width:210px}
  .note input{width:100%;padding:6px 9px;border:1px solid #eadfca;border-radius:8px;font-size:12.5px}
  @media (max-width:820px){ .row{flex-wrap:wrap} .note{width:100%} }
  .save{background:var(--coral,#ee6a5a);color:#fff;border:none;border-radius:10px;padding:12px 24px;font-weight:700;font-family:Oswald,sans-serif;letter-spacing:.5px;cursor:pointer}
  .lite{border:1px solid #e2d6c2;background:#fff;border-radius:9px;padding:9px 14px;font-weight:600;font-size:13px;color:#4a4453;text-decoration:none;cursor:pointer}
  .bar{position:sticky;bottom:0;background:var(--paper,#fff);border-top:1px solid #efe7d8;padding:14px 0;display:flex;gap:12px;align-items:center;margin-top:10px}
</style>

@if(session('status'))<div class="status">{{ session('status') }}</div>@endif
@php $c = $audit->counts(); $p = $audit->pulled ?? []; @endphp

<div class="sc-top">
  <div class="panel">
    <h3>Score</h3>
    <div class="big">{{ $audit->score }}% <span style="font-size:16px;color:#8a7d68">· {{ $audit->ratingLabel() }}</span></div>
    <div class="meter"><i style="width:{{ $audit->score }}%"></i></div>
    <div style="font-size:12.5px;color:#8a7d68">{{ $c['ok'] }} done · {{ $c['no'] }} flagged · {{ $c['na'] }} n/a · {{ $c['todo'] }} to review · {{ $c['total'] }} total</div>
    <div style="margin-top:12px;font-size:12.5px"><a class="lite" href="{{ $audit->url }}" target="_blank" rel="noopener">Open listing ↗</a></div>
  </div>
  <div class="panel">
    <h3>What we pulled</h3>
    <dl class="pulled" style="margin:0">
      <dt>Property</dt><dd>{{ $p['name'] ?? '—' }}</dd>
      <dt>Photos detected</dt><dd>{{ $p['image_count'] ?? 0 }}</dd>
      <dt>Review score</dt><dd>@if(!empty($p['rating'])){{ $p['rating'] }}@if(!empty($p['review_count'])) ({{ number_format($p['review_count']) }} reviews)@endif @else — @endif</dd>
      <dt>Price</dt><dd>{{ $p['price'] ?? '—' }}</dd>
    </dl>
    @if(!empty($p['blocked']) || (isset($p['ok']) && !$p['ok']))
      <div class="warn">{{ $p['error'] ?? 'Booking.com blocked the automated read.' }} Complete the checklist manually below.</div>
    @endif
    <form method="POST" action="{{ route('admin.listings.reanalyze', $audit) }}" style="margin-top:12px">@csrf<button class="lite" type="submit">↻ Re-analyze</button></form>
  </div>
</div>

<form method="POST" action="{{ route('admin.listings.update', $audit) }}">
  @csrf @method('PUT')

  <div class="panel" style="margin-bottom:16px">
    <label class="fld"><span style="display:block;font-weight:600;font-size:12.5px;margin-bottom:5px">Property name</span>
      <input name="property_name" value="{{ $audit->property_name }}" placeholder="Property name">
    </label>
  </div>

  @foreach(config('rmc.listing_checklist') as $group => $items)
    <div class="grp">{{ $group }}</div>
    @foreach($items as $it)
      @php $key = $it['key']; $st = $audit->statusOf($key); $auto = $audit->noteOf($key) === 'Auto-detected from listing'; @endphp
      <div class="row">
        <div class="lab">
          <b>{{ $it['label'] }}</b>
          @if($auto)<span class="auto">AUTO ✓</span>@endif
          @isset($it['hint'])<small>{{ $it['hint'] }}</small>@endisset
        </div>
        <div class="seg">
          <input class="i-ok" type="radio" id="ok-{{ $key }}" name="status[{{ $key }}]" value="ok" @checked($st==='ok')>
          <label for="ok-{{ $key }}">Yes</label>
          <input class="i-no" type="radio" id="no-{{ $key }}" name="status[{{ $key }}]" value="no" @checked($st==='no')>
          <label for="no-{{ $key }}">No</label>
          <input class="i-na" type="radio" id="na-{{ $key }}" name="status[{{ $key }}]" value="na" @checked($st==='na')>
          <label for="na-{{ $key }}">N/A</label>
        </div>
        <div class="note"><input name="note[{{ $key }}]" value="{{ $audit->noteOf($key) === 'Auto-detected from listing' ? '' : $audit->noteOf($key) }}" placeholder="Note (optional)"></div>
      </div>
    @endforeach
  @endforeach

  <div class="bar">
    <button class="save" type="submit">Save scorecard</button>
    <span style="flex:1"></span>
    <form method="POST" action="{{ route('admin.listings.destroy', $audit) }}" onsubmit="return confirm('Delete this audit?')">@csrf @method('DELETE')<button class="lite" type="submit">Delete</button></form>
  </div>
</form>
@endsection
