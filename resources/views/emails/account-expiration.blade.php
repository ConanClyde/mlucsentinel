<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Expiration Notice - MLUC Sentinel</title>
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
        .warning-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            color: #856404;
        }
        .warning-box strong {
            display: block;
            font-size: 18px;
            margin-bottom: 10px;
        }
        .expiration-date {
            background: #f8f9fa;
            border: 2px dashed #1b1b18;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 30px 0;
        }
        .expiration-date .date {
            font-size: 28px;
            font-weight: 700;
            color: #dc3545;
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
            <p>Account Expiration Notice</p>
        </div>
        
        <div class="content">
            <h2>Hello {{ $user->first_name }} {{ $user->last_name }},</h2>
            
            <div class="warning-box">
                <strong>⚠️ Important Notice</strong>
                Your MLUC Sentinel account will expire in {{ $daysUntilExpiration }} {{ $daysUntilExpiration === 1 ? 'day' : 'days' }}.
            </div>
            
            <p>This is a reminder that your account access will expire on:</p>
            
            <div class="expiration-date">
                <div class="date">{{ $expirationDate->format('F d, Y') }}</div>
                <div style="color: #6c757d; font-size: 14px;">{{ $expirationDate->format('l') }}</div>
            </div>
            
            <div class="info-box">
                <strong>What happens when your account expires?</strong>
                <p style="margin: 10px 0 0 0;">Once your account expires, you will no longer be able to access the MLUC Sentinel system. To continue using the system, please contact the administrator to renew your account before the expiration date.</p>
            </div>
            
            <p><strong>Action Required:</strong></p>
            <ul>
                <li>Contact your administrator or the MLUC Sentinel support team to renew your account</li>
                <li>Ensure all pending tasks are completed before the expiration date</li>
                <li>Save any important data or information you may need</li>
            </ul>
            
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

