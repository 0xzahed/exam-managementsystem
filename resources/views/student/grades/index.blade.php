@extends('layouts.dashboard')

@section('title', 'My Grades')

@section('content')
<div class="px-0 pt-2 md:pt-0">
    <div class="py-2 md:py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">My Grades</h1>
                        <p class="text-gray-600">View your grades across all enrolled courses</p>
                    </div>
                </div>
            </div>

            <!-- Overall Statistics -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">Enrolled Courses</p>
                            <h3 class="text-2xl font-bold">{{ $enrolledCourses->count() }}</h3>
                        </div>
                        <div class="bg-blue-400/30 p-3 rounded-lg">
                            <i class="fas fa-book text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-xl p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium">Total Assignments</p>
                            <h3 class="text-2xl font-bold">
                                @php
                                    $totalAssignments = $enrolledCourses->sum(function($course) {
                                        return $course->assignments->count();
                                    });
                                @endphp
                                {{ $totalAssignments }}
                            </h3>
                        </div>
                        <div class="bg-green-400/30 p-3 rounded-lg">
                            <i class="fas fa-tasks text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 text-white rounded-xl p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-yellow-100 text-sm font-medium">Total Exams</p>
                            <h3 class="text-2xl font-bold">
                                @php
                                    $totalExams = $enrolledCourses->sum(function($course) {
                                        return $course->exams->count();
                                    });
                                @endphp
                                {{ $totalExams }}
                            </h3>
                        </div>
                        <div class="bg-yellow-400/30 p-3 rounded-lg">
                            <i class="fas fa-clipboard-check text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm font-medium">Overall Average</p>
                            <h3 class="text-2xl font-bold">
                                @php
                                    $allGrades = $grades->values();
                                    $overallAverage = $allGrades->count() > 0 ? round($allGrades->avg('score'), 1) : 0;
                                @endphp
                                {{ $overallAverage }}%
                            </h3>
                        </div>
                        <div class="bg-purple-400/30 p-3 rounded-lg">
                            <i class="fas fa-chart-line text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Courses with Grades -->
            <div class="grid grid-cols-1 gap-6">
                @forelse($enrolledCourses as $course)
                @php
                    $courseGrades = $grades->filter(function($grade) use ($course) {
                        return $grade->course_id === $course->id;
                    });
                    $courseAverage = $courseAverages[$course->id] ?? 0;
                    $gradeColor = $courseAverage >= 90 ? 'green' : ($courseAverage >= 80 ? 'blue' : ($courseAverage >= 70 ? 'yellow' : ($courseAverage >= 60 ? 'orange' : 'red')));
                    $bgColor = $gradeColor === 'green' ? 'bg-green-100 text-green-800' : 
                              ($gradeColor === 'blue' ? 'bg-blue-100 text-blue-800' : 
                              ($gradeColor === 'yellow' ? 'bg-yellow-100 text-yellow-800' : 
                              ($gradeColor === 'orange' ? 'bg-orange-100 text-orange-800' : 'bg-red-100 text-red-800')));
                @endphp
                
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

                                            <span class="px-3 py-1 text-sm font-medium rounded-full {{ $bgColor }}">
                                                Course Average: {{ $courseAverage }}%
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <div class="flex items-center gap-2 mb-1">
                                            <i class="fas fa-tasks text-green-500"></i>
                                            <span class="font-medium text-gray-700">Assignments</span>
                                        </div>
                                        <p class="text-gray-900 font-semibold">{{ $course->assignments->count() }}</p>
                                    </div>

                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <div class="flex items-center gap-2 mb-1">
                                            <i class="fas fa-clipboard-check text-yellow-500"></i>
                                            <span class="font-medium text-gray-700">Exams</span>
                                        </div>
                                        <p class="text-gray-900 font-semibold">{{ $course->exams->count() }}</p>
                                    </div>

                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <div class="flex items-center gap-2 mb-1">
                                            <i class="fas fa-star text-purple-500"></i>
                                            <span class="font-medium text-gray-700">Graded Items</span>
                                        </div>
                                        <p class="text-gray-900 font-semibold">{{ $courseGrades->count() }}</p>
                                    </div>

                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <div class="flex items-center gap-2 mb-1">
                                            <i class="fas fa-user text-blue-500"></i>
                                            <span class="font-medium text-gray-700">Instructor</span>
                                        </div>
                                        <p class="text-gray-900 font-semibold">{{ $course->instructor->first_name ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex flex-col gap-3 min-w-[200px]">
                                <a href="{{ route('student.grades.show', $course) }}"
                                   class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white text-center py-3 px-4 rounded-lg font-medium transition-all duration-200 transform hover:-translate-y-0.5 hover:shadow-lg flex items-center justify-center gap-2">
                                    <i class="fas fa-chart-bar"></i>
                                    View Detailed Grades
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                    <div class="max-w-md mx-auto">
                        <i class="fas fa-book-open text-6xl text-gray-300 mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">No Enrolled Courses</h3>
                        <p class="text-gray-600 mb-6">You haven't enrolled in any courses yet. Enroll in a course to see your grades.</p>
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
