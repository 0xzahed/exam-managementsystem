<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'type',
        'question',
        'options',
        'correct_answer',
        'points',
        'order',
        'required'
    ];

    protected $casts = [
        'options' => 'array',
        'required' => 'boolean'
    ];

    // Relationships
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function answers()
    {
        return $this->hasMany(ExamAnswer::class);
    }

    // Helper methods
    public function isMcq()
    {
        return $this->type === 'mcq';
    }

    public function isShortAnswer()
    {
        return $this->type === 'short_answer';
    }

    public function isFileUpload()
    {
        return $this->type === 'file_upload';
    }
}
