@extends('layouts.admin')
@section('title', 'AI Assistant')
@section('content')
<style>
  .ai-h{font-family:Oswald,sans-serif;font-size:24px;margin:0 0 2px}
  .ai-sub{font-size:13px;color:#8a7d68;margin-bottom:14px}
  .ai-wrap{background:var(--paper,#fff);border-radius:14px;box-shadow:var(--shadow,0 6px 20px rgba(0,0,0,.06));display:flex;flex-direction:column;height:min(640px,74vh);overflow:hidden}
  .ai-stream{flex:1;overflow-y:auto;padding:20px 22px;display:flex;flex-direction:column;gap:14px}
  .msg{max-width:86%;padding:12px 15px;border-radius:14px;font-size:14px;line-height:1.6;white-space:pre-wrap}
  .msg.user{align-self:flex-end;background:#2f6f76;color:#fff;border-bottom-right-radius:4px}
  .msg.bot{align-self:flex-start;background:#f6efe2;color:#2a2530;border-bottom-left-radius:4px}
  .ai-empty{margin:auto;text-align:center;color:#8a7d68;max-width:460px}
  .ai-empty h3{font-family:Oswald,sans-serif;font-size:18px;color:#3a3540;margin:0 0 8px}
  .chip{display:inline-block;margin:4px;padding:8px 12px;border:1px solid #e2d6c2;border-radius:20px;font-size:13px;cursor:pointer;background:#fbf6ec}
  .chip:hover{border-color:#2f6f76}
  .ai-form{display:flex;gap:10px;padding:14px;border-top:1px solid #ece1cd;background:#fbf6ec}
  .ai-form textarea{flex:1;padding:11px 13px;border:1.5px solid #e2d6c2;border-radius:10px;font:inherit;font-size:14px;resize:none;height:46px;box-sizing:border-box}
  .ai-send{background:#2f6f76;color:#fff;border:none;border-radius:10px;padding:0 22px;font-weight:700;cursor:pointer}
  .ai-send:disabled{opacity:.6;cursor:default}
  .warn{background:#fdf0d5;border:1px solid #f0d79a;border-radius:10px;padding:12px 14px;font-size:13.5px;color:#8a6d1c;margin-bottom:14px}
  .ai-note{font-size:12px;color:#8a7d68;margin-top:10px}
</style>

<h1 class="ai-h">🤖 AI Assistant</h1>
<p class="ai-sub">Ask about anything in the collective — members, jobs, applicants, the community, requests. It reads a live snapshot of the system each time.</p>

@unless($configured)
  <div class="warn">The AI isn't connected yet — add the Anthropic API key to the server and it'll start answering.</div>
@endunless

<div class="ai-wrap">
  <div class="ai-stream" id="stream">
    @forelse($history as $m)
      <div class="msg {{ $m['role'] === 'user' ? 'user' : 'bot' }}">{{ $m['content'] }}</div>
    @empty
      <div class="ai-empty" id="empty">
        <h3>What would you like to know?</h3>
        <div>
          <span class="chip" onclick="useChip(this)">Which motels haven't finished their registration?</span>
          <span class="chip" onclick="useChip(this)">Summarise the open requests and what needs actioning</span>
          <span class="chip" onclick="useChip(this)">How many live jobs and applicants do we have?</span>
          <span class="chip" onclick="useChip(this)">Who's most active in the community?</span>
        </div>
      </div>
    @endforelse
  </div>
  <form class="ai-form" id="form" onsubmit="return sendMsg(event)">
    <textarea id="input" placeholder="Ask the assistant…"></textarea>
    <button class="ai-send" id="send" type="submit">Send</button>
  </form>
</div>
<p class="ai-note">Reads live data at each question. AI can make mistakes — double-check anything important.</p>

<script>
const stream = document.getElementById('stream');
const REPORT_ID = @json($reportId);
function add(role, text){ var e=document.getElementById('empty'); if(e)e.remove(); var d=document.createElement('div'); d.className='msg '+(role==='user'?'user':'bot'); d.textContent=text; stream.appendChild(d); stream.scrollTop=stream.scrollHeight; return d; }
function useChip(el){ document.getElementById('input').value=el.textContent; document.getElementById('input').focus(); }
function post(payload, youText){
  var btn=document.getElementById('send'); btn.disabled=true;
  if(youText) add('user', youText);
  var thinking=add('bot','…');
  return fetch('{{ route('admin.ai.ask') }}', {method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},body:JSON.stringify(payload)})
    .then(r=>r.json()).then(d=>{ if(d.you && !youText){} thinking.textContent=d.reply||'No reply.'; btn.disabled=false; stream.scrollTop=stream.scrollHeight; })
    .catch(()=>{ thinking.textContent='Something went wrong — please try again.'; btn.disabled=false; });
}
function sendMsg(ev){ ev.preventDefault(); var inp=document.getElementById('input'); var msg=inp.value.trim(); if(!msg)return false; inp.value=''; post({message:msg}, msg); return false; }
document.getElementById('input').addEventListener('keydown', function(e){ if(e.key==='Enter'&&!e.shiftKey){ e.preventDefault(); document.getElementById('form').requestSubmit(); }});
if(REPORT_ID){ post({report_id: REPORT_ID}, 'Build a report on request #'+REPORT_ID); }
</script>
@endsection
