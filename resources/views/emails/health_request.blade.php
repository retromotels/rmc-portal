@extends('emails.layout')
@section('content')
<h1 style="font-size:20px;margin:0 0 14px">Health-check request</h1>
<p style="margin:0 0 14px"><b>{{ $user->motel ?: $user->name }}</b> has requested:</p>
<p style="margin:0 0 16px;font-size:17px;font-weight:bold;color:#ee6a5a">{{ $label }}</p>
<table style="width:100%;border-collapse:collapse;font-size:14px;margin:0 0 18px">
  <tr><td style="padding:6px 0;color:#8a7d68;width:110px">Contact</td><td style="padding:6px 0">{{ $user->name }}</td></tr>
  <tr><td style="padding:6px 0;color:#8a7d68">Email</td><td style="padding:6px 0">{{ $user->email }}</td></tr>
</table>
<p style="margin:0">Follow up with the property, then mark it done in the admin notifications.</p>
@endsection
