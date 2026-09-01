<?php

namespace App\Http\Controllers\Consumer;

use App\Http\Controllers\Controller;
use App\Models\{Product, Cart, CartItem, Order, OrderItem, CompanyProfile, Review, User, GlobalBanner};
use App\Events\ProductOutOfStock;
use App\Notifications\{NewProductReview, NewOrderNotification};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ConsumerController extends Controller
{
    public function home()
    {
        $featured = Product::active()->with('merchant.companyProfile')->latest()->take(8)->get();
        $categories = Product::active()->select('category')->distinct()->pluck('category');
        $globalBanners = GlobalBanner::where('is_active', true)->orderBy('sort_order')->get();
        return view('consumer.home', compact('featured', 'categories', 'globalBanners'));
    }

    public function catalog(Request $request)
    {
        $q = Product::active()->with('merchant.companyProfile');
        
        if ($request->filled('search'))    $q->search($request->search);
        if ($request->filled('category'))  $q->where('category', $request->category);
        if ($request->filled('min_price')) $q->where('price', '>=', $request->min_price);
        if ($request->filled('max_price')) $q->where('price', '<=', $request->max_price);
        
        $products = $q->paginate(12)->withQueryString();
        $categories = Product::active()->select('category')->distinct()->pluck('category');

        // Solo los primeros 10 para el carrusel de vendedores
        $directory = CompanyProfile::where('kyc_status', 'approved')->select('id','merchant_id','company_name','logo_path')->take(10)->get();

        return view('consumer.catalog', compact('products', 'categories', 'directory'));
    }

    public function productDetail($id)
    {
        $product = Product::active()->with('reviews.user')->findOrFail($id);
        $related = Product::active()->where('category', $product->category)->where('id','!=',$id)->take(4)->get();
        
        $hasPurchased = false;
        if (Auth::check()) {
            $hasPurchased = Order::where('user_id', Auth::id())
                ->where('status', 'delivered')
                ->whereHas('items', function ($q) use ($id) {
                    $q->where('product_id', $id);
                })->exists();
        }

        return view('consumer.product-detail', compact('product', 'related', 'hasPurchased'));
    }

    // --- MÉTODOS DEL CARRITO ---
    public function cartIndex()
    {
        $cart = Auth::user()->cart()->with('items.product')->firstOrCreate(['user_id' => Auth::id()]);
        return view('consumer.cart', compact('cart'));
    }

    public function cartAdd(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id', 
            'quantity'   => 'integer|min:1',
            'options'    => 'nullable|array'
        ]);
        
        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
        
        $selectedOptions = $request->input('options', []);
        
        // Intentamos encontrar un item existente con las mismas opciones
        // Para simplificar, buscamos por JSON exacto si es posible, o simplemente creamos uno nuevo
        // En MySQL/Laravel, comparar JSON exacto:
        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $request->product_id)
            ->where('selected_options', json_encode($selectedOptions))
            ->first();

        if ($item) {
            $item->increment('quantity', $request->input('quantity', 1));
        } else {
            $item = CartItem::create([
                'cart_id'          => $cart->id,
                'product_id'       => $request->product_id,
                'quantity'         => $request->input('quantity', 1),
                'selected_options' => $selectedOptions
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Producto agregado al carrito.',
                'cart_count' => $cart->items()->sum('quantity')
            ]);
        }

        return back()->with('success', 'Producto agregado al carrito.');
    }

    public function cartUpdate(Request $request, CartItem $item)
    {
        $request->validate(['quantity' => 'required|integer|min:1']);
        if ($item->cart->user_id !== Auth::id()) abort(403);
        $item->update(['quantity' => $request->quantity]);
        return back()->with('success', 'Carrito actualizado.');
    }

    public function cartRemove(CartItem $item)
    {
        if ($item->cart->user_id !== Auth::id()) abort(403);
        $item->delete();
        return back()->with('success', 'Producto eliminado.');
    }

    // --- PROCESO DE PAGO (CHECKOUT) ---
    public function checkoutShow()
    {
        $cart = Auth::user()->cart()->with('items.product')->firstOrFail();
        if ($cart->items->isEmpty()) return redirect()->route('consumer.cart');

        // Calcular puntos disponibles para los comercios en este carrito
        $merchantIds = $cart->items->pluck('product.merchant_id')->unique();
        $availablePoints = \App\Models\LoyaltyPoint::where('user_id', Auth::id())
            ->whereIn('merchant_id', $merchantIds)
            ->sum('points');

        return view('consumer.checkout', compact('cart', 'availablePoints'));
    }

    public function checkoutProcess(Request $request)
    {
        $request->validate([
            'shipping_address' => 'required|string|max:500',
            'payment_method'   => 'required|in:card,transfer,cash',
            'card_number'      => 'required_if:payment_method,card',
        ]);

        $cart = Auth::user()->cart()->with('items.product')->firstOrFail();
        if ($cart->items->isEmpty()) return redirect()->route('consumer.cart');

        $total = $cart->items->sum(fn($i) => $i->quantity * $i->product->price);
        $discount = 0;
        
        // Calcular puntos disponibles nuevamente para seguridad
        $merchantIds = $cart->items->pluck('product.merchant_id')->unique();
        $availablePoints = \App\Models\LoyaltyPoint::where('user_id', Auth::id())
            ->whereIn('merchant_id', $merchantIds)
            ->sum('points');

        if ($request->boolean('use_points') && $availablePoints > 0) {
            $potentialDiscount = $availablePoints * 50; // 1 punto = $50
            $discount = min($potentialDiscount, $total);
            
            // Deducir puntos (proporcionalmente o hasta agotar descuento)
            $remainingDiscountToApply = $discount;
            $loyaltyRecords = \App\Models\LoyaltyPoint::where('user_id', Auth::id())
                ->whereIn('merchant_id', $merchantIds)
                ->where('points', '>', 0)
                ->get();

            foreach ($loyaltyRecords as $lr) {
                if ($remainingDiscountToApply <= 0) break;
                
                $pointsForThisMerchantValue = $lr->points * 50;
                if ($pointsForThisMerchantValue <= $remainingDiscountToApply) {
                    $remainingDiscountToApply -= $pointsForThisMerchantValue;
                    $lr->update(['points' => 0]);
                } else {
                    $pointsToDeduct = ceil($remainingDiscountToApply / 50);
                    $lr->decrement('points', $pointsToDeduct);
                    $remainingDiscountToApply = 0;
                }
            }
        }

        DB::transaction(function () use ($cart, $request, $total, $discount) {
            $pointsUsed = $discount / 50;
            
            $order = Order::create([
                'user_id'          => Auth::id(),
                'total'            => $total - $discount,
                'discount'         => $discount,
                'points_used'      => $pointsUsed,
                'status'           => 'pending',
                'shipping_address' => strip_tags($request->shipping_address),
                'payment_method'   => $request->payment_method,
                'payment_status'   => 'paid',
            ]);

            // Sistema de Fidelización: Agrupar por comerciante para asignar puntos
            $merchantSubtotals = [];
            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id'         => $order->id,
                    'product_id'       => $item->product_id,
                    'quantity'         => $item->quantity,
                    'price'            => $item->product->price,
                    'selected_options' => $item->selected_options,
                ]);
                
                $item->product->decrement('stock', $item->quantity);
                if ($item->product->fresh()->stock <= 0) {
                    event(new \App\Events\ProductOutOfStock($item->product));
                }

                $mId = $item->product->merchant_id;
                // Los nuevos puntos se ganan sobre el subtotal neto (proporcional al descuento)
                $ratio = ($total > 0) ? (($total - $discount) / $total) : 1;
                $netSubtotal = ($item->quantity * $item->product->price) * $ratio;
                $merchantSubtotals[$mId] = ($merchantSubtotals[$mId] ?? 0) + $netSubtotal;
            }

            $totalPointsEarned = 0;
            foreach ($merchantSubtotals as $mId => $subtotal) {
                $points = floor($subtotal / 1000);
                if ($points > 0) {
                    $totalPointsEarned += $points;
                    \App\Models\LoyaltyPoint::updateOrCreate(
                        ['user_id' => Auth::id(), 'merchant_id' => $mId],
                        ['points' => \Illuminate\Support\Facades\DB::raw("points + $points")]
                    );
                }
            }

            $order->update(['points_earned' => $totalPointsEarned]);

            // Notificar a los comerciantes involucrados
            $merchantIds = $cart->items->pluck('product.merchant_id')->unique();
            foreach ($merchantIds as $mId) {
                $m = User::find($mId);
                if ($m) {
                    $m->notify(new \App\Notifications\NewOrderNotification($order));
                }
            }

            $cart->items()->delete();
            $cart->delete();
        });

        return redirect()->route('consumer.orders')->with('success', '¡Pedido realizado con éxito!');
    }

    public function orders()
    {
        $orders = Order::where('user_id', Auth::id())->with('items.product')->latest()->paginate(10);
        return view('consumer.orders', compact('orders'));
    }

    public function receipt($id)
    {
        $order = Order::where('user_id', Auth::id())
            ->with(['items.product.merchant.companyProfile'])
            ->findOrFail($id);
            
        $userPoints = \App\Models\LoyaltyPoint::where('user_id', Auth::id())->sum('points');
            
        return view('consumer.receipt', compact('order', 'userPoints'));
    }

    // --- PERFIL DE USUARIO ---
    public function profile()
    {
        $user = Auth::user();
        $loyaltyPoints = \App\Models\LoyaltyPoint::where('user_id', $user->id)
            ->with('merchant.companyProfile')
            ->where('points', '>', 0)
            ->get();
        return view('consumer.profile', compact('user', 'loyaltyPoints'));
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|unique:users,email,'.Auth::id(),
            'phone'   => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'avatar'  => 'nullable|image|max:2048',
        ]);

        $user = Auth::user();
        
        $updateData = [
            'name'    => strip_tags($data['name']),
            'email'   => strip_tags($data['email']),
            'phone'   => strip_tags($data['phone'] ?? ''),
            'address' => strip_tags($data['address'] ?? ''),
        ];

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $updateData['avatar'] = $path;
        }

        $user->update($updateData);

        return redirect()->route('account.profile')->with('success', 'Perfil actualizado.');
    }

    // --- DIRECTORIO Y PERFIL DE MARCA ---
    public function merchantDirectory()
    {
        $merchants = CompanyProfile::where('kyc_status', 'approved')
            ->select('id','merchant_id','company_name','logo_path','description')
            ->paginate(12);
        return view('consumer.directory', compact('merchants'));
    }

    /**
     * MÉTODO CORREGIDO: Busca por merchant_id para evitar el error 404
     */
    public function merchantProfile($id)
    {
        $profile = CompanyProfile::where('kyc_status', 'approved')
            ->where('merchant_id', $id)
            ->firstOrFail();

        $products = Product::active()
            ->where('merchant_id', $profile->merchant_id)
            ->paginate(12);

        return view('consumer.merchant-profile', compact('profile', 'products'));
    }

    // --- CALIFICACIONES (REVIEWS) ---
    public function productReview(Request $request, $id)
    {
        $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000'
        ]);

        $product = Product::active()->findOrFail($id);
        
        $hasPurchased = Order::where('user_id', Auth::id())
            ->where('status', 'delivered')
            ->whereHas('items', function ($q) use ($id) {
                $q->where('product_id', $id);
            })->exists();

        if (!$hasPurchased) {
            abort(403, 'Debes comprar y recibir este producto para poder calificarlo.');
        }

        $review = Review::create([
            'product_id' => $product->id,
            'user_id'    => Auth::id(),
            'rating'     => $request->rating,
            'comment'    => strip_tags($request->comment ?? '')
        ]);

        $merchant = User::find($product->merchant_id);
        if ($merchant) {
            $merchant->notify(new NewProductReview($review->load('product', 'user')));
        }

        return back()->with('success', 'Gracias por tu valoración.');
    }

    public function contactSupport()
    {
        $admin = \App\Models\User::where('role', 'analyst')->first();
        if (!$admin) {
            return back()->with('error', 'No hay administradores disponibles en este momento.');
        }
        return redirect()->route('chat.show', $admin->id);
    }
}