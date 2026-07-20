@extends('layouts.admin')
@section('title', 'Overview')
@section('content')
@php
    $avg = $members->count() ? (int) round($members->avg(fn ($m) => $m->overallPct())) : 0;
@endphp
<div class="grid g3" style="margin-bottom:22px">
  <div class="card"><div class="lbl">Registered motels</div><div class="stat teal">{{ $members->count() }}</div><div class="sub">signed up</div></div>
  <div class="card"><div class="lbl">Details pending</div><div class="stat {{ $pending ? 'red' : 'teal' }}">{{ $pending }}</div><div class="sub">{{ $pending ? 'sign-ups to chase' : 'all completed' }}</div></div>
  <div class="card"><div class="lbl">Avg registration</div><div class="stat purple">{{ $avg }}%</div><div class="sub">across members</div></div>
</div>

<div class="section-title"><h3>Signed up, details pending</h3><div class="rule"></div></div>
<div class="card">
  @php $rows = $members->where('details_complete', false); @endphp
  @forelse($rows as $m)
    <div style="display:flex;align-items:center;gap:10px;font-size:13.5px;padding:6px 0;border-bottom:1px solid var(--line)">
      <span style="flex:1">{{ $m->motel ?: $m->name }}<br><span class="sub">{{ $m->email }}</span></span>
      <span class="flag overdue">Pending</span>
      <a href="{{ route('admin.motel', $m) }}">View →</a>
    </div>
  @empty
    <div class="sub">Everyone has completed their details. 🎉</div>
  @endforelse
</div>
@endsection
