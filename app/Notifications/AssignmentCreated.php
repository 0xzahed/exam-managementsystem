<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class AssignmentCreated extends Notification
{
    use Queueable;

    protected $assignment;

    /**
     * Create a new notification instance.
     */
    public function __construct($assignment)
    {
        $this->assignment = $assignment;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
    return (new MailMessage)
            ->subject("New Assignment: {$this->assignment->title}")
            ->greeting('Hello ' . $notifiable->first_name . ',')
            ->line("A new assignment has been published in your course: {$this->assignment->course->title}.")
            ->line("Assignment: {$this->assignment->title}")
            ->line('Due Date: ' . $this->assignment->due_date->format('M d, Y'))
            ->action('View Assignment', route('assignments.show', $this->assignment->id))
            ->line('Thank you for using InsightEdu!');
    }

    /**
     * Get the array representation of the notification for database.
     */
    public function toArray($notifiable)
    {
        return [
            'assignment_id' => $this->assignment->id,
            'title' => $this->assignment->title,
            'course' => $this->assignment->course->title,
            'due_date' => $this->assignment->due_date->format('M d, Y'),
            'message' => 'New assignment published'
        ];
    }
}
