<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Announcement</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }
        .priority-high {
            background-color: #dc3545;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 15px;
        }
        .priority-medium {
            background-color: #ffc107;
            color: #212529;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 15px;
        }
        .priority-low {
            background-color: #28a745;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 15px;
        }
        .title {
            font-size: 20px;
            font-weight: bold;
            color: #212529;
            margin-bottom: 15px;
        }
        .course-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
        .content {
            background-color: #ffffff;
            padding: 20px;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            margin-bottom: 25px;
        }
        .footer {
            text-align: center;
            color: #6c757d;
            font-size: 14px;
            border-top: 1px solid #e9ecef;
            padding-top: 20px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            margin-top: 20px;
        }
        .meta {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">InsightEdu</div>
            <p>Course Announcement</p>
        </div>

        <div class="meta">
            <p><strong>Hello {{ $student->first_name }} {{ $student->last_name }},</strong></p>
            <p>You have a new announcement from your course instructor.</p>
        </div>

        @if($announcement->priority === 'high')
            <div class="priority-high">🚨 HIGH PRIORITY</div>
        @elseif($announcement->priority === 'medium')
            <div class="priority-medium">⚠️ MEDIUM PRIORITY</div>
        @else
            <div class="priority-low">ℹ️ LOW PRIORITY</div>
        @endif

        <div class="course-info">
            <strong>Course:</strong> {{ $course->title }}<br>
            <strong>Instructor:</strong> {{ $instructor->first_name }} {{ $instructor->last_name }}<br>
            <strong>Date:</strong> {{ $announcement->created_at->format('F d, Y \a\t g:i A') }}
        </div>

        <div class="title">{{ $announcement->title }}</div>

        <div class="content">
            {!! $announcement->content !!}
        </div>

        <div style="text-align: center;">
            <a href="{{ url('/student/dashboard') }}" class="btn">
                View in Dashboard
            </a>
        </div>

        <div class="footer">
            <p>This is an automated notification from InsightEdu.</p>
            <p>If you have any questions, please contact your course instructor.</p>
            <p>&copy; {{ date('Y') }} InsightEdu. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
