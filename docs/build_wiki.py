"""Genera el wiki estático (docs/index.html) a partir de docs/MANUAL_TECNICO.md.

Cada encabezado de nivel 2 del manual se convierte en una página del wiki, se le
añaden los diagramas Mermaid definidos en DIAGRAMS y se empaqueta todo en un
único HTML con menú lateral, buscador y navegación por hash.

Uso: pip install markdown && python3 docs/build_wiki.py
"""

import html
import json
import re
from pathlib import Path

import markdown

ROOT = Path(__file__).resolve().parent.parent

# Diagramas Mermaid que se insertan al inicio de la página indicada (por slug).
DIAGRAMS = {
    "arquitectura": [
        (
            "Vista general de capas",
            """flowchart TD
    B["Navegador<br/>Blade + Tailwind + Alpine + Chart.js"] -->|sesion web| W["routes/web.php"]
    M["Cliente API / movil"] -->|token Sanctum| A["routes/api.php"]
    W --> MW["Middleware<br/>SecurityHeaders / auth / verified / role / EnsureKycApproved"]
    A --> MW
    MW --> C["Controladores<br/>Auth, Consumer, Merchant, Analyst, Chat, PQRS, Payment, Api"]
    C --> E["Modelos Eloquent"]
    C --> S["PayUService"]
    C --> P["Procesos Python<br/>symfony/process"]
    E --> DB[("MySQL / SQLite")]
    S --> PU["PayU WebCheckout"]
    PU -->|webhook| C
    C --> N["Notificaciones BD + correo"]
    C --> ST["Storage local<br/>storage/app/public"]""",
        ),
        (
            "Control de acceso por rol",
            """flowchart LR
    U["Usuario autenticado"] --> R{"role"}
    R -->|consumer| CN["/shop/cart, checkout, orders, reviews"]
    R -->|merchant| K{"kyc_status"}
    R -->|analyst| AD["/admin/*"]
    K -->|pending o rejected| PF["/merchant/profile<br/>acceso bloqueado"]
    K -->|approved| MD["/merchant/dashboard, products, orders, finances"]""",
        ),
    ],
    "modelo-de-datos": [
        (
            "Entidades principales",
            """erDiagram
    USERS ||--o| CARTS : tiene
    USERS ||--o{ ORDERS : realiza
    USERS ||--o| COMPANY_PROFILES : posee
    USERS ||--o{ PRODUCTS : vende
    USERS ||--o{ REVIEWS : escribe
    USERS ||--o{ LOYALTY_POINTS : acumula
    USERS ||--o{ P_Q_R_S : radica
    USERS ||--o{ MESSAGES : envia
    USERS ||--o{ BANNER_REQUESTS : solicita
    CARTS ||--o{ CART_ITEMS : contiene
    PRODUCTS ||--o{ CART_ITEMS : referencia
    PRODUCTS ||--o{ ORDER_ITEMS : referencia
    PRODUCTS ||--o{ REVIEWS : recibe
    ORDERS ||--o{ ORDER_ITEMS : agrupa
    PRODUCTS ||--o{ COUPONS : ""
    BANNER_REQUESTS ||--o| GLOBAL_BANNERS : aprueba""",
        )
    ],
    "flujos-funcionales": [
        (
            "Proceso de compra (checkout)",
            """sequenceDiagram
    participant C as Cliente
    participant CC as ConsumerController
    participant DB as Base de datos
    participant M as Comerciante
    C->>CC: POST /shop/checkout
    CC->>CC: Validar direccion y metodo de pago
    CC->>DB: Calcular total y puntos disponibles
    CC->>CC: Aplicar descuento (1 punto = $50)
    CC->>DB: Crear order + order_items (transaccion)
    CC->>DB: Descontar stock
    CC-->>M: ProductOutOfStock si stock = 0
    CC->>DB: Acumular puntos (1 punto por $1.000)
    CC-->>M: NewOrderNotification
    CC->>DB: Vaciar y eliminar carrito
    CC-->>C: Redirect a /shop/orders""",
        ),
        (
            "Onboarding y KYC del comerciante",
            """stateDiagram-v2
    [*] --> SinPerfil
    SinPerfil --> Pendiente: envia perfil + RUT + camara de comercio
    Pendiente --> Aprobado: analista aprueba
    Pendiente --> Rechazado: analista rechaza
    Rechazado --> Pendiente: corrige documentos
    Aprobado --> [*]: acceso completo al panel
    note right of Pendiente
        EnsureKycApproved bloquea
        el panel del comerciante
    end note""",
        ),
    ],
}

PAGE_ICONS = {
    "informacion-general": "◆",
    "arquitectura": "▣",
    "instalacion-y-ejecucion": "▶",
    "rutas": "↗",
    "modelo-de-datos": "▤",
    "flujos-funcionales": "⇄",
    "scripts-python": "∑",
    "seguridad": "⛨",
    "pruebas-y-calidad": "✓",
    "mantenimiento": "⚙",
}


def slugify(text: str) -> str:
    text = re.sub(r"^\d+\.\s*", "", text).lower()
    replacements = {"á": "a", "é": "e", "í": "i", "ó": "o", "ú": "u", "ñ": "n", "ü": "u"}
    for src, dst in replacements.items():
        text = text.replace(src, dst)
    text = re.sub(r"[^a-z0-9]+", "-", text)
    return text.strip("-")


def split_pages(md_source: str):
    """Divide el manual en páginas por encabezado de nivel 2."""
    body = md_source.split("\n## ", 1)
    intro = body[0]
    rest = "## " + body[1]
    chunks = re.split(r"\n(?=## )", rest)
    pages = []
    for chunk in chunks:
        title = chunk.splitlines()[0].removeprefix("## ").strip()
        content = "\n".join(chunk.splitlines()[1:]).strip()
        content = content.replace("\n---", "").strip()
        pages.append({"title": title, "slug": slugify(title), "markdown": content})
    return intro, pages


def render(md_text: str) -> str:
    md = markdown.Markdown(extensions=["tables", "fenced_code", "attr_list"])
    return md.convert(md_text)


def build() -> str:
    source = (ROOT / "docs" / "MANUAL_TECNICO.md").read_text(encoding="utf-8")
    intro, pages = split_pages(source)
    intro_html = render(intro.split("\n", 1)[1].replace("\n---", "").strip())

    rendered = []
    search_index = []
    for index, page in enumerate(pages):
        diagrams = "".join(
            f'<figure class="diagram"><figcaption>{caption}</figcaption>'
            f'<pre class="mermaid">{html.escape(code)}</pre></figure>'
            for caption, code in DIAGRAMS.get(page["slug"], [])
        )
        html_body = render(page["markdown"])
        subtitles = re.findall(r"^### (.+)$", page["markdown"], flags=re.MULTILINE)
        prev_page = pages[index - 1] if index > 0 else None
        next_page = pages[index + 1] if index < len(pages) - 1 else None
        pager = '<nav class="pager">'
        if prev_page:
            pager += f'<a href="#{prev_page["slug"]}">← {prev_page["title"]}</a>'
        else:
            pager += "<span></span>"
        if next_page:
            pager += f'<a href="#{next_page["slug"]}">{next_page["title"]} →</a>'
        pager += "</nav>"

        rendered.append(
            f'<section class="page" id="{page["slug"]}" hidden>'
            f'<h1>{page["title"]}</h1>{diagrams}{html_body}{pager}</section>'
        )
        text = re.sub(r"[#*`|>-]", " ", page["markdown"])
        search_index.append(
            {
                "slug": page["slug"],
                "title": page["title"],
                "subtitles": subtitles,
                "text": re.sub(r"\s+", " ", text)[:6000].lower(),
            }
        )

    home = (
        '<section class="page" id="inicio" hidden><h1>FusaShop — Wiki técnica</h1>'
        f'<div class="intro">{intro_html}</div>'
        '<div class="cards">'
        + "".join(
            f'<a class="card" href="#{p["slug"]}">'
            f'<span class="icon">{PAGE_ICONS.get(p["slug"], "•")}</span>'
            f'<strong>{p["title"]}</strong>'
            f'<em>{", ".join(re.findall(r"^### (.+)$", p["markdown"], flags=re.MULTILINE)[:3]) or "Ver sección"}</em>'
            "</a>"
            for p in pages
        )
        + "</div></section>"
    )

    nav = "".join(
        f'<a class="nav-item" data-slug="{p["slug"]}" href="#{p["slug"]}">'
        f'<span class="icon">{PAGE_ICONS.get(p["slug"], "•")}</span>{p["title"]}</a>'
        for p in pages
    )

    template = (ROOT / "docs" / "wiki_template.html").read_text(encoding="utf-8")
    return (
        template.replace("__NAV__", nav)
        .replace("__PAGES__", home + "".join(rendered))
        .replace("__INDEX__", json.dumps(search_index, ensure_ascii=False))
    )


if __name__ == "__main__":
    (ROOT / "docs" / "index.html").write_text(build(), encoding="utf-8")
    print("Generado docs/index.html")
