<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\User;
use App\Services\GoogleDriveService;

class AssignmentController extends Controller
{
    protected $googleDriveService;

    public function __construct(GoogleDriveService $googleDriveService)
    {
        $this->googleDriveService = $googleDriveService;
    }
    /**
     * Display a listing of assignments.
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->role === 'instructor') {
            // Get assignments created by this instructor
            $assignments = Assignment::with(['course'])
                ->where('instructor_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } else {
            // Get assignments for courses the student is enrolled in using DB query
            $enrolledCourseIds = DB::table('course_enrollments')
                ->where('user_id', $user->id)
                ->pluck('course_id');
                
            $assignments = Assignment::with(['course'])
                ->whereIn('course_id', $enrolledCourseIds)
                ->where('status', 'published')
                ->orderBy('due_date', 'asc')
                ->paginate(10);
        }
        
        return view('assignments.index', compact('assignments'));
    }

    /**
     * Show the form for creating a new assignment.
     */
    public function create()
    {
        $user = Auth::user();
        
        // Only instructors can create assignments
        if ($user->role !== 'instructor') {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to create assignments.');
        }
        
        // Get courses taught by this instructor
        $courses = Course::where('instructor_id', $user->id)->get();
        
        if ($courses->isEmpty()) {
            return redirect()->route('instructor.dashboard')->with('error', 'You need to create a course first before creating assignments.');
        }
        
        return view('assignments.create', compact('courses'));
    }

    /**
     * Store a newly created assignment in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Only instructors can create assignments
        if ($user->role !== 'instructor') {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to create assignments.');
        }
        
        // Validate the request
        $validatedData = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'assignment_type' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'instructions' => 'required|string',
            'assign_date' => 'required|date',
            'due_date' => 'required|date|after:assign_date',
            'submission_type' => 'required|in:both,file,text',
            'max_attempts' => 'required|integer|min:1|max:5',
            'allowed_file_types' => 'nullable|array',
            'allowed_file_types.*' => 'string',
            'allow_late_submission' => 'boolean',
            'notify_on_assign' => 'boolean',
            'marks' => 'required|integer|min:1|max:100',
            'grading_type' => 'required|in:points,percentage,letter',
            'grade_display' => 'required|in:immediately,after_due,manual',
            'assign_to' => 'required|string',
            'status' => 'required|in:draft,published',
            'instructor_files' => 'nullable|array',
            'instructor_files.*' => 'file|max:10240', // 10MB max per file
        ]);
        
        // Verify the instructor owns this course
        $course = Course::where('id', $validatedData['course_id'])
                       ->where('instructor_id', $user->id)
                       ->first();
                       
        if (!$course) {
            return redirect()->back()->with('error', 'You are not authorized to create assignments for this course.');
        }
        
        DB::beginTransaction();
        
        try {
            // Handle file uploads to Google Drive
            $instructorFiles = [];
            $googleDriveFolderId = '19E0Xyps8MjUF6c3efI5-XTUVU2LRO3WH'; // Your specified Google Drive folder
            
            if ($request->hasFile('instructor_files')) {
                foreach ($request->file('instructor_files') as $file) {
                    try {
                        // Upload to Google Drive
                        Log::info('Attempting Google Drive upload', [
                            'assignment_title' => $validatedData['title'],
                            'file_name' => $file->getClientOriginalName(),
                            'target_folder_id' => $googleDriveFolderId
                        ]);
                        
                        $uploadResult = $this->googleDriveService->uploadFile(
                            $file,
                            $validatedData['title'] . '_' . $file->getClientOriginalName(),
                            $googleDriveFolderId
                        );
                        
                        Log::info('Google Drive upload result', [
                            'assignment_title' => $validatedData['title'],
                            'upload_result' => $uploadResult
                        ]);
                        
                        // Also store locally as backup
                        $filename = time() . '_' . $file->getClientOriginalName();
                        $path = $file->storeAs('assignments/instructor_files', $filename, 'public');
                        
                        $instructorFiles[] = [
                            'original_name' => $file->getClientOriginalName(),
                            'stored_name' => $filename,
                            'path' => $path,
                            'size' => $file->getSize(),
                            'mime_type' => $file->getMimeType(),
                            'google_drive_id' => $uploadResult['id'] ?? null,
                            'google_drive_link' => $uploadResult['webViewLink'] ?? null
                        ];
                        
                        Log::info('Assignment file uploaded to Google Drive', [
                            'assignment_title' => $validatedData['title'],
                            'file_name' => $file->getClientOriginalName(),
                            'google_drive_id' => $uploadResult['id'] ?? null
                        ]);
                        
                    } catch (\Exception $e) {
                        Log::error('Failed to upload assignment file to Google Drive', [
                            'assignment_title' => $validatedData['title'],
                            'file_name' => $file->getClientOriginalName(),
                            'error' => $e->getMessage()
                        ]);
                        
                        // Fall back to local storage only
                        $filename = time() . '_' . $file->getClientOriginalName();
                        $path = $file->storeAs('assignments/instructor_files', $filename, 'public');
                        $instructorFiles[] = [
                            'original_name' => $file->getClientOriginalName(),
                            'stored_name' => $filename,
                            'path' => $path,
                            'size' => $file->getSize(),
                            'mime_type' => $file->getMimeType(),
                            'google_drive_error' => $e->getMessage()
                        ];
                    }
                }
            }
            
            // Create the assignment
            $assignment = Assignment::create([
                'instructor_id' => $user->id,
                'course_id' => $validatedData['course_id'],
                'title' => $validatedData['title'],
                'assignment_type' => $validatedData['assignment_type'],
                'short_description' => $validatedData['short_description'],
                'instructions' => $validatedData['instructions'],
                'assign_date' => $validatedData['assign_date'],
                'due_date' => $validatedData['due_date'],
                'submission_type' => $validatedData['submission_type'],
                'max_attempts' => $validatedData['max_attempts'],
                'allowed_file_types' => $validatedData['allowed_file_types'] ? json_encode($validatedData['allowed_file_types']) : null,
                'allow_late_submission' => $request->boolean('allow_late_submission'),
                'notify_on_assign' => $request->boolean('notify_on_assign'),
                'marks' => $validatedData['marks'],
                'grading_type' => $validatedData['grading_type'],
                'grade_display' => $validatedData['grade_display'],
                'assign_to' => $validatedData['assign_to'],
                'status' => $validatedData['status'],
                'instructor_files' => !empty($instructorFiles) ? json_encode($instructorFiles) : null,
                'limit_attempts' => $request->boolean('limit_attempts'),
                'notify_on_submission' => $request->boolean('notify_on_submission'),
                'send_reminders' => $request->boolean('send_reminders'),
                'notify_late_submission' => $request->boolean('notify_late_submission'),
            ]);
            
            // Notify enrolled students via email and in-app if requested
            if ($validatedData['status'] === 'published' && $request->boolean('notify_on_assign')) {
                // Fetch enrolled students
                $enrolledStudents = $course->students;
                foreach ($enrolledStudents as $student) {
                    $student->notify(new \App\Notifications\AssignmentCreated($assignment));
                }
            }
            
            DB::commit();
            
            $message = $validatedData['status'] === 'published' 
                ? 'Assignment published successfully!' 
                : 'Assignment saved as draft successfully!';
                
            return redirect()->route('instructor.assignments.index')->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            // Clean up uploaded files if there was an error
            if (!empty($instructorFiles)) {
                foreach ($instructorFiles as $file) {
                    Storage::disk('public')->delete($file['path']);
                }
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create assignment. Please try again.');
        }
    }

    /**
     * Display the specified assignment.
     */
    public function show($id)
    {
        $assignment = Assignment::with(['course', 'instructor'])->findOrFail($id);
        $user = Auth::user();
        
        // Check if user can view this assignment
        if ($user->role === 'instructor') {
            if ($assignment->instructor_id !== $user->id) {
                return redirect()->route('instructor.assignments.index')->with('error', 'You are not authorized to view this assignment.');
            }
        } else {
            // Check if student is enrolled in the course
            $isEnrolled = DB::table('course_enrollments')
                ->where('course_id', $assignment->course_id)
                ->where('user_id', $user->id)
                ->exists();
                
            if (!$isEnrolled || $assignment->status !== 'published') {
                return redirect()->route('instructor.assignments.index')->with('error', 'You are not authorized to view this assignment.');
            }
        }
        
        // Get user submission if student
        $userSubmission = null;
        if ($user->role === 'student') {
            $userSubmission = \App\Models\AssignmentSubmission::where('assignment_id', $assignment->id)
                ->where('student_id', $user->id)
                ->first();
        }
        
        return view('assignments.show', compact('assignment', 'userSubmission'));
    }

    /**
     * Show the form for editing the specified assignment.
     */
    public function edit($id)
    {
        $assignment = Assignment::findOrFail($id);
        $user = Auth::user();
        
        // Only the instructor who created the assignment can edit it
        if ($user->role !== 'instructor' || $assignment->instructor_id !== $user->id) {
            return redirect()->route('instructor.assignments.index')->with('error', 'You are not authorized to edit this assignment.');
        }
        
        // Get courses taught by this instructor
        $courses = Course::where('instructor_id', $user->id)->get();
        
        return view('assignments.edit', compact('assignment', 'courses'));
    }

    /**
     * Update the specified assignment in storage.
     */
    public function update(Request $request, $id)
    {
        $assignment = Assignment::findOrFail($id);
        $user = Auth::user();
        
        // Only the instructor who created the assignment can update it
        if ($user->role !== 'instructor' || $assignment->instructor_id !== $user->id) {
            return redirect()->route('instructor.assignments.index')->with('error', 'You are not authorized to update this assignment.');
        }
        
        // Validate the request (same validation as store method)
        $validatedData = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'assignment_type' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'instructions' => 'required|string',
            'assign_date' => 'required|date',
            'due_date' => 'required|date|after:assign_date',
            'submission_type' => 'required|in:both,file,text',
            'max_attempts' => 'required|integer|min:1|max:10',
            'allowed_file_types' => 'nullable|array',
            'allowed_file_types.*' => 'string',
            'allow_late_submission' => 'boolean',
            'notify_on_assign' => 'boolean',
            'marks' => 'required|integer|min:1|max:1000',
            'grading_type' => 'required|in:points,percentage,letter',
            'grade_display' => 'required|in:immediately,after_due,manual',
            'assign_to' => 'required|string',
            'status' => 'required|in:draft,published',
            'instructor_files' => 'nullable|array',
            'instructor_files.*' => 'file|max:10240',
        ]);
        
        DB::beginTransaction();
        
        try {
            // Handle new file uploads
            $existingFiles = $assignment->instructor_files ? json_decode($assignment->instructor_files, true) : [];
            $newFiles = $existingFiles;
            
            if ($request->hasFile('instructor_files')) {
                foreach ($request->file('instructor_files') as $file) {
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('assignments/instructor_files', $filename, 'public');
                    $newFiles[] = [
                        'original_name' => $file->getClientOriginalName(),
                        'stored_name' => $filename,
                        'path' => $path,
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType()
                    ];
                }
            }
            
            // Update the assignment
            $assignment->update([
                'course_id' => $validatedData['course_id'],
                'title' => $validatedData['title'],
                'assignment_type' => $validatedData['assignment_type'],
                'short_description' => $validatedData['short_description'],
                'instructions' => $validatedData['instructions'],
                'assign_date' => $validatedData['assign_date'],
                'due_date' => $validatedData['due_date'],
                'submission_type' => $validatedData['submission_type'],
                'max_attempts' => $validatedData['max_attempts'],
                'allowed_file_types' => $validatedData['allowed_file_types'] ? json_encode($validatedData['allowed_file_types']) : null,
                'allow_late_submission' => $request->boolean('allow_late_submission'),
                'notify_on_assign' => $request->boolean('notify_on_assign'),
                'marks' => $validatedData['marks'],
                'grading_type' => $validatedData['grading_type'],
                'grade_display' => $validatedData['grade_display'],
                'assign_to' => $validatedData['assign_to'],
                'status' => $validatedData['status'],
                'instructor_files' => !empty($newFiles) ? json_encode($newFiles) : null,
                'limit_attempts' => $request->boolean('limit_attempts'),
                'notify_on_submission' => $request->boolean('notify_on_submission'),
                'send_reminders' => $request->boolean('send_reminders'),
                'notify_late_submission' => $request->boolean('notify_late_submission'),
            ]);
            
            DB::commit();
            
            $message = $validatedData['status'] === 'published' 
                ? 'Assignment updated and published successfully!' 
                : 'Assignment updated and saved as draft successfully!';
                
            return redirect()->route('assignments.show', $assignment->id)->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update assignment. Please try again.');
        }
    }

    /**
     * Remove the specified assignment from storage.
     */
    public function destroy($id)
    {
        $assignment = Assignment::findOrFail($id);
        $user = Auth::user();
        
        // Only the instructor who created the assignment can delete it
        if ($user->role !== 'instructor' || $assignment->instructor_id !== $user->id) {
            return redirect()->route('instructor.assignments.index')->with('error', 'You are not authorized to delete this assignment.');
        }
        
        DB::beginTransaction();
        
        try {
            // Delete associated files
            if ($assignment->instructor_files) {
                $files = json_decode($assignment->instructor_files, true);
                foreach ($files as $file) {
                    Storage::disk('public')->delete($file['path']);
                }
            }
            
            // Delete the assignment
            $assignment->delete();
            
            DB::commit();
            
            return redirect()->route('instructor.assignments.index')->with('success', 'Assignment deleted successfully!');
            
        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->route('instructor.assignments.index')->with('error', 'Failed to delete assignment. Please try again.');
        }
    }

    /**
     * Send assignment notification to enrolled students
     */
    private function sendAssignmentNotification($assignment)
    {
        try {
            // Get all students enrolled in the course
            $enrolledStudents = DB::table('course_enrollments')
                ->join('users', 'course_enrollments.user_id', '=', 'users.id')
                ->where('course_enrollments.course_id', $assignment->course_id)
                ->select('users.*')
                ->get();

            $instructorEmail = 'zahed01x@gmail.com';
            $subject = "New Assignment: {$assignment->title}";
            
            foreach ($enrolledStudents as $student) {
                $message = "
                    <h2>New Assignment Notification</h2>
                    <p>Dear {$student->first_name} {$student->last_name},</p>
                    <p>A new assignment has been published in your course:</p>
                    <ul>
                        <li><strong>Course:</strong> {$assignment->course->title}</li>
                        <li><strong>Assignment:</strong> {$assignment->title}</li>
                        <li><strong>Due Date:</strong> {$assignment->formatted_due_date}</li>
                        <li><strong>Total Marks:</strong> {$assignment->marks}</li>
                    </ul>
                    <p><strong>Instructions:</strong></p>
                    <p>" . nl2br(e($assignment->instructions)) . "</p>
                    <p>Please log into the system to view the complete assignment details and submit your work.</p>
                    <p>Best regards,<br>InsightEdu Team</p>
                ";
                
                // Send email using PHP mail function
                $headers = "From: InsightEdu <{$instructorEmail}>\r\n";
                $headers .= "Reply-To: {$instructorEmail}\r\n";
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                
                mail($student->email, $subject, $message, $headers);
            }
        } catch (\Exception $e) {
            // Log error but don't fail the assignment creation
            Log::error('Failed to send assignment notification: ' . $e->getMessage());
        }
    }

    /**
     * Show assignment submissions for instructors
     */
    public function submissions($id)
    {
        $assignment = Assignment::with(['course', 'submissions.student'])->findOrFail($id);
        $user = Auth::user();
        
        // Only the instructor who created the assignment can view submissions
        if ($user->role !== 'instructor' || $assignment->instructor_id !== $user->id) {
            return redirect()->route('instructor.assignments.index')->with('error', 'You are not authorized to view submissions for this assignment.');
        }
        
        return view('assignments.submissions', compact('assignment'));
    }

    /**
     * Process student assignment submission
     */
    public function processSubmission(Request $request, $id)
    {
        $assignment = Assignment::findOrFail($id);
        $user = Auth::user();
        
        // Only students can submit assignments
        if ($user->role !== 'student') {
            return redirect()->route('instructor.assignments.index')->with('error', 'Only students can submit assignments.');
        }
        
        // Check if student is enrolled in the course
        $isEnrolled = DB::table('course_enrollments')
            ->where('course_id', $assignment->course_id)
            ->where('user_id', $user->id)
            ->exists();
            
        if (!$isEnrolled) {
            return redirect()->route('instructor.assignments.index')->with('error', 'You are not enrolled in this course.');
        }
        
        // Check if assignment is published
        if ($assignment->status !== 'published') {
            return redirect()->route('assignments.show', $assignment)->with('error', 'This assignment is not available for submission.');
        }
        
        // Check attempt limits
        $existingSubmission = DB::table('assignment_submissions')
            ->where('assignment_id', $assignment->id)
            ->where('student_id', $user->id)
            ->first();
            
        $currentAttempts = $existingSubmission ? ($existingSubmission->attempt_number ?? 1) : 0;
        $maxAttempts = $assignment->max_attempts ?? 3;
        
        if ($currentAttempts >= $maxAttempts) {
            return redirect()->route('assignments.show', $assignment)
                ->with('error', 'You have reached the maximum number of submission attempts (' . $maxAttempts . ') for this assignment.');
        }
        
        // Validate submission
        $rules = [
            'comments' => 'nullable|string|max:1000',
        ];
        
        if ($assignment->submission_type === 'file') {
            // File submission only - file is required
            $rules['submission_files'] = 'required|array|min:1|max:5';
            $rules['submission_files.*'] = 'file|max:10240'; // 10MB per file
        } elseif ($assignment->submission_type === 'text') {
            // Text submission only - text is required
            $rules['submission_text'] = 'required|string|max:10000';
        } elseif ($assignment->submission_type === 'both') {
            // Both allowed - at least one is required
            $rules['submission_files'] = 'nullable|array|max:5';
            $rules['submission_files.*'] = 'file|max:10240'; // 10MB per file
            $rules['submission_text'] = 'nullable|string|max:10000';
        }
        
        $validatedData = $request->validate($rules);
        
        // Additional validation for 'both' type - ensure at least one is provided
        if ($assignment->submission_type === 'both') {
            $hasFile = $request->hasFile('submission_files') && count($request->file('submission_files')) > 0;
            $hasText = !empty($request->submission_text);
            
            if (!$hasFile && !$hasText) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['submission' => 'Please provide either a file or text submission.']);
            }
        }
        
        DB::beginTransaction();
        
        try {

            // Handle Google Drive file upload
            $googleDriveFileId = null;
            $googleDriveUrl = null;
            $submissionFiles = [];
            
            if ($request->hasFile('submission_files') && count($request->file('submission_files')) > 0) {
                $file = $request->file('submission_files')[0]; // Take first file only
                
                // Get existing file ID for replacement
                $existingFileId = $existingSubmission ? $existingSubmission->google_drive_file_id : null;
                
                // Upload to Google Drive
                $uploadResult = $this->googleDriveService->uploadAssignmentSubmission(
                    $file,
                    $user->name,
                    $assignment->title,
                    $existingFileId
                );
                
                if ($uploadResult) {
                    $googleDriveFileId = $uploadResult['id'];
                    $googleDriveUrl = $uploadResult['url'];
                    
                    $submissionFiles = [
                        [
                            'name' => $file->getClientOriginalName(),
                            'google_drive_id' => $googleDriveFileId,
                            'google_drive_url' => $googleDriveUrl,
                            'size' => $file->getSize(),
                            'mime_type' => $file->getMimeType()
                        ]
                    ];
                    
                    Log::info('Assignment submission uploaded to Google Drive', [
                        'student_id' => $user->id,
                        'assignment_id' => $assignment->id,
                        'file_id' => $googleDriveFileId,
                        'is_resubmission' => !empty($existingFileId)
                    ]);
                } else {
                    throw new \Exception('Failed to upload file to Google Drive');
                }
            }
                
            if ($existingSubmission) {
                // Update existing submission and increment attempt number
                $newAttemptNumber = $currentAttempts + 1;
                $updateData = [
                    'content' => $validatedData['submission_text'] ?? null,
                    'comments' => $validatedData['comments'] ?? null,
                    'submitted_at' => now(),
                    'attempt_number' => $newAttemptNumber,
                    'updated_at' => now(),
                ];
                
                if ($googleDriveFileId) {
                    $updateData['google_drive_file_id'] = $googleDriveFileId;
                    $updateData['google_drive_url'] = $googleDriveUrl;
                    $updateData['submission_files'] = json_encode($submissionFiles);
                }
                
                DB::table('assignment_submissions')
                    ->where('id', $existingSubmission->id)
                    ->update($updateData);
                    
                $message = 'Assignment resubmitted successfully! This is attempt ' . $newAttemptNumber . ' of ' . $maxAttempts . '.';
            } else {
                // Create new submission
                $insertData = [
                    'assignment_id' => $assignment->id,
                    'student_id' => $user->id,
                    'content' => $validatedData['submission_text'] ?? null,
                    'comments' => $validatedData['comments'] ?? null,
                    'submitted_at' => now(),
                    'attempt_number' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
                if ($googleDriveFileId) {
                    $insertData['google_drive_file_id'] = $googleDriveFileId;
                    $insertData['google_drive_url'] = $googleDriveUrl;
                    $insertData['submission_files'] = json_encode($submissionFiles);
                }
                
                DB::table('assignment_submissions')->insert($insertData);
                
                $message = 'Assignment submitted successfully! This is attempt 1 of ' . $maxAttempts . '.';
            }
            
            DB::commit();
            
            return redirect()->route('assignments.show', $assignment)->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Assignment submission failed: ' . $e->getMessage());
            
            return redirect()->route('assignments.show', $assignment)
                ->with('error', 'Failed to submit assignment. Please try again.');
        }
    }

    /**
     * Grade a submission
     */
    public function gradeSubmission(Request $request, $submissionId)
    {
        $request->validate([
            'grade' => 'required|numeric|min:0',
            'feedback' => 'nullable|string|max:1000'
        ]);
        
        $submission = DB::table('assignment_submissions')->where('id', $submissionId)->first();
        if (!$submission) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Submission not found'], 404);
            }
            return back()->withErrors(['error' => 'Submission not found']);
        }
        
        $assignment = Assignment::find($submission->assignment_id);
        $user = Auth::user();
        
        // Only the instructor who created the assignment can grade
        if ($user->role !== 'instructor' || $assignment->instructor_id !== $user->id) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            return back()->withErrors(['error' => 'Unauthorized to grade this submission']);
        }
        
        // Validate grade is within assignment marks
        if ($request->grade > $assignment->marks) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Grade cannot exceed assignment marks'], 400);
            }
            return back()->withErrors(['error' => 'Grade cannot exceed assignment marks (' . $assignment->marks . ')']);
        }
        
        try {
            // Update assignment submission
            DB::table('assignment_submissions')
                ->where('id', $submissionId)
                ->update([
                    'grade' => $request->grade,
                    'feedback' => $request->feedback,
                    'graded_at' => now(),
                    'graded_by' => $user->id,
                    'updated_at' => now()
                ]);
            
            // Create or update grade record for gradebook
            $percentage = round(($request->grade / $assignment->marks) * 100, 2);
            $letterGrade = $this->calculateLetterGrade($percentage);
            
            \App\Models\Grade::updateOrCreate(
                [
                    'student_id' => $submission->student_id,
                    'course_id' => $assignment->course_id,
                    'instructor_id' => $user->id,
                    'gradeable_type' => \App\Models\AssignmentSubmission::class,
                    'gradeable_id' => $submissionId
                ],
                [
                    'points_earned' => $request->grade,
                    'total_points' => $assignment->marks,
                    'score' => $percentage,
                    'letter_grade' => $letterGrade,
                    'feedback' => $request->feedback,
                    'graded_at' => now(),
                    'grade_type' => 'assignment'
                ]
            );
            
            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Grade saved successfully']);
            }
            
            return redirect()->route('instructor.assignments.submissions', $assignment->id)
                ->with('success', 'Grade saved successfully for submission #' . $submissionId);
            
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to save grade'], 500);
            }
            return back()->withErrors(['error' => 'Failed to save grade: ' . $e->getMessage()]);
        }
    }

    /**
     * Reset attempts for a specific student (instructor action)
     */
    public function resetStudentAttempts(Request $request, $assignmentId, $studentId)
    {
        $assignment = Assignment::findOrFail($assignmentId);
        $user = Auth::user();
        
        // Only the instructor who created the assignment can reset attempts
        if ($user->role !== 'instructor' || $assignment->instructor_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        
        try {
            $success = $assignment->resetStudentAttempts($studentId);
            
            if ($success) {
                return response()->json([
                    'success' => true, 
                    'message' => 'Student attempts have been reset successfully.'
                ]);
            } else {
                return response()->json([
                    'success' => false, 
                    'message' => 'No submission found for this student.'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Failed to reset attempts: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Increase max attempts for an assignment (instructor action)
     */
    public function increaseMaxAttempts(Request $request, $assignmentId)
    {
        $assignment = Assignment::findOrFail($assignmentId);
        $user = Auth::user();
        
        // Only the instructor who created the assignment can increase attempts
        if ($user->role !== 'instructor' || $assignment->instructor_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'new_max_attempts' => 'required|integer|min:1|max:10'
        ]);
        
        try {
            $success = $assignment->increaseMaxAttempts($request->new_max_attempts);
            
            if ($success) {
                return response()->json([
                    'success' => true, 
                    'message' => 'Maximum attempts increased to ' . $request->new_max_attempts . '.'
                ]);
            } else {
                return response()->json([
                    'success' => false, 
                    'message' => 'New max attempts must be greater than current value.'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Failed to increase attempts: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Bulk grade multiple submissions
     */
    public function bulkGrade(Request $request, $id)
    {
        $request->validate([
            'grade' => 'required|numeric|min:0'
        ]);
        
        $assignment = Assignment::findOrFail($id);
        $user = Auth::user();
        
        if ($user->role !== 'instructor' || $assignment->instructor_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        
        if ($request->grade > $assignment->marks) {
            return response()->json(['success' => false, 'message' => 'Grade cannot exceed assignment marks'], 400);
        }
        
        try {
            $updated = DB::table('assignment_submissions')
                ->where('assignment_id', $id)
                ->whereNull('grade')
                ->update([
                    'grade' => $request->grade,
                    'graded_at' => now(),
                    'graded_by' => $user->id,
                    'updated_at' => now()
                ]);
                
            return response()->json(['success' => true, 'count' => $updated, 'message' => "Successfully graded {$updated} submissions"]);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to apply bulk grades'], 500);
        }
    }

    /**
     * Update assignment total marks
     */
    public function updateMarks(Request $request, $id)
    {
        $request->validate([
            'new_marks' => 'required|numeric|min:1|max:1000',
            'update_option' => 'required|in:proportional,keep_grades'
        ]);
        
        $assignment = Assignment::findOrFail($id);
        $user = Auth::user();
        
        if ($user->role !== 'instructor' || $assignment->instructor_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        
        DB::beginTransaction();
        
        try {
            $oldMarks = $assignment->marks;
            $newMarks = $request->new_marks;
            
            // Update assignment marks
            $assignment->update(['marks' => $newMarks]);
            
            // Update existing grades proportionally if requested
            if ($request->update_option === 'proportional' && $oldMarks > 0) {
                $ratio = $newMarks / $oldMarks;
                
                DB::table('assignment_submissions')
                    ->where('assignment_id', $id)
                    ->whereNotNull('grade')
                    ->update([
                        'grade' => DB::raw("ROUND(grade * {$ratio}, 2)"),
                        'updated_at' => now()
                    ]);
            }
            
            DB::commit();
            
            return response()->json(['success' => true, 'message' => 'Assignment marks updated successfully']);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Failed to update marks'], 500);
        }
    }

    /**
     * Export submissions to CSV
     */
    public function exportSubmissions($id)
    {
        $assignment = Assignment::with(['submissions.student'])->findOrFail($id);
        $user = Auth::user();
        
        if ($user->role !== 'instructor' || $assignment->instructor_id !== $user->id) {
            return redirect()->route('instructor.assignments.index')->with('error', 'Unauthorized');
        }
        
        $filename = 'submissions_' . $assignment->id . '_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function() use ($assignment) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Student Name',
                'Student Email',
                'Submission Date',
                'Status',
                'Grade',
                'Percentage',
                'Feedback',
                'Comments'
            ]);
            
            // CSV data
            foreach ($assignment->submissions as $submission) {
                $status = $submission->submitted_at ? 'Submitted' : 'Not Submitted';
                $percentage = $submission->grade ? round(($submission->grade / $assignment->marks) * 100, 1) . '%' : '';
                
                fputcsv($file, [
                    $submission->student->first_name . ' ' . $submission->student->last_name,
                    $submission->student->email,
                    $submission->submitted_at ? $submission->submitted_at->format('Y-m-d H:i:s') : '',
                    $status,
                    $submission->grade ?? '',
                    $percentage,
                    $submission->feedback ?? '',
                    $submission->comments ?? ''
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Download submission files
     */
    public function downloadSubmission($submissionId)
    {
        $submission = DB::table('assignment_submissions')->where('id', $submissionId)->first();
        if (!$submission) {
            return redirect()->back()->with('error', 'Submission not found');
        }
        
        $assignment = Assignment::find($submission->assignment_id);
        $user = Auth::user();
        
        // Check authorization
        $canDownload = false;
        if ($user->role === 'instructor' && $assignment->instructor_id === $user->id) {
            $canDownload = true;
        } elseif ($user->role === 'student' && $submission->student_id === $user->id) {
            $canDownload = true;
        }
        
        if (!$canDownload) {
            return redirect()->back()->with('error', 'Unauthorized');
        }
        
        $files = json_decode($submission->submission_files, true);
        if (empty($files)) {
            return redirect()->back()->with('error', 'No files to download');
        }
        
        // If single file, download directly
        if (count($files) === 1) {
            $file = $files[0];
            $filePath = Storage::disk('public')->path($file['path']);
            return response()->download($filePath, $file['name']);
        }
        
        // Multiple files - create zip
        $zip = new \ZipArchive();
        $zipFileName = 'submission_' . $submissionId . '_' . time() . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);
        
        if (!file_exists(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0755, true);
        }
        
        if ($zip->open($zipPath, \ZipArchive::CREATE) === TRUE) {
            foreach ($files as $file) {
                $filePath = Storage::disk('public')->path($file['path']);
                if (file_exists($filePath)) {
                    $zip->addFile($filePath, $file['name']);
                }
            }
            $zip->close();
            
            return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
        }
        
        return redirect()->back()->with('error', 'Failed to create download archive');
    }

    /**
     * View a specific assignment submission
     */
    public function viewSubmission($submissionId)
    {
        $user = Auth::user();
        
        // Get submission with related data
        $submission = DB::table('assignment_submissions')
            ->join('assignments', 'assignment_submissions.assignment_id', '=', 'assignments.id')
            ->join('users', 'assignment_submissions.student_id', '=', 'users.id')
            ->join('courses', 'assignments.course_id', '=', 'courses.id')
            ->where('assignment_submissions.id', $submissionId)
            ->select(
                'assignment_submissions.*',
                'assignments.title as assignment_title',
                'assignments.marks as total_marks',
                'assignments.instructions',
                'assignments.due_date',
                'assignments.course_id',
                'users.first_name',
                'users.last_name',
                'users.email as student_email',
                'courses.title as course_title'
            )
            ->first();

        if (!$submission) {
            abort(404, 'Submission not found');
        }

        // Check authorization - only instructor who owns the assignment can view
        if ($user->role !== 'instructor') {
            abort(403, 'Unauthorized access');
        }

        // Check if instructor owns the course
        $course = \App\Models\Course::where('id', $submission->course_id)
                                   ->where('instructor_id', $user->id)
                                   ->first();
        
        if (!$course) {
            abort(403, 'You are not authorized to view this submission');
        }

        // Parse submission files if they exist
        $submissionFiles = [];
        if ($submission->submission_files) {
            $submissionFiles = json_decode($submission->submission_files, true) ?: [];
        }

        // Format dates
        $submission->submitted_at = \Carbon\Carbon::parse($submission->submitted_at);
        $submission->due_date = \Carbon\Carbon::parse($submission->due_date);
        
        // Check if submission was late
        $submission->is_late = $submission->submitted_at->gt($submission->due_date);
        
        // Calculate days late if applicable
        if ($submission->is_late) {
            $submission->days_late = $submission->submitted_at->diffInDays($submission->due_date);
        }

        return view('assignments.view-submission', compact('submission', 'submissionFiles'));
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
