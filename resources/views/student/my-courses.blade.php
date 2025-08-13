@extends('layouts.dashboard')

@section('title', 'My Courses')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">My Courses</h1>
                <p class="text-gray-600 mt-2">Manage your enrolled courses</p>
            </div>
            <div class="flex items-center space-x-4">
                <button id="enrollNewCourseBtn" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Enroll in New Course
                </button>
            </div>
        </div>
    </div>

    <!-- Enrolled Courses Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-indigo-100">
                    <i class="fas fa-book text-indigo-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Courses</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $enrolledCourses->count() }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100">
                    <i class="fas fa-tasks text-green-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Assignments</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $enrolledCourses->sum(function($course) { return $course->assignments->count(); }) }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100">
                    <i class="fas fa-clock text-yellow-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Credits</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $enrolledCourses->sum('credits') }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100">
                    <i class="fas fa-graduation-cap text-purple-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Active Semester</p>
                    <p class="text-lg font-bold text-gray-900">Spring 2025</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Courses List -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Enrolled Courses</h2>
        </div>
        
        @if($enrolledCourses->count() > 0)
        <div class="divide-y divide-gray-200">
            @foreach($enrolledCourses as $course)
            <div class="p-6 hover:bg-gray-50 transition-colors">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-3 mb-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                {{ $course->code }}
                            </span>
                            <span class="text-sm text-gray-500">{{ $course->credits }} Credits</span>
                            <span class="text-sm text-gray-500">•</span>
                            <span class="text-sm text-gray-500">{{ $course->department }}</span>
                        </div>
                        
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ $course->title }}</h3>
                        
                        <div class="flex items-center space-x-6 text-sm text-gray-600">
                            <div class="flex items-center">
                                <i class="fas fa-user-tie mr-2"></i>
                                <span>{{ $course->instructor->first_name }} {{ $course->instructor->last_name }}</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-calendar mr-2"></i>
                                <span>Enrolled {{ \Carbon\Carbon::parse($course->pivot->enrolled_at)->format('M d, Y') }}</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-tasks mr-2"></i>
                                <span>{{ $course->assignments->count() }} Assignments</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('assignments.index') }}?course={{ $course->id }}" 
                           class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                            <i class="fas fa-tasks mr-2"></i>
                            View Assignments
                        </a>
                        
                        <button onclick="showUnenrollModal({{ $course->id }}, '{{ $course->title }}', '{{ $course->code }}')"
                                class="inline-flex items-center px-3 py-2 border border-red-300 rounded-lg text-sm text-red-700 bg-white hover:bg-red-50 transition-colors">
                            <i class="fas fa-sign-out-alt mr-2"></i>
                            Unenroll
                        </button>
                    </div>
                </div>
                
                @if($course->description)
                <p class="text-gray-600 text-sm mt-3 line-clamp-2">{{ $course->description }}</p>
                @endif
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-12">
            <div class="mx-auto h-24 w-24 text-gray-400 mb-4">
                <i class="fas fa-book-open text-6xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Enrolled Courses</h3>
            <p class="text-gray-600 mb-4">You haven't enrolled in any courses yet.</p>
            <a href="{{ route('student.courses.enroll') }}" 
               class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>
                Browse Available Courses
            </a>
        </div>
        @endif
    </div>
</div>

<!-- Unenroll Confirmation Modal -->
<div id="unenrollModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Confirm Unenrollment</h3>
                    <button onclick="closeUnenrollModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="mb-6">
                    <div class="flex items-center mb-4">
                        <div class="p-3 rounded-full bg-red-100">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                        <div class="ml-4">
                            <p class="font-medium text-gray-900">Are you sure you want to unenroll?</p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600">Course: <span class="font-medium" id="modalUnenrollCourseName"></span></p>
                    <p class="text-sm text-red-600 mt-2">⚠️ You will lose access to all course materials and assignments.</p>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeUnenrollModal()"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="button" onclick="confirmUnenroll()" id="unenrollButton"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        <i class="fas fa-spinner fa-spin mr-2 hidden" id="unenrollSpinner"></i>
                        Yes, Unenroll
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success/Error Messages -->
<div id="alertContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>
@endsection

@section('scripts')
<script src="{{ asset('js/student-my-courses.js') }}"></script>
@endsection
