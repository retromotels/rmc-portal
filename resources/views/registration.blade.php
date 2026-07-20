@extends('layouts.portal')
@section('title', 'Property Setup')
@section('content')

<div class="prose"><p>Everything you supply once, reused for OTA packs and supplier tenders. All uploads are optional. Tap a section to open its form.</p></div>

<div style="display:flex;align-items:center;gap:14px;margin:14px 0 20px">
  <div style="flex:1;height:12px;background:#efe4d2;border-radius:7px;overflow:hidden"><div style="height:100%;width:{{ $user->overallPct() }}%;background:linear-gradient(90deg,var(--teal),var(--aqua))"></div></div>
  <b style="font-family:Oswald;font-size:15px">{{ $user->overallPct() }}% complete</b>
</div>

@foreach($sections as $s)
  @php $pct = $user->sectionPct($s['id']); $ok = $pct >= 100; @endphp
  <details class="doc" @if($open === $s['id']) open @endif>
    <summary>
      <div class="di">{{ $s['icon'] }}</div>
      <div class="dt"><b>{{ $s['id'] }}. {{ $s['title'] }} @if($s['signup'] ?? false)<span class="badge" style="background:#e7f4f4;color:var(--teal-d)">From sign-up</span>@endif</b>
        <small>{{ $s['note'] ?? ($s['signup'] ?? false ? 'Collected during sign-up — edit anytime.' : '') }}</small></div>
      @if($ok)<span class="flag current">✓ Complete</span>
      @elseif($s['priority'] ?? false)<span class="flag due">This week</span>
      @else<span class="flag none">{{ $pct }}%</span>@endif
    </summary>
    <div class="doc-body">
      <form method="POST" action="{{ route('registration.save', $s['id']) }}" style="padding-top:12px">
        @csrf
        @foreach($s['fields'] as $f)
          @if($f['type'] === 'file')
            <label class="fld"><span>{{ $f['label'] }} <span class="sub">(optional)</span></span></label>
            @foreach($user->filesFor($s['id'], $f['id']) as $file)
              <div class="frow">
                <span class="fx">{{ strtoupper(pathinfo($file->original_name, PATHINFO_EXTENSION) ?: 'FILE') }}</span>
                <span class="fn">{{ $file->original_name }}</span>
                <span class="sub">{{ $file->humanSize() }}</span>
                <a href="{{ route('upload.download', $file) }}">Download</a>
              </div>
            @endforeach
          @else
            @include('partials.field', ['f' => $f, 'data' => $user->sectionData($s['id'])])
          @endif
        @endforeach
        <button class="btn btn-primary" style="margin-top:8px">Save {{ $s['title'] }}</button>
      </form>

      {{-- Uploads are separate multipart forms so they can be added independently --}}
      @foreach($s['fields'] as $f)
        @if($f['type'] === 'file')
          <form method="POST" action="{{ route('registration.upload', [$s['id'], $f['id']]) }}" enctype="multipart/form-data" style="margin-top:12px;display:flex;gap:10px;align-items:center">
            @csrf
            <input type="file" name="file[]" @if($f['multi'] ?? false) multiple @endif style="flex:1">
            <button class="btn btn-teal sm" type="submit">Upload</button>
          </form>
        @endif
      @endforeach
    </div>
  </details>
@endforeach
@endsection
