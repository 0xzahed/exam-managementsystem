@extends('layouts.dashboard')

@section('title', 'My Courses')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-book-open text-indigo-600 mr-3"></i>
                    My Courses
                </h1>
                <p class="text-gray-600 mt-2">Manage your enrolled courses and track your progress</p>
            </div>
            <div class="flex items-center space-x-2 text-sm text-gray-500">
                <i class="fas fa-calendar mr-1"></i>
                <span>{{ now()->format('M d, Y') }}</span>
            </div>
        </div>
    </div>

    <!-- Statistics Overview -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl p-6 shadow-lg transform transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium">Total Courses</p>
                    <p class="text-3xl font-bold">{{ $stats['enrolledCourses'] ?? 0 }}</p>
                    <p class="text-blue-200 text-xs mt-1">Currently enrolled</p>
                </div>
                <div class="bg-blue-400 bg-opacity-30 rounded-full p-3">
                    <i class="fas fa-book text-2xl text-blue-100"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl p-6 shadow-lg transform transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium">Total Credits</p>
                    <p class="text-3xl font-bold">{{ $stats['totalCredits'] ?? 0 }}</p>
                    <p class="text-green-200 text-xs mt-1">Credit hours</p>
                </div>
                <div class="bg-green-400 bg-opacity-30 rounded-full p-3">
                    <i class="fas fa-graduation-cap text-2xl text-green-100"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl p-6 shadow-lg transform transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium">Assignments</p>
                    <p class="text-3xl font-bold">{{ $stats['totalAssignments'] ?? 0 }}</p>
                    <p class="text-purple-200 text-xs mt-1">Total tasks</p>
                </div>
                <div class="bg-purple-400 bg-opacity-30 rounded-full p-3">
                    <i class="fas fa-tasks text-2xl text-purple-100"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-xl p-6 shadow-lg transform transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm font-medium">Exams</p>
                    <p class="text-3xl font-bold">{{ $stats['totalExams'] ?? 0 }}</p>
                    <p class="text-orange-200 text-xs mt-1">Scheduled tests</p>
                </div>
                <div class="bg-orange-400 bg-opacity-30 rounded-full p-3">
                    <i class="fas fa-clipboard-check text-2xl text-orange-100"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Courses Grid -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-gray-900">Enrolled Courses</h2>
            <span class="text-sm text-gray-500">{{ count($enrolledCourses ?? []) }} course(s)</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @forelse($enrolledCourses ?? [] as $course)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg hover:-translate-y-1 hover:border-indigo-300 transition-all duration-300">
                <!-- Course Header -->
                <div class="bg-gradient-to-r from-indigo-500 to-purple-600 p-4">
                    <h3 class="text-lg font-semibold text-white mb-1">{{ $course->title }}</h3>
                    <p class="text-indigo-100 text-sm">{{ $course->course_code }}</p>
                </div>

                <!-- Course Content -->
                <div class="p-6">
                    <p class="text-gray-600 text-sm mb-4 leading-relaxed">
                        {{ Str::limit($course->description, 100) }}
                    </p>

                    <!-- Course Info -->
                    <div class="space-y-3 mb-4">
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-user-tie w-4 mr-2 text-indigo-500"></i>
                            <span>{{ $course->instructor->name ?? 'TBA' }}</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-credit-card w-4 mr-2 text-green-500"></i>
                            <span>{{ $course->credits }} credits</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-users w-4 mr-2 text-blue-500"></i>
                            <span>{{ $course->students_count ?? 0 }} students enrolled</span>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                        <a href="{{ route('course.details', $course->id) }}"
                            class="bg-indigo-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-indigo-700 transition-colors flex items-center">
                            <i class="fas fa-eye mr-2"></i>
                            View Course
                        </a>
                        <div class="text-xs text-gray-400">
                            <i class="fas fa-clock mr-1"></i>
                            Updated {{ $course->updated_at->diffForHumans() }}
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-16 animate-fadeIn">
                <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-book-open text-3xl text-gray-400"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">No courses enrolled</h3>
                <p class="text-gray-500 mb-6 max-w-md mx-auto">
                    You haven't enrolled in any courses yet. Contact your instructor to get enrolled in courses.
                </p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection