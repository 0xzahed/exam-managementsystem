<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'course_id',
        'instructor_id',
        'gradeable_type',
        'gradeable_id',
        'score',
        'points_earned',
        'points_possible',
        'total_points',
        'percentage',
        'letter_grade',
        'feedback',
        'graded_at',
        'graded_by',
        'grade_type'
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'points_earned' => 'integer',
        'total_points' => 'integer',
        'graded_at' => 'datetime'
    ];

    /**
     * Get the student who received this grade
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the course this grade belongs to
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the instructor who gave this grade
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    /**
     * Get the parent gradeable model (assignment or exam)
     */
    public function gradeable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Calculate letter grade based on percentage
     */
    public function calculateLetterGrade(): string
    {
        if ($this->score === null) return 'N/A';
        
        if ($this->score >= 93) return 'A';
        if ($this->score >= 90) return 'A-';
        if ($this->score >= 87) return 'B+';
        if ($this->score >= 83) return 'B';
        if ($this->score >= 80) return 'B-';
        if ($this->score >= 77) return 'C+';
        if ($this->score >= 73) return 'C';
        if ($this->score >= 70) return 'C-';
        if ($this->score >= 67) return 'D+';
        if ($this->score >= 63) return 'D';
        if ($this->score >= 60) return 'D-';
        return 'F';
    }

    /**
     * Calculate percentage score from points
     */
    public function calculatePercentage(): float
    {
        if ($this->total_points && $this->points_earned !== null) {
            return round(($this->points_earned / $this->total_points) * 100, 2);
        }
        return $this->score ?? 0;
    }

    /**
     * Check if grade is passing
     */
    public function isPassing(): bool
    {
        return $this->score >= 60;
    }

    /**
     * Get grade color for UI
     */
    public function getGradeColor(): string
    {
        if ($this->score === null) return 'gray';
        
        if ($this->score >= 90) return 'green';
        if ($this->score >= 80) return 'blue';
        if ($this->score >= 70) return 'yellow';
        if ($this->score >= 60) return 'orange';
        return 'red';
    }

    /**
     * Scope for course grades
     */
    public function scopeForCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    /**
     * Scope for student grades
     */
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope for instructor grades
     */
    public function scopeForInstructor($query, $instructorId)
    {
        return $query->where('instructor_id', $instructorId);
    }
}
