<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamQuestion;
use App\Models\ExamAnswer;
use App\Services\GoogleDriveService;

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
        // Debug logging
        Log::info('Exam start requested', [
            'exam_id' => $exam->id,
            'student_id' => $studentId,
            'exam_title' => $exam->title
        ]);

        // Check exam scheduling restrictions first
        $examStatus = $exam->getStatusForStudent($studentId);
        Log::info('Exam status check', ['status' => $examStatus]);
        if ($examStatus === 'not_started') {
            $timeUntilStart = $exam->getTimeUntilStart($studentId);
            return back()->withErrors([
                'error' => 'This exam has not started yet. It will be available in ' .
                gmdate('H:i:s', $timeUntilStart)
            ]);
        }
        if ($examStatus === 'ended') {
            return back()->withErrors([
                'error' => 'This exam has already ended.'
            ]);
        }
        if ($examStatus !== 'available') {
            return back()->withErrors([
                'error' => 'This exam is not currently available.'
            ]);
        }

        // Verify student can take exam (enrollment, attempts, etc.)
        $canTake = $exam->canStudentTake($studentId);
        Log::info('Can student take exam', ['can_take' => $canTake]);
        if (!$canTake) {
            return back()->withErrors([
                'error' => 'You are not authorized to take this exam or have exceeded maximum attempts.'
            ]);
        }

        // Check if already has an in-progress attempt
        $inProgressAttempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $studentId)
            ->where('status', 'in_progress')
            ->first();
        if ($inProgressAttempt) {
            // Resume existing in-progress attempt
            return redirect()->route('student.exams.take', $exam);
        }

        // Check if student has reached max attempts
        $attemptCount = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $studentId)
            ->count();
        if ($attemptCount >= $exam->max_attempts) {
            $latestAttempt = ExamAttempt::where('exam_id', $exam->id)
                ->where('student_id', $studentId)
                ->latest()
                ->first();
            return redirect()->route('student.exams.result', $exam)
                ->with('info', 'You have used all your attempts for this exam. View your results below.');
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
            Log::error('Failed to start exam', ['exam_id' => $exam->id, 'student_id' => $studentId, 'error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Failed to start exam: ' . $e->getMessage()]);
        }
    }

    /**
     * Take exam interface
     */
    public function take(Exam $exam)
    {
        $studentId = Auth::id();
        Log::info('Take exam requested', [
            'exam_id' => $exam->id,
            'student_id' => $studentId
        ]);
        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $studentId)
            ->where('status', 'in_progress')
            ->first();
        if (!$attempt) {
            Log::warning('No active attempt found', [
                'exam_id' => $exam->id,
                'student_id' => $studentId
            ]);
            return redirect()->route('student.exams.index')
                ->withErrors(['error' => 'No active exam attempt found.']);
        }

        // Check if time has expired
        $remainingTime = $attempt->getRemainingTime();
        Log::info('Exam time check', [
            'remaining_time' => $remainingTime,
            'exam_id' => $exam->id
        ]);
        if ($remainingTime <= 0) {
            $this->autoSubmitExam($attempt);
            return redirect()->route('student.exams.result', $exam)
                ->with('info', 'Exam was automatically submitted due to time expiry.');
        }

        $exam->load(['questions', 'course']);
        $existingAnswers = $attempt->examAnswers()->with('examQuestion')->get();
        
        // Format existing answers for JavaScript
        $formattedAnswers = [];
        foreach ($existingAnswers as $answer) {
            $formattedAnswers[$answer->exam_question_id] = [
                'answer' => $answer->answer_text,
                'type' => $answer->examQuestion->type ?? 'text',
                'points_awarded' => $answer->points_awarded,
                'is_correct' => $answer->is_correct
            ];
        }
        
        Log::info('Exam take view loaded successfully', [
            'exam_id' => $exam->id,
            'questions_count' => $exam->questions->count(),
            'existing_answers_count' => $existingAnswers->count()
        ]);
        
        return view('exams.student.take', compact('exam', 'attempt', 'existingAnswers', 'formattedAnswers'));
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

        try {
            // Support two modes:
            // 1) Single-answer save (question_id + answer)
            // 2) Bulk auto-save (answers: { [questionId]: { answer, type } })
            if ($request->has('answers') && is_array($request->input('answers'))) {
                $answers = $request->input('answers');
                foreach ($answers as $questionId => $payload) {
                    /** @var ExamQuestion|null $question */
                    $question = ExamQuestion::find($questionId);
                    if (!$question) {
                        continue;
                    }
                    $answerText = is_array($payload) && array_key_exists('answer', $payload) ? $payload['answer'] : (string) $payload;
                    $answer = ExamAnswer::updateOrCreate(
                        ['exam_attempt_id' => $attempt->id, 'exam_question_id' => $questionId],
                        ['answer_text' => $answerText]
                    );
                    if ($question->type === 'mcq' && $exam->auto_grade_mcq) {
                        $isCorrect = $answerText === $question->correct_answer;
                        $points = $isCorrect ? $question->points : 0;
                        $answer->update([
                            'is_correct' => $isCorrect,
                            'points_awarded' => $points
                        ]);
                    }
                }
                return response()->json(['success' => true]);
            }

            // Validate and persist a single answer
            $request->validate([
                'question_id' => 'required|exists:exam_questions,id',
                'answer' => 'nullable|string',
                'files.*' => 'file|max:10240'
            ]);
            $question = ExamQuestion::find($request->question_id);
            $answer = ExamAnswer::updateOrCreate(
                ['exam_attempt_id' => $attempt->id, 'exam_question_id' => $request->question_id],
                ['answer_text' => $request->answer]
            );

            // Handle file uploads for file_upload questions
            if ($question && $question->type === 'file_upload' && $request->hasFile('files')) {
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

            if ($question && $question->type === 'mcq' && $exam->auto_grade_mcq) {
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
            if ($request->expectsJson()) {
                return response()->json(['error' => 'No active exam attempt found.'], 404);
            }
            return redirect()->route('student.exams.index')
                ->withErrors(['error' => 'No active exam attempt found.']);
        }

        try {
            DB::beginTransaction();
            // Persist any final answers sent from the client
            if ($request->has('answers') && is_array($request->input('answers'))) {
                $answers = $request->input('answers');
                foreach ($answers as $questionId => $payload) {
                    $question = ExamQuestion::find($questionId);
                    if (!$question) continue;
                    $answerText = is_array($payload) && array_key_exists('answer', $payload)
                        ? $payload['answer']
                        : (string) $payload;
                    $answer = ExamAnswer::updateOrCreate(
                        ['exam_attempt_id' => $attempt->id, 'exam_question_id' => $questionId],
                        ['answer_text' => $answerText]
                    );
                    if ($question->type === 'mcq' && $exam->auto_grade_mcq) {
                        $isCorrect = $answerText === $question->correct_answer;
                        $points = $isCorrect ? $question->points : 0;
                        $answer->update([
                            'is_correct' => $isCorrect,
                            'points_awarded' => $points
                        ]);
                    }
                }
            }

            // Calculate totals
            $totalScore = $attempt->examAnswers()
                ->whereNotNull('points_awarded')
                ->sum('points_awarded');
            $timeSpentMinutes = $attempt->started_at->diffInMinutes(now());

            // Determine status
            $status = $request->boolean('auto_submit') ? 'auto_submitted' : 'submitted';
            $attempt->update([
                'submitted_at' => now(),
                'time_spent_minutes' => $timeSpentMinutes,
                'total_score' => $totalScore,
                'status' => $status
            ]);
            DB::commit();

            $redirectUrl = route('student.exams.result', $exam);
            if ($request->expectsJson()) {
                return response()->json(['redirect_url' => $redirectUrl]);
            }
            return redirect($redirectUrl)->with('success', 'Exam submitted successfully!');
        } catch (\Exception $e) {
            DB::rollback();
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to submit exam: ' . $e->getMessage()], 500);
            }
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
     * Get exam status for student (API endpoint for real-time updates)
     */
    public function getExamStatus(Exam $exam)
    {
        $studentId = Auth::id();
        // Check if student is enrolled in the course
        $isEnrolled = Auth::user()->enrollments->contains('course_id', $exam->course_id);
        if (!$isEnrolled) {
            return response()->json(['error' => 'Not enrolled in this course'], 403);
        }
        $examStatus = $exam->getStatusForStudent($studentId);
        $existingAttempt = $exam->getAttemptForStudent($studentId);
        return response()->json([
            'exam_id' => $exam->id,
            'status' => $examStatus,
            'can_take' => $exam->canStudentTake($studentId),
            'has_attempt' => (bool) $existingAttempt,
            'attempt_status' => $existingAttempt ? $existingAttempt->status : null,
            'time_until_start' => $examStatus === 'not_started' ? $exam->getTimeUntilStart($studentId) : 0,
            'time_until_end' => $examStatus === 'available' ? $exam->getTimeUntilEnd($studentId) : 0,
        ]);
    }

    /**
     * Get remaining time for an exam attempt (API endpoint)
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
        $remainingSeconds = $attempt->getRemainingSeconds();
        // Auto-submit if time expired
        if ($remainingSeconds <= 0) {
            $this->autoSubmitExam($attempt);
            return response()->json(['expired' => true, 'remaining_seconds' => 0]);
        }
        return response()->json(['remaining_seconds' => $remainingSeconds]);
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
