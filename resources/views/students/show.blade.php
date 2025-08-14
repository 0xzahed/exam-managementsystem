@extends('layouts.dashboard')

@section('title', 'Student Details')

@section('additional-styles')
<style>
/* Page specific styles */
.course-card {
    transition: all 0.3s ease;
}

.course-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.stat-card {
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stat-card:hover {
    transform: translateY(-2px);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05));
    opacity: 0;
    transition: opacity 0.3s ease;
}

.stat-card:hover::before {
    opacity: 1;
}

.timeline-item {
    transition: all 0.2s ease;
}

.timeline-item:hover {
    background-color: #f8fafc;
    border-color: #e2e8f0;
}

.glass-effect {
    backdrop-filter: blur(10px);
    background: rgba(255, 255, 255, 0.9);
}

.gradient-text {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.6s ease-out;
}

.activity-badge {
    position: relative;
}

.activity-badge::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 100%;
    height: 100%;
    background: inherit;
    border-radius: inherit;
    transform: translate(-50%, -50%);
    animation: pulse 2s infinite;
    opacity: 0.3;
}

@keyframes pulse {
    0% { transform: translate(-50%, -50%) scale(1); opacity: 0.3; }
    70% { transform: translate(-50%, -50%) scale(1.4); opacity: 0; }
    100% { transform: translate(-50%, -50%) scale(1); opacity: 0; }
}
</style>
@endsection

@section('content')
<div class="px-2 py-4 sm:px-0 animate-fade-in-up">
    <!-- Flash messages are now handled by the central notification system -->

    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('students.index') }}" class="inline-flex items-center px-6 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 hover:border-gray-400 transition-all duration-200 shadow-sm hover:shadow-md">
            <i class="fas fa-arrow-left mr-2"></i>
            Back to Students
        </a>
    </div>

    <!-- Student Header -->
    <div class="mb-8 p-8 bg-gradient-to-br from-blue-600 via-purple-600 to-blue-800 text-white rounded-2xl shadow-2xl relative overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 left-0 w-96 h-96 bg-white rounded-full -translate-x-1/2 -translate-y-1/2"></div>
            <div class="absolute bottom-0 right-0 w-80 h-80 bg-white rounded-full translate-x-1/3 translate-y-1/3"></div>
        </div>
        
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center gap-6">
            <div class="w-28 h-28 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center text-white font-bold text-4xl backdrop-filter backdrop-blur-sm border border-white border-opacity-20">
                {{ strtoupper(substr($student->name, 0, 2)) }}
            </div>
            <div class="flex-1">
                <h1 class="text-5xl font-bold mb-3 leading-tight">{{ $student->name }}</h1>
                <p class="text-blue-100 text-xl mb-3">{{ $student->email }}</p>
                <div class="flex flex-wrap gap-6 text-sm">
                    <span class="flex items-center bg-white bg-opacity-10 px-3 py-2 rounded-lg backdrop-filter backdrop-blur-sm">
                        <i class="fas fa-calendar mr-2"></i>
                        Joined: {{ $student->created_at->format('M d, Y') }}
                    </span>
                    <span class="flex items-center bg-white bg-opacity-10 px-3 py-2 rounded-lg backdrop-filter backdrop-blur-sm">
                        <i class="fas fa-{{ $student->email_verified_at ? 'user-check' : 'user-times' }} mr-2"></i>
                        {{ $student->email_verified_at ? 'Verified' : 'Unverified' }}
                    </span>
                    <span class="flex items-center bg-white bg-opacity-10 px-3 py-2 rounded-lg backdrop-filter backdrop-blur-sm">
                        <i class="fas fa-clock mr-2"></i>
                        {{ $student->created_at->diffForHumans() }}
                    </span>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <button onclick="messageStudent({{ $student->id }})" class="px-6 py-3 bg-white bg-opacity-20 text-white rounded-xl hover:bg-opacity-30 transition-all duration-200 backdrop-filter backdrop-blur-sm border border-white border-opacity-20">
                    <i class="fas fa-envelope mr-2"></i>Send Message
                </button>
                <button onclick="exportStudent({{ $student->id }})" class="px-6 py-3 bg-white bg-opacity-20 text-white rounded-xl hover:bg-opacity-30 transition-all duration-200 backdrop-filter backdrop-blur-sm border border-white border-opacity-20">
                    <i class="fas fa-download mr-2"></i>Export Data
                </button>
            </div>
        </div>
    </div>
    <!-- Statistics Overview -->
    <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2 lg:grid-cols-4">
        <div class="stat-card p-6 bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-2xl shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium uppercase tracking-wide">Enrolled Courses</p>
                    <p class="text-4xl font-bold mt-1">{{ $enrolledCourses->count() }}</p>
                    <p class="text-blue-100 text-xs mt-1">{{ $enrolledCourses->count() === 1 ? 'Course' : 'Total Courses' }}</p>
                </div>
                <div class="p-4 bg-white bg-opacity-20 rounded-2xl">
                    <i class="fas fa-book text-3xl"></i>
                </div>
            </div>
        </div>

        <div class="stat-card p-6 bg-gradient-to-br from-green-500 to-green-600 text-white rounded-2xl shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium uppercase tracking-wide">Active Status</p>
                    <p class="text-4xl font-bold mt-1">{{ $enrolledCourses->count() }}</p>
                    <p class="text-green-100 text-xs mt-1">{{ $enrolledCourses->count() === 1 ? 'Active Course' : 'Active Courses' }}</p>
                </div>
                <div class="p-4 bg-white bg-opacity-20 rounded-2xl">
                    <i class="fas fa-check-circle text-3xl"></i>
                </div>
            </div>
        </div>

        <div class="stat-card p-6 bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-2xl shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium uppercase tracking-wide">Total Credits</p>
                    <p class="text-4xl font-bold mt-1">{{ $enrolledCourses->sum('credits') ?: '0' }}</p>
                    <p class="text-purple-100 text-xs mt-1">Credit Hours</p>
                </div>
                <div class="p-4 bg-white bg-opacity-20 rounded-2xl">
                    <i class="fas fa-award text-3xl"></i>
                </div>
            </div>
        </div>

        <div class="stat-card p-6 bg-gradient-to-br from-orange-500 to-orange-600 text-white rounded-2xl shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm font-medium uppercase tracking-wide">Member Since</p>
                    <p class="text-2xl font-bold mt-1">{{ $student->created_at->format('M Y') }}</p>
                    <p class="text-orange-100 text-xs mt-1">{{ $student->created_at->diffForHumans() }}</p>
                </div>
                <div class="p-4 bg-white bg-opacity-20 rounded-2xl">
                    <i class="fas fa-user-clock text-3xl"></i>
                </div>
            </div>
        </div>
    </div>
    <!-- Enrolled Courses Section -->
    <div class="bg-white border border-gray-100 shadow-xl rounded-2xl p-8 mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-8">
            <div>
                <h2 class="text-3xl font-bold gradient-text mb-3">Enrolled Courses</h2>
                <p class="text-gray-600 text-lg">Courses this student is currently enrolled in</p>
            </div>
            <div class="flex items-center space-x-3 bg-gradient-to-r from-blue-50 to-purple-50 px-6 py-3 rounded-xl border border-blue-100">
                <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-book-open text-white"></i>
                </div>
                <div>
                    <p class="font-bold text-gray-800">{{ $enrolledCourses->count() }}</p>
                    <p class="text-sm text-gray-600">Course{{ $enrolledCourses->count() !== 1 ? 's' : '' }}</p>
                </div>
            </div>
        </div>

        @if($enrolledCourses->count() > 0)
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
            @foreach($enrolledCourses as $course)
            <div class="course-card border-2 border-gray-200 rounded-2xl p-6 hover:shadow-2xl transition-all duration-300 bg-gradient-to-br from-gray-50 to-white">
                <!-- Course Header -->
                <div class="flex items-start justify-between mb-6">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl flex items-center justify-center text-white font-bold">
                                {{ strtoupper(substr($course->title, 0, 2)) }}
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-800">{{ $course->title }}</h3>
                                <p class="text-sm text-gray-500 font-medium">{{ $course->code }}</p>
                            </div>
                        </div>
                        @if($course->description)
                        <p class="text-gray-600 text-sm leading-relaxed mt-3">{{ Str::limit($course->description, 120) }}</p>
                        @endif
                    </div>
                    <div class="ml-4">
                        <span class="px-4 py-2 text-xs font-bold bg-gradient-to-r from-green-400 to-green-500 text-white rounded-xl shadow-sm">
                            <i class="fas fa-check-circle mr-1"></i>Active
                        </span>
                    </div>
                </div>

                <!-- Course Stats -->
                <div class="grid grid-cols-2 gap-4 mb-6">
                    @if($course->credits)
                    <div class="text-center p-4 bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl border border-blue-200">
                        <p class="text-3xl font-bold text-blue-600">{{ $course->credits }}</p>
                        <p class="text-sm text-blue-600 font-medium">Credits</p>
                    </div>
                    @endif
                    <div class="text-center p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl border border-gray-200">
                        <p class="text-lg font-bold text-gray-700">{{ $course->created_at->format('M Y') }}</p>
                        <p class="text-sm text-gray-600 font-medium">Enrolled</p>
                    </div>
                </div>

                <!-- Course Progress Bar -->
                <div class="mb-6">
                    <div class="flex justify-between text-sm text-gray-600 mb-2">
                        <span>Course Progress</span>
                        <span>85%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-gradient-to-r from-blue-500 to-purple-600 h-2 rounded-full" style="width: 85%"></div>
                    </div>
                </div>

                <!-- Course Actions -->
                <div class="flex gap-3 pt-4 border-t border-gray-200">
                    <button onclick="viewCourse({{ $course->id }})" class="flex-1 px-4 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-medium rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-md">
                        <i class="fas fa-eye mr-2"></i>View Course
                    </button>
                    <button onclick="removeCourseConfirm({{ $student->id }}, {{ $course->id }}, '{{ $course->title }}')" class="px-4 py-3 bg-gradient-to-r from-red-100 to-red-200 text-red-700 text-sm font-medium rounded-xl hover:from-red-200 hover:to-red-300 transition-all duration-200 border border-red-200">
                        <i class="fas fa-times mr-2"></i>Remove
                    </button>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <!-- Empty State -->
        <div class="text-center py-16">
            <div class="mb-6">
                <div class="w-32 h-32 bg-gradient-to-r from-gray-200 to-gray-300 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-book-open text-5xl text-gray-400"></i>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-gray-600 mb-3">No Courses Enrolled</h3>
            <p class="text-gray-500 mb-8 text-lg">This student hasn't enrolled in any of your courses yet.</p>
            <button onclick="addCourse({{ $student->id }})" class="px-8 py-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-medium rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg">
                <i class="fas fa-plus mr-2"></i>Add to Course
            </button>
        </div>
        @endif
    </div>
    <!-- Activity Timeline Section -->
    <div class="bg-white border border-gray-100 shadow-xl rounded-2xl p-8">
        <div class="mb-8">
            <h2 class="text-3xl font-bold gradient-text mb-3">Recent Activity</h2>
            <p class="text-gray-600 text-lg">Student's recent activities and milestones</p>
        </div>

        <div class="space-y-6">
            <!-- Timeline Item -->
            <div class="timeline-item flex items-start space-x-6 p-6 border border-gray-200 rounded-2xl hover:border-gray-300 transition-all duration-200">
                <div class="activity-badge w-14 h-14 bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-user-plus text-white text-lg"></i>
                </div>
                <div class="flex-1">
                    <div class="flex items-center justify-between mb-2">
                        <p class="font-bold text-gray-800 text-lg">Student Registration</p>
                        <span class="text-sm text-gray-400 bg-gray-100 px-3 py-1 rounded-full">{{ $student->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-gray-600 mb-1">Successfully registered to the platform</p>
                    <p class="text-sm text-gray-500">{{ $student->created_at->format('M d, Y \a\t h:i A') }}</p>
                </div>
            </div>

            @if($student->email_verified_at)
            <div class="timeline-item flex items-start space-x-6 p-6 border border-gray-200 rounded-2xl hover:border-gray-300 transition-all duration-200">
                <div class="activity-badge w-14 h-14 bg-gradient-to-r from-green-500 to-green-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-check-circle text-white text-lg"></i>
                </div>
                <div class="flex-1">
                    <div class="flex items-center justify-between mb-2">
                        <p class="font-bold text-gray-800 text-lg">Email Verification</p>
                        <span class="text-sm text-gray-400 bg-gray-100 px-3 py-1 rounded-full">{{ $student->email_verified_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-gray-600 mb-1">Email address verified successfully</p>
                    <p class="text-sm text-gray-500">{{ $student->email_verified_at->format('M d, Y \a\t h:i A') }}</p>
                </div>
            </div>
            @endif

            @foreach($enrolledCourses->take(3) as $course)
            <div class="timeline-item flex items-start space-x-6 p-6 border border-gray-200 rounded-2xl hover:border-gray-300 transition-all duration-200">
                <div class="activity-badge w-14 h-14 bg-gradient-to-r from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-book text-white text-lg"></i>
                </div>
                <div class="flex-1">
                    <div class="flex items-center justify-between mb-2">
                        <p class="font-bold text-gray-800 text-lg">Course Enrollment</p>
                        <span class="text-sm text-gray-400 bg-gray-100 px-3 py-1 rounded-full">{{ $course->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-gray-600 mb-1">Enrolled in <span class="font-medium">{{ $course->title }}</span></p>
                    <p class="text-sm text-gray-500">{{ $course->created_at->format('M d, Y \a\t h:i A') }}</p>
                </div>
            </div>
            @endforeach

            @if($enrolledCourses->count() > 3)
            <div class="text-center pt-4">
                <button onclick="showAllActivities()" class="px-6 py-3 text-blue-600 border border-blue-200 rounded-xl hover:bg-blue-50 transition-colors">
                    <i class="fas fa-chevron-down mr-2"></i>Show {{ $enrolledCourses->count() - 3 }} More Activities
                </button>
            </div>
            @endif
        </div>
    </div>
</div>
<!-- Remove Course Confirmation Modal -->
<div id="removeCourseModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50 backdrop-filter backdrop-blur-sm">
    <div class="bg-white rounded-2xl p-8 w-full max-w-md shadow-2xl border border-gray-200">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 mb-3">Remove Student from Course</h3>
            <p class="text-gray-600 leading-relaxed">Are you sure you want to remove this student from the course? This action cannot be undone and the student will lose access to all course materials.</p>
        </div>
        
        <div class="flex gap-4">
            <button onclick="hideRemoveCourseModal()" class="flex-1 px-6 py-3 bg-gray-100 text-gray-700 font-medium rounded-xl hover:bg-gray-200 transition-colors">
                Cancel
            </button>
            <button id="confirmRemoveBtn" class="flex-1 px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white font-medium rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200 shadow-md">
                Remove Student
            </button>
        </div>
    </div>
</div>
@endsection
@section('additional-scripts')
<script>
let currentStudentId = {{ $student->id }};
let currentCourseId = null;
let currentCourseTitle = null;

// Remove course confirmation
function removeCourseConfirm(studentId, courseId, courseTitle) {
    currentStudentId = studentId;
    currentCourseId = courseId;
    currentCourseTitle = courseTitle;
    
    document.getElementById('removeCourseModal').classList.remove('hidden');
    document.getElementById('removeCourseModal').classList.add('flex');
    
    // Update confirmation button
    document.getElementById('confirmRemoveBtn').onclick = function() {
        removeCourse(studentId, courseId);
    };
}

// Hide remove course modal
function hideRemoveCourseModal() {
    const modal = document.getElementById('removeCourseModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

// Remove student from course
function removeCourse(studentId, courseId) {
    // Show loading state
    const confirmBtn = document.getElementById('confirmRemoveBtn');
    const originalText = confirmBtn.innerHTML;
    confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Removing...';
    confirmBtn.disabled = true;

    fetch(`/students/${studentId}/courses/${courseId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Student removed from course successfully!', 'success');
            hideRemoveCourseModal();
            // Refresh the page to update the course list
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showToast(data.message || 'Failed to remove student', 'error');
            confirmBtn.innerHTML = originalText;
            confirmBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while removing student', 'error');
        confirmBtn.innerHTML = originalText;
        confirmBtn.disabled = false;
    });
}

// View course details (placeholder)
function viewCourse(courseId) {
    showToast('Course view functionality will be implemented soon!', 'info');
}

// Add course (placeholder)
function addCourse(studentId) {
    showToast('Add course functionality will be implemented soon!', 'info');
}

// Message student (placeholder)
function messageStudent(studentId) {
    showToast('Message functionality will be implemented soon!', 'info');
}

// Export student data (placeholder)
function exportStudent(studentId) {
    showToast('Export functionality will be implemented soon!', 'info');
}

// Show all activities
function showAllActivities() {
    showToast('Show all activities functionality will be implemented soon!', 'info');
}

// Enhanced toast notification function
function showToast(message, type = 'success') {
    // Create toast element
    const toast = document.createElement('div');
    const icons = {
        success: 'fas fa-check-circle',
        error: 'fas fa-exclamation-circle',
        info: 'fas fa-info-circle',
        warning: 'fas fa-exclamation-triangle'
    };
    
    const colors = {
        success: 'from-green-500 to-green-600',
        error: 'from-red-500 to-red-600',
        info: 'from-blue-500 to-blue-600',
        warning: 'from-yellow-500 to-yellow-600'
    };

    toast.className = `fixed top-4 right-4 p-4 rounded-xl shadow-2xl z-50 text-white transform translate-x-full transition-all duration-300 bg-gradient-to-r ${colors[type] || colors.success} border border-white border-opacity-20`;
    toast.innerHTML = `
        <div class="flex items-center space-x-3">
            <i class="${icons[type] || icons.success}"></i>
            <span class="font-medium">${message}</span>
        </div>
    `;
    
    // Add to body
    document.body.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.style.transform = 'translateX(0)';
    }, 100);
    
    // Remove after 4 seconds
    setTimeout(() => {
        toast.style.transform = 'translateX(full)';
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 300);
    }, 4000);
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    const modal = document.getElementById('removeCourseModal');
    if (modal && e.target === modal) {
        hideRemoveCourseModal();
    }
});

// Add smooth scroll behavior for any anchors
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});
</script>
@endsection

