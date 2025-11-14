<?php

namespace App\Events;

use App\Models\StakeholderType;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StakeholderTypeUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public StakeholderType $stakeholderType;

    public string $action;

    public ?string $editor;

    /**
     * Create a new event instance.
     */
    public function __construct(StakeholderType $stakeholderType, string $action, ?string $editor = null)
    {
        $this->stakeholderType = $stakeholderType;
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
            new Channel('stakeholder-types'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'stakeholder-type.updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'stakeholderType' => $this->stakeholderType,
            'action' => $this->action,
            'editor' => $this->editor,
        ];
    }
}
