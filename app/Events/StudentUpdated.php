<?php

namespace App\Events;

use App\Models\Student;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $student;

    public $action; // 'created', 'updated', 'deleted'

    public $editor;

    /**
     * Create a new event instance.
     */
    public function __construct(Student $student, string $action = 'updated', ?User $editor = null)
    {
        $this->student = $student->load(['user', 'college', 'program', 'vehicles.type']);
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
            new Channel('students'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'student.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        $vehicles = [];
        if ($this->student->relationLoaded('vehicles')) {
            $vehicles = $this->student->vehicles->map(function ($vehicle) {
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

        $licenseImage = $this->student->license_image;
        if ($licenseImage && ! str_starts_with($licenseImage, '/storage/') && ! str_starts_with($licenseImage, 'http')) {
            $licenseImage = '/storage/'.$licenseImage;
        }

        return [
            'student' => [
                'id' => $this->student->id,
                'user_id' => $this->student->user_id,
                'college_id' => $this->student->college_id,
                'program_id' => $this->student->program_id,
                'student_id' => $this->student->student_id,
                'license_no' => $this->student->license_no,
                'license_image' => $licenseImage,
                'expiration_date' => $this->student->expiration_date,
                'user' => [
                    'id' => $this->student->user->id,
                    'first_name' => $this->student->user->first_name,
                    'last_name' => $this->student->user->last_name,
                    'email' => $this->student->user->email,
                    'is_active' => $this->student->user->is_active,
                ],
                'college' => $this->student->college ? [
                    'id' => $this->student->college->id,
                    'name' => $this->student->college->name,
                ] : null,
                'program' => $this->student->program ? [
                    'id' => $this->student->program->id,
                    'name' => $this->student->program->name,
                ] : null,
                'vehicles' => $vehicles,
                'created_at' => $this->student->created_at,
                'updated_at' => $this->student->updated_at,
            ],
            'action' => $this->action,
            'editor' => $this->editor ? $this->editor->first_name.' '.$this->editor->last_name : 'System',
        ];
    }
}
