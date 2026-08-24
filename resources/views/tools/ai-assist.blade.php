@extends('layouts.portal')
@section('title', 'AI Assist')
@section('content')
<style>
  .ai-h{font-family:Oswald,sans-serif;font-size:26px;margin:0 0 3px}
  .ai-sub{color:#8a7d68;font-size:14px;margin-bottom:16px}
  .ai-wrap{background:#fff;border:1px solid #ece1cd;border-radius:16px;box-shadow:0 6px 20px rgba(0,0,0,.05);display:flex;flex-direction:column;height:min(620px,72vh);overflow:hidden}
  .ai-stream{flex:1;overflow-y:auto;padding:20px 22px;display:flex;flex-direction:column;gap:14px}
  .msg{max-width:82%;padding:12px 15px;border-radius:14px;font-size:14.5px;line-height:1.55;white-space:pre-wrap}
  .msg.user{align-self:flex-end;background:#2f6f76;color:#fff;border-bottom-right-radius:4px}
  .msg.bot{align-self:flex-start;background:#f6efe2;color:#2a2530;border-bottom-left-radius:4px}
  .ai-empty{margin:auto;text-align:center;color:#8a7d68;max-width:420px}
  .ai-empty h3{font-family:Cormorant Garamond,serif;font-size:22px;color:#3a3540;margin:0 0 8px}
  .chip{display:inline-block;margin:4px;padding:8px 12px;border:1px solid #e2d6c2;border-radius:20px;font-size:13px;cursor:pointer;background:#fbf6ec}
  .chip:hover{border-color:#2f6f76}
  .ai-form{display:flex;gap:10px;padding:14px;border-top:1px solid #ece1cd;background:#fbf6ec}
  .ai-form textarea{flex:1;padding:11px 13px;border:1.5px solid #e2d6c2;border-radius:10px;font:inherit;font-size:14.5px;resize:none;height:46px;box-sizing:border-box}
  .ai-send{background:#e0491d;color:#fff;border:none;border-radius:10px;padding:0 22px;font-weight:700;cursor:pointer}
  .ai-send:disabled{opacity:.6;cursor:default}
  .ai-note{font-size:12px;color:#8a7d68;margin-top:10px}
  .warn{background:#fdf0d5;border:1px solid #f0d79a;border-radius:10px;padding:12px 14px;font-size:13.5px;color:#8a6d1c;margin-bottom:14px}
</style>

<h1 class="ai-h">✨ AI Assist</h1>
<p class="ai-sub">Your motel's helper — ask for guest replies, marketing copy, pricing ideas or a hand with day-to-day questions.</p>

@unless($configured)
  <div class="warn">AI Assist is switched on, but the assistant isn't connected yet — head office is adding the key that powers it. You can still open it; it'll start answering once that's done.</div>
@endunless

<div class="ai-wrap">
  <div class="ai-stream" id="stream">
    @forelse($history as $m)
      <div class="msg {{ $m['role'] === 'user' ? 'user' : 'bot' }}">{{ $m['content'] }}</div>
    @empty
      <div class="ai-empty" id="empty">
        <h3>What can I help with?</h3>
        <p style="font-size:13.5px">Try one of these, or just type your own.</p>
        <div>
          <span class="chip" onclick="useChip(this)">Write a warm reply to a guest asking for a late checkout</span>
          <span class="chip" onclick="useChip(this)">Draft an Instagram caption for a winter midweek deal</span>
          <span class="chip" onclick="useChip(this)">Ideas to lift my Booking.com review score</span>
        </div>
      </div>
    @endforelse
  </div>
  <form class="ai-form" id="form" onsubmit="return sendMsg(event)">
    <textarea id="input" placeholder="Ask AI Assist…" required></textarea>
    <button class="ai-send" id="send" type="submit">Send</button>
  </form>
</div>
<p class="ai-note">AI can make mistakes — check anything important before you send it to a guest.</p>

<script>
const stream = document.getElementById('stream');
function add(role, text){
  var e = document.getElementById('empty'); if(e) e.remove();
  var d = document.createElement('div'); d.className = 'msg ' + (role==='user'?'user':'bot'); d.textContent = text;
  stream.appendChild(d); stream.scrollTop = stream.scrollHeight; return d;
}
function useChip(el){ document.getElementById('input').value = el.textContent; document.getElementById('input').focus(); }
function sendMsg(ev){
  ev.preventDefault();
  var inp = document.getElementById('input'); var btn = document.getElementById('send');
  var msg = inp.value.trim(); if(!msg) return false;
  add('user', msg); inp.value=''; btn.disabled=true;
  var thinking = add('bot', '…');
  fetch('{{ route('tools.ai-assist.ask') }}', {
    method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},
    body: JSON.stringify({message: msg})
  }).then(r=>r.json()).then(d=>{ thinking.textContent = d.reply || 'Sorry, no reply.'; btn.disabled=false; inp.focus(); stream.scrollTop=stream.scrollHeight; })
  .catch(()=>{ thinking.textContent = 'Something went wrong — please try again.'; btn.disabled=false; });
  return false;
}
document.getElementById('input').addEventListener('keydown', function(e){ if(e.key==='Enter' && !e.shiftKey){ e.preventDefault(); document.getElementById('form').requestSubmit(); }});
</script>
@endsection
