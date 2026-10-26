<?php

namespace App\Notifications;

use App\Models\PQRS;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewPQRSNotification extends Notification
{
    use Queueable;

    protected $pqrs;

    public function __construct(PQRS $pqrs)
    {
        $this->pqrs = $pqrs;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $isSecurity = $this->pqrs->category === 'seguridad';
        return (new MailMessage)
            ->subject(($isSecurity ? '[URGENTE · SEGURIDAD] ' : '') . 'Nueva PQRS - ' . ucfirst($this->pqrs->type))
            ->line($isSecurity
                ? 'Se recibió un reporte urgente de seguridad o posible fraude.'
                : 'Se ha recibido una nueva solicitud de tipo ' . ucfirst($this->pqrs->type) . '.')
            ->line('Usuario: ' . $this->pqrs->user->name)
            ->line('Asunto: ' . $this->pqrs->subject)
            ->action('Ver Detalle', url('/admin/pqrs/' . $this->pqrs->id))
            ->line('Por favor, revisa la solicitud lo antes posible.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => ($this->pqrs->category === 'seguridad' ? '🚨 Reporte urgente de seguridad: ' : 'Nueva PQRS: ') . ucfirst($this->pqrs->type),
            'message' => $this->pqrs->user->name . ' ha enviado una ' . $this->pqrs->type . '.',
            'icon' => 'assignment',
            'pqrs_id' => $this->pqrs->id
        ];
    }
}
