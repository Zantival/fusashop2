<?php

namespace App\Http\Controllers;

use App\Models\{User, Product, Cart, CartItem, Order, OrderItem};
use App\Mail\VerifyEmailCodeMail;
use App\Support\PasswordPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ApiController extends Controller
{
    public function login(Request $request)
    {
        $request->validate(['email' => 'required|email', 'password' => 'required']);
        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)
            || $user->is_blocked || !$user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Credenciales incorrectas.'], 401);
        }
        $token = $user->createToken('api-token')->plainTextToken;
        return response()->json(['token' => $token, 'user' => $user, 'role' => $user->role]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', PasswordPolicy::rule()],
            'role'     => 'required|in:consumer,merchant',
        ], PasswordPolicy::messages());
        $user = User::create([
            'name'     => strip_tags($data['name']),
            'email'    => strtolower($data['email']),
            'password' => Hash::make($data['password']),
            'role'     => $data['role'],
        ]);
        if ($user->isConsumer()) Cart::create(['user_id' => $user->id]);
        $code = $user->generateVerificationCode();
        try {
            Mail::to($user->email)->send(new VerifyEmailCodeMail($user, $code));
        } catch (\Throwable $e) {
            Log::error('API registration verification email could not be sent.', ['exception' => $e::class]);
        }

        return response()->json([
            'message' => 'Cuenta creada. Verifica tu correo con el código de 6 dígitos para iniciar sesión.',
            'requires_verification' => true,
            'email' => $user->email,
        ], 201);
    }

    public function verifyEmailCode(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
        ]);
        $user = User::where('email', strtolower($data['email']))->first();
        if (!$user || $user->is_blocked || $user->hasVerifiedEmail() || !$user->verifyCode($data['code'])) {
            return response()->json(['message' => 'El código no es válido o venció.'], 422);
        }

        $token = $user->createToken('api-token')->plainTextToken;
        return response()->json(['token' => $token, 'user' => $user, 'role' => $user->role]);
    }

    public function resendVerificationCode(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email']]);
        $user = User::where('email', strtolower($data['email']))->first();
        if ($user && !$user->is_blocked && !$user->hasVerifiedEmail()) {
            $code = $user->generateVerificationCode();
            try {
                Mail::to($user->email)->send(new VerifyEmailCodeMail($user, $code));
            } catch (\Throwable $e) {
                Log::error('API verification email could not be resent.', ['exception' => $e::class]);
            }
        }

        return response()->json(['message' => 'Si la cuenta requiere verificación, enviaremos un nuevo código.'], 202);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada.']);
    }

    public function products(Request $request)
    {
        $q = Product::active();
        if ($request->filled('search'))   $q->search($request->search);
        if ($request->filled('category')) $q->where('category', $request->category);
        return response()->json($q->paginate(12));
    }

    public function productShow($id)
    {
        return response()->json(Product::active()->findOrFail($id));
    }

    public function cart(Request $request)
    {
        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);
        return response()->json($cart->load('items.product'));
    }

    public function cartAdd(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:products,id', 'quantity' => 'integer|min:1']);
        $product = Product::active()->with('merchant.companyProfile')->findOrFail($request->integer('product_id'));
        abort_if($product->isReservationOnly(), 422, 'Este comercio gestiona sus servicios mediante reservas; solicita la reserva desde la ficha del servicio.');
        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);
        $item = CartItem::firstOrNew(['cart_id' => $cart->id, 'product_id' => $request->product_id]);
        $item->quantity = ($item->quantity ?? 0) + ($request->quantity ?? 1);
        $item->save();
        return response()->json($cart->load('items.product'));
    }

    public function cartRemove(Request $request, CartItem $item)
    {
        if ($item->cart->user_id !== $request->user()->id) abort(403);
        $item->delete();
        return response()->json(['message' => 'Eliminado.']);
    }

    public function orders(Request $request)
    {
        return response()->json($request->user()->orders()->with('items.product')->latest()->get());
    }
}
