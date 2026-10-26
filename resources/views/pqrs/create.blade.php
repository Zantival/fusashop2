@extends('layouts.app')
@section('title', 'Nueva PQRS')
@section('content')
<div class="max-w-3xl mx-auto px-6 py-8">
    <div class="mb-8">
        <a href="{{ route('pqrs.index') }}" class="inline-flex items-center gap-1 text-sm text-on-surface-variant hover:text-primary mb-4">
            <span class="material-symbols-outlined text-sm">arrow_back</span> Volver a mis solicitudes
        </a>
        <h1 class="text-3xl font-['Manrope'] font-bold text-on-background">Nueva Solicitud (PQRS)</h1>
        <p class="text-on-surface-variant">Cuéntanos en qué podemos ayudarte. Tu opinión es fundamental.</p>
    </div>

    <form action="{{ route('pqrs.store') }}" method="POST" class="bg-surface-container-lowest p-8 rounded-3xl shadow-card border border-outline-variant/30">
        @csrf
        <div class="grid gap-6">
            <div id="security-alert" class="{{ old('category', $category ?? '') === 'seguridad' ? '' : 'hidden' }} rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-900" role="alert">
                <strong class="block mb-1">Reporte de seguridad o posible fraude</strong>
                Incluye el número del pedido, la fecha y los datos de la transacción. No compartas contraseñas, códigos de verificación, claves de pago ni datos completos de tarjetas.
            </div>

            <div>
                <label class="block text-sm font-bold text-on-surface mb-2">Tipo de Solicitud</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach(['peticion' => 'Petición', 'queja' => 'Queja', 'reclamo' => 'Reclamo', 'sugerencia' => 'Sugerencia'] as $val => $label)
                        <label class="relative cursor-pointer group">
                            <input type="radio" name="type" value="{{ $val }}" class="peer sr-only" {{ old('type') == $val ? 'checked' : ($loop->first ? 'checked' : '') }}>
                            <div class="px-4 py-3 rounded-xl border border-outline-variant text-center transition-all peer-checked:border-primary peer-checked:bg-primary/5 peer-checked:text-primary group-hover:bg-surface-container-low">
                                <p class="text-xs font-bold">{{ $label }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div>
                <label for="category" class="block text-sm font-bold text-on-surface mb-2">Tema del reporte</label>
                <select name="category" id="category" required class="w-full bg-surface-container-low border border-outline-variant rounded-xl px-4 py-3 text-on-surface">
                    @foreach(['plataforma' => 'Plataforma / soporte técnico', 'envio' => 'Envíos', 'producto' => 'Producto', 'pago' => 'Pago o cobro', 'comercio' => 'Comercio', 'seguridad' => 'Seguridad / posible estafa o fraude'] as $value => $label)
                        <option value="{{ $value }}" {{ old('category', $category ?? 'plataforma') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <p id="security-guidance" class="{{ old('category', $category ?? '') === 'seguridad' ? '' : 'hidden' }} mt-2 text-xs text-rose-700">No incluyas contraseñas, códigos OTP, PIN ni números completos de tarjetas.</p>
            </div>

            <div>
                <label for="subject" class="block text-sm font-bold text-on-surface mb-2">Asunto</label>
                <input type="text" name="subject" id="subject" value="{{ old('subject') }}" required
                    class="w-full bg-surface-container-low border border-outline-variant rounded-xl px-4 py-3 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all"
                    placeholder="Ej: Problema con un pedido, posible suplantación, cobro sospechoso...">
            </div>

            <div>
                <label for="content" class="block text-sm font-bold text-on-surface mb-2">Descripción Detallada</label>
                <textarea name="content" id="content" rows="6" required
                    class="w-full bg-surface-container-low border border-outline-variant rounded-xl px-4 py-3 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all"
                    placeholder="Describe lo ocurrido. Para un reporte de seguridad incluye pedido, fecha y transacción; nunca envíes claves ni códigos de acceso...">{{ old('content') }}</textarea>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full py-4 bg-primary text-white font-bold rounded-2xl shadow-lg shadow-primary/20 hover:opacity-95 active:scale-[0.98] transition-all">
                    Enviar Solicitud
                </button>
                <p class="text-center text-[10px] text-on-surface-variant/60 mt-4 px-4">
                    Al enviar, tu solicitud será revisada por nuestro equipo administrativo en Fusagasugá. Recibirás una respuesta en un plazo máximo de 15 días hábiles.
                </p>
            </div>
        </div>
    </form>
</div>
<script>
document.getElementById('category')?.addEventListener('change', function () {
    document.getElementById('security-guidance')?.classList.toggle('hidden', this.value !== 'seguridad');
    document.getElementById('security-alert')?.classList.toggle('hidden', this.value !== 'seguridad');
});
</script>
@endsection
