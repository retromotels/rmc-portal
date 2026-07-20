@extends('layouts.admin')
@section('title', 'Signed Policies')
@section('content')
@php $signed = $members->filter(fn ($m) => $m->policyDocuments->count())->count(); @endphp
<div class="prose"><p>Digitally-accepted policy documents for every registered motel — Privacy &amp; Data Protection, Terms of Membership, and Member Authority. Each PDF is stamped with the accepting person's name and the exact date &amp; time of acceptance. <b>{{ $signed }}</b> of <b>{{ $members->count() }}</b> members have all three on file.</p></div>

@forelse($members as $m)
  <div class="card" style="margin-bottom:14px">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
      <div><b style="font-family:Oswald;font-size:16px">{{ $m->motel ?: $m->name }}</b>
        <div class="sub">{{ $m->name }} · {{ $m->email }} · signed up {{ $m->created_at?->format('j M Y') }}</div></div>
      @if($m->policyDocuments->count())<span class="flag current">✓ {{ $m->policyDocuments->count() }} signed</span>@else<span class="flag overdue">No records</span>@endif
    </div>
    @forelse($m->policyDocuments as $pd)
      <div class="policy-doc">
        <span class="pf">PDF</span>
        <div style="flex:1;min-width:0"><b style="font-family:Oswald;font-size:14px">{{ $pd->title }}</b>
          <div class="sub" style="font-size:12px">✍️ Digitally accepted by {{ $pd->accepted_name }} · {{ $pd->accepted_at?->format('j M Y, g:ia') }}</div></div>
        <a href="{{ route('admin.policy.download', $pd) }}">Download</a>
      </div>
    @empty
      <div class="sub">No stored policy documents.</div>
    @endforelse
  </div>
@empty
  <div class="card"><div class="sub">No registered motels yet.</div></div>
@endforelse
@endsection
