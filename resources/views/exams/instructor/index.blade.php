@extends('layouts.dashboard')

@section('title', 'Exams')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-7xl">
    <!-- Page Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">
                    @if(auth()->user()->role === 'instructor')
                    My Exams
                    @else
                    Available Exams
                    @endif
                </h1>
                <p class="text-gray-600">
                    @if(auth()->user()->role === 'instructor')
                    Manage and monitor your exam assignments
                    @else
                    View and take your scheduled exams
                    @endif
                </p>
            </div>

            @if(auth()->user()->role === 'instructor')
            <a href="{{ route('instructor.exams.create') }}"
                class="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-200 transform hover:-translate-y-0.5 hover:-translate-y-0.5 hover:shadow-lg flex items-center gap-2">
                <i class="fas fa-plus"></i>
                Create Exam
            </a>
            @endif
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @if(auth()->user()->role === 'instructor')
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-all duration-200 hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium">Total Exams</p>
                    <h3 class="text-2xl font-bold">{{ $exams->count() }}</h3>
                </div>
                <div class="bg-blue-400/30 p-3 rounded-lg">
                    <i class="fas fa-clipboard-list text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-all duration-200 hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium">Total Questions</p>
                    <h3 class="text-2xl font-bold">{{ $exams->sum(function($exam) { return $exam->questions->count(); }) }}</h3>
                </div>
                <div class="bg-green-400/30 p-3 rounded-lg">
                    <i class="fas fa-question-circle text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 text-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-all duration-200 hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-yellow-100 text-sm font-medium">Published Exams</p>
                    <h3 class="text-2xl font-bold">{{ $exams->where('status', 'published')->count() }}</h3>
                </div>
                <div class="bg-yellow-400/30 p-3 rounded-lg">
                    <i class="fas fa-globe text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-all duration-200 hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium">Total Attempts</p>
                    <h3 class="text-2xl font-bold">{{ $exams->sum(function($exam) { return $exam->attempts->count(); }) }}</h3>
                </div>
                <div class="bg-purple-400/30 p-3 rounded-lg">
                    <i class="fas fa-users text-xl"></i>
                </div>
            </div>
        </div>
        @else
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-all duration-200 hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium">Available Exams</p>
                    <h3 class="text-2xl font-bold">{{ $exams->count() }}</h3>
                </div>
                <div class="bg-blue-400/30 p-3 rounded-lg">
                    <i class="fas fa-clipboard-list text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-all duration-200 hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium">Completed</p>
                    <h3 class="text-2xl font-bold">{{ $exams->filter(function($exam) { return $exam->getAttemptForStudent(auth()->user()->id) && $exam->getAttemptForStudent(auth()->user()->id)->is_submitted; })->count() }}</h3>
                </div>
                <div class="bg-green-400/30 p-3 rounded-lg">
                    <i class="fas fa-check-circle text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 text-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-all duration-200 hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-yellow-100 text-sm font-medium">Pending</p>
                    <h3 class="text-2xl font-bold">{{ $exams->filter(function($exam) { return !$exam->getAttemptForStudent(auth()->user()->id) || !$exam->getAttemptForStudent(auth()->user()->id)->is_submitted; })->count() }}</h3>
                </div>
                <div class="bg-yellow-400/30 p-3 rounded-lg">
                    <i class="fas fa-clock text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-all duration-200 hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium">Average Score</p>
                    <h3 class="text-2xl font-bold">
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
        @endif
    </div>

    <!-- Filter Tabs -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
        <div class="p-4 sm:p-6 border-b border-gray-200">
            <div class="flex flex-wrap gap-2">
                @if(auth()->user()->role === 'instructor')
                <button onclick="filterExams('all', event)" class="filter-btn active px-4 py-2 rounded-lg font-medium transition-all duration-200 bg-blue-600 text-white">
                    <i class="fas fa-list mr-2"></i>All Exams
                </button>
                @else
                <button onclick="filterExams('available', event)" class="filter-btn px-4 py-2 rounded-lg font-medium transition-all duration-200 bg-gray-100 text-gray-700 hover:bg-gray-200">
                    <i class="fas fa-play mr-2"></i>Available
                </button>
                <button onclick="filterExams('completed', event)" class="filter-btn px-4 py-2 rounded-lg font-medium transition-all duration-200 bg-gray-100 text-gray-700 hover:bg-gray-200">
                    <i class="fas fa-check mr-2"></i>Completed
                </button>
                <button onclick="filterExams('pending', event)" class="filter-btn px-4 py-2 rounded-lg font-medium transition-all duration-200 bg-gray-100 text-gray-700 hover:bg-gray-200">
                    <i class="fas fa-clock mr-2"></i>Pending
                </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Exams List -->
    <div class="grid grid-cols-1 gap-6" id="exams-container">
        @forelse($exams as $exam)
        @php
        $attempt = null;
        $isCompleted = false;
        $isPending = false;
        $canTake = false;

        if(auth()->user()->role === 'student') {
        $attempt = $exam->getAttemptForStudent(auth()->user()->id);
        $isCompleted = $attempt && $attempt->is_submitted;
        $isPending = !$isCompleted;
        $canTake = !$isCompleted && $exam->status === 'published';
        }
        @endphp

        <div class="exam-card bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-lg transition-all duration-200 hover:-translate-y-1"
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

                                <div class="flex flex-wrap items-center gap-3 mb-3">
                                    <span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full">
                                        {{ $exam->course->title ?? 'General' }}
                                    </span>

                                    <span class="px-3 py-1 text-sm font-medium rounded-full 
                                        {{ $exam->status === 'published' ? 'bg-green-100 text-green-800' : 
                                           ($exam->status === 'draft' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                        {{ ucfirst($exam->status) }}
                                    </span>

                                    @if(auth()->user()->role === 'student')
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
                                <p class="text-gray-900 font-semibold">{{ $exam->duration_minutes ?? 60 }} min</p>
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
                                    <i class="fas fa-star text-yellow-500"></i>
                                    <span class="font-medium text-gray-700">Marks</span>
                                </div>
                                <p class="text-gray-900 font-semibold">{{ $exam->total_points ?? 100 }}</p>
                            </div>

                            <div class="bg-gray-50 rounded-lg p-3">
                                <div class="flex items-center gap-2 mb-1">
                                    <i class="fas fa-users text-purple-500"></i>
                                    <span class="font-medium text-gray-700">Attempts</span>
                                </div>
                                <p class="text-gray-900 font-semibold">{{ $exam->attempts->count() }}</p>
                            </div>
                        </div>

                        @if(auth()->user()->role === 'student' && $attempt && $isCompleted)
                        <div class="mt-4 bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex items-center gap-2 mb-2">
                                <i class="fas fa-trophy text-green-500"></i>
                                <span class="font-medium text-green-800">Your Score</span>
                            </div>
                            <p class="text-green-700">
                                <span class="text-2xl font-bold">{{ $attempt->score ?? 0 }}%</span>
                                <span class="text-sm ml-2">({{ $attempt->marks_obtained ?? 0 }}/{{ $exam->total_points ?? 100 }} marks)</span>
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
                    <div class="flex flex-col gap-3 min-w-[200px]">
                        @if(auth()->user()->role === 'instructor')
                        <a href="{{ route('instructor.exams.show', $exam) }}"
                            class="bg-blue-600 hover:bg-blue-700 text-white text-center py-3 px-4 rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                            <i class="fas fa-eye"></i>
                            View Details
                        </a>

                        <a href="{{ route('instructor.exams.edit', $exam) }}"
                            class="bg-gray-600 hover:bg-gray-700 text-white text-center py-3 px-4 rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                            <i class="fas fa-edit"></i>
                            Edit Exam
                        </a>

                        <a href="{{ route('instructor.exams.attempts', $exam) }}"
                            class="bg-green-600 hover:bg-green-700 text-white text-center py-3 px-4 rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                            <i class="fas fa-list"></i>
                            View Attempts ({{ $exam->attempts->count() }})
                        </a>
                        @else
                        @if($canTake)
                        <a href="{{ route('student.exams.take', $exam) }}"
                            class="bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white text-center py-3 px-4 rounded-lg font-medium transition-all duration-200 transform hover:-translate-y-0.5 hover:shadow-lg flex items-center justify-center gap-2">
                            <i class="fas fa-play"></i>
                            Start Exam
                        </a>
                        @elseif($isCompleted)
                        <a href="{{ route('student.exams.result', $exam) }}"
                            class="bg-blue-600 hover:bg-blue-700 text-white text-center py-3 px-4 rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                            <i class="fas fa-chart-bar"></i>
                            View Results
                        </a>
                        @else
                        <button disabled
                            class="bg-gray-300 text-gray-500 text-center py-3 px-4 rounded-lg font-medium cursor-not-allowed flex items-center justify-center gap-2">
                            <i class="fas fa-lock"></i>
                            Not Available
                        </button>
                        @endif

                        <!-- Exam Schedule Info for Students -->
                        <div class="bg-gray-50 rounded-lg p-3 text-sm">
                            <div class="flex items-center gap-2 mb-2">
                                <i class="fas fa-calendar-alt text-blue-500"></i>
                                <span class="font-medium text-gray-700">Schedule</span>
                            </div>
                            <p class="text-gray-900 font-semibold">{{ \Carbon\Carbon::parse($exam->start_time)->format('M j, Y') }}</p>
                            <p class="text-gray-600">{{ \Carbon\Carbon::parse($exam->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($exam->end_time)->format('g:i A') }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <div class="max-w-md mx-auto">
                <i class="fas fa-clipboard-list text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No Exams Found</h3>
                <p class="text-gray-600 mb-6">
                    @if(auth()->user()->role === 'instructor')
                    You haven't created any exams yet. Start by creating your first exam.
                    @else
                    No exams are currently available for you.
                    @endif
                </p>
                @if(auth()->user()->role === 'instructor')
                <a href="{{ route('instructor.exams.create') }}"
                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                    <i class="fas fa-plus"></i>
                    Create First Exam
                </a>
                @endif
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection

@section('scripts')

@vite('resources/js/pages/exams-index.js')

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

    .exam-card {
        border-left: 4px solid transparent;
        transition: all 0.3s ease;
    }

    .exam-card:hover {
        border-left-color: #667eea;
    }

    .exam-card[data-status="published"] {
        border-left-color: #10b981;
    }

    .exam-card[data-status="draft"] {
        border-left-color: #f59e0b;
    }

    .exam-card[data-completion="completed"] {
        border-left-color: #10b981;
    }
</style>
@endsection