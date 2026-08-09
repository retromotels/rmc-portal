@extends('layouts.admin')
@section('title', 'Content')
@section('content')
<style>
  .panel{background:var(--paper,#fff);border-radius:14px;padding:18px 20px;box-shadow:var(--shadow,0 6px 20px rgba(0,0,0,.06));max-width:820px;margin-bottom:16px}
  .panel h3{font-family:Oswald,sans-serif;font-size:16px;margin:0 0 4px}
  .panel .hint{font-size:12.5px;color:#8a7d68;margin:0 0 14px}
  .fld{display:block;margin-bottom:12px}
  .fld span{display:block;font-weight:600;font-size:12.5px;margin-bottom:5px;color:#4a4453}
  .fld input,.fld textarea{width:100%;padding:10px 11px;border:1px solid #e2d6c2;border-radius:9px;font-size:14px;font-family:inherit}
  .fld textarea{resize:vertical}
  .faq-row{display:flex;gap:10px;align-items:flex-start;margin-bottom:10px}
  .faq-row input{flex:1}
  .faq-row textarea{flex:2}
  .rm{border:1px solid #e2d6c2;background:#fff;border-radius:8px;padding:8px 10px;cursor:pointer;font-size:13px}
  .add{border:1px dashed #c9bda6;background:#fff;border-radius:9px;padding:9px 14px;cursor:pointer;font-weight:600;font-size:13px;color:#4a4453}
  .save{background:var(--coral,#ee6a5a);color:#fff;border:none;border-radius:10px;padding:12px 24px;font-weight:700;font-family:Oswald,sans-serif;letter-spacing:.5px;cursor:pointer}
</style>

@if(session('status'))<div class="status">{{ session('status') }}</div>@endif

<form method="POST" action="{{ route('admin.content.update') }}">
  @csrf @method('PUT')

  <div class="panel">
    <h3>Dashboard welcome banner</h3>
    <p class="hint">Shown at the top of every property’s dashboard. Leave both blank to hide the banner entirely.</p>
    <label class="fld"><span>Title</span><input name="banner_title" value="{{ $banner['title'] ?? '' }}" placeholder="Welcome to the collective!"></label>
    <label class="fld"><span>Copy</span><textarea name="banner_copy" rows="3" placeholder="Thank you for joining…">{{ $banner['copy'] ?? '' }}</textarea></label>
  </div>

  <div class="panel">
    <h3>About Us page</h3>
    <p class="hint">Shown on the property portal’s “About Us” tab.</p>
    <label class="fld"><span>Title</span><input name="about_title" value="{{ $about['title'] ?? '' }}" placeholder="About the Retro Motel Collective"></label>
    <label class="fld"><span>Body</span><textarea name="about_body" rows="6">{{ $about['body'] ?? '' }}</textarea></label>
    <label class="fld"><span>Image URLs (one per line)</span><textarea name="about_images" rows="3" placeholder="https://…/photo.jpg">{{ implode("\n", $about['images'] ?? []) }}</textarea></label>
  </div>

  <div class="panel">
    <h3>FAQ</h3>
    <p class="hint">Shown as an accordion on the property portal’s “FAQ” tab.</p>
    <div id="faqRows">
      @forelse($faq as $item)
        <div class="faq-row">
          <input name="faq_q[]" value="{{ $item['q'] ?? '' }}" placeholder="Question">
          <textarea name="faq_a[]" rows="2" placeholder="Answer">{{ $item['a'] ?? '' }}</textarea>
          <button type="button" class="rm" onclick="this.closest('.faq-row').remove()">✕</button>
        </div>
      @empty
        <div class="faq-row">
          <input name="faq_q[]" placeholder="Question">
          <textarea name="faq_a[]" rows="2" placeholder="Answer"></textarea>
          <button type="button" class="rm" onclick="this.closest('.faq-row').remove()">✕</button>
        </div>
      @endforelse
    </div>
    <button type="button" class="add" onclick="addFaq()">+ Add question</button>
  </div>

  <div class="panel"><button class="save" type="submit">Save content</button></div>
</form>

<script>
  function addFaq(){
    var wrap = document.getElementById('faqRows');
    var row = document.createElement('div');
    row.className = 'faq-row';
    row.innerHTML = '<input name="faq_q[]" placeholder="Question"><textarea name="faq_a[]" rows="2" placeholder="Answer"></textarea><button type="button" class="rm" onclick="this.closest(\'.faq-row\').remove()">✕</button>';
    wrap.appendChild(row);
  }
</script>
@endsection
