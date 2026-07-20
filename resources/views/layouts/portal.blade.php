<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Retro Motel Collective — Portal</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Monoton&family=Oswald:wght@300;400;500;600;700&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/portal.css') }}">
</head>
<body>
@php
    $u = auth()->user();
    $pending = collect(config('rmc.sections'))->reject(fn ($s) => $s['signup'] ?? false)
        ->keys()->filter(fn ($id) => !$u->sectionComplete($id))->count();
@endphp
<div id="app">
  <aside class="sidebar">
    <div class="sb-logo"><span class="neon">RETRO MOTEL</span><small>COLLECTIVE · MEMBER PORTAL</small></div>
    <nav class="nav">
      <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><span class="ic">◎</span>Dashboard</a>
      <a href="{{ route('registration.index') }}" class="{{ request()->routeIs('registration.index') ? 'active' : '' }}"><span class="ic">📝</span>Property Setup @if($pending)<span class="cnt">{{ $pending }}</span>@endif</a>
      <a href="{{ route('checker') }}" class="{{ request()->routeIs('checker') ? 'active' : '' }}"><span class="ic">🔍</span>Website Checker</a>
      @foreach(['✨|AI Assist','📤|My Documents','📇|Supplier Directory','🎙️|Monthly Roundtable','👥|Community','📚|Resource Library'] as $s)
        @php [$ic,$lbl] = explode('|', $s); @endphp
        <a class="soon" title="Launching 1 September"><span class="ic">{{ $ic }}</span>{{ $lbl }}<span class="soon-tag">SOON</span></a>
      @endforeach
      <a href="{{ route('account') }}" class="{{ request()->routeIs('account') ? 'active' : '' }}"><span class="ic">⚙️</span>Account</a>
    </nav>
    <div class="sb-foot">
      <div class="who">{{ $u->name }}</div><div class="prop">{{ $u->motel }}</div>
      <form method="POST" action="{{ route('logout') }}">@csrf<button class="lo" type="submit">Log out →</button></form>
    </div>
  </aside>
  <main class="main">
    <div class="topbar"><h2>@yield('title')</h2></div>
    <div class="content">
      @if(session('status'))<div class="status">{{ session('status') }}</div>@endif
      @yield('content')
    </div>
  </main>
</div>
@yield('modal')
</body>
</html>
