@extends('layouts.portal')
@section('title', $title)
@section('content')
<style>
  .cj{max-width:620px}
  .cj-hero{background:#1F2933;color:#F8EED6;border-radius:16px;padding:28px 30px;margin-bottom:20px}
  .cj-hero .ic{font-size:32px}
  .cj-hero h1{font-family:Cormorant Garamond,serif;font-size:32px;font-weight:700;margin:8px 0 8px}
  .cj-hero p{opacity:.9;font-size:14.5px;margin:0;white-space:pre-line}
  .cj-hero .cnt{margin-top:12px;font-size:13px;opacity:.75}
  .cj-card{background:#fff;border:1px solid #ece1cd;border-radius:14px;padding:22px 24px;box-shadow:0 6px 20px rgba(0,0,0,.05)}
  .cj-card h2{font-family:Cormorant Garamond,serif;font-size:22px;margin:0 0 4px}
  .cj-note{font-size:13px;color:#8a7d68;margin-bottom:16px}
  .fld{display:block;margin-bottom:14px}
  .fld span{display:block;font-size:12.5px;font-weight:700;margin-bottom:5px;color:#4a4453}
  .fld input,.fld textarea{width:100%;padding:11px 13px;border:1.5px solid #e2d6c2;border-radius:9px;font:inherit;font-size:14.5px;background:#fff;box-sizing:border-box}
  .fld textarea{min-height:80px;resize:vertical}
  .row{display:flex;gap:14px;flex-wrap:wrap}.row .fld{flex:1;min-width:180px}
  .cj-go{background:#e0491d;color:#fff;border:none;border-radius:10px;padding:14px 28px;font-weight:700;cursor:pointer;font-size:15px}
  .err{background:#fbe4e4;color:#a4283a;border-radius:9px;padding:10px 12px;font-size:13px;margin-bottom:14px}
</style>

<div class="cj">
  <div class="cj-hero">
    <div class="ic">👥</div>
    <h1>{{ $title }}</h1>
    <p>{{ $intro }}</p>
    @if($count)<div class="cnt">{{ $count }} {{ \Illuminate\Support\Str::plural('property', $count) }} already in the community.</div>@endif
  </div>

  <div class="cj-card">
    <h2>Add yourself to join</h2>
    <p class="cj-note">Create your community profile to see the member directory and the forum. Only members can see who's in and take part.</p>
    @if($errors->any())<div class="err">{{ $errors->first() }}</div>@endif

    <form method="POST" action="{{ route('tools.community.join') }}" enctype="multipart/form-data">
      @csrf
      <div class="row">
        <label class="fld"><span>Display name</span><input type="text" name="display_name" value="{{ old('display_name', $prop->motel ?: $prop->name) }}" required></label>
        <label class="fld"><span>Town / region</span><input type="text" name="town" value="{{ old('town', $prop->loc) }}" placeholder="e.g. Byron Bay, NSW"></label>
      </div>
      <label class="fld"><span>Headline</span><input type="text" name="headline" value="{{ old('headline') }}" placeholder="e.g. 14-room beachside motel, family run" maxlength="140"></label>
      <label class="fld"><span>About your property</span><textarea name="bio" placeholder="A few lines about you and your motel — what you're known for, what you're working on.">{{ old('bio', $prop->bio) }}</textarea></label>
      <div class="row">
        <label class="fld"><span>Website (optional)</span><input type="text" name="website" value="{{ old('website') }}" placeholder="https://…"></label>
        <label class="fld"><span>Profile photo (optional)</span><input type="file" name="avatar" accept="image/*"></label>
      </div>
      <button class="cj-go" type="submit">Join the community →</button>
    </form>
  </div>
</div>
@endsection
