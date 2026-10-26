import http from 'k6/http';
import { check, sleep, group } from 'k6';

// ==============================================================================
// SCRIPT DE PRUEBA DE CARGA Y ESTRÉS PARA PLATAFORMA FUSASHOP (k6)
// Simula tráfico concurrente en vitrina comercial, búsquedas y checkout
// ==============================================================================

export const options = {
  // Umbrales de calidad de servicio (SLO / SLA) para la tesis
  thresholds: {
    http_req_failed: ['rate<0.01'],          // Menos del 1% de solicitudes fallidas (5xx/4xx)
    http_req_duration: ['p(95)<500'],        // El 95% de las peticiones debe responder en < 500ms
    'http_req_duration{tipo:catalogo}': ['p(95)<300'], // Catálogo debe responder en < 300ms
    'http_req_duration{tipo:checkout}': ['p(95)<700'], // Checkout completo en < 700ms
  },
  
  // Escenario de carga escalonada (Ramping VUs)
  stages: [
    { duration: '30s', target: 20 },  // Fase 1: Calentamiento hasta 20 usuarios concurrentes
    { duration: '1m',  target: 50 },  // Fase 2: Carga media normal (50 usuarios)
    { duration: '2m',  target: 100 }, // Fase 3: Carga pico de comercio local (100 usuarios)
    { duration: '30s', target: 0 },   // Fase 4: Desaceleración y recuperación
  ],
};

const BASE_URL = __ENV.BASE_URL || 'http://127.0.0.1:8000';

export default function () {
  // 1. Navegación a la Página de Inicio (Home Pública)
  group('1. Carga de Home y Directorio', function () {
    const resHome = http.get(`${BASE_URL}/shop`, {
      tags: { tipo: 'catalogo' },
    });
    check(resHome, {
      'Home responde HTTP 200': (r) => r.status === 200,
      'Home contiene marca FusaShop': (r) => r.body.includes('FusaShop'),
    });
    sleep(1);
  });

  // 2. Búsqueda de Productos con Filtros Territoriales
  group('2. Búsqueda y Filtros Territoriales', function () {
    const searchTerms = ['cafe', 'almojabana', 'artesania', 'flores'];
    const term = searchTerms[Math.floor(Math.random() * searchTerms.length)];

    const resSearch = http.get(`${BASE_URL}/shop/catalog?search=${term}&sector=comuna-centro`, {
      tags: { tipo: 'catalogo' },
    });
    check(resSearch, {
      'Búsqueda responde HTTP 200': (r) => r.status === 200,
      'Latencia de búsqueda < 400ms': (r) => r.timings.duration < 400,
    });
    sleep(1.5);
  });

  // 3. Consulta de Ficha Detallada de Producto
  group('3. Visualización de Producto', function () {
    const productId = Math.floor(Math.random() * 10) + 1; // IDs 1 a 10
    const resProduct = http.get(`${BASE_URL}/shop/product/${productId}`, {
      tags: { tipo: 'catalogo' },
    });
    check(resProduct, {
      'Ficha responde HTTP 200 o 404 controlado': (r) => r.status === 200 || r.status === 404,
    });
    sleep(2);
  });

  // 4. Cotización y Simulación de Carrito / Checkout
  group('4. Carrito y Pre-Checkout', function () {
    const resCart = http.get(`${BASE_URL}/shop/cart`, {
      tags: { tipo: 'checkout' },
    });
    check(resCart, {
      'Carrito responde HTTP 200': (r) => r.status === 200 || r.status === 302,
    });
    sleep(1);
  });
}
