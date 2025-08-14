@extends('layouts.dashboard')

@section('title', $course->title . ' - Grades')

@section('content')
<div class="px-0 pt-2 md:pt-0">
    <div class="py-2 md:py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <a href="{{ route('student.grades.index') }}" 
                               class="text-gray-600 hover:text-gray-900 transition-colors">
                                <i class="fas fa-arrow-left text-lg"></i>
                            </a>
                            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $course->title }} - Grades</h1>
                        </div>
                        <p class="text-gray-600">{{ $course->description }}</p>
                    </div>
                </div>
            </div>
            <!-- Assignments Section -->
            @if($assignments->count() > 0)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-tasks text-green-500 mr-2"></i>
                        Assignment Grades
                    </h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Assignment
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Due Date
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Grade
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Percentage
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Feedback
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($assignments as $assignment)
                            @php
                                $gradeKey = 'App\Models\AssignmentSubmission_' . $assignment->id;
                                $grade = $grades->get($gradeKey);
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $assignment->title }}</div>
                                        <div class="text-sm text-gray-500">{{ Str::limit($assignment->description, 50) }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                    {{ $assignment->due_date ? $assignment->due_date->format('M j, Y') : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                    @if($grade)
                                        <div class="font-semibold text-lg">
                                            {{ $grade->points_earned }}/{{ $grade->total_points }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $grade->letter_grade ?? 'N/A' }}
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-sm">Not Graded</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                    {{ $grade ? $grade->score . '%' : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($grade && $grade->feedback)
                                        <button onclick="showFeedback('{{ $grade->feedback }}')"
                                                class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            View Feedback
                                        </button>
                                    @else
                                        <span class="text-gray-400 text-sm">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- Exams Section -->
            @if($exams->count() > 0)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-clipboard-check text-yellow-500 mr-2"></i>
                        Exam Grades
                    </h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Exam
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Grade
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Percentage
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Feedback
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($exams as $exam)
                            @php
                                $gradeKey = 'App\Models\Exam_' . $exam->id;
                                $grade = $grades->get($gradeKey);
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $exam->title }}</div>
                                        <div class="text-sm text-gray-500">{{ Str::limit($exam->description, 50) }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                    {{ $exam->start_time ? $exam->start_time->format('M j, Y') : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                    @if($grade)
                                        <div class="font-semibold text-lg">
                                            {{ $grade->points_earned }}/{{ $grade->points_possible ?? $grade->total_points }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $grade->letter_grade ?? 'N/A' }}
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-sm">Not Taken</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                    {{ $grade ? ($grade->percentage ?? $grade->score) . '%' : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($grade && $grade->feedback)
                                        <button onclick="showFeedback('{{ $grade->feedback }}')"
                                                class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            View Feedback
                                        </button>
                                    @else
                                        <span class="text-gray-400 text-sm">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- No Grades Message -->
            @if($assignments->count() == 0 && $exams->count() == 0)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <div class="max-w-md mx-auto">
                    <i class="fas fa-chart-bar text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No Grades Available</h3>
                    <p class="text-gray-600 mb-6">This course doesn't have any assignments or exams yet, or none have been graded.</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Feedback Modal -->
<div id="feedbackModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Instructor Feedback</h3>
            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                <p id="feedbackText" class="text-gray-700 whitespace-pre-wrap"></p>
            </div>
            <div class="flex justify-end">
                <button onclick="closeFeedbackModal()"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function showFeedback(feedback) {
    document.getElementById('feedbackText').textContent = feedback;
    document.getElementById('feedbackModal').classList.remove('hidden');
}

function closeFeedbackModal() {
    document.getElementById('feedbackModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('feedbackModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeFeedbackModal();
    }
});
</script>

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

.grade-cell {
    transition: all 0.2s ease;
}

.grade-cell:hover {
    transform: scale(1.05);
}
</style>
@endsection
