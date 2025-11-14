<?php

namespace App\Notifications;

use App\Models\StickerRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewStickerRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public StickerRequest $stickerRequest
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', \App\Notifications\Channels\CustomDatabaseChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = route('admin.stickers').'#request';
        $userName = $this->stickerRequest->user->first_name.' '.$this->stickerRequest->user->last_name;

        return (new MailMessage)
            ->subject("New Sticker Request #{$this->stickerRequest->id}")
            ->line('A new sticker request has been submitted and requires your review.')
            ->line("**Request ID:** {$this->stickerRequest->id}")
            ->line("**Submitted by:** {$userName}")
            ->line("**Email:** {$this->stickerRequest->user->email}")
            ->line("**Vehicle:** {$this->stickerRequest->vehicle->vehicleType->name}")
            ->line('**Plate Number:** '.($this->stickerRequest->vehicle->plate_no ?: 'No Plate'))
            ->line("**Reason:** {$this->stickerRequest->reason}")
            ->action('Review Request', $url)
            ->line('Please review and process this request promptly.');
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $userName = $this->stickerRequest->user->first_name.' '.$this->stickerRequest->user->last_name;

        return [
            'type' => 'new_sticker_request',
            'title' => "New Sticker Request #{$this->stickerRequest->id}",
            'message' => "New sticker request submitted by {$userName} for {$this->stickerRequest->vehicle->vehicleType->name}",
            'data' => [
                'request_id' => $this->stickerRequest->id,
                'user_id' => $this->stickerRequest->user_id,
                'user_name' => $userName,
                'user_email' => $this->stickerRequest->user->email,
                'vehicle_type' => $this->stickerRequest->vehicle->vehicleType->name,
                'vehicle_plate' => $this->stickerRequest->vehicle->plate_no,
                'reason' => $this->stickerRequest->reason,
                'created_at' => $this->stickerRequest->created_at->toISOString(),
                'url' => route('admin.stickers').'#request',
            ],
        ];
    }
}
