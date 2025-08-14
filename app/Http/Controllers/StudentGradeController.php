<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Assignment;
use App\Models\Exam;

class StudentGradeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:student']);
    }

    /**
     * Display all grades for the student across all enrolled courses
     */
    public function index()
    {
        $student = Auth::user();
        
        // Get all enrolled courses with grades
        $enrolledCourses = $student->enrolledCourses()
            ->with(['instructor', 'assignments', 'exams'])
            ->get();

        // Get all grades for this student
        $grades = Grade::where('student_id', $student->id)
            ->with(['course', 'gradeable'])
            ->get()
            ->keyBy(function($grade) {
                return $grade->course_id . '_' . $grade->gradeable_type . '_' . $grade->gradeable_id;
            });

        // Calculate course averages
        $courseAverages = [];
        foreach ($enrolledCourses as $course) {
            $courseGrades = $grades->filter(function($grade) use ($course) {
                return $grade->course_id === $course->id;
            });
            
            if ($courseGrades->count() > 0) {
                $totalScore = $courseGrades->sum('score');
                $courseAverages[$course->id] = round($totalScore / $courseGrades->count(), 2);
            } else {
                $courseAverages[$course->id] = 0;
            }
        }

        return view('student.grades.index', compact('enrolledCourses', 'grades', 'courseAverages'));
    }

    /**
     * Display grades for a specific course
     */
    public function show(Course $course)
    {
        $student = Auth::user();
        
        // Check if student is enrolled in this course
        $isEnrolled = $student->enrollments()
            ->where('course_id', $course->id)
            ->exists();
            
        if (!$isEnrolled) {
            abort(403, 'You are not enrolled in this course.');
        }

        // Get course assignments and exams
        $assignments = $course->assignments()
            ->where('status', 'published')
            ->orderBy('due_date')
            ->get();
            
        $exams = $course->exams()
            ->where('status', 'published')
            ->orderBy('start_time')
            ->get();

        // Get grades for this course
        $grades = Grade::where('student_id', $student->id)
            ->where('course_id', $course->id)
            ->with(['gradeable'])
            ->get()
            ->keyBy(function($grade) {
                return $grade->gradeable_type . '_' . $grade->gradeable_id;
            });

        // Calculate overall course grade
        $courseGrades = $grades->filter(function($grade) use ($course) {
            return $grade->course_id === $course->id;
        });
        
        $overallGrade = 0;
        if ($courseGrades->count() > 0) {
            $overallGrade = round($courseGrades->avg('score'), 2);
        }

        // Get letter grade
        $letterGrade = $this->calculateLetterGrade($overallGrade);

        return view('student.grades.show', compact(
            'course', 
            'assignments', 
            'exams', 
            'grades', 
            'overallGrade', 
            'letterGrade'
        ));
    }

    /**
     * Get grade details for AJAX requests
     */
    public function getGradeDetails(Request $request)
    {
        $request->validate([
            'grade_id' => 'required|exists:grades,id'
        ]);

        $student = Auth::user();
        $grade = Grade::with(['course', 'gradeable'])->findOrFail($request->grade_id);

        // Check if student owns this grade
        if ($grade->student_id !== $student->id) {
            abort(403, 'Access denied.');
        }

        return response()->json([
            'success' => true,
            'grade' => $grade
        ]);
    }

    /**
     * Calculate letter grade based on percentage
     */
    private function calculateLetterGrade($percentage): string
    {
        if ($percentage >= 93) return 'A';
        if ($percentage >= 90) return 'A-';
        if ($percentage >= 87) return 'B+';
        if ($percentage >= 83) return 'B';
        if ($percentage >= 80) return 'B-';
        if ($percentage >= 77) return 'C+';
        if ($percentage >= 73) return 'C';
        if ($percentage >= 70) return 'C-';
        if ($percentage >= 67) return 'D+';
        if ($percentage >= 63) return 'D';
        if ($percentage >= 60) return 'D-';
        return 'F';
    }
}
