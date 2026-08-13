@if($cfg)
(function () {
  if (window.__rmcwLoaded) return;
  window.__rmcwLoaded = true;
  var CFG = {!! json_encode($cfg, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};
@verbatim
  function boot() {
    function esc(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
    function fmt(s){ return esc(s).replace(/\*\*([^*]+)\*\*/g, '<b>$1</b>'); }

    var primary = CFG.primary || '#1E7F86';
    var accent  = CFG.accent  || '#E8553D';
    var entries = (CFG.entries || []).map(function (e) {
      var keys = String(e.keys || '').split(',').map(function (k){ return k.trim().toLowerCase(); }).filter(Boolean);
      return { label: (e.label || '').trim(), keys: keys, answer: e.answer || '' };
    });
    var FALLBACK = "I'm not sure about that one — try one of the buttons below, or ask our reception team.";

    function answer(q) {
      var s = ' ' + q.toLowerCase().trim() + ' ', best = null, bs = 0;
      for (var i = 0; i < entries.length; i++) {
        var sc = 0, ks = entries[i].keys;
        for (var j = 0; j < ks.length; j++) { if (ks[j] && s.indexOf(ks[j]) !== -1) sc += ks[j].length; }
        if (sc > bs) { bs = sc; best = entries[i]; }
      }
      return best ? best.answer : FALLBACK;
    }

    var CSS = "*{margin:0;padding:0;box-sizing:border-box;font-family:'DM Sans','Avenir Next','Segoe UI',system-ui,sans-serif;}"
      + ".launch{width:60px;height:60px;border-radius:50%;border:none;cursor:pointer;background:__P__;color:#fff;font-size:26px;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 22px rgba(0,0,0,.3);transition:transform .15s;}"
      + ".launch:hover{transform:scale(1.06);}"
      + ".panel{position:absolute;right:0;bottom:74px;width:360px;height:540px;max-height:calc(100vh - 120px);background:#FFF7EC;border-radius:18px;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 18px 50px rgba(0,0,0,.35);transform:translateY(16px) scale(.96);opacity:0;pointer-events:none;transform-origin:bottom right;transition:transform .2s,opacity .2s;}"
      + ".wrap.open .panel{transform:none;opacity:1;pointer-events:auto;}"
      + ".wrap.open .launch{transform:scale(.9);}"
      + ".phead{background:__P__;color:#fff;padding:14px 15px;display:flex;align-items:center;gap:10px;}"
      + ".badge{width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;}"
      + ".htext{flex:1;line-height:1.2;}.htext strong{font-size:15.5px;display:block;}.htext span{font-size:11px;opacity:.85;}"
      + ".close{background:rgba(255,255,255,.18);border:none;color:#fff;width:28px;height:28px;border-radius:50%;font-size:18px;line-height:1;cursor:pointer;flex-shrink:0;}"
      + ".msgs{flex:1;overflow-y:auto;padding:13px 11px 5px;display:flex;flex-direction:column;gap:8px;}"
      + ".msg{max-width:86%;padding:10px 12px;border-radius:14px;font-size:14px;line-height:1.45;white-space:pre-line;}"
      + ".msg.bot{background:#fff;color:#26221c;border:1px solid #EAD9BC;border-bottom-left-radius:4px;align-self:flex-start;}"
      + ".msg.bot b{color:__A__;}"
      + ".msg.user{background:__A__;color:#fff;border-bottom-right-radius:4px;align-self:flex-end;}"
      + ".typing{align-self:flex-start;background:#fff;border:1px solid #EAD9BC;border-radius:14px;padding:11px 14px;display:flex;gap:5px;}"
      + ".typing i{width:7px;height:7px;border-radius:50%;background:#C9A86A;animation:rmcwb 1.1s infinite;}"
      + ".typing i:nth-child(2){animation-delay:.18s;}.typing i:nth-child(3){animation-delay:.36s;}"
      + "@keyframes rmcwb{0%,70%,100%{opacity:.25;}35%{opacity:1;}}"
      + ".chips{display:flex;gap:6px;overflow-x:auto;padding:7px 11px;flex-shrink:0;}"
      + ".chips::-webkit-scrollbar{display:none;}"
      + ".chip{flex-shrink:0;padding:7px 12px;border-radius:16px;background:#fff;border:1.5px solid __P__;color:__P__;font-size:12.5px;font-weight:600;cursor:pointer;white-space:nowrap;}"
      + ".bar{display:flex;gap:7px;padding:9px 11px;background:#FFF7EC;border-top:1px solid #EAD9BC;flex-shrink:0;}"
      + ".bar input{flex:1;padding:11px 14px;border-radius:20px;border:1.5px solid #D9C49D;font-size:15px;outline:none;color:#26221c;}"
      + ".bar button{width:42px;height:42px;border-radius:50%;border:none;background:__A__;color:#fff;font-size:16px;cursor:pointer;flex-shrink:0;}"
      + "@media (max-width:480px){.panel{width:calc(100vw - 28px);height:calc(100vh - 100px);}}";
    CSS = CSS.split('__P__').join(primary).split('__A__').join(accent);

    var host = document.createElement('div');
    host.setAttribute('style', 'position:fixed;right:22px;bottom:22px;z-index:2147483000;');
    document.body.appendChild(host);
    var root = host.attachShadow({ mode: 'open' });

    var chipEntries = entries.filter(function (e) { return e.label; }).slice(0, 8);
    var chipsHtml = chipEntries.map(function (e) {
      var q = e.keys[0] || e.label;
      return '<button class="chip" data-q="' + esc(q) + '">' + esc(e.label) + '</button>';
    }).join('');

    root.innerHTML = '<style>' + CSS + '</style>'
      + '<div class="wrap" id="wrap">'
      + '<div class="panel">'
      + '<div class="phead"><div class="badge">💬</div><div class="htext"><strong>' + esc(CFG.title || 'Guest concierge') + '</strong><span>' + esc(CFG.subtitle || '') + '</span></div><button class="close" id="close" aria-label="Close">×</button></div>'
      + '<div class="msgs" id="msgs"></div>'
      + '<div class="chips" id="chips">' + chipsHtml + '</div>'
      + '<div class="bar"><input id="input" type="text" placeholder="Ask a question…" autocomplete="off"><button id="send" aria-label="Send">➤</button></div>'
      + '</div>'
      + '<button class="launch" id="launch" aria-label="Open guest chat">💬</button>'
      + '</div>';

    var wrap = root.getElementById('wrap');
    var launch = root.getElementById('launch');
    var closeB = root.getElementById('close');
    var msgs = root.getElementById('msgs');
    var input = root.getElementById('input');
    var send = root.getElementById('send');
    var greeted = false;

    function add(html, who) { var d = document.createElement('div'); d.className = 'msg ' + who; d.innerHTML = html; msgs.appendChild(d); msgs.scrollTop = msgs.scrollHeight; }
    function typing() { var t = document.createElement('div'); t.className = 'typing'; t.id = 'typing'; t.innerHTML = '<i></i><i></i><i></i>'; msgs.appendChild(t); msgs.scrollTop = msgs.scrollHeight; }
    function untype() { var t = root.getElementById('typing'); if (t) t.remove(); }
    function ask(t) {
      if (!t || !t.trim()) return;
      add(esc(t), 'user'); input.value = ''; typing();
      setTimeout(function () { untype(); add(fmt(answer(t)), 'bot'); }, 500 + Math.floor(Math.random() * 300));
    }
    send.addEventListener('click', function () { ask(input.value); });
    input.addEventListener('keydown', function (e) { if (e.key === 'Enter') ask(input.value); });
    root.getElementById('chips').addEventListener('click', function (e) {
      var b = e.target.closest('.chip'); if (!b) return; ask(b.getAttribute('data-q'));
    });
    function openChat() {
      wrap.classList.add('open');
      if (!greeted) { greeted = true; if (CFG.welcome) { setTimeout(function () { add(fmt(CFG.welcome), 'bot'); }, 250); } }
      setTimeout(function () { input.focus(); }, 300);
    }
    function closeChat() { wrap.classList.remove('open'); }
    launch.addEventListener('click', function () { wrap.classList.contains('open') ? closeChat() : openChat(); });
    closeB.addEventListener('click', closeChat);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
@endverbatim
})();
@endif
