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
<link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
<link rel="stylesheet" href="{{ asset('css/portal.css') }}">
</head>
<body>
@php $unread = \App\Models\AdminNotification::whereNull('read_at')->count(); @endphp
<div id="app">
  <aside class="sidebar admin">
    <div class="sb-logo">@include('partials.logo', ['stack' => true])<small class="adm-tag">HEAD OFFICE · ADMIN</small></div>
    <nav class="nav">
      <a href="{{ route('admin.overview') }}" class="{{ request()->routeIs('admin.overview') ? 'active' : '' }}"><span class="ic">📊</span>Overview</a>
      <a href="{{ route('admin.notifications.index') }}" class="{{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}"><span class="ic">🔔</span>Notifications @if($unread)<span class="cnt">{{ $unread }}</span>@endif</a>
      <a href="{{ route('admin.motels') }}" class="{{ request()->routeIs('admin.motel*') ? 'active' : '' }}"><span class="ic">🏨</span>Motels</a>
      <a href="{{ route('admin.onboard.create') }}" class="{{ request()->routeIs('admin.onboard.*') ? 'active' : '' }}"><span class="ic">➕</span>Create Property</a>
      <a href="{{ route('admin.activity') }}" class="{{ request()->routeIs('admin.activity') ? 'active' : '' }}"><span class="ic">📈</span>Activity</a>
      <a href="{{ route('admin.listings.index') }}" class="{{ request()->routeIs('admin.listings.*') ? 'active' : '' }}"><span class="ic">✅</span>Listing Check</a>
      <a href="{{ route('admin.content.edit') }}" class="{{ request()->routeIs('admin.content.*') ? 'active' : '' }}"><span class="ic">📰</span>Content</a>
      <a href="{{ route('admin.outbox.index') }}" class="{{ request()->routeIs('admin.outbox.*') ? 'active' : '' }}"><span class="ic">📧</span>Outbox</a>
      <a href="{{ route('admin.policies') }}" class="{{ request()->routeIs('admin.policies') ? 'active' : '' }}"><span class="ic">📄</span>Signed Policies</a>
    </nav>
    <div class="sb-foot">
      <div class="who">{{ auth()->user()->name }}</div><div class="prop">{{ auth()->user()->email }}</div>
      <form method="POST" action="{{ route('logout') }}">@csrf<button class="lo" type="submit">Log out →</button></form>
    </div>
  </aside>
  <main class="main">
    <div class="topbar">
      <h2>@yield('title')</h2>
      <div class="view-as">
        <span class="va-lbl">View portal as:</span>
        <a href="{{ route('admin.preview', 'standard') }}">Standard</a>
        <a href="{{ route('admin.preview', 'growth') }}">Growth</a>
        <a href="{{ route('admin.preview', 'full') }}">Full</a>
      </div>
    </div>
    <div class="content">
      @if(session('status'))<div class="status">{{ session('status') }}</div>@endif
      @yield('content')
    </div>
  </main>
</div>
</body>
</html>
