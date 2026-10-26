<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UnrecognizedRegistrationNotification extends Notification
{
    use Queueable;

    public function __construct(public User $reportedUser)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('🚨 [ALERTA DE SEGURIDAD] Registro no reconocido reportado: ' . $this->reportedUser->email)
            ->greeting('Equipo Administrador de FusaShop,')
            ->line('El titular de la cuenta de correo electrónico ha reportado que NO creó esta cuenta ni solicitó el código de verificación:')
            ->line('• Correo reportado: ' . $this->reportedUser->email)
            ->line('• Nombre registrado: ' . $this->reportedUser->name)
            ->line('• Rol asignado: ' . ucfirst($this->reportedUser->role))
            ->line('• Fecha de registro: ' . $this->reportedUser->created_at?->format('d/m/Y H:i:s'))
            ->line('🛡️ Acción preventiva automática: La cuenta ha sido bloqueada de inmediato en la base de datos y el código de verificación OTP ha sido revocado.')
            ->action('Revisar Cuenta en Panel de Analista', route('analyst.users.edit', $this->reportedUser->id))
            ->line('Por favor verifica el caso en el panel de control de usuarios para determinar si se trata de un error de digitación o de un intento de suplantación.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'unrecognized_registration_report',
            'user_id' => $this->reportedUser->id,
            'user_email' => $this->reportedUser->email,
            'user_name' => $this->reportedUser->name,
            'created_at' => $this->reportedUser->created_at?->toIso8601String(),
            'message' => 'Alerta de Seguridad: El titular del correo ' . $this->reportedUser->email . ' reportó que no solicitó el registro. La cuenta fue bloqueada preventivamente.',
        ];
    }
}
