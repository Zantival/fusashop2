<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>{{ $title }} | FusaShop</title>
  <style>
    :root { color-scheme: light; font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; color: #1b1c1c; background: #f5f8f6; }
    * { box-sizing: border-box; }
    body { min-height: 100vh; margin: 0; display: grid; place-items: center; padding: 24px; background: radial-gradient(ellipse at 15% 10%, #d8f6e8 0, transparent 38%), radial-gradient(ellipse at 90% 90%, #e1f1e9 0, transparent 35%), #f7f9f7; }
    .card { width: min(100%, 620px); padding: clamp(28px, 7vw, 64px); text-align: center; background: rgba(255,255,255,.94); border: 1px solid #e1eae4; border-radius: 28px; box-shadow: 0 24px 70px rgba(20, 65, 44, .11); }
    .brand { display: inline-flex; align-items: center; gap: 9px; margin-bottom: 40px; color: #006c47; font-size: 17px; font-weight: 850; letter-spacing: -.04em; }
    .brand-mark { width: 29px; height: 29px; display: grid; place-items: center; border-radius: 10px; color: #fff; background: linear-gradient(135deg, #006c47, #16a36e); }
    .brand-mark svg { width: 17px; height: 17px; }
    .symbol { width: 76px; height: 76px; display: grid; place-items: center; margin: 0 auto 21px; border-radius: 24px; color: #08734d; background: #e5f7ed; }
    .symbol svg { width: 38px; height: 38px; }
    .code { margin: 0; color: #08734d; font-size: clamp(46px, 12vw, 68px); line-height: 1; font-weight: 900; letter-spacing: -.07em; }
    h1 { margin: 19px 0 10px; font-size: clamp(23px, 5vw, 31px); line-height: 1.2; letter-spacing: -.04em; }
    .message { max-width: 440px; margin: 0 auto; color: #53635a; font-size: 16px; line-height: 1.7; }
    .hint { margin: 13px auto 0; color: #76847b; font-size: 13px; line-height: 1.6; }
    .actions { display: flex; flex-wrap: wrap; justify-content: center; gap: 12px; margin-top: 30px; }
    .button { min-height: 46px; display: inline-flex; align-items: center; justify-content: center; padding: 0 21px; border: 1px solid transparent; border-radius: 14px; font-size: 14px; font-weight: 750; text-decoration: none; transition: transform .15s ease, box-shadow .15s ease; }
    .button:hover { transform: translateY(-1px); }
    .primary { color: white; background: linear-gradient(135deg, #006c47, #0d9360); box-shadow: 0 8px 18px rgba(0, 108, 71, .2); }
    .secondary { color: #24513d; background: #fff; border-color: #dbe7df; }
    .help { margin: 28px 0 0; color: #8a968e; font-size: 12px; }
    @media (max-width: 420px) { .brand { margin-bottom: 30px; } .actions { flex-direction: column; } .button { width: 100%; } }
  </style>
</head>
<body>
  <main class="card" role="main">
    <div class="brand"><span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M4 7h2l2 9h9l2-6H7M10 20h.01M17 20h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>FusaShop</div>
    <div class="symbol" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M12 8v4m0 4h.01M10.3 3.86 2.7 17a2 2 0 0 0 1.73 3h15.14a2 2 0 0 0 1.73-3L13.7 3.86a2 2 0 0 0-3.46 0Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
    <p class="code">{{ $code }}</p>
    <h1>{{ $title }}</h1>
    <p class="message">{{ $message }}</p>
    @if(!empty($hint))<p class="hint">{{ $hint }}</p>@endif
    <div class="actions">
      <a class="button primary" href="{{ route('consumer.home') }}">Ir al inicio</a>
      <a class="button secondary" href="{{ route('login') }}">Iniciar sesión</a>
    </div>
    <p class="help">Si el problema continúa, vuelve a intentarlo más tarde o contacta al equipo de FusaShop.</p>
  </main>
</body>
</html>
