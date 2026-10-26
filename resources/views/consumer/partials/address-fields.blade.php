@php
  $f = fn ($k, $d = '') => old($k, $a?->{$k} ?? $d);
  $cls = 'rounded-xl border border-outline-variant/40 px-4 py-3 text-sm w-full';
  $muniList = \App\Support\ColombiaLocations::all();
@endphp
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
    if (postal && city.selectedOptions[0]?.dataset.postal && (!postal.value || ['123456','123465','000000','111111'].includes(postal.value))) postal.value = city.selectedOptions[0].dataset.postal;
  }
  state.addEventListener('change', function () { filterCities(true); });
  city.addEventListener('change', function () { filterCities(false); });
  filterCities(false);
});
</script>
@endonce
