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
            return (new MailMessage)
                ->subject('Verifica tu dirección de correo electrónico - FusaShop')
                ->line('¡Bienvenido a FusaShop! Haz clic en el botón de abajo para verificar tu dirección de correo electrónico y comenzar a usar la plataforma.')
                ->action('Verificar Correo', $url)
                ->line('Si no creaste una cuenta en FusaShop, no es necesario realizar ninguna acción.');
        });
    }
}
