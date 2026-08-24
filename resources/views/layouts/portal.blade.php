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
<link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
<link rel="stylesheet" href="{{ asset('css/portal.css') }}">
</head>
<body>
@php
    $u = auth()->user();
    $cp = $currentProperty ?? $u;
    $pending = collect(config('rmc.sections'))->reject(fn ($s) => $s['signup'] ?? false)
        ->keys()->filter(fn ($id) => !$cp->sectionComplete($id))->count();
@endphp
<div id="app">
  <aside class="sidebar">
    <div class="sb-logo">@include('partials.logo', ['stack' => true])<small>COLLECTIVE · MEMBER PORTAL</small></div>
    <nav class="nav">
      <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><span class="ic">◎</span>Dashboard</a>
      <a href="{{ route('registration.index') }}" class="{{ request()->routeIs('registration.index') ? 'active' : '' }}"><span class="ic">📝</span>Property Setup @if($pending)<span class="cnt">{{ $pending }}</span>@endif</a>
      <a href="{{ route('health') }}" class="{{ request()->routeIs('health') ? 'active' : '' }}"><span class="ic">🩺</span>Health Check</a>
      <a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'active' : '' }}"><span class="ic">ℹ️</span>About Us</a>
      <a href="{{ route('faq') }}" class="{{ request()->routeIs('faq') ? 'active' : '' }}"><span class="ic">❓</span>FAQ</a>
      <div style="font-size:10.5px;letter-spacing:1.4px;text-transform:uppercase;color:#b7ab97;margin:16px 0 4px;padding:0 4px">Tools</div>
      <a href="{{ route('tools.chat-widget') }}" class="{{ request()->routeIs('tools.chat-widget') ? 'active' : '' }}"><span class="ic">💬</span>Chat Widget</a>
      <a href="{{ route('jobs.index') }}" class="{{ request()->routeIs('jobs.*') ? 'active' : '' }}"><span class="ic">💼</span>Jobs</a>
      @if(config('rmc.features.vetting'))
        <a href="{{ route('tools.vetting') }}" class="{{ request()->routeIs('tools.vetting*') ? 'active' : '' }}"><span class="ic">🔎</span>IG Checker</a>
      @endif
      @if(config('rmc.features.documents'))
        <a href="{{ route('tools.documents') }}" class="{{ request()->routeIs('tools.documents*') ? 'active' : '' }}"><span class="ic">📄</span>Documents</a>
      @endif
      @if(config('rmc.features.suppliers'))
        <a href="{{ route('tools.suppliers') }}" class="{{ request()->routeIs('tools.suppliers*') ? 'active' : '' }}"><span class="ic">📚</span>Resource Library</a>
      @endif
      @php
        $soon = ['✨|AI Assist'];
        if (!config('rmc.features.documents')) $soon[] = '📤|My Documents';
        if (!config('rmc.features.suppliers')) $soon[] = '📚|Resource Library';
        $soon = array_merge($soon, ['🎙️|Monthly Roundtable','👥|Community']);
      @endphp
      @foreach($soon as $s)
        @php [$ic,$lbl] = explode('|', $s); @endphp
        <a class="soon" title="Launching 1 September"><span class="ic">{{ $ic }}</span>{{ $lbl }}<span class="soon-tag">SOON</span></a>
      @endforeach
      <a href="{{ route('account') }}" class="{{ request()->routeIs('account') ? 'active' : '' }}"><span class="ic">⚙️</span>Account</a>
    </nav>
    <div class="sb-foot">
      <div class="who">{{ $u->name }}</div><div class="prop">{{ $cp->motel }}</div>
      <form method="POST" action="{{ route('logout') }}">@csrf<button class="lo" type="submit">Log out →</button></form>
    </div>
  </aside>
  <main class="main">
    @isset($adminPreview)
      <div class="preview-bar">
        <span>👁 Viewing as <b>{{ $adminPreview->motel ?: $adminPreview->name }}</b>@if($adminPreview->loc) · {{ $adminPreview->loc }}@endif<span class="pv-tier"> · {{ $adminPreview->tierMeta()['name'] ?? ucfirst($adminPreview->tier) }}</span></span>
        <form method="POST" action="{{ route('preview.exit') }}">@csrf<button type="submit">Exit preview →</button></form>
      </div>
    @endisset
    <div class="topbar">
      <h2>@yield('title')</h2>
      @isset($accountProperties)
        <div class="prop-switch">
          <form method="POST" action="{{ route('properties.switch') }}" id="propForm">@csrf
            <span class="ps-ic">🏨</span>
            <select name="property_id" onchange="document.getElementById('propForm').submit()">
              @foreach($accountProperties as $p)
                <option value="{{ $p->id }}" @selected($cp->id === $p->id)>{{ $p->motel ?: 'Untitled property' }}</option>
              @endforeach
            </select>
          </form>
          <a class="ps-add" href="{{ route('properties.add') }}" title="Add a property">＋</a>
        </div>
      @endisset
    </div>
    <div class="content">
      @if(session('status'))<div class="status">{{ session('status') }}</div>@endif
      @yield('content')
    </div>
  </main>
</div>
@yield('modal')
</body>
</html>
