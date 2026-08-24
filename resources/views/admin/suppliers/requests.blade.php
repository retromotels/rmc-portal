@extends('layouts.admin')
@section('title', 'Supplier requests')
@section('content')
<style>
  .rq-back{color:#6c6577;text-decoration:none;font-size:13.5px}
  .rq-h{font-family:Oswald,sans-serif;font-size:24px;margin:8px 0 16px}
  .rq{background:var(--paper,#fff);border-radius:12px;padding:15px 17px;box-shadow:var(--shadow,0 6px 20px rgba(0,0,0,.06));margin-bottom:11px}
  .rq-top{display:flex;justify-content:space-between;align-items:baseline;flex-wrap:wrap;gap:6px}
  .rq-sup{font-family:Oswald,sans-serif;font-size:16px}
  .rq-when{font-size:12px;color:#8a7d68}
  .rq-prop{font-size:13.5px;color:#33507a;margin:4px 0}
  .rq-msg{font-size:13.5px;line-height:1.55;white-space:pre-line;margin-top:8px;padding-top:8px;border-top:1px solid #efe4d2}
  .rq-empty{background:var(--paper,#fff);border-radius:12px;padding:30px;text-align:center;color:#8a7d68;box-shadow:var(--shadow,0 6px 20px rgba(0,0,0,.06))}
</style>

<a class="rq-back" href="{{ route('admin.suppliers') }}">← Suppliers</a>
<h1 class="rq-h">Supplier requests</h1>

@forelse($requests as $req)
  <div class="rq">
    <div class="rq-top">
      <span class="rq-sup">{{ $req->supplier?->name ?? 'Supplier removed' }}</span>
      <span class="rq-when">{{ $req->created_at?->format('j M Y, g:ia') }}</span>
    </div>
    <div class="rq-prop">{{ $req->property_name }}@if($req->contact_email) · <a href="mailto:{{ $req->contact_email }}" style="color:#2f6f76">{{ $req->contact_email }}</a>@endif</div>
    @if($req->message)<div class="rq-msg">{{ $req->message }}</div>@endif
  </div>
@empty
  <div class="rq-empty">No requests yet.</div>
@endforelse

@if($requests->hasPages())
  <div style="display:flex;gap:14px;align-items:center;justify-content:center;margin-top:18px">
    @if(!$requests->onFirstPage())<a href="{{ $requests->previousPageUrl() }}" style="text-decoration:none;color:#2f6f76;font-weight:700">← Prev</a>@endif
    <span style="font-size:13px;color:#8a7d68">Page {{ $requests->currentPage() }} of {{ $requests->lastPage() }}</span>
    @if($requests->hasMorePages())<a href="{{ $requests->nextPageUrl() }}" style="text-decoration:none;color:#2f6f76;font-weight:700">Next →</a>@endif
  </div>
@endif
@endsection
