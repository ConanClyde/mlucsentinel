<?php

namespace App\Events;

use App\Models\MapLocation;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MapLocationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $location;

    public $action; // 'created', 'updated', 'deleted'

    public $editor;

    /**
     * Create a new event instance.
     */
    public function __construct(MapLocation $location, string $action = 'updated', ?User $editor = null)
    {
        $this->location = $location->load('type');
        $this->action = $action;
        $this->editor = $editor ?? auth()->user();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('map-locations'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'location.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'location' => [
                'id' => $this->location->id,
                'type_id' => $this->location->type_id,
                'name' => $this->location->name,
                'short_code' => $this->location->short_code,
                'description' => $this->location->description,
                'color' => $this->location->color,
                'vertices' => $this->location->vertices,
                'center_x' => $this->location->center_x,
                'center_y' => $this->location->center_y,
                'is_active' => $this->location->is_active,
                'display_order' => $this->location->display_order,
                'sticker_path' => $this->location->sticker_path,
                'created_at' => $this->location->created_at?->toISOString(),
                'updated_at' => $this->location->updated_at?->toISOString(),
                'type' => [
                    'id' => $this->location->type->id,
                    'name' => $this->location->type->name,
                    'default_color' => $this->location->type->default_color,
                ],
            ],
            'action' => $this->action,
            'editor' => $this->editor ? [
                'id' => $this->editor->id,
                'name' => $this->editor->first_name.' '.$this->editor->last_name,
            ] : null,
        ];
    }
}
