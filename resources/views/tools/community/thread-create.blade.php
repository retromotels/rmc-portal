@extends('layouts.portal')
@section('title', 'New post')
@section('content')
<style>
  .nt{max-width:640px}
  .nt-back{color:#6c6577;text-decoration:none;font-size:13.5px}
  .nt-h{font-family:Oswald,sans-serif;font-size:24px;margin:8px 0 16px}
  .nt-card{background:#fff;border:1px solid #ece1cd;border-radius:14px;padding:22px 24px;box-shadow:0 6px 20px rgba(0,0,0,.05)}
  .fld{display:block;margin-bottom:14px}
  .fld span{display:block;font-size:12.5px;font-weight:700;margin-bottom:5px;color:#4a4453}
  .fld input,.fld select,.fld textarea{width:100%;padding:11px 13px;border:1.5px solid #e2d6c2;border-radius:9px;font:inherit;font-size:14.5px;background:#fff;box-sizing:border-box}
  .fld textarea{min-height:180px;resize:vertical}
  .nt-go{background:#e0491d;color:#fff;border:none;border-radius:10px;padding:13px 26px;font-weight:700;cursor:pointer;font-size:14.5px}
  .err{background:#fbe4e4;color:#a4283a;border-radius:9px;padding:10px 12px;font-size:13px;margin-bottom:14px}
</style>

<div class="nt">
  <a class="nt-back" href="{{ route('tools.community') }}">← Community</a>
  <h1 class="nt-h">Start a new post</h1>
  <div class="nt-card">
    @if($errors->any())<div class="err">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('tools.community.thread.store') }}">
      @csrf
      <label class="fld"><span>Category</span>
        <select name="category" required>
          @foreach(config('rmc.forum_categories') as $k => $lbl)<option value="{{ $k }}" @selected(old('category')===$k)>{{ $lbl }}</option>@endforeach
        </select>
      </label>
      <label class="fld"><span>Title</span><input type="text" name="title" value="{{ old('title') }}" placeholder="What's your post about?" required></label>
      <label class="fld"><span>Message</span><textarea name="body" placeholder="Share your question, tip or update with the collective…" required>{{ old('body') }}</textarea></label>
      <button class="nt-go" type="submit">Post to community</button>
    </form>
  </div>
</div>
@endsection
