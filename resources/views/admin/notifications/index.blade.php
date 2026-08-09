@extends('layouts.admin')
@section('title', 'Notifications')
@section('content')
<style>
  .nt-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:14px}
  .nt{display:flex;gap:14px;align-items:flex-start;background:var(--paper,#fff);border-radius:12px;padding:14px 16px;margin-bottom:10px;box-shadow:var(--shadow,0 6px 20px rgba(0,0,0,.06))}
  .nt.unread{border-left:4px solid var(--coral,#ee6a5a)}
  .nt .ic{font-size:20px}
  .nt .m{flex:1;min-width:0}
  .nt b{font-family:Oswald,sans-serif;font-size:15px}
  .nt .sub{font-size:12.5px;color:#8a7d68}
  .tag{display:inline-block;font-size:10px;font-weight:700;letter-spacing:.3px;padding:2px 7px;border-radius:5px;text-transform:uppercase;background:#eee6d6;color:#7a6f59;margin-left:6px}
  .lite{border:1px solid #e2d6c2;background:#fff;border-radius:8px;padding:7px 11px;font-size:12.5px;font-weight:600;color:#4a4453;cursor:pointer;text-decoration:none}
  @php $icons = ['health_request'=>'🩺','website_check'=>'🔍','new_signup'=>'🎉','property_claimed'=>'✅']; @endphp
</style>

<div class="nt-head">
  <div class="prose"><p style="margin:0">Alerts from the portal — health-check requests, website checks and new properties. Follow up, then mark them read.</p></div>
  <form method="POST" action="{{ route('admin.notifications.readAll') }}">@csrf<button class="lite" type="submit">Mark all read</button></form>
</div>

@if(session('status'))<div class="status">{{ session('status') }}</div>@endif

@forelse($notifications as $n)
  <div class="nt {{ $n->read_at ? '' : 'unread' }}">
    <span class="ic">{{ $icons[$n->type] ?? '🔔' }}</span>
    <div class="m">
      <b>{{ $n->title }}</b><span class="tag">{{ str_replace('_', ' ', $n->type) }}</span>
      @if($n->body)<div class="sub">{{ $n->body }}</div>@endif
      <div class="sub">{{ $n->created_at?->diffForHumans() }}@if($n->user) · <a href="{{ route('admin.motel', $n->user) }}">view property →</a>@endif</div>
    </div>
    @unless($n->read_at)
      <form method="POST" action="{{ route('admin.notifications.read', $n) }}">@csrf<button class="lite" type="submit">Mark read</button></form>
    @endunless
  </div>
@empty
  <div class="nt"><div class="m"><span class="sub">No notifications yet.</span></div></div>
@endforelse
@endsection
