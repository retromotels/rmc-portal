@extends('jobs.public.layout')
@section('title', 'List a job')
@section('head')
<style>
  .ph-hero{background:linear-gradient(180deg,#fff3d6,var(--cream));border-bottom:1px solid var(--bone);padding:52px 0 40px;text-align:center}
  .ph-hero .eyebrow{color:var(--rust-ink)}
  .ph-hero h1{font-family:var(--serif);font-size:clamp(36px,6vw,58px);font-weight:700;margin:8px 0 12px}
  .ph-hero h1 em{font-style:italic;color:var(--rust)}
  .ph-hero p{font-size:17px;color:var(--ink-soft);max-width:600px;margin:0 auto}
  .ph-stats{display:flex;gap:30px;justify-content:center;flex-wrap:wrap;margin-top:22px}
  .ph-stats div{font-size:13px;color:var(--ink-soft)}.ph-stats b{display:block;font-family:var(--serif);font-size:26px;color:var(--ink)}
  .ph-incl{max-width:680px;margin:34px auto 0;background:#fff;border:1px solid var(--bone);border-radius:14px;padding:20px 24px}
  .ph-incl h3{font-family:var(--serif);font-size:20px;margin:0 0 10px}
  .ph-incl ul{margin:0;padding-left:20px;font-size:14.5px;color:var(--ink-soft);line-height:1.7}
  .tiers{display:grid;grid-template-columns:repeat(auto-fit,minmax(270px,1fr));gap:20px;margin:34px 0}
  .tier{background:#fff;border:1.5px solid var(--bone);border-radius:18px;padding:26px 24px;display:flex;flex-direction:column;box-shadow:0 8px 24px rgba(31,41,51,.06)}
  .tier.feat{border-color:var(--rust);box-shadow:0 14px 34px rgba(224,73,29,.16)}
  .tier .badge{align-self:flex-start;font-size:11px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;background:var(--butter);color:var(--rust-ink);padding:4px 10px;border-radius:20px;margin-bottom:10px}
  .tier h3{font-family:var(--serif);font-size:26px;margin:0 0 4px}
  .tier .price{font-family:var(--serif);font-size:34px;font-weight:700;margin:6px 0}
  .tier .price small{font-size:14px;color:var(--ink-soft);font-family:var(--sans)}
  .tier p.blurb{font-size:14px;color:var(--ink-soft);flex:1}
  .tier .buy{margin-top:16px;text-align:center;padding:13px;font-size:15px}
  .ph-note{text-align:center;font-size:13px;color:var(--ink-soft);margin-top:8px}
</style>
@endsection
@section('content')

@if(session('flash'))<div class="wrap"><div class="flash" style="margin-top:16px">{{ session('flash') }}</div></div>@endif

<header class="ph-hero">
  <div class="wrap">
    <p class="eyebrow">For those hiring</p>
    <h1>Put your role in front of <em>people who want it</em></h1>
    <p>List a hospitality or motel role on the Retro Motels board — seen by job seekers who actually want to work in independent motels. No recruiters, no lock-ins.</p>
    <div class="ph-stats">
      <div><b>{{ number_format(\App\Models\JobListing::live()->count()) }}</b> live roles</div>
      <div><b>{{ number_format(\App\Models\JobSeeker::count()) }}</b> registered seekers</div>
      <div><b>Admin-checked</b> every listing</div>
    </div>
  </div>
</header>

<main class="wrap">
  <div class="ph-incl">
    <h3>Every job post gets</h3>
    <ul>
      <li>Seen by a hospitality-focused audience — no irrelevant noise</li>
      <li>Applications straight to your inbox, plus a simple dashboard</li>
      <li>Reviewed by head office before it goes live</li>
    </ul>
  </div>

  <div class="tiers">
    @foreach($tiers as $key => $t)
      <div class="tier {{ $key === 'three_pack' ? 'feat' : '' }}">
        @if($key === 'three_pack')<span class="badge">Best value</span>@endif
        <h3>{{ $t['name'] }}</h3>
        <div class="price">
          @if($t['price']){{ $currency === 'AUD' ? 'A$' : '$' }}{{ number_format($t['price']) }}<small> / {{ $t['credits'] }} {{ \Illuminate\Support\Str::plural('listing', $t['credits']) }}</small>
          @else POA <small>/ built for you</small>@endif
        </div>
        <p class="blurb">{{ $t['blurb'] }}</p>

        @if($employer)
          @if($key === 'top_shelf')
            <a class="btn btn-ink buy" href="{{ route('employer.dashboard') }}#topshelf">Enquire</a>
          @else
            <form method="POST" action="{{ route('employer.buy', $key) }}">@csrf<button class="btn btn-rust buy" type="submit" style="width:100%;border:none;cursor:pointer">Buy {{ $t['name'] }}</button></form>
          @endif
        @else
          <a class="btn {{ $key === 'three_pack' ? 'btn-rust' : 'btn-ink' }} buy" href="{{ route('employer.register') }}">Get started</a>
        @endif
      </div>
    @endforeach
  </div>

  @unless(config('rmc.stripe.live'))
    <p class="ph-note">Card payments are being switched on shortly — for now, choosing a pack sends head office a request and we'll arrange it with you.</p>
  @endunless

  <p class="ph-note">Already have an account? <a href="{{ route('employer.login') }}" style="color:var(--rust);font-weight:700">Log in</a></p>
</main>
@endsection
