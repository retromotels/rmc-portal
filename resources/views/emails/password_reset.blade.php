@extends('emails.layout')
@section('content')
<h1 style="font-size:20px;margin:0 0 14px">Reset your password</h1>
<p style="margin:0 0 14px">Hi {{ $user->name }}, we received a request to reset the password on your Retro Motel Collective account.</p>
<p style="margin:0 0 20px"><a href="{{ $url }}" style="display:inline-block;background:#ee6a5a;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:bold">Reset password →</a></p>
<p style="margin:0 0 14px;font-size:13px;color:#8a7d68">This link expires in 60 minutes. If the button doesn’t work, copy this URL into your browser:<br>{{ $url }}</p>
<p style="margin:0">If you didn’t request this, you can safely ignore this email — your password won’t change.</p>
@endsection
