<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Code - MLUC Sentinel</title>
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
        .code-container {
            background: #f8f9fa;
            border: 2px dashed #1b1b18;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            margin: 30px 0;
        }
        .code {
            font-size: 36px;
            font-weight: 700;
            letter-spacing: 8px;
            color: #1b1b18;
            font-family: 'Courier New', monospace;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            color: #856404;
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
            <p>Password Reset Code</p>
        </div>
        
        <div class="content">
            <h2>Password Reset Request</h2>
            <p>You have requested to reset your password for your MLUC Sentinel account. Use the code below to complete the password reset process:</p>
            
            <div class="code-container">
                <div class="code">{{ $code }}</div>
            </div>
            
            <div class="warning">
                <strong>⚠️ Important:</strong> This code will expire in 5 minutes for security reasons. If you didn't request this password reset, please ignore this email.
            </div>
            
            <p>Enter this code on the password reset page to set your new password.</p>
            
            <a href="{{ route('password.reset', ['email' => request()->email ?? '']) }}" class="btn">
                Reset Password
            </a>
        </div>
        
        <div class="footer">
            <p>This email was sent from MLUC Sentinel - A Digital Parking Management System</p>
            <p>Don Mariano Marcos Memorial State University - Mid La Union Campus</p>
            <p>If you have any questions, please contact our support team.</p>
        </div>
    </div>
</body>
</html>
