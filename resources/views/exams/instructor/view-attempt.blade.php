@extends('layouts.dashboard')

@section('title', 'Grade Attempt - ' . $exam->title)

@section('content')
<div class="px-0 pt-2 md:pt-0">
    <div class="py-2 md:py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <a href="{{ route('instructor.exams.attempts', $exam) }}"
                            class="mr-4 p-2 text-gray-600 hover:text-gray-800 rounded-lg hover:bg-gray-100"
                            title="Back to Attempts">
                            <i class="fas fa-arrow-left text-xl"></i>
                        </a>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Grade Exam Attempt</h1>
                            <p class="mt-2 text-gray-600">{{ $exam->title }} - {{ $attempt->student->name }}</p>
                        </div>
                    </div>
                    
                    @if($attempt->status === 'graded')
                        <div class="flex items-center space-x-2 bg-green-50 px-4 py-2 rounded-lg border border-green-200">
                            <i class="fas fa-check-circle text-green-600"></i>
                            <span class="text-green-800 font-medium">Already Graded</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Student Info & Attempt Summary -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- Student Info -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center space-x-4">
                        <div class="h-12 w-12 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-blue-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $attempt->student->name }}</h3>
                            <p class="text-gray-600 text-sm">{{ $attempt->student->email }}</p>
                            <p class="text-gray-500 text-xs">Student ID: {{ $attempt->student->student_id ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Timing Info -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Started At</p>
                            <p class="text-lg font-semibold text-gray-900">
                                {{ $attempt->started_at ? $attempt->started_at->format('M d, Y') : 'N/A' }}
                            </p>
                            <p class="text-sm text-gray-500">
                                {{ $attempt->started_at ? $attempt->started_at->format('h:i A') : '' }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-600">Submitted At</p>
                            <p class="text-lg font-semibold text-gray-900">
                                {{ $attempt->submitted_at ? $attempt->submitted_at->format('M d, Y') : 'N/A' }}
                            </p>
                            <p class="text-sm text-gray-500">
                                {{ $attempt->submitted_at ? $attempt->submitted_at->format('h:i A') : '' }}
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Current Score -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="text-center">
                        <p class="text-sm font-medium text-gray-600 mb-2">Current Score</p>
                        @if($attempt->status === 'graded' && $attempt->total_score !== null)
                            <p class="text-3xl font-bold text-blue-600">
                                {{ $attempt->total_score }}<span class="text-xl text-gray-500">/{{ $attempt->max_score ?? $exam->questions->sum('points') }}</span>
                            </p>
                            <p class="text-sm text-gray-500 mt-1">{{ number_format($attempt->score, 1) }}%</p>
                        @else
                            <p class="text-3xl font-bold text-orange-600">
                                Pending
                            </p>
                            <p class="text-sm text-gray-500 mt-1">Not graded yet</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Grading Form -->
            <form id="gradingForm" action="{{ route('instructor.exams.grade-attempt', [$exam, $attempt]) }}" method="POST" class="space-y-6">
                @csrf
                
                <!-- Questions and Answers -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Questions & Answers</h2>
                        <p class="text-sm text-gray-600 mt-1">Grade each question individually</p>
                    </div>
                    
                    <div class="divide-y divide-gray-200">
                        @php $totalMaxPoints = 0; $totalCurrentPoints = 0; @endphp
                        @foreach($attempt->examAnswers as $index => $answer)
                            @php 
                                $totalMaxPoints += $answer->examQuestion->points;
                                $totalCurrentPoints += $answer->points_awarded ?? 0;
                            @endphp
                            <div class="p-6">
                                <!-- Question Header -->
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="bg-blue-100 text-blue-600 rounded-full w-8 h-8 flex items-center justify-center font-semibold text-sm">
                                            {{ $index + 1 }}
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900">Question {{ $index + 1 }}</h3>
                                            <p class="text-sm text-gray-600">Maximum Marks: {{ $answer->examQuestion->points }}</p>
                                        </div>
                                    </div>
                                    
                                    <!-- Points Input -->
                                    <div class="flex items-center space-x-3">
                                        <label for="grade_{{ $answer->id }}" class="text-sm font-medium text-gray-700">Marks:</label>
                                        <input type="number" 
                                               name="grades[{{ $answer->id }}]" 
                                               id="grade_{{ $answer->id }}"
                                               value="{{ $answer->points_awarded ?? 0 }}"
                                               max="{{ $answer->examQuestion->points }}"
                                               min="0"
                                               step="0.5"
                                               class="w-20 px-3 py-2 border border-gray-300 rounded-lg text-center font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                               onchange="updateTotalScore()">
                                        <span class="text-gray-500">/ {{ $answer->examQuestion->points }}</span>
                                    </div>
                                </div>
                                
                                <!-- Question Text -->
                                <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                                    <div class="text-gray-900 leading-relaxed">{!! nl2br(e($answer->examQuestion->question)) !!}</div>
                                </div>
                                
                                <!-- Student Answer -->
                                <div class="border-t border-gray-200 pt-4">
                                    <h4 class="text-sm font-medium text-gray-600 mb-2">Student Answer:</h4>
                                    <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                                        @if($answer->examQuestion->type === 'file_upload')
                                            @if(is_array($answer->answer_files) && count($answer->answer_files) > 0)
                                                <div class="space-y-2">
                                                    @foreach($answer->answer_files as $file)
                                                        <div class="flex items-center space-x-2 text-blue-600">
                                                            <i class="fas fa-file-alt"></i>
                                                            <a class="hover:underline font-medium" href="{{ $file['webViewLink'] ?? '#' }}" target="_blank">
                                                                {{ $file['name'] ?? 'File' }}
                                                            </a>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="flex items-center text-gray-500">
                                                    <i class="fas fa-exclamation-circle mr-2"></i>
                                                    <span class="italic">No file uploaded</span>
                                                </div>
                                            @endif
                                        @else
                                            <div class="text-gray-900 leading-relaxed">
                                                {{ $answer->answer_text ?? 'No answer provided' }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Grading Summary & Feedback -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Score Summary -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Score Summary</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                    <span class="font-medium text-gray-700">Total Marks:</span>
                                    <span id="totalScore" class="text-xl font-bold text-blue-600">{{ $totalCurrentPoints }}</span>
                                    <span class="text-gray-500">/ {{ $totalMaxPoints }}</span>
                                </div>
                                <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg">
                                    <span class="font-medium text-gray-700">Percentage:</span>
                                    <span id="percentage" class="text-xl font-bold text-blue-600">
                                        {{ $totalMaxPoints > 0 ? number_format(($totalCurrentPoints / $totalMaxPoints) * 100, 1) : 0 }}%
                                    </span>
                                </div>
                            </div>
                            
                            <input type="hidden" name="total_score" id="totalScoreInput" value="{{ $totalCurrentPoints }}">
                        </div>
                        
                        <!-- Feedback -->
                        <div>
                            <label for="feedback" class="block text-sm font-medium text-gray-700 mb-2">
                                Feedback (Optional)
                            </label>
                            <textarea name="feedback" 
                                      id="feedback" 
                                      rows="6" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                                      placeholder="Provide feedback to the student...">{{ $attempt->feedback }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-between items-center">
                    <a href="{{ route('instructor.exams.attempts', $exam) }}" 
                       class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Attempts
                    </a>
                    
                    <div class="flex space-x-3">
                        @if($attempt->status === 'graded')
                            <button type="submit" 
                                    class="inline-flex items-center px-6 py-3 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                                <i class="fas fa-edit mr-2"></i>
                                Update Grade
                            </button>
                        @else
                            <button type="submit" 
                                    class="inline-flex items-center px-6 py-3 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                <i class="fas fa-save mr-2"></i>
                                Save Grade
                            </button>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateTotalScore() {
    let total = 0;
    const inputs = document.querySelectorAll('input[name^="grades["]');
    
    inputs.forEach(input => {
        const value = parseFloat(input.value) || 0;
        total += value;
    });
    
    const maxTotal = {{ $totalMaxPoints }};
    const percentage = maxTotal > 0 ? (total / maxTotal) * 100 : 0;
    
    document.getElementById('totalScore').textContent = total;
    document.getElementById('percentage').textContent = percentage.toFixed(1) + '%';
    document.getElementById('totalScoreInput').value = total;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateTotalScore();
    
    // Add change listeners to all grade inputs
    const inputs = document.querySelectorAll('input[name^="grades["]');
    inputs.forEach(input => {
        input.addEventListener('input', updateTotalScore);
    });
});
</script>
@endsection
