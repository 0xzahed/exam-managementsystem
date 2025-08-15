@extends('layouts.dashboard')

@section('title', 'Take Exam - ' . $exam->title)

@section('content')
<div class="min-h-screen bg-gray-50">

    <!-- Main Content -->
    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Page Header with Timer and Actions -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center">
                    <h1 class="text-lg font-semibold text-gray-900">{{ $exam->title }}</h1>
                    <span class="ml-4 px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                        {{ $exam->course->title }}
                    </span>
                </div>
                <div class="flex items-center space-x-6">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm text-gray-600">Time Remaining:</span>
                        <div id="timer" class="font-mono text-lg font-bold text-red-600" aria-live="polite">
                            Loading...
                        </div>
                    </div>
                    <div id="autoSaveStatus" class="text-sm text-gray-500">
                        <span class="saving hidden">
                            <i class="fas fa-spinner fa-spin mr-1"></i>Saving...
                        </span>
                        <span class="saved">
                            <i class="fas fa-check text-green-500 mr-1"></i>All changes saved
                        </span>
                        <span class="error hidden">
                            <i class="fas fa-exclamation-triangle text-red-500 mr-1"></i>Save failed
                        </span>
                    </div>
                    <button type="button" id="submitExamBtn" 
                            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium"
                            onclick="handleSubmitClick()">
                        Submit Exam
                    </button>
                </div>
            </div>
            
            <!-- Exam Info Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6 p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Duration</h3>
                        <p class="text-lg text-gray-900">{{ $exam->duration_minutes }} minutes</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Total Questions</h3>
                        <p class="text-lg text-gray-900">{{ $exam->questions->count() }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Total Marks</h3>
                        <p class="text-lg text-gray-900">{{ $exam->total_points }}</p>
                    </div>
                </div>
                
                @if($exam->description)
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Instructions</h3>
                        <div class="text-gray-700 prose prose-sm max-w-none">
                            {!! nl2br(e($exam->description)) !!}
                        </div>
                    </div>
                @endif

                @if($exam->attachments && count($exam->attachments) > 0)
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h3 class="text-sm font-medium text-gray-500 mb-3">Exam Materials</h3>
                        <div class="space-y-2">
                            @foreach($exam->attachments as $attachment)
                                <a href="{{ $attachment['url'] }}" target="_blank" 
                                   class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                    <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                    </svg>
                                    <span class="text-sm text-gray-900">{{ $attachment['file_name'] }}</span>
                                    <svg class="w-4 h-4 text-gray-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Questions Form -->
            <form id="examForm" data-exam-id="{{ $exam->id }}" data-attempt-id="{{ $attempt->id }}">
                @csrf
                
                @foreach($exam->questions as $index => $question)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6 p-6 question-card" 
                         data-question-id="{{ $question->id }}">
                        
                        <!-- Question Header -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <div class="flex items-center mb-2">
                                    <span class="inline-flex items-center justify-center w-8 h-8 bg-blue-100 text-blue-800 rounded-full text-sm font-medium mr-3">
                                        {{ $index + 1 }}
                                    </span>
                                    <span class="text-lg font-semibold text-gray-900">Question {{ $index + 1 }}</span>
                                    @if($question->required)
                                        <span class="ml-2 text-red-500">*</span>
                                    @endif
                                </div>
                                <div class="text-gray-700 mb-4">
                                    {!! nl2br(e($question->question)) !!}
                                </div>
                            </div>
                            <div class="ml-4 text-right">
                                <span class="text-sm text-gray-500">Marks:</span>
                                <span class="font-medium text-gray-900">{{ $question->points }}</span>
                            </div>
                        </div>

                        <!-- Question Type Badge -->
                        <div class="mb-4">
                            @if($question->type === 'mcq')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Multiple Choice
                                </span>
                            @elseif($question->type === 'short_answer')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Short Answer
                                </span>
                            @elseif($question->type === 'file_upload')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    File Upload
                                </span>
                            @endif
                        </div>

                        <!-- Answer Section -->
                        <div class="answer-section">
                            @if($question->type === 'mcq')
                                <!-- MCQ Options -->
                                <div class="space-y-3">
                                    @php
                                        $existingAnswer = $existingAnswers->where('exam_question_id', $question->id)->first();
                                        $selectedAnswer = $existingAnswer ? $existingAnswer->answer_text : null;
                                    @endphp
                                    @foreach($question->options as $optionIndex => $option)
                                        <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                                            <input type="radio" 
                                                   name="question_{{ $question->id }}" 
                                                   value="{{ $option }}"
                                                   class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500"
                                                   data-question-id="{{ $question->id }}"
                                                   {{ $selectedAnswer === $option ? 'checked' : '' }}>
                                            <span class="ml-3 w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center text-sm font-medium text-gray-600 mr-3">
                                                {{ chr(65 + $optionIndex) }}
                                            </span>
                                            <span class="text-gray-900">{{ $option }}</span>
                                        </label>
                                    @endforeach
                                </div>

                            @elseif($question->type === 'short_answer')
                                <!-- Short Answer -->
                                @php
                                    $existingAnswer = $existingAnswers->where('exam_question_id', $question->id)->first();
                                    $answerText = $existingAnswer ? $existingAnswer->answer_text : '';
                                @endphp
                                <textarea name="answers[{{ $question->id }}]" 
                                          rows="6"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                          placeholder="Enter your answer here..."
                                          data-question-id="{{ $question->id }}">{{ $answerText }}</textarea>

                            @elseif($question->type === 'file_upload')
                                <!-- File Upload -->
                                @php
                                    $existingAnswer = $existingAnswers->where('exam_question_id', $question->id)->first();
                                    $hasFile = $existingAnswer && $existingAnswer->answer_text;
                                @endphp
                                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors">
                                    <input type="file" 
                                           name="answers[{{ $question->id }}]" 
                                           class="hidden file-input"
                                           id="file_{{ $question->id }}"
                                           accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png"
                                           data-question-id="{{ $question->id }}">
                                    <label for="file_{{ $question->id }}" class="cursor-pointer">
                                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                        </svg>
                                        <div class="text-gray-600">
                                            <span class="font-medium text-blue-600 hover:text-blue-500">Click to upload</span>
                                            or drag and drop
                                        </div>
                                        <p class="text-sm text-gray-500 mt-1">PDF, DOC, DOCX, TXT, JPG, PNG (Max 10MB)</p>
                                    </label>
                                    <div class="uploaded-file {{ $hasFile ? '' : 'hidden' }} mt-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                <span class="text-sm text-green-800 file-name">{{ $hasFile ? $existingAnswer->answer_text : '' }}</span>
                                            </div>
                                            <button type="button" class="text-red-600 hover:text-red-800 remove-file">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Answer Status -->
                        <div class="mt-4 flex items-center justify-between">
                            <div class="answer-status text-sm">
                                <span class="unanswered text-gray-500">Not answered</span>
                                <span class="answered hidden text-green-600">✓ Answered</span>
                            </div>
                            <div class="auto-save-indicator text-xs text-gray-400">
                                <span class="saving hidden">Saving...</span>
                                <span class="saved">Auto-saved</span>
                            </div>
                        </div>
                    </div>
                @endforeach

                <!-- Question Navigation -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Question Overview</h3>
                    <div class="grid grid-cols-5 sm:grid-cols-10 gap-2">
                        @foreach($exam->questions as $index => $question)
                            <button type="button" 
                                    class="question-nav-btn w-10 h-10 rounded-lg border border-gray-300 text-sm font-medium transition-colors hover:bg-gray-50"
                                    data-question-index="{{ $index }}"
                                    data-question-id="{{ $question->id }}">
                                {{ $index + 1 }}
                            </button>
                        @endforeach
                    </div>
                    <div class="mt-4 flex items-center space-x-6 text-sm">
                        <div class="flex items-center">
                            <div class="w-4 h-4 bg-green-100 border border-green-300 rounded mr-2"></div>
                            <span class="text-gray-600">Answered</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-4 h-4 bg-white border border-gray-300 rounded mr-2"></div>
                            <span class="text-gray-600">Not answered</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-4 h-4 bg-blue-100 border border-blue-300 rounded mr-2"></div>
                            <span class="text-gray-600">Current question</span>
                        </div>
                    </div>
                </div>

                <!-- Final Submit Section -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="text-center">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Ready to Submit?</h3>
                        <p class="text-gray-600 mb-6">Please review your answers before submitting. You cannot change answers after submission.</p>
                        
                        <div class="flex items-center justify-center space-x-4">
                            <button type="button" id="reviewAnswers" 
                                    class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                                Review Answers
                            </button>
                            <button type="button" id="finalSubmit" 
                                    onclick="handleSubmitClick()"
                                    class="px-8 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium">
                                Submit Exam
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Submit Confirmation Modal -->
<div id="submitConfirmation" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <div class="text-center">
                <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Submit Exam?</h3>
                <p class="text-gray-600 mb-6">
                    Are you sure you want to submit your exam? You cannot change your answers after submission.
                </p>
                <div class="answered-summary mb-6 text-sm">
                    <span class="answered-count font-medium">0</span> of 
                    <span class="total-questions font-medium">{{ $exam->questions->count() }}</span> questions answered
                </div>
                <div class="flex space-x-3">
                    <button id="cancelSubmit" onclick="handleCancelSubmit()" 
                            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        Continue Exam
                    </button>
                    <button id="confirmSubmit" onclick="handleConfirmSubmit()"
                            class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        Submit Now
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
@php
    $examConfig = [
        'examId' => $exam->id,
        'attemptId' => $attempt->id,
        'durationMinutes' => (int) $exam->duration_minutes,
        'startTime' => $attempt->started_at->toIso8601String(),
        'totalQuestions' => $exam->questions->count(),
        'autoSaveInterval' => 15000,
        'existingAnswers' => $formattedAnswers ?? [],
        'routes' => [
            'saveAnswer' => route('student.exams.save-answer', $exam),
            'submit' => route('student.exams.submit', $exam),
            'getTime' => route('student.exams.time', $exam),
        ],
    ];
@endphp

<div id="examConfigData" data-config='@json($examConfig)'></div>

<script>
    // Define global functions immediately
    console.log('=== DEFINING GLOBAL FUNCTIONS ===');
    
    window.handleSubmitClick = function() {
        console.log('Direct submit click handler called');
        if (window.examTaker && window.examTaker.showSubmitConfirmation) {
            console.log('Using ExamTaker submit');
            window.examTaker.showSubmitConfirmation();
        } else {
            console.log('ExamTaker not found, using fallback');
            window.showSubmitConfirmationFallback();
        }
    };
    
    window.handleConfirmSubmit = function() {
        console.log('Direct confirm submit handler called');
        if (window.examTaker && window.examTaker.submitExam) {
            console.log('Using ExamTaker submit');
            window.examTaker.submitExam();
        } else {
            console.log('ExamTaker not found, submitting via form');
            window.submitExamFallback();
        }
    };
    
    window.handleCancelSubmit = function() {
        console.log('Direct cancel submit handler called');
        if (window.examTaker && window.examTaker.hideSubmitConfirmation) {
            window.examTaker.hideSubmitConfirmation();
        } else {
            const modal = document.getElementById('submitConfirmation');
            if (modal) {
                modal.classList.add('hidden');
            }
            console.log('Modal hidden via fallback');
        }
    };
    
    window.showSubmitConfirmationFallback = function() {
        console.log('Showing submit confirmation fallback');
        const modal = document.getElementById('submitConfirmation');
        if (modal) {
            modal.classList.remove('hidden');
            console.log('Modal shown');
        } else {
            console.log('Modal not found, using browser confirm');
            if (confirm('Are you sure you want to submit your exam?')) {
                window.handleConfirmSubmit();
            }
        }
    };
    
    window.submitExamFallback = function() {
        console.log('Submit exam fallback called');
        
        if (!window.examConfig) {
            console.error('No exam config found');
            alert('Error: Cannot submit exam. Please refresh and try again.');
            return;
        }
        
        // Collect all answers
        const form = document.getElementById('examForm');
        if (!form) {
            console.error('Exam form not found');
            alert('Error: Cannot find exam form. Please refresh and try again.');
            return;
        }
        
        // Add CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            console.error('CSRF token not found');
            alert('Error: Security token missing. Please refresh and try again.');
            return;
        }
        
        // Submit form
        const formData = new FormData(form);
        formData.append('_token', csrfToken.getAttribute('content'));
        
        console.log('Submitting exam via fallback...');
        
        fetch(window.examConfig.routes.submit, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken.getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('Submit response:', data);
            if (data.success) {
                alert('Exam submitted successfully!');
                window.location.href = data.redirect || '/student/exams';
            } else {
                alert('Error: ' + (data.message || 'Failed to submit exam'));
            }
        })
        .catch(error => {
            console.error('Submit error:', error);
            alert('Error: Failed to submit exam. Please try again.');
        });
    };
    
    // Simple timer fallback
    window.startSimpleTimer = function() {
        console.log('Starting simple timer fallback');
        
        if (!window.examConfig || !window.examConfig.durationMinutes) {
            console.error('No duration found for simple timer');
            const timerEl = document.getElementById('timer');
            if (timerEl) timerEl.textContent = 'Timer Error';
            return;
        }
        
        const startTime = new Date(window.examConfig.startTime);
        const durationMs = window.examConfig.durationMinutes * 60 * 1000;
        
        const timerEl = document.getElementById('timer');
        if (!timerEl) {
            console.error('Timer element not found');
            return;
        }
        
        const updateTimer = () => {
            const now = new Date();
            const elapsed = now - startTime;
            const remaining = Math.max(0, durationMs - elapsed);
            
            const hours = Math.floor(remaining / (1000 * 60 * 60));
            const minutes = Math.floor((remaining % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((remaining % (1000 * 60)) / 1000);
            
            const timeString = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            timerEl.textContent = timeString;
            
            console.log('Timer updated:', timeString, 'Remaining ms:', remaining);
            
            if (remaining <= 0) {
                clearInterval(window.simpleTimerInterval);
                alert('Time is up! Please submit your exam.');
                window.handleSubmitClick();
            }
        };
        
        updateTimer();
        window.simpleTimerInterval = setInterval(updateTimer, 1000);
        console.log('Simple timer started successfully');
    };

    // Load exam configuration immediately
    (function() {
        var cfgEl = document.getElementById('examConfigData');
        console.log('=== EXAM CONFIG LOADING ===');
        console.log('Config element found:', !!cfgEl);
        
        if (cfgEl) {
            try { 
                var configData = cfgEl.getAttribute('data-config');
                console.log('Raw config data length:', configData ? configData.length : 0);
                
                window.examConfig = JSON.parse(configData); 
                console.log('Exam config loaded successfully:', window.examConfig);
                console.log('Duration minutes:', window.examConfig.durationMinutes);
                console.log('Start time:', window.examConfig.startTime);
                console.log('Routes:', window.examConfig.routes);
                
                // Start timer immediately
                window.startSimpleTimer();
                
            } catch (e) { 
                console.error('Failed to parse exam config:', e);
                console.error('Config data that failed:', configData);
                window.examConfig = null;
                
                // Show error in timer
                const timerEl = document.getElementById('timer');
                if (timerEl) timerEl.textContent = 'Config Error';
            }
        } else {
            console.error('Exam config element not found');
            const timerEl = document.getElementById('timer');
            if (timerEl) timerEl.textContent = 'No Config';
        }
        
        // Check if timer element exists
        var timerEl = document.getElementById('timer');
        console.log('Timer element found:', !!timerEl);
        if (timerEl) {
            console.log('Timer element text:', timerEl.textContent);
        }
        
        // Check if submit button exists
        var submitBtn = document.getElementById('finalSubmit');
        console.log('Submit button found:', !!submitBtn);
        
        console.log('=== CONFIG LOADING COMPLETE ===');
        console.log('Functions defined:', typeof window.handleSubmitClick, typeof window.handleConfirmSubmit);
    })();
</script>
@vite('resources/js/pages/exams/take.js')
@endpush
