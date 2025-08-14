<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentSubmission extends Model
{
    protected $fillable = [
        'assignment_id',
        'student_id',
        'content',
        'submission_files',
        'comments',
        'submitted_at',
        'grade',
        'feedback',
        'graded_at',
        'graded_by',
        'attempt_number',
        'file_path'
    ];

    protected $casts = [
        'submission_files' => 'array',
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the assignment this submission belongs to
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    /**
     * Get the student who made this submission
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the instructor who graded this submission
     */
    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * Get the grade for this submission
     */
    public function grade()
    {
        return $this->morphOne(Grade::class, 'gradeable');
    }

    /**
     * Check if this submission is late
     */
    public function isLate(): bool
    {
        if (!$this->submitted_at) return false;
        
        return $this->submitted_at > $this->assignment->due_date;
    }

    /**
     * Check if this submission has been graded
     */
    public function isGraded(): bool
    {
        return $this->grade !== null;
    }

    /**
     * Get percentage grade
     */
    public function getPercentageAttribute(): ?float
    {
        if ($this->grade === null) return null;
        
        return round(($this->grade / $this->assignment->marks) * 100, 1);
    }
    
    /**
     * Get the attempt number for this submission
     */
    public function getAttemptNumberAttribute(): int
    {
        return $this->attributes['attempt_number'] ?? 1;
    }
    
    /**
     * Check if this is the final attempt
     */
    public function isFinalAttempt(): bool
    {
        $maxAttempts = $this->assignment->max_attempts ?? 3;
        return $this->attempt_number >= $maxAttempts;
    }
}
