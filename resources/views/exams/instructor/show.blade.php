@extends('layouts.dashboard')

@section('title', 'Exam Details - ' . $exam->title)

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
                            <h1 class="text-3xl font-bold text-gray-900">{{ $exam->title }}</h1>
                            <p class="mt-2 text-gray-600">{{ $exam->description ?: 'Exam questions and details' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <!-- <a href="{{ route('instructor.exams.attempts', $exam) }}" 
                           class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                            <i class="fas fa-list-alt mr-2"></i>
                            View Attempts & Grade ({{ $exam->attempts->count() }})
                        </a> -->
                        <a href="{{ route('instructor.exams.edit', $exam) }}" 
                           class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                            <i class="fas fa-edit mr-2"></i>
                            Edit Exam
                        </a>
                    </div>
                </div>
            </div>

            <!-- Basic Exam Info -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-center">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="text-2xl font-bold text-blue-600">{{ $exam->questions->count() }}</div>
                        <div class="text-sm text-gray-600">Questions</div>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4">
                        <div class="text-2xl font-bold text-green-600">{{ $exam->total_points ?: $exam->questions->sum('points') }}</div>
                        <div class="text-sm text-gray-600">Total Marks</div>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-4">
                        <div class="text-2xl font-bold text-yellow-600">{{ $exam->duration_minutes }}</div>
                        <div class="text-sm text-gray-600">Minutes</div>
                    </div>
                    <div class="bg-purple-50 rounded-lg p-4">
                        <div class="text-2xl font-bold text-purple-600">{{ ucfirst($exam->status) }}</div>
                        <div class="text-sm text-gray-600">Status</div>
                    </div>
                    <div class="bg-orange-50 rounded-lg p-4">
                        <div class="text-2xl font-bold text-orange-600">{{ $exam->attempts->count() }}</div>
                        <div class="text-sm text-gray-600">
                            Student Attempts
                            @if($exam->attempts->count() > 0)
                                <br><span class="text-xs text-green-600">{{ $exam->attempts->whereIn('status', ['submitted', 'auto_submitted', 'graded'])->count() }} completed</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Questions -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Exam Questions</h2>
                    <p class="text-sm text-gray-600 mt-1">Review all questions for this exam</p>
                </div>
                <div class="p-6">
                    @if($exam->questions->count() > 0)
                        <div class="space-y-6">
                            @foreach($exam->questions as $question)
                            <div class="border border-gray-200 rounded-lg p-6 hover:border-gray-300 transition-colors">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center space-x-3">
                                        <span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full">
                                            Question {{ $loop->iteration }}
                                        </span>
                                        <span class="bg-gray-100 text-gray-800 text-sm font-medium px-3 py-1 rounded-full">
                                            {{ ucfirst(str_replace('_', ' ', $question->type)) }}
                                        </span>
                                        <span class="bg-green-100 text-green-800 text-sm font-medium px-3 py-1 rounded-full">
                                            {{ $question->points }} {{ $question->points == 1 ? 'mark' : 'marks' }}
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <h4 class="text-lg font-medium text-gray-900 mb-2">Question:</h4>
                                    <p class="text-gray-700 leading-relaxed">{{ $question->question }}</p>
                                </div>

                                @if($question->type === 'mcq' && $question->options)
                                    <div class="mt-4">
                                        <h5 class="text-sm font-medium text-gray-700 mb-3">Answer Options:</h5>
                                        <div class="space-y-2">
                                            @foreach($question->options as $key => $option)
                                                <div class="flex items-center space-x-3 p-2 rounded-lg {{ $key === $question->correct_answer ? 'bg-green-50 border border-green-200' : 'bg-gray-50' }}">
                                                    <span class="text-sm font-medium text-gray-700 min-w-[20px]">{{ $key }}.</span>
                                                    <span class="text-sm text-gray-700 flex-1">{{ $option }}</span>
                                                    @if($key === $question->correct_answer)
                                                        <span class="flex items-center text-green-600 text-sm font-medium">
                                                            <i class="fas fa-check-circle mr-1"></i>
                                                            Correct Answer
                                                        </span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @elseif($question->type === 'short_answer')
                                    <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                                        <p class="text-sm text-gray-600">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            This is a short answer question. Students will type their response.
                                        </p>
                                    </div>
                                @elseif($question->type === 'file_upload')
                                    <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                                        <p class="text-sm text-gray-600">
                                            <i class="fas fa-upload mr-1"></i>
                                            This is a file upload question. Students will submit a file as their answer.
                                        </p>
                                    </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12 text-gray-500">
                            <i class="fas fa-question-circle text-6xl mb-4 text-gray-300"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Questions Added</h3>
                            <p class="text-gray-600 mb-4">This exam doesn't have any questions yet.</p>
                            <a href="{{ route('instructor.exams.edit', $exam) }}" 
                               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                                <i class="fas fa-plus mr-2"></i>
                                Add Questions
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
