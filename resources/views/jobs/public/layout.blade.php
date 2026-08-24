<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Jobs') · Retro Motels</title>
<meta name="description" content="Jobs at independent motels across Australia — front office, housekeeping, food & beverage, management and more, from the Retro Motel Collective.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,600&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,700;9..40,800;9..40,900&display=swap" rel="stylesheet">
<style>
  :root{
    --ink:#1f2933;--ink-soft:#2a3642;--cream:#f8eed6;--bone:#f0dcae;
    --peach:#ffc078;--butter:#ffe574;--rust:#e0491d;--rust-ink:#9c3a1c;
    --pink:#ffb3a7;--lilac:#c7a1f0;--sky:#8ed2f4;--mint:#8fe2b6;--salmon:#ff9c85;
    --paper:#faf4e6;--serif:'Cormorant Garamond',Georgia,serif;--sans:'DM Sans','Manrope',Arial,sans-serif;
    --shadow:0 20px 50px rgba(31,41,51,.14);
  }
  *{margin:0;padding:0;box-sizing:border-box}
  body{font-family:var(--sans);color:var(--ink);background:var(--cream);line-height:1.55}
  a{color:inherit}
  .wrap{max-width:1080px;margin:0 auto;padding:0 22px}
  /* Nav */
  nav.top{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:16px 22px;background:var(--paper);border-bottom:1px solid var(--bone);position:sticky;top:0;z-index:30;flex-wrap:wrap}
  .wordmark{display:inline-flex;gap:3px;align-items:center;text-decoration:none}
  .wordmark .tl{width:22px;height:22px;border-radius:5px;display:grid;place-items:center;font-weight:900;font-size:12px;color:var(--ink)}
  .wordmark .gap{width:7px}
  .nav-links{display:flex;gap:20px;align-items:center;font-weight:600;font-size:14px}
  .nav-links a,.nav-links button{text-decoration:none;color:var(--ink-soft);background:none;border:none;font:inherit;cursor:pointer}
  .nav-links a:hover{color:var(--rust)}
  /* Distinct account button — teal pill so it stands apart from the rust hero/search */
  .nav-cta{background:#1f7a6d;color:#fff !important;padding:9px 18px;border-radius:24px;font-weight:800;box-shadow:0 6px 16px rgba(31,122,109,.28);transition:transform .12s,box-shadow .12s}
  .nav-cta:hover{color:#fff !important;transform:translateY(-1px);box-shadow:0 9px 20px rgba(31,122,109,.34)}
  .nav-avatar{width:30px;height:30px;border-radius:50%;object-fit:cover;border:2px solid var(--bone);vertical-align:middle}
  .nav-me{display:inline-flex;align-items:center;gap:8px}
  /* Hero */
  header.hero{background:linear-gradient(180deg,#fff3d6,var(--cream));padding:52px 0 40px;border-bottom:1px solid var(--bone)}
  .eyebrow{text-transform:uppercase;letter-spacing:.18em;font-size:12px;font-weight:800;color:var(--rust-ink)}
  header.hero h1{font-family:var(--serif);font-size:clamp(38px,6vw,64px);font-weight:700;line-height:1.02;margin:10px 0 12px}
  header.hero h1 em{font-style:italic;color:var(--rust)}
  header.hero .sub{font-size:17px;max-width:620px;color:var(--ink-soft)}
  /* Buttons */
  .btn{display:inline-block;border:none;border-radius:9px;padding:11px 20px;font-family:var(--sans);font-weight:700;font-size:14px;cursor:pointer;text-decoration:none;text-align:center}
  .btn-rust{background:var(--rust);color:#fff}
  .btn-ink{background:var(--ink);color:var(--cream)}
  .btn-ghost{background:#fff;border:1.5px solid var(--bone);color:var(--ink)}
  /* Footer */
  footer.site{background:var(--paper);border-top:1px solid var(--bone);margin-top:50px;padding:28px 0}
  footer .foot{display:flex;align-items:center;gap:16px;flex-wrap:wrap;justify-content:space-between}
  .tiles-mini{display:inline-flex;gap:3px}
  .tiles-mini .tl{width:18px;height:18px;border-radius:4px;display:grid;place-items:center;font-weight:900;font-size:10px;color:var(--ink)}
  .fine{font-size:12px;color:var(--ink-soft);max-width:620px}
  .flash{background:#dff3e6;border:1px solid #a9dcbf;color:#2e7d4f;border-radius:10px;padding:12px 15px;font-size:14px;margin:16px 0}
</style>
@yield('head')
</head>
<body>

@php $seeker = auth('seeker')->user(); @endphp
<nav class="top">
  <a class="wordmark" href="{{ route('jobs.board') }}" aria-label="Retro Motels">
    <span class="tl" style="background:var(--peach)">R</span><span class="tl" style="background:var(--butter)">E</span><span class="tl" style="background:var(--rust);color:var(--cream)">T</span><span class="tl" style="background:var(--pink)">R</span><span class="tl" style="background:var(--lilac)">O</span><span class="gap"></span><span class="tl" style="background:var(--sky)">M</span><span class="tl" style="background:var(--mint)">O</span><span class="tl" style="background:var(--rust);color:var(--cream)">T</span><span class="tl" style="background:var(--salmon)">E</span><span class="tl" style="background:var(--butter)">L</span><span class="tl" style="background:var(--pink)">S</span>
  </a>
  <div class="nav-links">
    <a href="{{ route('jobs.board') }}">All jobs</a>
    @if($seeker)
      <a href="{{ route('seeker.dashboard') }}">My applications</a>
      <a class="nav-me" href="{{ route('seeker.profile') }}">
        @if($seeker->avatar_path)<img class="nav-avatar" src="{{ route('seeker.avatar', $seeker) }}" alt="">@else<span class="nav-avatar" style="display:inline-grid;place-items:center;background:var(--peach);font-weight:800;color:var(--ink);border-color:transparent">{{ strtoupper(substr($seeker->name,0,1)) }}</span>@endif
        My profile
      </a>
      <form method="POST" action="{{ route('seeker.logout') }}" style="display:inline">@csrf<button type="submit">Log out</button></form>
    @else
      <a href="{{ route('seeker.login') }}">Log in</a>
      <a class="nav-cta" href="{{ route('seeker.register') }}">Create account</a>
    @endif
  </div>
</nav>

@yield('content')

<footer class="site">
  <div class="wrap foot">
    <span class="tiles-mini">
      <span class="tl" style="background:var(--peach)">R</span><span class="tl" style="background:var(--butter)">E</span><span class="tl" style="background:var(--rust);color:var(--cream)">T</span><span class="tl" style="background:var(--pink)">R</span><span class="tl" style="background:var(--lilac)">O</span>
    </span>
    <p class="fine">© {{ date('Y') }} Retro Motel Pty Ltd. Jobs at independent motels across the collective. Not a franchise, not a booking site — a commercial alliance of independents.</p>
  </div>
</footer>
</body>
</html>
