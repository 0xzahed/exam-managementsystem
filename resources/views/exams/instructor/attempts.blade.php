@extends('layouts.dashboard')

@section('title', 'Exam Attempts - ' . $exam->title)

@section('content')
<div class="px-0 pt-2 md:pt-0">
    <div class="py-2 md:py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <a href="{{ route('instructor.exams.index') }}"
                            class="mr-4 p-2 text-gray-600 hover:text-gray-800 rounded-lg hover:bg-gray-100"
                            title="Back to Exams">
                            <i class="fas fa-arrow-left text-xl"></i>
                        </a>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Exam Attempts</h1>
                            <p class="mt-2 text-gray-600">{{ $exam->title }} - {{ $exam->course->title }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('instructor.exams.show', $exam) }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                            <i class="fas fa-eye mr-2"></i>View Exam
                        </a>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <i class="fas fa-users text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Attempts</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $attempts->count() }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Submitted</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $attempts->whereIn('status', ['submitted', 'auto_submitted', 'graded'])->count() }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <i class="fas fa-award text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Graded</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $attempts->where('status', 'graded')->count() }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-yellow-100 rounded-lg">
                            <i class="fas fa-clock text-yellow-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">In Progress</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $attempts->where('status', 'in_progress')->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attempts Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Student Attempts</h2>
                    <p class="text-sm text-gray-600 mt-1">View all attempts for this exam</p>
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
                                    Started At
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Completed At
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Score
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($attempts as $attempt)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                    <i class="fas fa-user text-gray-600"></i>
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
                                        @switch($attempt->status)
                                            @case('submitted')
                                            @case('auto_submitted')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-check-circle mr-1"></i>Submitted
                                                </span>
                                                @break
                                            @case('graded')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    <i class="fas fa-award mr-1"></i>Graded
                                                </span>
                                                @break
                                            @case('in_progress')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    <i class="fas fa-clock mr-1"></i>In Progress
                                                </span>
                                                @break
                                            @case('abandoned')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    <i class="fas fa-times-circle mr-1"></i>Abandoned
                                                </span>
                                                @break
                                            @default
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    {{ ucfirst(str_replace('_', ' ', $attempt->status)) }}
                                                </span>
                                        @endswitch
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $attempt->started_at ? $attempt->started_at->format('M d, Y H:i') : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $attempt->completed_at ? $attempt->completed_at->format('M d, Y H:i') : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if(in_array($attempt->status, ['submitted', 'auto_submitted', 'graded']) && $attempt->total_score !== null)
                                            <div class="text-sm text-gray-900">
                                                <span class="font-medium">{{ $attempt->total_score ?? 0 }}/{{ $attempt->max_score ?? $exam->examQuestions->sum('points') }}</span>
                                                @if($attempt->score)
                                                    <span class="text-gray-500 ml-1">
                                                        ({{ number_format($attempt->score, 1) }}%)
                                                    </span>
                                                @endif
                                            </div>
                                        @elseif(in_array($attempt->status, ['submitted', 'auto_submitted']))
                                            <span class="inline-flex items-center px-2 py-1 bg-orange-100 text-orange-800 text-xs font-medium rounded">
                                                <i class="fas fa-clock mr-1"></i>Pending
                                            </span>
                                        @else
                                            <span class="text-sm text-gray-500">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-2">
                                            @if(in_array($attempt->status, ['submitted', 'auto_submitted', 'graded']))
                                                <a href="{{ route('instructor.exams.view-attempt', [$exam, $attempt]) }}" 
                                                   class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-md hover:bg-blue-700 transition-colors">
                                                    <i class="fas fa-eye mr-1"></i>
                                                    @if($attempt->status === 'graded')
                                                        Review
                                                    @else
                                                        Grade
                                                    @endif
                                                </a>
                                            @elseif($attempt->status === 'in_progress')
                                                <span class="inline-flex items-center px-3 py-1.5 bg-yellow-100 text-yellow-800 text-xs font-medium rounded-md">
                                                    <i class="fas fa-clock mr-1"></i>In Progress
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-600 text-xs font-medium rounded-md">
                                                    <i class="fas fa-ban mr-1"></i>No Action
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                                            <p class="text-lg font-medium text-gray-900 mb-2">No attempts yet</p>
                                            <p class="text-sm text-gray-600">Students haven't started this exam yet.</p>
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
@endsection
