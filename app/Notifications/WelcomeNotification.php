<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        // El correo de bienvenida se envía con una plantilla de marca después de verificar el OTP.
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => '¡Bienvenido!',
            'message' => 'Tu cuenta ha sido creada con éxito. ¡Explora los productos locales!',
            'icon' => 'waving_hand'
        ];
    }
}
