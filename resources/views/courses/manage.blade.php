@extends('layouts.dashboard')

@section('title','My Courses')

@section('additional-styles')
/* Page specific styles - animations removed */
@endsection

@section('content')
<div id="coursesManageRoot" data-open-edit-modal="{{ session('openEditModal') ?? '' }}" data-open-create="{{ request()->query('create') ? '1' : '' }}">
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
                <h1 class="mb-2 text-4xl font-bold text-gray-800">My Courses</h1>
                <p class="text-gray-600">Create, manage, and organize all your courses in one place</p>
            </div>

        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-4">
            <div class="p-6 text-white shadow-lg stats-card rounded-xl bg-purple-600">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-white/80">Total Courses</p>
                        <p class="text-3xl font-bold">{{ $stats['totalCourses'] ?? 0 }}</p>
                    </div>
                    <i class="text-3xl text-white/70 fas fa-book"></i>
                </div>
            </div>

            <!-- <div class="p-6 text-white transition-colors bg-green-500 shadow-lg rounded-xl hover:bg-green-600">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-green-100">Total Courses</p>
                        <p class="text-3xl font-bold">{{ $stats['activeCourses'] ?? 0 }}</p>
                    </div>
                    <i class="text-3xl text-green-200 fas fa-play-circle"></i>
                </div>
            </div> -->

            <div class="p-6 text-white transition-colors bg-green-500 shadow-lg rounded-xl hover:bg-green-600">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-green-100">Active Courses</p>
                        <p class="text-3xl font-bold">{{ $stats['activeCourses'] ?? 0 }}</p>
                    </div>
                    <i class="text-3xl text-green-200 fas fa-play-circle"></i>
                </div>
            </div>
            <div class="p-6 text-white transition-colors bg-yellow-500 shadow-lg rounded-xl hover:bg-yellow-600">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-yellow-100">Total Students</p>
                        <p class="text-3xl font-bold">{{ $stats['totalStudents'] ?? 0 }}</p>
                    </div>
                    <i class="text-3xl text-yellow-200 fas fa-users"></i>
                </div>
            </div>
            <div class="p-6 text-white transition-colors bg-blue-500 shadow-lg rounded-xl hover:bg-blue-600">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-100">This Semester</p>
                        <p class="text-lg font-bold">{{ $stats['currentSemester'] ?? 'Spring 2025' }}</p>
                    </div>
                    <i class="text-3xl text-blue-200 fas fa-calendar"></i>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="p-6 mb-8 bg-white border border-gray-100 shadow-lg rounded-xl">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-wrap gap-2">
                    <button onclick="filterCourses('all')" class="px-4 py-2 font-medium text-white transition-all bg-indigo-600 rounded-lg filter-btn">All Courses</button>
                    <button onclick="filterCourses('active')" class="px-4 py-2 font-medium text-gray-700 transition-all bg-gray-100 rounded-lg filter-btn hover:bg-gray-200">Active</button>
                    <button onclick="filterCourses('draft')" class="px-4 py-2 font-medium text-gray-700 transition-all bg-gray-100 rounded-lg filter-btn hover:bg-gray-200">Drafts</button>
                    <button onclick="filterCourses('archived')" class="px-4 py-2 font-medium text-gray-700 transition-all bg-gray-100 rounded-lg filter-btn hover:bg-gray-200">Archived</button>
                </div>
                <div class="flex gap-4">
                    <div class="relative">
                        <i class="absolute text-gray-400 transform -translate-y-1/2 fas fa-search left-3 top-1/2"></i>
                        <input type="text" id="courseSearch" placeholder="Search courses..." class="py-2 pl-10 pr-4 transition-colors border border-gray-300 rounded-lg focus:outline-none focus:border-indigo-500">
                    </div>
                    <select class="px-4 py-2 transition-colors border border-gray-300 rounded-lg focus:outline-none focus:border-indigo-500">
                        <option>All Semesters</option>
                        <option>Spring 2025</option>
                        <option>Fall 2024</option>
                        <option>Summer 2024</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Courses Grid -->
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2" id="coursesList">
            @forelse($courses as $course)
            <div class="p-6 bg-white border border-gray-100 shadow-lg course-card status-{{ $course->status ?? 'active' }} rounded-xl">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="text-xl font-bold text-gray-800">{{ $course->title }}</h3>
                            <span class="px-3 py-1 text-sm font-medium {{ $course->status === 'archived' ? 'text-gray-800 bg-gray-100' : ($course->status === 'draft' ? 'text-yellow-800 bg-yellow-100' : 'text-green-800 bg-green-100') }} rounded-full">{{ ucfirst($course->status ?? 'Active') }}</span>
                        </div>
                        <p class="mb-3 text-sm text-gray-600">{{ $course->code }}</p>
                        @if($course->prerequisites)
                        <p class="mb-3 text-sm text-gray-500"><strong>Prerequisites:</strong> {{ $course->prerequisites }}</p>
                        @endif
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-indigo-600">{{ $course->students()->count() }}</div>
                        <div class="text-xs text-gray-600">Students</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">{{ $course->courseMaterials()->count() }}</div>
                        <div class="text-xs text-gray-600">Materials</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-yellow-600">{{ $course->max_students ?? 50 }}</div>
                        <div class="text-xs text-gray-600">Capacity</div>
                    </div>
                </div>
                <div class="flex flex-col gap-2">
                    <div class="flex gap-2">
                        <a href="{{ route('courses.materials', $course->id) }}" class="flex-1 px-4 py-2 font-medium text-center text-white transition-colors bg-indigo-600 rounded-lg action-btn hover:bg-indigo-700">
                            <i class="mr-2 fas fa-folder"></i>Materials
                        </a>
                        <a href="{{ route('courses.edit', $course->id) }}" class="flex-1 px-4 py-2 font-medium text-center text-white transition-colors bg-yellow-600 rounded-lg action-btn hover:bg-yellow-700">
                            <i class="mr-2 fas fa-edit"></i>Edit
                        </a>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="viewStudents('{{ $course->id }}')" class="flex-1 px-4 py-2 font-medium text-white transition-colors bg-blue-600 rounded-lg action-btn hover:bg-blue-700">
                            <i class="mr-2 fas fa-users"></i>Students ({{ $course->students()->count() }})
                        </button>
                        <form method="POST" action="{{ route('courses.destroy', $course->id) }}" class="flex-1" onsubmit="return confirm('Are you sure you want to delete {{ $course->title }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full px-4 py-2 font-medium text-white transition-colors bg-red-600 rounded-lg action-btn hover:bg-red-700">
                                <i class="mr-2 fas fa-trash"></i>Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-2 py-16 text-center text-gray-400">
                <i class="mb-4 text-4xl fas fa-inbox"></i>
                <p class="text-lg">No courses found.</p>
                <p class="mt-2 text-sm">Create your first course to get started!</p>
                <div class="flex justify-center mt-4">
                    <a href="{{ route('courses.create') }}" class="inline-block px-6 py-2 text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                        <i class="mr-2 fas fa-plus"></i>Create Course
                    </a>
                </div>
            </div>
            @endforelse
        </div>

        <!-- Students List Modal -->
        <div id="studentsModal" class="fixed inset-0 z-[9999] items-center justify-center hidden">
            <div class="w-full max-w-6xl mx-4 bg-white rounded-xl shadow-2xl max-h-[90vh] overflow-hidden border-2 border-gray-300">
                <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-white">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Enrolled Students</h3>
                        <p id="courseInfo" class="text-sm text-gray-600">Loading...</p>
                    </div>
                    <button onclick="closeStudentsModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="text-xl fas fa-times"></i>
                    </button>
                </div>
                
                <div class="p-6 bg-white overflow-y-auto max-h-[70vh]">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-4">
                            <span id="studentCount" class="text-sm text-gray-600">0 students</span>
                            <div class="relative">
                                <input type="text" id="searchStudents" placeholder="Search students..." 
                                       class="py-2 pl-10 pr-4 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <i class="absolute text-gray-400 transform -translate-y-1/2 fas fa-search left-3 top-1/2"></i>
                            </div>
                        </div>
                        <button onclick="exportStudentList()" class="px-4 py-2 text-sm text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">
                            <i class="mr-2 fas fa-download"></i>Export List
                        </button>
                    </div>

                    <div id="studentsListContainer" class="overflow-hidden border border-gray-200 rounded-lg bg-white">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <div class="grid grid-cols-12 gap-4 text-sm font-medium text-gray-700">
                                <div class="col-span-1">#</div>
                                <div class="col-span-3">Name</div>
                                <div class="col-span-2">Student ID</div>
                                <div class="col-span-3">Email</div>
                                <div class="col-span-2">Enrolled Date</div>
                                <div class="col-span-1">Action</div>
                            </div>
                        </div>
                        <div id="studentsTableBody" class="divide-y divide-gray-200 bg-white">
                            <div class="flex items-center justify-center py-12">
                                <div class="text-center">
                                    <div class="mb-4">
                                        <i class="text-4xl text-gray-400 fas fa-spinner fa-spin"></i>
                                    </div>
                                    <p class="text-gray-500">Loading students...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <!-- End Root -->
</div>
@endsection

@section('scripts')
<script id="courses-json" type="application/json">
{!! json_encode($courses->load('students')) !!}
</script>
<script>
try {
    window.__COURSES__ = JSON.parse(document.getElementById('courses-json').textContent);
} catch (e) {
    window.__COURSES__ = [];
}
</script>
@vite('resources/js/pages/courses/manage.js')

@endsection
