<?php

namespace App\Observers;

use App\Models\Payment;
use Illuminate\Support\Facades\Cache;

class PaymentObserver
{
    public function created(Payment $payment): void
    {
        Cache::forget('payments.pending.count');
    }

    public function updated(Payment $payment): void
    {
        Cache::forget('payments.pending.count');
    }

    public function deleted(Payment $payment): void
    {
        Cache::forget('payments.pending.count');
    }

    public function restored(Payment $payment): void
    {
        Cache::forget('payments.pending.count');
    }

    public function forceDeleted(Payment $payment): void
    {
        Cache::forget('payments.pending.count');
    }
}
