@extends('layouts.dashboard')

@section('title', $course->title . ' - Gradebook')

@section('content')
<div class="px-0 pt-2 md:pt-0">
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
                            <p class="text-sm text-gray-600 mt-1">Click on any grade to edit it</p>
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
                                        <div class="text-xs text-gray-400">({{ $assignment->marks ?? 100 }} pts)</div>
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
                                        <button onclick="editGrade({{ $grade->id }}, '{{ $grade->points_earned }}', '{{ $grade->feedback ?? '' }}', {{ $grade->total_points }})"
                                                class="grade-cell bg-green-100 text-green-800 px-3 py-1 rounded-lg text-sm font-medium hover:bg-green-200 transition-colors cursor-pointer">
                                            {{ $grade->points_earned }}/{{ $grade->total_points }}
                                            <div class="text-xs text-green-600">{{ $grade->score }}%</div>
                                        </button>
                                    @else
                                        <span class="text-gray-400 text-sm">-</span>
                                    @endif
                                </td>
                                @endforeach

                                <!-- Exam Grades -->
                                @foreach($exams as $exam)
                                @php
                                    $gradeKey = $student->id . '_App\Models\ExamAttempt_' . $exam->id;
                                    $grade = $grades->get($gradeKey);
                                @endphp
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($grade)
                                        <button onclick="editGrade({{ $grade->id }}, '{{ $grade->points_earned }}', '{{ $grade->feedback ?? '' }}', {{ $grade->total_points }})"
                                                class="grade-cell bg-blue-100 text-blue-800 px-3 py-1 rounded-lg text-sm font-medium hover:bg-blue-200 transition-colors cursor-pointer">
                                            {{ $grade->points_earned }}/{{ $grade->total_points }}
                                            <div class="text-xs text-blue-600">{{ $grade->score }}%</div>
                                        </button>
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

<!-- Grade Edit Modal -->
<div id="gradeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Grade</h3>
            <form id="gradeForm">
                @csrf
                <input type="hidden" id="gradeId" name="grade_id">
                
                <div class="mb-4">
                    <label for="pointsEarned" class="block text-sm font-medium text-gray-700 mb-2">
                        Points Earned
                    </label>
                    <input type="number" id="pointsEarned" name="points_earned" min="0" step="0.01" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Total points: <span id="totalPoints">0</span></p>
                </div>

                <div class="mb-4">
                    <label for="feedback" class="block text-sm font-medium text-gray-700 mb-2">
                        Feedback (Optional)
                    </label>
                    <textarea id="feedback" name="feedback" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                              placeholder="Add feedback for the student..."></textarea>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeGradeModal()"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Update Grade
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function editGrade(gradeId, pointsEarned, feedback, totalPoints) {
    document.getElementById('gradeId').value = gradeId;
    document.getElementById('pointsEarned').value = pointsEarned;
    document.getElementById('feedback').value = feedback;
    document.getElementById('totalPoints').textContent = totalPoints;
    document.getElementById('gradeModal').classList.remove('hidden');
}

function closeGradeModal() {
    document.getElementById('gradeModal').classList.add('hidden');
}

document.getElementById('gradeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('{{ route("instructor.gradebook.update-grade") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showNotification('Grade updated successfully!', 'success');
            
            // Reload page to show updated grades
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showError('Error updating grade: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Error updating grade. Please try again.');
    });
});

// Close modal when clicking outside
document.getElementById('gradeModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeGradeModal();
    }
});

// Deprecated inline notification removed
</script>

<style>
.grade-cell {
    transition: all 0.2s ease;
}

.grade-cell:hover {
    transform: scale(1.05);
}

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
