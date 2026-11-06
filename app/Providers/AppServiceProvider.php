<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Mail;
use Illuminate\Pagination\Paginator;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Paginator::useTailwind();

        // Asegurar columna legal_name en company_profiles en servidores donde no se haya corrido migrate.
        try {
            $flagPath = storage_path('framework/company_profiles_legal_name_ready.flag');
            if (!is_file($flagPath)) {
                if (\Illuminate\Support\Facades\Schema::hasTable('company_profiles')) {
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('company_profiles', 'legal_name')) {
                        \Illuminate\Support\Facades\Schema::table('company_profiles', function (\Illuminate\Database\Schema\Blueprint $table) {
                            $table->string('legal_name')->nullable()->after('company_name');
                        });
                    }
                    @file_put_contents($flagPath, (string) time());
                }
            }
        } catch (\Throwable) {
            // No bloquear la petición si la conexión aún no está disponible.
        }

        // En local, permite dirigir correos de prueba al correo dueño de la cuenta Resend.
        if (app()->environment('local') && filled(config('mail.always_to'))) {
            Mail::alwaysTo(config('mail.always_to'), 'FusaShop Dev');
        }

        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            $nombre = $notifiable->name ?? 'usuario';
            return (new MailMessage)
                ->subject('Verifica tu correo electrónico - FusaShop')
                ->greeting('¡Hola, ' . $nombre . '!')
                ->line('Bienvenido/a a FusaShop. Haz clic en el botón de abajo para verificar tu dirección de correo electrónico y comenzar a usar la plataforma.')
                ->action('Verificar mi correo', $url)
                ->line('Este enlace expirará en 60 minutos.')
                ->line('Si no creaste una cuenta en FusaShop, puedes ignorar este mensaje.');
        });
    }
}
