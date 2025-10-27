<?php

namespace App\Events;

use App\Models\Vehicle;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VehicleUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $vehicle;

    public $action;

    public $editor;

    /**
     * Create a new event instance.
     */
    public function __construct(Vehicle $vehicle, string $action = 'updated', ?string $editor = null)
    {
        $this->vehicle = $vehicle->load(['user', 'type']);
        $this->action = $action;
        $this->editor = $editor ?? (auth()->check() ? auth()->user()->first_name.' '.auth()->user()->last_name : 'System');
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('vehicles'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'vehicle.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'vehicle' => [
                'id' => $this->vehicle->id,
                'user_id' => $this->vehicle->user_id,
                'type_id' => $this->vehicle->type_id,
                'plate_no' => $this->vehicle->plate_no,
                'color' => $this->vehicle->color,
                'number' => $this->vehicle->number,
                'sticker' => $this->vehicle->sticker,
                'is_active' => $this->vehicle->is_active,
                'user' => [
                    'id' => $this->vehicle->user->id,
                    'first_name' => $this->vehicle->user->first_name,
                    'last_name' => $this->vehicle->user->last_name,
                    'email' => $this->vehicle->user->email,
                    'user_type' => $this->vehicle->user->user_type,
                ],
                'type' => $this->vehicle->type ? [
                    'id' => $this->vehicle->type->id,
                    'name' => $this->vehicle->type->name,
                ] : null,
                'created_at' => $this->vehicle->created_at,
                'updated_at' => $this->vehicle->updated_at,
            ],
            'action' => $this->action,
            'editor' => $this->editor,
        ];
    }
}
