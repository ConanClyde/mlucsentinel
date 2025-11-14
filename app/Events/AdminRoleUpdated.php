<?php

namespace App\Events;

use App\Models\AdminRole;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdminRoleUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public AdminRole $role;

    public string $action;

    public ?string $editor;

    public function __construct(AdminRole $role, string $action, ?string $editor = null)
    {
        if ($role->exists) {
            $this->role = $role->load('privileges');
        } else {
            $this->role = $role;
        }
        $this->action = $action;
        $this->editor = $editor;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('admin-roles'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'admin-role.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'role' => $this->role,
            'action' => $this->action,
            'editor' => $this->editor,
        ];
    }
}
