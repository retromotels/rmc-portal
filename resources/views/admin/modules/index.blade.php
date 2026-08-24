@extends('layouts.admin')
@section('title', 'Modules')
@section('content')
<style>
  .mh{font-family:Oswald,sans-serif;font-size:24px;margin:0 0 3px}
  .msub{font-size:13px;color:#8a7d68;margin-bottom:18px}
  .mcard{background:var(--paper,#fff);border-radius:13px;padding:20px 22px;box-shadow:var(--shadow,0 6px 20px rgba(0,0,0,.06));margin-bottom:16px}
  .mrow{display:flex;justify-content:space-between;align-items:flex-start;gap:14px;flex-wrap:wrap}
  .mtitle{font-family:Oswald,sans-serif;font-size:18px;margin:0 0 3px}
  .mdesc{font-size:13px;color:#6a6152;max-width:560px}
  .fld{display:block;margin:12px 0}
  .fld span{display:block;font-size:12px;font-weight:700;margin-bottom:5px;color:#4a4453}
  .fld input,.fld textarea{width:100%;padding:10px 12px;border:1.5px solid #e2d6c2;border-radius:9px;font:inherit;font-size:14px;background:#fff;box-sizing:border-box}
  .fld textarea{min-height:90px;resize:vertical}
  /* toggle */
  .sw{position:relative;display:inline-block;width:52px;height:29px;flex:none}
  .sw input{opacity:0;width:0;height:0}
  .sl{position:absolute;inset:0;background:#d8cdb6;border-radius:30px;cursor:pointer;transition:.2s}
  .sl:before{content:"";position:absolute;height:23px;width:23px;left:3px;top:3px;background:#fff;border-radius:50%;transition:.2s}
  .sw input:checked + .sl{background:#2e8b57}
  .sw input:checked + .sl:before{transform:translateX(23px)}
  .msave{background:#2e8b57;color:#fff;border:none;border-radius:9px;padding:12px 24px;font-weight:700;cursor:pointer;font-family:Oswald,sans-serif;letter-spacing:.5px;font-size:14px}
  .note{font-size:12px;color:#8a7d68;margin-top:6px}
</style>

@if(session('status'))<div class="status">{{ session('status') }}</div>@endif

<h1 class="mh">Modules</h1>
<p class="msub">Turn member tools on or off and edit what they show — no deploy needed. Changes are live immediately.</p>

<form method="POST" action="{{ route('admin.modules.update') }}">
  @csrf @method('PUT')

  <div class="mcard">
    <div class="mrow">
      <div>
        <h2 class="mtitle">✨ AI Assist</h2>
        <p class="mdesc">An in-portal Claude assistant members can ask for help — marketing copy, guest replies, operations questions. Requires the Anthropic API key in the server config to actually answer.</p>
      </div>
      <label class="sw"><input type="checkbox" name="module_ai_assist" value="1" @checked(\App\Models\Setting::bool('module_ai_assist'))><span class="sl"></span></label>
    </div>
    @unless(config('rmc.ai.enabled'))<div class="note">⚠️ No Anthropic API key set yet — the tool will show a "coming soon" message until the key is added to <code>.env</code>.</div>@endunless
  </div>

  <div class="mcard">
    <div class="mrow">
      <div>
        <h2 class="mtitle">🎙️ Monthly Roundtable</h2>
        <p class="mdesc">A page telling members about the monthly call, with a join link.</p>
      </div>
      <label class="sw"><input type="checkbox" name="module_roundtable" value="1" @checked(\App\Models\Setting::bool('module_roundtable'))><span class="sl"></span></label>
    </div>
    <label class="fld"><span>Page title</span><input type="text" name="roundtable_title" value="{{ \App\Models\Setting::get('roundtable_title') }}"></label>
    <label class="fld"><span>Body</span><textarea name="roundtable_body">{{ \App\Models\Setting::get('roundtable_body') }}</textarea></label>
    <label class="fld"><span>Join link (optional)</span><input type="url" name="roundtable_link" value="{{ \App\Models\Setting::get('roundtable_link') }}" placeholder="https://…"></label>
  </div>

  <div class="mcard">
    <div class="mrow">
      <div>
        <h2 class="mtitle">👥 Community</h2>
        <p class="mdesc">A gated member directory + forum. A property must join (add itself) before it can see the directory or take part. The title and welcome message below appear on the join screen.</p>
      </div>
      <label class="sw"><input type="checkbox" name="module_community" value="1" @checked(\App\Models\Setting::bool('module_community'))><span class="sl"></span></label>
    </div>
    <label class="fld"><span>Join-screen title</span><input type="text" name="community_title" value="{{ \App\Models\Setting::get('community_title') }}"></label>
    <label class="fld"><span>Join-screen welcome message</span><textarea name="community_body">{{ \App\Models\Setting::get('community_body') }}</textarea></label>
  </div>

  <button class="msave" type="submit">Save modules</button>
</form>
@endsection
