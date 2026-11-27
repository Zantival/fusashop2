@php
  $f = fn ($k, $d = '') => old($k, $a?->{$k} ?? $d);
  $cls = 'rounded-xl border border-outline-variant/40 px-4 py-3 text-sm w-full';
  $muniList = \App\Support\ColombiaLocations::all();
@endphp
<div class="col-span-2 flex flex-wrap items-center gap-3 rounded-xl border border-emerald-100 bg-emerald-50 p-3">
  <button type="button" data-address-gps class="inline-flex items-center gap-2 rounded-lg bg-[#006c47] px-4 py-2.5 text-sm font-bold text-white hover:bg-emerald-700 disabled:cursor-wait disabled:opacity-60">
    <span class="material-symbols-outlined text-base">my_location</span>
    <span>Usar mi ubicación GPS</span>
  </button>
  <span data-address-gps-status class="text-xs text-slate-600" role="status" aria-live="polite">Usa el GPS para sugerir la dirección; las coordenadas se consultan en OpenStreetMap. Revisa los datos antes de guardar.</span>
</div>
<input class="{{ $cls }}" name="label" value="{{ $f('label', 'Casa') }}" placeholder="Etiqueta (Casa, Oficina)" required maxlength="60">
<input class="{{ $cls }}" name="recipient" value="{{ $f('recipient', auth()->user()->name) }}" placeholder="Quién recibe" required maxlength="120">
<input class="{{ $cls }}" name="phone" value="{{ $f('phone', auth()->user()->phone) }}" placeholder="Teléfono" required maxlength="20">
<input class="{{ $cls }}" name="street" value="{{ $f('street') }}" placeholder="Calle 10, Carrera 7, Avenida…" title="Indica Calle, Carrera, Avenida, Diagonal, Transversal o Vereda" required maxlength="160">
<input class="{{ $cls }}" name="number" value="{{ $f('number') }}" placeholder="Número (# 45-23)" maxlength="30">
<input class="{{ $cls }}" name="district" value="{{ $f('district') }}" placeholder="Barrio" maxlength="100">

<select class="{{ $cls }}" name="state" required>
  <option value="">Departamento…</option>
  @foreach($states as $code => $name)
    <option value="{{ $code }}" {{ $f('state') === $code ? 'selected' : '' }}>{{ $name }}</option>
  @endforeach
</select>

<select class="{{ $cls }}" name="city" required {{ $f('state') ? '' : 'disabled' }}>
  <option value="">Selecciona primero un departamento…</option>
  @foreach($muniList as $m)
    <option value="{{ $m['name'] }}" data-state="{{ $m['state_code'] }}" data-postal="{{ $m['postal_code'] }}" {{ \App\Support\ColombiaLocations::normalize($f('city')) === \App\Support\ColombiaLocations::normalize($m['name']) && $f('state') === $m['state_code'] ? 'selected' : '' }}>{{ $m['name'] }}</option>
  @endforeach
</select>

<input class="{{ $cls }}" name="postal_code" value="{{ $f('postal_code') }}" placeholder="Código postal (6 dígitos)" required pattern="[0-9]{6}" maxlength="6" inputmode="numeric">
<input class="{{ $cls }}" name="reference" value="{{ $f('reference') }}" placeholder="Indicaciones adicionales (apto, torre, etc.)" maxlength="255">

@once
<script>
function initShippingAddressForms() {
  var states = @json($states);
  var dummyPostals = ['000000', '123456', '123465', '111111', '999999', '123123'];
  var normalize = function (value) {
    return String(value || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().replace(/[^a-z0-9]/g, '');
  };

  document.querySelectorAll('form').forEach(function (form) {
    var state = form.querySelector('select[name="state"]');
    var city = form.querySelector('select[name="city"]');
    var postal = form.querySelector('input[name="postal_code"]');
    if (!state || !city) return;

    function filterCities(reset) {
      var code = state.value;
      city.disabled = !code;
      city.querySelectorAll('option[data-state]').forEach(function (option) {
        option.hidden = option.dataset.state !== code;
        option.disabled = option.dataset.state !== code;
      });
      if (reset || (city.selectedOptions[0]?.dataset.state && city.selectedOptions[0].dataset.state !== code)) city.value = '';
      if (postal && city.selectedOptions[0]?.dataset.postal && (!postal.value || dummyPostals.includes(postal.value))) postal.value = city.selectedOptions[0].dataset.postal;
    }
    state.addEventListener('change', function () { filterCities(true); });
    city.addEventListener('change', function () { filterCities(false); });
    filterCities(false);

    var gpsButton = form.querySelector('[data-address-gps]');
    var status = form.querySelector('[data-address-gps-status]');
    if (!gpsButton || gpsButton.dataset.initialized === 'true') return;
    gpsButton.dataset.initialized = 'true';

    gpsButton.addEventListener('click', function () {
      if (!navigator.geolocation) {
        status.textContent = 'Este navegador no permite obtener la ubicación. Completa la dirección manualmente.';
        status.className = 'text-xs text-rose-700';
        return;
      }

      gpsButton.disabled = true;
      gpsButton.querySelector('span:last-child').textContent = 'Buscando ubicación…';
      status.textContent = 'Permite el acceso a la ubicación cuando el navegador lo solicite.';
      status.className = 'text-xs text-slate-600';

      navigator.geolocation.getCurrentPosition(async function (position) {
        try {
          var { latitude, longitude, accuracy } = position.coords;
          var url = new URL('https://nominatim.openstreetmap.org/reverse');
          url.search = new URLSearchParams({ format: 'jsonv2', lat: latitude, lon: longitude, addressdetails: '1', zoom: '18' });
          var response = await fetch(url, { headers: { 'Accept': 'application/json', 'Accept-Language': 'es' } });
          if (!response.ok) throw new Error('No se pudo consultar la dirección del mapa.');
          var result = await response.json();
          var location = result.address || {};
          if (location.country_code && location.country_code.toLowerCase() !== 'co') {
            throw new Error('La ubicación detectada está fuera de Colombia. Completa los datos manualmente.');
          }

          var department = location.state || location.region || '';
          var stateCode = Object.entries(states).find(function ([code, name]) {
            return normalize(name) === normalize(department) || normalize(code) === normalize(department);
          })?.[0];
          if (stateCode) {
            state.value = stateCode;
            state.dispatchEvent(new Event('change', { bubbles: true }));
          }

          var municipality = location.city || location.town || location.village || location.municipality || location.county || '';
          var cityOption = Array.from(city.options).find(function (option) {
            return option.dataset.state === state.value && normalize(option.value) === normalize(municipality);
          });
          if (cityOption) {
            city.value = cityOption.value;
            city.dispatchEvent(new Event('change', { bubbles: true }));
          }

          var road = (location.road || location.pedestrian || location.residential || location.footway || location.path || '').trim();
          var street = form.querySelector('[name="street"]');
          var number = form.querySelector('[name="number"]');
          var district = form.querySelector('[name="district"]');
          if (street && road) {
            var streetType = /^(calle|cl\.?|carrera|cra\.?|kr\.?|avenida|av\.?|diagonal|diag\.?|transversal|transv\.?|tv\.?|circular|vereda|v[ií]a|kil[oó]metro|autopista|carretera|camino|sendero|ruta|lote|sector)(?:\s|$)/i;
            street.value = (streetType.test(road) ? road : 'Vía ' + road).slice(0, 160);
          }
          if (number && location.house_number) number.value = location.house_number.slice(0, 30);
          if (district) district.value = (location.suburb || location.neighbourhood || location.city_district || '').slice(0, 100);
          if (postal) {
            if (/^\d{6}$/.test(location.postcode || '')) postal.value = location.postcode;
            else if (cityOption?.dataset.postal) postal.value = cityOption.dataset.postal;
          }

          if (!stateCode || !cityOption || !road) {
            status.textContent = 'Ubicación detectada, pero faltan datos de vía, municipio o departamento. Revísalos y complétalos antes de guardar.';
            status.className = 'text-xs text-amber-700';
          } else {
            status.textContent = 'Dirección completada (precisión aproximada: ' + Math.round(accuracy) + ' m). Confirma la vía y el número antes de guardar.';
            status.className = 'text-xs font-semibold text-emerald-800';
          }
        } catch (error) {
          status.textContent = error.message || 'No pudimos completar la dirección. Puedes escribirla manualmente.';
          status.className = 'text-xs text-rose-700';
        } finally {
          gpsButton.disabled = false;
          gpsButton.querySelector('span:last-child').textContent = 'Usar mi ubicación GPS';
        }
      }, function (error) {
        var messages = {
          1: 'Permiso de ubicación denegado. Actívalo en el navegador para usar el GPS.',
          2: 'No se pudo determinar la ubicación. Comprueba el GPS o ingresa la dirección manualmente.',
          3: 'La ubicación tardó demasiado. Intenta de nuevo o ingresa la dirección manualmente.'
        };
        status.textContent = messages[error.code] || 'No se pudo obtener la ubicación. Puedes completar la dirección manualmente.';
        status.className = 'text-xs text-rose-700';
        gpsButton.disabled = false;
        gpsButton.querySelector('span:last-child').textContent = 'Usar mi ubicación GPS';
      }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 });
    });
  });
}

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initShippingAddressForms, { once: true });
else setTimeout(initShippingAddressForms, 0);
</script>
@endonce
