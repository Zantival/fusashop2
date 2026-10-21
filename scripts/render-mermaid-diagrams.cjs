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
      if (match) edges.push({ from: match[1], to: match[5], label: `${match[2]} ${match[6] || ''} ${match[4]}`.trim() });
    }
  } else if (kind === 'erDiagram') {
    const text = source.replace(/\r/g, '');
    for (const match of text.matchAll(/(\w+)\s*\{([\s\S]*?)\}/g)) {
      const rows = match[2].split('\n').map((row) => row.trim()).filter(Boolean);
      addNode(match[1], match[1], rows);
    }
    for (const line of lines) {
      const match = line.trim().match(/^(\w+)\s+([|o}{]{1,3}(?:--|\.\.)[|o}{]{1,3})\s+(\w+)(?:\s*:\s*(.*))?$/);
      if (match) {
        addNode(match[1], match[1]);
        addNode(match[3], match[3]);
        edges.push({ from: match[1], to: match[3], label: match[4] || '' });
      }
    }
  } else {
    for (const line of lines) {
      const definition = line.trim().match(/^(\w+)\s*(?:\["([^"]+)"\]|\(\[([^\]]+)\]\)|\[([^\]]+)\]|\(([^)]+)\))/);
      if (definition) {
        const label = (definition[2] || definition[3] || definition[4] || definition[5] || definition[1])
          .trim().replace(/^\(\s*["']?/, '').replace(/["']?\s*\)$/, '');
        addNode(definition[1], label);
      }
      const chain = line.trim().match(/^([\w]+(?:\s*-->\s*[\w]+)+)$/);
      if (chain) {
        const parts = chain[1].split(/\s*-->\s*/);
        for (let index = 0; index < parts.length - 1; index++) {
          addNode(parts[index], parts[index]); addNode(parts[index + 1], parts[index + 1]);
          edges.push({ from: parts[index], to: parts[index + 1], label: '' });
        }
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
  const cols = diagram.nodes.length > 12 ? 4 : diagram.nodes.length > 5 ? 3 : 2;
  const cardW = 310;
  const gapX = 38;
  const gapY = 42;
  const startX = 55;
  const startY = 142;
  const rowH = 238;
  const rows = Math.ceil(diagram.nodes.length / cols);
  const width = startX * 2 + cols * cardW + (cols - 1) * gapX;
  const relationRows = Math.ceil(diagram.edges.length / 2);
  const relationY = startY + rows * rowH + 20;
  const relationH = diagram.edges.length ? 58 + relationRows * 25 : 0;
  const height = relationY + relationH + 40;

  const positioned = diagram.nodes.map((node, index) => ({
    ...node,
    x: startX + (index % cols) * (cardW + gapX),
    y: startY + Math.floor(index / cols) * rowH,
  }));
  const byId = new Map(positioned.map((node) => [node.id, node]));
  const cards = positioned.map((node) => {
    const rawRows = node.rows.length ? node.rows : wrap(node.label, 34);
    const fieldLines = rawRows.flatMap((row) => wrap(row, 34));
    const headingLines = wrap(node.label, 27);
    const cardH = Math.max(116, 66 + headingLines.length * 19 + fieldLines.length * 23);
    const heading = headingLines.map((row, i) => `<text x="${node.x + 18}" y="${node.y + 31 + i * 19}" class="heading">${esc(row)}</text>`).join('');
    const ruleY = node.y + 42 + headingLines.length * 19;
    const details = fieldLines.map((row, i) => `<text x="${node.x + 18}" y="${ruleY + 25 + i * 23}" class="detail">${esc(row)}</text>`).join('');
    return `<g><rect x="${node.x}" y="${node.y}" width="${cardW}" height="${cardH}" rx="14" class="card"/><path d="M ${node.x + 1} ${ruleY} H ${node.x + cardW - 1}" class="rule"/>${heading}${details}</g>`;
  }).join('');

  const relations = diagram.edges.map((edge) => {
    const source = byId.get(edge.from)?.label || edge.from;
    const target = byId.get(edge.to)?.label || edge.to;
    const relation = `${source} → ${target}${edge.label ? ` · ${edge.label}` : ''}`;
    return relation;
  });
  const relationPanel = diagram.edges.length ? `<g>
    <rect x="${startX}" y="${relationY}" width="${width - startX * 2}" height="${relationH}" rx="14" class="panel"/>
    <text x="${startX + 18}" y="${relationY + 29}" class="heading">Relaciones / flujos</text>
    ${relations.map((relation, index) => {
      const column = index % 2;
      const row = Math.floor(index / 2);
      const x = startX + 18 + column * ((width - startX * 2) / 2);
      const y = relationY + 53 + row * 25;
      return `<text x="${x}" y="${y}" class="relation">${esc(wrap(relation, 64)[0])}</text>`;
    }).join('')}
  </g>` : '';

  return `<svg xmlns="http://www.w3.org/2000/svg" width="${width}" height="${height}" viewBox="0 0 ${width} ${height}">
  <defs><marker id="arrow" markerWidth="10" markerHeight="10" refX="8" refY="4" orient="auto"><path d="M 0 0 L 8 4 L 0 8 z" fill="#69867c"/></marker></defs>
  <style>
    .bg{fill:#f4f8f5}.title{font:700 30px Arial,sans-serif;fill:#10372b}.subtitle{font:15px Arial,sans-serif;fill:#547168}
    .card{fill:#fff;stroke:#a9c2b7;stroke-width:1.5;filter:drop-shadow(0 4px 8px #173a2d1a)}.panel{fill:#fff;stroke:#a9c2b7;stroke-width:1.5}.rule{stroke:#dce9e3;stroke-width:1.5}
    .heading{font:700 18px Arial,sans-serif;fill:#08694d}.detail{font:14px Consolas,monospace;fill:#243d35}.relation{font:12px Arial,sans-serif;fill:#344f45}
  </style>
  <rect class="bg" width="100%" height="100%"/><text x="55" y="54" class="title">${esc(filename.replaceAll('-', ' '))}</text><text x="55" y="83" class="subtitle">FusaShop · generado desde la fuente Mermaid</text>
  ${cards}${relationPanel}
  </svg>`;
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
