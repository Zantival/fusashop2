const fs = require('fs');
const path = require('path');

const svg1 = fs.readFileSync('docs/diagrama-casos-de-uso-cliente-munecos.svg', 'utf-8');
const svg2 = fs.readFileSync('docs/diagrama-casos-de-uso-comercio-munecos.svg', 'utf-8');

const html = `<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Diagrama de Casos de Uso con Muñequitos — FusaShop</title>
  <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
</head>
<body class="bg-transparent text-slate-900 antialiased p-2 sm:p-4">
  <div class="bg-white border border-slate-300 rounded-2xl p-4 sm:p-6 shadow-sm overflow-hidden flex flex-col space-y-4">
    
    <!-- Encabezado -->
    <div class="border-b border-slate-200 pb-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
      <div>
        <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">
          DIAGRAMA GENERAL DE CASOS DE USO (UML 2.5) CON MUÑEQUITOS
        </h2>
        <p class="text-xs text-slate-500">Plataforma FusaShop · Especificación Formal en Blanco y Negro (Stick Figures)</p>
      </div>
      <!-- Selector de Pestañas -->
      <div class="flex items-center gap-1.5 bg-slate-100 p-1 rounded-xl border border-slate-300 self-start sm:self-auto">
        <button id="tab-btn-client" onclick="switchTab('client')" class="px-3 py-1 rounded-lg text-xs font-bold transition-all bg-white text-slate-900 shadow-sm border border-slate-300">
          1. Cliente / Comprador
        </button>
        <button id="tab-btn-merchant" onclick="switchTab('merchant')" class="px-3 py-1 rounded-lg text-xs font-medium text-slate-600 hover:text-slate-900 transition-all">
          2. Comerciante &amp; Admin
        </button>
      </div>
    </div>

    <!-- Pestaña 1: Cliente / Comprador -->
    <div id="tab-content-client" class="block w-full overflow-x-auto rounded-xl border border-slate-200 bg-white p-2">
      <div class="flex justify-center min-w-[750px]">
        ${svg1}
      </div>
    </div>

    <!-- Pestaña 2: Comerciante & Analista Admin -->
    <div id="tab-content-merchant" class="hidden w-full overflow-x-auto rounded-xl border border-slate-200 bg-white p-2">
      <div class="flex justify-center min-w-[750px]">
        ${svg2}
      </div>
    </div>

    <!-- Barra de acciones y leyendas -->
    <div class="pt-2 border-t border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between text-xs text-slate-600 gap-2">
      <div class="flex items-center gap-3">
        <span class="inline-flex items-center gap-1 font-semibold text-slate-800">
          <span>🧍 Actores Stick-Figure</span>
        </span>
        <span class="inline-flex items-center gap-1 font-semibold text-slate-800">
          <span>⬭ Elipses de Casos de Uso</span>
        </span>
        <span class="inline-flex items-center gap-1 font-semibold text-slate-800">
          <span>- - &gt; Inclusión / Extensión</span>
        </span>
      </div>
      <span class="text-[11px] text-slate-500 font-medium">Archivos vectoriales guardados en carpeta <code>docs/</code></span>
    </div>

  </div>

  <script>
    function switchTab(tab) {
      const clientContent = document.getElementById('tab-content-client');
      const merchantContent = document.getElementById('tab-content-merchant');
      const clientBtn = document.getElementById('tab-btn-client');
      const merchantBtn = document.getElementById('tab-btn-merchant');

      if (tab === 'client') {
        clientContent.classList.remove('hidden');
        clientContent.classList.add('block');
        merchantContent.classList.remove('block');
        merchantContent.classList.add('hidden');

        clientBtn.className = 'px-3 py-1 rounded-lg text-xs font-bold transition-all bg-white text-slate-900 shadow-sm border border-slate-300';
        merchantBtn.className = 'px-3 py-1 rounded-lg text-xs font-medium text-slate-600 hover:text-slate-900 transition-all';
      } else {
        clientContent.classList.remove('block');
        clientContent.classList.add('hidden');
        merchantContent.classList.remove('hidden');
        merchantContent.classList.add('block');

        merchantBtn.className = 'px-3 py-1 rounded-lg text-xs font-bold transition-all bg-white text-slate-900 shadow-sm border border-slate-300';
        clientBtn.className = 'px-3 py-1 rounded-lg text-xs font-medium text-slate-600 hover:text-slate-900 transition-all';
      }
    }
  </script>
</body>
</html>`;

const outPath = 'C:/Users/HUAWEI/.gemini/antigravity/brain/0f8d43fb-364c-4fbe-a1e6-96bd72c75a2e/use_case_diagram.html';
fs.writeFileSync(outPath, html, 'utf-8');
console.log('use_case_diagram.html compiled with inline SVGs successfully.');
