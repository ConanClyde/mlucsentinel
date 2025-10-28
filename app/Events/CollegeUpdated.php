<?php

namespace App\Events;

use App\Models\College;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CollegeUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public College $college;

    public string $action;

    public ?string $editor;

    /**
     * Create a new event instance.
     */
    public function __construct(College $college, string $action, ?string $editor = null)
    {
        $this->college = $college;
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
            new Channel('colleges'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'college.updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'college' => $this->college,
            'action' => $this->action,
            'editor' => $this->editor,
        ];
    }
}
