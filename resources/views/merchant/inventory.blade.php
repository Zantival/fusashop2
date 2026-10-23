@extends('layouts.app')
@section('title', 'Inventario Rápido')
@section('content')
<div class="max-w-6xl mx-auto px-6 py-8">
  @if(session('success') || session('info') || $errors->any())
    <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
      @if(session('success'))<p>{{ session('success') }}</p>@endif
      @if(session('info'))<p>{{ session('info') }}</p>@endif
      @foreach($errors->all() as $error)<p class="text-rose-700">{{ $error }}</p>@endforeach
    </div>
  @endif
  <div class="flex items-center justify-between mb-8">
    <h1 class="text-3xl font-['Manrope'] font-bold text-on-background flex items-center gap-2">
      <span class="material-symbols-outlined text-primary text-3xl">inventory</span> Inventario Rápido
    </h1>
    <a href="{{ route('merchant.products') }}" class="text-primary font-bold hover:underline">Volver a Productos</a>
  </div>

  <p class="text-on-surface-variant mb-6">Actualiza las existencias de tus productos de forma rápida y sencilla.</p>

  <form method="POST" action="{{ route('merchant.inventory.update') }}">
    @csrf
    <div class="bg-surface-container-lowest rounded-2xl shadow-card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm responsive-stack-table">
          <thead class="bg-surface-container-low">
            <tr>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant w-16">IMG</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant">Producto</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant">Categoría</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant w-48">Stock / variantes</th>
            </tr>
          </thead>
          <tbody>
            @forelse($products as $product)
            <tr class="border-t border-outline-variant/20 hover:bg-surface-container-low/50">
              <td class="px-4 py-3" data-label="IMG">
                @if($product->image)
                  <img loading="lazy" decoding="async" src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" loading="lazy" decoding="async" class="w-10 h-10 object-cover rounded-lg border border-outline-variant/30">
                @else
                  <div class="w-10 h-10 bg-surface-container-low rounded-lg flex items-center justify-center">
                    <span class="material-symbols-outlined text-on-surface-variant/50">image</span>
                  </div>
                @endif
              </td>
              <td class="px-4 py-3 font-semibold text-on-background" data-label="Producto">{{ $product->name }}</td>
              <td class="px-4 py-3 text-on-surface-variant" data-label="Categoría">{{ $product->category }}</td>
              <td class="px-4 py-3" data-label="Stock Actual">
                @if($product->variants)
                  <div class="space-y-1">
                    <p class="font-bold">{{ $product->stock }} unidades en total</p>
                    <p class="text-[10px] text-on-surface-variant">{{ collect($product->variants)->map(fn($variant) => collect($variant['options'] ?? [])->map(fn($value, $key) => $key.': '.$value)->implode(' · ').' ('.$variant['stock'].' uds.)')->implode(' / ') }}</p>
                    <a href="{{ route('merchant.products.edit', $product->id) }}" class="text-xs font-bold text-primary underline">Editar stock y precios por variante</a>
                  </div>
                @else
                <input type="number" name="stocks[{{ $product->id }}]" value="{{ $product->stock }}" min="0" required
                       class="w-full rounded-lg border {{ $product->stock == 0 ? 'border-error text-error bg-error-container/20' : 'border-outline-variant' }} px-3 py-1.5 focus:ring-2 focus:ring-primary/40 focus:outline-none">
                @endif
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="4" class="px-4 py-12 text-center text-on-surface-variant">No tienes productos registrados.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @if($products->hasPages())
        <div class="p-4 border-t border-outline-variant/20 bg-surface-container-lowest">
          {{ $products->links() }}
        </div>
      @endif
      @if($products->contains(fn($product) => !$product->variants))
      <div class="p-4 border-t border-outline-variant/20 bg-surface-container-lowest flex justify-end">
        <button type="submit" class="px-6 py-2.5 bg-primary-gradient text-white font-bold rounded-xl hover:opacity-90 transition-all shadow-md flex items-center gap-2">
          <span class="material-symbols-outlined text-sm">save</span> Guardar Inventario
        </button>
      </div>
      @else
      <p class="p-4 border-t border-outline-variant/20 text-sm text-on-surface-variant">Edita cada producto para cambiar existencias por variante.</p>
      @endif
    </div>
  </form>
</div>
@endsection
