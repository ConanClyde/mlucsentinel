<?php

namespace App\Services;

use App\Events\NotificationCreated;
use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    public function notifyAdmins(string $type, string $title, string $message, array $data = [], ?int $excludeUserId = null): void
    {
        $query = User::whereIn('user_type', ['global_administrator', 'administrator']);
        if ($excludeUserId) {
            $query->where('id', '!=', $excludeUserId);
        }

        $query->get()->each(function (User $user) use ($type, $title, $message, $data) {
            // Dedupe/throttle: skip if similar notification exists in the last 60 seconds
            $recentExists = Notification::where('user_id', $user->id)
                ->where('type', $type)
                ->where('message', $message)
                ->where('created_at', '>=', now()->subSeconds(60))
                ->exists();
            if ($recentExists) {
                return;
            }

            $notification = Notification::create([
                'user_id' => $user->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data,
            ]);

            broadcast(new NotificationCreated($notification));
        });
    }

    public function notifyUsers(array $userIds, string $type, string $title, string $message, array $data = [], ?int $excludeUserId = null): void
    {
        $ids = array_unique(array_filter($userIds));
        if ($excludeUserId) {
            $ids = array_values(array_filter($ids, fn ($id) => (int) $id !== (int) $excludeUserId));
        }
        if (empty($ids)) {
            return;
        }

        User::whereIn('id', $ids)->get()->each(function (User $user) use ($type, $title, $message, $data) {
            // Dedupe/throttle: skip if similar notification exists in the last 60 seconds
            $recentExists = Notification::where('user_id', $user->id)
                ->where('type', $type)
                ->where('message', $message)
                ->where('created_at', '>=', now()->subSeconds(60))
                ->exists();
            if ($recentExists) {
                return;
            }

            $notification = Notification::create([
                'user_id' => $user->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data,
            ]);

            broadcast(new NotificationCreated($notification));
        });
    }
}
