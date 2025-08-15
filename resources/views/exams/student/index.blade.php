@extends('layouts.dashboard')

@section('title', 'Available Exams')

@section('content')
<div class="px-0 pt-2 md:pt-0">
    <div class="py-2 md:py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Available Exams</h1>
                        <p class="text-gray-600">View and take your scheduled exams</p>
                    </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">Available Exams</p>
                            <h3 class="text-2xl font-bold stat-available">{{ $exams->count() }}</h3>
                        </div>
                        <div class="bg-blue-400/30 p-3 rounded-lg">
                            <i class="fas fa-clipboard-list text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-xl p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium">Completed</p>
                            <h3 class="text-2xl font-bold stat-completed">{{ $exams->filter(function($exam) { return $exam->getAttemptForStudent(auth()->user()->id) && $exam->getAttemptForStudent(auth()->user()->id)->is_submitted; })->count() }}</h3>
                        </div>
                        <div class="bg-green-400/30 p-3 rounded-lg">
                            <i class="fas fa-check-circle text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 text-white rounded-xl p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-yellow-100 text-sm font-medium">Pending</p>
                            <h3 class="text-2xl font-bold stat-pending">{{ $exams->filter(function($exam) { return !$exam->getAttemptForStudent(auth()->user()->id) || !$exam->getAttemptForStudent(auth()->user()->id)->is_submitted; })->count() }}</h3>
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
                            <h3 class="text-2xl font-bold stat-average">
                                @php
                                $completedAttempts = collect();
                                foreach($exams as $exam) {
                                    $attempt = $exam->getAttemptForStudent(auth()->user()->id);
                                    if($attempt && $attempt->is_submitted && $attempt->score !== null) {
                                        $completedAttempts->push($attempt);
                                    }
                                }
                                $avgScore = $completedAttempts->avg('score');
                                @endphp
                                {{ $avgScore ? number_format($avgScore, 1) . '%' : 'N/A' }}
                            </h3>
                        </div>
                        <div class="bg-purple-400/30 p-3 rounded-lg">
                            <i class="fas fa-chart-line text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Tabs -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
                <div class="p-4 sm:p-6 border-b border-gray-200">
                    <div class="flex flex-wrap gap-2">
                        <button onclick="filterExams('all', event)" class="filter-btn active px-4 py-2 rounded-lg font-medium bg-blue-600 text-white">
                            <i class="fas fa-list mr-2"></i>All Exams
                        </button>
                        <button onclick="filterExams('available', event)" class="filter-btn px-4 py-2 rounded-lg font-medium bg-gray-100 text-gray-700 hover:bg-gray-200">
                            <i class="fas fa-play mr-2"></i>Available
                        </button>
                        <button onclick="filterExams('completed', event)" class="filter-btn px-4 py-2 rounded-lg font-medium bg-gray-100 text-gray-700 hover:bg-gray-200">
                            <i class="fas fa-check mr-2"></i>Completed
                        </button>
                        <button onclick="filterExams('pending', event)" class="filter-btn px-4 py-2 rounded-lg font-medium bg-gray-100 text-gray-700 hover:bg-gray-200">
                            <i class="fas fa-clock mr-2"></i>Pending
                        </button>
                    </div>
                </div>
            </div>

            <!-- Exams List -->
            <div class="grid grid-cols-1 gap-6" id="exams-container">
                @forelse($exams as $exam)
                @php
                $attempt = $exam->getAttemptForStudent(auth()->user()->id);
                $isCompleted = $attempt && $attempt->is_submitted;
                $isPending = !$isCompleted;
                $isActive = $exam->isActive(); // Check if exam is within time window
                $canTake = $exam->canStudentTake(auth()->user()->id) && !$isCompleted;
                @endphp

                <div class="exam-card bg-white rounded-xl shadow-sm border border-gray-200"
                     data-exam-id="{{ $exam->id }}"
                     data-status="{{ $exam->status }}"
                     data-completion="{{ $isCompleted ? 'completed' : 'pending' }}"
                     data-availability="{{ $canTake ? 'available' : 'unavailable' }}">

                    <div class="p-6">
                        <div class="flex flex-col lg:flex-row gap-6">
                            <!-- Exam Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-4 mb-4">
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-xl font-semibold text-gray-900 mb-2 truncate">{{ $exam->title }}</h3>
                                        <p class="text-gray-600 mb-3">{{ $exam->description }}</p>

                                        <div class="flex flex-wrap items-center gap-3 mb-3 status-badges">
                                            <span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full">
                                                {{ $exam->course->title ?? 'General' }}
                                            </span>

                                            <span class="px-3 py-1 text-sm font-medium rounded-full 
                                                {{ $exam->status === 'published' ? 'bg-green-100 text-green-800' : 
                                                   ($exam->status === 'draft' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                                {{ ucfirst($exam->status) }}
                                            </span>

                                            @if($isCompleted)
                                            <span class="bg-green-100 text-green-800 text-sm font-medium px-3 py-1 rounded-full">
                                                <i class="fas fa-check mr-1"></i>Completed
                                            </span>
                                            @elseif($canTake)
                                            <span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full">
                                                <i class="fas fa-play mr-1"></i>Available
                                            </span>
                                            @else
                                            <span class="bg-gray-100 text-gray-800 text-sm font-medium px-3 py-1 rounded-full">
                                                <i class="fas fa-lock mr-1"></i>Not Available
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <div class="flex items-center gap-2 mb-1">
                                            <i class="fas fa-clock text-blue-500"></i>
                                            <span class="font-medium text-gray-700">Duration</span>
                                        </div>
                                        <p class="text-gray-900 font-semibold">{{ $exam->duration ?? 60 }} min</p>
                                    </div>

                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <div class="flex items-center gap-2 mb-1">
                                            <i class="fas fa-question-circle text-green-500"></i>
                                            <span class="font-medium text-gray-700">Questions</span>
                                        </div>
                                        <p class="text-gray-900 font-semibold">{{ $exam->questions->count() }}</p>
                                    </div>

                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <div class="flex items-center gap-2 mb-1">
                                            <i class="fas fa-calendar text-purple-500"></i>
                                            <span class="font-medium text-gray-700">Start Time</span>
                                        </div>
                                        <p class="text-gray-900 font-semibold">{{ $exam->start_time ? $exam->start_time->format('M j, g:i A') : 'N/A' }}</p>
                                    </div>

                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <div class="flex items-center gap-2 mb-1">
                                            <i class="fas fa-calendar-times text-red-500"></i>
                                            <span class="font-medium text-gray-700">End Time</span>
                                        </div>
                                        <p class="text-gray-900 font-semibold">{{ $exam->end_time ? $exam->end_time->format('M j, g:i A') : 'N/A' }}</p>
                                    </div>
                                </div>

                                <!-- Time Status -->
                                <div class="mt-4 time-status">
                                    @if($isActive)
                                        <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-check-circle text-green-500"></i>
                                                <span class="text-green-800 font-medium">Exam is currently active and available</span>
                                            </div>
                                        </div>
                                    @elseif($exam->start_time && now()->lt($exam->start_time))
                                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-clock text-yellow-500"></i>
                                                <span class="text-yellow-800 font-medium">Exam will start at {{ $exam->start_time->format('M j, Y \a\t g:i A') }}</span>
                                            </div>
                                            <div class="countdown-timer mt-2 text-lg font-bold text-yellow-900" data-target="{{ $exam->start_time->timestamp }}"></div>
                                        </div>
                                    @elseif($exam->end_time && now()->gt($exam->end_time))
                                        <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-times-circle text-red-500"></i>
                                                <span class="text-red-800 font-medium">Exam ended at {{ $exam->end_time->format('M j, Y \a\t g:i A') }}</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                @if($attempt && $isCompleted)
                                <div class="mt-4 bg-green-50 border border-green-200 rounded-lg p-4">
                                    <div class="flex items-center gap-2 mb-2">
                                        <i class="fas fa-trophy text-green-500"></i>
                                        <span class="font-medium text-green-800">Your Score</span>
                                    </div>
                                    <p class="text-green-700">
                                        <span class="text-2xl font-bold">{{ $attempt->score ?? 0 }}%</span>
                                        <span class="text-sm ml-2">({{ $attempt->marks_obtained ?? 0 }}/{{ $exam->total_marks ?? 100 }} marks)</span>
                                    </p>
                                    @if($attempt->completed_at)
                                    <p class="text-green-600 text-sm mt-1">
                                        Completed on {{ $attempt->completed_at->format('M j, Y \a\t g:i A') }}
                                    </p>
                                    @endif
                                </div>
                                @endif
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex flex-col gap-3 min-w-[200px] action-buttons">
                                @if($canTake)
                                <form action="{{ route('student.exams.start', $exam) }}" method="POST" class="inline" onsubmit="alert('Starting exam: {{ $exam->title }}'); return true;">
                                    @csrf
                                    <button type="submit"
                                        class="w-full bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white text-center py-3 px-4 rounded-lg font-medium flex items-center justify-center gap-2">
                                        <i class="fas fa-play"></i>
                                        Start Exam
                                    </button>
                                </form>
                                @elseif($isCompleted)
                                <a href="{{ route('student.exams.result', $exam) }}"
                                   class="bg-blue-600 hover:bg-blue-700 text-white text-center py-3 px-4 rounded-lg font-medium flex items-center justify-center gap-2">
                                    <i class="fas fa-chart-bar"></i>
                                    View Results
                                </a>
                                @elseif(!$isActive && $exam->start_time && now()->lt($exam->start_time))
                                <button disabled
                                    class="bg-yellow-300 text-yellow-700 text-center py-3 px-4 rounded-lg font-medium cursor-not-allowed flex items-center justify-center gap-2">
                                    <i class="fas fa-clock"></i>
                                    Not Started Yet
                                </button>
                                @elseif(!$isActive && $exam->end_time && now()->gt($exam->end_time))
                                <button disabled
                                    class="bg-red-300 text-red-700 text-center py-3 px-4 rounded-lg font-medium cursor-not-allowed flex items-center justify-center gap-2">
                                    <i class="fas fa-times-circle"></i>
                                    Exam Ended
                                </button>
                                @else
                                <button disabled
                                    class="bg-gray-300 text-gray-500 text-center py-3 px-4 rounded-lg font-medium cursor-not-allowed flex items-center justify-center gap-2">
                                    <i class="fas fa-lock"></i>
                                    Not Available
                                </button>
                                @endif

                                <a href="#" onclick="showExamDetails({{ $exam->id }})"
                                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-center py-3 px-4 rounded-lg font-medium flex items-center justify-center gap-2">
                                    <i class="fas fa-info-circle"></i>
                                    Exam Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                    <div class="max-w-md mx-auto">
                        <i class="fas fa-clipboard-list text-6xl text-gray-300 mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">No Exams Available</h3>
                        <p class="text-gray-600 mb-6">No exams are currently available for you to take.</p>
            
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@vite('resources/js/pages/exams-index.js')
<script>
function showExamDetails(examId) {
    // For now, just log a message; UI modal can be added later
    showInfo('Exam details will be shown here. Exam ID: ' + examId);
}
</script>
@endsection
