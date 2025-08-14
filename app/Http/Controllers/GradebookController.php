<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Assignment;
use App\Models\Exam;
use App\Models\User;
use App\Models\AssignmentSubmission;
use App\Models\ExamAttempt;

class GradebookController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:instructor']);
    }

    /**
     * Display the main gradebook for all courses
     */
    public function index()
    {
        $instructor = Auth::user();
        
        $courses = Course::where('instructor_id', $instructor->id)
            ->withCount(['students', 'assignments', 'exams'])
            ->get();

        return view('gradebook.index', compact('courses'));
    }

    /**
     * Display the gradebook for a specific course
     */
    public function show(Course $course)
    {
        $instructor = Auth::user();
        
        // Check if instructor owns this course
        if ($course->instructor_id !== $instructor->id) {
            abort(403, 'Access denied.');
        }

        // Get all students enrolled in the course
        $students = $course->students()->orderBy('first_name')->get();
        
        // Get all assignments and exams for the course
        $assignments = $course->assignments()->where('status', 'published')->orderBy('due_date')->get();
        $exams = $course->exams()->where('status', 'published')->orderBy('start_time')->get();
        
        // Sync assignment grades to gradebook
        $this->syncAssignmentGrades($course);
        
        // Sync exam grades to gradebook  
        $this->syncExamGrades($course);
        
        // Get all grades for this course
        $grades = Grade::where('course_id', $course->id)->get()->keyBy(function($grade) {
            return $grade->student_id . '_' . $grade->gradeable_type . '_' . $grade->gradeable_id;
        });

        // Calculate course averages for each student
        $studentAverages = [];
        foreach ($students as $student) {
            $studentGrades = $grades->filter(function($grade) use ($student) {
                return $grade->student_id === $student->id;
            });
            
            if ($studentGrades->count() > 0) {
                $totalScore = $studentGrades->sum('score');
                $studentAverages[$student->id] = round($totalScore / $studentGrades->count(), 2);
            } else {
                $studentAverages[$student->id] = 0;
            }
        }

        return view('gradebook.show', compact(
            'course', 
            'students', 
            'assignments', 
            'exams', 
            'grades', 
            'studentAverages'
        ));
    }

    /**
     * Update a grade
     */
    public function updateGrade(Request $request)
    {
        $request->validate([
            'grade_id' => 'required|exists:grades,id',
            'points_earned' => 'required|numeric|min:0',
            'feedback' => 'nullable|string|max:1000'
        ]);

        $grade = Grade::findOrFail($request->grade_id);
        $instructor = Auth::user();

        // Check if instructor owns this grade
        if ($grade->instructor_id !== $instructor->id) {
            abort(403, 'Access denied.');
        }

        // Calculate percentage score
        $percentage = round(($request->points_earned / $grade->total_points) * 100, 2);
        
        // Calculate letter grade
        $letterGrade = $this->calculateLetterGrade($percentage);

        $grade->update([
            'points_earned' => $request->points_earned,
            'score' => $percentage,
            'letter_grade' => $letterGrade,
            'feedback' => $request->feedback,
            'graded_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'grade' => $grade->fresh(),
            'message' => 'Grade updated successfully'
        ]);
    }

    /**
     * Bulk update grades
     */
    public function bulkUpdateGrades(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'grades' => 'required|array',
            'grades.*.grade_id' => 'required|exists:grades,id',
            'grades.*.points_earned' => 'required|numeric|min:0'
        ]);

        $instructor = Auth::user();
        $course = Course::findOrFail($request->course_id);

        // Check if instructor owns this course
        if ($course->instructor_id !== $instructor->id) {
            abort(403, 'Access denied.');
        }

        $updatedGrades = [];
        
        foreach ($request->grades as $gradeData) {
            $grade = Grade::findOrFail($gradeData['grade_id']);
            
            // Check if instructor owns this grade
            if ($grade->instructor_id !== $instructor->id) {
                continue;
            }

            // Calculate percentage score
            $percentage = round(($gradeData['points_earned'] / $grade->total_points) * 100, 2);
            
            // Calculate letter grade
            $letterGrade = $this->calculateLetterGrade($percentage);

            $grade->update([
                'points_earned' => $gradeData['points_earned'],
                'score' => $percentage,
                'letter_grade' => $letterGrade,
                'graded_at' => now()
            ]);

            $updatedGrades[] = $grade->fresh();
        }

        return response()->json([
            'success' => true,
            'grades' => $updatedGrades,
            'message' => count($updatedGrades) . ' grades updated successfully'
        ]);
    }

    /**
     * Create or update a grade for an assignment submission
     */
    public function gradeAssignment(Request $request, AssignmentSubmission $submission)
    {
        $request->validate([
            'points_earned' => 'required|numeric|min:0',
            'feedback' => 'nullable|string|max:1000'
        ]);

        $instructor = Auth::user();
        $assignment = $submission->assignment;

        // Check if instructor owns this assignment
        if ($assignment->instructor_id !== $instructor->id) {
            abort(403, 'Access denied.');
        }

        // Calculate percentage score
        $totalPoints = $assignment->marks ?? 100;
        $percentage = round(($request->points_earned / $totalPoints) * 100, 2);
        
        // Calculate letter grade
        $letterGrade = $this->calculateLetterGrade($percentage);

        // Create or update grade
        $grade = Grade::updateOrCreate(
            [
                'student_id' => $submission->student_id,
                'course_id' => $assignment->course_id,
                'instructor_id' => $instructor->id,
                'gradeable_type' => AssignmentSubmission::class,
                'gradeable_id' => $submission->id
            ],
            [
                'points_earned' => $request->points_earned,
                'total_points' => $totalPoints,
                'score' => $percentage,
                'letter_grade' => $letterGrade,
                'feedback' => $request->feedback,
                'graded_at' => now()
            ]
        );

        // Update submission grade
        $submission->update([
            'grade' => $request->points_earned,
            'feedback' => $request->feedback,
            'graded_by' => $instructor->id
        ]);

        return response()->json([
            'success' => true,
            'grade' => $grade,
            'message' => 'Assignment graded successfully'
        ]);
    }

    /**
     * Create or update a grade for an exam attempt
     */
    public function gradeExam(Request $request, ExamAttempt $attempt)
    {
        $request->validate([
            'points_earned' => 'required|numeric|min:0',
            'feedback' => 'nullable|string|max:1000'
        ]);

        $instructor = Auth::user();
        $exam = $attempt->exam;

        // Check if instructor owns this exam
        if ($exam->instructor_id !== $instructor->id) {
            abort(403, 'Access denied.');
        }

        // Calculate percentage score
        $totalPoints = $exam->total_points ?? 100;
        $percentage = round(($request->points_earned / $totalPoints) * 100, 2);
        
        // Calculate letter grade
        $letterGrade = $this->calculateLetterGrade($percentage);

        // Create or update grade
        $grade = Grade::updateOrCreate(
            [
                'student_id' => $attempt->student_id,
                'course_id' => $exam->course_id,
                'instructor_id' => $instructor->id,
                'gradeable_type' => ExamAttempt::class,
                'gradeable_id' => $attempt->id
            ],
            [
                'points_earned' => $request->points_earned,
                'total_points' => $totalPoints,
                'score' => $percentage,
                'letter_grade' => $letterGrade,
                'feedback' => $request->feedback,
                'graded_at' => now()
            ]
        );

        // Update attempt score
        $attempt->update([
            'total_score' => $request->points_earned,
            'status' => 'graded'
        ]);

        return response()->json([
            'success' => true,
            'grade' => $grade,
            'message' => 'Exam graded successfully'
        ]);
    }

    /**
     * Export grades to CSV
     */
    public function exportGrades(Course $course)
    {
        $instructor = Auth::user();
        
        // Check if instructor owns this course
        if ($course->instructor_id !== $instructor->id) {
            abort(403, 'Access denied.');
        }

        $students = $course->students()->orderBy('first_name')->get();
        $assignments = $course->assignments()->where('status', 'published')->orderBy('due_date')->get();
        $exams = $course->exams()->where('status', 'published')->orderBy('start_time')->get();
        
        $grades = Grade::where('course_id', $course->id)->get()->keyBy(function($grade) {
            return $grade->student_id . '_' . $grade->gradeable_type . '_' . $grade->gradeable_id;
        });

        $filename = $course->title . '_Grades_' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($students, $assignments, $exams, $grades) {
            $file = fopen('php://output', 'w');
            
            // Header row
            $header = ['Student Name', 'Student Email'];
            foreach ($assignments as $assignment) {
                $header[] = $assignment->title . ' (Assignment)';
            }
            foreach ($exams as $exam) {
                $header[] = $exam->title . ' (Exam)';
            }
            $header[] = 'Course Average';
            fputcsv($file, $header);

            // Data rows
            foreach ($students as $student) {
                $row = [$student->first_name . ' ' . $student->last_name, $student->email];
                
                // Assignment grades
                foreach ($assignments as $assignment) {
                    $gradeKey = $student->id . '_App\Models\AssignmentSubmission_' . $assignment->id;
                    $grade = $grades->get($gradeKey);
                    $row[] = $grade ? $grade->points_earned . '/' . $grade->total_points . ' (' . $grade->score . '%)' : 'Not Submitted';
                }
                
                // Exam grades
                foreach ($exams as $exam) {
                    $gradeKey = $student->id . '_App\Models\ExamAttempt_' . $exam->id;
                    $grade = $grades->get($gradeKey);
                    $row[] = $grade ? $grade->points_earned . '/' . $grade->total_points . ' (' . $grade->score . '%)' : 'Not Taken';
                }
                
                // Course average
                $studentGrades = $grades->filter(function($grade) use ($student) {
                    return $grade->student_id === $student->id;
                });
                
                if ($studentGrades->count() > 0) {
                    $average = round($studentGrades->avg('score'), 2);
                    $row[] = $average . '%';
                } else {
                    $row[] = 'No Grades';
                }
                
                fputcsv($file, $row);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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
    
    /**
     * Sync assignment grades from assignment_submissions table to grades table
     */
    private function syncAssignmentGrades($course)
    {
        // Get all graded assignment submissions for this course that don't have Grade records yet
        $gradedSubmissions = DB::table('assignment_submissions')
            ->join('assignments', 'assignment_submissions.assignment_id', '=', 'assignments.id')
            ->where('assignments.course_id', $course->id)
            ->whereNotNull('assignment_submissions.grade')
            ->whereNotNull('assignment_submissions.graded_at')
            ->select(
                'assignment_submissions.*',
                'assignments.marks as total_marks',
                'assignments.instructor_id'
            )
            ->get();
        
        foreach ($gradedSubmissions as $submission) {
            // Check if Grade record already exists
            $existingGrade = Grade::where([
                'student_id' => $submission->student_id,
                'course_id' => $course->id,
                'gradeable_type' => AssignmentSubmission::class,
                'gradeable_id' => $submission->id
            ])->first();
            
            if (!$existingGrade) {
                // Calculate percentage and letter grade
                $percentage = round(($submission->grade / $submission->total_marks) * 100, 2);
                $letterGrade = $this->calculateLetterGrade($percentage);
                
                // Create Grade record
                Grade::create([
                    'student_id' => $submission->student_id,
                    'course_id' => $course->id,
                    'instructor_id' => $submission->instructor_id,
                    'gradeable_type' => AssignmentSubmission::class,
                    'gradeable_id' => $submission->id,
                    'points_earned' => $submission->grade,
                    'total_points' => $submission->total_marks,
                    'score' => $percentage,
                    'letter_grade' => $letterGrade,
                    'feedback' => $submission->feedback,
                    'graded_at' => $submission->graded_at,
                    'grade_type' => 'assignment'
                ]);
            }
        }
    }
    
    /**
     * Sync exam grades from exam_attempts table to grades table
     */
    private function syncExamGrades($course)
    {
        // Get all completed/graded exam attempts for this course
        $completedAttempts = DB::table('exam_attempts')
            ->join('exams', 'exam_attempts.exam_id', '=', 'exams.id')
            ->where('exams.course_id', $course->id)
            ->whereNotNull('exam_attempts.total_score')
            ->whereNotNull('exam_attempts.submitted_at')
            ->whereIn('exam_attempts.status', ['completed', 'graded', 'submitted', 'auto_submitted'])
            ->select(
                'exam_attempts.*',
                'exams.total_points as exam_total_points',
                'exams.instructor_id'
            )
            ->get();
        
        foreach ($completedAttempts as $attempt) {
            // Check if Grade record already exists
            $existingGrade = Grade::where([
                'student_id' => $attempt->student_id,
                'course_id' => $course->id,
                'gradeable_type' => 'App\\Models\\Exam',
                'gradeable_id' => $attempt->exam_id
            ])->first();
            
            if (!$existingGrade) {
                // Calculate percentage and letter grade
                $maxScore = $attempt->exam_total_points ?: $attempt->max_score ?: 100;
                $percentage = $maxScore > 0 ? round(($attempt->total_score / $maxScore) * 100, 2) : 0;
                $letterGrade = $this->calculateLetterGrade($percentage);
                
                // Create Grade record
                Grade::create([
                    'student_id' => $attempt->student_id,
                    'course_id' => $course->id,
                    'instructor_id' => $attempt->instructor_id,
                    'gradeable_type' => 'App\\Models\\Exam',
                    'gradeable_id' => $attempt->exam_id,
                    'points_earned' => $attempt->total_score,
                    'points_possible' => $maxScore,
                    'total_points' => $maxScore,
                    'score' => $percentage,
                    'percentage' => $percentage,
                    'letter_grade' => $letterGrade,
                    'feedback' => null,
                    'graded_at' => $attempt->submitted_at,
                    'grade_type' => 'exam'
                ]);
            }
        }
    }
}
