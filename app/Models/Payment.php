<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'vehicle_id',
        'type',
        'status',
        'amount',
        'reference',
        'batch_id',
        'vehicle_count',
        'is_representative',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'is_representative' => 'boolean',
            'vehicle_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get all vehicles in this batch
     */
    public function batchVehicles()
    {
        return $this->hasMany(Payment::class, 'batch_id', 'batch_id')
            ->with('vehicle.type');
    }

    /**
     * Get receipt URL for this payment
     */
    public function getReceiptUrl(): string
    {
        $receiptService = new \App\Services\PaymentReceiptService;

        return $receiptService->getReceiptUrl($this);
    }

    /**
     * Check if receipt exists for this payment
     */
    public function hasReceipt(): bool
    {
        $receiptService = new \App\Services\PaymentReceiptService;

        return $receiptService->receiptExists($this);
    }

    /**
     * Scope to get only batch representative payments (main payment per batch)
     */
    public function scopeBatchRepresentative($query)
    {
        return $query->where('is_representative', true);
    }
}
