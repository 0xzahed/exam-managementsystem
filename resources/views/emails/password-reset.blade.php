<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset - InsightEdu</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8fafc;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }
        
        .header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .header p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .content {
            padding: 40px 30px;
        }
        
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #2d3748;
        }
        
        .message {
            font-size: 16px;
            margin-bottom: 30px;
            color: #4a5568;
            line-height: 1.7;
        }
        
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        
        .reset-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .reset-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .alt-link {
            background-color: #e2e8f0;
            padding: 20px;
            border-radius: 8px;
            margin: 30px 0;
            border-left: 4px solid #667eea;
        }
        
        .alt-link p {
            font-size: 14px;
            color: #4a5568;
            margin-bottom: 10px;
        }
        
        .alt-link a {
            color: #667eea;
            word-break: break-all;
            text-decoration: none;
        }
        
        .footer {
            background-color: #f7fafc;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        
        .footer p {
            font-size: 14px;
            color: #718096;
            margin-bottom: 10px;
        }
        
        .warning {
            background-color: #fed7e2;
            border: 1px solid #f56565;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .warning p {
            font-size: 14px;
            color: #c53030;
            margin: 0;
        }
        
        @media (max-width: 600px) {
            .container {
                margin: 0 10px;
            }
            
            .header, .content, .footer {
                padding: 20px;
            }
            
            .reset-button {
                padding: 12px 25px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>InsightEdu</h1>
            <p>Educational Management System</p>
        </div>
        
        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Hello{{ $userName ? ' ' . $userName : '' }}!
            </div>
            
            <div class="message">
                You are receiving this email because we received a password reset request for your account. 
                To reset your password, please click the button below:
            </div>
            
            <div class="button-container">
                <a href="{{ $resetUrl }}" class="reset-button">Reset Password</a>
            </div>
            
            <div class="alt-link">
                <p>If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:</p>
                <a href="{{ $resetUrl }}">{{ $resetUrl }}</a>
            </div>
            
            <div class="warning">
                <p><strong>Important:</strong> This password reset link will expire in 60 minutes for security reasons. If you did not request a password reset, no further action is required.</p>
            </div>
            
            <div class="message">
                If you're having trouble or didn't request this password reset, please contact our support team.
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p>© {{ date('Y') }} InsightEdu. All rights reserved.</p>
            <p>This is an automated email. Please do not reply to this message.</p>
        </div>
    </div>
</body>
</html>
