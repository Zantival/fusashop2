<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Order;

class NewOrderNotification extends Notification
{
    use Queueable;

    public function __construct(public Order $order)
    {
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('¡Pedido pagado recibido en FusaShop!')
            ->greeting('Hola ' . $notifiable->name . ',')
            ->line('El pago del pedido #' . $this->order->id . ' fue aprobado. Total: $' . number_format($this->order->total, 2) . '.')
            ->action('Ver Detalles del Pedido', route('merchant.orders'))
            ->line('Por favor, prepara el pedido lo antes posible.');
    }

    public function toArray($notifiable): array
    {
        return [
            'id' => $this->order->id,
            'title' => '¡Pedido pagado recibido!',
            'message' => "El pago del pedido #{$this->order->id} fue aprobado. Revisa los detalles y prepara el envío.",
            'icon' => 'shopping_cart',
            'url' => route('merchant.orders'),
            'type' => 'new_order',
            'amount' => $this->order->total,
            'payment_status' => $this->order->payment_status,
        ];
    }
}
