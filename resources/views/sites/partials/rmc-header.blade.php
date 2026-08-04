{{-- Retro Motels network header — matches retromotels.com. Self-styled; shown on public microsites. --}}
<style>
  .rmc-top{position:sticky;top:0;z-index:200;display:flex;align-items:center;justify-content:space-between;
    padding:12px 24px;background:rgba(31,41,51,.94);backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);
    border-bottom:1px solid rgba(245,236,216,.08);font-family:'DM Sans',system-ui,sans-serif}
  .rmc-top *{box-sizing:border-box}
  .rmc-wordmark{display:flex;gap:4px;align-items:center;text-decoration:none}
  .rmc-wordmark .tl{width:24px;height:24px;border-radius:5px;display:grid;place-items:center;font-weight:900;font-size:14px;color:#1f2933;font-family:'DM Sans',system-ui,sans-serif}
  .rmc-wordmark .rmc-gap{width:8px}
  .rmc-links{display:flex;gap:20px;align-items:center;list-style:none;margin:0;padding:0}
  .rmc-links a{text-decoration:none;font-size:12px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#f8eed6;opacity:.82;transition:.2s}
  .rmc-links a:hover{opacity:1;color:#ffc078}
  .rmc-btn{background:#e0491d;color:#f8eed6 !important;opacity:1 !important;padding:9px 18px;border-radius:999px}
  .rmc-btn:hover{background:#c13c14}
  .rmc-burger{display:none;background:none;border:none;color:#f8eed6;font-size:24px;cursor:pointer}
  @media (max-width:900px){
    .rmc-links{display:none;position:absolute;top:100%;left:0;right:0;flex-direction:column;align-items:flex-start;
      background:rgba(31,41,51,.98);padding:16px 24px;gap:16px;border-bottom:1px solid rgba(245,236,216,.08)}
    .rmc-links.open{display:flex}
    .rmc-burger{display:block}
  }
</style>
<nav class="rmc-top">
  <a class="rmc-wordmark" href="https://retromotels.com/#top" aria-label="Retro Motels home">
    <span class="tl" style="background:#ffc078">R</span>
    <span class="tl" style="background:#ffe574">E</span>
    <span class="tl" style="background:#e0491d;color:#f8eed6">T</span>
    <span class="tl" style="background:#ffb3a7">R</span>
    <span class="tl" style="background:#c7a1f0">O</span>
    <span class="rmc-gap"></span>
    <span class="tl" style="background:#8ed2f4">M</span>
    <span class="tl" style="background:#8fe2b6">O</span>
    <span class="tl" style="background:#e0491d;color:#f8eed6">T</span>
    <span class="tl" style="background:#ff9c85">E</span>
    <span class="tl" style="background:#ffe574">L</span>
    <span class="tl" style="background:#ffb3a7">S</span>
  </a>
  <ul class="rmc-links" id="rmcLinks">
    <li><a href="https://retromotels.com/#promise">The Promise</a></li>
    <li><a href="https://retromotels.com/#savings">Savings</a></li>
    <li><a href="https://retromotels.com/#tiers">Membership</a></li>
    <li><a href="https://retromotels.com/#grid">The Vibe</a></li>
    <li><a href="https://retromotels.com/#members">Members</a></li>
    <li><a href="https://retromotels.com/#contact">Contact</a></li>
    <li><a href="https://portal.retromotels.com" class="rmc-btn">Login</a></li>
  </ul>
  <button class="rmc-burger" type="button" aria-label="Menu" onclick="document.getElementById('rmcLinks').classList.toggle('open')">&#9776;</button>
</nav>
