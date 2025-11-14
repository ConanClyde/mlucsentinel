<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountExpirationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $user,
        public \Carbon\Carbon $expirationDate
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'MLUC Sentinel - Account Expiration Notice',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $today = now()->startOfDay();
        $expiration = $this->expirationDate->copy()->startOfDay();
        $daysUntilExpiration = (int) $today->diffInDays($expiration, false);

        return new Content(
            view: 'emails.account-expiration',
            with: [
                'user' => $this->user,
                'expirationDate' => $this->expirationDate,
                'daysUntilExpiration' => $daysUntilExpiration,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
