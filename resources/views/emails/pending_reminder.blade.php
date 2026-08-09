@extends('emails.layout')
@section('content')
<h1 style="font-size:20px;margin:0 0 14px">Let’s finish your setup</h1>
<p style="margin:0 0 14px">Hi {{ $user->name }}, you registered <b>{{ $user->motel }}</b> with the Retro Motel Collective a little while ago, but your property details aren’t finished yet.</p>
<p style="margin:0 0 14px">It only takes a few minutes — and it’s what lets us set up your membership, member rates and group tenders. Log in and pick up where you left off:</p>
<p style="margin:0 0 20px"><a href="{{ url('/login') }}" style="display:inline-block;background:#ee6a5a;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:bold">Log in &amp; finish →</a></p>
<p style="margin:0">Need a hand? Just reply to this email and head office will help.</p>
@endsection
