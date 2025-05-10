<?php

namespace App\Notifications;

use App\Models\SparingRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequestSparingNotification extends Notification
{
    use Queueable;
    public SparingRequest $sparingRequest;
    /**
     * Create a new notification instance.
     */
    public function __construct(SparingRequest $sparingRequest)
    {
        $this->sparingRequest = $sparingRequest;
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
//            'schedule' => $this->sparingRequest->sparing->listBooking->date,
//            'session' => $this->sparingRequest->sparing->listBooking->session,
        return [
            'created_at' =>  $this->sparingRequest->created_at,
            'message' => 'Terdapat permintaan sparing oleh ' . $this->sparingRequest->user->name . ' yang belum direspon',
            'type' => "Pengajuan Sparing",
        ];
    }
}
