<?php

namespace App\Notifications\Channels;

use App\Events\NotificationCreated;
use App\Models\Notification;
use Illuminate\Notifications\Notification as LaravelNotification;

class CustomDatabaseChannel
{
    /**
     * Send the given notification.
     */
    public function send(object $notifiable, LaravelNotification $notification): void
    {
        try {
            $data = $notification->toArray($notifiable);

            $dbNotification = Notification::create([
                'user_id' => $notifiable->id,
                'type' => $data['type'] ?? class_basename($notification),
                'title' => $data['title'] ?? 'Notification',
                'message' => $data['message'] ?? '',
                'data' => $data['data'] ?? $data,
            ]);

            // Broadcast for real-time notifications
            broadcast(new NotificationCreated($dbNotification));
        } catch (\Exception $e) {
            // Log error but don't fail the notification process
            \Log::error('Failed to save notification to database', [
                'user_id' => $notifiable->id ?? null,
                'notification' => class_basename($notification),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
