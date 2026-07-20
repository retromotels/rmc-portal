<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  body{font-family:DejaVu Sans, sans-serif;color:#2d2837;font-size:12px;line-height:1.6;margin:0}
  .band{background:#1a1526;color:#fff;padding:22px 40px}
  .band .brand{color:#ff5e5b;font-size:18px;font-weight:bold;letter-spacing:1px}
  .band .title{font-size:13px;margin-top:6px}
  .rule{height:4px;background:#ff5e5b}
  .wrap{padding:28px 40px}
  .meta{background:#f7efe1;border:1px solid #e2d6c2;border-radius:6px;padding:12px 16px;margin-bottom:20px;font-size:11px}
  .meta b{color:#1a1526}
  h1{font-size:16px;color:#1a1526;margin:0 0 12px}
  p{margin:0 0 12px}
  .foot{margin-top:28px;border-top:1px solid #e2d6c2;padding-top:10px;font-size:9px;color:#96907c}
</style>
</head>
<body>
  <div class="band">
    <div class="brand">RETRO MOTEL COLLECTIVE</div>
    <div class="title">{{ $policy['title'] }}</div>
  </div>
  <div class="rule"></div>
  <div class="wrap">
    <div class="meta">
      <div><b>Accepted by:</b> {{ $user->name }}@if($user->motel) ({{ $user->motel }})@endif</div>
      <div><b>Email:</b> {{ $user->email }}</div>
      <div><b>Date &amp; time accepted:</b> {{ $acceptedAt->format('j F Y, g:i a') }}</div>
    </div>
    <h1>{{ $policy['title'] }}</h1>
    @foreach($policy['body'] as $para)
      <p>{{ $para }}</p>
    @endforeach
    <div class="foot">Digitally accepted via the RMC member portal · document retained on file · generated {{ now()->format('j M Y') }}</div>
  </div>
</body>
</html>
