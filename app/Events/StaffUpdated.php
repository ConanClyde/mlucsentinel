<?php

namespace App\Events;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StaffUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $staff;

    public $action; // 'created', 'updated', 'deleted'

    public $editor;

    /**
     * Create a new event instance.
     */
    public function __construct(Staff $staff, string $action = 'updated', ?User $editor = null)
    {
        $this->staff = $staff->load(['user', 'vehicles.type']);
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
            new Channel('staff'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'staff.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        $vehicles = [];
        if ($this->staff->relationLoaded('vehicles')) {
            $vehicles = $this->staff->vehicles->map(function ($vehicle) {
                $stickerImage = $vehicle->sticker;
                if ($stickerImage && ! str_starts_with($stickerImage, '/storage/') && ! str_starts_with($stickerImage, 'http')) {
                    $stickerImage = '/storage/'.$stickerImage;
                }

                return [
                    'id' => $vehicle->id,
                    'type_id' => $vehicle->type_id,
                    'type_name' => $vehicle->type ? $vehicle->type->name : null,
                    'plate_no' => $vehicle->plate_no,
                    'color' => $vehicle->color,
                    'number' => $vehicle->number,
                    'sticker_image' => $stickerImage,
                ];
            })->toArray();
        }

        $licenseImage = $this->staff->license_image;
        if ($licenseImage && ! str_starts_with($licenseImage, '/storage/') && ! str_starts_with($licenseImage, 'http')) {
            $licenseImage = '/storage/'.$licenseImage;
        }

        return [
            'staff' => [
                'id' => $this->staff->id,
                'user_id' => $this->staff->user_id,
                'staff_id' => $this->staff->staff_id,
                'license_no' => $this->staff->license_no,
                'license_image' => $licenseImage,
                'expiration_date' => $this->staff->expiration_date,
                'user' => [
                    'id' => $this->staff->user->id,
                    'first_name' => $this->staff->user->first_name,
                    'last_name' => $this->staff->user->last_name,
                    'email' => $this->staff->user->email,
                    'is_active' => $this->staff->user->is_active,
                ],
                'vehicles' => $vehicles,
                'created_at' => $this->staff->created_at,
                'updated_at' => $this->staff->updated_at,
            ],
            'action' => $this->action,
            'editor' => $this->editor ? $this->editor->first_name.' '.$this->editor->last_name : 'System',
        ];
    }
}
