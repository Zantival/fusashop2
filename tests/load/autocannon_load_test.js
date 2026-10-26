import http from 'node:http';
import https from 'node:https';
import { performance } from 'node:perf_hooks';

// ==============================================================================
// PRUEBA DE CARGA Y ESTRÉS FUSASHOP (Node.js nativo, sin dependencias)
// Compatible con PowerShell, CMD y Bash en Windows / Linux / macOS
// ==============================================================================

// 1. Detección flexible de argumentos de consola y variables de entorno
const args = process.argv.slice(2);
function getArg(name, fallback) {
  for (let i = 0; i < args.length; i++) {
    if (args[i].startsWith(`--${name}=`)) return args[i].split('=')[1];
    if (args[i] === `--${name}` && args[i + 1]) return args[i + 1];
  }
  return fallback;
}

const rawBaseUrl = getArg('url', process.env.BASE_URL || 'http://127.0.0.1:8000');
const baseUrl = new URL(rawBaseUrl);
const targetPath = getArg('path', process.env.TARGET_PATH || '/shop');
const targetUrl = new URL(targetPath, baseUrl);

// Soporta: CONCURRENCY=10 (bash), o node script.js 10 10, o --concurrency=10
const concurrency = Math.max(1, Number.parseInt(
  getArg('concurrency', process.env.CONCURRENCY || (args[0] && !args[0].startsWith('-') ? args[0] : '10')),
  10
));

const durationSeconds = Math.max(1, Number.parseInt(
  getArg('duration', process.env.DURATION || (args[1] && !args[1].startsWith('-') ? args[1] : '10')),
  10
));

const timeoutMs = Math.max(1000, Number.parseInt(
  getArg('timeout', process.env.REQUEST_TIMEOUT || '15000'),
  10
));

if (!['http:', 'https:'].includes(targetUrl.protocol)) {
  console.error('❌ Error: La URL debe comenzar con http:// o https://');
  process.exit(1);
}

const transport = targetUrl.protocol === 'https:' ? https : http;
const agent = new (targetUrl.protocol === 'https:' ? https.Agent : http.Agent)({
  keepAlive: true,
  maxSockets: concurrency * 2,
  keepAliveMsecs: 2000,
});

// 2. Comprobación preliminar de salud del servidor (Health Check)
console.log('='.repeat(70));
console.log('🚀 FusaShop — Prueba de Carga y Resiliencia HTTP');
console.log('='.repeat(70));
console.log(`📡 URL Objetivo:  ${targetUrl.href}`);
console.log(`👥 Concurrencia:  ${concurrency} usuarios simultáneos`);
console.log(`⏱️  Duración:      ${durationSeconds} segundos`);
console.log(`⏳ Timeout:       ${timeoutMs} ms`);
console.log('-'.repeat(70));
process.stdout.write('🔍 Verificando disponibilidad del servidor local... ');

try {
  await new Promise((resolve, reject) => {
    const healthReq = transport.get(targetUrl, { agent, timeout: 5000 }, (res) => {
      res.resume(); // consumir flujo
      if (res.statusCode >= 200 && res.statusCode < 400) {
        resolve();
      } else {
        reject(new Error(`El servidor respondió con código HTTP ${res.statusCode}`));
      }
    });
    healthReq.on('timeout', () => healthReq.destroy(new Error('Tiempo de espera agotado al conectar con el servidor (5s)')));
    healthReq.on('error', reject);
  });
  console.log('✅ Servidor en línea (HTTP 200 OK)\n');
} catch (err) {
  console.log('❌ FALLO');
  console.error('\n' + '!'.repeat(70));
  console.error('⚠️  NO SE PUDO CONECTAR AL SERVIDOR DE FUSASHOP');
  console.error('!'.repeat(70));
  console.error(`Detalle del error: ${err.message}`);
  console.error('\nCausa más común: El servidor de Laravel no está encendido o no escucha en este puerto.');
  console.error('\n💡 Solución: Abre OTRA ventana de terminal y ejecuta:');
  console.error('   php artisan serve\n');
  console.error('Luego vuelve a ejecutar este comando:');
  console.error(`   node tests/load/autocannon_load_test.js ${concurrency} ${durationSeconds}\n`);
  process.exit(1);
}

// 3. Ejecución de la prueba de carga
const results = { attempted: 0, completed: 0, errors: 0, non2xx: 0, bytes: 0, latencies: [] };
const endAt = Date.now() + durationSeconds * 1000;

function requestOnce() {
  results.attempted++;
  const startedAt = performance.now();

  return new Promise((resolve) => {
    let settled = false;
    const finish = (error = null) => {
      if (settled) return;
      settled = true;
      if (error) results.errors++;
      resolve(error);
    };

    const request = transport.get(targetUrl, {
      agent,
      headers: {
        'User-Agent': 'FusaShop-LoadTest-Runner/1.0',
        'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Connection': 'keep-alive',
      },
    }, (response) => {
      if (response.statusCode < 200 || response.statusCode >= 300) {
        results.non2xx++;
      }
      response.on('data', (chunk) => {
        results.bytes += chunk.length;
      });
      response.on('end', () => {
        results.completed++;
        results.latencies.push(performance.now() - startedAt);
        finish();
      });
      response.on('error', finish);
    });

    request.setTimeout(timeoutMs, () => {
      request.destroy(new Error(`Timeout (${timeoutMs} ms)`));
    });
    request.on('error', finish);
  });
}

async function worker() {
  while (Date.now() < endAt) {
    const error = await requestOnce();
    if (error) {
      // Pausa corta preventiva si hubo congestión para evitar tormenta de sockets en Windows
      await new Promise((r) => setTimeout(r, 60));
    }
  }
}

console.log('⚡ Ejecutando prueba de estrés...');
await Promise.all(Array.from({ length: concurrency }, () => worker()));

// 4. Procesamiento de Métricas y Estadísticas
const sortedLatencies = results.latencies.toSorted((a, b) => a - b);
const averageLatency = sortedLatencies.length
  ? sortedLatencies.reduce((sum, value) => sum + value, 0) / sortedLatencies.length
  : 0;

function percentile(sortedValues, percentileValue) {
  if (!sortedValues.length) return 0;
  return sortedValues[Math.ceil(percentileValue * sortedValues.length) - 1];
}

const reqPerSec = (results.completed / durationSeconds).toFixed(2);
const errorRate = results.attempted > 0
  ? ((results.errors / results.attempted) * 100).toFixed(2)
  : '0.00';

console.log('\n' + '='.repeat(70));
console.log('📊 REPORTE DE RESULTADOS DE PRUEBA DE CARGA (FUSASHOP)');
console.log('='.repeat(70));
console.log(`📦 Solicitudes intentadas:   ${results.attempted}`);
console.log(`✅ Respuestas completadas:   ${results.completed}`);
console.log(`🚀 Rendimiento (Throughput): ${reqPerSec} req/segundo`);
console.log(`⏱️  Latencia promedio:        ${averageLatency.toFixed(2)} ms`);
console.log(`📈 Latencia p95 (95% usuarios): ${percentile(sortedLatencies, 0.95).toFixed(2)} ms`);
console.log(`📈 Latencia p99 (99% usuarios): ${percentile(sortedLatencies, 0.99).toFixed(2)} ms`);
console.log(`📉 Latencia mínima / máxima: ${sortedLatencies[0]?.toFixed(2) || 0} ms / ${sortedLatencies[sortedLatencies.length - 1]?.toFixed(2) || 0} ms`);
console.log(`❌ Errores de conexión:      ${results.errors} (${errorRate}%)`);
console.log(`⚠️  Respuestas no 2xx:        ${results.non2xx}`);
console.log(`🌐 Volumen de datos:         ${(results.bytes / 1024 / 1024).toFixed(2)} MB`);
console.log('='.repeat(70));

if (results.errors === 0 && results.completed > 0) {
  console.log('🎉 RESULTADO: Prueba aprobada con éxito. Cero errores de conexión.');
} else if (results.completed > 0) {
  console.log('⚠️  RESULTADO: Prueba completada con observaciones de concurrencia.');
} else {
  console.log('❌ RESULTADO: No se completaron peticiones.');
  process.exitCode = 1;
}
