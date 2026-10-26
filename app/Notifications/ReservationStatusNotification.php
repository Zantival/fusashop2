<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservationStatusNotification extends Notification
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
        $merchantName = $this->reservation->merchant?->companyProfile?->company_name ?? $this->reservation->merchant?->name ?? 'Comercio';

        $statusLabels = [
            'confirmed' => '¡Tu reserva ha sido CONFIRMADA!',
            'rejected' => 'Tu solicitud de reserva no pudo ser aceptada',
            'completed' => 'Tu reserva ha sido marcada como completada',
            'cancelled' => 'Tu reserva ha sido cancelada',
        ];

        $statusTitles = [
            'confirmed' => 'Reserva Confirmada',
            'rejected' => 'Reserva No Disponible',
            'completed' => 'Servicio Completado',
            'cancelled' => 'Reserva Cancelada',
        ];

        $subject = ($statusLabels[$this->reservation->status] ?? 'Actualización de tu reserva') . " ({$this->reservation->reference}) - FusaShop";

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting("¡Hola, {$notifiable->name}!")
            ->line("El comercio **{$merchantName}** ha actualizado el estado de tu reserva para **{$productName}**.")
            ->line("📋 **Referencia:** `{$this->reservation->reference}`")
            ->line("📌 **Nuevo estado:** " . ($statusTitles[$this->reservation->status] ?? ucfirst($this->reservation->status)));

        if ($this->reservation->preferred_date) {
            $dateStr = $this->reservation->preferred_date->format('d/m/Y');
            $timeStr = $this->reservation->preferred_time ? substr((string) $this->reservation->preferred_time, 0, 5) : 'Por convenir';
            $mail->line("📅 **Fecha / Hora:** {$dateStr} a las {$timeStr}");
        }

        if ($this->reservation->merchant_notes) {
            $mail->line("💬 **Mensaje del comercio:** \"{$this->reservation->merchant_notes}\"");
        }

        $mail->action('Ver Mis Reservas', route('consumer.reservations'))
            ->line('Gracias por usar FusaShop para conectar con comercios y servicios locales de Fusagasugá.');

        return $mail;
    }

    public function toArray($notifiable): array
    {
        $productName = $this->reservation->product?->name ?? 'Servicio';
        $merchantName = $this->reservation->merchant?->companyProfile?->company_name ?? $this->reservation->merchant?->name ?? 'Comercio';

        $statusMessages = [
            'confirmed' => "¡Excelente noticia! Tu reserva para \"{$productName}\" fue confirmada por {$merchantName}.",
            'rejected' => "El comercio {$merchantName} indicó que no tiene disponibilidad para \"{$productName}\" en la fecha solicitada.",
            'completed' => "Tu reserva para \"{$productName}\" ha sido completada con éxito.",
            'cancelled' => "La reserva para \"{$productName}\" fue cancelada.",
        ];

        return [
            'type' => 'reservation_status',
            'reservation_id' => $this->reservation->id,
            'reference' => $this->reservation->reference,
            'status' => $this->reservation->status,
            'title' => 'Actualización de reserva',
            'message' => $statusMessages[$this->reservation->status] ?? "El estado de tu reserva para \"{$productName}\" cambió a: {$this->reservation->status}.",
            'icon' => $this->reservation->status === 'confirmed' ? 'check_circle' : 'event_available',
            'url' => route('consumer.reservations'),
            'product_name' => $productName,
            'merchant_name' => $merchantName,
        ];
    }
}
