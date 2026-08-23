@extends('layouts.admin')
@section('title', 'Applicants')
@section('content')
<style>
  .sk-head{display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:12px;margin-bottom:14px}
  .sk-head h1{font-family:Oswald,sans-serif;font-size:24px;margin:0}
  .sk-sub{font-size:13px;color:#8a7d68;margin-top:3px}
  .sk-filters{display:flex;gap:9px;flex-wrap:wrap;margin-bottom:16px}
  .sk-filters input,.sk-filters select{padding:9px 12px;border:1px solid #e2d6c2;border-radius:9px;font:inherit;font-size:13.5px;background:#fff}
  .sk-filters input{min-width:220px}
  .sk-btn{background:#2f6f76;color:#fff;border:none;border-radius:9px;padding:9px 16px;font-weight:700;cursor:pointer;font-family:Oswald,sans-serif;letter-spacing:.4px}
  .sk-clear{align-self:center;font-size:13px;color:#8a7d68;text-decoration:none}
  .sk-wrap{background:var(--paper,#fff);border-radius:13px;box-shadow:var(--shadow,0 6px 20px rgba(0,0,0,.06));overflow:hidden}
  table.sk{width:100%;border-collapse:collapse;font-size:13.5px}
  table.sk th{text-align:left;font-family:Oswald,sans-serif;font-weight:600;font-size:12px;letter-spacing:.5px;text-transform:uppercase;color:#8a7d68;padding:12px 14px;border-bottom:1px solid #efe4d2;background:#fbf6ec}
  table.sk td{padding:12px 14px;border-bottom:1px solid #f2ead9;vertical-align:top}
  table.sk tr:last-child td{border-bottom:none}
  .sk-name{font-weight:700;color:#3a3540}
  .sk-email{color:#33507a;text-decoration:none}
  .sk-email:hover{text-decoration:underline}
  .sk-state{font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;background:#eef3f3;color:#2f6f76;white-space:nowrap}
  .sk-none{color:#b3a68f}
  .sk-roles{color:#6a6152;font-size:12.5px;line-height:1.5}
  .sk-cv{display:inline-block;background:#2e8b57;color:#fff;border-radius:8px;padding:6px 12px;font-weight:700;font-size:12.5px;text-decoration:none}
  .sk-nocv{font-size:12.5px;color:#b3a68f}
  .sk-empty{padding:40px;text-align:center;color:#8a7d68}
  .sk-chip{font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;background:#fdf0d5;color:#9a6a10;white-space:nowrap}
</style>

<div class="sk-head">
  <div>
    <h1>Applicants</h1>
    <div class="sk-sub">{{ number_format($total) }} registered {{ $total === 1 ? 'account' : 'accounts' }} · {{ number_format($withResume) }} with a resume on file</div>
  </div>
</div>

<form class="sk-filters" method="GET" action="{{ route('admin.seekers') }}">
  <input type="text" name="q" value="{{ $kw }}" placeholder="Search name or email…">
  <select name="state" onchange="this.form.submit()">
    <option value="">All states</option>
    @foreach(config('rmc.job_states') as $code => $lbl)
      <option value="{{ $code }}" @selected($state === $code)>{{ $code }} — {{ $lbl }}</option>
    @endforeach
  </select>
  <button class="sk-btn" type="submit">Search</button>
  @if($kw || $state)
    <a class="sk-clear" href="{{ route('admin.seekers') }}">Clear</a>
  @endif
</form>

<div class="sk-wrap">
  <table class="sk">
    <thead>
      <tr>
        <th>Name</th>
        <th>Email</th>
        <th>State</th>
        <th>Phone</th>
        <th>Applications</th>
        <th>Resume</th>
        <th>Joined</th>
      </tr>
    </thead>
    <tbody>
      @forelse($seekers as $s)
        @php $cvApp = $s->applications->first(fn ($a) => (bool) $a->cv_path); @endphp
        <tr>
          <td class="sk-name">{{ $s->name }}</td>
          <td><a class="sk-email" href="mailto:{{ $s->email }}">{{ $s->email }}</a></td>
          <td>@if($s->state)<span class="sk-state">{{ $s->state }}</span>@else<span class="sk-none">—</span>@endif</td>
          <td>{{ $s->phone ?: '—' }}</td>
          <td>
            @if($s->applications_count)
              <span class="sk-chip">{{ $s->applications_count }}</span>
              <div class="sk-roles">{{ $s->applications->take(4)->map(fn ($a) => $a->job?->title ?? 'Role')->implode(', ') }}@if($s->applications_count > 4) +{{ $s->applications_count - 4 }} more @endif</div>
            @else
              <span class="sk-none">None yet</span>
            @endif
          </td>
          <td>
            @if($cvApp)
              <a class="sk-cv" href="{{ route('admin.seeker.cv', $s) }}">⬇ Resume</a>
            @else
              <span class="sk-nocv">—</span>
            @endif
          </td>
          <td style="color:#8a7d68;white-space:nowrap">{{ $s->created_at?->format('j M Y') }}</td>
        </tr>
      @empty
        <tr><td colspan="7" class="sk-empty">@if($kw || $state)No applicants match those filters.@else No one has registered yet. Accounts appear here as job seekers sign up on the board.@endif</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

@if($seekers->hasPages())
  <div style="display:flex;gap:14px;align-items:center;justify-content:center;margin-top:18px">
    @if(!$seekers->onFirstPage())
      <a href="{{ $seekers->previousPageUrl() }}" style="text-decoration:none;color:#2f6f76;font-weight:700">← Prev</a>
    @endif
    <span style="font-size:13px;color:#8a7d68">Page {{ $seekers->currentPage() }} of {{ $seekers->lastPage() }}</span>
    @if($seekers->hasMorePages())
      <a href="{{ $seekers->nextPageUrl() }}" style="text-decoration:none;color:#2f6f76;font-weight:700">Next →</a>
    @endif
  </div>
@endif
@endsection
