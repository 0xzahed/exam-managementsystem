<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Exam extends Model
{
    protected $fillable = [
        'title',
        'description',
        'course_id',
        'instructor_id',
        'duration_minutes',
        'start_time',
        'end_time',
        'total_points',
        'auto_grade_mcq',
        'show_results_immediately',
        'prevent_navigation',
        'shuffle_questions',
        'max_attempts',
        'passing_score',
        'attachments',
        'status'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'attachments' => 'array',
        'auto_grade_mcq' => 'boolean',
        'show_results_immediately' => 'boolean',
        'prevent_navigation' => 'boolean',
        'shuffle_questions' => 'boolean',
        'max_attempts' => 'integer',
        'passing_score' => 'integer'
    ];

    // Relationships
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function questions()
    {
        return $this->hasMany(ExamQuestion::class)->orderBy('order');
    }

    public function attempts()
    {
        return $this->hasMany(ExamAttempt::class);
    }

    /**
     * Exam Cohorts (groups of students with specific time windows)
     */
    public function cohorts()
    {
        return $this->hasMany(\App\Models\ExamCohort::class);
    }

    // Helper methods
    public function isActive()
    {
        $now = Carbon::now();
        return $this->status === 'published' && $now->gte($this->start_time) && $now->lte($this->end_time);
    }

    /**
     * Check if exam is currently available for students to start
     * This includes scheduling restrictions
     */
    public function isAvailableForStudent($studentId)
    {
        $now = Carbon::now();
        // Check exam status - must be published
        if ($this->status !== 'published') {
            return false;
        }
        // Get student-specific cohort if exists
        $studentCohort = $this->getStudentCohort($studentId);
        if ($studentCohort) {
            // Use cohort-specific times
            $startTime = $studentCohort->start_time;
            $endTime = $studentCohort->end_time;
        } else {
            // Use general exam times
            $startTime = $this->start_time;
            $endTime = $this->end_time;
        }
        // Check if current time is before start time
        if ($now->lt($startTime)) {
            return false; // Not started yet
        }
        // Check if current time is after end time
        if ($now->gt($endTime)) {
            return false; // Already ended
        }
        // Check if student is enrolled in the course
        $user = \App\Models\User::find($studentId);
        if (!$user || !$user->enrollments()->where('course_id', $this->course_id)->exists()) {
            return false;
        }
        return true;
    }

    /**
     * Get exam status for a specific student
     */
    public function getStatusForStudent($studentId)
    {
        $now = Carbon::now();
        // Check exam status - must be published
        if ($this->status !== 'published') {
            return 'draft';
        }
        // Get student-specific cohort if exists
        $studentCohort = $this->getStudentCohort($studentId);
        if ($studentCohort) {
            $startTime = $studentCohort->start_time;
            $endTime = $studentCohort->end_time;
        } else {
            $startTime = $this->start_time;
            $endTime = $this->end_time;
        }
        if ($now->lt($startTime)) {
            return 'not_started';
        } elseif ($now->gt($endTime)) {
            return 'ended';
        } else {
            return 'available';
        }
    }

    /**
     * Get time until exam starts for a student
     */
    public function getTimeUntilStart($studentId)
    {
        $now = Carbon::now();
        $studentCohort = $this->getStudentCohort($studentId);
        $startTime = $studentCohort ? $studentCohort->start_time : $this->start_time;
        if ($now->gte($startTime)) {
            return 0; // Already started or past start time
        }
        return $now->diffInSeconds($startTime);
    }

    /**
     * Get time until exam ends for a student
     */
    public function getTimeUntilEnd($studentId)
    {
        $now = Carbon::now();
        $studentCohort = $this->getStudentCohort($studentId);
        $endTime = $studentCohort ? $studentCohort->end_time : $this->end_time;
        if ($now->gte($endTime)) {
            return 0; // Already ended
        }
        return $now->diffInSeconds($endTime);
    }

    public function canStudentTake($studentId)
    {
        // First check if exam is available for the student
        if (!$this->isAvailableForStudent($studentId)) {
            return false;
        }
        // Check max attempts
        $attemptCount = $this->attempts()->where('student_id', $studentId)->count();
        if ($attemptCount >= $this->max_attempts) {
            return false;
        }
        return true;
    }

    public function getAttemptForStudent($studentId)
    {
        return $this->attempts()->where('student_id', $studentId)->first();
    }

    public function getTotalMarksAttribute()
    {
        return $this->total_points ?? 100;
    }

    public function getDurationAttribute()
    {
        return $this->duration_minutes ?? 60;
    }

    /**
     * Get the cohort for a specific student if defined
     *
     * @param int $studentId
     * @return \App\Models\ExamCohort|null
     */
    public function getStudentCohort($studentId)
    {
        return $this->cohorts()
            ->whereJsonContains('student_ids', $studentId)
            ->first();
    }
}
