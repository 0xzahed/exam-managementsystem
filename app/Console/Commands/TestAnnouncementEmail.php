<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Announcement;
use App\Models\Course;
use App\Models\User;
use App\Mail\AnnouncementNotification;
use Illuminate\Support\Facades\Mail;

class TestAnnouncementEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:announcement-email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test announcement email functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get an instructor
        $instructor = User::where('role', 'instructor')->first();
        if (!$instructor) {
            $this->error('No instructor found in database');
            return 1;
        }

        // Get a course
        $course = Course::where('instructor_id', $instructor->id)->first();
        if (!$course) {
            $this->error('No course found for instructor');
            return 1;
        }

        // Get enrolled students
        $students = $course->students;
        if ($students->isEmpty()) {
            $this->error('No students enrolled in course');
            return 1;
        }

        // Create a test announcement
        $announcement = Announcement::create([
            'title' => 'Test Announcement - Email Check',
            'content' => 'This is a test announcement to check if emails are being sent to enrolled students.',
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
            'priority' => 'medium',
            'send_email' => true,
            'is_published' => true
        ]);

        $this->info('Created test announcement: ' . $announcement->title);
        $this->info('Course: ' . $course->title);
        $this->info('Enrolled students: ' . $students->count());

        // Send emails to students
        foreach ($students as $student) {
            try {
                Mail::to($student->email)->send(new AnnouncementNotification($announcement, $student));
                $this->info('✅ Email sent to: ' . $student->email);
            } catch (\Exception $e) {
                $this->error('❌ Failed to send email to ' . $student->email . ': ' . $e->getMessage());
            }
        }

        // Update announcement as sent
        $announcement->update(['sent_at' => now()]);

        $this->info('Test completed!');
        return 0;
    }
}
