<?php

namespace App\Events;

use App\Models\VehicleType;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VehicleTypeUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public VehicleType $vehicleType;

    public string $action;

    public ?string $editor;

    /**
     * Create a new event instance.
     */
    public function __construct(VehicleType $vehicleType, string $action, ?string $editor = null)
    {
        $this->vehicleType = $vehicleType;
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
            new Channel('vehicle-types'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'vehicle-type.updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'vehicleType' => $this->vehicleType,
            'action' => $this->action,
            'editor' => $this->editor,
        ];
    }
}
