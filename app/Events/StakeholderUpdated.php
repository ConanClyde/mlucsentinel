<?php

namespace App\Events;

use App\Models\Stakeholder;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StakeholderUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $stakeholder;

    public $action;

    public $editor;

    public function __construct(Stakeholder $stakeholder, string $action = 'updated', ?User $editor = null)
    {
        $this->stakeholder = $stakeholder->load(['user', 'type', 'vehicles.type']);
        $this->action = $action;
        $this->editor = $editor ?? auth()->user();
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('stakeholders'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'stakeholder.updated';
    }

    public function broadcastWith(): array
    {
        $vehicles = [];
        if ($this->stakeholder->relationLoaded('vehicles')) {
            $vehicles = $this->stakeholder->vehicles->map(function ($vehicle) {
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

        $licenseImage = $this->stakeholder->license_image;
        if ($licenseImage && ! str_starts_with($licenseImage, '/storage/') && ! str_starts_with($licenseImage, 'http')) {
            $licenseImage = '/storage/'.$licenseImage;
        }

        return [
            'stakeholder' => [
                'id' => $this->stakeholder->id,
                'user_id' => $this->stakeholder->user_id,
                'type_id' => $this->stakeholder->type_id,
                'type_name' => $this->stakeholder->type ? $this->stakeholder->type->name : null,
                'license_no' => $this->stakeholder->license_no,
                'license_image' => $licenseImage,
                'expiration_date' => $this->stakeholder->expiration_date,
                'user' => [
                    'id' => $this->stakeholder->user->id,
                    'first_name' => $this->stakeholder->user->first_name,
                    'last_name' => $this->stakeholder->user->last_name,
                    'email' => $this->stakeholder->user->email,
                    'is_active' => $this->stakeholder->user->is_active,
                ],
                'vehicles' => $vehicles,
                'created_at' => $this->stakeholder->created_at,
                'updated_at' => $this->stakeholder->updated_at,
            ],
            'action' => $this->action,
            'editor' => $this->editor ? $this->editor->first_name.' '.$this->editor->last_name : 'System',
        ];
    }
}
