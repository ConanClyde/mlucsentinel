<?php

namespace App\Events;

use App\Models\PatrolLog;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PatrolLogCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public PatrolLog $patrolLog;

    /**
     * Create a new event instance.
     */
    public function __construct(PatrolLog $patrolLog)
    {
        $this->patrolLog = $patrolLog->load(['securityUser', 'mapLocation']);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('patrol-logs'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'patrol-log.created';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'patrolLog' => [
                'id' => $this->patrolLog->id,
                'checked_in_at' => $this->patrolLog->checked_in_at->toIso8601String(),
                'notes' => $this->patrolLog->notes,
                'security_user' => [
                    'id' => $this->patrolLog->securityUser->id,
                    'first_name' => $this->patrolLog->securityUser->first_name,
                    'last_name' => $this->patrolLog->securityUser->last_name,
                    'email' => $this->patrolLog->securityUser->email,
                ],
                'map_location' => [
                    'id' => $this->patrolLog->mapLocation->id,
                    'name' => $this->patrolLog->mapLocation->name,
                    'short_code' => $this->patrolLog->mapLocation->short_code,
                ],
            ],
        ];
    }
}
