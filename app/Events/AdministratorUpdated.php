<?php

namespace App\Events;

use App\Models\Administrator;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdministratorUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $administrator;
    public $action; // 'created', 'updated', 'deleted'
    public $editor;

    /**
     * Create a new event instance.
     */
    public function __construct(Administrator $administrator, string $action = 'updated', ?string $editor = null)
    {
        $this->administrator = $administrator->load(['user', 'adminRole']);
        $this->action = $action;
        $this->editor = $editor ?? (auth()->check() ? auth()->user()->first_name . ' ' . auth()->user()->last_name : 'System');
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('administrators'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'administrator.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'administrator' => [
                'id' => $this->administrator->id,
                'user_id' => $this->administrator->user_id,
                'role_id' => $this->administrator->role_id,
                'user' => [
                    'id' => $this->administrator->user->id,
                    'first_name' => $this->administrator->user->first_name,
                    'last_name' => $this->administrator->user->last_name,
                    'email' => $this->administrator->user->email,
                    'is_active' => $this->administrator->user->is_active,
                ],
                'admin_role' => $this->administrator->adminRole ? [
                    'id' => $this->administrator->adminRole->id,
                    'name' => $this->administrator->adminRole->name,
                ] : null,
                'created_at' => $this->administrator->created_at,
                'updated_at' => $this->administrator->updated_at,
            ],
            'action' => $this->action,
            'editor' => $this->editor,
        ];
    }
}