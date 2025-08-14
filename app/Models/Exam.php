<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Exam extends Model
{
    use HasFactory;

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

    // Helper methods
    public function isActive()
    {
        $now = Carbon::now();
        return $this->status === 'published' && 
               $now->gte($this->start_time) && $now->lte($this->end_time);
    }

    public function canStudentTake($studentId)
    {
        // Check if student has already attempted
        $attempt = $this->attempts()->where('student_id', $studentId)->first();
        if ($attempt) {
            return false;
        }

        // Check if exam is published
        if ($this->status !== 'published') {
            return false;
        }

        // Check if current time is within exam window
        $now = Carbon::now();
        if (!$now->gte($this->start_time) || !$now->lte($this->end_time)) {
            return false;
        }

        // Check if student is enrolled in the course
        $user = \App\Models\User::find($studentId);
        if (!$user || !$user->enrollments()->where('course_id', $this->course_id)->exists()) {
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
}
