<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - MLUC Sentinel</title>
    <style>
        body {
            font-family: 'Satoshi', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #1b1b18;
            background-color: #FDFDFC;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: #1b1b18;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }
        .content {
            padding: 40px 30px;
        }
        .success-box {
            background: #d4edda;
            border: 2px solid #28a745;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            color: #155724;
        }
        .success-box strong {
            display: block;
            font-size: 18px;
            margin-bottom: 10px;
        }
        .receipt-number {
            background: #f8f9fa;
            border: 2px dashed #1b1b18;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 30px 0;
        }
        .receipt-number .number {
            font-size: 28px;
            font-weight: 700;
            color: #1b1b18;
            margin: 10px 0;
        }
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #0066cc;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
            color: #004085;
        }
        .info-box strong {
            display: block;
            margin-bottom: 8px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid rgba(0, 102, 204, 0.2);
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            flex: 1;
        }
        .info-value {
            text-align: right;
            flex: 1;
        }
        .amount-highlight {
            background: #f8f9fa;
            border: 2px solid #1b1b18;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 30px 0;
        }
        .amount-highlight .label {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 8px;
        }
        .amount-highlight .amount {
            font-size: 32px;
            font-weight: 700;
            color: #1b1b18;
        }
        .vehicle-list {
            margin: 15px 0;
        }
        .vehicle-item {
            padding: 12px;
            background: #f8f9fa;
            margin: 8px 0;
            border-radius: 4px;
            border-left: 3px solid #0066cc;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            background: #1b1b18;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>MLUC Sentinel</h1>
            <p>Payment Receipt</p>
        </div>

        <div class="content">
            <h2>Hello {{ $user->first_name }} {{ $user->last_name }},</h2>
            
            <div class="success-box">
                <strong>✅ Payment Confirmed</strong>
                Your vehicle registration sticker payment has been successfully confirmed.
            </div>

            <div class="receipt-number">
                <div style="color: #6c757d; font-size: 14px; margin-bottom: 8px;">Receipt Number</div>
                <div class="number">{{ $receiptNumber }}</div>
            </div>
            
            <div class="amount-highlight">
                <div class="label">Total Amount Paid</div>
                <div class="amount">₱{{ number_format($payment->amount, 2) }}</div>
            </div>

            <div class="info-box">
                <strong>Payment Details</strong>
                <div class="info-row">
                    <span class="info-label">Date:</span>
                    <span class="info-value">{{ $payment->created_at->format('F d, Y h:i A') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Vehicle Count:</span>
                    <span class="info-value">{{ $payment->vehicle_count }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value" style="color: #28a745; font-weight: 700;">Paid</span>
                </div>
            </div>

            @if($payment->vehicle)
            <div class="info-box">
                <strong>Vehicle Information</strong>
                <div class="info-row">
                    <span class="info-label">Plate Number:</span>
                    <span class="info-value">{{ $payment->vehicle->plate_no ?? 'N/A' }}</span>
                </div>
                @if($payment->vehicle->type)
                <div class="info-row">
                    <span class="info-label">Type:</span>
                    <span class="info-value">{{ $payment->vehicle->type->name }}</span>
                </div>
                @endif
                @if($payment->vehicle->color)
                <div class="info-row">
                    <span class="info-label">Color:</span>
                    <span class="info-value">{{ ucfirst($payment->vehicle->color) }}</span>
                </div>
                @endif
            </div>
            @endif

            @if($payment->batchVehicles && $payment->batchVehicles->count() > 1)
            <div class="info-box">
                <strong>Registered Vehicles ({{ $payment->batchVehicles->count() }})</strong>
                <div class="vehicle-list">
                    @foreach($payment->batchVehicles as $vehicle)
                    <div class="vehicle-item">
                        <strong>{{ $vehicle->plate_no ?? 'N/A' }}</strong>
                        @if($vehicle->type)
                            - {{ $vehicle->type->name }}
                        @endif
                        @if($vehicle->color)
                            ({{ ucfirst($vehicle->color) }})
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="info-box">
                <strong>Next Steps</strong>
                <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                <li>Your vehicle sticker will be generated and available for download in your dashboard</li>
                <li>Print your sticker and display it prominently on your vehicle</li>
                <li>The QR code on the sticker is unique to your vehicle</li>
            </ul>
            </div>
            
            <p style="margin-top: 30px;">Your official receipt is attached to this email as a PDF document. Please keep this receipt for your records.</p>
            
            <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
            
            <p>Thank you for using MLUC Sentinel.</p>
        </div>

        <div class="footer">
            <p>This email was sent from MLUC Sentinel - A Digital Parking Management System</p>
            <p>Don Mariano Marcos Memorial State University - Mid La Union Campus</p>
            <p>If you have any questions, please contact our support team.</p>
            <p style="margin-top: 15px; font-size: 12px; color: #999;">
                This is an automated notification. Please do not reply to this email.
            </p>
        </div>
    </div>
</body>
</html>
