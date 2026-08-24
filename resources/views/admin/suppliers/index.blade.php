@extends('layouts.admin')
@section('title', 'Resource Library')
@section('content')
<style>
  .sh{display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:12px;margin-bottom:16px}
  .sh h1{font-family:Oswald,sans-serif;font-size:24px;margin:0}
  .sadd{background:#e0491d;color:#fff;border-radius:9px;padding:9px 16px;font-weight:700;text-decoration:none;font-family:Oswald,sans-serif;letter-spacing:.4px}
  .sreq{background:#fff;border:1px solid #e2d6c2;color:#33507a;border-radius:9px;padding:9px 14px;font-weight:700;text-decoration:none;margin-right:8px}
  .swrap{background:var(--paper,#fff);border-radius:13px;box-shadow:var(--shadow,0 6px 20px rgba(0,0,0,.06));overflow:hidden}
  table.st{width:100%;border-collapse:collapse;font-size:13.5px}
  table.st th{text-align:left;font-family:Oswald,sans-serif;font-weight:600;font-size:12px;letter-spacing:.5px;text-transform:uppercase;color:#8a7d68;padding:12px 14px;border-bottom:1px solid #efe4d2;background:#fbf6ec}
  table.st td{padding:12px 14px;border-bottom:1px solid #f2ead9;vertical-align:middle}
  .s-name{font-weight:700;color:#3a3540}
  .s-type{font-size:11px;font-weight:700;padding:2px 9px;border-radius:20px;background:#eef3f3;color:#2f6f76}
  .flag{font-size:11px;font-weight:700;padding:2px 8px;border-radius:20px}.on{background:#dff3e6;color:#2e7d4f}.off{background:#eee;color:#777}
  .lk{color:#33507a;text-decoration:none;font-weight:700;font-size:12.5px}
</style>

@if(session('status'))<div class="status">{{ session('status') }}</div>@endif

<div class="sh">
  <div>
    <h1>Resource Library</h1>
    <div style="font-size:13px;color:#8a7d68;margin-top:3px">Curate the supplier offers and resources members see.</div>
  </div>
  <div>
    <a class="sreq" href="{{ route('admin.suppliers.requests') }}">Requests @if($openRequests)<span style="color:#a4283a">({{ $openRequests }})</span>@endif</a>
    <a class="sadd" href="{{ route('admin.suppliers.create') }}">+ New supplier</a>
  </div>
</div>

<div class="swrap">
  <table class="st">
    <thead><tr><th>Supplier</th><th>Category</th><th>Offer</th><th>Status</th><th>Saved</th><th>Requests</th><th></th></tr></thead>
    <tbody>
      @forelse($suppliers as $s)
        <tr>
          <td class="s-name">{{ $s->name }}</td>
          <td>{{ $s->categoryLabel() }}</td>
          <td><span class="s-type">{{ config('rmc.supplier_offer_types.'.$s->offer_type, $s->offer_type) }}</span></td>
          <td><span class="flag {{ $s->is_active ? 'on' : 'off' }}">{{ $s->is_active ? 'Active' : 'Hidden' }}</span></td>
          <td>{{ $s->saves_count }}</td>
          <td>{{ $s->requests_count }}</td>
          <td><a class="lk" href="{{ route('admin.suppliers.edit', $s) }}">Edit</a></td>
        </tr>
      @empty
        <tr><td colspan="7" style="padding:30px;text-align:center;color:#8a7d68">No suppliers yet. Add your first offer.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
