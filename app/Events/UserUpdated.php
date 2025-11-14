<?php

namespace App\Events;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
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
        $this->editor = $editor ?? (auth()->check() ? auth()->user()->first_name.' '.auth()->user()->last_name : 'System');
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
        if (in_array($this->user->user_type, [UserType::GlobalAdministrator, UserType::Administrator])) {
            $channels[] = new Channel('administrators');
        }

        // Broadcast to reporters channel if user is a reporter
        if (in_array($this->user->user_type, [UserType::Reporter, UserType::Security])) {
            $channels[] = new Channel('reporters');
        }

        // Broadcast to students channel if user is a student
        if ($this->user->user_type === UserType::Student) {
            $channels[] = new Channel('students');
        }

        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        if (in_array($this->user->user_type, [UserType::GlobalAdministrator, UserType::Administrator])) {
            return 'administrator.updated';
        } elseif (in_array($this->user->user_type, [UserType::Reporter, UserType::Security])) {
            return 'reporter.updated';
        } elseif ($this->user->user_type === UserType::Student) {
            return 'student.updated';
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
        if (in_array($this->user->user_type, [UserType::GlobalAdministrator, UserType::Administrator])) {
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
        } elseif (in_array($this->user->user_type, [UserType::Reporter, UserType::Security])) {
            $reporter = $this->user->reporter;
            if ($reporter) {
                $data['reporter'] = [
                    'id' => $reporter->id,
                    'user_id' => $reporter->user_id,
                    'reporter_role_id' => $reporter->reporter_role_id,
                    'user' => $data['user'],
                    'reporter_role' => $reporter->reporterRole ? [
                        'id' => $reporter->reporterRole->id,
                        'name' => $reporter->reporterRole->name,
                    ] : null,
                    'created_at' => $reporter->created_at,
                    'updated_at' => $reporter->updated_at,
                ];
            }
        } elseif ($this->user->user_type === UserType::Student) {
            $student = $this->user->student;
            if ($student) {
                $data['student'] = [
                    'id' => $student->id,
                    'user_id' => $student->user_id,
                    'college_id' => $student->college_id,
                    'student_id' => $student->student_id,
                    'license_no' => $student->license_no,
                    'license_image' => $student->license_image,
                    'expiration_date' => $student->expiration_date,
                    'user' => $data['user'],
                    'college' => $student->college ? [
                        'id' => $student->college->id,
                        'name' => $student->college->name,
                    ] : null,
                    'created_at' => $student->created_at,
                    'updated_at' => $student->updated_at,
                ];
            }
        }

        return $data;
    }
}
