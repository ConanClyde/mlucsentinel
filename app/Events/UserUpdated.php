<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $action; // 'updated', 'password_changed', 'deleted'
    public $editor;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, string $action = 'updated', ?string $editor = null)
    {
        $this->user = $user;
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
        $channels = [];
        
        // Broadcast to administrators channel if user is an administrator
        if (in_array($this->user->user_type, ['global_administrator', 'administrator'])) {
            $channels[] = new Channel('administrators');
        }
        
        // Broadcast to reporters channel if user is a reporter
        if (in_array($this->user->user_type, ['reporter', 'security'])) {
            $channels[] = new Channel('reporters');
        }
        
        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        if (in_array($this->user->user_type, ['global_administrator', 'administrator'])) {
            return 'administrator.updated';
        } elseif (in_array($this->user->user_type, ['reporter', 'security'])) {
            return 'reporter.updated';
        }
        
        return 'user.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        $data = [
            'user' => [
                'id' => $this->user->id,
                'first_name' => $this->user->first_name,
                'last_name' => $this->user->last_name,
                'email' => $this->user->email,
                'user_type' => $this->user->user_type,
                'is_active' => $this->user->is_active,
                'created_at' => $this->user->created_at,
                'updated_at' => $this->user->updated_at,
            ],
            'action' => $this->action,
            'editor' => $this->editor,
        ];

        // Add specific data based on user type
        if (in_array($this->user->user_type, ['global_administrator', 'administrator'])) {
            $administrator = $this->user->administrator;
            if ($administrator) {
                $data['administrator'] = [
                    'id' => $administrator->id,
                    'user_id' => $administrator->user_id,
                    'role_id' => $administrator->role_id,
                    'user' => $data['user'],
                    'admin_role' => $administrator->adminRole ? [
                        'id' => $administrator->adminRole->id,
                        'name' => $administrator->adminRole->name,
                    ] : null,
                    'created_at' => $administrator->created_at,
                    'updated_at' => $administrator->updated_at,
                ];
            }
        } elseif (in_array($this->user->user_type, ['reporter', 'security'])) {
            $reporter = $this->user->reporter;
            if ($reporter) {
                $data['reporter'] = [
                    'id' => $reporter->id,
                    'user_id' => $reporter->user_id,
                    'type_id' => $reporter->type_id,
                    'expiration_date' => $reporter->expiration_date,
                    'user' => $data['user'],
                    'reporter_type' => $reporter->reporterType ? [
                        'id' => $reporter->reporterType->id,
                        'name' => $reporter->reporterType->name,
                    ] : null,
                    'created_at' => $reporter->created_at,
                    'updated_at' => $reporter->updated_at,
                ];
            }
        }

        return $data;
    }
}
