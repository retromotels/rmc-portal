@extends('layouts.admin')
@section('title', 'Usage · ' . $document->title)
@section('content')
<style>
  .sb-back{color:#6c6577;text-decoration:none;font-size:13.5px}
  .sb-h{font-family:Oswald,sans-serif;font-size:22px;margin:8px 0 3px}
  .sb-sub{font-size:13px;color:#8a7d68;margin-bottom:18px}
  .cards{display:flex;gap:14px;flex-wrap:wrap;margin-bottom:20px}
  .kc{background:var(--paper,#fff);border-radius:12px;padding:16px 20px;box-shadow:var(--shadow,0 6px 20px rgba(0,0,0,.06));min-width:130px}
  .kc .n{font-family:Oswald,sans-serif;font-size:28px}.kc .l{font-size:12px;color:#8a7d68}
  .sw{background:var(--paper,#fff);border-radius:13px;box-shadow:var(--shadow,0 6px 20px rgba(0,0,0,.06));overflow:hidden}
  table.st{width:100%;border-collapse:collapse;font-size:13.5px}
  table.st th{text-align:left;font-family:Oswald,sans-serif;font-weight:600;font-size:12px;letter-spacing:.5px;text-transform:uppercase;color:#8a7d68;padding:12px 14px;border-bottom:1px solid #efe4d2;background:#fbf6ec}
  table.st td{padding:11px 14px;border-bottom:1px solid #f2ead9}
</style>

<a class="sb-back" href="{{ route('admin.documents') }}">← Documents</a>
<h1 class="sb-h">{{ $document->title }}</h1>
<div class="sb-sub">Usage by property</div>

<div class="cards">
  <div class="kc"><div class="n">{{ $document->events->where('action', 'view')->count() }}</div><div class="l">Total opens</div></div>
  <div class="kc"><div class="n">{{ $document->events->where('action', 'download')->count() }}</div><div class="l">Total downloads</div></div>
  <div class="kc"><div class="n">{{ $byProp->count() }}</div><div class="l">Properties</div></div>
</div>

<div class="sw">
  <table class="st">
    <thead><tr><th>Property</th><th>Opens</th><th>Downloads</th><th>Last activity</th></tr></thead>
    <tbody>
      @forelse($byProp as $row)
        <tr>
          <td style="font-weight:700">{{ $row['name'] }}</td>
          <td>{{ $row['views'] }}</td>
          <td>{{ $row['downloads'] }}</td>
          <td style="color:#8a7d68">{{ $row['last']?->format('j M Y, g:ia') }}</td>
        </tr>
      @empty
        <tr><td colspan="4" style="padding:28px;text-align:center;color:#8a7d68">No activity yet.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
