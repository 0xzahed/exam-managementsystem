<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnrollmentController extends Controller
{
    /**
     * Show course enrollment page
     */
    public function showEnrollment()
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            
            if (!$user) {
                return redirect()->route('login')->with('error', 'Please login first.');
            }
            
            // Get enrolled course IDs for the student
            $enrolledCourseIds = $user->enrolledCourses()->pluck('courses.id')->toArray();
            
            // Get all available courses that student is not enrolled in
            $availableCourses = Course::where('is_active', true)
                ->whereNotIn('id', $enrolledCourseIds)
                ->with(['instructor'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($course) {
                    // Calculate available seats
                    $enrolledCount = $course->students()->count();
                    $availableSeats = $course->max_students - $enrolledCount;
                    
                    // Add calculated fields
                    $course->available_seats = max(0, $availableSeats);
                    $course->enrolled_count = $enrolledCount;
                    $course->instructor_name = $course->instructor 
                        ? $course->instructor->first_name . ' ' . $course->instructor->last_name 
                        : 'TBA';
                    
                    return $course;
                });
            
            // Calculate stats for the dashboard
            $enrolledCourses = $user->enrolledCourses()->get();
            $stats = [
                'enrolled_courses' => $enrolledCourses->count(),
                'total_credits' => $enrolledCourses->sum('credits'),
                'available_courses' => $availableCourses->count(),
                'total_courses' => Course::where('is_active', true)->count()
            ];
            
            return view('student.enroll', compact('availableCourses', 'stats'));
        } catch (\Exception $e) {
            Log::error('Error in showEnrollment method: ' . $e->getMessage());
            return redirect()->route('student.dashboard')->with('error', 'Failed to load enrollment page.');
        }
    }
    
    /**
     * Enroll student in a course
     */
    public function enrollInCourse(Request $request, Course $course)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Validate request
        $request->validate([
            'password' => 'required|string|min:1|max:255'
        ], [
            'password.required' => 'Course password is required.',
            'password.min' => 'Course password cannot be empty.',
            'password.max' => 'Course password is too long.'
        ]);
        
        try {
            DB::beginTransaction();
            
            // Check if course exists and is active
            if (!$course->exists || !$course->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'This course is not available for enrollment.'
                ], 400);
            }
            
            // Check if user is a student
            if ($user->role !== 'student') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only students can enroll in courses.'
                ], 403);
            }
            
            // Check if already enrolled
            if ($user->enrolledCourses()->where('course_id', $course->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are already enrolled in this course.'
                ], 400);
            }
            
            // Check course capacity
            $enrolledCount = $course->students()->count();
            if ($enrolledCount >= $course->max_students) {
                return response()->json([
                    'success' => false,
                    'message' => 'This course has reached its maximum capacity (' . $course->max_students . ' students).'
                ], 400);
            }
            
            // Verify course password
            if (trim($request->password) !== trim($course->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid course password. Please check with your instructor.'
                ], 400);
            }
            
            // Check for prerequisites (if any)
            if (!empty($course->prerequisites)) {
                // You can implement prerequisite checking logic here
                // For now, we'll just show a warning but allow enrollment
                Log::info("Student {$user->id} enrolling in course {$course->id} with prerequisites: {$course->prerequisites}");
            }
            
            // Enroll student
            $user->enrolledCourses()->attach($course->id, [
                'enrolled_at' => now(),
                'status' => 'enrolled',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            DB::commit();
            
            // Log successful enrollment
            Log::info("Student {$user->id} ({$user->email}) successfully enrolled in course {$course->id} ({$course->title})");
            
            return response()->json([
                'success' => true,
                'message' => "🎉 Successfully enrolled in {$course->code} - {$course->title}!",
                'course' => [
                    'id' => $course->id,
                    'title' => $course->title,
                    'code' => $course->code,
                    'credits' => $course->credits
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Enrollment failed for user {$user->id} in course {$course->id}: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your enrollment. Please try again.'
            ], 500);
        }
    }

    /**
     * Show student's enrolled courses
     */
    public function myCourses()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        try {
            // Get enrolled courses with instructor info
            $enrolledCourses = $user->enrolledCourses()
                ->with(['instructor', 'assignments'])
                ->orderBy('course_enrollments.enrolled_at', 'desc')
                ->get();
            
            // Fix date format issue by ensuring all pivot dates are Carbon instances
            $enrolledCourses = $enrolledCourses->map(function ($course) {
                $course->pivot->enrolled_at = \Carbon\Carbon::parse($course->pivot->enrolled_at);
                $course->pivot->created_at = \Carbon\Carbon::parse($course->pivot->created_at);
                $course->pivot->updated_at = \Carbon\Carbon::parse($course->pivot->updated_at);
                return $course;
            });

            // Calculate stats
            $stats = [
                'enrolledCourses' => $enrolledCourses->count(),
                'totalCredits' => $enrolledCourses->sum('credits'),
                'totalAssignments' => $enrolledCourses->sum(function($course) { 
                    return $course->assignments ? $course->assignments->count() : 0; 
                }),
                'totalExams' => 0 // Will be implemented later
            ];
            
            return view('student.my-courses', compact('enrolledCourses', 'stats'));
        } catch (\Exception $e) {
            Log::error('Error in myCourses method: ' . $e->getMessage());
            return redirect()->route('student.dashboard')->with('error', 'Failed to load enrolled courses.');
        }
    }

    /**
     * Show course details for enrolled student
     */
    public function courseDetails(Course $course)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Check if student is enrolled in this course
        if (!$user->enrolledCourses()->where('course_id', $course->id)->exists()) {
            return redirect()->route('student.courses.my')->with('error', 'You are not enrolled in this course.');
        }
        
        try {
            // Load course with all related data
            $course->load(['instructor', 'courseMaterials', 'assignments']);
            
            // Get assignments for this course
            $assignments = $course->assignments()->orderBy('due_date', 'asc')->get();
            
            return view('student.course-details', compact('course', 'assignments'));
        } catch (\Exception $e) {
            Log::error('Error in courseDetails method: ' . $e->getMessage());
            return redirect()->route('student.courses.my')->with('error', 'Failed to load course details.');
        }
    }
    
    /**
     * Unenroll from a course
     */
    public function unenrollFromCourse(Course $course)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Check if student is enrolled
        if (!$user->enrolledCourses()->where('course_id', $course->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not enrolled in this course.'
            ], 400);
        }
        
        try {
            $user->enrolledCourses()->detach($course->id);
            
            return response()->json([
                'success' => true,
                'message' => "You have successfully unenrolled from {$course->title}."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to unenroll from course. Please try again.'
            ], 500);
        }
    }
}
