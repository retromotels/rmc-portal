@extends('layouts.portal')
@section('title', 'The Vetting Desk')
@section('content')
<style>
  .vd-hero{background:#1F2933;color:#F8EED6;border-radius:16px;padding:26px 28px;margin-bottom:20px}
  .vd-hero .eb{font-size:10px;font-weight:700;letter-spacing:.24em;text-transform:uppercase;opacity:.6}
  .vd-hero h1{font-family:Cormorant Garamond,Georgia,serif;font-size:34px;margin:6px 0 6px;font-weight:700}
  .vd-hero h1 em{color:#FF9C85;font-style:italic}
  .vd-hero p{opacity:.9;font-size:14px;margin:0;max-width:640px}
  .vd-card{background:#fff;border:1px solid #ece1cd;border-radius:14px;padding:22px;box-shadow:0 6px 20px rgba(0,0,0,.05);margin-bottom:18px}
  .vd-card h2{font-family:Cormorant Garamond,Georgia,serif;font-size:22px;margin:0 0 4px}
  .vd-sec{font-size:11px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:#9C3A1C;margin:18px 0 10px}
  .vd-row{display:flex;gap:14px;flex-wrap:wrap}
  .fld{display:block;margin-bottom:13px;flex:1;min-width:150px}
  .fld > span{display:block;font-size:12px;font-weight:700;margin-bottom:5px;color:#4a4453}
  .fld input,.fld select,.fld textarea{width:100%;padding:11px 13px;border:1.5px solid #e2d6c2;border-radius:9px;font:inherit;font-size:14.5px;background:#fff;box-sizing:border-box}
  .fld textarea{min-height:74px;resize:vertical}
  .hint{font-size:12px;color:#8a7d68;margin:-6px 0 12px}
  .vd-go{background:#E0491D;color:#fff;border:none;border-radius:10px;padding:14px 30px;font-weight:700;cursor:pointer;font-size:14px;letter-spacing:.06em}
  .vd-err{background:#fbe4e4;color:#a4283a;border-radius:9px;padding:10px 12px;font-size:13px;margin-bottom:14px}
  .note{background:#FDF6E7;border:1px solid #efe4d2;border-radius:10px;padding:12px 14px;font-size:12.5px;color:#6a6152;line-height:1.55}
  .hist a{display:flex;justify-content:space-between;gap:12px;text-decoration:none;color:inherit;padding:11px 13px;border:1px solid #ece1cd;border-radius:10px;margin-bottom:8px;background:#fff}
  .hist a:hover{border-color:#FFC078}
  .hist .h{font-weight:700}
  .sc{font-family:Oswald,sans-serif;font-weight:700;border-radius:20px;padding:2px 11px;font-size:12.5px}
  .sc.good{background:#dff3e6;color:#2E7D52}.sc.warn{background:#fbeed0;color:#B8791C}.sc.bad{background:#fbe4e4;color:#C0341A}
</style>

@if($errors->any())<div class="vd-err">{{ $errors->first() }}</div>@endif

<div class="vd-hero">
  <div class="eb">Retro Motels Collective · Member tool</div>
  <h1>The <em>Vetting Desk</em></h1>
  <p>Check an Instagram creator against your motel before you say yes. Enter what you can see on their public profile and we'll score the fit against your drive market and guest type — with a ready-to-send reply either way.</p>
</div>

<form method="POST" action="{{ route('tools.vetting.run') }}">
  @csrf
  <div class="vd-card">
    <h2>Who are you checking?</h2>
    <div class="vd-row">
      <label class="fld"><span>Creator's Instagram handle</span><input type="text" name="handle" value="{{ old('handle') }}" placeholder="@their_handle" required></label>
      <label class="fld"><span>Their account type</span>
        <select name="account_type">
          <option value="">—</option>
          @foreach(['Personal','Creator','Business'] as $t)<option value="{{ $t }}" @selected(old('account_type')===$t)>{{ $t }}</option>@endforeach
        </select>
      </label>
      <label class="fld"><span>Where they're based</span><input type="text" name="based_location" value="{{ old('based_location') }}" placeholder="e.g. Gold Coast QLD"></label>
    </div>

    <div class="vd-sec">The numbers (from their public profile)</div>
    <div class="vd-row">
      <label class="fld"><span>Followers</span><input type="number" name="followers" value="{{ old('followers') }}" min="0" required></label>
      <label class="fld"><span>Following</span><input type="number" name="following" value="{{ old('following') }}" min="0"></label>
      <label class="fld"><span>Total posts</span><input type="number" name="posts" value="{{ old('posts') }}" min="0"></label>
    </div>
    <div class="vd-row">
      <label class="fld"><span>Avg likes (last ~12 posts)</span><input type="number" name="avg_likes" value="{{ old('avg_likes') }}" min="0" required></label>
      <label class="fld"><span>Avg comments</span><input type="number" name="avg_comments" value="{{ old('avg_comments') }}" min="0"></label>
      <label class="fld"><span>Posts per week</span><input type="number" step="0.1" name="posts_per_week" value="{{ old('posts_per_week') }}" min="0" placeholder="e.g. 0.4"></label>
    </div>
    <label class="fld"><span>Location tags on their recent posts (one per line)</span>
      <textarea name="post_locations" placeholder="Byron Bay NSW&#10;Gold Coast QLD&#10;Sydney NSW">{{ old('post_locations') }}</textarea>
    </label>
    <p class="hint">Copy the place tags shown on their last dozen posts. This drives the audience-map score.</p>
    <label class="fld"><span>A few of their recent captions or content themes (optional)</span>
      <textarea name="captions" placeholder="Paste a few captions, or describe their content — road trips, surf, family travel…">{{ old('captions') }}</textarea>
    </label>
  </div>

  <div class="vd-card">
    <h2>Check against your property</h2>
    <p class="hint" style="margin-top:2px">Scored against <strong>{{ $prop->motel ?: $prop->name }}</strong>. Set these once and we'll remember them.</p>
    <label class="fld"><span>Your drive market — the towns/regions your guests come from (comma or line separated)</span>
      <textarea name="drive_market" placeholder="Canberra, the Snowy, Sydney, Bega Valley, Eden">{{ old('drive_market', $prop->drive_market) }}</textarea>
    </label>
    <label class="fld"><span>Your typical guest type</span>
      <textarea name="guest_type" placeholder="Canberra families in the holidays, couples in the shoulder season, beach + national parks">{{ old('guest_type', $prop->guest_type) }}</textarea>
    </label>
    <label class="fld" style="max-width:320px"><span>Your own Instagram handle (optional)</span><input type="text" name="own_handle" value="{{ old('own_handle', $prop->ig_handle) }}" placeholder="@your_motel"></label>

    <div style="margin-top:8px"><button class="vd-go" type="submit">Run the check →</button></div>
  </div>
</form>

<div class="note" style="margin-bottom:18px">
  <strong>Being straight with you:</strong> real follower age and location are only visible to the account holder — no tool at this price can see them. This is a strong, honest read from public signals. For anyone you're serious about, ask for a screenshot of their Instagram Insights audience tab — every professional has one, and a refusal is an answer in itself.
</div>

@if($history->isNotEmpty())
  <div class="vd-card hist">
    <h2 style="margin-bottom:12px">Recent checks</h2>
    @foreach($history as $h)
      <a href="{{ route('tools.vetting.result', $h) }}">
        <span class="h">{{ '@'.$h->handle }} <span style="color:#8a7d68;font-weight:400">· {{ $h->property_name }} · {{ $h->created_at?->format('j M') }}</span></span>
        <span class="sc {{ $h->band() }}">{{ $h->score }}/100</span>
      </a>
    @endforeach
  </div>
@endif
@endsection
