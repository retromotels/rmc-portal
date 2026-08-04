<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>RMC — Head Office</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Monoton&family=Oswald:wght@300;400;500;600;700&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/portal.css') }}">
</head>
<body>
<div id="app">
  <aside class="sidebar admin">
    <div class="sb-logo"><span class="neon aqua">RMC</span><small class="adm-tag">HEAD OFFICE · ADMIN</small></div>
    <nav class="nav">
      <a href="{{ route('admin.overview') }}" class="{{ request()->routeIs('admin.overview') ? 'active' : '' }}"><span class="ic">📊</span>Overview</a>
      <a href="{{ route('admin.motels') }}" class="{{ request()->routeIs('admin.motel*') ? 'active' : '' }}"><span class="ic">🏨</span>Motels</a>
      <a href="{{ route('admin.policies') }}" class="{{ request()->routeIs('admin.policies') ? 'active' : '' }}"><span class="ic">📄</span>Signed Policies</a>
      <a href="{{ route('admin.sites.index') }}" class="{{ request()->routeIs('admin.sites.*') ? 'active' : '' }}"><span class="ic">🌐</span>Site Builder</a>
    </nav>
    <div class="sb-foot">
      <div class="who">{{ auth()->user()->name }}</div><div class="prop">{{ auth()->user()->email }}</div>
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
</body>
</html>
