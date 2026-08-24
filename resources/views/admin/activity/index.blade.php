@extends('layouts.admin')
@section('title', 'User Log')
@section('content')
<style>
  .ac-tbl{width:100%;border-collapse:collapse;font-size:13.5px;background:var(--paper,#fff);border-radius:12px;overflow:hidden;box-shadow:var(--shadow,0 6px 20px rgba(0,0,0,.06))}
  .ac-tbl th{text-align:left;padding:11px 14px;font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#8a7d68;background:#faf5ea}
  .ac-tbl td{padding:10px 14px;border-top:1px solid #f2ebdd;vertical-align:top}
  .ac-tbl tr:hover td{background:#fbf7ee}
  .pill{display:inline-block;font-size:11px;font-weight:700;padding:2px 8px;border-radius:6px;background:#eef4ee;color:#3a6b4a}
  .pill.email{background:#eef2fb;color:#33507a}
  .k{display:inline-block;font-size:10px;font-weight:800;letter-spacing:.4px;text-transform:uppercase;padding:2px 7px;border-radius:20px;background:#f0ece3;color:#8a7d68}
  .k.email{background:#fdeede;color:#9a6a10}
  .det{font-size:12px;color:#8a7d68;margin-top:2px}
</style>
<h1 style="font-family:Oswald,sans-serif;font-size:24px;margin:0 0 3px">User Log</h1>
<div class="prose"><p style="margin:0 0 14px;color:#8a7d68;font-size:14px">Property activity and every email the portal triggers — spot who's active, who might need a call, and confirm messages went out.</p></div>
<table class="ac-tbl">
  <thead><tr><th>When</th><th>Type</th><th>Who</th><th>Detail</th><th>IP</th></tr></thead>
  <tbody>
  @forelse($logs as $l)
    <tr>
      <td style="white-space:nowrap;color:#8a7d68">{{ $l->created_at?->format('j M, g:ia') }}</td>
      <td>@if(($l->kind ?? 'page') === 'email')<span class="k email">Email</span>@else<span class="k">Page</span>@endif</td>
      <td>{{ $l->user?->motel ?: $l->user?->name ?: ('email' === ($l->kind ?? '') ? 'System' : '—') }}</td>
      <td>
        <span class="pill {{ ($l->kind ?? '') === 'email' ? 'email' : '' }}">{{ $l->label }}</span>
        @if($l->detail)<div class="det">{{ $l->detail }}</div>@endif
      </td>
      <td style="color:#8a7d68">{{ $l->ip }}</td>
    </tr>
  @empty
    <tr><td colspan="5" style="color:#8a7d68;padding:18px 14px">Nothing recorded yet.</td></tr>
  @endforelse
  </tbody>
</table>
@endsection
