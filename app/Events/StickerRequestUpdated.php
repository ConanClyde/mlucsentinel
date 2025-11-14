<?php

namespace App\Events;

use App\Models\StickerRequest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StickerRequestUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $stickerRequest;

    /**
     * Create a new event instance.
     */
    public function __construct(StickerRequest $stickerRequest)
    {
        $this->stickerRequest = $stickerRequest->load(['user', 'vehicle.vehicleType']);
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('sticker-requests'),
            new PrivateChannel('user.'.$this->stickerRequest->user_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'sticker-request-updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->stickerRequest->id,
            'status' => $this->stickerRequest->status,
            'user' => [
                'id' => $this->stickerRequest->user->id,
                'name' => $this->stickerRequest->user->first_name.' '.$this->stickerRequest->user->last_name,
                'email' => $this->stickerRequest->user->email,
            ],
            'vehicle' => [
                'type' => $this->stickerRequest->vehicle->vehicleType->name,
                'plate_no' => $this->stickerRequest->vehicle->plate_no,
            ],
            'processed_at' => $this->stickerRequest->processed_at?->toISOString(),
            'created_at' => $this->stickerRequest->created_at->toISOString(),
            'message' => "Sticker request #{$this->stickerRequest->id} has been {$this->stickerRequest->status}",
        ];
    }
}
