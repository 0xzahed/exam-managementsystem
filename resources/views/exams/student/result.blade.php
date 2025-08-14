@extends('layouts.dashboard')

@section('title', 'Exam Result - ' . $exam->title)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <!-- Header with Back Navigation -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <div class="flex items-center space-x-3 mb-2">
                    <a href="{{ route('student.exams.index') }}" class="text-gray-500 hover:text-gray-700 transition-colors duration-200">
                        <i class="fas fa-arrow-left text-lg"></i>
                    </a>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $exam->title }}</h1>
                </div>
                <p class="text-gray-600 flex items-center ml-8">
                    <i class="fas fa-book mr-2"></i>{{ $exam->course->title }}
                </p>
            </div>
            <div class="text-right">
                <div class="text-sm text-gray-500 uppercase tracking-wide font-medium">Status</div>
                <div class="flex items-center mt-1">
                    @if($attempt->status === 'submitted' || $attempt->status === 'auto_submitted')
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        <span class="font-semibold text-green-600 capitalize">{{ str_replace('_', ' ', $attempt->status) }}</span>
                    @elseif($attempt->status === 'graded')
                        <i class="fas fa-award text-blue-500 mr-2"></i>
                        <span class="font-semibold text-blue-600">Graded</span>
                    @else
                        <i class="fas fa-clock text-yellow-500 mr-2"></i>
                        <span class="font-semibold text-yellow-600 capitalize">{{ str_replace('_', ' ', $attempt->status) }}</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Enhanced Score Overview Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-blue-600 uppercase tracking-wide">Score</div>
                        <div class="text-3xl font-bold text-blue-900 mt-1">{{ $attempt->total_score ?? 0 }}</div>
                        <div class="text-sm text-blue-700">out of {{ $attempt->max_score }} marks</div>
                        @if(!is_null($attempt->score))
                            <div class="text-lg font-semibold text-blue-800 mt-1">{{ $attempt->score }}%</div>
                        @endif
                    </div>
                    <div class="bg-blue-200 rounded-full p-3">
                        <i class="fas fa-trophy text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-green-600 uppercase tracking-wide">Time Spent</div>
                        <div class="text-3xl font-bold text-green-900 mt-1">{{ $attempt->time_spent_minutes }}</div>
                        <div class="text-sm text-green-700">minutes</div>
                    </div>
                    <div class="bg-green-200 rounded-full p-3">
                        <i class="fas fa-clock text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 border border-purple-200">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-purple-600 uppercase tracking-wide">Submitted</div>
                        <div class="text-lg font-bold text-purple-900 mt-1">
                            {{ optional($attempt->submitted_at)->format('M d, Y') }}
                        </div>
                        <div class="text-sm text-purple-700">
                            {{ optional($attempt->submitted_at)->format('h:i A') }}
                        </div>
                    </div>
                    <div class="bg-purple-200 rounded-full p-3">
                        <i class="fas fa-calendar-check text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Answers Section -->
        <div class="border-t border-gray-200 pt-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-list-alt mr-3 text-blue-500"></i>
                Your Answers
            </h2>
            
            <div class="space-y-6">
                @foreach($attempt->examAnswers as $index => $answer)
                    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
                        <!-- Question Header -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="bg-blue-100 text-blue-600 rounded-full w-8 h-8 flex items-center justify-center font-semibold text-sm">
                                    {{ $index + 1 }}
                                </div>
                                <div class="text-lg font-semibold text-gray-900">Question {{ $index + 1 }}</div>
                            </div>
                            <div class="text-right">
                                <div class="flex items-center space-x-2">
                                    @if(($answer->points_awarded ?? 0) > 0)
                                        <i class="fas fa-check-circle text-green-500"></i>
                                        <span class="text-green-600 font-semibold">{{ $answer->points_awarded ?? 0 }} marks</span>
                                    @else
                                        <i class="fas fa-times-circle text-red-500"></i>
                                        <span class="text-red-600 font-semibold">0 pts</span>
                                    @endif
                                    <span class="text-gray-500">/ {{ $answer->examQuestion->points }} marks</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Question Text -->
                        <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                            <div class="text-gray-900 leading-relaxed">{!! nl2br(e($answer->examQuestion->question)) !!}</div>
                        </div>
                        
                        <!-- Answer Section -->
                        <div class="border-t border-gray-200 pt-4">
                            <div class="text-sm font-medium text-gray-600 mb-2">Your Answer:</div>
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
                                        {{ $answer->answer_text ?? '—' }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="border-t border-gray-200 pt-8 mt-8">
            <div class="flex justify-between items-center">
                <a href="{{ route('student.exams.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-medium transition-colors flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Exams
                </a>
                
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print, nav, .sidebar, button, .bg-gradient-to-br {
        display: none !important;
    }
    
    .main-content {
        margin-left: 0 !important;
        margin-top: 0 !important;
    }
    
    body {
        background: white !important;
    }
    
    .bg-gradient-to-br {
        background: #f8fafc !important;
    }
}
</style>
@endsection


