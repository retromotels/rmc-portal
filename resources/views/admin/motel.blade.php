@extends('layouts.admin')
@section('title', $member->motel ?: 'Motel')
@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;gap:12px;flex-wrap:wrap">
  <a class="btn btn-ghost sm" href="{{ route('admin.motels') }}">← All motels</a>
  <a class="btn btn-teal sm" href="{{ route('admin.images.index', $member) }}">🖼️ Website &amp; booking images</a>
</div>

<div class="grid g2">
  <div class="card">
    <div class="lbl">Profile</div><h3 style="margin:4px 0 12px">{{ $member->motel ?: $member->name }}</h3>
    @foreach([
        'Owner' => $member->name, 'Email' => $member->email,
        'Location' => $member->loc ?: '—', 'Room band' => $member->band()['label'],
        'Tier' => $member->tierMeta()['name'],
        'Details' => $member->details_complete ? 'Complete' : 'Pending',
        'Registration' => $member->overallPct() . '%',
        'Member since' => $member->created_at?->format('j M Y'),
      ] as $k => $v)
      <div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--line)"><span class="sub">{{ $k }}</span><b style="text-align:right">{{ $v }}</b></div>
    @endforeach
  </div>

  <div class="card">
    <div class="lbl">Registration sections</div>
    <div style="margin-top:12px;display:flex;flex-direction:column;gap:9px">
      @foreach($sections as $s)
        @php $pct = $member->sectionPct($s['id']); @endphp
        <div style="display:flex;align-items:center;gap:10px;font-size:13.5px">
          <span>{{ $s['icon'] }}</span><span style="flex:1">{{ $s['id'] }}. {{ $s['title'] }}</span>
          <span class="flag {{ $pct >= 100 ? 'current' : ($pct > 0 ? 'due' : 'none') }}">{{ $pct >= 100 ? '✓' : $pct . '%' }}</span>
        </div>
      @endforeach
    </div>
  </div>
</div>

<div class="section-title"><h3>Signed policy documents</h3><div class="rule"></div></div>
<div class="card">
  @forelse($member->policyDocuments as $pd)
    <div class="policy-doc">
      <span class="pf">PDF</span>
      <div style="flex:1;min-width:0"><b style="font-family:Oswald;font-size:14px">{{ $pd->title }}</b>
        <div class="sub" style="font-size:12px">✍️ Digitally accepted by {{ $pd->accepted_name }} · {{ $pd->accepted_at?->format('j M Y, g:ia') }}</div></div>
      <a href="{{ route('admin.policy.download', $pd) }}">Download</a>
    </div>
  @empty
    <div class="sub">No stored policy documents for this member.</div>
  @endforelse
</div>

<div class="section-title"><h3>Registration details &amp; uploads</h3><div class="rule"></div></div>
@foreach($sections as $s)
  @php $data = $member->sectionData($s['id']); @endphp
  <div class="card" style="margin-bottom:12px">
    <div class="lbl">{{ $s['icon'] }} {{ $s['id'] }}. {{ $s['title'] }} — {{ $member->sectionPct($s['id']) }}%</div>
    <div style="margin-top:8px">
      @foreach($s['fields'] as $f)
        @if($f['type'] === 'file')
          @foreach($member->filesFor($s['id'], $f['id']) as $file)
            <div class="frow"><span class="fx">{{ strtoupper(pathinfo($file->original_name, PATHINFO_EXTENSION) ?: 'FILE') }}</span>
              <span class="fn">{{ $file->original_name }}</span><span class="sub">{{ $file->humanSize() }}</span>
              <a href="{{ route('admin.upload.download', $file) }}">Download</a></div>
          @endforeach
        @elseif(!empty($data[$f['id']]))
          <div style="display:flex;gap:10px;padding:5px 0;border-bottom:1px solid var(--line);font-size:13px"><span class="sub" style="flex:1">{{ $f['label'] }}</span><b style="flex:1;text-align:right">{{ $data[$f['id']] }}</b></div>
        @endif
      @endforeach
    </div>
  </div>
@endforeach

@php $childCount = is_null($member->account_id) ? \App\Models\User::where('account_id', $member->id)->count() : 0; @endphp
<div class="section-title"><h3 style="color:#b23a48">Danger zone</h3><div class="rule"></div></div>
<div class="card" style="border:1px solid #f0b8c0;background:#fff7f8">
  <div class="lbl" style="color:#b23a48">Delete this motel</div>
  <p class="sub" style="margin:6px 0 12px;line-height:1.6">Permanently removes this profile and account — including its registration details, uploaded files, signed policy documents and chat widget. This cannot be undone.@if($childCount) <b>It will also delete its {{ $childCount }} linked {{ $childCount === 1 ? 'property' : 'properties' }}.</b>@endif</p>
  <form method="POST" action="{{ route('admin.motel.delete', $member) }}" onsubmit="return confirm('Delete this motel permanently? This cannot be undone.')">
    @csrf @method('DELETE')
    <div style="max-width:380px">
      <span class="sub">Type <b>{{ $member->motel ?: $member->name }}</b> to confirm</span>
      <input type="text" name="confirm" autocomplete="off" placeholder="{{ $member->motel ?: $member->name }}" style="display:block;width:100%;padding:9px 11px;border:1px solid #e2c2c8;border-radius:8px;margin-top:5px;font-size:14px">
    </div>
    <button type="submit" style="margin-top:11px;background:#c0392b;color:#fff;border:none;border-radius:9px;padding:10px 22px;font-weight:700;font-family:Oswald,sans-serif;letter-spacing:.5px;cursor:pointer">Delete permanently</button>
  </form>
</div>
@endsection
