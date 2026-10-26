<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\VerifyEmailCodeMail;
use App\Mail\WelcomeMail;
use App\Models\Cart;
use App\Models\User;
use App\Support\PasswordPolicy;
use App\Notifications\NewUserNotification;
use App\Notifications\WelcomeNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        // Aceptar solo destinos locales para evitar redirecciones abiertas.
        $intended = $request->query('intended');
        if (is_string($intended) && $this->isSafeLocalRedirect($intended)) {
            redirect()->setIntendedUrl($intended);
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'string', 'email:rfc', 'max:254'],
            'password' => ['required', 'string', 'max:1024'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Bloquear acceso si la cuenta está bloqueada
            if (Auth::user()->is_blocked) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors(['email' => 'Tu cuenta ha sido bloqueada. Contacta al administrador.'])->onlyInput('email');
            }

            // Si aún no está verificado, enviar o asegurar código activo
            if (! Auth::user()->hasVerifiedEmail()) {
                $user = Auth::user();
                if (empty($user->verification_code) || ($user->verification_code_expires_at && $user->verification_code_expires_at->isPast())) {
                    $code = $user->generateVerificationCode();
                    try {
                        Mail::to($user->email)->send(new VerifyEmailCodeMail($user, $code));
                    } catch (\Throwable $e) {
                        Log::error('Error enviando código de verificación al login: ' . $e->getMessage());
                        session()->flash('verification-email-error', 'Revisa la configuración y el remitente verificado de Resend; luego solicita un nuevo código.');
                    }
                }
                return redirect()->route('verification.notice');
            }

            return $this->redirectByRole(Auth::user());
        }

        return back()->withErrors(['email' => 'Credenciales incorrectas.'])->onlyInput('email');
    }

    private function isSafeLocalRedirect(string $url): bool
    {
        if ($url === '' || preg_match('/[\\\\\x00-\x1F\x7F]/', $url)) {
            return false;
        }

        $parts = parse_url($url);
        if ($parts === false || isset($parts['user']) || isset($parts['pass'])) {
            return false;
        }

        if (isset($parts['host'])) {
            $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
            return $appHost !== null
                && strcasecmp($parts['host'], $appHost) === 0
                && in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true);
        }

        return str_starts_with($url, '/') && !str_starts_with($url, '//');
    }

    public function showRegister(Request $request)
    {
        $role = in_array($request->query('role'), ['consumer', 'merchant']) ? $request->query('role') : 'consumer';
        return view('auth.register', compact('role'));
    }

    public function register(Request $request)
    {
        $isSocial = session()->has('social_registration');
        $socialData = session('social_registration', []);

        $rules = [
            'name'     => ['required', 'string', 'max:255', function ($attribute, $value, $fail) {
                if (!preg_match('/^\p{L}+(?:[ \'\x{2019}-]\p{L}+)*$/u', $value)) {
                    $fail('Escribe tu nombre y apellidos usando letras; permite un solo espacio entre palabras, sin espacios al inicio, al final ni repetidos.');
                }
            }],
            'email'    => ['required', 'email', 'unique:users,email'],
            'role'     => ['required', 'in:consumer,merchant'],
            'phone_country' => ['required', 'in:57,1,52,34,51,56,54'],
            'phone'    => ['required', 'regex:/^[0-9]{7,15}$/'],
            'accepted_terms' => ['required', 'accepted'],
            'accepted_privacy' => ['required', 'accepted'],
            'age_or_guardian_confirmed' => ['required', 'accepted'],
            'marketing_opt_in'          => ['nullable', 'boolean'],
        ];

        if ($isSocial) {
            $rules['password'] = ['nullable', 'confirmed', PasswordPolicy::rule()];
        } else {
            $rules['password'] = ['required', 'confirmed', PasswordPolicy::rule()];
        }

        $data = $request->validate($rules, [
            'phone.required' => 'El número de teléfono es obligatorio.',
            'phone.regex' => 'Escribe un número válido sin espacios. En Colombia debe tener 10 dígitos y comenzar por 3 (no por 0).',
            'phone_country.required' => 'Selecciona el indicativo de tu país.',
            'name.regex' => 'Escribe un nombre válido, sin espacios al inicio, al final ni repetidos.',
            ...PasswordPolicy::messages(),
        ]);

        if (($data['phone_country'] === '57' && (!preg_match('/^3[0-9]{9}$/', $data['phone'])))
            || ($data['phone_country'] !== '57' && strlen($data['phone']) < 7)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'phone' => $data['phone_country'] === '57'
                    ? 'El celular colombiano debe tener 10 dígitos y comenzar por 3.'
                    : 'Revisa el número para el indicativo internacional seleccionado.',
            ]);
        }

        // Si el usuario se registra como Comerciante, validar y extraer su método de pago preferido
        $merchantPayoutData = [];
        if ($data['role'] === 'merchant') {
            $hasPayoutInput = $request->filled('payout_account_number') || $request->filled('wallet_phone');
            if ($hasPayoutInput) {
                $merchantPayoutData = \App\Models\CompanyProfile::validateAndExtractPayoutData($request, true);
            }
        }

        $code = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        $hashedPassword = !empty($data['password'])
            ? Hash::make($data['password'])
            : Hash::make(Str::random(32));

        $user = User::create([
            'name'                         => strip_tags($data['name']),
            'email'                        => strtolower($data['email']),
            'password'                     => $hashedPassword,
            'role'                         => $data['role'],
            'phone'                        => '+' . $data['phone_country'] . $data['phone'],
            'provider'                     => $isSocial ? ($socialData['provider'] ?? null) : null,
            'provider_id'                  => $isSocial ? ($socialData['provider_id'] ?? null) : null,
            'avatar'                       => $isSocial ? ($socialData['avatar'] ?? null) : null,
            'verification_code'            => $code,
            'verification_code_expires_at' => now()->addMinutes(30),
            'email_verified_at'            => null,
            'accepted_terms_at'             => now(),
            'accepted_privacy_at'           => now(),
            'accepted_terms_version'        => '2026-10-25',
            'accepted_privacy_version'      => '2026-10-25',
            'age_or_guardian_confirmed_at' => now(),
            'marketing_consent_at'          => !empty($data['marketing_opt_in']) ? now() : null,
        ]);

        if ($isSocial) {
            session()->forget('social_registration');
        }

        if ($user->isConsumer()) {
            Cart::create(['user_id' => $user->id]);
        } elseif ($user->isMerchant() && (!empty($merchantPayoutData) || $request->filled('company_name'))) {
            \App\Models\CompanyProfile::updateOrCreate(
                ['merchant_id' => $user->id],
                array_merge([
                    'company_name'          => strip_tags(trim((string) $request->input('company_name'))) ?: $user->name,
                    'phone'                 => $user->phone ?? '',
                    'rut_path'              => '',
                    'kyc_status'            => 'pending',
                    'payout_account_holder' => $merchantPayoutData['payout_account_holder'] ?? $user->name,
                ], $merchantPayoutData)
            );
        }

        $verificationEmailSent = false;
        try {
            Mail::to($user->email)->send(new VerifyEmailCodeMail($user, $code));
            $verificationEmailSent = true;
        } catch (\Throwable $e) {
            Log::error('Error enviando código de verificación durante el registro: ' . $e->getMessage());
        }

        try {
            // Avisar a administración del registro. La bienvenida al usuario se reserva
            // para después de que confirme el código recibido por correo.
            $analysts = User::where('role', 'analyst')->get();
            foreach ($analysts as $analyst) {
                $analyst->notify(new NewUserNotification($user));
            }
        } catch (\Throwable $e) {
            Log::error('Error enviando correos durante el registro: ' . $e->getMessage());
        }

        Auth::login($user);
        $request->session()->regenerate();

        $response = redirect()->route('verification.notice');
        return $verificationEmailSent
            ? $response->with('status', 'verification-code-sent')
            : $response->with('verification-email-error', 'No se pudo enviar a tu correo. Configura un remitente y dominio verificados en Resend y vuelve a reenviar el código.');
    }

    public function verifyEmailCode(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'digits:6'],
        ], [
            'code.required' => 'Por favor digita los 6 números del código.',
            'code.digits'   => 'El código de verificación debe contener exactamente 6 dígitos.',
        ]);

        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->hasVerifiedEmail()) {
            return $this->redirectByRole($user);
        }

        if (! $user->verifyCode($request->code)) {
            return back()->withErrors([
                'code' => 'El código de 6 números es incorrecto o ha vencido. Por favor solicita un nuevo código.'
            ]);
        }

        try {
            Mail::to($user->email)->send(new WelcomeMail($user));
        } catch (\Throwable $e) {
            Log::error('No fue posible enviar el correo de bienvenida posterior a la verificación.', [
                'user_id' => $user->id,
                'exception' => $e::class,
            ]);
        }

        try {
            $user->notify(new WelcomeNotification());
        } catch (\Throwable $e) {
            Log::error('No fue posible guardar la notificación de bienvenida posterior a la verificación.', [
                'user_id' => $user->id,
                'exception' => $e::class,
            ]);
        }

        session()->flash('welcome_new_user', true);
        session()->flash('success', '¡Excelente! Tu correo electrónico ha sido verificado con éxito.');

        return $this->redirectByRole($user);
    }

    public function resendVerificationCode(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->hasVerifiedEmail()) {
            return $this->redirectByRole($user);
        }

        $code = $user->generateVerificationCode();

        try {
            Mail::to($user->email)->send(new VerifyEmailCodeMail($user, $code));
        } catch (\Throwable $e) {
            Log::error('Error reenviando código de verificación: ' . $e->getMessage());
            return back()->with('verification-email-error', 'No se pudo enviar a tu correo. Configura un remitente y dominio verificados en Resend y vuelve a intentarlo.');
        }

        return back()->with('status', 'verification-code-sent');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    private function redirectByRole(User $user)
    {
        // Si el usuario no tiene su correo verificado (y no es administrador), lo forzamos a la pantalla de verificación.
        if (! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        if ($user->role === 'merchant') {
            if (!$user->companyProfile || $user->companyProfile->kyc_status !== 'approved') {
                return redirect()->intended(route('merchant.profile'));
            }
            return redirect()->intended(route('merchant.dashboard'));
        }

        return match ($user->role) {
            'analyst'  => redirect()->route('analyst.dashboard'),
            default    => redirect()->intended(route('consumer.home')),
        };
    }

    public function showPasswordRequest()
    {
        return view('auth.forgot-password');
    }

    public function sendPasswordResetLink(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        try {
            PasswordBroker::sendResetLink($request->only('email'));
        } catch (\Throwable $exception) {
            Log::error('Password reset email could not be sent.', ['exception' => $exception::class]);

            return back()->withInput($request->only('email'))
                ->with('error', 'No pudimos enviar el correo en este momento. Revisa la configuración del servicio de correo e inténtalo de nuevo.');
        }
        $message = 'Si el correo está asociado a una cuenta, recibirás un enlace para cambiar tu contraseña. Revisa también la carpeta de spam.';

        return back()->with('status', $message);
    }

    public function showPasswordReset(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordPolicy::rule()],
        ], PasswordPolicy::messages());

        $status = PasswordBroker::reset(
            $data,
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status === PasswordBroker::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', 'Tu contraseña se cambió correctamente. Ya puedes iniciar sesión.');
        }

        return back()->withInput($request->only('email'))
            ->withErrors(['email' => 'El enlace de recuperación no es válido o venció. Solicita uno nuevo.']);
    }
}
