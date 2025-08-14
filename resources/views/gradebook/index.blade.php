@extends('layouts.dashboard')

@section('title', 'Gradebook')

@section('content')
<div class="px-0 pt-2 md:pt-0">
    <div class="py-2 md:py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Gradebook</h1>
                        <p class="text-gray-600">Manage grades for all your courses</p>
                    </div>
  
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-all duration-200 hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">Total Courses</p>
                            <h3 class="text-2xl font-bold">{{ $courses->count() }}</h3>
                        </div>
                        <div class="bg-blue-400/30 p-3 rounded-lg">
                            <i class="fas fa-chalkboard-teacher text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-all duration-200 hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium">Total Students</p>
                            <h3 class="text-2xl font-bold">{{ $courses->sum('students_count') }}</h3>
                        </div>
                        <div class="bg-green-400/30 p-3 rounded-lg">
                            <i class="fas fa-users text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 text-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-all duration-200 hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-yellow-100 text-sm font-medium">Total Assignments</p>
                            <h3 class="text-2xl font-bold">{{ $courses->sum('assignments_count') }}</h3>
                        </div>
                        <div class="bg-yellow-400/30 p-3 rounded-lg">
                            <i class="fas fa-tasks text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-all duration-200 hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm font-medium">Total Exams</p>
                            <h3 class="text-2xl font-bold">{{ $courses->sum('exams_count') }}</h3>
                        </div>
                        <div class="bg-purple-400/30 p-3 rounded-lg">
                            <i class="fas fa-clipboard-check text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Courses List -->
            <div class="grid grid-cols-1 gap-6">
                @forelse($courses as $course)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-lg transition-all duration-200">
                    <div class="p-6">
                        <div class="flex flex-col lg:flex-row gap-6">
                            <!-- Course Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-4 mb-4">
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-xl font-semibold text-gray-900 mb-2 truncate">{{ $course->title }}</h3>
                                        <p class="text-gray-600 mb-3">{{ $course->description }}</p>

                                        <div class="flex flex-wrap items-center gap-3 mb-3">
                                            <span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full">
                                                {{ $course->code ?? 'No Code' }}
                                            </span>

                                            <span class="px-3 py-1 text-sm font-medium rounded-full 
                                                {{ $course->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                {{ ucfirst($course->status ?? 'Active') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <div class="flex items-center gap-2 mb-1">
                                            <i class="fas fa-users text-blue-500"></i>
                                            <span class="font-medium text-gray-700">Students</span>
                                        </div>
                                        <p class="text-gray-900 font-semibold">{{ $course->students_count ?? 0 }}</p>
                                    </div>

                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <div class="flex items-center gap-2 mb-1">
                                            <i class="fas fa-tasks text-green-500"></i>
                                            <span class="font-medium text-gray-700">Assignments</span>
                                        </div>
                                        <p class="text-gray-900 font-semibold">{{ $course->assignments_count ?? 0 }}</p>
                                    </div>

                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <div class="flex items-center gap-2 mb-1">
                                            <i class="fas fa-clipboard-check text-yellow-500"></i>
                                            <span class="font-medium text-gray-700">Exams</span>
                                        </div>
                                        <p class="text-gray-900 font-semibold">{{ $course->exams_count ?? 0 }}</p>
                                    </div>

                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <div class="flex items-center gap-2 mb-1">
                                            <i class="fas fa-calendar text-purple-500"></i>
                                            <span class="font-medium text-gray-700">Created</span>
                                        </div>
                                        <p class="text-gray-900 font-semibold">{{ $course->created_at ? $course->created_at->format('M j, Y') : 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex flex-col gap-3 min-w-[200px]">
                                <a href="{{ route('instructor.gradebook.show', $course) }}"
                                   class="bg-blue-600 hover:from-blue-700 hover:to-blue-800 text-white text-center py-3 px-4 rounded-lg font-medium duration-200 transform  flex items-center justify-center gap-2">
                                    <i class="fas fa-book-open"></i>
                                    View Gradebook
                                </a>

                                <a href="{{ route('instructor.gradebook.export', $course) }}"
                                   class="bg-green-600 hover:bg-green-700 text-white text-center py-3 px-4 rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                                    <i class="fas fa-download"></i>
                                    Export Grades
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                    <div class="max-w-md mx-auto">
                        <i class="fas fa-book-open text-6xl text-gray-300 mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">No Courses Found</h3>
                        <p class="text-gray-600 mb-6">You haven't created any courses yet. Create a course to start managing grades.</p>
                        <a href="{{ route('courses.create') }}"
                           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                            <i class="fas fa-plus"></i>
                            Create Course
                        </a>
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<style>
    .fade-in {
        animation: fadeIn 0.6s ease-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .course-card {
        border-left: 4px solid transparent;
        transition: all 0.3s ease;
    }

    .course-card:hover {
        border-left-color: #667eea;
    }

    .course-card[data-status="active"] {
        border-left-color: #10b981;
    }
</style>
@endsection
