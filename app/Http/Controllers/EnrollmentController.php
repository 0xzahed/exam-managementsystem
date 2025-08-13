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
            
            // Get all available courses that student is not enrolled in
            $enrolledCourseIds = $user->enrolledCourses()->pluck('course_id')->toArray();
            
            $availableCourses = Course::where('is_active', true)
                ->whereNotIn('id', $enrolledCourseIds)
                ->with('instructor')
                ->orderBy('created_at', 'desc')
                ->get();
            
            return view('student.enroll', compact('availableCourses'));
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
        
        // Validate course password
        $request->validate([
            'password' => 'required|string'
        ]);
        
        // Check if course is active
        if (!$course->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'This course is not currently active.'
            ], 400);
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
                'message' => 'This course has reached its maximum capacity.'
            ], 400);
        }
        
        // Verify course password
        if ($request->password !== $course->password) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid course password.'
            ], 400);
        }
        
        // Enroll student
        try {
            $user->enrolledCourses()->attach($course->id, [
                'enrolled_at' => now(),
                'status' => 'enrolled'
            ]);
            
            return response()->json([
                'success' => true,
                'message' => "You have successfully enrolled in {$course->title}!"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to enroll in course. Please try again.'
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
            
            return view('student.my-courses', compact('enrolledCourses'));
        } catch (\Exception $e) {
            Log::error('Error in myCourses method: ' . $e->getMessage());
            return redirect()->route('student.dashboard')->with('error', 'Failed to load enrolled courses.');
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
