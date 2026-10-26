<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class PasswordResetLinkNotification extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $minutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject('Recupera tu contraseña de FusaShop')
            ->greeting('Hola'.($notifiable->name ? ' '.$notifiable->name : ''))
            ->line('Recibimos una solicitud para cambiar la contraseña de tu cuenta.')
            ->action('Crear contraseña nueva', $this->resetUrl($notifiable))
            ->line("Este enlace vence en {$minutes} minutos y solo se puede usar una vez.")
            ->line('Si no solicitaste este cambio, puedes ignorar este mensaje. Tu contraseña seguirá igual.');
    }
}
