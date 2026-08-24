@extends('jobs.public.layout')
@section('title', 'Employer dashboard')
@section('head')
<style>
  .ed{max-width:880px;margin:36px auto;padding:0 22px}
  .ed h1{font-family:var(--serif);font-size:32px;font-weight:700;margin-bottom:2px}
  .ed .hi{font-size:14px;color:var(--ink-soft);margin-bottom:20px}
  .credits{display:flex;align-items:center;gap:18px;flex-wrap:wrap;background:#1f2933;color:var(--cream);border-radius:16px;padding:22px 26px;margin-bottom:18px}
  .credits .n{font-family:var(--serif);font-size:44px;line-height:1}
  .credits .l{font-size:13px;opacity:.85}
  .credits .cta{margin-left:auto;display:flex;gap:10px;flex-wrap:wrap}
  .card{background:#fff;border:1px solid var(--bone);border-radius:16px;padding:20px 22px;box-shadow:0 8px 24px rgba(31,41,51,.06);margin-bottom:18px}
  .card h2{font-family:var(--serif);font-size:22px;margin:0 0 12px}
  .tiers{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px}
  .tq{border:1px solid var(--bone);border-radius:12px;padding:14px 16px}
  .tq h3{font-family:var(--serif);font-size:18px;margin:0 0 2px}
  .tq .p{font-weight:700;margin-bottom:8px}
  .jrow{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;border:1px solid var(--bone);border-radius:11px;padding:12px 14px;margin-bottom:9px}
  .jrow .t{font-weight:700}
  .flag{font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px}
  .f-pending{background:#fdf0d5;color:#9a6a10}.f-approved{background:#dff3e6;color:#2e7d4f}.f-rejected{background:#fbe4e4;color:#a4283a}.f-closed{background:#eee;color:#777}
  .muted{color:var(--ink-soft);font-size:13px}
  .ts{background:var(--paper);border:1px solid var(--bone);border-radius:12px;padding:16px}
  .ts textarea{width:100%;padding:11px 13px;border:1.5px solid var(--bone);border-radius:9px;font:inherit;font-size:14px;min-height:70px;box-sizing:border-box;margin-bottom:10px}
</style>
@endsection
@section('content')
<div class="ed">
  <h1>Welcome, {{ $employer->company }}</h1>
  <p class="hi">{{ $employer->email }}</p>

  @if(session('flash'))<div class="flash">{{ session('flash') }}</div>@endif

  <div class="credits">
    <div><div class="n">{{ $employer->job_credits }}</div><div class="l">job credit{{ $employer->job_credits === 1 ? '' : 's' }} available</div></div>
    <div class="cta">
      @if($employer->job_credits > 0)<a class="btn btn-rust" href="{{ route('employer.job.create') }}">Post a job →</a>@endif
      <a class="btn btn-ghost" href="{{ route('employers.pricing') }}">Buy credits</a>
    </div>
  </div>

  <div class="card">
    <h2>Buy more credits</h2>
    <div class="tiers">
      @foreach($tiers as $key => $t)
        <div class="tq">
          <h3>{{ $t['name'] }}</h3>
          <div class="p">@if($t['price']){{ $currency === 'AUD' ? 'A$' : '$' }}{{ number_format($t['price']) }} · {{ $t['credits'] }} {{ \Illuminate\Support\Str::plural('credit', $t['credits']) }}@else POA @endif</div>
          @if($key === 'top_shelf')
            <a class="btn btn-ink" href="#topshelf" style="font-size:13px;padding:8px 14px">Enquire</a>
          @else
            <form method="POST" action="{{ route('employer.buy', $key) }}">@csrf<button class="btn btn-rust" type="submit" style="border:none;cursor:pointer;font-size:13px;padding:8px 14px">Buy</button></form>
          @endif
        </div>
      @endforeach
    </div>
    @unless(config('rmc.stripe.live'))<p class="muted" style="margin-top:12px">Card payments are being switched on — for now a purchase sends head office a request to arrange it.</p>@endunless
  </div>

  <div class="card">
    <h2>Your listings</h2>
    @forelse($jobs as $j)
      <div class="jrow">
        <div>
          <div class="t">{{ $j->title }}</div>
          <div class="muted">{{ $j->location ?: '—' }}@if($j->applications_count) · {{ $j->applications_count }} applicant{{ $j->applications_count === 1 ? '' : 's' }}@endif</div>
          @if($j->status === 'rejected' && $j->reject_reason)<div class="muted" style="color:#a4283a">Reason: {{ $j->reject_reason }}</div>@endif
        </div>
        <span class="flag f-{{ $j->status }}">{{ $j->status === 'pending' ? 'Awaiting approval' : ucfirst($j->status) }}</span>
      </div>
    @empty
      <p class="muted">No listings yet. @if($employer->job_credits > 0)<a href="{{ route('employer.job.create') }}" style="color:var(--rust);font-weight:700">Post your first job →</a>@else Buy a credit to get started.@endif</p>
    @endforelse
  </div>

  <div class="card" id="topshelf">
    <h2>Top Shelf enquiry</h2>
    <p class="muted" style="margin-bottom:10px">Hiring all year? Get bulk credits, featured slots and social amplification — priced for your brand.</p>
    <form class="ts" method="POST" action="{{ route('employer.enquire') }}">@csrf
      <textarea name="note" placeholder="Tell us a bit about your hiring needs (optional)"></textarea>
      <button class="btn btn-ink" type="submit">Send enquiry</button>
    </form>
  </div>

  @if($purchases->isNotEmpty())
    <div class="card">
      <h2>Purchase history</h2>
      @foreach($purchases as $p)
        <div class="jrow">
          <div><div class="t">{{ config('rmc.external_jobs.tiers.'.$p->tier.'.name', ucfirst($p->tier)) }}</div><div class="muted">{{ $p->created_at?->format('j M Y') }}@if($p->amount) · A${{ number_format($p->amount) }}@endif</div></div>
          <span class="flag {{ $p->status === 'paid' ? 'f-approved' : 'f-pending' }}">{{ ucfirst($p->status) }}</span>
        </div>
      @endforeach
    </div>
  @endif

  <p style="text-align:center"><form method="POST" action="{{ route('employer.logout') }}" style="display:inline">@csrf<button type="submit" class="muted" style="background:none;border:none;cursor:pointer;text-decoration:underline">Log out</button></form></p>
</div>
@endsection
