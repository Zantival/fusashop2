<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservationCancelledNotification extends Notification
{
    use Queueable;

    public function __construct(public Reservation $reservation)
    {
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $productName = $this->reservation->product?->name ?? 'Servicio';
        $customerName = $this->reservation->customer?->name ?? 'El cliente';

        $mail = (new MailMessage)
            ->subject("Reserva cancelada por el cliente ({$this->reservation->reference}) - FusaShop")
            ->greeting("¡Hola, {$notifiable->name}!")
            ->line("**{$customerName}** ha cancelado su solicitud de reserva para **{$productName}**.")
            ->line("📋 **Referencia:** `{$this->reservation->reference}`");

        if ($this->reservation->preferred_date) {
            $mail->line("📅 **Fecha agendada previamente:** " . $this->reservation->preferred_date->format('d/m/Y'));
        }

        $mail->action('Ver Reservas', route('merchant.reservations'))
            ->line('Tu agenda y disponibilidad se han actualizado automáticamente.');

        return $mail;
    }

    public function toArray($notifiable): array
    {
        $productName = $this->reservation->product?->name ?? 'Servicio';
        $customerName = $this->reservation->customer?->name ?? 'El cliente';

        return [
            'type' => 'reservation_cancelled',
            'reservation_id' => $this->reservation->id,
            'reference' => $this->reservation->reference,
            'status' => 'cancelled',
            'title' => 'Reserva cancelada por el cliente',
            'message' => "{$customerName} canceló la solicitud de reserva para \"{$productName}\" (Ref: {$this->reservation->reference}).",
            'icon' => 'event_busy',
            'url' => route('merchant.reservations'),
            'product_name' => $productName,
            'customer_name' => $customerName,
        ];
    }
}
