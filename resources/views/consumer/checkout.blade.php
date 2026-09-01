@extends('layouts.app')
@section('title','Checkout')
@section('content')
<div class="max-w-5xl mx-auto px-6 py-8">
  <h1 class="text-3xl font-['Manrope'] font-bold text-on-background mb-8">Finalizar Compra</h1>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Payment Form -->
    <div class="space-y-6">
      <!-- Card Preview -->
      <div class="relative h-52 rounded-2xl bg-primary-gradient p-6 text-white shadow-xl overflow-hidden">
        <div class="absolute top-0 right-0 w-48 h-48 bg-white/5 rounded-full -translate-y-12 translate-x-12"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/5 rounded-full translate-y-12 -translate-x-12"></div>
        <div class="relative z-10 h-full flex flex-col justify-between">
          <div class="flex justify-between items-start">
            <div class="w-10 h-8 bg-[#feb700] rounded-md flex items-center justify-center">
              <span class="material-symbols-outlined text-sm" style="font-variation-settings:'FILL' 1">credit_card</span>
            </div>
            <span class="font-['Manrope'] font-bold text-xl tracking-wider">VISA</span>
          </div>
          <div>
            <p class="font-mono text-xl tracking-[0.3em] mb-3" id="preview-number">#### #### #### ####</p>
            <div class="flex justify-between items-end">
              <div>
                <p class="text-white/70 text-xs uppercase tracking-wider">Titular</p>
                <p class="font-semibold uppercase" id="preview-name">{{ strtoupper(auth()->user()->name) }}</p>
              </div>
              <div class="text-right">
                <p class="text-white/70 text-xs uppercase tracking-wider">Vence</p>
                <p class="font-semibold" id="preview-expiry">MM/AA</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <form method="POST" action="{{ route('consumer.checkout.process') }}" class="space-y-5" id="checkout-form">
        @csrf

        @if($errors->any())
          <div class="bg-[#ffdad6] text-[#ba1a1a] px-4 py-3 rounded-xl text-sm">
            @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
          </div>
        @endif

        <div>
          <label class="block text-sm font-semibold text-on-surface mb-2">Método de pago</label>
          <div class="grid grid-cols-3 gap-3">
            @foreach(['card'=>['credit_card','Tarjeta'],'transfer'=>['account_balance','Transferencia'],'cash'=>['payments','Efectivo']] as $val=>[$icon,$label])
            <label class="cursor-pointer">
              <input type="radio" name="payment_method" value="{{ $val }}" class="sr-only peer" {{ $val==='card'?'checked':'' }}/>
              <div class="flex flex-col items-center gap-1 p-3 bg-surface-container-low rounded-xl border-2 border-transparent peer-checked:border-primary peer-checked:bg-[#6efcb9]/20 transition-all">
                <span class="material-symbols-outlined text-primary">{{ $icon }}</span>
                <span class="text-xs font-semibold text-on-surface">{{ $label }}</span>
              </div>
            </label>
            @endforeach
          </div>
        </div>

        <div id="card-fields">
          <div class="mb-4">
            <label class="block text-sm font-semibold text-on-surface mb-2">Número de tarjeta</label>
            <input type="text" name="card_number" id="card-number" maxlength="19" placeholder="1234 5678 9012 3456"
              class="w-full px-4 py-3 bg-surface-container-highest rounded-xl border-0 outline-none focus:ring-2 focus:ring-primary font-mono text-lg"/>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-on-surface mb-2">Vencimiento</label>
              <input type="text" name="card_expiry" id="card-expiry" maxlength="5" placeholder="MM/AA"
                class="w-full px-4 py-3 bg-surface-container-highest rounded-xl border-0 outline-none focus:ring-2 focus:ring-primary text-center font-mono"/>
            </div>
            <div>
              <label class="block text-sm font-semibold text-on-surface mb-2">CVV</label>
              <input type="text" name="card_cvv" maxlength="3" placeholder="123"
                class="w-full px-4 py-3 bg-surface-container-highest rounded-xl border-0 outline-none focus:ring-2 focus:ring-primary text-center font-mono"/>
            </div>
          </div>
        </div>

        {{-- ─── Dirección de Envío con Geolocalización Gratuita ──────────── --}}
        <div>
          <label class="block text-sm font-semibold text-on-surface mb-2 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-base">local_shipping</span>
            Dirección de envío
          </label>

          {{-- Buscador con autocomplete Nominatim --}}
          <div class="relative mb-2">
            <span class="material-symbols-outlined absolute left-3 top-3.5 text-on-surface-variant text-base z-10">search</span>
            <input id="shipping-search" type="text" placeholder="Busca tu dirección..." autocomplete="off"
              class="w-full pl-10 pr-12 py-3 bg-surface-container-highest rounded-xl border-0 outline-none focus:ring-2 focus:ring-primary text-sm"
              value="{{ old('shipping_address', auth()->user()->address) }}">
            {{-- Botón GPS --}}
            <button type="button" id="shipping-gps-btn" title="Detectar mi ubicación"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-primary hover:text-primary/70 p-1 rounded-lg hover:bg-primary/10 transition-colors">
              <span class="material-symbols-outlined text-lg">my_location</span>
            </button>
            {{-- Sugerencias --}}
            <ul id="shipping-suggestions" class="hidden absolute top-full left-0 w-full z-50 bg-white rounded-xl shadow-xl border border-outline-variant/20 mt-1 max-h-52 overflow-y-auto"></ul>
          </div>

          {{-- Campo oculto real que se envía al servidor --}}
          <textarea name="shipping_address" id="shipping-address-hidden" required rows="2"
            class="w-full px-4 py-3 bg-surface-container-highest rounded-xl border-0 outline-none focus:ring-2 focus:ring-primary resize-none text-sm text-on-surface-variant"
            placeholder="La dirección aparecerá aquí...">{{ old('shipping_address', auth()->user()->address) }}</textarea>

          {{-- Mini-mapa de confirmación --}}
          <div id="shipping-map-wrapper" class="mt-3 overflow-hidden rounded-xl border border-outline-variant/20 hidden">
            <div class="bg-surface-container-low px-3 py-2 flex items-center gap-2 text-xs text-on-surface-variant font-semibold">
              <span class="material-symbols-outlined text-primary text-sm">pin_drop</span>
              Confirma el punto de entrega — puedes arrastrar el marcador
            </div>
            <div id="shipping-map" class="w-full h-44"></div>
          </div>

          {{-- Inputs ocultos de coordenadas --}}
          <input type="hidden" name="shipping_lat" id="shipping-lat">
          <input type="hidden" name="shipping_lng" id="shipping-lng">
        </div>

        <button type="submit" class="w-full py-4 bg-primary-gradient text-white font-semibold rounded-xl hover:opacity-90 active:scale-95 transition-all shadow-lg shadow-primary/20 flex items-center justify-center gap-2 text-lg">
          <span class="material-symbols-outlined">lock</span>
          Pagar ${{ number_format($cart->total(),0,',','.') }}
        </button>
      </form>
    </div>

    <!-- Order Summary -->
    <div>
      <div class="bg-surface-container-lowest rounded-2xl p-6 shadow-[0_12px_32px_rgba(27,28,28,.06)]">
        <h2 class="font-['Manrope'] font-bold text-on-background text-xl mb-6">Resumen del pedido</h2>
        <div class="space-y-4 mb-6">
          @foreach($cart->items as $item)
          <div class="flex gap-3">
            <div class="w-16 h-16 rounded-xl overflow-hidden shrink-0">
              <x-product-image :product="$item->product" class="w-full h-full"/>
            </div>
            <div class="flex-1">
              <p class="font-semibold text-on-background text-sm">{{ e($item->product->name) }}</p>
              <p class="text-on-surface-variant text-xs">Cantidad: {{ $item->quantity }}</p>
            </div>
            <span class="font-bold text-on-background">${{ number_format($item->quantity*$item->product->price,0,',','.') }}</span>
          </div>
          @endforeach
        </div>
        <div class="border-t border-outline-variant/30 pt-4 space-y-4">
          @if($availablePoints > 0)
            <div class="p-4 bg-surface-container rounded-2xl border border-surface-container-highest">
              <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                  <span class="material-symbols-outlined text-[#feb700]" style="font-variation-settings:'FILL' 1">stars</span>
                  <span class="text-sm font-bold text-on-surface">Puntos disponibles</span>
                </div>
                <span class="text-sm font-black text-primary">{{ number_format($availablePoints, 0, ',', '.') }}</span>
              </div>
              
              <div class="flex items-center justify-between p-3 bg-white rounded-xl border border-outline-variant/20">
                <div>
                  <p class="text-[10px] font-bold text-on-surface-variant uppercase">Descuento aplicable</p>
                  <p class="text-sm font-black text-primary" id="loyalty-discount-value">-${{ number_format($availablePoints * 50, 0, ',', '.') }}</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" name="use_points" id="use-points-check" value="1" class="sr-only peer">
                  <div class="w-11 h-6 bg-surface-container-highest peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                </label>
              </div>
            </div>
          @endif

          <div class="flex justify-between font-bold text-xl text-on-background">
            <span>Total a pagar</span>
            <span class="text-primary" id="final-total" data-base="{{ $cart->total() }}">${{ number_format($cart->total(), 0, ',', '.') }}</span>
          </div>

          @php $pointsToEarn = floor($cart->total() / 1000); @endphp
          @if($pointsToEarn > 0)
            <div class="p-3 bg-primary/5 rounded-xl border border-primary/10 flex items-center gap-3">
              <div class="w-8 h-8 rounded-full bg-[#feb700] flex items-center justify-center text-[#6b4b00]">
                <span class="material-symbols-outlined text-[18px]" style="font-variation-settings:'FILL' 1">stars</span>
              </div>
              <div>
                <p class="text-xs font-black text-on-surface">Ganarás puntos extra</p>
                <p class="text-[10px] text-on-surface-variant leading-tight">Por cada $1.000 pagados hoy.</p>
              </div>
            </div>
          @endif

          <p class="text-xs text-on-surface-variant mt-4 flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">verified_user</span>
            Pago seguro con encriptación SSL
          </p>
        </div>
      </div>
    </div>
  </div>
</div>
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const cardNum=document.getElementById('card-number');
const cardExp=document.getElementById('card-expiry');
const previewNum=document.getElementById('preview-number');
const previewExp=document.getElementById('preview-expiry');

cardNum?.addEventListener('input',function(){
  let v=this.value.replace(/\D/g,'').substring(0,16);
  this.value=v.replace(/(.{4})/g,'$1 ').trim();
  previewNum.textContent=this.value||'#### #### #### ####';
});

cardExp?.addEventListener('input',function(){
  let v=this.value.replace(/\D/g,'');
  if(v.length > 4) v = v.substring(0,4);
  if(v.length>=2) v=v.substring(0,2)+'/'+v.substring(2,4);
  this.value=v;
  previewExp.textContent=this.value||'MM/AA';
  
  // Validation colors
  if(v.length === 5) {
    const [m, y] = v.split('/').map(Number);
    const now = new Date();
    const curY = parseInt(now.getFullYear().toString().slice(-2));
    const curM = now.getMonth() + 1;
    const isValid = m >= 1 && m <= 12 && (y > curY || (y === curY && m >= curM));
    this.classList.toggle('ring-2', true);
    this.classList.toggle('ring-error', !isValid);
    this.classList.toggle('ring-primary', isValid);
  } else {
    this.classList.remove('ring-2', 'ring-error', 'ring-primary');
  }
});

document.getElementById('checkout-form').addEventListener('submit', function(e) {
  if (document.querySelector('input[name="payment_method"]:checked').value === 'card') {
    const v = cardExp.value;
    if (!/^\d{2}\/\d{2}$/.test(v)) {
      e.preventDefault();
      alert('La fecha debe tener formato MM/AA');
      return;
    }
    const [m, y] = v.split('/').map(Number);
    const now = new Date();
    const curY = parseInt(now.getFullYear().toString().slice(-2));
    const curM = now.getMonth() + 1;
    if (m < 1 || m > 12 || y < curY || (y === curY && m < curM)) {
      e.preventDefault();
      alert('La tarjeta está vencida o el mes es inválido');
    }
  }
});

document.querySelectorAll('input[name=payment_method]').forEach(r=>{
  r.addEventListener('change',function(){
    document.getElementById('card-fields').style.display=this.value==='card'?'block':'none';
  });
});

const usePointsCheck = document.getElementById('use-points-check');
const finalTotalEl = document.getElementById('final-total');
const submitBtn = document.querySelector('button[type="submit"]');
const availablePts = {{ $availablePoints ?? 0 }};

usePointsCheck?.addEventListener('change', function() {
    const baseTotal = parseInt(finalTotalEl.dataset.base);
    const discount = this.checked ? (availablePts * 50) : 0;
    const finalTotal = Math.max(baseTotal - discount, 0);
    
    // Update display
    finalTotalEl.textContent = '$' + finalTotal.toLocaleString('es-CO');
    
    // Update button text
    if (submitBtn) {
        submitBtn.innerHTML = `<span class="material-symbols-outlined">lock</span> Pagar $${finalTotal.toLocaleString('es-CO')}`;
    }
});
</script>

<script>
// ─── Geolocalización de Envío (Leaflet + Nominatim) ─────────────────────────────────
(function() {
  var shippingMap, shippingMarker;
  var shippingSearchInput = document.getElementById('shipping-search');
  var shippingHiddenInput = document.getElementById('shipping-address-hidden');
  var shippingSugList     = document.getElementById('shipping-suggestions');
  var shippingMapWrapper  = document.getElementById('shipping-map-wrapper');
  var shippingLatInput    = document.getElementById('shipping-lat');
  var shippingLngInput    = document.getElementById('shipping-lng');

  if (!shippingSearchInput) return; // Guard: sale si los elementos no existen

  function initShippingMap(lat, lng) {
    var mapEl = document.getElementById('shipping-map');
    if (!mapEl) return;
    
    if (typeof L === 'undefined') { 
      shippingMapWrapper.classList.remove('hidden');
      mapEl.innerHTML = '<div class="p-4 text-error font-bold flex items-center gap-2"><span class="material-symbols-outlined">error</span> Leaflet.js no pudo cargar.</div>';
      console.warn('Leaflet aun no cargó'); 
      return; 
    }
    
    try {
      shippingMapWrapper.classList.remove('hidden');

    if (shippingMap) {
      shippingMap.setView([lat, lng], 16);
      shippingMarker.setLatLng([lat, lng]);
      setTimeout(function() { shippingMap.invalidateSize(); }, 100);
      return;
    }

    shippingMap = L.map('shipping-map', { zoomControl: true }).setView([lat, lng], 16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
      maxZoom: 19
    }).addTo(shippingMap);

    var icon = L.divIcon({
      className: '',
      html: '<div style="width:32px;height:32px;background:linear-gradient(135deg,#006c47,#00b67a);border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:3px solid white;box-shadow:0 4px 12px rgba(0,108,71,.4);"></div>',
      iconSize: [32, 32],
      iconAnchor: [16, 32],
    });

    shippingMarker = L.marker([lat, lng], { draggable: true, icon: icon }).addTo(shippingMap);

    shippingMarker.on('dragend', function() {
      var pos = shippingMarker.getLatLng();
      shippingLatInput.value = pos.lat.toFixed(7);
      shippingLngInput.value = pos.lng.toFixed(7);
      reverseGeocodeShipping(pos.lat, pos.lng);
    });

    shippingMap.on('click', function(e) {
      shippingMarker.setLatLng(e.latlng);
      shippingLatInput.value = e.latlng.lat.toFixed(7);
      shippingLngInput.value = e.latlng.lng.toFixed(7);
      reverseGeocodeShipping(e.latlng.lat, e.latlng.lng);
    });

    setTimeout(function() { shippingMap.invalidateSize(); }, 200);

    } catch (e) {
      document.getElementById('shipping-map').innerHTML = '<div class="p-4 text-error font-bold flex items-center gap-2"><span class="material-symbols-outlined">error</span> Error del mapa: ' + e.message + '</div>';
    }
  }

  function reverseGeocodeShipping(lat, lng) {
    fetch('https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=' + lat + '&lon=' + lng + '&addressdetails=1', {
      headers: { 'Accept-Language': 'es' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data && data.display_name) {
        shippingSearchInput.value = data.display_name.split(',')[0];
        shippingHiddenInput.value = data.display_name;
      }
    })
    .catch(function() {});
  }

  // ─── Autocomplete Nominatim ───────────────────────────────────────────────────
  var shippingTimer;
  shippingSearchInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      var firstSuggestion = shippingSugList.querySelector('li');
      if (firstSuggestion && !shippingSugList.classList.contains('hidden')) {
        firstSuggestion.click();
      } else {
        var q = this.value.trim().replace(/#/g, ' ');
        if (q.length >= 3) {
          shippingSearchInput.disabled = true;
          fetch('https://nominatim.openstreetmap.org/search?format=jsonv2&q=' + encodeURIComponent(q) + '&countrycodes=co&limit=1')
          .then(function(r){ return r.json(); })
          .then(function(results){
            shippingSearchInput.disabled = false;
            if (results.length > 0) {
              var lat = parseFloat(results[0].lat);
              var lng = parseFloat(results[0].lon);
              shippingSearchInput.value = results[0].display_name.split(',')[0];
              shippingHiddenInput.value = results[0].display_name;
              shippingLatInput.value    = lat.toFixed(7);
              shippingLngInput.value    = lng.toFixed(7);
              initShippingMap(lat, lng);
            } else {
              alert('No se encontró el lugar. Intenta arrastrar el marcador rojo en el mapa manualmente.');
            }
          }).catch(function(){ shippingSearchInput.disabled = false; });
        }
      }
    }
  });

  shippingSearchInput.addEventListener('input', function() {
    clearTimeout(shippingTimer);
    shippingHiddenInput.value = this.value;
    var q = this.value.trim();
    if (q.length < 3) { shippingSugList.innerHTML = ''; shippingSugList.classList.add('hidden'); return; }

    var searchQuery = q.replace(/#/g, ' ');

    shippingTimer = setTimeout(function() {
      fetch('https://nominatim.openstreetmap.org/search?format=jsonv2&q=' + encodeURIComponent(searchQuery) + '&countrycodes=co&limit=5&addressdetails=1', {
        headers: { 'Accept-Language': 'es' }
      })
      .then(function(r) { return r.json(); })
      .then(function(results) {
        shippingSugList.innerHTML = '';
        if (!results.length) { 
          shippingSugList.innerHTML = '<li class="px-4 py-3 text-sm text-error bg-error/10 font-bold">No se encontró la dirección exacta. Escribe solo el barrio o calle, o arrastra el marcador rojo del mapa manualmente.</li>';
          shippingSugList.classList.remove('hidden');
          return; 
        }
        results.forEach(function(place) {
          var li = document.createElement('li');
          li.className = 'px-4 py-3 text-sm text-on-surface cursor-pointer hover:bg-surface-container-low flex items-center gap-2 transition-colors border-b border-outline-variant/10 last:border-0';
          li.innerHTML = '<span class="material-symbols-outlined text-primary text-sm shrink-0">location_on</span><span class="truncate">' + place.display_name + '</span>';
          li.addEventListener('click', function() {
            var lat = parseFloat(place.lat);
            var lng = parseFloat(place.lon);
            shippingSearchInput.value = place.display_name.split(',')[0];
            shippingHiddenInput.value = place.display_name;
            shippingLatInput.value    = lat.toFixed(7);
            shippingLngInput.value    = lng.toFixed(7);
            initShippingMap(lat, lng);
            shippingSugList.innerHTML = '';
            shippingSugList.classList.add('hidden');
          });
          shippingSugList.appendChild(li);
        });
        shippingSugList.classList.remove('hidden');
      })
      .catch(function() { shippingSugList.classList.add('hidden'); });
    }, 500);
  });

  document.addEventListener('click', function(e) {
    if (!shippingSearchInput.contains(e.target) && !shippingSugList.contains(e.target)) {
      shippingSugList.classList.add('hidden');
    }
  });

  // ─── Botón GPS ────────────────────────────────────────────────────────────────
  var gpsBtn = document.getElementById('shipping-gps-btn');
  if (gpsBtn) {
    gpsBtn.addEventListener('click', function() {
      if (!navigator.geolocation) { alert('Tu navegador no soporta geolocalización.'); return; }
      var btn = this;
      btn.disabled = true;
      btn.innerHTML = '<span class="material-symbols-outlined animate-spin text-lg">sync</span>';
      navigator.geolocation.getCurrentPosition(
        function(pos) {
          var lat = pos.coords.latitude;
          var lng = pos.coords.longitude;
          shippingLatInput.value = lat.toFixed(7);
          shippingLngInput.value = lng.toFixed(7);
          initShippingMap(lat, lng);
          reverseGeocodeShipping(lat, lng);
          btn.disabled = false;
          btn.innerHTML = '<span class="material-symbols-outlined text-lg">my_location</span>';
        },
        function() {
          alert('No se pudo obtener tu ubicación. Verifica los permisos del navegador.');
          btn.disabled = false;
          btn.innerHTML = '<span class="material-symbols-outlined text-lg">my_location</span>';
        },
        { enableHighAccuracy: true, timeout: 10000 }
      );
    });
  }

  // ─── Inicializar mapa siempre (con dir guardada o por defecto) ────────────────
  var savedAddr = shippingHiddenInput.value.trim();
  if (savedAddr && savedAddr.length > 3) {
    fetch('https://nominatim.openstreetmap.org/search?format=jsonv2&q=' + encodeURIComponent(savedAddr) + '&limit=1', {
      headers: { 'Accept-Language': 'es' }
    })
    .then(function(r) { return r.json(); })
    .then(function(results) {
      if (results.length) {
        var lat = parseFloat(results[0].lat);
        var lng = parseFloat(results[0].lon);
        shippingLatInput.value = lat.toFixed(7);
        shippingLngInput.value = lng.toFixed(7);
        initShippingMap(lat, lng);
      } else {
        initShippingMap(4.3361, -74.3638);
      }
    })
    .catch(function() {
      initShippingMap(4.3361, -74.3638);
    });
  } else {
    // Ubicación por defecto (Fusagasugá)
    initShippingMap(4.3361, -74.3638);
  }

})(); // IIFE
</script>
@endpush
@endsection
