@extends('layouts.dashboard')

@section('title', 'Exam Results - ' . $exam->title)

@section('content')
<div class="px-0 pt-2 md:pt-0">
    <div class="py-2 md:py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div class="flex items-center">
                        <a href="{{ route('instructor.exams.index') }}"
                            class="mr-4 p-2 text-gray-600 hover:text-gray-800 rounded-lg hover:bg-gray-100"
                            title="Back to Exams">
                            <i class="fas fa-arrow-left text-xl"></i>
                        </a>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Exam Results</h1>
                            <p class="mt-2 text-gray-600">{{ $exam->title }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('instructor.exams.show', $exam) }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                            <i class="fas fa-eye mr-2"></i>
                            View Exam
                        </a>
                        <a href="{{ route('instructor.exams.attempts', $exam) }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                            <i class="fas fa-list mr-2"></i>
                            View Attempts
                        </a>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">Total Attempts</p>
                            <h3 class="text-2xl font-bold">{{ $exam->attempts->count() }}</h3>
                        </div>
                        <div class="bg-blue-400/30 p-3 rounded-lg">
                            <i class="fas fa-users text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-xl p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium">Completed</p>
                            <h3 class="text-2xl font-bold">{{ $exam->attempts->where('status', 'completed')->count() }}</h3>
                        </div>
                        <div class="bg-green-400/30 p-3 rounded-lg">
                            <i class="fas fa-check-circle text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 text-white rounded-xl p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-yellow-100 text-sm font-medium">In Progress</p>
                            <h3 class="text-2xl font-bold">{{ $exam->attempts->where('status', 'in_progress')->count() }}</h3>
                        </div>
                        <div class="bg-yellow-400/30 p-3 rounded-lg">
                            <i class="fas fa-clock text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm font-medium">Average Score</p>
                            <h3 class="text-2xl font-bold">
                                @php
                                $completedAttempts = $exam->attempts->where('status', 'completed')->where('score', '!=', null);
                                $avgScore = $completedAttempts->count() > 0 ? $completedAttempts->avg('score') : 0;
                                @endphp
                                {{ number_format($avgScore, 1) }}%
                            </h3>
                        </div>
                        <div class="bg-purple-400/30 p-3 rounded-lg">
                            <i class="fas fa-chart-line text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Student Results</h2>
                            <p class="text-sm text-gray-600 mt-1">Detailed results for all students who attempted this exam</p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <button onclick="exportResults()" 
                                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                                <i class="fas fa-download mr-2"></i>
                                Export Results
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Student
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Score
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Marks
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Started
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Completed
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Duration
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($exam->attempts as $attempt)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                <span class="text-sm font-medium text-gray-700">
                                                    {{ substr($attempt->student->first_name, 0, 1) }}{{ substr($attempt->student->last_name, 0, 1) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $attempt->student->first_name }} {{ $attempt->student->last_name }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $attempt->student->email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $attempt->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                           ($attempt->status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                        {{ ucfirst(str_replace('_', ' ', $attempt->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($attempt->score !== null)
                                        <div class="text-sm font-medium text-gray-900">{{ number_format($attempt->score, 1) }}%</div>
                                        <div class="text-sm text-gray-500">
                                            @if($attempt->score >= $exam->passing_score)
                                                <span class="text-green-600">Passed</span>
                                            @else
                                                <span class="text-red-600">Failed</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($attempt->marks_obtained !== null)
                                        {{ $attempt->marks_obtained }}/{{ $exam->total_points }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $attempt->started_at ? $attempt->started_at->format('M j, g:i A') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $attempt->completed_at ? $attempt->completed_at->format('M j, g:i A') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($attempt->started_at && $attempt->completed_at)
                                        @php
                                            $duration = $attempt->started_at->diffInMinutes($attempt->completed_at);
                                        @endphp
                                        {{ $duration }} min
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="#" onclick="viewAttemptDetails({{ $attempt->id }})" 
                                       class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($attempt->status === 'completed')
                                        <a href="#" onclick="downloadAttempt({{ $attempt->id }})" 
                                           class="text-green-600 hover:text-green-900">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-clipboard-list text-4xl text-gray-300 mb-4"></i>
                                        <p class="text-lg font-medium text-gray-900 mb-2">No Attempts Yet</p>
                                        <p class="text-gray-600">Students haven't attempted this exam yet.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Attempt Details Modal -->
<div id="attemptModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Attempt Details</h3>
                <button onclick="closeAttemptModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div id="attemptDetails" class="space-y-4">
                <!-- Attempt details will be loaded here -->
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function exportResults() {
    // Implement export functionality
    showInfo('Export functionality will be implemented here');
}

function viewAttemptDetails(attemptId) {
    // Load attempt details via AJAX
    fetch(`/instructor/exam-attempts/${attemptId}/details`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('attemptDetails').innerHTML = data.html;
            document.getElementById('attemptModal').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error loading attempt details:', error);
            showError('Failed to load attempt details');
        });
}

function closeAttemptModal() {
    document.getElementById('attemptModal').classList.add('hidden');
}

function downloadAttempt(attemptId) {
    // Implement download functionality
    showInfo('Download functionality will be implemented here');
}

// Close modal when clicking outside
document.getElementById('attemptModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeAttemptModal();
    }
});
</script>
@endsection
