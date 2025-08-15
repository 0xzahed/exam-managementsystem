<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\ExamAttempt;
use App\Models\ExamAnswer;
use App\Models\Course;
use App\Models\User;
use App\Models\Grade;
use App\Services\GoogleDriveService;

class InstructorExamController extends Controller
{
    protected $googleDriveService;

    public function __construct(GoogleDriveService $googleDriveService)
    {
        $this->googleDriveService = $googleDriveService;
    }

    /**
     * Helper method to validate exam questions data
     */
    private function validateExamQuestions($questions)
    {
        $errors = [];
        foreach ($questions as $index => $question) {
            $questionNumber = $index + 1;
            // Basic question validation
            if (empty(trim($question['question'] ?? ''))) {
                $errors[] = "Question {$questionNumber}: Question text is required.";
                continue;
            }
            if (!isset($question['points']) || $question['points'] < 1) {
                $errors[] = "Question {$questionNumber}: Points must be at least 1.";
                continue;
            }
            // Type-specific validation
            if ($question['type'] === 'mcq') {
                if (!isset($question['options']) || !is_array($question['options']) || count($question['options']) < 2) {
                    $errors[] = "Question {$questionNumber}: MCQ must have at least 2 options.";
                    continue;
                }
                $emptyOptions = 0;
                foreach ($question['options'] as $option) {
                    if (empty(trim($option))) {
                        $emptyOptions++;
                    }
                }
                if ($emptyOptions > 0) {
                    $errors[] = "Question {$questionNumber}: All MCQ options must be filled.";
                    continue;
                }
                if (empty($question['correct_answer'])) {
                    $errors[] = "Question {$questionNumber}: Correct answer must be selected for MCQ.";
                    continue;
                }
            }
        }
        return $errors;
    }

    /**
     * Helper method to validate cohorts data
     */
    private function validateCohorts($cohorts)
    {
        $errors = [];
        if (!$cohorts || !is_array($cohorts)) {
            return $errors;
        }
        foreach ($cohorts as $index => $cohort) {
            $cohortNumber = $index + 1;
            if (empty(trim($cohort['cohort_name'] ?? ''))) {
                $errors[] = "Cohort {$cohortNumber}: Name is required.";
                continue;
            }
            if (empty($cohort['start_time'])) {
                $errors[] = "Cohort {$cohortNumber}: Start time is required.";
                continue;
            }
            if (empty($cohort['end_time'])) {
                $errors[] = "Cohort {$cohortNumber}: End time is required.";
                continue;
            }
            if (!isset($cohort['student_ids']) || !is_array($cohort['student_ids']) || count($cohort['student_ids']) === 0) {
                $errors[] = "Cohort {$cohortNumber}: At least one student must be assigned.";
                continue;
            }
        }
        return $errors;
    }

    /**
     * Helper method to process and create exam questions
     */
    private function processExamQuestions($exam, $questions)
    {
        $totalPoints = 0;
        foreach ($questions as $index => $questionData) {
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
        return $totalPoints;
    }

    /**
     * Helper method to process exam cohorts
     */
    private function processExamCohorts($exam, $cohorts)
    {
        if (!$cohorts || !is_array($cohorts)) {
            return;
        }
        // Remove existing cohorts if updating
        $exam->cohorts()->delete();
        foreach ($cohorts as $cohortData) {
            $exam->cohorts()->create([
                'cohort_name' => $cohortData['cohort_name'],
                'description' => $cohortData['description'] ?? null,
                'start_time' => $cohortData['start_time'],
                'end_time' => $cohortData['end_time'],
                'student_ids' => $cohortData['student_ids'],
            ]);
        }
    }

    /**
     * Helper method to handle file attachments
     */
    private function processExamAttachments($exam, $request)
    {
        $attachments = $exam->attachments ?? [];
        if ($request->hasFile('attachments')) {
            $course = $exam->course;
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
    }

    /**
     * Display a listing of exams
     */
    public function index()
    {
        $instructorId = Auth::id();
        $exams = Exam::with(['course', 'questions', 'attempts'])
            ->where('instructor_id', $instructorId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Calculate pending grading count for notification
        $pendingGradingCount = 0;
        foreach ($exams as $exam) {
            $pendingGradingCount += $exam->attempts->whereIn('status', ['submitted', 'auto_submitted'])->count();
        }

        return view('exams.instructor.index', compact('exams', 'pendingGradingCount'));
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
        Log::info('Exam creation started', ['user_id' => Auth::id()]);
        try {
            // Basic Laravel validation first
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
                'attachments.*' => 'file|max:10240',
                'cohorts' => 'nullable|array',
                'cohorts.*.cohort_name' => 'required_with:cohorts|string|max:255',
                'cohorts.*.start_time' => 'required_with:cohorts|date',
                'cohorts.*.end_time' => 'required_with:cohorts|date|after:cohorts.*.start_time',
                'cohorts.*.student_ids' => 'required_with:cohorts|array|min:1',
            ]);

            // Use helper methods for detailed validation
            $questionErrors = $this->validateExamQuestions($request->questions);
            $cohortErrors = $this->validateCohorts($request->cohorts);
            if (!empty($questionErrors) || !empty($cohortErrors)) {
                $allErrors = array_merge($questionErrors, $cohortErrors);
                return back()->withErrors(['validation_errors' => $allErrors])->withInput();
            }

            Log::info('Validation passed for exam creation');
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
                'status' => 'published'
            ]);

            // Process questions using helper method
            $totalPoints = $this->processExamQuestions($exam, $request->questions);
            $exam->update(['total_points' => $totalPoints]);

            // Process attachments using helper method
            $this->processExamAttachments($exam, $request);

            // Process cohorts using helper method
            $this->processExamCohorts($exam, $request->cohorts);

            DB::commit();

            $successMessage = 'Exam "' . $exam->title . '" created successfully!';

            // Handle action buttons (moved logic from JS to controller)
            if ($request->has('create_another')) {
                return $this->flashSuccess($successMessage, 'instructor.exams.create');
            }
            if ($request->has('go_to_details')) {
                return $this->flashSuccess($successMessage, 'instructor.exams.show', [$exam]);
            }
            return $this->flashSuccess($successMessage, 'instructor.exams.index');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Exam creation failed', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Failed to create exam: ' . $e->getMessage()])->withInput();
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
        Log::info('Exam update started', ['exam_id' => $exam->id, 'user_id' => Auth::id()]);
        try {
            // Basic Laravel validation
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
                'questions.*.options' => 'required_if:questions.*.type,mcq|array|min:2',
                'questions.*.correct_answer' => 'required_if:questions.*.type,mcq|string',
                'attachments.*' => 'file|max:10240',
                'cohorts' => 'nullable|array',
                'cohorts.*.cohort_name' => 'required_with:cohorts|string|max:255',
                'cohorts.*.start_time' => 'required_with:cohorts|date',
                'cohorts.*.end_time' => 'required_with:cohorts|date|after:cohorts.*.start_time',
                'cohorts.*.student_ids' => 'required_with:cohorts|array|min:1',
            ]);

            // Use helper methods for detailed validation
            $questionErrors = $this->validateExamQuestions($request->questions);
            $cohortErrors = $this->validateCohorts($request->cohorts);
            if (!empty($questionErrors) || !empty($cohortErrors)) {
                $allErrors = array_merge($questionErrors, $cohortErrors);
                return redirect()->route('instructor.exams.edit', $exam)
                    ->withErrors(['validation_errors' => $allErrors])
                    ->withInput();
            }

            Log::info('Validation passed for exam update');
            DB::beginTransaction();

            // Update exam basic info
            $exam->update([
                'title' => $request->title,
                'description' => $request->description,
                'course_id' => $request->course_id,
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

            // Delete existing questions and process new ones using helper method
            $exam->questions()->delete();
            $totalPoints = $this->processExamQuestions($exam, $request->questions);
            $exam->update(['total_points' => $totalPoints]);

            // Process attachments using helper method
            $this->processExamAttachments($exam, $request);

            // Process cohorts using helper method
            $this->processExamCohorts($exam, $request->cohorts);

            DB::commit();
            Log::info('Exam update completed successfully', ['exam_id' => $exam->id]);

            // Handle action buttons (moved logic from JS to controller)
            if ($request->has('save_and_continue')) {
                return $this->flashSuccess('Exam updated successfully!', 'instructor.exams.edit', [$exam]);
            }
            return $this->flashSuccess('Exam updated successfully!', 'instructor.exams.index');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Exam update failed', ['exam_id' => $exam->id, 'error' => $e->getMessage()]);
            return redirect()->route('instructor.exams.edit', $exam)
                ->withErrors(['error' => 'Failed to update exam: ' . $e->getMessage()])
                ->withInput();
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Exam update validation failed', ['exam_id' => $exam->id, 'errors' => $e->errors()]);
            throw $e;
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
        // Calculate total current points and max points
        $totalCurrentPoints = $attempt->examAnswers->sum('points') ?? 0;
        $totalMaxPoints = $exam->questions->sum('points') ?? 0;
        return view('exams.instructor.view-attempt', compact('exam', 'attempt', 'totalCurrentPoints', 'totalMaxPoints'));
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
            'grades' => 'sometimes|array',
            'grades.*' => 'numeric|min:0',
            'questions' => 'sometimes|array',
            'questions.*' => 'numeric|min:0',
            'feedback' => 'nullable|string',
            'total_score' => 'required|numeric|min:0'
        ]);
        try {
            DB::beginTransaction();
            // Handle existing exam answers
            if ($request->has('grades')) {
                foreach ($request->grades as $answerId => $points) {
                    ExamAnswer::where('id', $answerId)
                        ->where('exam_attempt_id', $attempt->id)
                        ->update(['points_awarded' => $points]);
                }
            }
            // Handle direct question grading (when no answers exist)
            if ($request->has('questions')) {
                foreach ($request->questions as $questionId => $points) {
                    ExamAnswer::updateOrCreate([
                        'exam_attempt_id' => $attempt->id,
                        'exam_question_id' => $questionId,
                    ], [
                        'points_awarded' => $points,
                        'answer_text' => 'Graded manually - no answer recorded',
                        'is_correct' => $points > 0
                    ]);
                }
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
            Grade::updateOrCreate([
                'student_id' => $attempt->student_id,
                'course_id' => $exam->course_id,
                'gradeable_type' => 'App\Models\Exam',
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
    public function toggleStatus(Exam $exam)
    {
        $this->authorizeExamAccess($exam);
        $exam->update(['status' => $exam->status === 'published' ? 'draft' : 'published']);
        return back()->with('success', 'Exam status updated successfully.');
    }

    /**
     * Authorize exam access for instructor
     */
    private function authorizeExamAccess(Exam $exam)
    {
        if ($exam->instructor_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this exam.');
        }
    }

    private function flashSuccess($message, $route, $params = [])
    {
        return redirect()->route($route, $params)->with('success', $message);
    }

    private function flashError($message, $route, $params = [])
    {
        return redirect()->route($route, $params)->withErrors(['error' => $message]);
    }
}
