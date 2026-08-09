@extends('layouts.portal')
@section('title', 'FAQ')
@section('content')
<style>
  .faq-wrap{max-width:760px}
  .faq-item{border:1px solid #ece2cf;border-radius:12px;margin-bottom:10px;background:#fff;overflow:hidden}
  .faq-item summary{cursor:pointer;list-style:none;padding:15px 18px;font-weight:600;font-size:15px;color:var(--ink,#2d2837);display:flex;justify-content:space-between;align-items:center}
  .faq-item summary::-webkit-details-marker{display:none}
  .faq-item summary:after{content:'+';font-size:20px;color:#b0a189}
  .faq-item[open] summary:after{content:'–'}
  .faq-item .a{padding:0 18px 16px;font-size:14.5px;line-height:1.7;color:#4a4453}
</style>

<div class="faq-wrap">
  @forelse($faq as $item)
    <details class="faq-item">
      <summary>{{ $item['q'] }}</summary>
      <div class="a">{!! nl2br(e($item['a'] ?? '')) !!}</div>
    </details>
  @empty
    <div class="dp-note">No FAQs have been added yet — check back soon.</div>
  @endforelse
</div>
@endsection
