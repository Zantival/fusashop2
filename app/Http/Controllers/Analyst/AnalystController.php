<?php

namespace App\Http\Controllers\Analyst;

use App\Http\Controllers\Controller;
use App\Models\{User, Product, Order, OrderItem, CompanyProfile, Review, GlobalBanner};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Hash, Storage};
use Illuminate\Validation\Rules\Password;
use Symfony\Component\Process\Process;

class AnalystController extends Controller
{
    public function dashboard()
    {
        $totalSales     = OrderItem::selectRaw('SUM(quantity * price) as total')->value('total') ?? 0;
        $totalOrders    = Order::count();
        $totalConsumers = User::where('role','consumer')->count();
        $totalMerchants = User::where('role','merchant')->count();

        $monthlySales   = Order::selectRaw('MONTH(created_at) as month, SUM(total) as total')
                               ->whereYear('created_at', date('Y'))
                               ->groupBy('month')->orderBy('month')->get();
        $ordersByStatus = Order::selectRaw('status, COUNT(*) as count')->groupBy('status')->get();

        $salesByCompany = OrderItem::join('products', 'products.id', '=', 'order_items.product_id')
                            ->join('company_profiles', 'company_profiles.merchant_id', '=', 'products.merchant_id')
                            ->select('company_profiles.company_name', DB::raw('SUM(order_items.quantity * order_items.price) as total_sales'))
                            ->groupBy('company_profiles.company_name')
                            ->orderByDesc('total_sales')
                            ->take(6)->get();

        $topProducts    = OrderItem::selectRaw('product_id, SUM(quantity) as sold')
                                   ->groupBy('product_id')->orderByDesc('sold')->take(5)
                                   ->with('product')->get();
        $recentUsers    = User::latest()->take(5)->get();
        
        $pendingKyc = CompanyProfile::where('kyc_status', 'pending')->with('user')->latest()->take(5)->get();
        $pendingBanners = \App\Models\BannerRequest::where('status', 'pending')->with('user')->latest()->take(5)->get();

        return view('analyst.dashboard', compact(
            'totalSales','totalOrders','totalConsumers','totalMerchants',
            'monthlySales','salesByCompany','ordersByStatus','topProducts','recentUsers','pendingKyc', 'pendingBanners'
        ));
    }

    public function users(Request $request)
    {
        $q = User::query();
        if ($request->filled('role'))   $q->where('role', $request->role);
        if ($request->filled('search')) $q->where(function($query) use ($request) {
            $query->where('name','like','%'.$request->search.'%')
                  ->orWhere('email','like','%'.$request->search.'%');
        });
        $users = $q->latest()->paginate(20)->withQueryString();
        return view('analyst.users', compact('users'));
    }

    public function userCreate()
    {
        return view('analyst.user-form', ['user' => null]);
    }

    public function userStore(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => ['required', Password::min(8)],
            'role'     => 'required|in:consumer,merchant,analyst',
            'phone'    => 'nullable|string|max:20',
        ]);
        User::create([
            'name'     => strip_tags($data['name']),
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => $data['role'],
            'phone'    => $data['phone'] ?? null,
        ]);
        return redirect()->route('analyst.users')->with('success', 'Usuario creado exitosamente.');
    }

    public function userEdit(User $user)
    {
        return view('analyst.user-form', compact('user'));
    }

    public function userUpdate(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,'.$user->id,
            'role'     => 'required|in:consumer,merchant,analyst',
            'phone'    => 'nullable|string|max:20',
            'password' => ['nullable', Password::min(8)],
        ]);
        $update = [
            'name'  => strip_tags($data['name']),
            'email' => $data['email'],
            'role'  => $data['role'],
            'phone' => $data['phone'] ?? null,
        ];
        if (!empty($data['password'])) $update['password'] = Hash::make($data['password']);
        $user->update($update);
        return redirect()->route('analyst.users')->with('success', 'Usuario actualizado.');
    }

    public function userDelete(User $user)
    {
        if ($user->id === auth()->id()) return back()->with('error', 'No puedes eliminar tu propia cuenta.');
        $user->delete();
        return back()->with('success', 'Usuario eliminado.');
    }

    public function userToggleBlock(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes bloquearte a ti mismo.');
        }
        $user->update(['is_blocked' => !$user->is_blocked]);
        $msg = $user->is_blocked ? 'bloqueado' : 'desbloqueado';
        return back()->with('success', "Usuario {$msg} exitosamente.");
    }

    public function userRut(User $user)
    {
        $profile = $user->companyProfile;
        if (!$profile || !$profile->rut_path) abort(404, 'RUT no encontrado');
        $path = storage_path('app/private/' . $profile->rut_path);
        if (!file_exists($path)) $path = storage_path('app/' . $profile->rut_path);
        if (!file_exists($path)) abort(404, 'Archivo físico no encontrado');
        return response()->file($path);
    }

    public function updateKyc(Request $request, User $user)
    {
        $request->validate([
            'kyc_status' => 'required|in:approved,rejected',
            'kyc_notes'  => 'nullable|string|max:1000',
        ]);

        $profile = $user->companyProfile;
        if ($profile) {
            $profile->update([
                'kyc_status' => $request->kyc_status,
                'kyc_notes'  => $request->kyc_notes,
            ]);

            // Notify the merchant
            $user->notify(new \App\Notifications\KycDecisionNotification($profile));
        }

        return back()->with('success', 'Estado KYC actualizado y comerciante notificado.');
    }

    public function orders(Request $request)
    {
        $q = Order::with('user','items.product');
        if ($request->filled('status')) $q->where('status', $request->status);
        $orders = $q->latest()->paginate(20)->withQueryString();
        return view('analyst.orders', compact('orders'));
    }

    public function payments()
    {
        $bannerPayments = \App\Models\BannerRequest::with('user')
            ->latest()
            ->get()
            ->map(function($br) {
                return [
                    'id'          => $br->id,
                    'type'        => 'Banner Publicitario',
                    'merchant'    => $br->user->name ?? 'Desconocido',
                    'email'       => $br->user->email ?? '-',
                    'amount'      => 150000,
                    'status'      => $br->status,
                    'date'        => $br->created_at,
                    'detail_url'  => route('analyst.banner-requests.show', $br->id),
                ];
            });

        $merchantAccounts = \App\Models\CompanyProfile::with('user')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($cp) {
                return [
                    'id'           => $cp->merchant_id,
                    'company_name' => $cp->company_name,
                    'owner'        => $cp->user->name ?? 'Desconocido',
                    'email'        => $cp->user->email ?? '-',
                    'kyc_status'   => $cp->kyc_status,
                    'created_at'   => $cp->created_at,
                ];
            });

        $totalBannerRevenue = $bannerPayments->where('status', 'approved')->sum('amount');

        return view('analyst.payments', compact('bannerPayments', 'merchantAccounts', 'totalBannerRevenue'));
    }

    public function analyticsData()
    {
        return response()->json([
            'monthly_sales'    => Order::where('payment_status','paid')
                ->select(DB::raw('MONTH(created_at) as month'), DB::raw('SUM(total) as total'))
                ->whereYear('created_at', date('Y'))->groupBy('month')->orderBy('month')->get(),
            'category_revenue' => OrderItem::join('products','products.id','=','order_items.product_id')
                ->select('products.category', DB::raw('SUM(order_items.quantity * order_items.price) as revenue'))
                ->groupBy('products.category')->get(),
        ]);
    }

    // ─── Global Banners ────────────────────────────────────────────────────────
    public function banners()
    {
        $banners = GlobalBanner::orderBy('sort_order')->get();
        return view('analyst.banners', compact('banners'));
    }

    public function bannerRequestShow($id)
    {
        $requestInfo = \App\Models\BannerRequest::with('user')->findOrFail($id);
        return view('analyst.banner-request-detail', compact('requestInfo'));
    }

    public function bannerRequestApprove(Request $request, $id)
    {
        $requestInfo = \App\Models\BannerRequest::with('user')->findOrFail($id);
        
        if ($requestInfo->status !== 'pending') {
            return back()->with('error', 'Esta solicitud ya ha sido procesada.');
        }

        $requestInfo->update(['status' => 'approved']);

        // Determine destination link
        $linkUrl = route('consumer.merchant.profile', $requestInfo->user_id);
        if ($request->input('link_type') === 'custom' && $request->filled('custom_link_url')) {
            $linkUrl = $request->custom_link_url;
        }

        GlobalBanner::create([
            'title'      => 'Banner Patrocinado - ' . ($requestInfo->user->companyProfile->company_name ?? 'Comerciante'),
            'image_path' => $requestInfo->image_path,
            'link_url'   => $linkUrl,
            'is_active'  => true,
            'sort_order' => GlobalBanner::max('sort_order') + 1,
        ]);

        return redirect()->route('analyst.banners')->with('success', 'La solicitud fue aprobada. El Banner ha sido publicado globalmente.');
    }

    public function salesReportPrint()
    {
        $monthlySales = Order::selectRaw('MONTH(created_at) as month, SUM(total) as total')
                             ->whereYear('created_at', date('Y'))
                             ->groupBy('month')->orderBy('month')->get();
                             
        $salesByCompany = OrderItem::join('products', 'products.id', '=', 'order_items.product_id')
                            ->join('company_profiles', 'company_profiles.merchant_id', '=', 'products.merchant_id')
                            ->select('company_profiles.company_name', DB::raw('SUM(order_items.quantity * order_items.price) as total_sales'))
                            ->groupBy('company_profiles.company_name')
                            ->orderByDesc('total_sales')
                            ->get();

        return view('analyst.sales-report-print', compact('monthlySales', 'salesByCompany'));
    }

    public function bannerStore(Request $request)
    {
        $request->validate([
            'title'     => 'nullable|string|max:255',
            'image'     => 'required|file|mimes:jpg,jpeg,png,webp,mp4,webm,mov|max:10240',
            'link_url'  => 'nullable|url|max:500',
            'sort_order'=> 'integer|min:0',
        ]);
        $path = $request->file('image')->store('global_banners', 'public');
        GlobalBanner::create([
            'title'      => strip_tags($request->title ?? ''),
            'image_path' => $path,
            'link_url'   => $request->link_url,
            'is_active'  => true,
            'sort_order' => $request->sort_order ?? 0,
        ]);
        return back()->with('success', 'Banner creado exitosamente.');
    }

    public function updateGlobalLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:png,jpg,jpeg,webp,svg|max:2048'
        ]);

        $request->file('logo')->storeAs('system', 'global_logo.png', 'public');
        
        return back()->with('success', 'Logo del sistema actualizado.');
    }

    public function bannerToggle(GlobalBanner $banner)
    {
        $banner->update(['is_active' => !$banner->is_active]);
        return back()->with('success', 'Banner ' . ($banner->is_active ? 'activado' : 'desactivado') . '.');
    }

    public function bannerDelete(GlobalBanner $banner)
    {
        Storage::disk('public')->delete($banner->image_path);
        $banner->delete();
        return back()->with('success', 'Banner eliminado.');
    }

    // ─── ML Reviews Report ─────────────────────────────────────────────────────
    public function reviewsReport()
    {
        $merchants = CompanyProfile::with('user.products.reviews')->get();
        $report = [];

        foreach ($merchants as $m) {
            if (!$m->user) continue;
            $productIds = $m->user->products->pluck('id');
            $reviews = Review::whereIn('product_id', $productIds)->with('product')->get();

            if ($reviews->isEmpty()) continue;

            $avgRating   = round($reviews->avg('rating'), 2);
            $totalReviews = $reviews->count();
            $totalSales  = (float) OrderItem::whereIn('product_id', $productIds)->sum(DB::raw('quantity * price'));

            // Simulated sentiment analysis based on rating
            $positive = $reviews->where('rating', '>=', 4)->count();
            $neutral  = $reviews->where('rating', 3)->count();
            $negative = $reviews->where('rating', '<=', 2)->count();

            $sentiment = 'Neutral';
            if ($avgRating >= 4.0) $sentiment = 'Positivo';
            if ($avgRating < 3.0)  $sentiment = 'Negativo';

            // Exact count of stars
            $stars = [
                5 => $reviews->where('rating', 5)->count(),
                4 => $reviews->where('rating', 4)->count(),
                3 => $reviews->where('rating', 3)->count(),
                2 => $reviews->where('rating', 2)->count(),
                1 => $reviews->where('rating', 1)->count(),
            ];

            // Best and worst products
            $byProduct = $reviews->groupBy('product_id')->map(function($g) {
                return ['avg' => round($g->avg('rating'), 2), 'count' => $g->count(), 'name' => $g->first()->product->name ?? 'N/A'];
            })->sortByDesc(fn($v) => $v['avg']);

            $bestProduct  = $byProduct->first();
            $worstProduct = $byProduct->last();

            // ML cluster simulation: tier based on rating + volume
            $score = ($avgRating / 5) * 0.6 + min(1, $totalSales / 10000000) * 0.4;
            $tier  = $score >= 0.7 ? 'Excelente' : ($score >= 0.4 ? 'Aceptable' : 'Peligro');

            $report[] = [
                'merchant_id'   => $m->merchant_id,
                'company_name'  => $m->company_name,
                'avg_rating'    => $avgRating,
                'total_reviews' => $totalReviews,
                'total_sales'   => $totalSales,
                'sentiment'     => $sentiment,
                'positive'      => $positive,
                'neutral'       => $neutral,
                'negative'      => $negative,
                'stars'         => $stars,
                'best_product'  => $bestProduct,
                'worst_product' => $worstProduct,
                'tier'          => $tier,
                'score'         => round($score * 100, 1),
            ];
        }

        usort($report, fn($a, $b) => $b['avg_rating'] <=> $a['avg_rating']);

        return view('analyst.reviews-report', compact('report'));
    }

    public function runMl()
    {
        $merchants = CompanyProfile::with('user.products.reviews')->get();
        $payload = [];
        foreach ($merchants as $m) {
            $productIds = $m->user ? $m->user->products->pluck('id') : collect();
            $totalSales = OrderItem::whereIn('product_id', $productIds)->sum(DB::raw('quantity * price'));
            $avgRating  = Review::whereIn('product_id', $productIds)->avg('rating') ?? 0;
            $payload[] = [
                'merchant_id'  => $m->merchant_id,
                'company_name' => $m->company_name,
                'total_sales'  => (float) $totalSales,
                'avg_rating'   => (float) $avgRating,
            ];
        }

        $scriptPath = base_path('python/ml_analyzer.py');
        if (!file_exists($scriptPath)) {
            return back()->with('error', 'Script ML no encontrado.');
        }
        $process = new Process(['python3', $scriptPath]);
        $process->setInput(json_encode($payload));
        $process->run();

        if (!$process->isSuccessful()) {
            return back()->with('error', 'Error en el motor ML: ' . $process->getErrorOutput());
        }

        $output = json_decode($process->getOutput(), true);
        if (isset($output['status']) && $output['status'] === 'error') {
            return back()->with('error', 'ML Error: ' . $output['message']);
        }

        session()->flash('ml_results', $output['analytics'] ?? []);
        return back()->with('success', 'Análisis ML completado.');
    }
}
