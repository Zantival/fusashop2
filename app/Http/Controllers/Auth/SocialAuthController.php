<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\VerifyEmailCodeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirect(string $provider)
    {
        if ($provider !== 'google') {
            abort(404);
        }

        if (empty(config('services.google.client_id')) || empty(config('services.google.client_secret'))) {
            return redirect()->route('login')->withErrors([
                'email' => 'El inicio de sesión con Google no está configurado. Agrega GOOGLE_CLIENT_ID y GOOGLE_CLIENT_SECRET en el archivo .env.',
            ]);
        }

        return Socialite::driver($provider)->redirect();
    }

    public function cancel()
    {
        session()->forget('social_registration');
        return redirect()->route('register');
    }

    public function callback(string $provider)
    {
        if ($provider !== 'google') {
            abort(404);
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
            $email = strtolower((string) $socialUser->getEmail());

            $user = User::where('email', $email)->first();

            // CASO 1: Si no existe en la base de datos, NO creamos la cuenta en blanco.
            // Redirigimos a la pantalla de registro para pedir los datos faltantes (Rol: Comprador/Vendedor, Teléfono, etc.)
            if (!$user) {
                session([
                    'social_registration' => [
                        'provider'    => $provider,
                        'provider_id' => (string) $socialUser->getId(),
                        'email'       => $email,
                        'name'        => $socialUser->getName() ?: ($socialUser->getNickname() ?: 'Usuario Google'),
                        'avatar'      => $socialUser->getAvatar(),
                    ],
                ]);

                return redirect()->route('register')->with('info', "Tu cuenta de Google ({$email}) aún no está registrada en FusaShop. Selecciona si deseas Comprar o Vender y completa tus datos para crear tu cuenta.");
            }

            // CASO 2: La cuenta ya existe en la base de datos
            if ($user->is_blocked) {
                return redirect()->route('login')->withErrors([
                    'email' => 'Tu cuenta ha sido bloqueada. Contacta al administrador.',
                ]);
            }

            // Vincular provider y provider_id si no estaban guardados
            if (empty($user->provider_id)) {
                $user->forceFill([
                    'provider'    => $provider,
                    'provider_id' => (string) $socialUser->getId(),
                ])->save();
            }

            // Si el correo no ha sido verificado con los 6 dígitos, enviar código y redirigir a verificación OTP
            if (! $user->hasVerifiedEmail()) {
                Auth::login($user);
                if (empty($user->verification_code) || ($user->verification_code_expires_at && $user->verification_code_expires_at->isPast())) {
                    $code = $user->generateVerificationCode();
                    try {
                        Mail::to($user->email)->send(new VerifyEmailCodeMail($user, $code));
                    } catch (\Throwable $e) {
                        Log::error('Error enviando código de verificación al login con Google: ' . $e->getMessage());
                    }
                }
                return redirect()->route('verification.notice');
            }

            // Usuario verificado: iniciar sesión normalmente
            Auth::login($user, true);
            return $this->redirectByRole($user);

        } catch (\Throwable $e) {
            Log::error('Error en callback de autenticación social.', ['provider' => $provider, 'exception' => $e]);
            return redirect()->route('login')->withErrors([
                'email' => 'No se pudo iniciar sesión con Google. Intenta de nuevo.',
            ]);
        }
    }

    private function redirectByRole(User $user)
    {
        if ($user->role === 'merchant') {
            if (!$user->companyProfile) {
                return redirect()->route('merchant.profile');
            }
            return redirect()->route('merchant.dashboard');
        }

        return match ($user->role) {
            'analyst' => redirect()->route('analyst.dashboard'),
            default   => redirect()->route('consumer.home'),
        };
    }
}
