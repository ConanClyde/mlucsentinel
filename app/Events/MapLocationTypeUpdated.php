<?php

namespace App\Events;

use App\Models\MapLocationType;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MapLocationTypeUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public MapLocationType $locationType;

    public string $action;

    public ?string $editor;

    /**
     * Create a new event instance.
     */
    public function __construct(MapLocationType $locationType, string $action, ?string $editor = null)
    {
        $this->locationType = $locationType;
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
            new Channel('map-location-types'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'map-location-type.updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'locationType' => $this->locationType,
            'action' => $this->action,
            'editor' => $this->editor,
        ];
    }
}
