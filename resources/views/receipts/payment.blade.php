<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - {{ $receipt_number }}</title>
    <style>
        body { 
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 20px;
            background: #ffffff;
        }
        .receipt-container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border: 2px solid #e5e7eb;
        }
        .receipt-header {
            background-color: #3b82f6;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }
        .receipt-title { 
            font-size: 20px; 
            font-weight: bold; 
            margin: 0 0 8px 0;
        }
        .receipt-subtitle { 
            font-size: 14px; 
            margin: 0;
        }
        .receipt-body { 
            padding: 20px; 
        }
        .receipt-info {
            width: 100%;
            margin-bottom: 20px;
        }
        .receipt-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .receipt-info td {
            padding: 4px 0;
            font-size: 13px;
        }
        .receipt-number { 
            font-weight: bold; 
            color: #3b82f6; 
            font-size: 14px;
        }
        .receipt-ref { 
            color: #6b7280; 
            font-size: 12px; 
        }
        .receipt-date { 
            text-align: right; 
            color: #6b7280; 
        }
        .customer-info {
            background-color: #f9fafb;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #e5e7eb;
        }
        .customer-name { 
            font-weight: bold; 
            font-size: 14px; 
            margin-bottom: 6px; 
        }
        .customer-email { 
            color: #4b5563; 
            font-size: 13px; 
            margin-bottom: 4px;
        }
        .customer-type { 
            color: #6b7280; 
            font-size: 13px; 
        }
        .items-section { 
            margin: 20px 0; 
        }
        .item-row {
            width: 100%;
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .item-row table {
            width: 100%;
            border-collapse: collapse;
        }
        .item-row td {
            padding: 4px 0;
        }
        .item-label { 
            font-weight: bold; 
            font-size: 14px;
        }
        .item-detail { 
            color: #6b7280; 
            font-size: 12px; 
        }
        .item-amount { 
            font-weight: bold; 
            text-align: right;
            font-size: 14px;
        }
        .total-section {
            background-color: #eff6ff;
            padding: 15px;
            margin: 20px 0;
            border: 1px solid #bfdbfe;
        }
        .total-row {
            width: 100%;
        }
        .total-row table {
            width: 100%;
            border-collapse: collapse;
        }
        .total-row td {
            font-size: 18px;
            font-weight: bold;
            padding: 0;
        }
        .total-amount { 
            color: #3b82f6; 
            text-align: right;
        }
        .status-section { 
            text-align: center; 
            margin: 20px 0; 
        }
        .status-badge {
            display: inline-block;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: bold;
            border: 2px solid;
        }
        .status-paid { 
            background-color: #d1fae5; 
            color: #065f46; 
            border-color: #10b981;
        }
        .status-pending { 
            background-color: #fef3c7; 
            color: #92400e; 
            border-color: #f59e0b;
        }
        .status-failed { 
            background-color: #fee2e2; 
            color: #991b1b; 
            border-color: #ef4444;
        }
        .status-cancelled { 
            background-color: #f3f4f6; 
            color: #374151; 
            border-color: #9ca3af;
        }
        .receipt-footer {
            background-color: #f9fafb;
            padding: 15px;
            text-align: center;
            font-size: 11px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
        }
        .receipt-footer p { 
            margin: 4px 0; 
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- Header -->
        <div class="receipt-header">
            <h1 class="receipt-title">{{ $organization['name'] }}</h1>
            <p class="receipt-subtitle">Payment Receipt</p>
        </div>
        
        <!-- Body -->
        <div class="receipt-body">
            <!-- Receipt Info -->
            <div class="receipt-info">
                <div>
                    <div class="receipt-number">{{ $receipt_number }}</div>
                    <div class="receipt-ref">{{ $payment->reference }}</div>
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
            
            <!-- Items -->
            <div class="items-section">
                @foreach($vehicles as $vehiclePayment)
                <div class="item-row">
                    <div>
                        <div class="item-label">Vehicle Sticker</div>
                        <div class="item-detail">
                            @if($vehiclePayment->vehicle && $vehiclePayment->vehicle->plate_no)
                                {{ $vehiclePayment->vehicle->plate_no }}
                            @elseif($vehiclePayment->vehicle)
                                {{ $vehiclePayment->vehicle->color }}-{{ $vehiclePayment->vehicle->number }}
                            @else
                                N/A
                            @endif
                        </div>
                    </div>
                    <div class="item-amount">₱{{ number_format($vehiclePayment->amount, 2) }}</div>
                </div>
                @endforeach
            </div>
            
            <!-- Total -->
            <div class="total-section">
                <div class="total-row">
                    <span>Total:</span>
                    <span class="total-amount">₱{{ number_format($total_amount, 2) }}</span>
                </div>
            </div>
            
            <!-- Status -->
            <div class="status-section">
                @if($payment->status === 'paid')
                    <div class="status-badge status-paid">✓ Paid</div>
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