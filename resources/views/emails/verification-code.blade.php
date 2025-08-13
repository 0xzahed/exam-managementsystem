<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - InsightEdu</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #4F46E5;
            padding-bottom: 20px;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #4F46E5;
            margin-bottom: 10px;
        }
        .code-container {
            background-color: #f8fafc;
            border: 2px dashed #4F46E5;
            padding: 20px;
            text-align: center;
            margin: 30px 0;
            border-radius: 8px;
        }
        .verification-code {
            font-size: 36px;
            font-weight: bold;
            color: #4F46E5;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 14px;
            color: #6b7280;
        }
        .warning {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">🎓 InsightEdu</div>
            <p style="margin: 0; color: #6b7280;">Email Verification Required</p>
        </div>

        <h2 style="color: #1f2937;">Hello {{ $user->name }}!</h2>
        
        <p>Thank you for registering with InsightEdu. To complete your account setup, please verify your email address using the verification code below:</p>

        <div class="code-container">
            <p style="margin: 0 0 10px 0; font-weight: bold; color: #374151;">Your Verification Code:</p>
            <div class="verification-code">{{ $code }}</div>
            <p style="margin: 10px 0 0 0; font-size: 12px; color: #6b7280;">This code expires in 10 minutes</p>
        </div>

        <p><strong>How to verify:</strong></p>
        <ol style="padding-left: 20px;">
            <li>Go to the verification page</li>
            <li>Enter your email address: <strong>{{ $user->email }}</strong></li>
            <li>Enter the 6-digit code above</li>
            <li>Click "Verify Account"</li>
        </ol>

        <div class="warning">
            <p style="margin: 0; font-size: 14px;"><strong>⚠️ Important:</strong> This verification code will expire in 10 minutes. If you didn't request this verification, please ignore this email.</p>
        </div>

        <p>If you're having trouble, you can request a new verification code from the verification page.</p>

        <div class="footer">
            <p>Best regards,<br>The InsightEdu Team</p>
            <p style="margin-top: 15px;">
                <strong>Daffodil International University</strong><br>
                Academic Management System
            </p>
        </div>
    </div>
</body>
</html>
