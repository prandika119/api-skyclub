<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Models\ListBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SuccessBookingNotification extends Notification
{
    use Queueable;
    private Booking $booking;
    private $listBooking;
    private int $total;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
        $this->listBooking = $booking->listBooking;
        $this->total = 0;

        foreach ($this->listBooking as $item) {
            $this->total += $item->price;
        }
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
            'booking_id' => $this->booking->id,
            'schedules' => $this->listBooking,
            'total' => $this->total,
            'payment_date' => $this->booking->created_at->format('d M Y H:i'),
            'message' => 'Pesanan kamu telah diterima',
            'type' => 'Pesanan Berhasil'
        ];
    }
}
