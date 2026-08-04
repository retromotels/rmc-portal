@extends('layouts.admin')
@section('title', 'Edit page — ' . $page->title)
@section('content')
<style>
  .panel{background:var(--paper,#fff);border-radius:14px;padding:18px 20px;box-shadow:var(--shadow,0 6px 20px rgba(0,0,0,.06));max-width:900px;margin-bottom:16px}
  .fld{display:block;margin-bottom:12px}
  .fld span{display:block;font-weight:600;font-size:12.5px;margin-bottom:5px;color:#4a4453}
  .fld input,.fld textarea{width:100%;padding:10px 11px;border:1px solid #e2d6c2;border-radius:9px;font-size:14px;font-family:inherit}
  .fld textarea{resize:vertical}
  .row2{display:grid;grid-template-columns:2fr 1fr;gap:12px}
  .save{background:var(--coral,#ee6a5a);color:#fff;border:none;border-radius:10px;padding:12px 24px;font-weight:700;font-family:Oswald,sans-serif;letter-spacing:.5px;cursor:pointer}
  .lite{border:1px solid #e2d6c2;background:#fff;border-radius:9px;padding:9px 14px;font-weight:600;font-size:13px;color:#4a4453;text-decoration:none}
  .switch{display:inline-flex;align-items:center;gap:8px;font-weight:600;font-size:13px}
</style>

@if(session('status'))<div class="status">{{ session('status') }}</div>@endif
@foreach($errors->all() as $e)<div class="status" style="background:#fdeceb;color:#b23b2e">{{ $e }}</div>@endforeach

<p style="margin:0 0 14px"><a class="lite" href="{{ route('admin.sites.edit', $site) }}">← Back to {{ $site->name }}</a></p>

<form method="POST" action="{{ route('admin.sites.page.update', [$site, $page]) }}">
  @csrf @method('PUT')
  <div class="panel">
    <div class="row2">
      <label class="fld"><span>Page title (also the nav label)</span><input name="title" value="{{ old('title', $page->title) }}" required></label>
      <label class="fld"><span>Nav order</span><input name="nav_order" type="number" value="{{ old('nav_order', $page->nav_order) }}"></label>
    </div>
    <div style="font-size:12.5px;color:#8a7d68;margin-bottom:12px">URL: <code>/motel/&lt;site&gt;/{{ $page->slug }}</code> @if($page->source_url)· mirrored from <a href="{{ $page->source_url }}" target="_blank">{{ $page->source_url }}</a>@endif</div>
    <label class="fld"><span>Content</span><textarea name="body" rows="16">{{ old('body', $page->body) }}</textarea></label>
    <label class="fld"><span>Image URLs (one per line)</span><textarea name="images_text" rows="5">{{ old('images_text', implode("\n", $page->images ?? [])) }}</textarea></label>
    <label class="switch"><input type="checkbox" name="visible" value="1" @checked($page->visible)> Show this page in the site menu</label>
  </div>
  <div class="panel" style="display:flex;gap:12px;align-items:center">
    <button class="save" type="submit">Save page</button>
    <a class="lite" href="{{ $site->urlFor($page, true) }}" target="_blank">Open in preview ↗</a>
  </div>
</form>
@endsection
