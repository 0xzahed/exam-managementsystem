<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $fillable = [
        'course_id',
        'title',
        'description',
        'file_name',
        'file_url',
        'file_path',
        'google_drive_id',
        'file_size',
        'mime_type',
        'type',
        'section',
        'uploaded_by'
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    // Relationship with course
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // Relationship with uploader (user)
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Get file size in human readable format
    public function getFileSizeHumanAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
