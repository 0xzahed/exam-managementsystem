@extends('layouts.app')

@section('title', 'Exam Result - ' . $exam->title)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">{{ $exam->title }}</h1>
                <p class="text-gray-500 text-sm">{{ $exam->course->title }}</p>
            </div>
            <div class="text-right">
                <div class="text-sm text-gray-500">Status</div>
                <div class="font-medium text-gray-900 capitalize">{{ str_replace('_', ' ', $attempt->status) }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                <div class="text-sm text-gray-500">Score</div>
                <div class="text-2xl font-semibold text-gray-900">{{ $attempt->total_score ?? 0 }} / {{ $attempt->max_score }}</div>
                @if(!is_null($attempt->score))
                    <div class="text-sm text-gray-600">{{ $attempt->score }}%</div>
                @endif
            </div>
            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                <div class="text-sm text-gray-500">Time Spent</div>
                <div class="text-2xl font-semibold text-gray-900">{{ $attempt->time_spent_minutes }} min</div>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                <div class="text-sm text-gray-500">Submitted At</div>
                <div class="text-2xl font-semibold text-gray-900">{{ optional($attempt->submitted_at)->format('Y-m-d H:i') }}</div>
            </div>
        </div>

        <h2 class="text-lg font-semibold text-gray-900 mb-4">Your Answers</h2>
        <div class="space-y-4">
            @foreach($attempt->examAnswers as $index => $answer)
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-start justify-between">
                        <div class="text-gray-900 font-medium">Question {{ $index + 1 }}</div>
                        <div class="text-sm text-gray-600">Points: {{ $answer->points_awarded ?? 0 }} / {{ $answer->examQuestion->points }}</div>
                    </div>
                    <div class="mt-2 text-gray-700">{!! nl2br(e($answer->examQuestion->question)) !!}</div>
                    <div class="mt-3 p-3 bg-gray-50 rounded"> 
                        @if($answer->examQuestion->type === 'file_upload')
                            @if(is_array($answer->answer_files) && count($answer->answer_files) > 0)
                                <ul class="list-disc list-inside text-sm">
                                    @foreach($answer->answer_files as $file)
                                        <li><a class="text-blue-600 hover:underline" href="{{ $file['webViewLink'] ?? '#' }}" target="_blank">{{ $file['name'] ?? 'File' }}</a></li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-gray-600 text-sm">No file uploaded</span>
                            @endif
                        @else
                            <span class="text-gray-800">{{ $answer->answer_text ?? '—' }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection


