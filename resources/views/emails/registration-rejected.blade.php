<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Status Update - MLUC Sentinel</title>
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
        .error-box {
            background: #f8d7da;
            border: 2px solid #dc3545;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            color: #721c24;
        }
        .error-box strong {
            display: block;
            font-size: 18px;
            margin-bottom: 10px;
        }
        .rejection-reason {
            background: #f8f9fa;
            border: 2px dashed #1b1b18;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 30px 0;
        }
        .rejection-reason strong {
            display: block;
            margin-bottom: 10px;
            color: #1b1b18;
        }
        .rejection-reason .reason-text {
            color: #6c757d;
            font-size: 14px;
            margin-top: 10px;
            white-space: pre-wrap;
            text-align: left;
        }
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #0066cc;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
            color: #004085;
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
            <p>Registration Status Update</p>
        </div>
        
        <div class="content">
            <h2>Hello {{ $pendingRegistration->first_name }} {{ $pendingRegistration->last_name }},</h2>
            
            <div class="error-box">
                <strong>❌ Registration Not Approved</strong>
                We regret to inform you that your registration request could not be approved at this time.
            </div>
            
            <p>Thank you for your interest in MLUC Sentinel. Unfortunately, after reviewing your registration, we were unable to approve your request.</p>
            
            @if($pendingRegistration->rejection_reason)
            <div class="rejection-reason">
                <strong>Reason for Rejection:</strong>
                <div class="reason-text">{{ $pendingRegistration->rejection_reason }}</div>
            </div>
            @endif
            
            <div class="info-box">
                <strong>What Can You Do?</strong>
                <p style="margin: 10px 0 0 0;">If you believe this decision was made in error, or if you have additional information that may help with your registration, please contact our support team for assistance.</p>
            </div>
            
            <p>If you have any questions or need clarification about this decision, please don't hesitate to contact our support team.</p>
            
            <p>Thank you for your understanding.</p>
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
