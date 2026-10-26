<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewReservationNotification extends Notification
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
        $customerName = $this->reservation->customer?->name ?? 'Cliente';
        $dateStr = $this->reservation->preferred_date ? $this->reservation->preferred_date->format('d/m/Y') : 'Fecha por convenir';
        $timeStr = $this->reservation->preferred_time ? substr((string) $this->reservation->preferred_time, 0, 5) : 'Hora por convenir';
        $partyStr = $this->reservation->party_size ? "{$this->reservation->party_size} persona(s)" : 'No especificado';

        $mail = (new MailMessage)
            ->subject("¡Nueva solicitud de reserva recibida! ({$this->reservation->reference}) - FusaShop")
            ->greeting("¡Hola, {$notifiable->name}!")
            ->line("Has recibido una nueva solicitud de reserva para **{$productName}** de parte de **{$customerName}**.")
            ->line("📋 **Referencia:** `{$this->reservation->reference}`")
            ->line("📅 **Fecha deseada:** {$dateStr} · ⏰ **Hora:** {$timeStr}")
            ->line("👥 **Comensales / Personas:** {$partyStr}");

        if ($this->reservation->customer_notes) {
            $mail->line("💬 **Mensaje del cliente:** \"{$this->reservation->customer_notes}\"");
        }

        $mail->action('Ver y Gestionar Reserva', route('merchant.reservations'))
            ->line('Por favor ingresa a tu panel comercial para confirmar o gestionar la disponibilidad del servicio.');

        return $mail;
    }

    public function toArray($notifiable): array
    {
        $productName = $this->reservation->product?->name ?? 'Servicio';
        $customerName = $this->reservation->customer?->name ?? 'Cliente';

        return [
            'type' => 'new_reservation',
            'reservation_id' => $this->reservation->id,
            'reference' => $this->reservation->reference,
            'title' => '¡Nueva solicitud de reserva!',
            'message' => "{$customerName} solicitó reservar \"{$productName}\" (Ref: {$this->reservation->reference}). Revisa fechas y confirma disponibilidad.",
            'icon' => 'event_available',
            'url' => route('merchant.reservations'),
            'customer_name' => $customerName,
            'product_name' => $productName,
        ];
    }
}
