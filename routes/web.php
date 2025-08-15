<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\InstructorExamController;
use App\Http\Controllers\StudentExamController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\GradebookController;
use App\Http\Controllers\StudentGradeController;
use App\Helpers\RoleRedirectHelper;

Route::get('/', function () {
    // If user is authenticated, redirect to their dashboard
    if (Auth::check()) {
        return RoleRedirectHelper::redirectToRoleDashboard();
    }
    
    return view('welcome');
})->name('home');

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Password Reset Routes
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// Email Verification Routes
Route::get('/verify-code', function(Request $request) {
    $email = $request->get('email') ?? session('email');
    
    if (!$email) {
        return redirect()->route('register')->withErrors(['email' => 'Email verification session expired. Please register again.']);
    }
    
    return view('auth.verify-code', compact('email'));
})->name('verify.code.form');
Route::post('/verify-code', [EmailController::class, 'verifyCode'])->name('verify.code.submit');
Route::post('/resend-code', [EmailController::class, 'resendCode'])->name('resend.code');

// Google OAuth Routes
Route::get('/auth/google/{role?}', [GoogleAuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
Route::get('/auth/google/logout', [GoogleAuthController::class, 'logoutFromGoogle'])->name('auth.google.logout');
Route::post('/auth/google/register', [GoogleAuthController::class, 'handleGoogleRegistration'])->name('auth.google.register');
Route::get('/auth/google/activate', function() {
    // Simple activation view that blocks redirect until user clicks Activate
    if (!session()->has('google_user_data')) {
        return redirect()->route('login')->with('error', 'Google session expired. Please try again.');
    }
    return view('auth.google-activate');
})->name('auth.google.activate');
Route::get('/verify-email/{token}', [GoogleAuthController::class, 'verifyEmail'])->name('email.verify');

// Additional OAuth Routes
Route::get('/google/register', [GoogleAuthController::class, 'redirectToGoogle'])->name('google.register');
Route::get('/github/register', function() { return redirect()->route('register'); })->name('github.register');
Route::get('/github/logout/register', function() { return redirect()->route('register'); })->name('github.logout.register');

// Protected Routes with Role Middleware
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return RoleRedirectHelper::redirectToRoleDashboard();
    })->name('dashboard');
});

Route::middleware(['auth', 'role:student'])->group(function () {
    Route::get('/student/dashboard', [DashboardController::class, 'dashboard'])->name('student.dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/instructor/dashboard', [DashboardController::class, 'instructorDashboard'])
         ->name('instructor.dashboard');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return 'Admin Dashboard';
    })->name('admin.dashboard');
});

    // Help & Support
    Route::get('/help', [App\Http\Controllers\HelpController::class, 'index'])->name('help.index');

    // Profile Settings (protected)
    Route::get('/profile/settings', [App\Http\Controllers\ProfileController::class, 'index'])
        ->middleware('auth')
        ->name('profile.settings');
    Route::put('/profile/settings', [App\Http\Controllers\ProfileController::class, 'update'])
        ->middleware('auth')
        ->name('profile.update');
    // Profile password change route
    Route::put('/profile/password', [App\Http\Controllers\ProfileController::class, 'password'])->name('profile.password');

    // Course Routes for Instructors
Route::middleware(['auth', 'role:instructor'])->group(function () {
    Route::get('/courses/create', [CourseController::class, 'create'])->name('courses.create');
    Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
    Route::get('/courses/{course}/edit', [CourseController::class, 'edit'])->name('courses.edit');
    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/manage', [CourseController::class, 'manage'])->name('courses.manage');
    // Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show'); // Removed - no matching view
    Route::put('/courses/{course}', [CourseController::class, 'update'])->name('courses.update');
    Route::delete('/courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');
    
    // Course students management
    Route::get('/courses/{course}/students', [CourseController::class, 'getStudents'])->name('courses.students');
    Route::delete('/courses/{course}/students/{student}', [CourseController::class, 'removeStudent'])->name('courses.students.remove');
    
    // Course materials
    Route::get('/courses/{course}/materials', [MaterialController::class, 'index'])->name('courses.materials');
    Route::post('/courses/{course}/materials', [MaterialController::class, 'store'])->name('courses.materials.store');
    Route::get('/courses/{course}/materials/{material}/download', [MaterialController::class, 'download'])->name('courses.materials.download');
    Route::delete('/courses/{course}/materials/{material}', [MaterialController::class, 'destroy'])->name('courses.materials.destroy');
    
    // Section management routes
    Route::post('/courses/{course}/sections', [MaterialController::class, 'createSection'])->name('courses.sections.create');
    Route::post('/courses/{course}/sections/store', [MaterialController::class, 'storeSection'])->name('courses.sections.store');
    
    // Material management routes  
    Route::get('/courses/{course}/materials/create', [MaterialController::class, 'create'])->name('courses.materials.create');
    Route::get('/courses/{course}/materials/{material}/edit', [MaterialController::class, 'edit'])->name('courses.materials.edit');
    Route::put('/courses/{course}/materials/{material}', [MaterialController::class, 'update'])->name('courses.materials.update');
    
    // Student management routes
    Route::get('/students', [DashboardController::class, 'index'])->name('students.index');
    
    // Assignment management routes (Instructor only)
    Route::get('/instructor/assignments', [AssignmentController::class, 'index'])->name('instructor.assignments.index');
    Route::get('/instructor/assignments/create', [AssignmentController::class, 'create'])->name('instructor.assignments.create');
    Route::post('/instructor/assignments', [AssignmentController::class, 'store'])->name('instructor.assignments.store');
    Route::get('/instructor/assignments/{assignment}', [AssignmentController::class, 'show'])->name('instructor.assignments.show');
    Route::get('/instructor/assignments/{assignment}/edit', [AssignmentController::class, 'edit'])->name('instructor.assignments.edit');
    Route::put('/instructor/assignments/{assignment}', [AssignmentController::class, 'update'])->name('instructor.assignments.update');
    Route::delete('/instructor/assignments/{assignment}', [AssignmentController::class, 'destroy'])->name('instructor.assignments.destroy');
    
    // Assignment submission management routes
    Route::get('/instructor/assignments/{assignment}/submissions', [AssignmentController::class, 'submissions'])->name('instructor.assignments.submissions');
    Route::get('/instructor/assignments/submissions/{submission}/view', [AssignmentController::class, 'viewSubmission'])->name('instructor.assignments.submissions.view');
    Route::get('/instructor/assignments/submissions/{submission}/download', [AssignmentController::class, 'downloadSubmission'])->name('instructor.assignments.submissions.download');
    Route::post('/instructor/assignments/submissions/{submission}/grade', [AssignmentController::class, 'gradeSubmission'])->name('instructor.assignments.submissions.grade');
    Route::post('/assignments/{assignment}/bulk-grade', [AssignmentController::class, 'bulkGrade'])->name('assignments.bulk-grade');
    Route::post('/assignments/{assignment}/update-marks', [AssignmentController::class, 'updateMarks'])->name('assignments.update-marks');
    Route::get('/assignments/{assignment}/export-submissions', [AssignmentController::class, 'exportSubmissions'])->name('assignments.export-submissions');
    
    // Attempt management routes
    Route::post('/assignments/{assignment}/students/{student}/reset-attempts', [AssignmentController::class, 'resetStudentAttempts'])->name('instructor.assignments.reset-attempts');
    Route::post('/assignments/{assignment}/increase-max-attempts', [AssignmentController::class, 'increaseMaxAttempts'])->name('instructor.assignments.increase-max-attempts');
});

Route::middleware(['auth', 'role:student'])->group(function () {
    // Student course enrollment routes
    Route::get('/student/enroll', [EnrollmentController::class, 'showEnrollment'])->name('student.courses.enroll');
    Route::post('/student/courses/{course}/enroll', [EnrollmentController::class, 'enrollInCourse'])->name('student.courses.enroll.submit');
    Route::get('/my-courses', [EnrollmentController::class, 'myCourses'])->name('student.courses.my');
    Route::get('/courses/{course}/details', [EnrollmentController::class, 'courseDetails'])->name('course.details');
    Route::delete('/my-courses/{course}/unenroll', [EnrollmentController::class, 'unenrollFromCourse'])->name('student.courses.unenroll');
    
    // Student assignment routes only
    Route::get('/assignments', [AssignmentController::class, 'index'])->name('assignments.index');
    Route::get('/assignments/{assignment}', [AssignmentController::class, 'show'])->name('assignments.show');
    Route::post('/assignments/{assignment}/submit', [AssignmentController::class, 'processSubmission'])->name('assignments.process-submission');
    Route::get('/assignments/submissions/{submission}/download', [AssignmentController::class, 'downloadSubmission'])->name('assignments.download');
    
    // Student announcement routes
    Route::get('/announcements', [AnnouncementController::class, 'index'])->name('student.announcements.index');
    Route::get('/announcements/{announcement}', [AnnouncementController::class, 'show'])->name('student.announcements.show');
});

// Test routes (remove in production)
Route::get('/test-auth', function() {
    return response()->json([
        'authenticated' => Auth::check(),
        'user' => Auth::check() ? [
            'id' => Auth::user()->id,
            'name' => Auth::user()->first_name . ' ' . Auth::user()->last_name,
            'role' => Auth::user()->role,
            'email' => Auth::user()->email
        ] : null,
        'session_id' => session()->getId(),
        'csrf_token' => csrf_token()
    ]);
});

// Debug route for student dashboard
Route::get('/debug-student', function() {
    $user = Auth::user();
    if (!$user) return 'No user authenticated';
    
    $enrolledCourses = $user->enrolledCourses()->with('instructor')->get();
    
    return response()->json([
        'user' => [
            'id' => $user->id,
            'name' => $user->first_name . ' ' . $user->last_name,
            'role' => $user->role,
            'email' => $user->email
        ],
        'enrolled_courses_count' => $enrolledCourses->count(),
        'enrolled_courses' => $enrolledCourses->toArray(),
        'available_courses_count' => \App\Models\Course::where('is_active', true)->count()
    ]);
})->middleware('auth');

// Debug route for enrollment controller
Route::get('/debug-enrollment', function() {
    $user = Auth::user();
    if (!$user) return 'No user authenticated';
    
    try {
        // Get enrolled courses with instructor info
        $enrolledCourses = $user->enrolledCourses()
            ->with(['instructor', 'assignments'])
            ->orderBy('course_enrollments.enrolled_at', 'desc')
            ->get();
        
        // Fix date format issue by converting string dates to Carbon instances
        $enrolledCourses->each(function ($course) {
            if (is_string($course->pivot->enrolled_at)) {
                $course->pivot->enrolled_at = \Carbon\Carbon::parse($course->pivot->enrolled_at);
            }
            if (is_string($course->pivot->created_at)) {
                $course->pivot->created_at = \Carbon\Carbon::parse($course->pivot->created_at);
            }
            if (is_string($course->pivot->updated_at)) {
                $course->pivot->updated_at = \Carbon\Carbon::parse($course->pivot->updated_at);
            }
        });
        
        return response()->json([
            'status' => 'success',
            'user_id' => $user->id,
            'user_role' => $user->role,
            'enrolled_courses' => $enrolledCourses->toArray()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ]);
    }
})->middleware(['auth', 'role:student']);

// Debug login route for testing
Route::get('/debug-login/{userId}', function($userId) {
    $user = \App\Models\User::find($userId);
    if (!$user) {
        return 'User not found';
    }
    
    Auth::login($user);
    
    return response()->json([
        'status' => 'logged in',
        'user' => [
            'id' => $user->id,
            'name' => $user->first_name . ' ' . $user->last_name,
            'email' => $user->email,
            'role' => $user->role
        ],
        'redirect_to' => $user->role === 'student' ? '/my-courses' : '/dashboard'
    ]);
})->name('debug.login');

// Test authentication status
Route::get('/test-session', function() {
    $user = Auth::user();
    return response()->json([
        'authenticated' => Auth::check(),
        'user' => $user ? [
            'id' => $user->id,
            'name' => $user->first_name . ' ' . $user->last_name,
            'email' => $user->email,
            'role' => $user->role
        ] : null,
        'session_id' => session()->getId()
    ]);
});

// Debug route for exam issues
Route::get('/debug-exam-creation', function() {
    $user = Auth::user();
    if (!$user) {
        return response()->json(['error' => 'Not authenticated']);
    }
    
    $data = [
        'user_id' => $user->id,
        'user_role' => $user->role,
        'is_instructor' => $user->role === 'instructor',
        'courses_count' => \App\Models\Course::where('instructor_id', $user->id)->count(),
        'exams_count' => \App\Models\Exam::where('instructor_id', $user->id)->count(),
    ];
    
    // Test exam creation
    try {
        $testExam = new \App\Models\Exam();
        $testExam->title = 'Test Exam';
        $testExam->description = 'Test Description';
        $testExam->course_id = \App\Models\Course::where('instructor_id', $user->id)->first()?->id ?? 1;
        $testExam->instructor_id = $user->id;
        $testExam->duration_minutes = 60;
        $testExam->start_time = now()->addHour();
        $testExam->end_time = now()->addHours(2);
        $testExam->status = 'draft';
        $testExam->save();
        
        $data['test_exam_created'] = true;
        $data['test_exam_id'] = $testExam->id;
        
        // Clean up test exam
        $testExam->delete();
        
    } catch (\Exception $e) {
        $data['test_exam_error'] = $e->getMessage();
    }
    
    return response()->json($data);
})->middleware('auth');

Route::get('/debug-exams', function() {
    if (!Auth::check()) {
        return response()->json(['error' => 'Not authenticated']);
    }
    
    $user = Auth::user();
    $data = [
        'user' => [
            'id' => $user->id,
            'role' => $user->role,
            'name' => $user->first_name . ' ' . $user->last_name
        ]
    ];
    
    if ($user->role === 'student') {
        $enrollments = $user->enrollments;
        $enrolledCourseIds = $enrollments ? $enrollments->pluck('course_id') : collect([]);
        
        $data['student_info'] = [
            'enrollments_count' => $enrollments ? $enrollments->count() : 0,
            'enrolled_course_ids' => $enrolledCourseIds->toArray(),
            'enrolled_courses' => $enrollments ? $enrollments->map(function($enrollment) {
                return [
                    'course_id' => $enrollment->course_id,
                    'course_title' => $enrollment->course ? $enrollment->course->title : 'No course'
                ];
            })->toArray() : []
        ];
        
        // Get all exams for enrolled courses
        $exams = collect([]);
        if ($enrolledCourseIds->isNotEmpty()) {
            $exams = \App\Models\Exam::with(['course'])
                ->whereIn('course_id', $enrolledCourseIds)
                ->get();
        }
        
        $data['exams'] = [
            'total_count' => $exams->count(),
            'published_count' => $exams->where('status', 'published')->count(),
            'draft_count' => $exams->where('status', 'draft')->count(),
            'exam_details' => $exams->map(function($exam) {
                return [
                    'id' => $exam->id,
                    'title' => $exam->title,
                    'status' => $exam->status,
                    'course_id' => $exam->course_id,
                    'course_title' => $exam->course ? $exam->course->title : 'No course'
                ];
            })->toArray()
        ];
    } elseif ($user->role === 'instructor') {
        $exams = \App\Models\Exam::with(['course'])
            ->where('instructor_id', $user->id)
            ->get();
            
        $data['instructor_info'] = [
            'exams_count' => $exams->count(),
            'published_count' => $exams->where('status', 'published')->count(),
            'draft_count' => $exams->where('status', 'draft')->count(),
            'exam_details' => $exams->map(function($exam) {
                return [
                    'id' => $exam->id,
                    'title' => $exam->title,
                    'status' => $exam->status,
                    'course_id' => $exam->course_id,
                    'course_title' => $exam->course ? $exam->course->title : 'No course'
                ];
            })->toArray()
        ];
    }
    
    return response()->json($data);
})->middleware('auth');

// Exam Management Routes

// Instructor Exam Routes
Route::prefix('instructor')->name('instructor.')->middleware(['auth', 'role:instructor'])->group(function () {
    Route::resource('exams', InstructorExamController::class);
    Route::get('exams/{exam}/debug', [InstructorExamController::class, 'debugEdit'])->name('exams.debug');
    Route::get('exams/{exam}/results', [InstructorExamController::class, 'results'])->name('exams.results');
    Route::get('exams/{exam}/attempts', [InstructorExamController::class, 'attempts'])->name('exams.attempts');
    Route::get('exams/{exam}/attempts/{attempt}', [InstructorExamController::class, 'viewAttempt'])->name('exams.view-attempt');
    Route::post('exams/{exam}/attempts/{attempt}/grade', [InstructorExamController::class, 'gradeAttempt'])->name('exams.grade-attempt');
    
    // Announcement routes
    Route::get('announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
    Route::get('announcements/create', [AnnouncementController::class, 'create'])->name('announcements.create');
    Route::post('announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
    Route::get('announcements/{announcement}', [AnnouncementController::class, 'show'])->name('announcements.show');
    Route::get('announcements/{announcement}/edit', [AnnouncementController::class, 'edit'])->name('announcements.edit');
    Route::put('announcements/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update');
    Route::delete('announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');
    Route::get('announcements/student/list', [AnnouncementController::class, 'getStudentAnnouncements'])->name('announcements.student.list');
    
    // Gradebook routes
    Route::get('gradebook', [GradebookController::class, 'index'])->name('gradebook.index');
    Route::get('gradebook/{course}', [GradebookController::class, 'show'])->name('gradebook.show');
    Route::post('gradebook/update-grade', [GradebookController::class, 'updateGrade'])->name('gradebook.update-grade');
    Route::post('gradebook/bulk-update', [GradebookController::class, 'bulkUpdateGrades'])->name('gradebook.bulk-update');
    Route::post('gradebook/grade-assignment/{submission}', [GradebookController::class, 'gradeAssignment'])->name('gradebook.grade-assignment');
    Route::post('gradebook/grade-exam/{attempt}', [GradebookController::class, 'gradeExam'])->name('gradebook.grade-exam');
    Route::get('gradebook/{course}/export', [GradebookController::class, 'exportGrades'])->name('gradebook.export');
});

// Student Exam Routes  
Route::prefix('student')->name('student.')->middleware(['auth', 'role:student'])->group(function () {
    Route::get('exams', [StudentExamController::class, 'index'])->name('exams.index');
    // Route::get('exams/{exam}', [StudentExamController::class, 'show'])->name('exams.show'); // Removed - no matching view
    Route::get('exams/status', [StudentExamController::class, 'getExamStatus'])->name('exams.status');
    Route::post('exams/{exam}/start', [StudentExamController::class, 'start'])->name('exams.start');
    Route::get('exams/{exam}/take', [StudentExamController::class, 'take'])->name('exams.take');
    Route::post('exams/{exam}/save-answer', [StudentExamController::class, 'saveAnswer'])->name('exams.save-answer');
    Route::post('exams/{exam}/submit', [StudentExamController::class, 'submit'])->name('exams.submit');
    Route::get('exams/{exam}/result', [StudentExamController::class, 'result'])->name('exams.result');
    Route::get('exams/{exam}/time', [StudentExamController::class, 'getRemainingTime'])->name('exams.time');
    
    // Student Grade Routes
    Route::get('grades', [StudentGradeController::class, 'index'])->name('grades.index');
    Route::get('grades/{course}', [StudentGradeController::class, 'show'])->name('grades.show');
    Route::get('grades/details/{grade}', [StudentGradeController::class, 'getGradeDetails'])->name('grades.details');
});
