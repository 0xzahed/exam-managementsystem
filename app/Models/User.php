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
        'profile_photo',
        'verification_token',
        'verification_expires_at',
        'verification_code',
        'verification_code_expires_at',
        'is_verified',
        // Profile fields
        'phone',
        'department',
        'bio',
        'date_of_birth',
        'gender',
        'year_of_study',
        'email_notifications',
        'assignment_reminders',
        'title',
        'specialization',
        'office_location',
        'office_hours',
        'website',
        'linkedin',
        'education',
        'research_interests',
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
            // Profile field casts
            'date_of_birth' => 'date',
            'email_notifications' => 'boolean',
            'assignment_reminders' => 'boolean',
        ];
    }
    
    /**
     * The course enrollments for this user
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function enrollments()
    {
        return $this->hasMany(\App\Models\CourseEnrollment::class, 'user_id');
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
    
    /**
     * The announcements this user has created (for instructors)
     */
    public function announcements()
    {
        return $this->hasMany(\App\Models\Announcement::class, 'instructor_id');
    }
    
    /**
     * Get the profile photo display URL
     */
    public function getProfilePhotoDisplayUrlAttribute()
    {
        // Check for uploaded profile photo first
        if ($this->profile_photo) {
            // If it's a local file path (starts with profile_photos/)
            if (strpos($this->profile_photo, 'profile_photos/') === 0) {
                return asset('storage/' . $this->profile_photo);
            }
            
            // If it's a Google Drive URL (legacy), convert to direct image URL
            if (strpos($this->profile_photo, 'drive.google.com') !== false) {
                return $this->convertGoogleDriveUrl($this->profile_photo);
            }
            
            // If it's a full URL (other services), return as is
            if (filter_var($this->profile_photo, FILTER_VALIDATE_URL)) {
                return $this->profile_photo;
            }
            
            // If it's any other local file path
            return asset('storage/' . $this->profile_photo);
        }
        
        // Fallback to Google avatar
        if ($this->avatar) {
            // Also check if avatar is Google Drive URL
            if (strpos($this->avatar, 'drive.google.com') !== false) {
                return $this->convertGoogleDriveUrl($this->avatar);
            }
            return $this->avatar;
        }
        
        // Return default avatar or null
        return null;
    }
    
    /**
     * Convert Google Drive share URL to direct image URL
     */
    private function convertGoogleDriveUrl($url)
    {
        // Extract file ID from Google Drive URL
        preg_match('/\/file\/d\/([a-zA-Z0-9-_]+)/', $url, $matches);
        
        if (isset($matches[1])) {
            $fileId = $matches[1];
            // Return direct image URL
            return "https://drive.google.com/uc?export=view&id={$fileId}";
        }
        
        // If we can't extract ID, return original URL
        return $url;
    }
    
    /**
     * Get the user's full name
     */
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}
