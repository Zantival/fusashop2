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
