<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Student Dashboard
     */
    public function dashboard()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get actual enrolled courses with proper pivot data
        $enrolledCourses = $user->enrolledCourses()
            ->with(['instructor'])
            ->withPivot(['enrolled_at', 'status', 'created_at', 'updated_at'])
            ->orderByPivot('enrolled_at', 'desc')
            ->get();
        
        // Fix pivot date formatting
        $enrolledCourses->each(function ($course) {
            if ($course->pivot->enrolled_at && is_string($course->pivot->enrolled_at)) {
                $course->pivot->enrolled_at = \Carbon\Carbon::parse($course->pivot->enrolled_at);
            }
        });
        
        // Calculate real statistics
        $totalEnrolledCourses = $enrolledCourses->count();
        $pendingAssignments = 0; // Will be implemented when assignment system is ready
        $upcomingExams = 0; // Will be implemented when exam system is ready
        
        // Get recent activities based on actual data
        $recentActivities = collect();
        if ($enrolledCourses->count() > 0) {
            $recentCourse = $enrolledCourses->first();
            $recentActivities->push((object) [
                'type' => 'enrollment',
                'message' => 'Enrolled in ' . $recentCourse->title,
                'time' => $recentCourse->pivot->enrolled_at ? $recentCourse->pivot->enrolled_at->diffForHumans() : 'Recently',
                'icon' => 'fas fa-user-plus'
            ]);
        }
        
        // Empty collections for future features
        $assignments = collect(); 
        $exams = collect(); 
        $grades = collect(); 
        $announcements = collect(); 
        
        // Calculate average grade (placeholder)
        $averageGrade = null;
        
        return view('dashboard.student', compact(
            'user',
            'enrolledCourses',
            'totalEnrolledCourses',
            'pendingAssignments',
            'upcomingExams',
            'assignments',
            'exams',
            'grades',
            'announcements',
            'averageGrade',
            'recentActivities'
        ));
    }

    /**
     * Display all students enrolled in instructor's courses
     */
    public function index()
    {
        $instructor = Auth::user();
        
        // Get instructor's courses
        $courses = Course::where('instructor_id', $instructor->id)->get();
        
        // Get all students enrolled in instructor's courses using DB query
        $studentIds = [];
        foreach ($courses as $course) {
            $courseStudentIds = DB::table('course_enrollments')
                ->where('course_id', $course->id)
                ->pluck('user_id')
                ->toArray();
            $studentIds = array_merge($studentIds, $courseStudentIds);
        }
        
        // Remove duplicates
        $studentIds = array_unique($studentIds);
        
        // Get students with their enrolled courses (only instructor's courses)
        $students = User::whereIn('id', $studentIds)
            ->paginate(12);
        
        // Calculate statistics
        $totalStudents = count($studentIds);
        $totalCourses = $courses->count();
        $totalEnrollments = 0;
        foreach ($courses as $course) {
            $totalEnrollments += DB::table('course_enrollments')
                ->where('course_id', $course->id)
                ->count();
        }
        
        return view('students.index', compact(
            'students', 
            'courses', 
            'totalStudents', 
            'totalCourses', 
            'totalEnrollments'
        ));
    }
    
    /**
     * Get student details with enrolled courses
     */
    public function show($id)
    {
        $instructor = Auth::user();
        
        // Get student
        $student = User::find($id);
        
        if (!$student) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student not found'
                ], 404);
            }
            return redirect()->route('students.index')
                           ->with('error', 'Student not found');
        }
        
        // Check if student is enrolled in any of instructor's courses using DB query
        $enrolledCourses = DB::table('course_enrollments')
            ->join('courses', 'course_enrollments.course_id', '=', 'courses.id')
            ->where('course_enrollments.user_id', $student->id)
            ->where('courses.instructor_id', $instructor->id)
            ->select('courses.*')
            ->get();
        
        if ($enrolledCourses->isEmpty()) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student not enrolled in your courses'
                ], 403);
            }
            return redirect()->route('students.index')
                           ->with('error', 'Student not enrolled in your courses');
        }
        
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'student' => $student,
                'enrolled_courses' => $enrolledCourses
            ]);
        }
        
        return view('students.show', compact('student', 'enrolledCourses'));
    }
    
    /**
     * Remove student from specific course
     */
    public function removeFromCourse(Request $request, $studentId)
    {
        $instructor = Auth::user();
        $courseId = $request->input('course_id');
        
        // Verify course belongs to instructor
        $course = Course::where('id', $courseId)
            ->where('instructor_id', $instructor->id)
            ->first();
        
        if (!$course) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found or unauthorized'
            ], 403);
        }
        
        // Remove student from course using DB query
        DB::table('course_enrollments')
            ->where('user_id', $studentId)
            ->where('course_id', $courseId)
            ->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Student removed from course successfully'
        ]);
    }
}
