@extends('layouts.admin')
@section('title', 'Listing Check')
@section('content')
<style>
  .lc-head{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:16px}
  .lc-btn{border:1px solid #e2d6c2;background:#fff;border-radius:9px;padding:9px 14px;font-size:13px;font-weight:600;color:#4a4453;text-decoration:none;cursor:pointer}
  .lc-btn.primary{background:var(--coral,#ee6a5a);border-color:var(--coral,#ee6a5a);color:#fff}
  .lc-card{background:var(--paper,#fff);border-radius:13px;padding:15px 18px;box-shadow:var(--shadow,0 6px 20px rgba(0,0,0,.06));display:flex;gap:16px;align-items:center;margin-bottom:12px}
  .lc-main{flex:1;min-width:0}
  .lc-main b{font-family:Oswald,sans-serif;font-size:16px}
  .lc-url{font-size:12.5px;color:#8a7d68;word-break:break-all}
  .lc-score{flex:none;text-align:center;min-width:74px}
  .lc-num{font-family:Oswald,sans-serif;font-size:26px;font-weight:700;line-height:1}
  .lc-lab{font-size:11px;font-weight:700;letter-spacing:.4px;text-transform:uppercase}
  .s-hi{color:#1c7a45}.s-mid{color:#c07f16}.s-lo{color:#b23b2e}
</style>

<div class="lc-head">
  <div class="prose"><p style="margin:0">Paste a Booking.com property URL and get a best-practice checklist to bring the listing up to standard. We auto-tick what we can read; you verify the rest. A score tracks how complete each listing is.</p></div>
  <a class="lc-btn primary" href="{{ route('admin.listings.create') }}">+ Check a listing</a>
</div>

@if(session('status'))<div class="status">{{ session('status') }}</div>@endif

@forelse($audits as $a)
  @php $cls = $a->score >= 75 ? 's-hi' : ($a->score >= 50 ? 's-mid' : 's-lo'); @endphp
  <div class="lc-card">
    <div class="lc-score">
      <div class="lc-num {{ $cls }}">{{ $a->score }}<span style="font-size:13px">%</span></div>
      <div class="lc-lab {{ $cls }}">{{ $a->ratingLabel() }}</div>
    </div>
    <div class="lc-main">
      <b>{{ $a->property_name ?: 'Booking listing' }}</b>
      @if($a->user) <span style="font-size:12px;color:#8a7d68">· {{ $a->user->motel ?: $a->user->name }}</span>@endif
      <div class="lc-url">{{ $a->url }}</div>
      <div style="font-size:12px;color:#8a7d68;margin-top:3px">{{ $a->counts()['ok'] }} done · {{ $a->counts()['todo'] }} to review · updated {{ $a->updated_at?->diffForHumans() }}</div>
    </div>
    <a class="lc-btn" href="{{ route('admin.listings.show', $a) }}">Open</a>
    <form method="POST" action="{{ route('admin.listings.destroy', $a) }}" onsubmit="return confirm('Delete this audit?')">@csrf @method('DELETE')<button class="lc-btn" type="submit">Delete</button></form>
  </div>
@empty
  <div class="lc-card"><div class="lc-main"><span style="color:#8a7d68;font-size:13px">No listings checked yet. Click “Check a listing”.</span></div></div>
@endforelse
@endsection
