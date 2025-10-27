<?php

namespace App\Events;

use App\Models\Payment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Payment $payment,
        public string $action, // 'created', 'updated', 'deleted'
        public ?string $editor = null
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('payments'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'payment.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'payment' => [
                'id' => $this->payment->id,
                'user_id' => $this->payment->user_id,
                'vehicle_id' => $this->payment->vehicle_id,
                'type' => $this->payment->type,
                'status' => $this->payment->status,
                'amount' => $this->payment->amount,
                'reference' => $this->payment->reference,
                'paid_at' => $this->payment->paid_at,
                'created_at' => $this->payment->created_at,
                'user' => $this->payment->user ? [
                    'id' => $this->payment->user->id,
                    'first_name' => $this->payment->user->first_name,
                    'last_name' => $this->payment->user->last_name,
                    'email' => $this->payment->user->email,
                    'user_type' => $this->payment->user->user_type,
                ] : null,
                'vehicle' => $this->payment->vehicle ? [
                    'id' => $this->payment->vehicle->id,
                    'plate_no' => $this->payment->vehicle->plate_no,
                    'color' => $this->payment->vehicle->color,
                    'number' => $this->payment->vehicle->number,
                    'type' => $this->payment->vehicle->type ? [
                        'id' => $this->payment->vehicle->type->id,
                        'name' => $this->payment->vehicle->type->name,
                    ] : null,
                ] : null,
            ],
            'action' => $this->action,
            'editor' => $this->editor,
        ];
    }
}
