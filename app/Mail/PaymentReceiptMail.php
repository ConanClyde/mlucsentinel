<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class PaymentReceiptMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Payment $payment,
        public string $receiptPath
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment Receipt - MLUC Sentinel',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-receipt',
            with: [
                'payment' => $this->payment,
                'user' => $this->payment->user,
                'receiptNumber' => $this->generateReceiptNumber(),
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        if (Storage::disk('public')->exists($this->receiptPath)) {
            $attachments[] = Attachment::fromStorage('public/'.$this->receiptPath)
                ->as('receipt.pdf')
                ->withMime('application/pdf');
        }

        return $attachments;
    }

    /**
     * Generate receipt number for display
     */
    protected function generateReceiptNumber(): string
    {
        $date = $this->payment->created_at->format('Ymd');
        $paymentId = str_pad($this->payment->id, 6, '0', STR_PAD_LEFT);

        return "RCP-{$date}-{$paymentId}";
    }
}
