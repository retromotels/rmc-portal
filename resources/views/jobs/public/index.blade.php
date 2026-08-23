@extends('jobs.public.layout')
@section('title', 'Jobs at independent motels')
@section('head')
<style>
  .controls{background:#fff;border-bottom:1px solid var(--bone);padding:16px 0}
  .cform{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
  .cform input[type=text],.cform select{padding:11px 14px;border:1.5px solid var(--bone);border-radius:9px;font:inherit;font-size:14px;background:var(--paper)}
  .cform input[type=text]{flex:1;min-width:200px}
  .chips{display:flex;gap:8px;flex-wrap:wrap;margin-top:12px}
  .chip{padding:8px 14px;border-radius:20px;border:1.5px solid var(--bone);background:#fff;font-size:13px;font-weight:600;text-decoration:none;color:var(--ink-soft)}
  .chip.on{background:var(--ink);color:var(--cream);border-color:var(--ink)}
  .result-meta{font-size:13.5px;color:var(--ink-soft);margin:20px 0 14px}
  .jobs{display:grid;gap:14px}
  .jcard{background:#fff;border:1px solid var(--bone);border-radius:16px;padding:20px 22px;box-shadow:0 8px 24px rgba(31,41,51,.06);display:flex;gap:18px;align-items:flex-start;flex-wrap:wrap;transition:transform .12s,box-shadow .12s;text-decoration:none;color:inherit}
  .jcard:hover{transform:translateY(-2px);box-shadow:var(--shadow)}
  .jc-main{flex:1;min-width:260px}
  .jc-title{font-family:var(--serif);font-size:24px;font-weight:700;margin:0 0 3px}
  .jc-prop{font-size:14px;color:var(--rust-ink);font-weight:600}
  .jc-badges{display:flex;gap:7px;flex-wrap:wrap;margin:9px 0}
  .badge{font-size:12px;font-weight:700;padding:4px 11px;border-radius:20px;background:var(--butter);color:var(--rust-ink)}
  .badge.alt{background:var(--sky);color:#0f4658}
  .badge.pay{background:var(--mint);color:#15603f}
  .jc-snip{font-size:14px;color:var(--ink-soft);margin-top:4px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
  .jc-cta{align-self:center;white-space:nowrap}
  .empty{background:#fff;border:1px solid var(--bone);border-radius:16px;padding:44px;text-align:center;color:var(--ink-soft)}
  .pager{display:flex;align-items:center;justify-content:center;gap:16px;margin:26px 0 6px}
  .pg{background:#fff;border:1.5px solid var(--bone);border-radius:9px;padding:9px 16px;font-weight:700;font-size:13.5px;text-decoration:none;color:var(--ink)}
  .pg:hover{border-color:var(--rust);color:var(--rust)}
  .pg.off{opacity:.4;pointer-events:none}
  .pg-info{font-size:13px;color:var(--ink-soft)}
</style>
@endsection
@section('content')

<header class="hero">
  <div class="wrap">
    <p class="eyebrow">Retro Motel Collective · Careers</p>
    <h1>Work at a <em>motel</em> with soul</h1>
    <p class="sub">Front desks, housekeeping, kitchens and more at independent motels around the country. Real places run by real people — find your next role below.</p>
  </div>
</header>

<section class="controls">
  <div class="wrap">
    <form class="cform" method="GET" action="{{ route('jobs.board') }}">
      <input type="text" name="q" value="{{ $kw }}" placeholder="Search role, keyword or town…">
      <select name="dept" onchange="this.form.submit()">
        <option value="">All departments</option>
        @foreach(config('rmc.job_departments') as $k => $lbl)
          <option value="{{ $k }}" @selected($dept === $k)>{{ $lbl }}</option>
        @endforeach
      </select>
      <select name="state" onchange="this.form.submit()">
        <option value="">All states</option>
        @foreach(config('rmc.job_states') as $code => $lbl)
          <option value="{{ $code }}" @selected($state === $code)>{{ $code }} — {{ $lbl }}</option>
        @endforeach
      </select>
      <select name="pay" onchange="this.form.submit()">
        <option value="">Any pay</option>
        @foreach(config('rmc.salary_bands') as $min => $lbl)
          <option value="{{ $min }}" @selected($pay === (string) $min)>{{ $lbl }}</option>
        @endforeach
      </select>
      <button class="btn btn-rust" type="submit">Search</button>
    </form>
    <div class="chips">
      <a href="{{ route('jobs.board', array_filter(['dept' => $dept, 'state' => $state, 'pay' => $pay, 'q' => $kw])) }}" class="chip {{ !$type ? 'on' : '' }}">All types</a>
      @foreach(config('rmc.employment_types') as $k => $lbl)
        <a href="{{ route('jobs.board', array_filter(['type' => $k, 'dept' => $dept, 'state' => $state, 'pay' => $pay, 'q' => $kw])) }}" class="chip {{ $type === $k ? 'on' : '' }}">{{ $lbl }}</a>
      @endforeach
    </div>
  </div>
</section>

<main class="wrap">
  <div class="result-meta">{{ number_format($jobs->total()) }} open {{ $jobs->total() === 1 ? 'role' : 'roles' }}@if($kw) matching “{{ $kw }}”@endif</div>

  @forelse($jobs as $job)
    <a class="jcard" href="{{ route('jobs.public.show', $job->slug) }}">
      <div class="jc-main">
        <h2 class="jc-title">{{ $job->title }}</h2>
        <div class="jc-prop">{{ $job->employerName() }}@if($job->location) · {{ $job->location }}@endif</div>
        <div class="jc-badges">
          <span class="badge">{{ $job->typeLabel() }}</span>
          @if($job->departmentLabel())<span class="badge alt">{{ $job->departmentLabel() }}</span>@endif
          @if($job->pay)<span class="badge pay">{{ $job->pay }}</span>@endif
        </div>
        <p class="jc-snip">{{ \Illuminate\Support\Str::of($job->description)->stripTags()->limit(180) }}</p>
      </div>
      <span class="btn btn-ink jc-cta">View &amp; apply →</span>
    </a>
  @empty
    <div class="empty">@if($kw || $type || $dept || $state || $pay)No roles match those filters — <a href="{{ route('jobs.board') }}" style="color:var(--rust)">clear filters</a> and check back soon.@else No open roles right now — check back soon.@endif</div>
  @endforelse

  @if($jobs->hasPages())
    <div class="pager">
      @if($jobs->onFirstPage())<span class="pg off">← Prev</span>@else<a class="pg" href="{{ $jobs->previousPageUrl() }}">← Prev</a>@endif
      <span class="pg-info">Page {{ $jobs->currentPage() }} of {{ $jobs->lastPage() }}</span>
      @if($jobs->hasMorePages())<a class="pg" href="{{ $jobs->nextPageUrl() }}">Next →</a>@else<span class="pg off">Next →</span>@endif
    </div>
  @endif
</main>
@endsection
