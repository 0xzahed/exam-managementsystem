@extends('layouts.dashboard')

@section('title', 'Grade Exam - ' . $exam->title)

@section('content')
<div class="max-w-4xl mx-auto p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Grade Exam</h1>
            <p class="text-gray-600">{{ $exam->title }} - {{ $attempt->student->name }}</p>
        </div>
        <a href="{{ route('instructor.exams.attempts', $exam) }}" 
           class="px-4 py-2 text-gray-600 hover:text-gray-800">
            ← Back
        </a>
    </div>

    <!-- Student Info -->
    <div class="bg-white border rounded-lg p-4 mb-6">
        <div class="grid grid-cols-3 gap-4 text-sm">
            <div>
                <span class="font-medium">Student:</span> {{ $attempt->student->name }}
            </div>
            <div>
                <span class="font-medium">Started:</span> {{ $attempt->started_at->format('M d, H:i') }}
            </div>
            <div>
                <span class="font-medium">Submitted:</span> {{ $attempt->submitted_at->format('M d, H:i') }}
            </div>
        </div>
    </div>

    <!-- Grading Form -->
    <form action="{{ route('instructor.exams.grade-attempt', [$exam, $attempt]) }}" method="POST">
        @csrf
        
        <!-- Total Score Display -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex justify-between items-center">
                <span class="font-medium">Total Score:</span>
                <div class="text-xl font-bold">
                    <span id="totalScore">{{ $totalCurrentPoints }}</span> / {{ $totalMaxPoints ?? 0 }}
                    (<span id="percentage">{{ $totalMaxPoints > 0 ? number_format(($totalCurrentPoints / $totalMaxPoints) * 100, 1) : 0 }}%</span>)
                </div>
            </div>
        </div>

        <!-- Questions -->
        @if($attempt->examAnswers && $attempt->examAnswers->count() > 0)
            @foreach($attempt->examAnswers as $index => $answer)
            <div class="bg-white border rounded-lg p-4 mb-4">
                <!-- Question Header -->
                <div class="flex justify-between items-start mb-3">
                    <div class="flex-1">
                        <h3 class="font-medium text-gray-800">Question {{ $index + 1 }}</h3>
                        <div class="text-gray-700 mt-2">{!! nl2br(e($answer->examQuestion->question)) !!}</div>
                    </div>
                    <div class="ml-4 flex items-center space-x-2">
                        <span class="text-sm text-gray-600">Grade:</span>
                        <input type="number" 
                               name="grades[{{ $answer->id }}]" 
                               value="{{ $answer->points_awarded ?? 0 }}"
                               max="{{ $answer->examQuestion->points }}"
                               min="0"
                               step="0.5"
                               class="w-16 px-2 py-1 border rounded text-center focus:ring-2 focus:ring-blue-500"
                               onchange="updateTotalScore()">
                        <span class="text-sm text-gray-600">/ {{ $answer->examQuestion->points }}</span>
                    </div>
                </div>

                <!-- Student Answer -->
                <div class="border-t pt-3">
                    <h4 class="text-sm font-medium text-gray-600 mb-2">Student Answer:</h4>
                    <div class="bg-gray-50 p-3 rounded border">
                        @if($answer->examQuestion->type === 'file_upload')
                            @if(is_array($answer->answer_files) && count($answer->answer_files) > 0)
                                @foreach($answer->answer_files as $file)
                                    <div class="flex items-center text-blue-600 mb-1">
                                        <i class="fas fa-file mr-2"></i>
                                        <a href="{{ $file['webViewLink'] ?? '#' }}" target="_blank" class="hover:underline">
                                            {{ $file['name'] ?? 'File' }}
                                        </a>
                                    </div>
                                @endforeach
                            @else
                                <span class="text-gray-500 italic">No file uploaded</span>
                            @endif
                        @else
                            <div class="text-gray-800">
                                {{ $answer->answer_text ?? 'No answer provided' }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        @else
            <!-- No Answers - Create from Exam Questions -->
            @if($exam->questions && $exam->questions->count() > 0)
                @foreach($exam->questions as $index => $question)
                <div class="bg-white border rounded-lg p-4 mb-4">
                    <!-- Question Header -->
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1">
                            <h3 class="font-medium text-gray-800">Question {{ $index + 1 }}</h3>
                            <div class="text-gray-700 mt-2">{!! nl2br(e($question->question)) !!}</div>
                        </div>
                        <div class="ml-4 flex items-center space-x-2">
                            <span class="text-sm text-gray-600">Grade:</span>
                            <input type="number" 
                                   name="questions[{{ $question->id }}]" 
                                   value="0"
                                   max="{{ $question->points }}"
                                   min="0"
                                   step="0.5"
                                   class="w-16 px-2 py-1 border rounded text-center focus:ring-2 focus:ring-blue-500"
                                   onchange="updateTotalScore()">
                            <span class="text-sm text-gray-600">/ {{ $question->points }}</span>
                        </div>
                    </div>

                    <!-- Student Answer -->
                    <div class="border-t pt-3">
                        <h4 class="text-sm font-medium text-gray-600 mb-2">Student Answer:</h4>
                        <div class="bg-gray-50 p-3 rounded border">
                            <span class="text-gray-500 italic">No answer recorded for this question</span>
                        </div>
                    </div>
                </div>
                @endforeach
            @else
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <p class="text-yellow-800">No questions found for this exam.</p>
                </div>
            @endif
        @endif

        <!-- Feedback -->
        <div class="bg-white border rounded-lg p-4 mb-6">
            <label class="block font-medium text-gray-700 mb-2">Feedback (Optional)</label>
            <textarea name="feedback" 
                      rows="3" 
                      class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500"
                      placeholder="Enter feedback for the student..."></textarea>
        </div>

        <!-- Hidden Input -->
        <input type="hidden" name="total_score" id="totalScoreInput" value="{{ $totalCurrentPoints }}">

        <!-- Save Button -->
        <div class="flex justify-end">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium">
                @if($attempt->status === 'graded')
                    Update Grade
                @else
                    Save Grade
                @endif
            </button>
        </div>
    </form>
</div>

<script>
function updateTotalScore() {
    let total = 0;
    
    // Handle existing exam answers
    const gradeInputs = document.querySelectorAll('input[name^="grades["]');
    gradeInputs.forEach(input => {
        const value = parseFloat(input.value) || 0;
        total += value;
    });
    
    // Handle direct question inputs
    const questionInputs = document.querySelectorAll('input[name^="questions["]');
    questionInputs.forEach(input => {
        const value = parseFloat(input.value) || 0;
        total += value;
    });
    
    const maxTotal = parseInt('{{ $totalMaxPoints ?? 0 }}') || 0;
    const percentage = maxTotal > 0 ? (total / maxTotal) * 100 : 0;
    
    document.getElementById('totalScore').textContent = total;
    document.getElementById('percentage').textContent = percentage.toFixed(1) + '%';
    document.getElementById('totalScoreInput').value = total;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateTotalScore();
    
    // Add change listeners to all grade inputs
    const allInputs = document.querySelectorAll('input[name^="grades["], input[name^="questions["]');
    allInputs.forEach(input => {
        input.addEventListener('input', updateTotalScore);
    });
});
</script>
@endsection
