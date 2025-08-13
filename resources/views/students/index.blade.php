@extends('layouts.dashboard')

@section('title', 'All Students')

@section('additional-styles')
<style>
/* Page specific styles */
.student-card {
    transition: all 0.2s ease;
}

.student-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.course-badge {
    transition: all 0.2s ease;
}

.course-badge:hover {
    transform: scale(1.05);
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

    @if($errors->any())
    <div class="px-4 py-3 mb-6 text-red-700 border border-red-200 rounded-lg bg-red-50">
        <i class="mr-2 fas fa-exclamation-circle"></i>
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="mb-2 text-4xl font-bold text-gray-800">All Students</h1>
            <p class="text-gray-600">View all students enrolled in your courses</p>
        </div>

        <div class="flex flex-wrap gap-3">
            <div class="flex items-center space-x-2 bg-white px-4 py-2 rounded-lg border">
                <i class="fas fa-users text-blue-500"></i>
                <span class="font-medium text-gray-700">Total Students: {{ $totalStudents }}</span>
            </div>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-3 ">
        <div class="p-6 bg-gradient-to-r from-blue-500 bg-blue-600 text-white rounded-xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100">Total Enrolled</p>
                    <p class="text-3xl font-bold">{{ $totalStudents }}</p>
                </div>
                <div class="p-3 bg-white bg-opacity-20 rounded-full">
                    <i class="fas fa-user-graduate text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="p-6 bg-gradient-to-r from-green-500 bg-green-600 text-white rounded-xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100">Active Courses</p>
                    <p class="text-3xl font-bold">{{ $totalCourses }}</p>
                </div>
                <div class="p-3 bg-white bg-opacity-20 rounded-full">
                    <i class="fas fa-book text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="p-6 bg-gradient-to-r from-purple-500 bg-purple-600 text-white rounded-xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100">Total Enrollments</p>
                    <p class="text-3xl font-bold">{{ $totalEnrollments }}</p>
                </div>
                <div class="p-3 bg-white bg-opacity-20 rounded-full">
                    <i class="fas fa-chart-line text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="p-6 mb-8 bg-white border border-gray-100 shadow-lg rounded-xl">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex flex-wrap gap-3">
                <!-- Search -->
                <div class="relative">
                    <input type="text" id="searchInput" placeholder="Search students..." class="pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <i class="absolute left-3 top-3 fas fa-search text-gray-400"></i>
                </div>

                <!-- Course Filter -->
                <select id="courseFilter" class="px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All Courses</option>
                    @foreach($courses as $course)
                    <option value="{{ $course->id }}">{{ $course->title }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button onclick="exportStudents()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-download mr-2"></i>Export
                </button>
            </div>
        </div>
    </div>

    <!-- Students Grid -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 xl:grid-cols-3" id="studentsList">
        @forelse($students as $student)
        <div class="student-card p-6 bg-white border border-gray-100 shadow-lg rounded-xl" data-student-name="{{ strtolower($student->name) }}" data-course-ids="{{ $student->enrolledCourses->pluck('id')->implode(',') }}">
            <!-- Student Header -->
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-lg mr-4">
                    {{ strtoupper(substr($student->name, 0, 2)) }}
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-gray-800">{{ $student->name }}</h3>
                    <p class="text-sm text-gray-500">{{ $student->email }}</p>
                </div>
            </div>

            <!-- Student Stats -->
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <p class="text-2xl font-bold text-blue-600">{{ $student->enrolledCourses->count() }}</p>
                    <p class="text-sm text-gray-600">Enrolled Courses</p>
                </div>
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <p class="text-2xl font-bold text-green-600">{{ $student->created_at->format('M Y') }}</p>
                    <p class="text-sm text-gray-600">Joined</p>
                </div>
            </div>

            <!-- Enrolled Courses -->
            <div class="mb-4">
                <p class="text-sm font-medium text-gray-700 mb-2">Enrolled Courses:</p>
                <div class="flex flex-wrap gap-2">
                    @forelse($student->enrolledCourses as $course)
                    <span class="course-badge px-3 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                        {{ $course->title }}
                    </span>
                    @empty
                    <span class="px-3 py-1 text-xs font-medium bg-gray-100 text-gray-600 rounded-full">
                        No courses enrolled
                    </span>
                    @endforelse
                </div>
            </div>

            <!-- Student Actions -->
            <div class="flex gap-2">
                <button onclick="viewStudent({{ $student->id }})" class="flex-1 px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-eye mr-2"></i>View Details
                </button>
                <button onclick="messageStudent({{ $student->id }})" class="px-4 py-2 bg-gray-600 text-white text-sm rounded-lg hover:bg-gray-700 transition-colors">
                    <i class="fas fa-envelope"></i>
                </button>
            </div>
        </div>
        @empty
        <!-- Empty State -->
        <div class="col-span-full text-center py-12">
            <div class="mb-4">
                <i class="fas fa-user-friends text-6xl text-gray-300"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">No Students Found</h3>
            <p class="text-gray-500">Students enrolled in your courses will appear here.</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($students->hasPages())
    <div class="mt-8">
        {{ $students->links() }}
    </div>
    @endif
</div>

<!-- Student Detail Modal -->
<div id="studentDetailModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-gray-800">Student Details</h3>
            <button onclick="hideStudentModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div id="studentDetailContent">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>
@endsection

@vite('resources/js/pages/students/index.js')
