@extends('layouts.admin')
@section('title', 'Site Builder')
@section('content')
<style>
  .sb-head{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:16px}
  .sb-grid{display:grid;grid-template-columns:1fr;gap:14px}
  .sb-card{background:var(--paper,#fff);border-radius:14px;padding:16px 18px;box-shadow:var(--shadow,0 6px 20px rgba(0,0,0,.06));display:flex;gap:16px;align-items:center}
  .sb-thumb{width:96px;height:70px;border-radius:10px;background:#e9e2d4 center/cover no-repeat;flex:none}
  .sb-main{flex:1;min-width:0}
  .sb-main b{font-family:Oswald,sans-serif;font-size:17px}
  .sb-chip{display:inline-block;font-size:11px;font-weight:700;letter-spacing:.4px;padding:3px 9px;border-radius:20px;text-transform:uppercase}
  .sb-live{background:#dff3e6;color:#1c7a45}
  .sb-draft{background:#f1ece0;color:#8a7d68}
  .sb-links a{color:var(--teal-d,#2f6f7e);font-weight:600;text-decoration:none}
  .sb-meta{font-size:12.5px;color:#8a7d68}
  .sb-actions{display:flex;gap:8px;flex:none}
  .sb-btn{border:1px solid #e2d6c2;background:#fff;border-radius:9px;padding:8px 12px;font-size:13px;font-weight:600;color:#4a4453;text-decoration:none;cursor:pointer}
  .sb-btn.primary{background:var(--coral,#ee6a5a);border-color:var(--coral,#ee6a5a);color:#fff}
</style>

<div class="sb-head">
  <div class="prose"><p style="margin:0">Build a branded microsite for any motel from its existing URL — pick a theme, pull the content, then share a private password-protected preview or publish an indexable booking page.</p></div>
  <a class="sb-btn primary" href="{{ route('admin.sites.create') }}">+ New microsite</a>
</div>

<div class="sb-grid">
  @forelse($sites as $site)
    <div class="sb-card">
      <div class="sb-thumb" style="background-image:url('{{ $site->heroOrFirst() }}')"></div>
      <div class="sb-main">
        <b>{{ $site->name }}</b>
        @if($site->published)<span class="sb-chip sb-live">● Live</span>@else<span class="sb-chip sb-draft">Draft</span>@endif
        <div class="sb-meta">{{ $site->themeLabel() }} theme · {{ $site->locationLabel() ?: parse_url($site->source_url, PHP_URL_HOST) }} · {{ $site->preview_hits }} preview {{ Str::plural('access', $site->preview_hits) }}</div>
        <div class="sb-links" style="margin-top:6px;font-size:13px">
          <a href="{{ $site->previewUrl() }}" target="_blank">Preview ↗</a> <span style="color:#c9bda6">·</span> pwd <code>{{ $site->preview_password }}</code>
          @if($site->publicUrl()) <span style="color:#c9bda6">·</span> <a href="{{ $site->publicUrl() }}" target="_blank">Public page ↗</a>@endif
        </div>
      </div>
      <div class="sb-actions">
        <a class="sb-btn" href="{{ route('admin.sites.edit', $site) }}">Edit</a>
        <form method="POST" action="{{ route('admin.sites.destroy', $site) }}" onsubmit="return confirm('Delete this microsite?')">@csrf @method('DELETE')<button class="sb-btn" type="submit">Delete</button></form>
      </div>
    </div>
  @empty
    <div class="sb-card"><div class="sb-main"><span class="sb-meta">No microsites yet. Click “New microsite” to build one from a URL.</span></div></div>
  @endforelse
</div>
@endsection
