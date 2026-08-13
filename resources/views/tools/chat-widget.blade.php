@extends('layouts.portal')
@section('title', 'Chat Widget')
@section('content')
@php $cfg = $w->config ?? []; $entries = $cfg['entries'] ?? []; @endphp
<style>
  .cw-wrap{max-width:860px}
  .cw-lead{font-size:14px;color:#6c6577;margin-bottom:16px;line-height:1.6}
  .panel{background:var(--paper,#fff);border-radius:14px;padding:18px 20px;box-shadow:var(--shadow,0 6px 20px rgba(0,0,0,.06));margin-bottom:16px}
  .panel h3{font-family:Oswald,sans-serif;font-size:15px;margin:0 0 4px}
  .panel .hint{font-size:12.5px;color:#8a7d68;margin:0 0 14px}
  .fld{display:block;margin-bottom:12px}
  .fld span{display:block;font-weight:600;font-size:12.5px;margin-bottom:5px;color:#4a4453}
  .fld input[type=text],.fld textarea{width:100%;padding:10px 11px;border:1px solid #e2d6c2;border-radius:9px;font-size:14px;font-family:inherit}
  .fld textarea{resize:vertical}
  .row2{display:flex;gap:14px;flex-wrap:wrap}
  .row2 .fld{flex:1;min-width:200px}
  .color-row{display:flex;gap:20px;flex-wrap:wrap}
  .color-row label{display:flex;align-items:center;gap:9px;font-size:13px;font-weight:600;color:#4a4453}
  .color-row input[type=color]{width:44px;height:34px;border:1px solid #e2d6c2;border-radius:8px;padding:2px;background:#fff;cursor:pointer}
  .toggle{display:flex;align-items:center;gap:9px;font-size:13.5px;font-weight:600;color:#4a4453}
  .ent{border:1px solid #ecdfca;border-radius:11px;padding:12px;margin-bottom:11px;background:#fdf9f0}
  .ent .top{display:flex;gap:10px;margin-bottom:8px}
  .ent .top .fld{flex:1;margin-bottom:0}
  .ent textarea{width:100%;padding:9px 11px;border:1px solid #e2d6c2;border-radius:9px;font-size:13.5px;font-family:inherit;resize:vertical}
  .ent .rm{border:1px solid #e2d6c2;background:#fff;border-radius:8px;padding:0 11px;cursor:pointer;font-size:14px;color:#a94e30;align-self:flex-start;height:38px}
  .add{border:1px dashed #c9bda6;background:#fff;border-radius:9px;padding:9px 14px;cursor:pointer;font-weight:600;font-size:13px;color:#4a4453}
  .save{background:var(--coral,#ee6a5a);color:#fff;border:none;border-radius:10px;padding:12px 26px;font-weight:700;font-family:Oswald,sans-serif;letter-spacing:.5px;cursor:pointer}
  .embed{display:flex;gap:10px;align-items:stretch;flex-wrap:wrap}
  .embed code{flex:1;min-width:280px;background:#1e2430;color:#d7e2f0;border-radius:10px;padding:12px 14px;font-family:ui-monospace,Menlo,Consolas,monospace;font-size:12.5px;word-break:break-all;line-height:1.5}
  .copy{background:#2f6f76;color:#fff;border:none;border-radius:10px;padding:0 18px;font-weight:700;font-size:13px;cursor:pointer;font-family:Oswald,sans-serif;letter-spacing:.4px}
  .copy.done{background:#2e8b57}
  .kbd{font-size:11.5px;color:#8a7d68;margin-top:8px}
</style>

<div class="cw-wrap">
  <p class="cw-lead">Build a guest chat bubble for your own website — guests can ask about wifi, breakfast, the pool and more, answered instantly from what you enter below. No coding: fill it in, hit <b>Save</b>, then paste the one-line code onto your site. Edit any time and your live widget updates automatically.</p>

  @if(session('status'))<div class="status">{{ session('status') }}</div>@endif

  <div class="panel">
    <h3>Your embed code</h3>
    <p class="hint">Paste this one line into your website, just before the closing &lt;/body&gt; tag. That's it.</p>
    <div class="embed">
      <code id="embedCode">&lt;script src="{{ $src }}" async&gt;&lt;/script&gt;</code>
      <button class="copy" id="copyBtn" type="button">Copy</button>
    </div>
    <div class="kbd">Widget is currently <b>{{ $w->enabled ? 'ON' : 'OFF' }}</b>. Turning it off (below) hides it from your site without removing the code.</div>
  </div>

  <form method="POST" action="{{ route('tools.chat-widget') }}">
    @csrf @method('PUT')

    <div class="panel">
      <h3>Appearance</h3>
      <label class="toggle" style="margin-bottom:14px"><input type="checkbox" name="enabled" value="1" @checked($w->enabled)> Widget is live on my website</label>
      <div class="row2">
        <label class="fld"><span>Title (shown in the chat header)</span><input type="text" name="title" value="{{ $cfg['title'] ?? '' }}" placeholder="e.g. The Cheshire Cat"></label>
        <label class="fld"><span>Subtitle</span><input type="text" name="subtitle" value="{{ $cfg['subtitle'] ?? 'Guest concierge · ask me anything' }}"></label>
      </div>
      <label class="fld"><span>Welcome message (first thing guests see)</span><textarea name="welcome" rows="2" placeholder="Hi, welcome! Ask me about wifi, breakfast, the pool…">{{ $cfg['welcome'] ?? '' }}</textarea></label>
      <div class="color-row">
        <label>Header colour <input type="color" name="primary" value="{{ $cfg['primary'] ?? '#1E7F86' }}"></label>
        <label>Accent colour <input type="color" name="accent" value="{{ $cfg['accent'] ?? '#E8553D' }}"></label>
      </div>
    </div>

    <div class="panel">
      <h3>Questions &amp; answers</h3>
      <p class="hint">Each card is one topic. <b>Button label</b> shows as a quick-tap chip. <b>Trigger words</b> are what a guest might type (comma-separated). <b>Answer</b> is the reply — use <b>**double asterisks**</b> for bold and new lines for spacing.</p>
      <div id="rows">
        @forelse($entries as $e)
          <div class="ent">
            <div class="top">
              <label class="fld"><span>Button label</span><input type="text" name="e_label[]" value="{{ $e['label'] ?? '' }}" placeholder="📶 WiFi"></label>
              <label class="fld"><span>Trigger words (comma-separated)</span><input type="text" name="e_keys[]" value="{{ $e['keys'] ?? '' }}" placeholder="wifi, internet, password"></label>
              <button type="button" class="rm" onclick="this.closest('.ent').remove()">✕</button>
            </div>
            <textarea name="e_answer[]" rows="3" placeholder="Answer…">{{ $e['answer'] ?? '' }}</textarea>
          </div>
        @empty
        @endforelse
      </div>
      <button type="button" class="add" onclick="cwAddRow()">+ Add question</button>
    </div>

    <div class="panel"><button class="save" type="submit">Save widget</button></div>
  </form>
</div>

<script>
  function cwAddRow(){
    var wrap = document.getElementById('rows');
    var d = document.createElement('div');
    d.className = 'ent';
    d.innerHTML = '<div class="top">'
      + '<label class="fld"><span>Button label</span><input type="text" name="e_label[]" placeholder="📶 WiFi"></label>'
      + '<label class="fld"><span>Trigger words (comma-separated)</span><input type="text" name="e_keys[]" placeholder="wifi, internet, password"></label>'
      + '<button type="button" class="rm" onclick="this.closest(\'.ent\').remove()">✕</button>'
      + '</div>'
      + '<textarea name="e_answer[]" rows="3" placeholder="Answer…"></textarea>';
    wrap.appendChild(d);
  }
  document.getElementById('copyBtn').addEventListener('click', function(){
    var txt = document.getElementById('embedCode').textContent;
    navigator.clipboard.writeText(txt).then(function(){
      var b = document.getElementById('copyBtn'); b.textContent = 'Copied ✓'; b.classList.add('done');
      setTimeout(function(){ b.textContent = 'Copy'; b.classList.remove('done'); }, 1800);
    });
  });
</script>

{{-- Live preview: loads this property's actual widget bottom-right (reflects last save) --}}
<script src="{{ $src }}" defer></script>
@endsection
