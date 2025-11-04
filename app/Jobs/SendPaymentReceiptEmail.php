<?php

namespace App\Jobs;

use App\Mail\PaymentReceiptMail;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPaymentReceiptEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Payment $payment,
        public string $receiptPath
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info("Sending payment receipt email for payment {$this->payment->id}");

            // Load user relationship
            $this->payment->load('user');

            // Send email to user
            Mail::to($this->payment->user->email)
                ->send(new PaymentReceiptMail($this->payment, $this->receiptPath));

            Log::info("Payment receipt email sent successfully to {$this->payment->user->email}");
        } catch (\Exception $e) {
            Log::error("Failed to send payment receipt email for payment {$this->payment->id}: ".$e->getMessage());
            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Payment receipt email failed after {$this->tries} attempts for payment {$this->payment->id}: ".$exception->getMessage());
    }
}
