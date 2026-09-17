"""Genera public/manual-tecnico.html a partir de docs/MANUAL_TECNICO.md.

Uso: pip install markdown && python3 docs/build_manual_html.py
"""

from pathlib import Path

import markdown

ROOT = Path(__file__).resolve().parent.parent

src = (ROOT / 'docs' / 'MANUAL_TECNICO.md').read_text(encoding='utf-8')
md = markdown.Markdown(extensions=['tables', 'fenced_code', 'toc', 'attr_list'],
                       extension_configs={'toc': {'toc_depth': '2-3'}})
body = md.convert(src)
toc = md.toc

tpl = """<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Manual Técnico — FusaShop</title>
<style>
:root{--bg:#f6f7f9;--panel:#fff;--ink:#12181f;--muted:#5b6673;--accent:#1f6feb;--border:#e3e7ec;--code:#f2f4f7}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--ink);font:16px/1.65 -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif}
a{color:var(--accent);text-decoration:none}
a:hover{text-decoration:underline}
header.top{background:#0d1b2a;color:#fff;padding:28px 32px}
header.top h1{margin:0;font-size:26px;letter-spacing:.3px}
header.top p{margin:6px 0 0;color:#a9b6c5;font-size:14px}
.layout{display:flex;align-items:flex-start;gap:28px;max-width:1280px;margin:28px auto;padding:0 24px}
nav.toc{position:sticky;top:24px;flex:0 0 290px;background:var(--panel);border:1px solid var(--border);border-radius:10px;padding:16px 18px;max-height:calc(100vh - 60px);overflow:auto}
nav.toc h2{margin:0 0 10px;font-size:12px;text-transform:uppercase;letter-spacing:.09em;color:var(--muted)}
nav.toc ul{list-style:none;margin:0;padding-left:0}
nav.toc ul ul{padding-left:14px}
nav.toc li{margin:3px 0}
nav.toc a{color:#31405166;color:#344051;font-size:14px;display:block;padding:3px 6px;border-radius:6px}
nav.toc a:hover{background:#eef3fb;text-decoration:none}
main{flex:1 1 auto;min-width:0;background:var(--panel);border:1px solid var(--border);border-radius:10px;padding:30px 38px}
main h1{display:none}
main h2{margin-top:38px;padding-bottom:8px;border-bottom:2px solid var(--border);font-size:23px}
main h3{margin-top:26px;font-size:18px;color:#1b2633}
main h2:first-of-type{margin-top:0}
code{background:var(--code);padding:1px 5px;border-radius:4px;font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;font-size:13.5px}
pre{background:#0d1b2a;color:#e7edf5;padding:16px 18px;border-radius:8px;overflow:auto}
pre code{background:none;color:inherit;padding:0;font-size:13px;line-height:1.5}
table{border-collapse:collapse;width:100%;margin:16px 0;font-size:14.5px;display:block;overflow-x:auto}
th,td{border:1px solid var(--border);padding:8px 11px;text-align:left;vertical-align:top}
th{background:#f0f3f7}
tr:nth-child(even) td{background:#fafbfc}
blockquote{margin:16px 0;padding:10px 16px;background:#fff8e6;border-left:4px solid #e0a800;color:#4a3c11}
hr{border:0;border-top:1px solid var(--border);margin:34px 0}
footer{max-width:1280px;margin:0 auto 40px;padding:0 24px;color:var(--muted);font-size:13px}
@media (max-width:980px){.layout{flex-direction:column}nav.toc{position:static;width:100%;flex:none}main{padding:22px}}
</style>
</head>
<body>
<header class="top">
  <h1>Manual Técnico — FusaShop</h1>
  <p>Plataforma de comercio electrónico · Laravel 12 · Repositorio Zantival/fusashop2</p>
</header>
<div class="layout">
  <nav class="toc"><h2>Contenido</h2>__TOC__</nav>
  <main>__BODY__</main>
</div>
<footer>Documento generado a partir de <code>docs/MANUAL_TECNICO.md</code>.</footer>
</body>
</html>
"""

out = tpl.replace('__TOC__', toc).replace('__BODY__', body)
(ROOT / 'public' / 'manual-tecnico.html').write_text(out, encoding='utf-8')
print('Generado public/manual-tecnico.html')
