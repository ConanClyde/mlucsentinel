<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Batch Payment Receipt - {{ $receipt_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            background: #f9fafb;
            padding: 20px;
        }
        .receipt-container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }
        .receipt-header {
            background: #3b82f6;
            color: #fff;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .receipt-title { font-size: 18px; font-weight: 700; margin-bottom: 4px; }
        .receipt-subtitle { font-size: 14px; opacity: 0.9; }
        .batch-badge {
            display: inline-block;
            background: #2563eb;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            margin-top: 8px;
        }
        .receipt-body { padding: 20px; }
        .receipt-info {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            margin-bottom: 16px;
        }
        .receipt-number { font-weight: 600; color: #3b82f6; }
        .receipt-ref { color: #6b7280; font-size: 13px; }
        .receipt-date { text-align: right; color: #6b7280; }
        .customer-info {
            background: #f9fafb;
            padding: 16px;
            border-radius: 6px;
            margin-bottom: 16px;
        }
        .customer-name { font-weight: 600; font-size: 14px; margin-bottom: 4px; }
        .customer-email { color: #4b5563; font-size: 13px; }
        .customer-type { color: #6b7280; font-size: 13px; }
        .batch-summary {
            background: #eff6ff;
            padding: 16px;
            border-radius: 6px;
            margin: 16px 0;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            text-align: center;
        }
        .summary-item { }
        .summary-value { font-size: 18px; font-weight: 700; color: #3b82f6; }
        .summary-label { font-size: 11px; color: #6b7280; margin-top: 4px; }
        .vehicles-section { margin: 16px 0; }
        .vehicles-title { font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 8px; }
        .vehicle-item {
            display: flex;
            justify-content: space-between;
            background: #f9fafb;
            padding: 10px;
            border-radius: 4px;
            font-size: 14px;
            margin-bottom: 4px;
        }
        .vehicle-amount { font-weight: 600; }
        .total-section {
            background: #eff6ff;
            padding: 16px;
            border-radius: 6px;
            margin: 16px 0;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 18px;
            font-weight: 700;
        }
        .total-amount { color: #3b82f6; }
        .status-section { text-align: center; margin: 16px 0; }
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        .status-paid { background: #d1fae5; color: #065f46; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-failed { background: #fee2e2; color: #991b1b; }
        .status-cancelled { background: #f3f4f6; color: #374151; }
        .receipt-footer {
            background: #f9fafb;
            padding: 16px;
            text-align: center;
            border-radius: 0 0 8px 8px;
            font-size: 12px;
            color: #6b7280;
        }
        .receipt-footer p { margin: 4px 0; }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- Header -->
        <div class="receipt-header">
            <h1 class="receipt-title">{{ $organization['name'] }}</h1>
            <p class="receipt-subtitle">Batch Payment Receipt</p>
            <div class="batch-badge">BATCH</div>
        </div>
        
        <!-- Body -->
        <div class="receipt-body">
            <!-- Receipt Info -->
            <div class="receipt-info">
                <div>
                    <div class="receipt-number">{{ $receipt_number }}</div>
                    <div class="receipt-ref">{{ $payment->batch_id }}</div>
                </div>
                <div class="receipt-date">
                    <div>{{ $issued_date }}</div>
                    <div>{{ $issued_time }}</div>
                </div>
            </div>
            
            <!-- Customer -->
            <div class="customer-info">
                <div class="customer-name">{{ $user->first_name }} {{ $user->last_name }}</div>
                <div class="customer-email">{{ $user->email }}</div>
                <div class="customer-type">{{ $user->user_type->label() }}</div>
            </div>
            
            <!-- Batch Summary -->
            <div class="batch-summary">
                <div class="summary-item">
                    <div class="summary-value">{{ $vehicle_count }}</div>
                    <div class="summary-label">Vehicles</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value">₱{{ number_format($unit_price, 2) }}</div>
                    <div class="summary-label">Per Vehicle</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value">₱{{ number_format($total_amount, 2) }}</div>
                    <div class="summary-label">Total</div>
                </div>
            </div>
            
            <!-- Vehicles List -->
            <div class="vehicles-section">
                <div class="vehicles-title">Vehicles:</div>
                @foreach($batch_payments as $vehiclePayment)
                <div class="vehicle-item">
                    <div>
                        @if($vehiclePayment->vehicle && $vehiclePayment->vehicle->plate_no)
                            {{ $vehiclePayment->vehicle->plate_no }}
                        @elseif($vehiclePayment->vehicle)
                            {{ $vehiclePayment->vehicle->color }}-{{ $vehiclePayment->vehicle->number }}
                        @else
                            N/A
                        @endif
                    </div>
                    <div class="vehicle-amount">₱{{ number_format($vehiclePayment->amount, 2) }}</div>
                </div>
                @endforeach
            </div>
            
            <!-- Total -->
            <div class="total-section">
                <div class="total-row">
                    <span>Total Amount:</span>
                    <span class="total-amount">₱{{ number_format($total_amount, 2) }}</span>
                </div>
            </div>
            
            <!-- Status -->
            <div class="status-section">
                @if($payment->status === 'paid')
                    <div class="status-badge status-paid">✓ Batch Paid</div>
                @elseif($payment->status === 'pending')
                    <div class="status-badge status-pending">⏱ Pending</div>
                @elseif($payment->status === 'failed')
                    <div class="status-badge status-failed">✗ Failed</div>
                @else
                    <div class="status-badge status-cancelled">⊘ Cancelled</div>
                @endif
            </div>
        </div>
        
        <!-- Footer -->
        <div class="receipt-footer">
            <p>Thank you for using MLUC Sentinel!</p>
            <p>Keep this receipt for your records.</p>
        </div>
    </div>
</body>
</html>