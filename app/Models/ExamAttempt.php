<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'student_id',
        'cohort_id',
        'started_at',
        'submitted_at',
        'time_spent_minutes',
        'total_score',
        'max_score',
        'status',
        'answers'
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
}
