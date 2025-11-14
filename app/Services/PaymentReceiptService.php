<?php

namespace App\Services;

use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PaymentReceiptService
{
    /**
     * Generate a PDF receipt for a payment
     */
    public function generateReceipt(Payment $payment): string
    {
        // Load necessary relationships
        $payment->load(['user', 'vehicle.type', 'batchVehicles']);

        // Generate receipt data
        $receiptData = $this->prepareReceiptData($payment);

        // Generate PDF
        $pdf = Pdf::loadView('receipts.payment', $receiptData);

        // Generate filename
        $filename = $this->generateReceiptFilename($payment);

        // Store the PDF
        $path = "receipts/{$filename}";
        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }

    /**
     * Generate receipt data for the template
     */
    protected function prepareReceiptData(Payment $payment): array
    {
        $user = $payment->user;
        $vehicles = $payment->batchVehicles->count() > 0
            ? $payment->batchVehicles
            : collect([$payment->vehicle]);

        return [
            'payment' => $payment,
            'user' => $user,
            'vehicles' => $vehicles,
            'receipt_number' => $this->generateReceiptNumber($payment),
            'issued_date' => now()->format('F d, Y'),
            'issued_time' => now()->format('h:i A'),
            'total_amount' => $payment->amount,
            'vehicle_count' => $payment->vehicle_count,
            'unit_price' => \App\Models\Fee::getAmount('sticker_fee', 15.00),
            'subtotal' => $payment->vehicle_count * \App\Models\Fee::getAmount('sticker_fee', 15.00),
            'tax_rate' => 0.00, // No tax for now
            'tax_amount' => 0.00,
            'organization' => [
                'name' => 'MLUC Sentinel',
                'address' => 'Mariano Marcos State University',
                'city' => 'Laoag City, Ilocos Norte',
                'phone' => '(077) 600-0000',
                'email' => 'sentinel@mmsu.edu.ph',
            ],
        ];
    }

    /**
     * Generate a unique receipt number
     */
    protected function generateReceiptNumber(Payment $payment): string
    {
        $date = now()->format('Ymd');
        $paymentId = str_pad($payment->id, 6, '0', STR_PAD_LEFT);

        return "RCP-{$date}-{$paymentId}";
    }

    /**
     * Generate receipt filename
     */
    protected function generateReceiptFilename(Payment $payment): string
    {
        $receiptNumber = $this->generateReceiptNumber($payment);
        $userName = str_replace(' ', '_', strtolower($payment->user->first_name.'_'.$payment->user->last_name));

        return "{$receiptNumber}_{$userName}.pdf";
    }

    /**
     * Get receipt download URL
     */
    public function getReceiptUrl(Payment $payment): string
    {
        $filename = $this->generateReceiptFilename($payment);
        $path = "receipts/{$filename}";

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        // Generate receipt if it doesn't exist
        $generatedPath = $this->generateReceipt($payment);

        return Storage::disk('public')->url($generatedPath);
    }

    /**
     * Generate receipt for batch payments
     */
    public function generateBatchReceipt(Payment $payment): string
    {
        // Load all payments in the batch
        $batchPayments = Payment::where('batch_id', $payment->batch_id)
            ->with(['user', 'vehicle.type'])
            ->get();

        $payment->load(['user']);

        $receiptData = [
            'payment' => $payment,
            'user' => $payment->user,
            'batch_payments' => $batchPayments,
            'receipt_number' => $this->generateReceiptNumber($payment),
            'issued_date' => now()->format('F d, Y'),
            'issued_time' => now()->format('h:i A'),
            'total_amount' => $payment->amount,
            'vehicle_count' => $payment->vehicle_count,
            'unit_price' => \App\Models\Fee::getAmount('sticker_fee', 15.00),
            'subtotal' => $payment->vehicle_count * \App\Models\Fee::getAmount('sticker_fee', 15.00),
            'tax_rate' => 0.00,
            'tax_amount' => 0.00,
            'organization' => [
                'name' => 'MLUC Sentinel',
                'address' => 'Mariano Marcos State University',
                'city' => 'Laoag City, Ilocos Norte',
                'phone' => '(077) 600-0000',
                'email' => 'sentinel@mmsu.edu.ph',
            ],
        ];

        // Generate PDF
        $pdf = Pdf::loadView('receipts.batch-payment', $receiptData);

        // Generate filename
        $filename = $this->generateReceiptFilename($payment);

        // Store the PDF
        $path = "receipts/{$filename}";
        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }

    /**
     * Delete receipt file
     */
    public function deleteReceipt(Payment $payment): bool
    {
        $filename = $this->generateReceiptFilename($payment);
        $path = "receipts/{$filename}";

        return Storage::disk('public')->delete($path);
    }

    /**
     * Check if receipt exists
     */
    public function receiptExists(Payment $payment): bool
    {
        $filename = $this->generateReceiptFilename($payment);
        $path = "receipts/{$filename}";

        return Storage::disk('public')->exists($path);
    }
}
