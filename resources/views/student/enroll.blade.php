@extends('layouts.dashboard')

@section('title', 'Enroll in Course')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('student.courses.my') }}" class="inline-flex items-center px-3 py-2 text-gray-600 hover:text-gray-900 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to My Courses
                </a>
                <div class="border-l border-gray-300 pl-4">
                    <h1 class="text-3xl font-bold text-gray-900">Enroll in Course</h1>
                    <p class="text-gray-600 mt-2">Browse and enroll in available courses</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Available Courses -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($availableCourses as $course)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200">
            <div class="p-6">
                <!-- Course Header -->
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                {{ $course->code }}
                            </span>
                            <span class="text-sm text-gray-500">{{ $course->credits }} Credits</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $course->title }}</h3>
                    </div>
                </div>

                <!-- Course Info -->
                <div class="space-y-2 mb-4">
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-user-tie w-4 mr-2"></i>
                        <span>{{ $course->instructor->first_name }} {{ $course->instructor->last_name }}</span>
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-building w-4 mr-2"></i>
                        <span>{{ $course->department }}</span>
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-calendar w-4 mr-2"></i>
                        <span>{{ $course->semester_type }} {{ $course->year }}</span>
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-users w-4 mr-2"></i>
                        <span>{{ $course->students->count() }}/{{ $course->max_students }} Students</span>
                    </div>
                </div>

                <!-- Course Description -->
                <p class="text-gray-600 text-sm mb-4 line-clamp-3">{{ $course->description }}</p>

                @if($course->prerequisites)
                <div class="mb-4">
                    <span class="text-sm font-medium text-gray-700">Prerequisites:</span>
                    <p class="text-sm text-gray-600">{{ $course->prerequisites }}</p>
                </div>
                @endif

                <!-- Enroll Button -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-full bg-gray-200 rounded-full h-2 mr-3">
                            <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ ($course->students->count() / $course->max_students) * 100 }}%"></div>
                        </div>
                    </div>
                    @if($course->students->count() >= $course->max_students)
                    <span class="px-3 py-2 text-sm font-medium text-gray-500 bg-gray-100 rounded-lg">Course Full</span>
                    @else
                    <button onclick="showEnrollModal({{ $course->id }}, '{{ $course->title }}', '{{ $course->code }}')" 
                            class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                        <i class="fas fa-plus mr-1"></i>
                        Enroll
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full">
            <div class="text-center py-12">
                <div class="mx-auto h-24 w-24 text-gray-400 mb-4">
                    <i class="fas fa-graduation-cap text-6xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Available Courses</h3>
                <p class="text-gray-600">There are no courses available for enrollment at the moment.</p>
            </div>
        </div>
        @endforelse
    </div>
</div>

<!-- Enrollment Modal -->
<div id="enrollModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Enroll in Course</h3>
                    <button onclick="closeEnrollModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="mb-4">
                    <p class="text-sm text-gray-600">Course:</p>
                    <p class="font-medium text-gray-900" id="modalCourseName"></p>
                </div>
                
                <form id="enrollForm">
                    @csrf
                    <div class="mb-4">
                        <label for="coursePassword" class="block text-sm font-medium text-gray-700 mb-2">Course Password</label>
                        <input type="password" id="coursePassword" name="password" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="Enter course password">
                        <p class="text-xs text-gray-500 mt-1">Please enter the course password provided by your instructor.</p>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeEnrollModal()"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" id="enrollButton"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                            <i class="fas fa-spinner fa-spin mr-2 hidden" id="enrollSpinner"></i>
                            Enroll
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Success/Error Messages -->
<div id="alertContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>
@endsection

@section('scripts')
<script src="{{ asset('js/student-enrollment.js') }}"></script>
@endsection
