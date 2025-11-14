<?php

namespace App\Events;

use App\Models\Fee;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FeeUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Fee $fee;

    public string $action;

    public ?string $editor;

    /**
     * Create a new event instance.
     */
    public function __construct(Fee $fee, string $action, ?string $editor = null)
    {
        $this->fee = $fee;
        $this->action = $action;
        $this->editor = $editor;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('fees'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'fee.updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'fee' => $this->fee,
            'action' => $this->action,
            'editor' => $this->editor,
        ];
    }
}
