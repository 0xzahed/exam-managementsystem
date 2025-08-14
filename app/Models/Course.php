<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Course extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'code',
        'description',
        'credits',
        'department',
        'semester_type',
        'year',
        'max_students',
        'prerequisites',
        'password',
        'instructor_id',
        'is_active',
        'sections',
        'google_drive_folder_id'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sections' => 'array',
    ];

    // Relationship with instructor (user)
    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    // Relationship with enrolled students
    public function students()
    {
        return $this->belongsToMany(User::class, 'course_enrollments')
                    ->withTimestamps()
                    ->withPivot(['enrolled_at', 'status']);
    }

    // Relationship with materials
    public function materials()
    {
        return $this->hasMany(Material::class);
    }

    // Relationship with course materials  
    public function courseMaterials()
    {
        return $this->hasMany(CourseMaterial::class);
    }
    
    // Relationship with assignments
    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }
    
    // Relationship with exams
    public function exams()
    {
        return $this->hasMany(Exam::class);
    }
    
    // Relationship with announcements
    public function announcements()
    {
        return $this->hasMany(Announcement::class);
    }
}
