@extends('layouts.admin')
@section('title', 'Community')
@section('content')
<style>
  .cm-h{font-family:Oswald,sans-serif;font-size:24px;margin:0 0 3px}
  .cm-sub{font-size:13px;color:#8a7d68;margin-bottom:16px}
  .kc-row{display:flex;gap:14px;flex-wrap:wrap;margin-bottom:22px}
  .kc{background:var(--paper,#fff);border-radius:12px;padding:14px 20px;box-shadow:var(--shadow,0 6px 20px rgba(0,0,0,.06));min-width:120px}
  .kc .n{font-family:Oswald,sans-serif;font-size:28px}.kc .l{font-size:12px;color:#8a7d68}
  .sect{font-family:Oswald,sans-serif;font-size:16px;margin:0 0 12px;letter-spacing:.3px}
  .m-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:12px;margin-bottom:26px}
  .m-card{background:var(--paper,#fff);border-radius:12px;padding:14px 16px;box-shadow:var(--shadow,0 6px 20px rgba(0,0,0,.06));display:flex;gap:11px;align-items:center}
  .av{width:42px;height:42px;border-radius:50%;object-fit:cover;flex:none;background:#FFC078;display:grid;place-items:center;font-family:Oswald,sans-serif;font-weight:700;color:#1F2933}
  .m-name{font-weight:700;font-size:14px}
  .m-meta{font-size:12px;color:#8a7d68}
  .tbl{width:100%;border-collapse:collapse;font-size:13.5px;background:var(--paper,#fff);border-radius:12px;overflow:hidden;box-shadow:var(--shadow,0 6px 20px rgba(0,0,0,.06))}
  .tbl th{text-align:left;padding:11px 14px;font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#8a7d68;background:#faf5ea}
  .tbl td{padding:11px 14px;border-top:1px solid #f2ebdd;vertical-align:middle}
  .tbl tr:hover td{background:#fbf7ee}
  .t-title{font-weight:700;color:#3a3540;text-decoration:none}
  .t-cat{font-size:11px;font-weight:700;color:#2f6f76}
  .lk{color:#33507a;text-decoration:none;font-weight:700;font-size:12.5px}
  .empty{padding:24px;text-align:center;color:#8a7d68}
</style>

@if(session('status'))<div class="status">{{ session('status') }}</div>@endif

<h1 class="cm-h">Community</h1>
<div class="cm-sub">Who's joined and every conversation happening in the member forum.</div>

<div class="kc-row">
  <div class="kc"><div class="n">{{ $stats['members'] }}</div><div class="l">motels joined</div></div>
  <div class="kc"><div class="n">{{ $stats['threads'] }}</div><div class="l">threads</div></div>
  <div class="kc"><div class="n">{{ $stats['replies'] }}</div><div class="l">replies</div></div>
</div>

<h2 class="sect">Motels in the community</h2>
<div class="m-grid">
  @forelse($members as $m)
    <div class="m-card">
      @if($m->photoUrl())<img class="av" src="{{ $m->photoUrl() }}" alt="">@else<span class="av">{{ $m->initials() }}</span>@endif
      <div>
        <div class="m-name">{{ $m->display_name }}</div>
        <div class="m-meta">{{ $m->town ?: '—' }} · {{ $m->threads_count }} {{ \Illuminate\Support\Str::plural('post', $m->threads_count) }} · joined {{ $m->created_at?->format('j M') }}</div>
      </div>
    </div>
  @empty
    <div class="empty">No motels have joined yet.</div>
  @endforelse
</div>

<h2 class="sect">Conversations</h2>
<table class="tbl">
  <thead><tr><th>Thread</th><th>Category</th><th>Started by</th><th>Replies</th><th>Last activity</th><th></th></tr></thead>
  <tbody>
    @forelse($threads as $t)
      <tr>
        <td><a class="t-title" href="{{ route('admin.community.thread', $t) }}">{{ $t->title }}</a>@if($t->pinned) 📌@endif</td>
        <td class="t-cat">{{ $t->categoryLabel() }}</td>
        <td>{{ $t->author?->display_name ?? '—' }}</td>
        <td>{{ $t->replies_count }}</td>
        <td style="color:#8a7d68;white-space:nowrap">{{ $t->last_reply_at?->diffForHumans() }}</td>
        <td><a class="lk" href="{{ route('admin.community.thread', $t) }}">Open →</a></td>
      </tr>
    @empty
      <tr><td colspan="6" class="empty">No conversations yet.</td></tr>
    @endforelse
  </tbody>
</table>

@if($threads->hasPages())
  <div style="display:flex;gap:14px;align-items:center;justify-content:center;margin-top:16px;font-size:13px">
    @if(!$threads->onFirstPage())<a href="{{ $threads->previousPageUrl() }}" style="color:#2f6f76;font-weight:700;text-decoration:none">← Prev</a>@endif
    <span style="color:#8a7d68">Page {{ $threads->currentPage() }} of {{ $threads->lastPage() }}</span>
    @if($threads->hasMorePages())<a href="{{ $threads->nextPageUrl() }}" style="color:#2f6f76;font-weight:700;text-decoration:none">Next →</a>@endif
  </div>
@endif
@endsection
