@php
  $f = fn ($k, $d = '') => old($k, $a?->{$k} ?? $d);
  $cls = 'rounded-xl border border-outline-variant/40 px-4 py-3 text-sm w-full';
  $muniList = \App\Support\ColombiaLocations::all();
@endphp
<input class="{{ $cls }}" name="label" value="{{ $f('label', 'Casa') }}" placeholder="Etiqueta (Casa, Oficina)" required maxlength="60">
<input class="{{ $cls }}" name="recipient" value="{{ $f('recipient', auth()->user()->name) }}" placeholder="Quién recibe" required maxlength="120">
<input class="{{ $cls }}" name="phone" value="{{ $f('phone', auth()->user()->phone) }}" placeholder="Teléfono" required maxlength="20">
<input class="{{ $cls }}" name="street" value="{{ $f('street') }}" placeholder="Calle / Carrera" required maxlength="160">
<input class="{{ $cls }}" name="number" value="{{ $f('number') }}" placeholder="Número (# 45-23)" maxlength="30">
<input class="{{ $cls }}" name="district" value="{{ $f('district') }}" placeholder="Barrio" maxlength="100">

<div class="relative">
  <input class="{{ $cls }}" name="city" list="colombia-cities-list" value="{{ $f('city') }}" placeholder="Ciudad / Municipio (ej. Ubaté)" required maxlength="100" autocomplete="off" onchange="autoFillLocation(this)">
  <datalist id="colombia-cities-list">
    @foreach($muniList as $m)
      <option value="{{ $m['name'] }}" data-state="{{ $m['state_code'] }}" data-postal="{{ $m['postal_code'] }}">{{ $m['name'] }} ({{ $m['dpt_name'] }})</option>
    @endforeach
  </datalist>
</div>

<select class="{{ $cls }}" name="state" required>
  <option value="">Departamento…</option>
  @foreach($states as $code => $name)
    <option value="{{ $code }}" {{ $f('state') === $code ? 'selected' : '' }}>{{ $name }}</option>
  @endforeach
</select>

<input class="{{ $cls }}" name="postal_code" value="{{ $f('postal_code') }}" placeholder="Código postal (6 dígitos)" required pattern="[0-9]{6}" maxlength="6" inputmode="numeric">
<input class="{{ $cls }}" name="reference" value="{{ $f('reference') }}" placeholder="Indicaciones adicionales (apto, torre, etc.)" maxlength="255">

@once
<script>
function autoFillLocation(input) {
  var form = input.closest('form');
  if (!form) return;
  var val = (input.value || '').trim().toLowerCase();
  if (!val) return;
  var opts = document.querySelectorAll('#colombia-cities-list option');
  for (var i = 0; i < opts.length; i++) {
    var opt = opts[i];
    if (opt.value.toLowerCase() === val) {
      var stateSelect = form.querySelector('select[name="state"]');
      var postalInput = form.querySelector('input[name="postal_code"]');
      if (stateSelect && opt.dataset.state) {
        stateSelect.value = opt.dataset.state;
      }
      if (postalInput && opt.dataset.postal && (!postalInput.value || ['123456','123465','000000','111111'].includes(postalInput.value))) {
        postalInput.value = opt.dataset.postal;
      }
      break;
    }
  }
}
</script>
@endonce
