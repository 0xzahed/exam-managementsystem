@extends('layouts.dashboard')

@section('title', 'Student Details')

@section('additional-styles')
<style>
/* Page specific styles */
.course-badge {
    transition: all 0.2s ease;
}

.course-badge:hover {
    transform: scale(1.05);
}

.stat-card {
    transition: all 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}
</style>
@endsection

@section('content')
<div class="px-2 py-4 sm:px-0">
    <!-- Success/Error Messages -->
    @if(session('success'))
    <div class="px-4 py-3 mb-6 text-green-700 border border-green-200 rounded-lg bg-green-50">
        <i class="mr-2 fas fa-check-circle"></i>{{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="px-4 py-3 mb-6 text-red-700 border border-red-200 rounded-lg bg-red-50">
        <i class="mr-2 fas fa-exclamation-circle"></i>{{ session('error') }}
    </div>
    @endif

    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('students.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm rounded-lg hover:bg-gray-700 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Back to Students
        </a>
    </div>

    <!-- Student Header -->
    <div class="p-6 mb-8 bg-white border border-gray-100 shadow-lg rounded-xl">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-6">
                <div class="w-20 h-20 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-2xl">
                    {{ strtoupper(substr($student->name, 0, 2)) }}
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">{{ $student->name }}</h1>
                    <p class="text-gray-600 text-lg">{{ $student->email }}</p>
                    <p class="text-sm text-gray-500 mt-1">Joined: {{ $student->created_at->format('F j, Y') }}</p>
                </div>
            </div>
            
            <div class="flex gap-3">
                <button onclick="messageStudent({{ $student->id }})" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-envelope mr-2"></i>Send Message
                </button>
                
                @if($student->enrolledCourses->count() > 0)
                <div class="relative">
                    <button onclick="toggleDropdown()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        <i class="fas fa-user-minus mr-2"></i>Remove from Course
                        <i class="fas fa-chevron-down ml-2"></i>
                    </button>
                    
                    <div id="courseDropdown" class="absolute right-0 mt-2 w-64 bg-white border border-gray-200 rounded-lg shadow-lg hidden z-10">
                        <div class="py-2">
                            @foreach($student->enrolledCourses as $course)
                            <form action="{{ route('students.remove.course', ['student' => $student->id, 'course' => $course->id]) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Are you sure you want to remove this student from {{ $course->title }}?')" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Remove from: {{ $course->title }}
                                </button>
                            </form>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-3">
        <div class="stat-card p-6 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100">Enrolled Courses</p>
                    <p class="text-3xl font-bold">{{ $student->enrolledCourses->count() }}</p>
                </div>
                <div class="p-3 bg-white bg-opacity-20 rounded-full">
                    <i class="fas fa-book text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="stat-card p-6 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100">Active Status</p>
                    <p class="text-lg font-bold">{{ $student->enrolledCourses->where('pivot.status', 'active')->count() }} Active</p>
                </div>
                <div class="p-3 bg-white bg-opacity-20 rounded-full">
                    <i class="fas fa-check-circle text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="stat-card p-6 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100">Member Since</p>
                    <p class="text-lg font-bold">{{ $student->created_at->diffForHumans() }}</p>
                </div>
                <div class="p-3 bg-white bg-opacity-20 rounded-full">
                    <i class="fas fa-calendar text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Enrolled Courses -->
    <div class="p-6 bg-white border border-gray-100 shadow-lg rounded-xl">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Enrolled Courses</h2>
            <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">
                {{ $student->enrolledCourses->count() }} {{ $student->enrolledCourses->count() == 1 ? 'Course' : 'Courses' }}
            </span>
        </div>

        @forelse($student->enrolledCourses as $course)
        <div class="flex justify-between items-center p-4 border border-gray-200 rounded-lg mb-4 last:mb-0">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                    <h3 class="text-lg font-bold text-gray-800">{{ $course->title }}</h3>
                    <span class="course-badge px-3 py-1 text-xs font-medium {{ $course->pivot->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }} rounded-full">
                        {{ ucfirst($course->pivot->status ?? 'Active') }}
                    </span>
                </div>
                <p class="text-gray-600 mb-2">{{ $course->code }}</p>
                @if($course->description)
                <p class="text-sm text-gray-500 mb-3">{{ Str::limit($course->description, 100) }}</p>
                @endif
                <div class="flex items-center gap-4 text-sm text-gray-500">
                    <span><i class="fas fa-calendar mr-1"></i>Enrolled: {{ $course->pivot->enrolled_at ? $course->pivot->enrolled_at->format('M j, Y') : $course->pivot->created_at->format('M j, Y') }}</span>
                    <span><i class="fas fa-users mr-1"></i>{{ $course->students->count() }} Students</span>
                    @if($course->credits)
                    <span><i class="fas fa-award mr-1"></i>{{ $course->credits }} Credits</span>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('courses.materials', $course->id) }}" class="px-3 py-2 bg-blue-100 text-blue-700 text-sm rounded-lg hover:bg-blue-200 transition-colors">
                    <i class="fas fa-folder mr-1"></i>Materials
                </a>
                
                <form action="{{ route('students.remove.course', ['student' => $student->id, 'course' => $course->id]) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" onclick="return confirm('Are you sure you want to remove this student from {{ $course->title }}?')" class="px-3 py-2 bg-red-100 text-red-700 text-sm rounded-lg hover:bg-red-200 transition-colors">
                        <i class="fas fa-times mr-1"></i>Remove
                    </button>
                </form>
            </div>
        </div>
        @empty
        <!-- Empty State -->
        <div class="text-center py-12">
            <div class="mb-4">
                <i class="fas fa-book-open text-6xl text-gray-300"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">No Courses Enrolled</h3>
            <p class="text-gray-500">This student is not currently enrolled in any of your courses.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection

@vite('resources/js/pages/students/index.js')

@section('scripts')
<script>
// Toggle dropdown for remove course
function toggleDropdown() {
    const dropdown = document.getElementById('courseDropdown');
    dropdown.classList.toggle('hidden');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('courseDropdown');
    const button = e.target.closest('[onclick="toggleDropdown()"]');
    
    if (!button && !dropdown.contains(e.target)) {
        dropdown.classList.add('hidden');
    }
});
</script>
@endsection
