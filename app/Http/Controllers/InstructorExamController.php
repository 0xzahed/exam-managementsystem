<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\Course;
use App\Models\User;
use App\Services\GoogleDriveService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class InstructorExamController extends Controller
{
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

            // Check if user wants to create another exam
            if ($request->has('create_another')) {
                return redirect()->route('instructor.exams.create')
                    ->with('success', '🎉 Exam "' . $exam->title . '" created and published successfully! Students will see it at the scheduled time. You can now create another exam.');
            }
            
            // Check if user wants to go to exam details
            if ($request->has('go_to_details')) {
                return redirect()->route('instructor.exams.show', $exam)
                    ->with('success', '🎉 Exam "' . $exam->title . '" created and published successfully! Students will see it at the scheduled time.');
            }
            
            // Default: go to exam index (list)
            return redirect()->route('instructor.exams.index')
                ->with('success', '🎉 Exam "' . $exam->title . '" created and published successfully! Students will see it at the scheduled time.');

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

            return redirect()->route('instructor.exams.show', $exam)
                ->with('success', 'Exam updated successfully!');

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
            
            return redirect()->route('instructor.exams.index')
                ->with('success', 'Exam deleted successfully!');
                
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
