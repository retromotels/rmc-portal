<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Private preview</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Monoton&family=Oswald:wght@400;600;700&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
<style>
  *{box-sizing:border-box}
  body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;
    font-family:'DM Sans',sans-serif;color:#f3ede2;
    background:radial-gradient(1200px 600px at 50% -10%,#3a2f52,#1a1526 60%)}
  .card{width:100%;max-width:420px;background:rgba(255,255,255,.06);backdrop-filter:blur(6px);
    border:1px solid rgba(255,255,255,.12);border-radius:18px;padding:34px 30px;text-align:center}
  .brand{font-family:'Oswald',sans-serif;letter-spacing:2px;font-size:13px;color:#7fd7d1;text-transform:uppercase;margin-bottom:18px}
  .lock{font-size:34px;margin-bottom:8px}
  h1{font-family:'Oswald',sans-serif;font-size:22px;margin:0 0 6px}
  p{font-size:14px;line-height:1.55;color:#c9c2d6;margin:0 0 20px}
  form{display:flex;flex-direction:column;gap:12px}
  input{padding:13px 14px;border-radius:11px;border:1px solid rgba(255,255,255,.18);background:rgba(0,0,0,.25);color:#fff;font-size:16px;text-align:center;letter-spacing:2px}
  input::placeholder{letter-spacing:normal;color:#9990ad}
  button{padding:13px;border:none;border-radius:11px;background:#ee6a5a;color:#fff;font-weight:700;font-family:'Oswald',sans-serif;letter-spacing:.5px;font-size:15px;cursor:pointer}
  .err{background:rgba(238,106,90,.18);border:1px solid rgba(238,106,90,.5);color:#ffd9d3;font-size:13px;padding:9px;border-radius:10px;margin-bottom:14px}
  .foot{margin-top:20px;font-size:12px;color:#8a82a0}
</style>
</head>
<body>
  <div class="card">
    <div class="brand">Retro Motel Collective</div>
    <div class="lock">🔒</div>
    <h1>Private preview</h1>
    <p>This is a password-protected preview{{ $site->name && $site->name !== 'New microsite' ? ' of a new website concept for '.$site->name : '' }}. Enter the password we sent you to take a look.</p>
    @if($error)<div class="err">{{ $error }}</div>@endif
    <form method="POST" action="{{ route('site.unlock', $site->preview_token) }}">
      @csrf
      <input type="text" name="password" placeholder="Enter password" autocomplete="off" autofocus required>
      <button type="submit">View preview →</button>
    </form>
    <div class="foot">Not the right place? Contact RMC head office.</div>
  </div>
</body>
</html>
