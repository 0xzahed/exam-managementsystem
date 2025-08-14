<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Course;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Announcement;
use App\Models\Grade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
            ->with(['instructor', 'assignments', 'exams'])
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
        
        // Get real exams for enrolled courses
        $enrolledCourseIds = $enrolledCourses->pluck('id');
        $allExams = collect();
        $upcomingExams = 0;
        
        if ($enrolledCourseIds->isNotEmpty()) {
            $allExams = Exam::whereIn('course_id', $enrolledCourseIds)
                ->where('status', 'published')
                ->with(['course'])
                ->get();
                
            // All published exams are available to enrolled students
            $availableExams = $allExams;
            
            $upcomingExams = $availableExams->filter(function($exam) {
                return $exam->isActive() || $exam->start_time > now();
            })->count();
        }
        
        // Get pending assignments
        $pendingAssignments = 0;
        $assignments = collect();
        if ($enrolledCourseIds->isNotEmpty()) {
            $assignments = Assignment::whereIn('course_id', $enrolledCourseIds)
                ->where('status', 'published')
                ->where('due_date', '>=', now())
                ->with(['course', 'submissions'])
                ->get();
                
            $pendingAssignments = $assignments->filter(function($assignment) use ($user) {
                $submission = $assignment->submissions()->where('student_id', $user->id)->first();
                return !$submission || $submission->status !== 'submitted';
            })->count();
        }
        
        // Get recent activities based on actual data
        $recentActivities = collect();
        
        // Add recent enrollments
        if ($enrolledCourses->count() > 0) {
            $recentCourse = $enrolledCourses->first();
            $recentActivities->push((object) [
                'type' => 'enrollment',
                'message' => 'Enrolled in ' . $recentCourse->title,
                'time' => $recentCourse->pivot->enrolled_at ? $recentCourse->pivot->enrolled_at->diffForHumans() : 'Recently',
                'icon' => 'fas fa-user-plus',
                'color' => 'text-blue-600'
            ]);
        }
        
        // Add recent exam attempts
        $recentExamAttempts = ExamAttempt::where('student_id', $user->id)
            ->with(['exam.course'])
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();
            
        foreach ($recentExamAttempts as $attempt) {
            $recentActivities->push((object) [
                'type' => 'exam',
                'message' => 'Completed ' . $attempt->exam->title,
                'time' => $attempt->created_at->diffForHumans(),
                'icon' => 'fas fa-clipboard-check',
                'color' => 'text-green-600'
            ]);
        }
        
        // Add recent assignment submissions
        $recentSubmissions = AssignmentSubmission::where('student_id', $user->id)
            ->with(['assignment.course'])
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();
            
        foreach ($recentSubmissions as $submission) {
            $recentActivities->push((object) [
                'type' => 'assignment',
                'message' => 'Submitted ' . $submission->assignment->title,
                'time' => $submission->created_at->diffForHumans(),
                'icon' => 'fas fa-upload',
                'color' => 'text-purple-600'
            ]);
        }
        
        // Sort activities by time and take recent ones
        $recentActivities = $recentActivities->sortByDesc('time')->take(5);
        
        // Real exams data for dashboard display
        $exams = collect();
        if (isset($availableExams) && $availableExams->isNotEmpty()) {
            $exams = $availableExams->take(3)->map(function($exam) use ($user) {
                $attempt = $exam->attempts()->where('student_id', $user->id)->first();
                $isCompleted = $attempt && $attempt->status !== 'in_progress';
                
                return [
                    'id' => $exam->id,
                    'title' => $exam->title,
                    'course' => $exam->course->title ?? 'General',
                    'duration' => $exam->duration ? $exam->duration . ' minutes' : '60 minutes',
                    'type' => $exam->questions->count() . ' Questions',
                    'start_time' => $exam->start_time,
                    'status' => $isCompleted ? 'Completed' : 'Pending',
                    'badge' => $isCompleted ? 'Completed' : ($exam->isActive() ? 'Active' : 'Upcoming'),
                    'badge_bg' => $isCompleted ? 'bg-green-600' : ($exam->isActive() ? 'bg-blue-600' : 'bg-yellow-600'),
                    'bg_from' => $isCompleted ? 'from-green-50' : ($exam->isActive() ? 'from-blue-50' : 'from-yellow-50'),
                    'bg_to' => $isCompleted ? 'to-green-100' : ($exam->isActive() ? 'to-blue-100' : 'to-yellow-100'),
                    'border' => $isCompleted ? 'border-green-200' : ($exam->isActive() ? 'border-blue-200' : 'border-yellow-200'),
                    'can_take' => !$isCompleted && $exam->isActive(),
                    'route' => $isCompleted ? route('student.exams.result', $exam) : route('student.exams.take', $exam)
                ];
            });
        }
        
        // Get recent grades
        $grades = collect();
        if ($enrolledCourseIds->isNotEmpty()) {
        $grades = Grade::whereIn('course_id', $enrolledCourseIds)
            ->where('student_id', $user->id)
            ->with(['course', 'gradeable'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(function($grade) {
                    // Use polymorphic gradeable relation
                    $gradeable = $grade->gradeable;
                    $title = $gradeable->title ?? '';
                    $type = $gradeable ? class_basename($gradeable) : '';
                    
                    $score = $grade->score ?? 0;
                    $maxScore = $grade->max_score ?? 100;
                    $percentage = $maxScore > 0 ? round(($score / $maxScore) * 100, 1) : 0;
                    
                    return [
                        'title' => $title,
                        'course' => $grade->course->title ?? 'Unknown Course',
                        'type' => $type,
                        'score' => $score . '/' . $maxScore,
                        'percentage' => $percentage,               // numeric for calculations
                        'percentage_display' => $percentage . '%',  // formatted for view
                        'color' => $percentage >= 90 ? 'text-green-600' : ($percentage >= 80 ? 'text-blue-600' : ($percentage >= 70 ? 'text-yellow-600' : 'text-red-600')),
                        'date' => $grade->created_at->format('M d, Y')
                    ];
                });
        }
        
        // Get recent announcements from enrolled courses
        $announcements = collect();
        if ($enrolledCourseIds->isNotEmpty()) {
            try {
                $announcements = Announcement::whereIn('course_id', $enrolledCourseIds)
                    ->where('is_published', true)
                    ->with(['course', 'instructor'])
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();
            } catch (\Exception $e) {
                // Announcement table doesn't exist yet
                $announcements = collect();
            }
        } 
        
        // Calculate average grade
        $averageGrade = null;
        if ($grades->isNotEmpty()) {
            $totalPercentage = $grades->sum('percentage');
            $averageGrade = round($totalPercentage / $grades->count(), 1);
        }
        
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
     * Instructor Dashboard
     */
    public function instructorDashboard()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get instructor's courses with students count
        $courses = Course::where('instructor_id', $user->id)
                        ->with(['students', 'assignments', 'exams'])
                        ->get();
        
        // Calculate total students across all courses
        $totalStudents = $courses->sum(function($course) {
            return $course->students->count();
        });
        
        // Get pending assignments to grade
        $pendingAssignments = Assignment::where('instructor_id', $user->id)
                                       ->with(['course', 'submissions'])
                                       ->where('status', 'published')
                                       ->get()
                                       ->filter(function($assignment) {
                                           return $assignment->submissions()->count() > 0;
                                       });
        
        $pendingGrades = $pendingAssignments->sum(function($assignment) {
            return $assignment->submissions()->whereNull('grade')->count();
        });
        
        // Get exams statistics
        $upcomingExams = 0;
        $activeExams = 0;
        $totalExams = 0;
        
        try {
            $examQuery = Exam::where('instructor_id', $user->id);
            $totalExams = $examQuery->count();
            $upcomingExams = $examQuery->where('start_time', '>', now())->count();
            $activeExams = $examQuery->where('start_time', '<=', now())
                                    ->where('end_time', '>=', now())
                                    ->where('status', 'published')
                                    ->count();
        } catch (\Exception $e) {
            // Exam table doesn't exist yet
            $upcomingExams = 0;
            $activeExams = 0;
            $totalExams = 0;
        }
        
        // Get today's schedule (placeholder for now)
        $todaySchedule = collect();
        $todayClasses = 0;
        
        // Get recent activities
        $recentActivities = collect();
        
        // Add recent course creations
        $recentCourses = $courses->sortByDesc('created_at')->take(2);
        foreach ($recentCourses as $course) {
            $recentActivities->push((object) [
                'type' => 'course_created',
                'description' => "Created course: {$course->title}",
                'time_ago' => $course->created_at->diffForHumans(),
                'icon' => 'fas fa-chalkboard-teacher',
                'color' => 'text-blue-600'
            ]);
        }
        
        // Add recent student enrollments
        foreach ($courses as $course) {
            $recentEnrollments = DB::table('course_enrollments')
                                  ->where('course_id', $course->id)
                                  ->orderBy('created_at', 'desc')
                                  ->take(1)
                                  ->get();
            
            foreach ($recentEnrollments as $enrollment) {
                $student = User::find($enrollment->user_id);
                if ($student) {
                    $recentActivities->push((object) [
                        'type' => 'enrollment',
                        'description' => "{$student->first_name} {$student->last_name} enrolled in {$course->title}",
                        'time_ago' => \Carbon\Carbon::parse($enrollment->created_at)->diffForHumans(),
                        'icon' => 'fas fa-user-plus',
                        'color' => 'text-green-600'
                    ]);
                }
            }
        }
        
        // Add recent assignment activities
        $recentAssignments = Assignment::where('instructor_id', $user->id)
                                      ->orderBy('created_at', 'desc')
                                      ->take(2)
                                      ->get();
        
        foreach ($recentAssignments as $assignment) {
            $recentActivities->push((object) [
                'type' => 'assignment_created',
                'description' => "Created assignment: {$assignment->title}",
                'time_ago' => $assignment->created_at->diffForHumans(),
                'icon' => 'fas fa-tasks',
                'color' => 'text-purple-600'
            ]);
        }
        
        // Add recent exam activities
        try {
            $recentExams = Exam::where('instructor_id', $user->id)
                              ->orderBy('created_at', 'desc')
                              ->take(2)
                              ->get();
            
            foreach ($recentExams as $exam) {
                $recentActivities->push((object) [
                    'type' => 'exam_created',
                    'description' => "Created exam: {$exam->title}",
                    'time_ago' => $exam->created_at->diffForHumans(),
                    'icon' => 'fas fa-clipboard-check',
                    'color' => 'text-orange-600'
                ]);
            }
        } catch (\Exception $e) {
            // Exam table doesn't exist yet
        }
        
        // Sort activities by time and take recent ones
        $recentActivities = $recentActivities->sortByDesc('time_ago')->take(5);
        
        // Get recent announcements
        $recentAnnouncements = collect();
        try {
            $recentAnnouncements = Announcement::where('instructor_id', $user->id)
                ->with(['course'])
                ->orderBy('created_at', 'desc')
                ->take(3)
                ->get();
        } catch (\Exception $e) {
            // Announcement table doesn't exist yet
        }
        
        return view('dashboard.instructor', compact(
            'courses', 
            'totalStudents', 
            'pendingGrades', 
            'todayClasses',
            'pendingAssignments',
            'todaySchedule',
            'recentActivities',
            'upcomingExams',
            'activeExams',
            'totalExams',
            'recentAnnouncements'
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
     * Remove student from specific course (if needed)
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
