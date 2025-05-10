<?php

namespace App\Notifications;

use App\Models\ListBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RejectCancelScheduleNotification extends Notification
{
    use Queueable;
    protected ListBooking $listBooking;
    /**
     * Create a new notification instance.
     */
    public function __construct(ListBooking $listBooking)
    {
        $this->listBooking = $listBooking;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->listBooking->id,
            'date' => $this->listBooking->date,
            'session' => $this->listBooking->session,
            'message' => 'Pesanan kamu telah ditolak untuk pembatalan',
            'type' => 'Pembatalan Jadwal'
        ];
    }
}
