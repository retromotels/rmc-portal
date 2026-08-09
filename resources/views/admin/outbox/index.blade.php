@extends('layouts.admin')
@section('title', 'Outbox')
@section('content')
<style>
  .ob-note{background:#fdf3e6;border:1px solid #f0d9b5;color:#8a5a1a;font-size:13px;padding:11px 14px;border-radius:10px;margin-bottom:16px}
  .ob-tbl{width:100%;border-collapse:collapse;font-size:13.5px;background:var(--paper,#fff);border-radius:12px;overflow:hidden;box-shadow:var(--shadow,0 6px 20px rgba(0,0,0,.06))}
  .ob-tbl th{text-align:left;padding:11px 14px;font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#8a7d68;background:#faf5ea}
  .ob-tbl td{padding:11px 14px;border-top:1px solid #f2ebdd;vertical-align:top}
  .ob-tbl tr:hover td{background:#fbf7ee}
  .tag{display:inline-block;font-size:10.5px;font-weight:700;letter-spacing:.3px;padding:2px 7px;border-radius:5px;text-transform:uppercase;background:#eee6d6;color:#7a6f59}
  .st{font-size:11px;font-weight:700;text-transform:uppercase}
  .st-queued{color:#c07f16}.st-sent{color:#1c7a45}.st-failed{color:#b23b2e}
  .ob-tbl a{color:var(--teal-d,#2f6f7e);font-weight:600;text-decoration:none}
</style>

<div class="ob-note">📭 These are the emails the system has generated. Sending isn’t connected yet — everything is <b>queued</b> so you can preview it. Once SendGrid is wired up, queued messages will actually send.</div>

<table class="ob-tbl">
  <thead><tr><th>When</th><th>Type</th><th>To</th><th>Subject</th><th>Status</th><th></th></tr></thead>
  <tbody>
  @forelse($emails as $e)
    <tr>
      <td style="white-space:nowrap;color:#8a7d68">{{ $e->created_at?->format('j M, g:ia') }}</td>
      <td><span class="tag">{{ str_replace('_', ' ', $e->template) }}</span></td>
      <td>{{ $e->to_email }}</td>
      <td>{{ \Illuminate\Support\Str::limit($e->subject, 52) }}</td>
      <td><span class="st st-{{ $e->status }}">{{ $e->status }}</span></td>
      <td><a href="{{ route('admin.outbox.show', $e) }}">Preview →</a></td>
    </tr>
  @empty
    <tr><td colspan="6" style="color:#8a7d68;padding:20px 14px">No emails yet. They’ll appear here as properties sign up, request health checks, or reset passwords.</td></tr>
  @endforelse
  </tbody>
</table>
@endsection
