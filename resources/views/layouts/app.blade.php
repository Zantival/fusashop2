<!DOCTYPE html>
<html lang="es" class="light">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title','FusaShop') | FusaShop</title>

{{-- Optimización de carga: Preconnect y DNS Prefetch --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="dns-prefetch" href="https://cdn.tailwindcss.com">
<link rel="dns-prefetch" href="https://cdn.jsdelivr.net">

{{-- <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script> --}}
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" media="print" onload="this.media='all'"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />

<link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="{{ asset('assets/js/bootstrap.js') }}"></script>
<script src="{{ asset('assets/js/app.js') }}" defer></script>
<script src="{{ asset('assets/js/alpine.min.js') }}" defer></script>
<style>
  [x-cloak] { display: none !important; visibility: hidden !important; }
  @keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.4); }
    100% { transform: scale(1); }
  }
  .pulse { animation: pulse 0.4s ease-in-out; }
  
  /* Optimizaciones de renderizado */
  section { content-visibility: auto; contain-intrinsic-size: 1px 500px; }
  .pd-hero, header { content-visibility: visible; }
  
  /* Font display swap para evitar FOIT */
  @font-face { font-family: 'Inter'; font-display: swap; }
  @font-face { font-family: 'Manrope'; font-display: swap; }
</style>
{{-- <script>
tailwind.config={darkMode:"class",theme:{extend:{colors:{"primary":"#006c47","primary-container":"#00b67a","on-primary":"#ffffff","surface":"#fcf9f8","surface-container-lowest":"#ffffff","surface-container-low":"#f6f3f2","surface-container":"#f0eded","surface-container-highest":"#e5e2e1","on-surface":"#1b1c1c","on-surface-variant":"#3c4a41","background":"#fcf9f8","secondary-container":"#feb700","error":"#ba1a1a"},borderRadius:{lg:"0.5rem",xl:"1rem","2xl":"1.5rem"},fontFamily:{headline:["Manrope"],body:["Inter"]}}}}
</script> --}}
@stack('styles')
</head>
<body class="bg-surface text-on-surface min-h-screen flex flex-col">

<!-- Mobile Overlay Menu -->
<div id="mobile-menu" class="mobile-menu" onclick="if(event.target===this) closeMobileMenu()">
  <div class="mobile-menu-panel">
    <div class="flex items-center gap-3 mb-8 pb-6 border-b border-surface-container">
      @auth
        <div class="w-12 h-12 bg-greenhouse-gradient rounded-full flex items-center justify-center text-white font-bold text-lg shrink-0">
          {{ strtoupper(substr(auth()->user()->name,0,1)) }}
        </div>
        <div>
          <p class="font-bold text-on-surface">{{ auth()->user()->name }}</p>
          <p class="text-xs text-on-surface-variant">{{ ucfirst(auth()->user()->role) }}</p>
        </div>
      @else
        <div class="w-12 h-12 bg-surface-container rounded-full flex items-center justify-center shrink-0">
          <span class="material-symbols-outlined text-on-surface-variant">person</span>
        </div>
        <div>
          <p class="font-bold">Bienvenido</p>
          <a href="{{ route('login') }}" class="text-xs text-primary font-semibold">Iniciar sesión →</a>
        </div>
      @endauth
    </div>

    @auth
      @if(auth()->user()->isConsumer())
        <a href="{{ route('consumer.home') }}" onclick="closeMobileMenu()" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-surface-container-low transition-colors text-on-surface font-semibold">
          <span class="material-symbols-outlined text-primary">home</span> Inicio
        </a>
        <a href="{{ route('consumer.catalog') }}" onclick="closeMobileMenu()" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-surface-container-low transition-colors text-on-surface font-semibold">
          <span class="material-symbols-outlined text-primary">explore</span> Catálogo
        </a>
        <a href="{{ route('consumer.directory') }}" onclick="closeMobileMenu()" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-surface-container-low transition-colors text-on-surface font-semibold">
          <span class="material-symbols-outlined text-primary">store</span> Tiendas
        </a>
        <a href="{{ route('consumer.cart') }}" onclick="closeMobileMenu()" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-surface-container-low transition-colors text-on-surface font-semibold">
          <span class="material-symbols-outlined text-primary">shopping_cart</span> Mi Carrito
        </a>
        <a href="{{ route('consumer.orders') }}" onclick="closeMobileMenu()" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-surface-container-low transition-colors text-on-surface font-semibold">
          <span class="material-symbols-outlined text-primary">receipt_long</span> Mis Pedidos
        </a>
        <a href="{{ route('chat.index') }}" onclick="closeMobileMenu()" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-surface-container-low transition-colors text-on-surface font-semibold">
          <span class="material-symbols-outlined text-primary">chat</span> Mensajes
        </a>
      @elseif(auth()->user()->isMerchant())
        <a href="{{ route('merchant.dashboard') }}" onclick="closeMobileMenu()" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-surface-container-low transition-colors text-on-surface font-semibold">
          <span class="material-symbols-outlined text-primary">dashboard</span> Panel
        </a>
        <a href="{{ route('merchant.products') }}" onclick="closeMobileMenu()" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-surface-container-low transition-colors text-on-surface font-semibold">
          <span class="material-symbols-outlined text-primary">inventory_2</span> Productos
        </a>
        <a href="{{ route('merchant.inventory') }}" onclick="closeMobileMenu()" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-surface-container-low transition-colors text-on-surface font-semibold">
          <span class="material-symbols-outlined text-primary">warehouse</span> Inventario
        </a>
        <a href="{{ route('merchant.orders') }}" onclick="closeMobileMenu()" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-surface-container-low transition-colors text-on-surface font-semibold">
          <span class="material-symbols-outlined text-primary">receipt</span> Pedidos
        </a>
        <a href="{{ route('chat.index') }}" onclick="closeMobileMenu()" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-surface-container-low transition-colors text-on-surface font-semibold">
          <span class="material-symbols-outlined text-primary">chat</span> Mensajes
        </a>
      @elseif(auth()->user()->isAnalyst())
        <a href="{{ route('analyst.dashboard') }}" onclick="closeMobileMenu()" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-surface-container-low transition-colors text-on-surface font-semibold">
          <span class="material-symbols-outlined text-primary">dashboard</span> Panel Admin
        </a>
        <a href="{{ route('analyst.users') }}" onclick="closeMobileMenu()" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-surface-container-low transition-colors text-on-surface font-semibold">
          <span class="material-symbols-outlined text-primary">group</span> Usuarios
        </a>
        <a href="{{ route('analyst.orders') }}" onclick="closeMobileMenu()" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-surface-container-low transition-colors text-on-surface font-semibold">
          <span class="material-symbols-outlined text-primary">shopping_bag</span> Pedidos
        </a>
        <a href="{{ route('analyst.payments') }}" onclick="closeMobileMenu()" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-surface-container-low transition-colors text-on-surface font-semibold">
          <span class="material-symbols-outlined text-primary">account_balance</span> Pagos
        </a>
        <a href="{{ route('analyst.banners') }}" onclick="closeMobileMenu()" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-surface-container-low transition-colors text-on-surface font-semibold">
          <span class="material-symbols-outlined text-primary">image</span> Banners
        </a>
      @endif
    @else
      <a href="{{ route('consumer.catalog') }}" onclick="closeMobileMenu()" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-surface-container-low transition-colors text-on-surface font-semibold">
        <span class="material-symbols-outlined text-primary">explore</span> Catálogo
      </a>
      <a href="{{ route('consumer.directory') }}" onclick="closeMobileMenu()" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-surface-container-low transition-colors text-on-surface font-semibold">
        <span class="material-symbols-outlined text-primary">store</span> Tiendas
      </a>
    @endauth

    <div class="mt-auto pt-6 border-t border-surface-container space-y-2">
      <a href="{{ route('account.profile') }}" onclick="closeMobileMenu()" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-surface-container-low transition-colors text-on-surface font-semibold">
        <span class="material-symbols-outlined text-on-surface-variant">manage_accounts</span> Mi Perfil
      </a>
      @auth
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-[#ffdad6]/50 transition-colors text-[#ba1a1a] font-semibold">
            <span class="material-symbols-outlined">logout</span> Cerrar sesión
          </button>
        </form>
      @endauth
    </div>
  </div>
</div>

<!-- Header -->
<header id="main-header" class="h-16 glass-panel fixed top-0 w-full z-50 shadow-sm transition-all duration-300">
<div class="max-w-7xl mx-auto px-4 md:px-6 h-full flex items-center justify-between gap-4">

  <!-- Logo + Desktop Nav -->
  <div class="flex items-center gap-6">
    <a href="/" class="flex items-center gap-2 group shrink-0">
      @if(file_exists(storage_path('app/public/system/global_logo.png')))
        <img src="{{ asset('storage/system/global_logo.png') }}?v={{ filemtime(storage_path('app/public/system/global_logo.png')) }}" alt="FusaShop" class="h-9 object-contain">
      @else
        <div class="w-8 h-8 bg-greenhouse-gradient rounded-lg flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
          <span class="material-symbols-outlined text-white text-sm">storefront</span>
        </div>
        <span class="text-lg font-black text-[#006c47] font-['Manrope'] tracking-tight hidden sm:block">FusaShop</span>
      @endif
    </a>

    <!-- Desktop Navigation -->
    <nav class="hidden md:flex items-center gap-0.5">
      @if(!auth()->check() || auth()->user()->isConsumer())
        <a href="{{ route('consumer.catalog') }}" class="nav-link {{ request()->routeIs('consumer.catalog') ? 'active' : '' }}">Catálogo</a>
        <a href="{{ route('consumer.directory') }}" class="nav-link {{ request()->routeIs('consumer.directory*') ? 'active' : '' }}">Tiendas</a>
        @auth
          <a href="{{ route('consumer.orders') }}" class="nav-link {{ request()->routeIs('consumer.orders') ? 'active' : '' }}">Pedidos</a>
          <a href="{{ route('chat.index') }}" class="nav-link {{ request()->routeIs('chat.*') ? 'active' : '' }}">Mensajes</a>
          <a href="{{ route('consumer.support.contact') }}" class="nav-link {{ request()->routeIs('consumer.support.contact') ? 'active' : '' }} flex items-center gap-1">
            <span class="material-symbols-outlined text-[18px]">support_agent</span> Soporte
          </a>
        @endauth
      @elseif(auth()->user()->isMerchant())
        <a href="{{ route('merchant.dashboard') }}" class="nav-link {{ request()->routeIs('merchant.dashboard') ? 'active' : '' }}">Panel</a>
        <a href="{{ route('merchant.products') }}" class="nav-link {{ request()->routeIs('merchant.products*') ? 'active' : '' }}">Productos</a>
        <a href="{{ route('merchant.inventory') }}" class="nav-link {{ request()->routeIs('merchant.inventory*') ? 'active' : '' }}">Inventario</a>
        <a href="{{ route('merchant.orders') }}" class="nav-link {{ request()->routeIs('merchant.orders*') ? 'active' : '' }}">Pedidos</a>
        <a href="{{ route('merchant.finances') }}" class="nav-link {{ request()->routeIs('merchant.finances*') ? 'active' : '' }}">Finanzas</a>
        @if(\Illuminate\Support\Facades\Route::has('merchant.reviews'))<a href="{{ route('merchant.reviews') }}" class="nav-link {{ request()->routeIs('merchant.reviews') ? 'active' : '' }}">Reseñas</a>@endif
        <a href="{{ route('chat.index') }}" class="nav-link {{ request()->routeIs('chat.*') ? 'active' : '' }}">Mensajes</a>
        <a href="{{ route('merchant.support.contact') }}" class="nav-link {{ request()->routeIs('merchant.support.contact') ? 'active' : '' }} flex items-center gap-1">
          <span class="material-symbols-outlined text-[18px]">support_agent</span> Soporte
        </a>
        <a href="{{ route('consumer.merchant.profile', auth()->id()) }}" target="_blank" class="px-3 py-1.5 bg-primary/10 text-primary font-bold rounded-lg hover:bg-primary/20 transition-all text-xs flex items-center gap-1 ml-2">
          <span class="material-symbols-outlined text-sm">visibility</span> Mi Tienda
        </a>
      @elseif(auth()->user()->isAnalyst())
        <a href="{{ route('analyst.dashboard') }}" class="nav-link {{ request()->routeIs('analyst.dashboard') ? 'active' : '' }}">Panel</a>
        <a href="{{ route('analyst.users') }}" class="nav-link {{ request()->routeIs('analyst.users*') ? 'active' : '' }}">Usuarios</a>
        <a href="{{ route('analyst.orders') }}" class="nav-link {{ request()->routeIs('analyst.orders*') ? 'active' : '' }}">Pedidos</a>
        <a href="{{ route('analyst.payments') }}" class="nav-link {{ request()->routeIs('analyst.payments') ? 'active' : '' }}">Pagos</a>
        <a href="{{ route('analyst.banners') }}" class="nav-link {{ request()->routeIs('analyst.banners*') ? 'active' : '' }}">Banners</a>
        <a href="{{ route('analyst.pqrs.index') }}" class="nav-link {{ request()->routeIs('analyst.pqrs.*') ? 'active' : '' }}">PQRS</a>
      @endif
    </nav>
  </div>

  <!-- Right Actions -->
  <div class="flex items-center gap-1">

    @auth
      <!-- Cart (Consumers only) -->
      @if(auth()->user()->isConsumer())
        @php $cartCount = \App\Models\CartItem::whereHas('cart', fn($q) => $q->where('user_id', auth()->id()))->sum('quantity'); @endphp
        <a href="{{ route('consumer.cart') }}" class="relative p-1.5 rounded-full hover:bg-surface-container transition-colors text-on-surface-variant hover:text-primary" title="Carrito">
          <span class="material-symbols-outlined text-[20px]">shopping_cart</span>
          <span class="cart-badge absolute -top-0.5 -right-0.5 bg-[#ba1a1a] text-white text-[9px] font-black w-[18px] h-[18px] flex items-center justify-center rounded-full border-2 border-white leading-none {{ $cartCount > 0 ? '' : 'hidden' }}">
            {{ $cartCount > 9 ? '9+' : ($cartCount > 0 ? $cartCount : '') }}
          </span>
        </a>
      @endif

      <!-- Notifications -->
      <div class="relative">
        <button id="notif-btn" onclick="toggleDropdown('notif-dropdown')" 
                class="relative p-1.5 rounded-full hover:bg-surface-container transition-colors text-on-surface-variant flex items-center justify-center"
                title="Notificaciones">
          <span class="material-symbols-outlined text-[20px]">notifications</span>
          @php $unreadCount = auth()->user()->unreadNotifications->count(); @endphp
          @if($unreadCount > 0)
            <span id="notif-badge-ui" class="absolute -top-0.5 -right-0.5 bg-[#feb700] text-[#6b4b00] text-[8px] font-black w-[18px] h-[18px] flex items-center justify-center rounded-full border-2 border-white leading-none">
              {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
          @endif
        </button>

        <!-- Notifications Dropdown -->
        <div id="notif-dropdown"
             class="hidden absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-surface-container z-[70] overflow-hidden">
          <div class="flex items-center justify-between px-5 py-4 border-b border-surface-container">
            <h3 class="font-bold text-on-surface text-sm">Notificaciones</h3>
            <a href="{{ route('notifications.index') }}" class="text-xs text-primary font-semibold hover:underline">Ver todas</a>
          </div>
          <div class="max-h-72 overflow-y-auto divide-y divide-surface-container-low" id="notif-list-container">
            @forelse(auth()->user()->notifications()->take(6)->get() as $notif)
              @php
                $url = $notif->data['url'] ?? route('notifications.index');
                if(!isset($notif->data['url']) && isset($notif->data['type']) && $notif->data['type'] === 'banner_request' && isset($notif->data['request_id'])) {
                    $url = route('analyst.banner-requests.show', $notif->data['request_id']);
                }
              @endphp
              <a href="{{ $url }}" 
                 class="flex items-start gap-3 px-5 py-3 hover:bg-surface-container-low transition-colors {{ $notif->read_at ? 'opacity-60' : '' }}">
                <div class="w-8 h-8 {{ $notif->read_at ? 'bg-surface-container' : 'bg-[#6efcb9]/30' }} rounded-full flex items-center justify-center shrink-0 mt-0.5">
                  <span class="material-symbols-outlined text-primary text-[16px]">{{ $notif->data['icon'] ?? 'notifications' }}</span>
                </div>
                <div class="min-w-0 flex-1">
                  <p class="text-xs font-{{ $notif->read_at ? 'medium' : 'bold' }} text-on-surface truncate">{{ $notif->data['title'] ?? 'Notificación' }}</p>
                  <p class="text-[10px] text-on-surface-variant truncate">{{ $notif->data['message'] ?? '' }}</p>
                  <p class="text-[10px] text-on-surface-variant/50 mt-0.5">{{ $notif->created_at->diffForHumans() }}</p>
                </div>
              </a>
            @empty
              <div class="py-12 text-center text-on-surface-variant/40">
                <span class="material-symbols-outlined text-4xl mb-2">notifications_off</span>
                <p class="text-xs">Sin notificaciones nuevas</p>
              </div>
            @endforelse
          </div>
          <a href="{{ route('notifications.index') }}" class="block text-center py-3 text-xs text-primary font-bold hover:bg-surface-container-low transition-colors border-t border-surface-container">
            Ir al centro de notificaciones
          </a>
        </div>
      </div>

      <!-- User Menu -->
      <div class="relative ml-1">
        <button id="user-menu-btn" onclick="toggleDropdown('user-dropdown')" 
                class="flex items-center gap-1.5 py-1 px-1.5 pr-2 bg-surface-container rounded-full border border-surface-container-highest hover:shadow-sm transition-all max-w-[160px] sm:max-w-none">
          @if(auth()->user()->avatar)
            <img src="{{ Storage::url(auth()->user()->avatar) }}" class="w-7 h-7 rounded-full object-cover shrink-0">
          @else
            <div class="w-7 h-7 bg-[#006c47]/10 text-[#006c47] rounded-full flex items-center justify-center font-bold text-xs shrink-0">
              {{ strtoupper(substr(auth()->user()->name,0,1)) }}
            </div>
          @endif
          <span class="text-xs font-bold text-on-surface-variant hidden sm:block truncate" style="max-width:90px">{{ auth()->user()->name }}</span>
          <span class="material-symbols-outlined text-on-surface-variant shrink-0" style="font-size:16px">expand_more</span>
        </button>

        <div id="user-dropdown"
             class="hidden absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-surface-container p-2 z-[60] animate-in fade-in slide-in-from-top-2 duration-200">
          <div class="px-4 py-3 border-b border-surface-container mb-2">
            <p class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Mi Cuenta</p>
            <p class="text-sm font-black text-primary truncate">{{ auth()->user()->name }}</p>
          </div>
          
          <a href="{{ route('account.profile') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm text-on-surface hover:bg-surface-container-low rounded-xl transition-all group">
            <span class="material-symbols-outlined text-[20px] text-on-surface-variant group-hover:text-primary">account_circle</span> 
            <span class="font-semibold">Mi Perfil</span>
          </a>

          @if(auth()->user()->isConsumer())
            <a href="{{ route('consumer.orders') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm text-on-surface hover:bg-surface-container-low rounded-xl transition-all group">
              <span class="material-symbols-outlined text-[20px] text-on-surface-variant group-hover:text-primary">shopping_bag</span> 
              <span class="font-semibold">Mis Compras</span>
            </a>
            <a href="{{ route('account.profile') }}#loyalty-section" class="flex items-center gap-3 px-3 py-2.5 text-sm text-on-surface hover:bg-surface-container-low rounded-xl transition-all group">
              <span class="material-symbols-outlined text-[20px] text-[#feb700]" style="font-variation-settings: 'FILL' 1">stars</span> 
              <span class="font-semibold">Puntos Fusa</span>
            </a>
          @endif

          <a href="{{ route('chat.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm text-on-surface hover:bg-surface-container-low rounded-xl transition-all group">
            <span class="material-symbols-outlined text-[20px] text-on-surface-variant group-hover:text-primary">chat</span> 
            <span class="font-semibold">Mensajes</span>
          </a>

          <a href="{{ route('pqrs.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm text-on-surface hover:bg-surface-container-low rounded-xl transition-all group">
            <span class="material-symbols-outlined text-[20px] text-on-surface-variant group-hover:text-primary">assignment</span> 
            <span class="font-semibold">PQRS / Soporte</span>
          </a>

          @if(auth()->user()->isAnalyst())
            <div class="mt-2 pt-2 border-t border-surface-container">
              <a href="{{ route('analyst.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm bg-primary/5 text-primary hover:bg-primary/10 rounded-xl transition-all font-bold group">
                <span class="material-symbols-outlined text-[20px] group-hover:scale-110 transition-transform">admin_panel_settings</span> 
                <span>Panel de Control</span>
              </a>
            </div>
          @elseif(auth()->user()->isMerchant())
            <div class="mt-2 pt-2 border-t border-surface-container">
              <a href="{{ route('merchant.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm bg-primary/5 text-primary hover:bg-primary/10 rounded-xl transition-all font-bold group">
                <span class="material-symbols-outlined text-[20px] group-hover:scale-110 transition-transform">storefront</span> 
                <span>Gestionar Mi Tienda</span>
              </a>
              <a href="{{ route('merchant.finances') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm text-on-surface hover:bg-surface-container-low rounded-xl transition-all group mt-1">
                <span class="material-symbols-outlined text-[20px] text-on-surface-variant group-hover:text-primary">query_stats</span> 
                <span class="font-semibold">Mis Finanzas</span>
              </a>
            </div>
          @endif

          <hr class="my-2 border-surface-container">
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full text-left flex items-center gap-3 px-3 py-2.5 text-sm text-[#ba1a1a] hover:bg-[#ffdad6]/30 rounded-xl transition-all group">
              <span class="material-symbols-outlined text-[20px] group-hover:translate-x-1 transition-transform">logout</span> 
              <span class="font-bold">Cerrar Sesión</span>
            </button>
          </form>
        </div>
      </div>
    @else
      <div class="hidden sm:flex items-center gap-2">
        <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-semibold text-[#006c47] hover:bg-[#6efcb9]/10 rounded-lg transition-colors">Entrar</a>
        <a href="{{ route('register') }}" class="px-4 py-2 bg-greenhouse-gradient text-white text-sm font-semibold rounded-lg hover:opacity-90 shadow-sm shadow-[#006c47]/20 transition-all">Registrarme</a>
      </div>
    @endauth

    <!-- Hamburger (Mobile) -->
    <button id="hamburger-btn" onclick="toggleMobileMenu()" class="hamburger flex md:hidden p-1 ml-1" aria-label="Menú">
      <span></span><span></span><span></span>
    </button>
  </div>
</div>
</header>

<main class="flex-1 mt-16">
  @if(session('success'))
    <div x-data="{show:true}" x-cloak x-show="show" style="display: none;"
         x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="opacity-0 translate-x-10" 
         x-transition:enter-end="opacity-100 translate-x-0" 
         x-transition:leave="transition ease-in duration-200" 
         x-transition:leave-start="opacity-100 translate-x-0" 
         x-transition:leave-end="opacity-0 translate-x-10" 
         x-init="style.display=''; setTimeout(() => show = false, 4000)" 
         class="fixed top-20 right-4 z-[80] bg-white rounded-xl shadow-lg border border-surface-container p-4 flex items-center gap-3 max-w-sm">
      <div class="w-8 h-8 bg-[#6efcb9]/30 rounded-full flex items-center justify-center shrink-0">
        <span class="material-symbols-outlined text-primary text-[18px]">check_circle</span>
      </div>
      <p class="text-sm font-semibold text-on-surface">{{ session('success') }}</p>
      <button @click="show=false" onclick="this.parentElement.style.display='none'" class="ml-auto text-on-surface-variant hover:text-on-surface"><span class="material-symbols-outlined text-[18px]">close</span></button>
    </div>
  @endif
  @if(session('error'))
    <div x-data="{show:true}" x-cloak x-show="show" style="display: none;"
         x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="opacity-0 translate-x-10" 
         x-transition:enter-end="opacity-100 translate-x-0" 
         x-transition:leave="transition ease-in duration-200" 
         x-transition:leave-start="opacity-100 translate-x-0" 
         x-transition:leave-end="opacity-0 translate-x-10" 
         x-init="style.display=''; setTimeout(() => show = false, 5000)" 
         class="fixed top-20 right-4 z-[80] bg-white rounded-xl shadow-lg border border-[#ba1a1a]/20 p-4 flex items-center gap-3 max-w-sm">
      <div class="w-8 h-8 bg-[#ffdad6] rounded-full flex items-center justify-center shrink-0">
        <span class="material-symbols-outlined text-[#ba1a1a] text-[18px]">error</span>
      </div>
      <p class="text-sm font-semibold text-on-surface">{{ session('error') }}</p>
      <button @click="show=false" onclick="this.parentElement.style.display='none'" class="ml-auto text-on-surface-variant hover:text-[#ba1a1a]"><span class="material-symbols-outlined text-[18px]">close</span></button>
    </div>
  @endif
  @yield('content')
</main>

<!-- Footer -->
<footer class="bg-surface-container-low border-t border-surface-container-highest mt-16 pb-24 md:pb-0">
  <div class="max-w-7xl mx-auto px-6 py-12 grid grid-cols-2 md:grid-cols-4 gap-10">
    <div class="col-span-2">
      <div class="flex items-center gap-2 mb-4">
        <div class="w-8 h-8 bg-greenhouse-gradient rounded-lg flex items-center justify-center"><span class="material-symbols-outlined text-white text-sm">storefront</span></div>
        <span class="text-lg font-black text-[#006c47] font-['Manrope']">FusaShop</span>
      </div>
      <p class="text-sm text-on-surface-variant max-w-xs leading-relaxed mb-4">Conectando productores locales de Fusagasugá con compradores conscientes. Apoya lo local.</p>
    </div>
    <div>
      <h4 class="font-bold text-on-surface mb-4 text-sm uppercase tracking-wider">Explorar</h4>
      <ul class="space-y-2 text-sm text-on-surface-variant">
        <li><a href="{{ route('consumer.catalog') }}" class="hover:text-primary transition-colors">Catálogo</a></li>
        <li><a href="{{ route('consumer.directory') }}" class="hover:text-primary transition-colors">Directorio</a></li>
        <li><a href="{{ route('register') }}" class="hover:text-primary transition-colors">Vender aquí</a></li>
      </ul>
    </div>
    <div>
      <h4 class="font-bold text-on-surface mb-4 text-sm uppercase tracking-wider">Legal</h4>
      <ul class="space-y-2 text-sm text-on-surface-variant">
        <li><a href="{{ route('privacy') }}" class="hover:text-primary transition-colors">Privacidad</a></li>
        <li><a href="{{ route('privacy') }}" class="hover:text-primary transition-colors">Datos Personales</a></li>
        <li><a href="#" class="hover:text-primary transition-colors">Soporte</a></li>
      </ul>
    </div>
  </div>
  <div class="border-t border-surface-container-highest py-4 text-center">
    <p class="text-xs text-on-surface-variant/50 font-semibold">&copy; {{ date('Y') }} FusaShop. Todos los derechos reservados.</p>
  </div>
</footer>

<!-- Bottom Nav (Mobile) -->
<nav class="md:hidden fixed bottom-0 left-0 right-0 h-16 bg-white/90 backdrop-blur-xl border-t border-surface-container flex items-center justify-around z-40">
  @php
    $navItems = [];
    if(!auth()->check()) {
      $navItems = [
        ['icon'=>'home','label'=>'Inicio','route'=>route('consumer.home')],
        ['icon'=>'explore','label'=>'Catálogo','route'=>route('consumer.catalog')],
        ['icon'=>'login','label'=>'Entrar','route'=>route('login')],
      ];
    } elseif(auth()->user()->isConsumer()) {
      $navItems = [
        ['icon'=>'home','label'=>'Inicio','route'=>route('consumer.home')],
        ['icon'=>'shopping_cart','label'=>'Carrito','route'=>route('consumer.cart')],
        ['icon'=>'receipt_long','label'=>'Pedidos','route'=>route('consumer.orders')],
        ['icon'=>'chat','label'=>'Chat','route'=>route('chat.index')],
        ['icon'=>'person','label'=>'Perfil','route'=>route('account.profile')],
      ];
    } elseif(auth()->user()->isMerchant()) {
      $navItems = [
        ['icon'=>'dashboard','label'=>'Panel','route'=>route('merchant.dashboard')],
        ['icon'=>'inventory_2','label'=>'Productos','route'=>route('merchant.products')],
        ['icon'=>'warehouse','label'=>'Inventario','route'=>route('merchant.inventory')],
        ['icon'=>'receipt','label'=>'Pedidos','route'=>route('merchant.orders')],
        ['icon'=>'chat','label'=>'Chat','route'=>route('chat.index')],
      ];
    } elseif(auth()->user()->isAnalyst()) {
      $navItems = [
        ['icon'=>'dashboard','label'=>'Admin','route'=>route('analyst.dashboard')],
        ['icon'=>'group','label'=>'Usuarios','route'=>route('analyst.users')],
        ['icon'=>'shopping_bag','label'=>'Pedidos','route'=>route('analyst.orders')],
        ['icon'=>'image','label'=>'Banners','route'=>route('analyst.banners')],
      ];
    }
  @endphp
  @foreach($navItems as $item)
    @php $isActive = request()->is(ltrim(parse_url($item['route'])['path'] ?? '', '/') . '*'); @endphp
    <a href="{{ $item['route'] }}" class="flex flex-col items-center justify-center gap-0.5 flex-1 h-full {{ $isActive ? 'text-primary' : 'text-on-surface-variant' }} transition-colors">
      <span class="material-symbols-outlined text-[22px]" style="font-variation-settings: 'FILL' {{ $isActive ? 1 : 0 }}">{{ $item['icon'] }}</span>
      <span class="text-[9px] font-bold">{{ $item['label'] }}</span>
    </a>
  @endforeach
</nav>

<!-- Cookie Banner -->
@include('partials.cookie-banner')

<script>
// ── Mobile Menu ──────────────────────────────
function toggleMobileMenu() {
  const menu = document.getElementById('mobile-menu');
  const btn = document.getElementById('hamburger-btn');
  menu.classList.toggle('open');
  btn.classList.toggle('open');
  if (menu.classList.contains('open')) {
    document.body.style.overflow = 'hidden';
  } else {
    document.body.style.overflow = '';
  }
}

function closeMobileMenu() {
  const menu = document.getElementById('mobile-menu');
  const btn = document.getElementById('hamburger-btn');
  menu.classList.remove('open');
  btn.classList.remove('open');
  document.body.style.overflow = '';
}

// ── Header scroll effect ──────────────────────
window.addEventListener('scroll', function() {
  const header = document.getElementById('main-header');
  if (header) {
    if (window.scrollY > 10) {
      header.classList.add('shadow-md');
      header.style.backgroundColor = 'rgba(255, 255, 255, 0.95)';
    } else {
      header.classList.remove('shadow-md');
      header.style.backgroundColor = '';
    }
  }
});

// ── Dropdowns Vanilla JS ─────────────────────
function toggleDropdown(id) {
  const dropdown = document.getElementById(id);
  const allDropdowns = ['notif-dropdown', 'user-dropdown'];
  
  // Close others
  allDropdowns.forEach(d => {
    if(d !== id) {
      const other = document.getElementById(d);
      if (other) other.classList.add('hidden');
    }
  });

  // Toggle current
  const isOpening = dropdown.classList.contains('hidden');
  dropdown.classList.toggle('hidden');

  // Special logic for notifications
  if (id === 'notif-dropdown' && isOpening) {
    // We keep the badge but maybe pulse it or just let it be. We will not auto-mark as read.
    // Let the user click on "Ver todas" or a specific notification to mark as read.
  }
}

// Global click outside to close
document.addEventListener('click', (e) => {
  const notifDropdown = document.getElementById('notif-dropdown');
  const userDropdown = document.getElementById('user-dropdown');
  const notifBtn = document.getElementById('notif-btn');
  const userBtn = document.getElementById('user-menu-btn');

  if (notifDropdown && !notifDropdown.contains(e.target) && notifBtn && !notifBtn.contains(e.target)) {
    notifDropdown.classList.add('hidden');
  }
  if (userDropdown && !userDropdown.contains(e.target) && userBtn && !userBtn.contains(e.target)) {
    userDropdown.classList.add('hidden');
  }
});

@auth
// ── Notification counter live update ─────────
function updateNotifBadge() {
  fetch('/notifications?json=1', { headers: {'Accept':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content} })
    .then(r => r.json())
    .then(d => {
      const badge = document.getElementById('notif-badge-ui');
      const dropdown = document.getElementById('notif-dropdown');
      
      if (badge && d.unread !== undefined && dropdown && dropdown.classList.contains('hidden')) {
          if (d.unread > 0) {
              badge.innerText = d.unread > 9 ? '9+' : d.unread;
              badge.style.display = 'flex';
          } else {
              badge.style.display = 'none';
          }
      }
    }).catch(() => {});
}
setInterval(updateNotifBadge, 60000);
@endauth
</script>
  @if(auth()->check() && (auth()->user()->isMerchant() || auth()->user()->isConsumer()))
    <a href="{{ auth()->user()->isMerchant() ? route('merchant.support.contact') : route('consumer.support.contact') }}" 
       class="fixed bottom-6 right-6 z-[60] flex items-center justify-center w-14 h-14 bg-indigo-600 text-white rounded-full shadow-2xl hover:scale-110 hover:bg-indigo-700 transition-all group animate-bounce"
       title="Contactar Administrador">
      <span class="material-symbols-outlined text-3xl">support_agent</span>
      <span class="absolute right-full mr-3 px-3 py-1 bg-[#1b1c1c] text-white text-xs font-bold rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">
        ¿Necesitas ayuda? Habla con Admin
      </span>
    </a>
  @endif

  @stack('scripts')
</body>
</html>
