<?php

namespace App\Events;

use App\Models\Security;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SecurityUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $security;

    public $action; // 'created', 'updated', 'deleted'

    public $editor;

    /**
     * Create a new event instance.
     */
    public function __construct(Security $security, string $action = 'updated', ?User $editor = null)
    {
        $this->security = $security->load(['user', 'vehicles.type']);
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
            new Channel('security'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'security.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        $vehicles = [];
        if ($this->security->relationLoaded('vehicles')) {
            $vehicles = $this->security->vehicles->map(function ($vehicle) {
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

        $licenseImage = $this->security->license_image;
        if ($licenseImage && ! str_starts_with($licenseImage, '/storage/') && ! str_starts_with($licenseImage, 'http')) {
            $licenseImage = '/storage/'.$licenseImage;
        }

        return [
            'security' => [
                'id' => $this->security->id,
                'user_id' => $this->security->user_id,
                'security_id' => $this->security->security_id,
                'license_no' => $this->security->license_no,
                'license_image' => $licenseImage,
                'expiration_date' => $this->security->expiration_date,
                'user' => [
                    'id' => $this->security->user->id,
                    'first_name' => $this->security->user->first_name,
                    'last_name' => $this->security->user->last_name,
                    'email' => $this->security->user->email,
                    'is_active' => $this->security->user->is_active,
                ],
                'vehicles' => $vehicles,
                'created_at' => $this->security->created_at,
                'updated_at' => $this->security->updated_at,
            ],
            'action' => $this->action,
            'editor' => $this->editor ? $this->editor->first_name.' '.$this->editor->last_name : 'System',
        ];
    }
}
