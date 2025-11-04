<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Violation Report Approved - MLUC Sentinel</title>
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
            background: #dc2626;
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
        .alert-box {
            background: #fef2f2;
            border-left: 4px solid #dc2626;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
            color: #991b1b;
        }
        .alert-box strong {
            display: block;
            margin-bottom: 8px;
            font-size: 16px;
        }
        .info-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #495057;
            flex: 1;
        }
        .info-value {
            color: #1b1b18;
            flex: 1;
            text-align: right;
        }
        .description-box {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            color: #495057;
        }
        .action-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 20px;
            margin: 30px 0;
            color: #856404;
        }
        .action-box strong {
            display: block;
            margin-bottom: 10px;
            font-size: 16px;
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
        h2 {
            color: #1b1b18;
            margin-top: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>MLUC Sentinel</h1>
            <p>Violation Report Approved</p>
        </div>
        
        <div class="content">
            <h2>Violation Report Approved</h2>
            
            <div class="alert-box">
                <strong>Action Required</strong>
                A violation report against your vehicle has been approved and requires your attention.
            </div>
            
            <div class="info-section">
                <div class="info-row">
                    <span class="info-label">Report ID:</span>
                    <span class="info-value"><strong>#{{ $report->id }}</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Violation Type:</span>
                    <span class="info-value">{{ $report->violationType->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Location:</span>
                    <span class="info-value">{{ $report->location }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date Reported:</span>
                    <span class="info-value">{{ $report->reported_at->format('F d, Y h:i A') }}</span>
                </div>
                @if($report->violatorVehicle)
                <div class="info-row">
                    <span class="info-label">Vehicle:</span>
                    <span class="info-value">
                        @if($report->violatorVehicle->plate_no)
                            {{ $report->violatorVehicle->plate_no }}
                        @else
                            {{ ucfirst($report->violatorVehicle->color) }} - {{ $report->violatorVehicle->number }}
                        @endif
                    </span>
                </div>
                @endif
                @if($report->violatorVehicle && $report->violatorVehicle->type)
                <div class="info-row">
                    <span class="info-label">Vehicle Type:</span>
                    <span class="info-value">{{ $report->violatorVehicle->type->name }}</span>
                </div>
                @endif
            </div>
            
            @if($report->description)
            <div class="description-box">
                <strong>Description:</strong>
                <p style="margin: 10px 0 0 0;">{{ $report->description }}</p>
            </div>
            @endif
            
            @if($report->remarks)
            <div class="description-box">
                <strong>Administrator Remarks:</strong>
                <p style="margin: 10px 0 0 0;">{{ $report->remarks }}</p>
            </div>
            @endif
            
            <div class="action-box">
                <strong>Required Action</strong>
                <p style="margin: 10px 0 0 0;">Please address this violation at your earliest convenience. Failure to address this violation may result in further actions.</p>
            </div>
            
            <p style="margin-top: 30px;">For questions or concerns regarding this violation report, please contact the administration office.</p>
            
            <p style="margin-top: 30px; color: #6c757d; font-size: 14px;">Thank you for your attention to this matter. We appreciate your cooperation in maintaining campus safety and order.</p>
        </div>
        
        <div class="footer">
            <p><strong>MLUC Sentinel</strong></p>
            <p>A Digital Parking Management System</p>
            <p>Don Mariano Marcos Memorial State University - Mid La Union Campus</p>
        </div>
    </div>
</body>
</html>

