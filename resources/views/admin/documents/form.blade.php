@extends('layouts.admin')
@section('title', $document->exists ? 'Edit document' : 'New document')
@section('content')
<style>
  .fb{max-width:820px}
  .fb-back{color:#6c6577;text-decoration:none;font-size:13.5px}
  .fb-h{font-family:Oswald,sans-serif;font-size:24px;margin:8px 0 16px}
  .fb-card{background:var(--paper,#fff);border-radius:13px;padding:22px;box-shadow:var(--shadow,0 6px 20px rgba(0,0,0,.06))}
  .fld{display:block;margin-bottom:15px}
  .fld > span{display:block;font-size:12.5px;font-weight:700;margin-bottom:5px;color:#4a4453}
  .fld input,.fld textarea{width:100%;padding:11px 13px;border:1.5px solid #e2d6c2;border-radius:9px;font:inherit;font-size:14.5px;background:#fff;box-sizing:border-box}
  .fld textarea.body{min-height:320px;font-family:ui-monospace,Menlo,Consolas,monospace;font-size:13px;line-height:1.5}
  .row{display:flex;gap:14px;flex-wrap:wrap}.row .fld{flex:1;min-width:170px}
  .ph{background:#fbf6ec;border:1px solid #efe4d2;border-radius:9px;padding:12px 14px;font-size:12.5px;color:#6a6152;margin-bottom:15px}
  .ph code{background:#efe4d2;border-radius:4px;padding:1px 5px;font-size:12px}
  .fb-err{background:#fbe4e4;color:#a4283a;border-radius:9px;padding:10px 12px;font-size:13px;margin-bottom:14px}
  .fb-save{background:#2e8b57;color:#fff;border:none;border-radius:9px;padding:12px 24px;font-weight:700;cursor:pointer;font-family:Oswald,sans-serif;letter-spacing:.5px;font-size:14px}
  .fb-del{background:none;border:1px solid #f0c2c8;color:#a4283a;border-radius:9px;padding:11px 16px;font-weight:700;cursor:pointer;margin-left:auto}
  .chk{display:flex;align-items:center;gap:8px;font-size:14px;font-weight:600;color:#4a4453}
</style>

<div class="fb">
  <a class="fb-back" href="{{ route('admin.documents') }}">← Documents</a>
  <h1 class="fb-h">{{ $document->exists ? 'Edit document' : 'New document' }}</h1>

  <div class="fb-card">
    @if($errors->any())<div class="fb-err">{{ $errors->first() }}</div>@endif

    <div class="ph">
      <strong>Placeholders</strong> — type these in the body and they prefill with each property's details:
      @foreach(\App\Models\Document::placeholders() as $key => $lbl)<code>{{ '{{'.$key.'}}' }}</code> @endforeach
    </div>

    <form method="POST" action="{{ $document->exists ? route('admin.documents.update', $document) : route('admin.documents.store') }}">
      @csrf
      @if($document->exists)@method('PUT')@endif
      <label class="fld"><span>Title</span><input type="text" name="title" value="{{ old('title', $document->title) }}" required></label>
      <div class="row">
        <label class="fld"><span>Category</span><input type="text" name="category" value="{{ old('category', $document->category) }}" placeholder="e.g. Front Office"></label>
        <label class="fld"><span>Sort order</span><input type="number" name="sort" value="{{ old('sort', $document->sort ?? 0) }}" min="0"></label>
      </div>
      <label class="fld"><span>Short description</span><input type="text" name="description" value="{{ old('description', $document->description) }}" maxlength="400"></label>
      <label class="fld"><span>Body (HTML)</span><textarea class="body" name="body" required>{{ old('body', $document->body) }}</textarea></label>
      <label class="chk" style="margin-bottom:16px"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $document->is_published ?? true))> Published (visible to members)</label>

      <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
        <button class="fb-save" type="submit">{{ $document->exists ? 'Save changes' : 'Create document' }}</button>
        @if($document->exists)
          <button class="fb-del" type="submit" form="del-form" onclick="return confirm('Delete this document permanently?')">Delete</button>
        @endif
      </div>
    </form>
    @if($document->exists)
      <form id="del-form" method="POST" action="{{ route('admin.documents.destroy', $document) }}">@csrf @method('DELETE')</form>
    @endif
  </div>
</div>
@endsection
