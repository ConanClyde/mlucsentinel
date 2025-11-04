<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 3px solid #1b1b18;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #1b1b18;
            margin: 0 0 10px 0;
            font-size: 28px;
        }
        .header p {
            color: #706f6c;
            margin: 0;
            font-size: 14px;
        }
        .content {
            margin-bottom: 30px;
        }
        .content h2 {
            color: #1b1b18;
            font-size: 20px;
            margin-bottom: 15px;
        }
        .info-box {
            background-color: #f8f8f8;
            border-left: 4px solid #1b1b18;
            padding: 15px;
            margin: 20px 0;
        }
        .info-box p {
            margin: 8px 0;
        }
        .info-box strong {
            color: #1b1b18;
        }
        .highlight {
            background-color: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            margin: 20px 0;
        }
        .highlight p {
            margin: 5px 0;
            font-size: 16px;
        }
        .highlight .amount {
            font-size: 32px;
            font-weight: bold;
            color: #1b1b18;
        }
        .footer {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #e3e3e0;
            margin-top: 30px;
            color: #706f6c;
            font-size: 12px;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #1b1b18;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .vehicle-list {
            margin: 15px 0;
        }
        .vehicle-item {
            padding: 10px;
            background-color: #f8f8f8;
            margin: 5px 0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>MLUC Sentinel</h1>
            <p>Mariano Marcos State University - Vehicle Tracking System</p>
        </div>

        <div class="content">
            <h2>Payment Confirmation</h2>
            
            <p>Dear {{ $user->first_name }} {{ $user->last_name }},</p>
            
            <p>Thank you for your payment! Your vehicle registration sticker payment has been successfully confirmed.</p>

            <div class="highlight">
                <p>Receipt Number</p>
                <p style="font-size: 24px; font-weight: bold; color: #1b1b18;">{{ $receiptNumber }}</p>
            </div>

            <div class="info-box">
                <p><strong>Payment Details:</strong></p>
                <p><strong>Date:</strong> {{ $payment->created_at->format('F d, Y h:i A') }}</p>
                <p><strong>Amount:</strong> ₱{{ number_format($payment->amount, 2) }}</p>
                <p><strong>Vehicle Count:</strong> {{ $payment->vehicle_count }}</p>
                <p><strong>Status:</strong> <span style="color: #28a745;">Paid</span></p>
            </div>

            @if($payment->vehicle)
            <div class="info-box">
                <p><strong>Vehicle Information:</strong></p>
                <p><strong>Plate Number:</strong> {{ $payment->vehicle->plate_no ?? 'N/A' }}</p>
                <p><strong>Type:</strong> {{ $payment->vehicle->type->name ?? 'N/A' }}</p>
                <p><strong>Color:</strong> {{ ucfirst($payment->vehicle->color ?? 'N/A') }}</p>
            </div>
            @endif

            @if($payment->batchVehicles && $payment->batchVehicles->count() > 1)
            <div class="info-box">
                <p><strong>Registered Vehicles ({{ $payment->batchVehicles->count() }}):</strong></p>
                <div class="vehicle-list">
                    @foreach($payment->batchVehicles as $vehicle)
                    <div class="vehicle-item">
                        <strong>{{ $vehicle->plate_no ?? 'N/A' }}</strong> - {{ $vehicle->type->name ?? 'Vehicle' }} ({{ ucfirst($vehicle->color ?? 'N/A') }})
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <p>Your official receipt is attached to this email as a PDF document. Please keep this receipt for your records.</p>

            <p><strong>Next Steps:</strong></p>
            <ul>
                <li>Your vehicle sticker will be generated and available for download in your dashboard</li>
                <li>Print your sticker and display it prominently on your vehicle</li>
                <li>The QR code on the sticker is unique to your vehicle</li>
            </ul>
        </div>

        <div class="footer">
            <p>This is an automated email from MLUC Sentinel. Please do not reply to this message.</p>
            <p>For assistance, contact us at sentinel@mmsu.edu.ph or call (077) 600-0000</p>
            <p>&copy; {{ date('Y') }} MLUC Sentinel - Mariano Marcos State University</p>
        </div>
    </div>
</body>
</html>

