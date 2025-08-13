<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'password',
        'role',
        'status',
        'last_login_at',
        'student_id',
        'employee_id',
        'employee_student_id',
        'google_id',
        'avatar',
        'verification_token',
        'verification_expires_at',
        'verification_code',
        'verification_code_expires_at',
        'is_verified',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'verification_expires_at' => 'datetime',
            'verification_code_expires_at' => 'datetime',
            'is_verified' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }
    
    /**
     * The courses this user is enrolled in (for students)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function enrolledCourses()
    {
        return $this->belongsToMany(Course::class, 'course_enrollments', 'user_id', 'course_id')
                    ->withPivot('enrolled_at', 'status', 'created_at', 'updated_at')
                    ->withTimestamps();
    }
    
    /**
     * The courses this user teaches (for instructors)
     */
    public function taughtCourses()
    {
        return $this->hasMany(Course::class, 'instructor_id');
    }
}
