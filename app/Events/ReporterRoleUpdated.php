<?php

namespace App\Events;

use App\Models\ReporterRole;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReporterRoleUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $role;

    public $action;

    /**
     * Create a new event instance.
     */
    public function __construct(ReporterRole $role, string $action = 'updated')
    {
        $this->role = [
            'id' => $role->id,
            'name' => $role->name,
            'description' => $role->description,
            'is_active' => $role->is_active,
            'allowed_user_types' => $role->getAllowedUserTypes(),
            'reporters_count' => $role->reporters()->count(),
            'created_at' => $role->created_at,
            'updated_at' => $role->updated_at,
        ];
        $this->action = $action;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('reporter-roles'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'reporter-role.'.$this->action;
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        if ($this->action === 'deleted') {
            return [
                'roleId' => $this->role['id'],
            ];
        }

        return [
            'role' => $this->role,
        ];
    }
}
