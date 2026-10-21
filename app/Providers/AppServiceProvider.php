<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Paginator::useTailwind();

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
