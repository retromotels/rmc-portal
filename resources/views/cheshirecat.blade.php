<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>The Cheshire Cat Motel</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    html, body { height: 100%; }
    body {
      min-height: 100vh;
      font-family: 'DM Sans', system-ui, -apple-system, sans-serif;
      color: #2d2837;
      overflow-x: hidden;
    }
    /* Full-bleed background */
    .cc-bg {
      position: fixed;
      inset: 0;
      background: url('{{ asset('img/cheshirecat-bg.jpg') }}') center center / cover no-repeat;
      z-index: 0;
    }
    /* Content sits above the background — filled in next step */
    .cc-content {
      position: relative;
      z-index: 1;
      min-height: 100vh;
    }
  </style>
</head>
<body>
  <div class="cc-bg"></div>
  <main class="cc-content"></main>
</body>
</html>
