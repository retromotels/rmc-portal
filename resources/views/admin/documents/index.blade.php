@extends('layouts.admin')
@section('title', 'Resource Library')
@section('content')
<style>
  .dh{display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:12px;margin-bottom:16px}
  .dh h1{font-family:Oswald,sans-serif;font-size:24px;margin:0}
  .dadd{background:#e0491d;color:#fff;border-radius:9px;padding:9px 16px;font-weight:700;text-decoration:none;font-family:Oswald,sans-serif;letter-spacing:.4px}
  .dwrap{background:var(--paper,#fff);border-radius:13px;box-shadow:var(--shadow,0 6px 20px rgba(0,0,0,.06));overflow:hidden}
  table.dt{width:100%;border-collapse:collapse;font-size:13.5px}
  table.dt th{text-align:left;font-family:Oswald,sans-serif;font-weight:600;font-size:12px;letter-spacing:.5px;text-transform:uppercase;color:#8a7d68;padding:12px 14px;border-bottom:1px solid #efe4d2;background:#fbf6ec}
  table.dt td{padding:12px 14px;border-bottom:1px solid #f2ead9;vertical-align:middle}
  .d-title{font-weight:700;color:#3a3540}
  .d-cat{font-size:11px;color:#2f6f76;font-weight:700}
  .metric{font-weight:800}
  .d-links a{color:#33507a;text-decoration:none;font-weight:700;font-size:12.5px;margin-right:10px}
  .flag{font-size:11px;font-weight:700;padding:2px 8px;border-radius:20px}
  .on{background:#dff3e6;color:#2e7d4f}.off{background:#eee;color:#777}
</style>

@if(session('status'))<div class="status">{{ session('status') }}</div>@endif

<div class="dh">
  <div>
    <h1>Resource Library</h1>
    <div style="font-size:13px;color:#8a7d68;margin-top:3px">SOP templates and resources members can personalise and download. Track which perform best.</div>
  </div>
  <a class="dadd" href="{{ route('admin.documents.create') }}">+ New document</a>
</div>

<div class="dwrap">
  <table class="dt">
    <thead><tr><th>Title</th><th>Category</th><th>Status</th><th>Views</th><th>Downloads</th><th></th></tr></thead>
    <tbody>
      @forelse($documents as $doc)
        <tr>
          <td class="d-title">{{ $doc->title }}</td>
          <td class="d-cat">{{ $doc->category ?: '—' }}</td>
          <td><span class="flag {{ $doc->is_published ? 'on' : 'off' }}">{{ $doc->is_published ? 'Published' : 'Draft' }}</span></td>
          <td class="metric">{{ $views[$doc->id] ?? 0 }}</td>
          <td class="metric">{{ $downloads[$doc->id] ?? 0 }}</td>
          <td class="d-links" style="white-space:nowrap">
            <a href="{{ route('admin.documents.stats', $doc) }}">Usage</a>
            <a href="{{ route('admin.documents.edit', $doc) }}">Edit</a>
          </td>
        </tr>
      @empty
        <tr><td colspan="6" style="padding:30px;text-align:center;color:#8a7d68">No documents yet. Add your first template.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
