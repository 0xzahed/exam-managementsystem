<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CourseMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'content',
        'section',
        'type',
        'file_path',
        'file_name',
        'file_size',
        'file_type',
        'google_drive_url',
        'is_private',
        'uploaded_by'
    ];

    protected $casts = [
        'is_private' => 'boolean',
        'file_size' => 'integer'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
