<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamAnswer;
use App\Models\ExamQuestion;
use App\Models\Course;
use App\Services\GoogleDriveService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class StudentExamController extends Controller
{
    protected $googleDriveService;

    public function __construct(GoogleDriveService $googleDriveService)
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (Auth::user()->role !== 'student') {
                abort(403, 'Access denied. Student access required.');
            }
            return $next($request);
        });
        
        $this->googleDriveService = $googleDriveService;
    }

    /**
     * Display available exams for student
     */
    public function index()
    {
        $studentId = Auth::id();
        
        // Get enrolled courses - handle case where user has no enrollments
        $enrollments = Auth::user()->enrollments;
        $enrolledCourseIds = $enrollments ? $enrollments->pluck('course_id') : collect([]);
        
        // Debug information
        Log::info('Student exam access debug', [
            'student_id' => $studentId,
            'enrollments_count' => $enrollments ? $enrollments->count() : 0,
            'enrolled_course_ids' => $enrolledCourseIds->toArray()
        ]);
        
        // Get all exams for enrolled courses that are published (show upcoming, active, and ended)
        $exams = collect([]);
        if ($enrolledCourseIds->isNotEmpty()) {
            $currentTime = Carbon::now();
            $exams = Exam::with(['course', 'questions', 'attempts'])
                ->whereIn('course_id', $enrolledCourseIds)
                ->where('status', 'published')
                ->orderBy('start_time', 'asc')
                ->get();
                
            // Debug exam fetching
            Log::info('Exams found for student', [
                'student_id' => $studentId,
                'current_time' => $currentTime->toDateTimeString(),
                'exams_count' => $exams->count(),
                'exam_details' => $exams->map(function($exam) {
                    return [
                        'id' => $exam->id,
                        'title' => $exam->title,
                        'status' => $exam->status,
                        'course_id' => $exam->course_id,
                        'start_time' => $exam->start_time ? $exam->start_time->toDateTimeString() : 'No start time',
                        'end_time' => $exam->end_time ? $exam->end_time->toDateTimeString() : 'No end time',
                        'course_title' => $exam->course ? $exam->course->title : 'No course',
                        'is_active' => $exam->isActive()
                    ];
                })->toArray()
            ]);
        } else {
            Log::info('No enrollments found for student', ['student_id' => $studentId]);
        }

        return view('exams.student.index', compact('exams'));
    }



    /**
     * Start exam attempt
     */
    public function start(Exam $exam)
    {
        $studentId = Auth::id();
        
        // Verify student can take exam
        if (!$exam->canStudentTake($studentId)) {
            abort(403, 'You are not authorized to take this exam.');
        }
        
        // Check if already has an attempt
        $existingAttempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $studentId)
            ->first();
            
        if ($existingAttempt) {
            if ($existingAttempt->isInProgress()) {
                return redirect()->route('student.exams.take', $exam);
            }
            
            abort(403, 'You have already completed this exam.');
        }

        try {
            DB::beginTransaction();

            $studentCohort = $exam->getStudentCohort($studentId);
            
            // Create exam attempt
            $attempt = ExamAttempt::create([
                'exam_id' => $exam->id,
                'student_id' => $studentId,
                'cohort_id' => $studentCohort ? $studentCohort->id : null,
                'started_at' => now(),
                'max_score' => $exam->total_points,
                'status' => 'in_progress'
            ]);

            DB::commit();

            return redirect()->route('student.exams.take', $exam);

        } catch (\Exception $e) {
            DB::rollback();
            
            return back()->withErrors(['error' => 'Failed to start exam: ' . $e->getMessage()]);
        }
    }

    /**
     * Take exam interface
     */
    public function take(Exam $exam)
    {
        $studentId = Auth::id();
        
        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $studentId)
            ->where('status', 'in_progress')
            ->first();
            
        if (!$attempt) {
            return redirect()->route('student.exams.index')
                ->withErrors(['error' => 'No active exam attempt found.']);
        }

        // Check if time has expired
        if ($attempt->getRemainingTime() <= 0) {
            $this->autoSubmitExam($attempt);
            return redirect()->route('student.exams.result', $exam)
                ->with('info', 'Exam was automatically submitted due to time expiry.');
        }

        $exam->load(['questions', 'course']);
        $existingAnswers = $attempt->examAnswers()->with('examQuestion')->get();
        
        return view('exams.student.take', compact('exam', 'attempt', 'existingAnswers'));
    }

    /**
     * Save exam answer (for auto-save functionality)
     */
    public function saveAnswer(Request $request, Exam $exam)
    {
        $studentId = Auth::id();
        
        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $studentId)
            ->where('status', 'in_progress')
            ->first();
            
        if (!$attempt) {
            return response()->json(['error' => 'No active exam attempt found.'], 404);
        }

        $request->validate([
            'question_id' => 'required|exists:exam_questions,id',
            'answer' => 'nullable|string',
            'files.*' => 'file|max:10240'
        ]);

        try {
            $question = ExamQuestion::find($request->question_id);
            
            // Find or create answer
            $answer = ExamAnswer::updateOrCreate(
                [
                    'exam_attempt_id' => $attempt->id,
                    'exam_question_id' => $request->question_id
                ],
                [
                    'answer_text' => $request->answer
                ]
            );

            // Handle file uploads for file_upload questions
            if ($question->type === 'file_upload' && $request->hasFile('files')) {
                $uploadedFiles = [];
                
                foreach ($request->file('files') as $file) {
                    $uploadResult = $this->googleDriveService->uploadExamSubmission(
                        $file,
                        $exam->course->title,
                        $exam->title,
                        Auth::user()->name
                    );
                    
                    if ($uploadResult) {
                        $uploadedFiles[] = $uploadResult;
                    }
                }
                
                $answer->update(['answer_files' => $uploadedFiles]);
            }

            // Auto-grade MCQ questions
            if ($question->type === 'mcq' && $exam->auto_grade_mcq) {
                $isCorrect = $request->answer === $question->correct_answer;
                $points = $isCorrect ? $question->points : 0;
                
                $answer->update([
                    'is_correct' => $isCorrect,
                    'points_awarded' => $points
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Answer saved successfully.']);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to save answer: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Submit exam
     */
    public function submit(Request $request, Exam $exam)
    {
        $studentId = Auth::id();
        
        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $studentId)
            ->where('status', 'in_progress')
            ->first();
            
        if (!$attempt) {
            return redirect()->route('student.exams.index')
                ->withErrors(['error' => 'No active exam attempt found.']);
        }

        try {
            DB::beginTransaction();

            // Calculate total score
            $totalScore = $attempt->examAnswers()
                ->whereNotNull('points_awarded')
                ->sum('points_awarded');

            // Calculate time spent
            $timeSpentMinutes = $attempt->started_at->diffInMinutes(now());

            // Update attempt
            $attempt->update([
                'submitted_at' => now(),
                'time_spent_minutes' => $timeSpentMinutes,
                'total_score' => $totalScore,
                'status' => 'submitted'
            ]);

            DB::commit();

            return redirect()->route('student.exams.result', $exam)
                ->with('success', 'Exam submitted successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            
            return back()->withErrors(['error' => 'Failed to submit exam: ' . $e->getMessage()]);
        }
    }

    /**
     * Show exam result
     */
    public function result(Exam $exam)
    {
        $studentId = Auth::id();
        
        $attempt = ExamAttempt::with([
            'examAnswers.examQuestion',
            'exam.course'
        ])
        ->where('exam_id', $exam->id)
        ->where('student_id', $studentId)
        ->whereIn('status', ['submitted', 'auto_submitted', 'graded'])
        ->first();
        
        if (!$attempt) {
            abort(404, 'Exam result not found.');
        }

        return view('exams.student.result', compact('attempt', 'exam'));
    }

    /**
     * Get remaining time for exam (AJAX)
     */
    public function getRemainingTime(Exam $exam)
    {
        $studentId = Auth::id();
        
        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $studentId)
            ->where('status', 'in_progress')
            ->first();
            
        if (!$attempt) {
            return response()->json(['error' => 'No active attempt found.'], 404);
        }

        $remainingMinutes = $attempt->getRemainingTime();
        
        // Auto-submit if time expired
        if ($remainingMinutes <= 0) {
            $this->autoSubmitExam($attempt);
            return response()->json(['expired' => true]);
        }

        return response()->json(['remaining_minutes' => $remainingMinutes]);
    }

    /**
     * Auto-submit exam when time expires
     */
    private function autoSubmitExam(ExamAttempt $attempt)
    {
        try {
            DB::beginTransaction();

            $totalScore = $attempt->examAnswers()
                ->whereNotNull('points_awarded')
                ->sum('points_awarded');

            $timeSpentMinutes = $attempt->started_at->diffInMinutes(now());

            $attempt->update([
                'submitted_at' => now(),
                'time_spent_minutes' => $timeSpentMinutes,
                'total_score' => $totalScore,
                'status' => 'auto_submitted'
            ]);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Auto-submit failed: ' . $e->getMessage());
        }
    }
}
