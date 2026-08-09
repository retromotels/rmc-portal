@extends('emails.layout')
@section('content')
<h1 style="font-size:20px;margin:0 0 14px">New property signed up</h1>
<p style="margin:0 0 14px">A new property just registered on the member portal:</p>
<table style="width:100%;border-collapse:collapse;font-size:14px;margin:0 0 18px">
  <tr><td style="padding:6px 0;color:#8a7d68;width:110px">Property</td><td style="padding:6px 0"><b>{{ $user->motel ?: '—' }}</b></td></tr>
  <tr><td style="padding:6px 0;color:#8a7d68">Contact</td><td style="padding:6px 0">{{ $user->name }}</td></tr>
  <tr><td style="padding:6px 0;color:#8a7d68">Email</td><td style="padding:6px 0">{{ $user->email }}</td></tr>
  <tr><td style="padding:6px 0;color:#8a7d68">Registered</td><td style="padding:6px 0">{{ $user->created_at?->format('j M Y, g:ia') }}</td></tr>
</table>
<p style="margin:0"><a href="{{ url('/admin/motels') }}" style="display:inline-block;background:#ee6a5a;color:#ffffff;text-decoration:none;padding:11px 20px;border-radius:8px;font-weight:bold">View in admin →</a></p>
@endsection
