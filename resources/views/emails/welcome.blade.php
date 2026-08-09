@extends('emails.layout')
@section('content')
<h1 style="font-size:20px;margin:0 0 14px">Welcome, {{ $user->name }}</h1>
<p style="margin:0 0 14px">Thanks for registering <b>{{ $user->motel }}</b> with the Retro Motel Collective — we’re glad to have you.</p>
<p style="margin:0 0 14px">Your account is active. The next step is to complete your property details so we can set up your membership and start unlocking member rates and group benefits.</p>
<p style="margin:0 0 20px"><a href="{{ url('/dashboard') }}" style="display:inline-block;background:#ee6a5a;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:bold">Complete your details →</a></p>
<p style="margin:0 0 14px">Your three membership documents (Privacy &amp; Data Protection, Terms of Membership, and Member Authority) are saved to your account.</p>
<p style="margin:0">— The RMC team</p>
@endsection
