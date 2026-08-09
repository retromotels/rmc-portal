@extends('layouts.portal')
@section('title', 'Add a property')
@section('content')
<div class="card" style="max-width:560px">
  <h3 style="margin-bottom:6px">Add another property</h3>
  <p class="sub" style="margin-bottom:16px">Add a second (or third…) property to your account. You’ll then complete its details, and can switch between properties any time from the top bar.</p>
  <form method="POST" action="{{ route('properties.store') }}">
    @csrf
    <label class="fld"><span>Property / motel name</span><input name="motel" value="{{ old('motel') }}" required></label>
    <label class="fld"><span>Total rooms / units</span><input name="rooms" type="number" min="0" value="{{ old('rooms') }}" placeholder="e.g. 24"></label>
    @foreach($errors->all() as $e)<div class="err">{{ $e }}</div>@endforeach
    <button class="btn btn-primary btn-block" type="submit">Add property &amp; continue →</button>
  </form>
</div>
@endsection
