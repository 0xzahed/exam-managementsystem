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

class AssignmentController extends Controller
{
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
            // Handle file uploads
            $instructorFiles = [];
            if ($request->hasFile('instructor_files')) {
                foreach ($request->file('instructor_files') as $file) {
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('assignments/instructor_files', $filename, 'public');
                    $instructorFiles[] = [
                        'original_name' => $file->getClientOriginalName(),
                        'stored_name' => $filename,
                        'path' => $path,
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType()
                    ];
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
            
            // Send email notifications to enrolled students if notify_on_assign is checked and status is published
            if ($validatedData['status'] === 'published' && $request->boolean('notify_on_assign')) {
                $this->sendAssignmentNotification($assignment);
            }
            
            DB::commit();
            
            $message = $validatedData['status'] === 'published' 
                ? 'Assignment published successfully!' 
                : 'Assignment saved as draft successfully!';
                
            return redirect()->route('assignments.index')->with('success', $message);
            
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
                return redirect()->route('assignments.index')->with('error', 'You are not authorized to view this assignment.');
            }
        } else {
            // Check if student is enrolled in the course
            $isEnrolled = DB::table('course_enrollments')
                ->where('course_id', $assignment->course_id)
                ->where('student_id', $user->id)
                ->exists();
                
            if (!$isEnrolled || $assignment->status !== 'published') {
                return redirect()->route('assignments.index')->with('error', 'You are not authorized to view this assignment.');
            }
        }
        
        return view('assignments.show', compact('assignment'));
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
            return redirect()->route('assignments.index')->with('error', 'You are not authorized to edit this assignment.');
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
            return redirect()->route('assignments.index')->with('error', 'You are not authorized to update this assignment.');
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
            return redirect()->route('assignments.index')->with('error', 'You are not authorized to delete this assignment.');
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
            
            return redirect()->route('assignments.index')->with('success', 'Assignment deleted successfully!');
            
        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->route('assignments.index')->with('error', 'Failed to delete assignment. Please try again.');
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
                ->join('users', 'course_enrollments.student_id', '=', 'users.id')
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
            return redirect()->route('assignments.index')->with('error', 'You are not authorized to view submissions for this assignment.');
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
            return redirect()->route('assignments.index')->with('error', 'Only students can submit assignments.');
        }
        
        // Check if student is enrolled in the course
        $isEnrolled = DB::table('course_enrollments')
            ->where('course_id', $assignment->course_id)
            ->where('student_id', $user->id)
            ->exists();
            
        if (!$isEnrolled) {
            return redirect()->route('assignments.index')->with('error', 'You are not enrolled in this course.');
        }
        
        // Check if assignment is published
        if ($assignment->status !== 'published') {
            return redirect()->route('assignments.show', $assignment)->with('error', 'This assignment is not available for submission.');
        }
        
        // Validate submission
        $rules = [
            'comments' => 'nullable|string|max:1000',
        ];
        
        if ($assignment->submission_type === 'file' || $assignment->submission_type === 'both') {
            $rules['submission_files'] = 'nullable|array|max:5';
            $rules['submission_files.*'] = 'file|max:10240'; // 10MB per file
        }
        
        if ($assignment->submission_type === 'text' || $assignment->submission_type === 'both') {
            $rules['submission_text'] = 'nullable|string|max:10000';
        }
        
        $validatedData = $request->validate($rules);
        
        DB::beginTransaction();
        
        try {
            // Handle file uploads
            $submissionFiles = [];
            if ($request->hasFile('submission_files')) {
                foreach ($request->file('submission_files') as $file) {
                    $filename = time() . '_' . $user->id . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('assignments/submissions', $filename, 'public');
                    $submissionFiles[] = [
                        'name' => $file->getClientOriginalName(),
                        'stored_name' => $filename,
                        'path' => $path,
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType()
                    ];
                }
            }
            
            // Check for existing submission
            $existingSubmission = DB::table('assignment_submissions')
                ->where('assignment_id', $assignment->id)
                ->where('student_id', $user->id)
                ->first();
                
            if ($existingSubmission) {
                // Update existing submission
                DB::table('assignment_submissions')
                    ->where('id', $existingSubmission->id)
                    ->update([
                        'content' => $validatedData['submission_text'] ?? null,
                        'submission_files' => !empty($submissionFiles) ? json_encode($submissionFiles) : null,
                        'comments' => $validatedData['comments'] ?? null,
                        'submitted_at' => now(),
                        'updated_at' => now(),
                    ]);
            } else {
                // Create new submission
                DB::table('assignment_submissions')->insert([
                    'assignment_id' => $assignment->id,
                    'student_id' => $user->id,
                    'content' => $validatedData['submission_text'] ?? null,
                    'submission_files' => !empty($submissionFiles) ? json_encode($submissionFiles) : null,
                    'comments' => $validatedData['comments'] ?? null,
                    'submitted_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('assignments.show', $assignment)->with('success', 'Assignment submitted successfully!');
            
        } catch (\Exception $e) {
            DB::rollback();
            
            // Clean up uploaded files
            if (!empty($submissionFiles)) {
                foreach ($submissionFiles as $file) {
                    Storage::disk('public')->delete($file['path']);
                }
            }
            
            return redirect()->back()->with('error', 'Failed to submit assignment. Please try again.');
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
            return response()->json(['success' => false, 'message' => 'Submission not found'], 404);
        }
        
        $assignment = Assignment::find($submission->assignment_id);
        $user = Auth::user();
        
        // Only the instructor who created the assignment can grade
        if ($user->role !== 'instructor' || $assignment->instructor_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        
        // Validate grade is within assignment marks
        if ($request->grade > $assignment->marks) {
            return response()->json(['success' => false, 'message' => 'Grade cannot exceed assignment marks'], 400);
        }
        
        try {
            DB::table('assignment_submissions')
                ->where('id', $submissionId)
                ->update([
                    'grade' => $request->grade,
                    'feedback' => $request->feedback,
                    'graded_at' => now(),
                    'graded_by' => $user->id,
                    'updated_at' => now()
                ]);
                
            return response()->json(['success' => true, 'message' => 'Grade saved successfully']);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to save grade'], 500);
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
            return redirect()->route('assignments.index')->with('error', 'Unauthorized');
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
}
