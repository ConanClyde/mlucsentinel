<?php

namespace App\Notifications;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReportStatusUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Report $report,
        public string $oldStatus,
        public string $newStatus
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', \App\Notifications\Channels\CustomDatabaseChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $statusLabel = ucfirst($this->newStatus);
        $url = route('reporter.my-reports');

        return (new MailMessage)
            ->subject("Report #{$this->report->id} Status Updated")
            ->line('The status of your violation report has been updated.')
            ->line("**Report ID:** {$this->report->id}")
            ->line('**Previous Status:** '.ucfirst($this->oldStatus))
            ->line("**New Status:** {$statusLabel}")
            ->when($this->report->remarks, function ($mail) {
                return $mail->line("**Remarks:** {$this->report->remarks}");
            })
            ->action('View Report', $url)
            ->line('Thank you for contributing to campus safety!');
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $statusLabel = ucfirst($this->newStatus);

        return [
            'type' => 'report_status_updated',
            'title' => "Report #{$this->report->id} Status Updated",
            'message' => "The status of your violation report has been updated to {$statusLabel}.",
            'data' => [
                'report_id' => $this->report->id,
                'old_status' => $this->oldStatus,
                'new_status' => $this->newStatus,
                'remarks' => $this->report->remarks,
                'violator_vehicle_id' => $this->report->violator_vehicle_id,
                'violation_type' => $this->report->violationType->name ?? null,
                'url' => route('reporter.my-reports'),
            ],
        ];
    }
}
