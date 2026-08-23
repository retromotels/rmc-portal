@extends('jobs.public.layout')
@section('title', 'My applications')
@section('head')
<style>
  .dash{max-width:760px;margin:38px auto;padding:0 22px}
  .dash h1{font-family:var(--serif);font-size:34px;font-weight:700;margin-bottom:2px}
  .dash .hi{font-size:14px;color:var(--ink-soft);margin-bottom:20px}
  .arow{background:#fff;border:1px solid var(--bone);border-radius:14px;padding:16px 18px;box-shadow:0 8px 24px rgba(31,41,51,.06);margin-bottom:11px;display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;text-decoration:none;color:inherit}
  .arow:hover{border-color:var(--peach)}
  .a-title{font-family:var(--serif);font-size:20px;font-weight:700}
  .a-prop{font-size:13px;color:var(--rust-ink);font-weight:600}
  .a-when{font-size:12px;color:var(--ink-soft);margin-top:3px}
  .a-stat{font-size:11.5px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;padding:4px 11px;border-radius:20px;background:var(--butter);color:var(--rust-ink)}
  .empty{background:#fff;border:1px solid var(--bone);border-radius:14px;padding:40px;text-align:center;color:var(--ink-soft)}
</style>
@endsection
@section('content')
<div class="dash">
  <h1>My applications</h1>
  <p class="hi">Signed in as {{ $seeker->name }} · {{ $seeker->email }}</p>

  @forelse($apps as $app)
    <a class="arow" href="{{ $app->job ? route('jobs.public.show', $app->job->slug) : route('jobs.board') }}">
      <div>
        <div class="a-title">{{ $app->job->title ?? 'Role removed' }}</div>
        <div class="a-prop">{{ $app->job?->employerName() }}@if($app->job && $app->job->location) · {{ $app->job->location }}@endif</div>
        <div class="a-when">Applied {{ $app->created_at?->format('j M Y') }}</div>
      </div>
      <span class="a-stat">{{ $app->status }}</span>
    </a>
  @empty
    <div class="empty">You haven't applied to any roles yet. <a href="{{ route('jobs.board') }}" style="color:var(--rust);font-weight:700">Browse open jobs →</a></div>
  @endforelse
</div>
@endsection
