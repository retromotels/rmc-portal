@extends('layouts.admin')
@section('title', 'Activity')
@section('content')
<style>
  .ac-tbl{width:100%;border-collapse:collapse;font-size:13.5px;background:var(--paper,#fff);border-radius:12px;overflow:hidden;box-shadow:var(--shadow,0 6px 20px rgba(0,0,0,.06))}
  .ac-tbl th{text-align:left;padding:11px 14px;font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#8a7d68;background:#faf5ea}
  .ac-tbl td{padding:10px 14px;border-top:1px solid #f2ebdd}
  .ac-tbl tr:hover td{background:#fbf7ee}
  .pill{display:inline-block;font-size:11px;font-weight:700;padding:2px 8px;border-radius:6px;background:#eef4ee;color:#3a6b4a}
</style>
<div class="prose"><p style="margin:0 0 14px">When properties log in and what they’re looking at. Use it to spot who’s active and who might need a call.</p></div>
<table class="ac-tbl">
  <thead><tr><th>When</th><th>Property</th><th>Activity</th><th>IP</th></tr></thead>
  <tbody>
  @forelse($logs as $l)
    <tr>
      <td style="white-space:nowrap;color:#8a7d68">{{ $l->created_at?->format('j M, g:ia') }}</td>
      <td>{{ $l->user?->motel ?: $l->user?->name ?: '—' }}</td>
      <td><span class="pill">{{ $l->label }}</span></td>
      <td style="color:#8a7d68">{{ $l->ip }}</td>
    </tr>
  @empty
    <tr><td colspan="4" style="color:#8a7d68;padding:18px 14px">No activity recorded yet.</td></tr>
  @endforelse
  </tbody>
</table>
@endsection
