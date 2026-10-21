const fs = require('node:fs');
const path = require('node:path');
const sharp = require('sharp');

const root = path.resolve(__dirname, '..');
const diagramDir = path.join(root, 'docs', 'diagramas');
const outputDir = path.join(diagramDir, 'png');

function esc(value) {
  return String(value).replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&apos;' })[char]);
}

function parseDiagram(source) {
  const lines = source.split(/\r?\n/);
  const kind = (lines.find((line) => line.trim()) || '').trim().split(/\s+/)[0];
  const title = kind === 'classDiagram' ? 'UML · Clases' : kind === 'erDiagram' ? 'Modelo de datos' : 'Casos y componentes';
  const nodes = new Map();
  const edges = [];
  const addNode = (id, label, rows = []) => {
    if (!nodes.has(id)) nodes.set(id, { id, label: label || id, rows });
  };

  if (kind === 'classDiagram') {
    const text = source.replace(/\r/g, '');
    for (const match of text.matchAll(/class\s+(\w+)\s*\{([\s\S]*?)\}/g)) {
      addNode(match[1], match[1], match[2].split('\n').map((row) => row.trim()).filter(Boolean));
    }
    for (const line of lines) {
      const match = line.trim().match(/^(\w+)\s+"([^"]+)"\s+([.*<>o-]+)\s+"([^"]+)"\s+(\w+)(?:\s*:\s*(.*))?$/);
      if (match) edges.push({ from: match[1], to: match[5], label: match[6] || '', sourceMultiplicity: match[2], targetMultiplicity: match[4] });
    }
  } else if (kind === 'erDiagram') {
    for (let index = 0; index < lines.length; index++) {
      const entity = lines[index].trim().match(/^(\w+)\s*\{\s*$/);
      if (!entity) continue;
      const rows = [];
      for (index++; index < lines.length && !/^\s*}\s*$/.test(lines[index]); index++) {
        const row = lines[index].trim();
        if (row) rows.push(row);
      }
      addNode(entity[1], entity[1], rows);
    }
    for (const line of lines) {
      const match = line.trim().match(/^(\w+)\s+([|o}{]{1,3}(?:--|\.\.)[|o}{]{1,3})\s+(\w+)(?:\s*:\s*(.*))?$/);
      if (match) {
        addNode(match[1], match[1]);
        addNode(match[3], match[3]);
        edges.push({ from: match[1], to: match[3], label: match[4] || '', cardinality: match[2] });
      }
    }
  } else {
    for (const line of lines) {
      const definition = line.trim().match(/^([\p{L}\p{N}_]+)\s*(?:\["([^"]+)"\]|\(\[([^\]]+)\]\)|\[([^\]]+)\]|\(([^)]+)\))/u);
      if (definition) {
        const label = (definition[2] || definition[3] || definition[4] || definition[5] || definition[1])
          .trim().replace(/^\(\s*["']?/, '').replace(/["']?\s*\)$/, '');
        addNode(definition[1], label);
      }
      const links = [...line.trim().matchAll(/([\p{L}\p{N}_]+)\s*(-->|-\.->)\s*(?:\|([^|]+)\|\s*)?([\p{L}\p{N}_]+)/gu)];
      for (const link of links) {
        addNode(link[1], link[1]); addNode(link[4], link[4]);
        edges.push({ from: link[1], to: link[4], label: link[3] || '', dashed: link[2] === '-.->' });
      }
    }
  }
  return { kind, title, nodes: [...nodes.values()], edges };
}

function wrap(text, maxChars) {
  const words = String(text).split(/\s+/);
  const lines = [];
  let line = '';
  for (const word of words) {
    if (line && `${line} ${word}`.length > maxChars) { lines.push(line); line = word; }
    else line = line ? `${line} ${word}` : word;
  }
  if (line) lines.push(line);
  return lines;
}

function makeSvg(diagram, filename) {
  const isUseCase = filename === 'casos-de-uso';
  const width = isUseCase ? 1640 : 1860;
  const height = isUseCase ? 2050 : 0;
  const cardW = isUseCase ? 470 : 300;
  const cardStartY = 140;
  const nodes = diagram.nodes.map((node) => {
    const titleLines = wrap(node.label, isUseCase ? 41 : 27);
    const fieldLines = (node.rows.length ? node.rows : []).flatMap((row) => wrap(row, 32));
    return { ...node, titleLines, fieldLines, w: isUseCase ? 0 : cardW, h: isUseCase ? 0 : Math.max(116, 64 + titleLines.length * 20 + fieldLines.length * 22) };
  });
  const byId = new Map(nodes.map((node) => [node.id, node]));

  if (isUseCase) {
    const actors = ['consumidor', 'comerciante', 'admin', 'proveedor'];
    const actorY = { consumidor: 365, comerciante: 810, admin: 1260, proveedor: 1710 };
    const actorBus = { consumidor: 365, comerciante: 380, admin: 395, proveedor: 410 };
    const useCaseNodes = nodes.filter((node) => !actors.includes(node.id));
    const systemLeft = 455;
    const systemTop = 120;
    const systemW = 1120;
    const systemH = 1805;
    useCaseNodes.forEach((node, index) => {
      node.cx = 1050;
      node.cy = 205 + index * 98;
      node.ovalW = 510;
      node.ovalH = 76;
    });
    actors.forEach((id) => {
      const actor = byId.get(id);
      if (actor) { actor.cx = 165; actor.cy = actorY[id]; }
    });

    const connections = diagram.edges.map((edge) => {
      const a = byId.get(edge.from); const b = byId.get(edge.to);
      if (!a || !b) return '';
      if (actors.includes(edge.from)) {
        const bus = actorBus[edge.from];
      return `<path class="association" d="M 194 ${a.cy} L ${bus} ${a.cy} L ${bus} ${b.cy} L ${b.cx - b.ovalW / 2} ${b.cy}"/>`;
      }
      const isInclude = edge.dashed || edge.label.includes('include');
      const curve = `M ${a.cx + 245} ${a.cy} C ${a.cx + 420} ${a.cy}, ${b.cx + 420} ${b.cy}, ${b.cx + 245} ${b.cy}`;
      const label = edge.label ? `<text x="${a.cx + 330}" y="${(a.cy + b.cy) / 2}" class="edge-label">${esc(edge.label.replaceAll('<<', '«').replaceAll('>>', '»'))}</text>` : '';
      return `<path class="${isInclude ? 'include' : 'association'}" d="${curve}" marker-end="url(#arrow)"/>${label}`;
    }).join('');
    const actorDrawings = actors.map((id) => {
      const actor = byId.get(id);
      const label = wrap(actor.label.replace(/^[^\p{L}\p{N}]+/u, ''), 22);
      return `<g class="actor"><circle cx="${actor.cx}" cy="${actor.cy - 30}" r="15"/><path d="M ${actor.cx} ${actor.cy - 15} L ${actor.cx} ${actor.cy + 25} M ${actor.cx - 22} ${actor.cy + 1} L ${actor.cx + 22} ${actor.cy + 1} M ${actor.cx} ${actor.cy + 25} L ${actor.cx - 18} ${actor.cy + 48} M ${actor.cx} ${actor.cy + 25} L ${actor.cx + 18} ${actor.cy + 48}"/><text x="${actor.cx}" y="${actor.cy + 72}" text-anchor="middle" class="actor-label">${esc(label[0] || actor.label)}</text></g>`;
    }).join('');
    const ovals = useCaseNodes.map((node) => `<g><ellipse cx="${node.cx}" cy="${node.cy}" rx="${node.ovalW / 2}" ry="${node.ovalH / 2}" class="usecase"/><text x="${node.cx}" y="${node.cy + 6}" text-anchor="middle" class="usecase-label">${esc(node.label)}</text></g>`).join('');

    return `<svg xmlns="http://www.w3.org/2000/svg" width="${width}" height="${height}" viewBox="0 0 ${width} ${height}">
      ${svgDefs()}${sharedStyle()}
      <rect class="bg" width="100%" height="100%"/><text x="55" y="52" class="title">Diagrama de casos de uso · FusaShop</text><text x="55" y="82" class="subtitle">Actores y funcionalidades principales por rol</text>
      <rect x="${systemLeft}" y="${systemTop}" width="${systemW}" height="${systemH}" rx="18" class="system"/><text x="${systemLeft + 24}" y="${systemTop + 36}" class="system-label">FusaShop</text>
      <g>${connections}</g>${actorDrawings}${ovals}
      <g class="legend"><path d="M 75 1970 H 155" class="association"/><text x="170" y="1975">Asociación actor–caso de uso</text><path d="M 510 1970 H 590" class="include" marker-end="url(#arrow)"/><text x="605" y="1975">Flujo que involucra otro servicio</text></g>
    </svg>`;
  }

  if (filename === 'uml-componentes') {
    const componentWidth = 2520;
    const componentHeight = 1320;
    const positions = {
      shopper: [65, 350], seller: [65, 610], analyst: [65, 870], browser: [350, 610],
      routes: [660, 610], middleware: [940, 610], controllers: [1220, 610],
      models: [1530, 355], services: [1530, 850], jobs: [1820, 850], database: [1850, 535],
      wompi: [2185, 235], envia: [2185, 415], factus: [2185, 595], resend: [2185, 775],
    };
    for (const node of nodes) {
      const [x, y] = positions[node.id] || [2185, 955];
      node.x = x; node.y = y; node.w = 230; node.h = 88;
    }
    const componentEdges = diagram.edges.map((edge) => {
      const from = byId.get(edge.from); const to = byId.get(edge.to);
      if (!from || !to) return '';
      if (edge.from === 'services' && ['wompi', 'envia', 'factus', 'resend'].includes(edge.to)) {
        const laneX = 2070;
        return `<path d="M ${from.x + from.w} ${from.y + from.h / 2} L ${laneX} ${from.y + from.h / 2} L ${laneX} ${to.y + to.h / 2} L ${to.x} ${to.y + to.h / 2}" class="edge" marker-end="url(#arrow)"/>`;
      }
      const dx = to.x + to.w / 2 - (from.x + from.w / 2);
      const dy = to.y + to.h / 2 - (from.y + from.h / 2);
      const unitX = dx / (Math.hypot(dx, dy) || 1); const unitY = dy / (Math.hypot(dx, dy) || 1);
      const startT = Math.min(from.w / 2 / (Math.abs(unitX) || 1e-6), from.h / 2 / (Math.abs(unitY) || 1e-6));
      const endT = Math.min(to.w / 2 / (Math.abs(unitX) || 1e-6), to.h / 2 / (Math.abs(unitY) || 1e-6));
      const sx = from.x + from.w / 2 + unitX * startT; const sy = from.y + from.h / 2 + unitY * startT;
      const ex = to.x + to.w / 2 - unitX * endT; const ey = to.y + to.h / 2 - unitY * endT;
      return `<path d="M ${sx} ${sy} L ${ex} ${ey}" class="edge" marker-end="url(#arrow)"/>`;
    }).join('');
    const actors = ['shopper', 'seller', 'analyst'];
    const boxes = nodes.map((node) => {
      const labelLines = wrap(node.label, 23);
      const text = labelLines.map((line, index) => `<text x="${node.x + node.w / 2}" y="${node.y + node.h / 2 + (index - (labelLines.length - 1) / 2) * 18 + 5}" text-anchor="middle" class="component-label">${esc(line)}</text>`).join('');
      const shape = node.id === 'database' ? `<path d="M ${node.x} ${node.y + 10} Q ${node.x} ${node.y} ${node.x + node.w / 2} ${node.y} Q ${node.x + node.w} ${node.y} ${node.x + node.w} ${node.y + 10} V ${node.y + node.h - 10} Q ${node.x + node.w / 2} ${node.y + node.h + 8} ${node.x} ${node.y + node.h - 10} Z" class="component db"/>` : `<rect x="${node.x}" y="${node.y}" width="${node.w}" height="${node.h}" rx="10" class="component ${actors.includes(node.id) ? 'actor-box' : ''}"/>`;
      return `<g>${shape}${text}</g>`;
    }).join('');
    return `<svg xmlns="http://www.w3.org/2000/svg" width="${componentWidth}" height="${componentHeight}" viewBox="0 0 ${componentWidth} ${componentHeight}">
      ${svgDefs()}${sharedStyle()}<style>.component{fill:#fff;stroke:#3e8267;stroke-width:2}.db{fill:#edf8f2}.actor-box{fill:#eaf4ef}.component-label{font:700 16px Arial,sans-serif;fill:#153f30}.boundary{fill:#fbfdfc;stroke:#86a797;stroke-width:2;stroke-dasharray:9 7}.boundary-title{font:700 20px Arial,sans-serif;fill:#315e4c}</style>
      <rect class="bg" width="100%" height="100%"/><text x="55" y="52" class="title">UML · Componentes e integraciones</text><text x="55" y="82" class="subtitle">Canales, aplicación Laravel, persistencia y servicios externos</text>
      <rect x="625" y="205" width="1275" height="920" rx="20" class="boundary"/><text x="650" y="242" class="boundary-title">Aplicación Laravel 12</text>
      <g>${componentEdges}</g>${boxes}
    </svg>`;
  }

  const cols = 4;
  const gapX = 135;
  const gapY = 105;
  const startX = 70;
  const rowH = Math.max(225, ...Array.from({ length: Math.ceil(nodes.length / cols) }, (_, row) => Math.max(...nodes.slice(row * cols, (row + 1) * cols).map((node) => node.h))));
  const rows = Math.ceil(nodes.length / cols);
  const pageHeight = Math.max(1180, cardStartY + rows * (rowH + gapY) + 80);
  nodes.forEach((node, index) => {
    node.gridCol = index % cols;
    node.gridRow = Math.floor(index / cols);
    node.x = startX + node.gridCol * (cardW + gapX);
    node.y = cardStartY + node.gridRow * (rowH + gapY);
  });
  const midX = (node) => node.x + cardW / 2;
  const midY = (node) => node.y + node.h / 2;
  const relations = diagram.edges.map((edge) => {
    const from = byId.get(edge.from); const to = byId.get(edge.to);
    if (!from || !to) return '';
    let sx; let sy; let ex; let ey; let path;
    const dx = midX(to) - midX(from); const dy = midY(to) - midY(from);
    const unitX = dx / (Math.hypot(dx, dy) || 1); const unitY = dy / (Math.hypot(dx, dy) || 1);
    const startT = Math.min(cardW / 2 / (Math.abs(unitX) || 1e-6), from.h / 2 / (Math.abs(unitY) || 1e-6));
    const endT = Math.min(cardW / 2 / (Math.abs(unitX) || 1e-6), to.h / 2 / (Math.abs(unitY) || 1e-6));
    sx = midX(from) + unitX * startT; sy = midY(from) + unitY * startT;
    ex = midX(to) - unitX * endT; ey = midY(to) - unitY * endT;
    path = `M ${sx} ${sy} L ${ex} ${ey}`;
    const label = '';
    const sourceMultiplicity = diagram.kind === 'classDiagram'
      ? edge.sourceMultiplicity
      : diagram.kind === 'erDiagram'
        ? ({ '||': '1', 'o|': '0..1', '|{': '1..*', 'o{': '0..*', '}|': '1..*', '}o': '0..*' }[edge.cardinality?.split(/--|\.\./)[0]] || '')
        : '';
    const targetMultiplicity = diagram.kind === 'classDiagram'
      ? edge.targetMultiplicity
      : diagram.kind === 'erDiagram'
        ? ({ '||': '1', 'o|': '0..1', '|{': '1..*', 'o{': '0..*', '}|': '1..*', '}o': '0..*' }[edge.cardinality?.split(/--|\.\./)[1]] || '')
        : '';
    const multiplicities = `${sourceMultiplicity ? `<text x="${sx + unitX * 11}" y="${sy + unitY * 11 - 4}" text-anchor="middle" class="edge-label">${esc(sourceMultiplicity)}</text>` : ''}${targetMultiplicity ? `<text x="${ex - unitX * 11}" y="${ey - unitY * 11 - 4}" text-anchor="middle" class="edge-label">${esc(targetMultiplicity)}</text>` : ''}`;
    const labelSvg = label ? `<text x="${(sx + ex) / 2}" y="${(sy + ey) / 2 - 5}" text-anchor="middle" class="edge-label">${esc(label)}</text>` : '';
    const marker = diagram.kind === 'erDiagram' ? '' : ' marker-end="url(#arrow)"';
    return `<g><path d="${path}" class="edge"${marker}/>${labelSvg}${multiplicities}</g>`;
  }).join('');
  const cards = nodes.map((node) => {
    const heading = node.titleLines.map((line, index) => `<text x="${node.x + 17}" y="${node.y + 29 + index * 20}" class="heading">${esc(line)}</text>`).join('');
    const ruleY = node.y + 39 + node.titleLines.length * 20;
    const details = node.fieldLines.map((line, index) => `<text x="${node.x + 17}" y="${ruleY + 22 + index * 22}" class="detail">${esc(line)}</text>`).join('');
    return `<g><rect x="${node.x}" y="${node.y}" width="${cardW}" height="${node.h}" rx="5" class="entity"/><path d="M ${node.x + 1} ${ruleY} H ${node.x + cardW - 1}" class="rule"/>${heading}${details}</g>`;
  }).join('');
  return `<svg xmlns="http://www.w3.org/2000/svg" width="${width}" height="${pageHeight}" viewBox="0 0 ${width} ${pageHeight}">
    ${svgDefs()}${sharedStyle()}
    <rect class="bg" width="100%" height="100%"/><text x="55" y="52" class="title">${esc(filename.replaceAll('-', ' '))}</text><text x="55" y="82" class="subtitle">FusaShop · entidades y relaciones</text>
    <g>${relations}</g>${cards}
  </svg>`;
}

function svgDefs() {
  return `<defs><marker id="arrow" markerWidth="10" markerHeight="10" refX="8" refY="4" orient="auto"><path d="M 0 0 L 8 4 L 0 8 z" fill="#52796b"/></marker></defs>`;
}

function sharedStyle() {
  return `<style>
    .bg{fill:#f4f8f5}.title{font:700 30px Arial,sans-serif;fill:#10372b}.subtitle{font:15px Arial,sans-serif;fill:#547168}
    .entity{fill:#fff;stroke:#76998a;stroke-width:1.6;filter:drop-shadow(0 3px 5px #173a2d18)}.rule{stroke:#b6cfc3;stroke-width:1.4}
    .heading{font:700 17px Arial,sans-serif;fill:#08694d}.detail{font:13px Consolas,monospace;fill:#243d35}.edge{fill:none;stroke:#52796b;stroke-width:1.6;opacity:.78}.edge-label{font:12px Arial,sans-serif;fill:#244d3d;paint-order:stroke;stroke:#f4f8f5;stroke-width:4px;stroke-linejoin:round}
    .system{fill:#f9fcfa;stroke:#789b8c;stroke-width:2}.system-label{font:700 20px Arial,sans-serif;fill:#315e4c}.usecase{fill:#fff;stroke:#3d8166;stroke-width:1.8}.usecase-label{font:16px Arial,sans-serif;fill:#153f30}.actor path,.actor circle{fill:none;stroke:#1e6045;stroke-width:3}.actor-label{font:700 15px Arial,sans-serif;fill:#153f30}.association{fill:none;stroke:#507565;stroke-width:1.7}.include{fill:none;stroke:#6c9a86;stroke-width:1.5;stroke-dasharray:8 6}.legend text{font:14px Arial,sans-serif;fill:#365e4e}
  </style>`;
}

async function main() {
  fs.mkdirSync(outputDir, { recursive: true });
  const sources = fs.readdirSync(diagramDir).filter((file) => file.endsWith('.mmd'));
  for (const filename of sources) {
    const source = fs.readFileSync(path.join(diagramDir, filename), 'utf8');
    const diagram = parseDiagram(source);
    if (!diagram.nodes.length) throw new Error(`No se encontraron nodos Mermaid en ${filename}`);
    const svg = makeSvg(diagram, path.basename(filename, '.mmd'));
    const output = path.join(outputDir, `${path.basename(filename, '.mmd')}.png`);
    await sharp(Buffer.from(svg)).png().toFile(output);
    console.log(`PNG generado: ${path.relative(root, output)}`);
  }
}

main().catch((error) => { console.error(error); process.exitCode = 1; });
