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
Route::get('/google/callback', [GoogleAuthController::class, 'handleGoogleCallback'])->name('google.callback');
Route::get('/auth/google/logout', [GoogleAuthController::class, 'logoutFromGoogle'])->name('auth.google.logout');
Route::post('/auth/google/register', [GoogleAuthController::class, 'handleGoogleRegistration'])->name('auth.google.register');
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
    Route::get('/instructor/dashboard', function () {
        // Fetch actual data for instructor dashboard
        $user = Auth::user();
        $courses = \App\Models\Course::where('instructor_id', $user->id)
                                    ->with('students')
                                    ->get();
        $totalStudents = $courses->sum(function($course) {
            return $course->students->count();
        });
        $pendingGrades = 0; // Will be implemented later
        $todayClasses = 0; // Will be implemented later
        $pendingAssignments = collect();
        $todaySchedule = collect();
        $recentActivities = collect();
        
        return view('dashboard.instructor', compact(
            'courses', 
            'totalStudents', 
            'pendingGrades', 
            'todayClasses',
            'pendingAssignments',
            'todaySchedule',
            'recentActivities'
        ));
    })->name('instructor.dashboard');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return 'Admin Dashboard';
    })->name('admin.dashboard');
});

// Course Routes for Instructors
Route::middleware(['auth', 'role:instructor'])->group(function () {
    Route::get('/courses/create', [CourseController::class, 'create'])->name('courses.create');
    Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/manage', [CourseController::class, 'manage'])->name('courses.manage');
    Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
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
    Route::get('/students/{student}', [DashboardController::class, 'show'])->name('students.show');
    Route::delete('/students/{student}/courses/{course}', [DashboardController::class, 'removeFromCourse'])->name('students.remove.course');
    
    // Assignment management routes (Instructor only)
    Route::get('/instructor/assignments', [AssignmentController::class, 'index'])->name('instructor.assignments.index');
    Route::get('/assignments/create', [AssignmentController::class, 'create'])->name('assignments.create');
    Route::post('/assignments', [AssignmentController::class, 'store'])->name('assignments.store');
    Route::get('/assignments/{assignment}/edit', [AssignmentController::class, 'edit'])->name('assignments.edit');
    Route::put('/assignments/{assignment}', [AssignmentController::class, 'update'])->name('assignments.update');
    Route::delete('/assignments/{assignment}', [AssignmentController::class, 'destroy'])->name('assignments.destroy');
    
    // Assignment submission management routes
    Route::get('/assignments/{assignment}/submissions', [AssignmentController::class, 'submissions'])->name('assignments.submissions');
    Route::get('/assignments/submissions/{submission}/view', [AssignmentController::class, 'viewSubmission'])->name('assignments.submissions.view');
    Route::get('/assignments/submissions/{submission}/download', [AssignmentController::class, 'downloadSubmission'])->name('assignments.submissions.download');
    Route::post('/assignments/submissions/{submission}/grade', [AssignmentController::class, 'gradeSubmission'])->name('assignments.submissions.grade');
    Route::post('/assignments/{assignment}/bulk-grade', [AssignmentController::class, 'bulkGrade'])->name('assignments.bulk-grade');
    Route::post('/assignments/{assignment}/update-marks', [AssignmentController::class, 'updateMarks'])->name('assignments.update-marks');
    Route::get('/assignments/{assignment}/export-submissions', [AssignmentController::class, 'exportSubmissions'])->name('assignments.export-submissions');
});

Route::middleware(['auth', 'role:student'])->group(function () {
    // Student course enrollment routes
    Route::get('/courses/enroll', [EnrollmentController::class, 'showEnrollment'])->name('student.courses.enroll');
    Route::post('/courses/{course}/enroll', [EnrollmentController::class, 'enrollInCourse'])->name('student.courses.enroll.submit');
    Route::get('/my-courses', [EnrollmentController::class, 'myCourses'])->name('student.courses.my');
    Route::delete('/my-courses/{course}/unenroll', [EnrollmentController::class, 'unenrollFromCourse'])->name('student.courses.unenroll');
    
    // Student assignment routes only
    Route::get('/assignments', [AssignmentController::class, 'index'])->name('assignments.index');
    Route::get('/assignments/{assignment}', [AssignmentController::class, 'show'])->name('assignments.show');
    Route::post('/assignments/{assignment}/submit', [AssignmentController::class, 'processSubmission'])->name('assignments.process-submission');
    Route::get('/assignments/submissions/{submission}/download', [AssignmentController::class, 'downloadSubmission'])->name('assignments.download');
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
