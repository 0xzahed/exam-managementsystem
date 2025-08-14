@extends('layouts.dashboard')

@section('title', $course->title . ' - Gradebook')

@section('content')
<div class="px-0 pt                                        <div class="text-xs text-gray-400">({{ $exam->total_points ?? 100 }} marks)</div>2 md:pt-0">
    <div class="py-2 md:py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <a href="{{ route('instructor.gradebook.index') }}" 
                               class="text-gray-600 hover:text-gray-900 transition-colors">
                                <i class="fas fa-arrow-left text-lg"></i>
                            </a>
                            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $course->title }} - Gradebook</h1>
                        </div>
                        <p class="text-gray-600">{{ $course->description }}</p>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('instructor.gradebook.export', $course) }}" 
                           class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition-colors">
                            <i class="fas fa-download mr-2"></i>
                            Export Grades
                        </a>
                        <a href="{{ route('instructor.dashboard') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                            <i class="fas fa-home mr-2"></i>
                            Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <!-- Course Statistics -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">Total Students</p>
                            <h3 class="text-2xl font-bold">{{ $students->count() }}</h3>
                        </div>
                        <div class="bg-blue-400/30 p-3 rounded-lg">
                            <i class="fas fa-users text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-xl p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium">Assignments</p>
                            <h3 class="text-2xl font-bold">{{ $assignments->count() }}</h3>
                        </div>
                        <div class="bg-green-400/30 p-3 rounded-lg">
                            <i class="fas fa-tasks text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 text-white rounded-xl p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-yellow-100 text-sm font-medium">Exams</p>
                            <h3 class="text-2xl font-bold">{{ $exams->count() }}</h3>
                        </div>
                        <div class="bg-yellow-400/30 p-3 rounded-lg">
                            <i class="fas fa-clipboard-check text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm font-medium">Avg Grade</p>
                            <h3 class="text-2xl font-bold">
                                @php
                                    $allGrades = $grades->values();
                                    $avgGrade = $allGrades->count() > 0 ? round($allGrades->avg('score'), 1) : 0;
                                @endphp
                                {{ $avgGrade }}%
                            </h3>
                        </div>
                        <div class="bg-purple-400/30 p-3 rounded-lg">
                            <i class="fas fa-chart-line text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gradebook Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Gradebook</h2>
                            <p class="text-sm text-gray-600 mt-1">Student grade overview</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500">Auto-sync enabled</span>
                            <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky left-0 bg-gray-50 z-10">
                                    Student
                                </th>
                                @foreach($assignments as $assignment)
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[120px]">
                                    <div class="text-center">
                                        <div class="font-medium">{{ Str::limit($assignment->title, 15) }}</div>
                                        <div class="text-xs text-gray-400">({{ $assignment->marks ?? 100 }} marks)</div>
                                    </div>
                                </th>
                                @endforeach
                                @foreach($exams as $exam)
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[120px]">
                                    <div class="text-center">
                                        <div class="font-medium">{{ Str::limit($exam->title, 15) }}</div>
                                        <div class="text-xs text-gray-400">({{ $exam->total_points ?? 100 }} pts)</div>
                                    </div>
                                </th>
                                @endforeach
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[100px]">
                                    Course Avg
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($students as $student)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap sticky left-0 bg-white z-10">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-blue-600 flex items-center justify-center">
                                                <span class="text-white font-medium text-sm">
                                                    {{ strtoupper(substr($student->first_name, 0, 1)) }}{{ strtoupper(substr($student->last_name, 0, 1)) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $student->first_name }} {{ $student->last_name }}
                                            </div>
                                            <div class="text-sm text-gray-500">{{ $student->email }}</div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Assignment Grades -->
                                @foreach($assignments as $assignment)
                                @php
                                    $gradeKey = $student->id . '_App\Models\AssignmentSubmission_' . $assignment->id;
                                    $grade = $grades->get($gradeKey);
                                @endphp
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($grade)
                                        <div class="bg-green-100 text-green-800 px-3 py-1 rounded-lg text-sm font-medium">
                                            {{ $grade->points_earned }}/{{ $grade->total_points }}
                                            <div class="text-xs text-green-600">{{ $grade->score }}%</div>
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-sm">-</span>
                                    @endif
                                </td>
                                @endforeach

                                <!-- Exam Grades -->
                                @foreach($exams as $exam)
                                @php
                                    $gradeKey = $student->id . '_App\Models\Exam_' . $exam->id;
                                    $grade = $grades->get($gradeKey);
                                @endphp
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($grade)
                                        @php
                                            $maxPoints = $grade->points_possible ?? $grade->total_points;
                                            $percentage = $grade->percentage ?? $grade->score;
                                        @endphp
                                        <div class="bg-blue-100 text-blue-800 px-3 py-1 rounded-lg text-sm font-medium">
                                            {{ $grade->points_earned }}/{{ $maxPoints }}
                                            <div class="text-xs text-blue-600">{{ $percentage }}%</div>
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-sm">-</span>
                                    @endif
                                </td>
                                @endforeach

                                <!-- Course Average -->
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @php
                                        $studentGrade = $studentAverages[$student->id] ?? 0;
                                        $gradeColor = $studentGrade >= 90 ? 'green' : ($studentGrade >= 80 ? 'blue' : ($studentGrade >= 70 ? 'yellow' : ($studentGrade >= 60 ? 'orange' : 'red')));
                                        $bgColor = $gradeColor === 'green' ? 'bg-green-100 text-green-800' : 
                                                  ($gradeColor === 'blue' ? 'bg-blue-100 text-blue-800' : 
                                                  ($gradeColor === 'yellow' ? 'bg-yellow-100 text-yellow-800' : 
                                                  ($gradeColor === 'orange' ? 'bg-orange-100 text-orange-800' : 'bg-red-100 text-red-800')));
                                    @endphp
                                    <span class="px-3 py-1 rounded-lg text-sm font-medium {{ $bgColor }}">
                                        {{ $studentGrade }}%
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// No grade editing functionality - read-only gradebook
</script>

<style>
/* Sticky table styles */
.sticky {
    position: sticky;
    z-index: 10;
}

/* Responsive table */
@media (max-width: 768px) {
    .overflow-x-auto {
        overflow-x: auto;
    }
}
</style>
@endsection
