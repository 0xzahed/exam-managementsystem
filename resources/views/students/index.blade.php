@extends('layouts.dashboard')

@section('title', 'All Students')

@section('content')
<div class="px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Students</h1>
        <p class="text-gray-600">View all students enrolled in your courses</p>
    </div>

    <!-- Simple Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg border">
            <div class="flex items-center">
                <i class="fas fa-users text-blue-500 text-xl mr-3"></i>
                <div>
                    <p class="text-sm text-gray-600">Total Students</p>
                    <p class="text-xl font-bold">{{ $totalStudents }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg border">
            <div class="flex items-center">
                <i class="fas fa-book text-green-500 text-xl mr-3"></i>
                <div>
                    <p class="text-sm text-gray-600">Active Courses</p>
                    <p class="text-xl font-bold">{{ $totalCourses }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg border">
            <div class="flex items-center">
                <i class="fas fa-chart-line text-purple-500 text-xl mr-3"></i>
                <div>
                    <p class="text-sm text-gray-600">Total Enrollments</p>
                    <p class="text-xl font-bold">{{ $totalEnrollments }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Simple Search -->
    <div class="bg-white p-4 rounded-lg border mb-6">
        <div class="flex gap-4">
            <input type="text" id="searchInput" placeholder="Search students..." 
                   class="flex-1 px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
            <select id="courseFilter" class="px-3 py-2 border rounded-lg">
                <option value="">All Courses</option>
                @foreach($courses as $course)
                <option value="{{ $course->id }}">{{ $course->title }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Students Table -->
    <div class="bg-white rounded-lg border overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enrolled Courses</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200" id="studentsList">
                @forelse($students as $student)
                <tr class="hover:bg-gray-50 student-row" 
                    data-student-name="{{ strtolower($student->name) }}" 
                    data-course-ids="{{ $student->enrolledCourses->pluck('id')->implode(',') }}">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white font-medium text-sm mr-3">
                                {{ strtoupper(substr($student->name, 0, 2)) }}
                            </div>
                            <div class="text-sm font-medium text-gray-900">{{ $student->name }}</div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $student->email }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex flex-wrap gap-1">
                            @forelse($student->enrolledCourses as $course)
                            <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">
                                {{ $course->title }}
                            </span>
                            @empty
                            <span class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded">
                                No courses
                            </span>
                            @endforelse
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $student->created_at->format('M d, Y') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-users text-4xl mb-2"></i>
                        <p>No students found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($students->hasPages())
    <div class="mt-6">
        {{ $students->links() }}
    </div>
    @endif
</div>

<script>
// Simple search functionality
document.getElementById('searchInput').addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('.student-row');
    
    rows.forEach(row => {
        const name = row.dataset.studentName;
        if (name.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Simple course filter
document.getElementById('courseFilter').addEventListener('change', function() {
    const selectedCourse = this.value;
    const rows = document.querySelectorAll('.student-row');
    
    rows.forEach(row => {
        const courseIds = row.dataset.courseIds;
        if (!selectedCourse || courseIds.includes(selectedCourse)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>
@endsection
