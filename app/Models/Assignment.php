<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'instructor_id',
        'course_id',
        'title',
        'assignment_type',
        'short_description',
        'instructions',
        'assign_date',
        'due_date',
        'submission_type',
        'max_attempts',
        'allowed_file_types',
        'allow_late_submission',
        'notify_on_assign',
        'marks',
        'grading_type',
        'grade_display',
        'assign_to',
        'status',
        'instructor_files',
        'limit_attempts',
        'notify_on_submission',
        'send_reminders',
        'notify_late_submission',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'assign_date' => 'datetime',
        'due_date' => 'datetime',
        'allow_late_submission' => 'boolean',
        'notify_on_assign' => 'boolean',
        'limit_attempts' => 'boolean',
        'notify_on_submission' => 'boolean',
        'send_reminders' => 'boolean',
        'notify_late_submission' => 'boolean',
        'allowed_file_types' => 'array',
        'instructor_files' => 'array',
    ];

    /**
     * Get the instructor that owns the assignment.
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    /**
     * Get the course that owns the assignment.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the submissions for the assignment.
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    /**
     * Check if the assignment is overdue.
     */
    public function isOverdue(): bool
    {
        return now() > $this->due_date;
    }

    /**
     * Check if the assignment is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Check if the assignment is a draft.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Get the formatted due date.
     */
    public function getFormattedDueDateAttribute(): string
    {
        return $this->due_date->format('M d, Y g:i A');
    }

    /**
     * Get the formatted assign date.
     */
    public function getFormattedAssignDateAttribute(): string
    {
        return $this->assign_date->format('M d, Y g:i A');
    }

    /**
     * Get the status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'published' => 'green',
            'draft' => 'yellow',
            'archived' => 'gray',
            default => 'blue'
        };
    }

    /**
     * Get the days until due date.
     */
    public function getDaysUntilDueAttribute(): int
    {
        return now()->diffInDays($this->due_date, false);
    }

    /**
     * Check if late submission is allowed and assignment is overdue.
     */
    public function canSubmitLate(): bool
    {
        return $this->allow_late_submission && $this->isOverdue();
    }

    /**
     * Get the submission count for this assignment.
     */
    public function getSubmissionCountAttribute(): int
    {
        return $this->submissions()->count();
    }

    /**
     * Scope for published assignments.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope for draft assignments.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope for assignments by instructor.
     */
    public function scopeByInstructor($query, $instructorId)
    {
        return $query->where('instructor_id', $instructorId);
    }

    /**
     * Scope for assignments by course.
     */
    public function scopeByCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }
    
    /**
     * Check if a student can submit to this assignment.
     */
    public function canStudentSubmit($studentId): bool
    {
        $submission = $this->submissions()->where('student_id', $studentId)->first();
        $currentAttempts = $submission ? ($submission->attempt_number ?? 1) : 0;
        $maxAttempts = $this->max_attempts ?? 3;
        
        return $currentAttempts < $maxAttempts;
    }
    
    /**
     * Get the current attempt number for a student.
     */
    public function getStudentAttemptNumber($studentId): int
    {
        $submission = $this->submissions()->where('student_id', $studentId)->first();
        return $submission ? ($submission->attempt_number ?? 1) : 0;
    }
    
    /**
     * Get the remaining attempts for a student.
     */
    public function getStudentRemainingAttempts($studentId): int
    {
        $currentAttempts = $this->getStudentAttemptNumber($studentId);
        $maxAttempts = $this->max_attempts ?? 3;
        return max(0, $maxAttempts - $currentAttempts);
    }
    
    /**
     * Reset attempts for a student (instructor action)
     */
    public function resetStudentAttempts($studentId): bool
    {
        $submission = $this->submissions()->where('student_id', $studentId)->first();
        if ($submission) {
            $submission->update(['attempt_number' => 0]);
            return true;
        }
        return false;
    }
    
    /**
     * Increase max attempts for the assignment
     */
    public function increaseMaxAttempts($newMaxAttempts): bool
    {
        if ($newMaxAttempts > $this->max_attempts) {
            $this->update(['max_attempts' => $newMaxAttempts]);
            return true;
        }
        return false;
    }
}
