<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_attempt_id',
        'exam_question_id',
        'answer_text',
        'answer_files',
        'is_correct',
        'points_awarded',
        'instructor_feedback'
    ];

    protected $casts = [
        'answer_files' => 'array',
        'is_correct' => 'boolean'
    ];

    // Relationships
    public function examAttempt()
    {
        return $this->belongsTo(ExamAttempt::class);
    }

    public function examQuestion()
    {
        return $this->belongsTo(ExamQuestion::class);
    }

    // Helper methods
    public function isGraded()
    {
        return !is_null($this->points_awarded);
    }

    public function needsManualGrading()
    {
        return $this->examQuestion->type === 'short_answer' && !$this->isGraded();
    }
}
