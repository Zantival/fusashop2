<p class="text-[#3c4a41] text-xs sm:text-sm mb-3 sm:mb-5 font-medium">{{ $products->total() }} producto{{ $products->total()!=1?'s':'' }} encontrados</p>
@if($products->isEmpty())
  <div class="text-center py-14 sm:py-20 bg-white rounded-2xl shadow-sm p-4">
    <span class="material-symbols-outlined text-6xl sm:text-7xl text-[#3c4a41] block mb-3">search_off</span>
    <p class="text-[#3c4a41] text-sm font-semibold">No se encontraron productos.</p>
    <a href="{{ route('consumer.catalog') }}" class="hover:underline mt-2 inline-block text-xs sm:text-sm font-semibold" style="color:#006c47">Ver todos</a>
  </div>
@else
  <div class="grid grid-cols-2 lg:grid-cols-3 gap-2.5 sm:gap-4 md:gap-5">
    @foreach($products as $p)
      <x-product-card :product="$p" />
    @endforeach
  </div>
  <div class="mt-6 sm:mt-8">{{ $products->links() }}</div>
@endif
