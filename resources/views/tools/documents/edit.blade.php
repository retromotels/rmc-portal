@extends('layouts.portal')
@section('title', $document->title)
@section('content')
<style>
  .eb-top{display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:12px;margin-bottom:14px}
  .eb-back{color:#6c6577;text-decoration:none;font-size:13.5px}
  .eb-h{font-family:Oswald,sans-serif;font-size:24px;margin:6px 0 2px}
  .eb-sub{font-size:13px;color:#8a7d68}
  .eb-tools{display:flex;gap:6px;flex-wrap:wrap;background:#fff;border:1px solid #ece1cd;border-bottom:none;border-radius:12px 12px 0 0;padding:9px 12px}
  .eb-tools button{background:#f6efe2;border:1px solid #e2d6c2;border-radius:7px;min-width:34px;height:32px;padding:0 9px;font-weight:700;cursor:pointer;font-size:14px}
  .eb-tools button:hover{background:#efe4d0}
  .eb-doc{background:#fff;border:1px solid #ece1cd;border-radius:0 0 12px 12px;padding:28px 32px;min-height:440px;max-width:820px;box-shadow:0 8px 26px rgba(0,0,0,.06);outline:none;font-size:15px;line-height:1.6;color:#2a2530}
  .eb-doc h1{font-size:24px;margin:0 0 10px}.eb-doc h2{font-size:18px;margin:18px 0 6px}
  .eb-doc ol,.eb-doc ul{margin:6px 0 10px 22px}
  .eb-actions{display:flex;gap:10px;margin-top:14px;flex-wrap:wrap}
  .eb-dl{background:#2e8b57;color:#fff;border:none;border-radius:9px;padding:12px 22px;font-weight:700;cursor:pointer;font-family:Oswald,sans-serif;letter-spacing:.5px;font-size:14px}
  .eb-note{font-size:12.5px;color:#8a7d68;margin-top:10px}
</style>

<div class="eb-top">
  <div>
    <a class="eb-back" href="{{ route('tools.documents') }}">← Documents</a>
    <h1 class="eb-h">{{ $document->title }}</h1>
    <div class="eb-sub">Personalised for <strong>{{ $currentProperty->motel ?: $currentProperty->name }}</strong>. Edit below, then download.</div>
  </div>
</div>

<div class="eb-tools">
  <button type="button" onmousedown="event.preventDefault()" onclick="fmt('bold')" title="Bold"><b>B</b></button>
  <button type="button" onmousedown="event.preventDefault()" onclick="fmt('italic')" title="Italic"><i>I</i></button>
  <button type="button" onmousedown="event.preventDefault()" onclick="fmt('underline')" title="Underline"><u>U</u></button>
  <button type="button" onmousedown="event.preventDefault()" onclick="fmt('formatBlock','H2')" title="Heading">H</button>
  <button type="button" onmousedown="event.preventDefault()" onclick="fmt('insertUnorderedList')" title="Bulleted list">•</button>
  <button type="button" onmousedown="event.preventDefault()" onclick="fmt('insertOrderedList')" title="Numbered list">1.</button>
  <button type="button" onmousedown="event.preventDefault()" onclick="fmt('removeFormat')" title="Clear formatting">✕</button>
</div>
<div class="eb-doc" id="editor" contenteditable="true">{!! $content !!}</div>

<form class="eb-actions" method="POST" action="{{ route('tools.documents.download', $document) }}" onsubmit="document.getElementById('payload').value = document.getElementById('editor').innerHTML">
  @csrf
  <input type="hidden" name="content" id="payload">
  <button class="eb-dl" type="submit">⬇ Download Word document</button>
</form>
<p class="eb-note">Downloads open in Microsoft Word or Google Docs. Your edits here aren't saved on the server — download to keep your copy.</p>

<script>
  function fmt(cmd, val){ document.execCommand(cmd, false, val || null); document.getElementById('editor').focus(); }
</script>
@endsection
