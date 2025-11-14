<?php

namespace App\Events;

use App\Models\Program;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProgramUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Program $program;

    public string $action;

    public ?string $editor;

    /**
     * Create a new event instance.
     */
    public function __construct(Program $program, string $action, ?string $editor = null)
    {
        // Only load relationship if program still exists in database
        if ($program->exists) {
            $this->program = $program->load('college');
        } else {
            // For deleted programs, ensure college relationship is accessible
            if ($program->college) {
                $this->program = $program;
            } else {
                // If college wasn't loaded, try to load it from the array data
                $this->program = $program;
            }
        }
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
            new Channel('programs'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'program.updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'program' => $this->program,
            'action' => $this->action,
            'editor' => $this->editor,
        ];
    }
}
