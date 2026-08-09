@extends('layouts.admin')
@section('title', 'Email preview')
@section('content')
<style>
  .em-meta{background:var(--paper,#fff);border-radius:12px;padding:16px 18px;box-shadow:var(--shadow,0 6px 20px rgba(0,0,0,.06));margin-bottom:16px;max-width:680px}
  .em-meta div{font-size:13.5px;margin:3px 0}
  .em-meta b{color:#8a7d68;font-weight:600;display:inline-block;width:80px}
  .em-frame{width:100%;max-width:680px;height:640px;border:1px solid #e2d6c2;border-radius:12px;background:#f3ede2}
  .lite{border:1px solid #e2d6c2;background:#fff;border-radius:9px;padding:9px 14px;font-weight:600;font-size:13px;color:#4a4453;text-decoration:none}
</style>

<p style="margin:0 0 14px"><a class="lite" href="{{ route('admin.outbox.index') }}">← Back to outbox</a></p>

<div class="em-meta">
  <div><b>Type</b> {{ str_replace('_', ' ', $email->template) }}</div>
  <div><b>To</b> {{ $email->to_email }}@if($email->to_name) ({{ $email->to_name }})@endif</div>
  <div><b>Subject</b> {{ $email->subject }}</div>
  <div><b>Status</b> {{ $email->status }} · {{ $email->created_at?->format('j M Y, g:ia') }}</div>
</div>

<iframe class="em-frame" srcdoc="{{ $email->body }}"></iframe>
@endsection
