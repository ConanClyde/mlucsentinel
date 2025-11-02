<?php

namespace App\Notifications;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ViolationApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Report $report
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Load necessary relationships if not already loaded
        $this->report->loadMissing(['violationType', 'violatorVehicle.type']);

        return (new MailMessage)
            ->subject('Violation Report Approved - Action Required')
            ->view('emails.violation-approved', [
                'report' => $this->report,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'report_id' => $this->report->id,
            'violation_type' => $this->report->violationType->name ?? 'Unknown',
            'title' => 'Violation Report Approved',
            'message' => "Your vehicle has been cited for {$this->report->violationType->name}. Please take necessary action.",
            'url' => route('home'),
        ];
    }
}
