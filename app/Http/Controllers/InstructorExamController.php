<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\ExamAttempt;
use App\Models\ExamAnswer;
use App\Models\Course;
use App\Models\User;
use App\Services\GoogleDriveService;
use App\Traits\FlashMessageTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class InstructorExamController extends Controller
{
    use FlashMessageTrait;
    protected $googleDriveService;

    public function __construct(GoogleDriveService $googleDriveService)
    {
        $this->googleDriveService = $googleDriveService;
    }

    /**
     * Display a listing of exams
     */
    public function index()
    {
        $instructorId = Auth::id();
        $exams = Exam::with(['course', 'questions'])
            ->where('instructor_id', $instructorId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('exams.instructor.index', compact('exams'));
    }

    /**
     * Show the form for creating a new exam
     */
    public function create()
    {
        $instructorId = Auth::id();
    $courses = Course::where('instructor_id', $instructorId)->get();
    // Get all students for cohort assignments
    $students = User::where('role', 'student')->get();
        
    return view('exams.instructor.create', compact('courses', 'students'));
    }

    /**
     * Store a newly created exam
     */
    public function store(Request $request)
    {
        // Add debugging
        Log::info('Exam creation started', ['user_id' => Auth::id(), 'request_data' => $request->all()]);
        
        try {
            $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'course_id' => 'required|exists:courses,id',
            'duration_minutes' => 'required|integer|min:1',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'questions' => 'required|array|min:1',
            'questions.*.type' => 'required|in:mcq,short_answer,file_upload',
            'questions.*.question' => 'required|string',
            'questions.*.points' => 'required|integer|min:1',
            'questions.*.options' => 'required_if:questions.*.type,mcq|array',
            'questions.*.correct_answer' => 'required_if:questions.*.type,mcq|string',
            'attachments.*' => 'file|max:10240', // 10MB max per file
            // Cohorts data
            'cohorts' => 'nullable|array',
            'cohorts.*.cohort_name' => 'required_with:cohorts|string|max:255',
            'cohorts.*.start_time' => 'required_with:cohorts|date',
            'cohorts.*.end_time' => 'required_with:cohorts|date|after:cohorts.*.start_time',
            'cohorts.*.student_ids' => 'required_with:cohorts|array|min:1',
        ]);

        Log::info('Validation passed for exam creation');

        try {
            DB::beginTransaction();

            // Create exam
            $exam = Exam::create([
                'title' => $request->title,
                'description' => $request->description,
                'course_id' => $request->course_id,
                'instructor_id' => Auth::id(),
                'duration_minutes' => $request->duration_minutes,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'auto_grade_mcq' => $request->has('auto_grade_mcq'),
                'show_results_immediately' => $request->has('show_results_immediately'),
                'prevent_navigation' => $request->has('prevent_navigation'),
                'shuffle_questions' => $request->has('shuffle_questions'),
                'max_attempts' => $request->input('max_attempts', 1),
                'passing_score' => $request->input('passing_score', 60),
                'status' => 'published'  // Auto-publish all exams when created
            ]);

            // Calculate total points and create questions
            $totalPoints = 0;
            foreach ($request->questions as $index => $questionData) {
                $question = ExamQuestion::create([
                    'exam_id' => $exam->id,
                    'type' => $questionData['type'],
                    'question' => $questionData['question'],
                    'options' => $questionData['type'] === 'mcq' ? $questionData['options'] : null,
                    'correct_answer' => $questionData['correct_answer'] ?? null,
                    'points' => $questionData['points'],
                    'order' => $index + 1,
                    'required' => $questionData['required'] ?? true
                ]);
                
                $totalPoints += $questionData['points'];
            }

            // Update exam total points
            $exam->update(['total_points' => $totalPoints]);

            // Handle file attachments
            $attachments = [];
            if ($request->hasFile('attachments')) {
                $course = Course::find($request->course_id);
                
                foreach ($request->file('attachments') as $file) {
                    $uploadResult = $this->googleDriveService->uploadExamMaterial(
                        $file,
                        $course->title,
                        $exam->title
                    );
                    
                    if ($uploadResult) {
                        $attachments[] = $uploadResult;
                    }
                }
                
                $exam->update(['attachments' => $attachments]);
            }
            // Handle exam cohorts assignment
            if ($request->filled('cohorts')) {
                foreach ($request->input('cohorts') as $cohortData) {
                    $exam->cohorts()->create([
                        'cohort_name' => $cohortData['cohort_name'],
                        'description' => $cohortData['description'] ?? null,
                        'start_time' => $cohortData['start_time'],
                        'end_time' => $cohortData['end_time'],
                        'student_ids' => $cohortData['student_ids'],
                    ]);
                }
            }

            DB::commit();

            $successMessage = 'Exam "' . $exam->title . '" created successfully!';

            // Check if user wants to create another exam
            if ($request->has('create_another')) {
                return $this->flashSuccess($successMessage, 'instructor.exams.create');
            }
            
            // Check if user wants to go to exam details
            if ($request->has('go_to_details')) {
                return $this->flashSuccess($successMessage, 'instructor.exams.show', [$exam]);
            }
            
            // Default: go to exam index (list)
            return $this->flashSuccess($successMessage, 'instructor.exams.index');

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Exam creation failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            
            return back()->withErrors(['error' => 'Failed to create exam: ' . $e->getMessage()])
                ->withInput();
        }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Exam creation validation failed', ['errors' => $e->errors()]);
            throw $e;
        }
    }

    /**
     * Display the specified exam
     */
    public function show(Exam $exam)
    {
        $this->authorizeExamAccess($exam);
        
        $exam->load(['course', 'questions', 'attempts.student']);
        
        return view('exams.instructor.show', compact('exam'));
    }

    /**
     * Show the form for editing the specified exam
     */
    public function edit(Exam $exam)
    {
        $this->authorizeExamAccess($exam);
        
        $instructorId = Auth::id();
        $courses = Course::where('instructor_id', $instructorId)->get();
    $exam->load('questions', 'cohorts');
    // Get all students for cohort assignments
    $students = User::where('role', 'student')->get();
        
    return view('exams.instructor.edit', compact('exam', 'courses', 'students'));
    }

    /**
     * Update the specified exam
     */
    public function update(Request $request, Exam $exam)
    {
        $this->authorizeExamAccess($exam);
        
    $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'questions' => 'required|array|min:1',
            'questions.*.type' => 'required|in:mcq,short_answer,file_upload',
            'questions.*.question' => 'required|string',
            'questions.*.points' => 'required|integer|min:1',
            'questions.*.options' => 'required_if:questions.*.type,mcq|array|min:2',
            'questions.*.correct_answer' => 'required_if:questions.*.type,mcq|string',
            'attachments.*' => 'file|max:10240',
            // Cohorts data
            'cohorts' => 'nullable|array',
            'cohorts.*.cohort_name' => 'required_with:cohorts|string|max:255',
            'cohorts.*.start_time' => 'required_with:cohorts|date',
            'cohorts.*.end_time' => 'required_with:cohorts|date|after:cohorts.*.start_time',
            'cohorts.*.student_ids' => 'required_with:cohorts|array|min:1',
        ]);

        try {
            DB::beginTransaction();

            // Update exam basic info
            $exam->update([
                'title' => $request->title,
                'description' => $request->description,
                'duration_minutes' => $request->duration_minutes,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'auto_grade_mcq' => $request->has('auto_grade_mcq'),
                'show_results_immediately' => $request->has('show_results_immediately'),
                'prevent_navigation' => $request->has('prevent_navigation'),
                'shuffle_questions' => $request->has('shuffle_questions'),
                'max_attempts' => $request->input('max_attempts', 1),
                'passing_score' => $request->input('passing_score', 60),
                'status' => $request->status ?? $exam->status
            ]);

            // Delete existing questions and create new ones
            $exam->questions()->delete();
            
            $totalPoints = 0;
            foreach ($request->questions as $index => $questionData) {
                ExamQuestion::create([
                    'exam_id' => $exam->id,
                    'type' => $questionData['type'],
                    'question' => $questionData['question'],
                    'options' => $questionData['type'] === 'mcq' ? $questionData['options'] : null,
                    'correct_answer' => $questionData['correct_answer'] ?? null,
                    'points' => $questionData['points'],
                    'order' => $index + 1,
                    'required' => $questionData['required'] ?? true
                ]);
                
                $totalPoints += $questionData['points'];
            }

            // Update total points
            $exam->update(['total_points' => $totalPoints]);

            // Handle new attachments
            if ($request->hasFile('attachments')) {
                $course = $exam->course;
                $attachments = $exam->attachments ?? [];
                
                foreach ($request->file('attachments') as $file) {
                    $uploadResult = $this->googleDriveService->uploadExamMaterial(
                        $file,
                        $course->title,
                        $exam->title
                    );
                    
                    if ($uploadResult) {
                        $attachments[] = $uploadResult;
                    }
                }
                
                $exam->update(['attachments' => $attachments]);
            }
            // Handle exam cohorts assignment
            if ($request->filled('cohorts')) {
                // Remove old cohorts
                $exam->cohorts()->delete();
                foreach ($request->input('cohorts') as $cohortData) {
                    $exam->cohorts()->create([
                        'cohort_name' => $cohortData['cohort_name'],
                        'description' => $cohortData['description'] ?? null,
                        'start_time' => $cohortData['start_time'],
                        'end_time' => $cohortData['end_time'],
                        'student_ids' => $cohortData['student_ids'],
                    ]);
                }
            }

            DB::commit();

            return $this->flashSuccess('Exam updated successfully!', 'instructor.exams.show', [$exam]);

        } catch (\Exception $e) {
            DB::rollback();
            
            return back()->withErrors(['error' => 'Failed to update exam: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified exam
     */
    public function destroy(Exam $exam)
    {
        $this->authorizeExamAccess($exam);
        
        try {
            $exam->delete();
            
            return $this->flashSuccess('Exam deleted successfully!', 'instructor.exams.index');
                
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete exam: ' . $e->getMessage()]);
        }
    }

    /**
     * Show exam results
     */
    public function results(Exam $exam)
    {
        $this->authorizeExamAccess($exam);
        
        $exam->load([
            'attempts.student',
            'attempts.examAnswers.examQuestion'
        ]);
        
        return view('exams.instructor.results', compact('exam'));
    }

    /**
     * Show exam attempts
     */
    public function attempts(Exam $exam)
    {
        $this->authorizeExamAccess($exam);
        
        // Get all attempts for this exam with student information
        $attempts = $exam->attempts()
            ->with(['student'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('exams.instructor.attempts', compact('exam', 'attempts'));
    }

    /**
     * View individual attempt details for grading
     */
    public function viewAttempt(Exam $exam, ExamAttempt $attempt)
    {
        $this->authorizeExamAccess($exam);
        
        // Ensure this attempt belongs to this exam
        if ($attempt->exam_id !== $exam->id) {
            abort(404);
        }
        
        // Load attempt with all related data
        $attempt->load([
            'student',
            'examAnswers.examQuestion',
            'exam.course'
        ]);
        
        // Load exam questions for max score calculation
        $exam->load('questions');
        
        return view('exams.instructor.view-attempt', compact('exam', 'attempt'));
    }

    /**
     * Grade individual attempt and save to gradebook
     */
    public function gradeAttempt(Request $request, Exam $exam, ExamAttempt $attempt)
    {
        $this->authorizeExamAccess($exam);
        
        // Ensure this attempt belongs to this exam
        if ($attempt->exam_id !== $exam->id) {
            abort(404);
        }
        
        // Load exam questions for calculating max score
        $exam->load('questions');
        
        // Validate request
        $request->validate([
            'grades' => 'required|array',
            'grades.*' => 'numeric|min:0',
            'feedback' => 'nullable|string',
            'total_score' => 'required|numeric|min:0'
        ]);
        
        try {
            DB::beginTransaction();
            
            // Update individual question grades
            foreach ($request->grades as $answerId => $points) {
                ExamAnswer::where('id', $answerId)
                    ->where('exam_attempt_id', $attempt->id)
                    ->update(['points_awarded' => $points]);
            }
            
            // Calculate percentage score
            $maxScore = $exam->questions->sum('points');
            
            if ($maxScore <= 0) {
                throw new \Exception('Exam has no questions or invalid points');
            }
            
            $percentage = ($request->total_score / $maxScore) * 100;
            
            // Update attempt with final score
            $attempt->update([
                'total_score' => $request->total_score,
                'status' => 'graded'
            ]);
            
            // Add to gradebook (simple approach)
            \App\Models\Grade::updateOrCreate([
                'student_id' => $attempt->student_id,
                'course_id' => $exam->course_id,
                'gradeable_type' => 'App\\Models\\Exam',
                'gradeable_id' => $exam->id,
            ], [
                'instructor_id' => Auth::id(),
                'points_earned' => $request->total_score,
                'points_possible' => $maxScore,
                'percentage' => round($percentage, 2),
                'feedback' => $request->feedback,
                'graded_at' => now(),
                'graded_by' => Auth::id()
            ]);
            
            DB::commit();
            
            return $this->flashSuccess(
                "Exam graded successfully! Score: {$request->total_score}/{$maxScore} (" . round($percentage, 1) . "%)",
                'instructor.exams.attempts',
                [$exam]
            );
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Exam grading failed: ' . $e->getMessage());
            
            return $this->flashError(
                'Failed to grade exam: ' . $e->getMessage(),
                'instructor.exams.view-attempt',
                [$exam, $attempt]
            );
        }
    }

    /**
     * Toggle exam status between draft and published
     */
    
    /**
     * Authorize exam access for instructor
     */
    private function authorizeExamAccess(Exam $exam)
    {
        if ($exam->instructor_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this exam.');
        }
    }
}
