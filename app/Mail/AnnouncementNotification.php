<?php

namespace App\Mail;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AnnouncementNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $announcement;
    public $student;

    /**
     * Create a new message instance.
     */
    public function __construct(Announcement $announcement, User $student)
    {
        $this->announcement = $announcement;
        $this->student = $student;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $priority = $this->announcement->priority === 'high' ? 'urgent' : 'normal';
        
        return new Envelope(
            subject: '📢 ' . $this->announcement->title . ' - ' . $this->announcement->course->title,
            tags: ['announcement', $this->announcement->course->title],
            metadata: [
                'announcement_id' => $this->announcement->id,
                'course_id' => $this->announcement->course_id,
                'priority' => $this->announcement->priority,
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.announcement-notification',
            with: [
                'announcement' => $this->announcement,
                'student' => $this->student,
                'course' => $this->announcement->course,
                'instructor' => $this->announcement->instructor,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
