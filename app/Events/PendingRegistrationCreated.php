<?php

namespace App\Events;

use App\Models\PendingRegistration;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PendingRegistrationCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $pendingRegistration;

    /**
     * Create a new event instance.
     */
    public function __construct(PendingRegistration $pendingRegistration)
    {
        $this->pendingRegistration = $pendingRegistration->load([
            'pendingVehicles.vehicleType',
            'program',
            'stakeholderType',
            'reporterRole',
        ]);
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
        return 'pending-registration.created';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        $vehicles = $this->pendingRegistration->pendingVehicles->map(function ($vehicle) {
            return [
                'id' => $vehicle->id,
                'type_id' => $vehicle->type_id,
                'type_name' => $vehicle->vehicleType ? $vehicle->vehicleType->name : null,
                'plate_no' => $vehicle->plate_no,
            ];
        })->toArray();

        return [
            'pendingRegistration' => [
                'id' => $this->pendingRegistration->id,
                'first_name' => $this->pendingRegistration->first_name,
                'last_name' => $this->pendingRegistration->last_name,
                'email' => $this->pendingRegistration->email,
                'user_type' => $this->pendingRegistration->user_type,
                'status' => $this->pendingRegistration->status,
                'license_no' => $this->pendingRegistration->license_no,
                'license_image' => $this->pendingRegistration->license_image,
                'student_id' => $this->pendingRegistration->student_id,
                'staff_id' => $this->pendingRegistration->staff_id,
                'security_id' => $this->pendingRegistration->security_id,
                'stakeholder_type_id' => $this->pendingRegistration->stakeholder_type_id,
                'reporter_role_id' => $this->pendingRegistration->reporter_role_id,
                'program_id' => $this->pendingRegistration->program_id,
                'program' => $this->pendingRegistration->program ? [
                    'id' => $this->pendingRegistration->program->id,
                    'name' => $this->pendingRegistration->program->name,
                ] : null,
                'stakeholder_type' => $this->pendingRegistration->stakeholderType ? [
                    'id' => $this->pendingRegistration->stakeholderType->id,
                    'name' => $this->pendingRegistration->stakeholderType->name,
                ] : null,
                'reporter_role' => $this->pendingRegistration->reporterRole ? [
                    'id' => $this->pendingRegistration->reporterRole->id,
                    'name' => $this->pendingRegistration->reporterRole->name,
                ] : null,
                'pending_vehicles' => $vehicles,
                'created_at' => $this->pendingRegistration->created_at,
                'updated_at' => $this->pendingRegistration->updated_at,
            ],
        ];
    }
}
