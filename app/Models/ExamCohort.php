<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ExamCohort extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'cohort_name',
        'description',
        'start_time',
        'end_time',
        'student_ids'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'student_ids' => 'array'
    ];

    // Relationships
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function attempts()
    {
        return $this->hasMany(ExamAttempt::class, 'cohort_id');
    }

    // Helper methods
    public function isActive()
    {
        $now = Carbon::now();
        return $now->between($this->start_time, $this->end_time);
    }

    public function hasStudent($studentId)
    {
        return in_array($studentId, $this->student_ids);
    }

    public function getStudents()
    {
        return User::whereIn('id', $this->student_ids)->get();
    }
}
