<?php

namespace App\Events;

use App\Models\Reporter;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReporterUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $reporter;
    public $action;
    public $editor;

    /**
     * Create a new event instance.
     */
    public function __construct(Reporter $reporter, string $action)
    {
        $this->reporter = $reporter;
        $this->action = $action;
        $this->editor = auth()->user() ? auth()->user()->first_name . ' ' . auth()->user()->last_name : null;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('reporters'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'reporter.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'reporter' => [
                'id' => $this->reporter->id,
                'user_id' => $this->reporter->user_id,
                'type_id' => $this->reporter->type_id,
                'expiration_date' => $this->reporter->expiration_date,
                'user' => [
                    'id' => $this->reporter->user->id,
                    'first_name' => $this->reporter->user->first_name,
                    'last_name' => $this->reporter->user->last_name,
                    'email' => $this->reporter->user->email,
                    'is_active' => $this->reporter->user->is_active,
                ],
                'reporter_type' => $this->reporter->reporterType ? [
                    'id' => $this->reporter->reporterType->id,
                    'name' => $this->reporter->reporterType->name,
                ] : null,
                'created_at' => $this->reporter->created_at,
                'updated_at' => $this->reporter->updated_at,
            ],
            'action' => $this->action,
            'editor' => $this->editor,
        ];
    }
}
