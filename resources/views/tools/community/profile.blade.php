@extends('layouts.portal')
@section('title', 'My community profile')
@section('content')
<style>
  .cp{max-width:600px}
  .cp-back{color:#6c6577;text-decoration:none;font-size:13.5px}
  .cp-h{font-family:Oswald,sans-serif;font-size:24px;margin:8px 0 16px}
  .cp-card{background:#fff;border:1px solid #ece1cd;border-radius:14px;padding:22px 24px;box-shadow:0 6px 20px rgba(0,0,0,.05)}
  .cp-av{display:flex;gap:16px;align-items:center;margin-bottom:16px}
  .av{width:70px;height:70px;border-radius:50%;object-fit:cover;background:#FFC078;display:grid;place-items:center;font-family:Oswald,sans-serif;font-weight:700;font-size:26px;color:#1F2933}
  .fld{display:block;margin-bottom:14px}
  .fld span{display:block;font-size:12.5px;font-weight:700;margin-bottom:5px;color:#4a4453}
  .fld input,.fld textarea{width:100%;padding:11px 13px;border:1.5px solid #e2d6c2;border-radius:9px;font:inherit;font-size:14.5px;background:#fff;box-sizing:border-box}
  .fld textarea{min-height:90px;resize:vertical}
  .row{display:flex;gap:14px;flex-wrap:wrap}.row .fld{flex:1;min-width:180px}
  .cp-go{background:#2e8b57;color:#fff;border:none;border-radius:10px;padding:13px 26px;font-weight:700;cursor:pointer;font-size:14.5px}
  .err{background:#fbe4e4;color:#a4283a;border-radius:9px;padding:10px 12px;font-size:13px;margin-bottom:14px}
</style>

<div class="cp">
  <a class="cp-back" href="{{ route('tools.community') }}">← Community</a>
  <h1 class="cp-h">My community profile</h1>
  <div class="cp-card">
    @if($errors->any())<div class="err">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('tools.community.profile.update') }}" enctype="multipart/form-data">
      @csrf
      <div class="cp-av">
        @if($me->avatar_path)<img class="av" src="{{ route('tools.community.avatar', $me) }}" alt="">@else<span class="av">{{ $me->initials() }}</span>@endif
        <label class="fld" style="flex:1;margin:0"><span>Change photo</span><input type="file" name="avatar" accept="image/*"></label>
      </div>
      <div class="row">
        <label class="fld"><span>Display name</span><input type="text" name="display_name" value="{{ old('display_name', $me->display_name) }}" required></label>
        <label class="fld"><span>Town / region</span><input type="text" name="town" value="{{ old('town', $me->town) }}"></label>
      </div>
      <label class="fld"><span>Headline</span><input type="text" name="headline" value="{{ old('headline', $me->headline) }}" maxlength="140"></label>
      <label class="fld"><span>About your property</span><textarea name="bio">{{ old('bio', $me->bio) }}</textarea></label>
      <label class="fld"><span>Website</span><input type="text" name="website" value="{{ old('website', $me->website) }}" placeholder="https://…"></label>
      <button class="cp-go" type="submit">Save profile</button>
    </form>
  </div>
</div>
@endsection
