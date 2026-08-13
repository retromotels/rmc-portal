<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>The Cheshire Cat Motel</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    html, body { height: 100%; }
    body { min-height: 100vh; font-family: 'DM Sans', system-ui, -apple-system, sans-serif; color: #2d2837; overflow-x: hidden; }
    .cc-bg { position: fixed; inset: 0; background-position: center center; background-size: cover; background-repeat: no-repeat; z-index: 0; }
    .cc-content { position: relative; z-index: 1; min-height: 100vh; }
  </style>
</head>
<body>
  <div class="cc-bg" style="background-image:url('{{ asset('img/cheshirecat-bg.jpg') }}')"></div>
  <main class="cc-content"></main>

  {{-- ============ Guest chat widget (self-contained, no API) ============ --}}
  @verbatim
  <style>
    #ccWidget { position: fixed; right: 22px; bottom: 22px; z-index: 9999; font-family: 'DM Sans','Avenir Next','Segoe UI',system-ui,sans-serif; }
    .cc-launch { width: 62px; height: 62px; border-radius: 50%; border: none; cursor: pointer; background: linear-gradient(135deg,#7ba073,#4f6f4a); color: #fff; font-size: 26px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 22px rgba(45,70,40,.42); transition: transform .15s; }
    .cc-launch:hover { transform: scale(1.06); }
    .cc-launch:active { transform: scale(.94); }
    .cc-panel { position: absolute; right: 0; bottom: 78px; width: 372px; height: 560px; max-height: calc(100vh - 120px); background: linear-gradient(180deg,#FBEFD9 0%,#FFF7EC 32%); border-radius: 20px; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 18px 50px rgba(30,45,30,.45); transform: translateY(16px) scale(.96); opacity: 0; pointer-events: none; transform-origin: bottom right; transition: transform .22s ease, opacity .22s ease; }
    #ccWidget.open .cc-panel { transform: translateY(0) scale(1); opacity: 1; pointer-events: auto; }
    #ccWidget.open .cc-launch { transform: scale(.9); }
    .cc-phead { background: linear-gradient(135deg,#6f8f6a,#4c6a48); color: #FBEFD9; padding: 15px 16px; display: flex; align-items: center; gap: 11px; }
    .cc-badge { width: 42px; height: 42px; border-radius: 50%; background: radial-gradient(circle at 35% 30%,#cfe0b8,#7ba073 72%); display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; box-shadow: 0 0 14px rgba(120,160,110,.55); }
    .cc-htext { flex: 1; line-height: 1.2; }
    .cc-htext strong { font-size: 16px; letter-spacing: .5px; display: block; }
    .cc-htext span { font-size: 11.5px; opacity: .85; }
    .cc-close { background: rgba(255,255,255,.16); border: none; color: #fff; width: 30px; height: 30px; border-radius: 50%; font-size: 19px; line-height: 1; cursor: pointer; flex-shrink: 0; }
    .cc-close:hover { background: rgba(255,255,255,.3); }
    .cc-msgs { flex: 1; overflow-y: auto; padding: 14px 12px 6px; display: flex; flex-direction: column; gap: 9px; }
    .cc-msg { max-width: 86%; padding: 10px 13px; border-radius: 15px; font-size: 14.5px; line-height: 1.45; white-space: pre-line; animation: ccpop .2s ease-out; }
    @keyframes ccpop { from { opacity: 0; transform: translateY(7px); } to { opacity: 1; transform: translateY(0); } }
    .cc-msg.bot { background: #fff; color: #26221c; border: 1.5px solid #EAD9BC; border-bottom-left-radius: 4px; align-self: flex-start; box-shadow: 0 2px 6px rgba(120,90,40,.08); }
    .cc-msg.bot b { color: #b0563c; }
    .cc-msg.user { background: linear-gradient(135deg,#c06a3f,#a94e30); color: #fff; border-bottom-right-radius: 4px; align-self: flex-end; }
    .cc-typing { align-self: flex-start; background: #fff; border: 1.5px solid #EAD9BC; border-radius: 15px; border-bottom-left-radius: 4px; padding: 12px 15px; display: flex; gap: 5px; }
    .cc-typing i { width: 7px; height: 7px; border-radius: 50%; background: #C9A86A; animation: ccblink 1.1s infinite; }
    .cc-typing i:nth-child(2) { animation-delay: .18s; }
    .cc-typing i:nth-child(3) { animation-delay: .36s; }
    @keyframes ccblink { 0%,70%,100% { opacity: .25; } 35% { opacity: 1; } }
    .cc-chips { display: flex; gap: 7px; overflow-x: auto; padding: 8px 12px; scrollbar-width: none; flex-shrink: 0; }
    .cc-chips::-webkit-scrollbar { display: none; }
    .cc-chip { flex-shrink: 0; padding: 8px 13px; border-radius: 18px; background: #fff; border: 1.5px solid #6f8f6a; color: #4c6a48; font-size: 13px; font-weight: 600; cursor: pointer; font-family: inherit; white-space: nowrap; }
    .cc-chip:active { background: #6f8f6a; color: #fff; transform: scale(.96); }
    .cc-bar { display: flex; gap: 8px; padding: 10px 12px; background: #FBEFD9; border-top: 1.5px solid #EAD9BC; flex-shrink: 0; }
    .cc-bar input { flex: 1; padding: 12px 15px; border-radius: 22px; border: 1.5px solid #D9C49D; background: #fff; font-size: 15px; font-family: inherit; color: #26221c; outline: none; }
    .cc-bar input:focus { border-color: #6f8f6a; }
    .cc-bar button { width: 44px; height: 44px; border-radius: 50%; border: none; background: linear-gradient(135deg,#c06a3f,#a94e30); color: #fff; font-size: 17px; cursor: pointer; flex-shrink: 0; }
    .cc-bar button:active { transform: scale(.92); }
    @media (max-width: 480px) {
      #ccWidget { right: 14px; bottom: 14px; }
      .cc-panel { width: calc(100vw - 28px); height: calc(100vh - 100px); bottom: 74px; }
    }
  </style>

  <div id="ccWidget">
    <div class="cc-panel" id="ccPanel">
      <div class="cc-phead">
        <div class="cc-badge">😺</div>
        <div class="cc-htext"><strong>The Cheshire Cat</strong><span>Guest concierge · ask me anything</span></div>
        <button class="cc-close" id="ccClose" aria-label="Close chat">×</button>
      </div>
      <div class="cc-msgs" id="ccMsgs"></div>
      <div class="cc-chips" id="ccChips"></div>
      <div class="cc-bar">
        <input id="ccInput" type="text" placeholder="Ask about wifi, pool, breakfast…" autocomplete="off">
        <button id="ccSend" aria-label="Send">➤</button>
      </div>
    </div>
    <button class="cc-launch" id="ccLaunch" aria-label="Open guest chat">💬</button>
  </div>

  <script>
  (function () {
    // ===== Cheshire Cat knowledge base (edit answers here) =====
    var KB = [
      { keys: ["wifi","wi-fi","internet","password","network","connect","online"],
        a: "📶 <b>WiFi</b>\nNetwork: <b>CheshireCat</b>\nPassword: <b>grinandbear</b>\n\nFree and unlimited for your whole stay." },
      { keys: ["reception","front desk","office","staff","help","contact","phone","call"],
        a: "🛎️ <b>Reception</b>\nOpen daily <b>7am – 9pm</b> at the front of the motel.\nCall us on <b>(07) 5555 0199</b> — after hours it diverts to our on-call manager." },
      { keys: ["check in","check-in","checkin","arrive","arrival","early check"],
        a: "🕑 <b>Check-in</b>\nRooms are ready from <b>2:00pm</b>. Arriving early? Drop your bags at reception and we'll text you when your room is ready." },
      { keys: ["check out","check-out","checkout","leaving","late checkout","late check-out"],
        a: "🕙 <b>Check-out</b>\nCheck-out is by <b>10:00am</b>. Need a little longer? Ask reception about a late check-out — we'll do our best." },
      { keys: ["pool","swim","swimming","spa","plunge","hot tub"],
        a: "🩱 <b>Pool &amp; spa</b>\nOur terracotta plunge pool is open <b>7am – 9pm</b>.\nTowels are at reception. Please keep an eye on the little ones." },
      { keys: ["laundry","washing","washer","dryer","clothes","laundromat"],
        a: "🧺 <b>Guest laundry</b>\nSelf-service washer &amp; dryer sit <b>next to reception</b>, open 24/7.\nDetergent pods and coins are at the front desk." },
      { keys: ["breakfast","cafe","café","coffee","food","eat","dining","hungry"],
        a: "☕ <b>Breakfast — The Grinning Bean</b>\nServed <b>6:30am – 11am</b> at our on-site café.\nBig breakfasts, house granola and proper coffee. Room-charge welcome." },
      { keys: ["bakery","pie","lunch","dinner","snack","takeaway","restaurant","pastry"],
        a: "🥐 <b>Hungry later?</b>\nAfter 11am, wander to the <b>Rabbit Hole Bakery</b> (2-min walk) for pies and pastries, or the café strip on <b>Wonder Street</b> for lunch and dinner." },
      { keys: ["fire","firepit","fire pit","marshmallow","bonfire","evening","night"],
        a: "🔥 <b>Fire pit</b>\nLit every evening from <b>6pm</b> in the courtyard — and yes, we hand out <b>marshmallows</b>. Pull up a chair and stay a while." },
      { keys: ["bbq","barbecue","barbeque","grill"],
        a: "🍖 <b>BBQ area</b>\nA free guest BBQ is on the pool deck. Give the plate a wipe when you're done so it's ready for the next guest — thanks!" },
      { keys: ["parking","park","car","garage"," ev ","charger","charge"],
        a: "🚗 <b>Parking &amp; EV</b>\nFree on-site parking right outside your room.\nThere is an <b>EV charger</b> by reception too — ask the front desk to switch it on." },
      { keys: ["things to do","to do","around","attraction","activit","explore","nearby","what to do","sightsee","tips","local"],
        a: "🗺️ <b>Out &amp; about</b>\n• <b>Beach &amp; surf</b> — 5-min stroll, best on a mid tide.\n• <b>Lookout walk</b> — sunset views over the bay.\n• <b>Saturday markets</b> — local makers on Wonder Street.\nAsk reception and we'll point the way." },
      { keys: ["beach","surf","waves","ocean","board","tide","sand"],
        a: "🏖️ <b>Beach &amp; surf</b>\nThe beach is a short <b>5-minute walk</b> out front. Surf is best on a <b>mid-to-high tide</b> — reception has today's tide times and can lend you a board." },
      { keys: ["smoke","smoking","vape","vaping","pet","pets","dog","noise","quiet","rule","rules"],
        a: "📋 <b>House rules</b>\n• <b>Non-smoking</b> throughout (rooms &amp; indoors).\n• <b>Pets</b> welcome by prior arrangement — chat to reception.\n• <b>Quiet hours</b> 10pm – 7am so everyone rests easy." },
      { keys: ["hello","hi ","hey ","g'day","gday","howdy","good morning","good evening","hiya"],
        a: "😺 Hello and welcome to <b>The Cheshire Cat</b>! Tap a button below or ask me about WiFi, the pool, breakfast, the fire pit or things to do nearby." },
      { keys: ["thank","thanks","cheers","ta "],
        a: "😸 You're most welcome — enjoy your stay! Don't miss the marshmallows at the fire pit from 6pm. 🔥" }
    ];

    var FALLBACK = "Curiouser and curiouser — I'm not sure about that one! 😺\nOur team at <b>reception (7am – 9pm)</b> will know. Try asking me about:\n\n📶 WiFi   🩱 Pool   ☕ Breakfast\n🧺 Laundry   🔥 Fire pit   🏖️ Beach\n🗺️ Things to do   🚗 Parking";

    var CHIPS = [
      ["📶 WiFi","wifi"], ["☕ Breakfast","breakfast"], ["🩱 Pool","pool"],
      ["🔥 Fire pit","fire pit"], ["🏖️ Beach","beach"], ["🗺️ Things to do","things to do"],
      ["🧺 Laundry","laundry"], ["🛎️ Reception","reception"]
    ];

    var wrap = document.getElementById("ccWidget");
    var launch = document.getElementById("ccLaunch");
    var closeBtn = document.getElementById("ccClose");
    var msgs = document.getElementById("ccMsgs");
    var chipsEl = document.getElementById("ccChips");
    var input = document.getElementById("ccInput");
    var send = document.getElementById("ccSend");
    var greeted = false;

    function esc(s) { return s.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;"); }
    function add(html, who) { var d = document.createElement("div"); d.className = "cc-msg " + who; d.innerHTML = html; msgs.appendChild(d); msgs.scrollTop = msgs.scrollHeight; }
    function typing() { var t = document.createElement("div"); t.className = "cc-typing"; t.id = "ccTyping"; t.innerHTML = "<i></i><i></i><i></i>"; msgs.appendChild(t); msgs.scrollTop = msgs.scrollHeight; }
    function untype() { var t = document.getElementById("ccTyping"); if (t) t.remove(); }
    function answer(q) {
      var s = " " + q.toLowerCase().trim() + " ";
      var best = null, bs = 0;
      for (var i = 0; i < KB.length; i++) {
        var sc = 0, ks = KB[i].keys;
        for (var j = 0; j < ks.length; j++) { if (s.indexOf(ks[j].toLowerCase()) !== -1) sc += ks[j].length; }
        if (sc > bs) { bs = sc; best = KB[i]; }
      }
      return best ? best.a : FALLBACK;
    }
    function ask(t) {
      if (!t || !t.trim()) return;
      add(esc(t), "user"); input.value = ""; typing();
      setTimeout(function () { untype(); add(answer(t), "bot"); }, 520 + Math.floor(Math.random() * 320));
    }
    send.addEventListener("click", function () { ask(input.value); });
    input.addEventListener("keydown", function (e) { if (e.key === "Enter") ask(input.value); });
    for (var c = 0; c < CHIPS.length; c++) {
      (function (pair) {
        var b = document.createElement("button");
        b.className = "cc-chip"; b.textContent = pair[0];
        b.addEventListener("click", function () { ask(pair[1]); });
        chipsEl.appendChild(b);
      })(CHIPS[c]);
    }
    function openChat() {
      wrap.classList.add("open");
      if (!greeted) { greeted = true; setTimeout(function () { add("😺 <b>Welcome to The Cheshire Cat!</b>\nI'm your guest concierge. Tap a button below or ask me anything about the motel and the local area.", "bot"); }, 250); }
      setTimeout(function () { input.focus(); }, 300);
    }
    function closeChat() { wrap.classList.remove("open"); }
    launch.addEventListener("click", function () { wrap.classList.contains("open") ? closeChat() : openChat(); });
    closeBtn.addEventListener("click", closeChat);
  })();
  </script>
  @endverbatim
</body>
</html>
