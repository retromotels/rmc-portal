@extends('layouts.admin')
@section('title', 'Motels')
@section('content')
@php $pending = $members->where('details_complete', false)->count(); @endphp
<div class="prose"><p>Every motel that has registered.
  @if($pending)<b>{{ $pending }}</b> {{ $pending === 1 ? 'has' : 'have' }} signed up but not completed details — chase these to capture their info.@else All registered members have completed their details.@endif
</p></div>

<table class="tbl">
  <thead><tr><th>Motel</th><th>Owner</th><th class="c">Details</th><th>Tier</th><th class="c">Registration</th><th class="c">Signed up</th></tr></thead>
  <tbody>
    @foreach($members->sortBy('details_complete') as $m)
      <tr class="clickrow" onclick="location.href='{{ route('admin.motel', $m) }}'">
        <td><b>{{ $m->motel ?: '—' }}</b><br><span class="sub">{{ $m->loc }}</span></td>
        <td>{{ $m->name }}<br><span class="sub">{{ $m->email }}</span></td>
        <td class="c">@if($m->details_complete)<span class="flag current">✓ Done</span>@else<span class="flag overdue">Pending</span>@endif</td>
        <td>{{ $m->tierMeta()['name'] }}</td>
        <td class="c">{{ $m->overallPct() }}%</td>
        <td class="c">{{ $m->created_at?->format('j M Y') }}</td>
      </tr>
    @endforeach
  </tbody>
</table>
@endsection
