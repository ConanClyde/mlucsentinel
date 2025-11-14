<?php

namespace App\Notifications;

use App\Models\StickerRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StickerRequestStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public StickerRequest $stickerRequest,
        public string $oldStatus,
        public string $newStatus
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
        $statusLabel = ucfirst($this->newStatus);
        $url = route('user.requests');

        $message = (new MailMessage)
            ->subject("Sticker Request #{$this->stickerRequest->id} Status Updated")
            ->line('The status of your sticker request has been updated.')
            ->line("**Request ID:** {$this->stickerRequest->id}")
            ->line("**Vehicle:** {$this->stickerRequest->vehicle->vehicleType->name}")
            ->line('**Previous Status:** '.ucfirst($this->oldStatus))
            ->line("**New Status:** {$statusLabel}");

        if ($this->stickerRequest->admin_notes) {
            $message->line("**Admin Notes:** {$this->stickerRequest->admin_notes}");
        }

        return $message
            ->action('View Request', $url)
            ->line('Thank you for using MLUC Sentinel!');
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'sticker_request_status',
            'title' => "Sticker Request #{$this->stickerRequest->id} {$this->newStatus}",
            'message' => "Your sticker request for {$this->stickerRequest->vehicle->vehicleType->name} has been {$this->newStatus}",
            'data' => [
                'request_id' => $this->stickerRequest->id,
                'old_status' => $this->oldStatus,
                'new_status' => $this->newStatus,
                'vehicle_type' => $this->stickerRequest->vehicle->vehicleType->name,
                'vehicle_plate' => $this->stickerRequest->vehicle->plate_no,
                'admin_notes' => $this->stickerRequest->admin_notes,
                'processed_at' => $this->stickerRequest->processed_at?->toISOString(),
                'url' => route('user.requests'),
            ],
        ];
    }
}
