<?php

namespace App\Notifications;

use App\Models\SparingRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RejectSparingNotification extends Notification
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
        return [
            'created_by' => $this->sparingRequest->sparing->createdBy->name,
            'team' => $this->sparingRequest->sparing->createdBy->team,
            'created_at' => $this->sparingRequest->sparing->created_at,
            'schedule' => $this->sparingRequest->sparing->listBooking->formatted_date,
            'session' => $this->sparingRequest->sparing->listBooking->formatted_session,
            'field' => $this->sparingRequest->sparing->listBooking->field->name,
            'message' => 'Permintaan sparing kamu telah ditolak',
            'type' => 'Penolakan Sparing',
        ];
    }
}
