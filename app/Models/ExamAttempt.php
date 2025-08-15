<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamAttempt extends Model
{
    protected $fillable = [
        'exam_id',
        'student_id',
        'cohort_id',
        'started_at',
        'submitted_at',
        'status',
        'total_score',
        'max_score',
        'time_spent_minutes'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'answers' => 'array'
    ];

    // Relationships
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function cohort()
    {
        return $this->belongsTo(ExamCohort::class, 'cohort_id');
    }

    public function examAnswers()
    {
        return $this->hasMany(ExamAnswer::class);
    }

    /**
     * Get the grade for this exam attempt
     */
    public function grade()
    {
        return $this->morphOne(Grade::class, 'gradeable');
    }

    // Helper methods
    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

    public function isSubmitted()
    {
        return in_array($this->status, ['submitted', 'auto_submitted', 'graded']);
    }

    // Accessor for is_submitted (used in views)
    public function getIsSubmittedAttribute()
    {
        return $this->isSubmitted();
    }

    // Accessor for score percentage
    public function getScoreAttribute()
    {
        if ($this->max_score && $this->total_score !== null) {
            return round(($this->total_score / $this->max_score) * 100, 1);
        }
        return null;
    }

    // Accessor for marks_obtained (alias for total_score)
    public function getMarksObtainedAttribute()
    {
        return $this->total_score;
    }

    // Accessor for completed_at (use submitted_at)
    public function getCompletedAtAttribute()
    {
        return $this->submitted_at;
    }

    public function getRemainingTime()
    {
        if (!$this->isInProgress()) {
            return 0;
        }
        $examDuration = $this->exam->duration_minutes;
        $elapsedMinutes = $this->started_at->diffInMinutes(now());
        $remainingMinutes = $examDuration - $elapsedMinutes;
        return max(0, $remainingMinutes);
    }

    /**
     * Get remaining time in seconds (authoritative server-side countdown)
     */
    public function getRemainingSeconds()
    {
        if (!$this->isInProgress()) {
            return 0;
        }
        $examDurationSeconds = (int) $this->exam->duration_minutes * 60;
        $elapsedSeconds = $this->started_at->diffInSeconds(now());
        $remainingSeconds = $examDurationSeconds - $elapsedSeconds;
        return max(0, $remainingSeconds);
    }
}
