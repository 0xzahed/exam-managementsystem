<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your InsightEdu Account</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .content {
            padding: 30px 20px;
        }
        .verification-btn {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 10px;
            border-radius: 5px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎓 InsightEdu</h1>
            <p>Welcome to your learning journey!</p>
        </div>
        
        <div class="content">
            <h2>Hello {{ $user->first_name }} {{ $user->last_name }}!</h2>
            
            <p>Thank you for registering with InsightEdu. To complete your account setup and start accessing our platform, please verify your email address.</p>
            
            <div style="text-align: center;">
                <a href="{{ $verificationUrl }}" class="verification-btn">
                    ✅ Verify Email Address
                </a>
            </div>
            
            <div class="warning">
                <strong>⚠️ Important:</strong> This verification link will expire in 10 minutes for security reasons. If you don't verify within this time, you'll need to register again.
            </div>
            
            <p>If the button above doesn't work, you can copy and paste this link into your browser:</p>
            <p style="word-break: break-all; background-color: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace;">
                {{ $verificationUrl }}
            </p>
            
            <p>If you didn't create an account with InsightEdu, please ignore this email.</p>
            
            <hr style="margin: 30px 0; border: none; border-top: 1px solid #eee;">
            
            <p><strong>What's next after verification?</strong></p>
            <ul>
                <li>Access your personalized dashboard</li>
                <li>Enroll in courses (for students)</li>
                <li>Create and manage courses (for instructors)</li>
                <li>Connect with your educational community</li>
            </ul>
        </div>
        
        <div class="footer">
            <p>Best regards,<br>The InsightEdu Team</p>
            <p>This is an automated email. Please do not reply to this message.</p>
            <p>&copy; {{ date('Y') }} InsightEdu. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
