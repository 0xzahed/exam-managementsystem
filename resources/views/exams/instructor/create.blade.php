@extends('layouts.dashboard')

@section('title', 'Create New Exam')

@section('content')
<div class="px-0 pt-2 md:pt-0">
    <div class="py-2 md:py-4">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <a href="{{ route('instructor.dashboard') }}"
                            class="mr-4 p-2 text-gray-600 hover:text-gray-800 rounded-lg hover:bg-gray-100"
                            title="Back to Dashboard">
                            <i class="fas fa-arrow-left text-xl"></i>
                        </a>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Create New Exam</h1>
                            <p class="mt-2 text-gray-600">Design and configure your exam with questions and settings</p>
                        </div>
                    </div>
                    <a href="{{ route('instructor.exams.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Exams
                    </a>
                </div>
            </div>

            <!-- Main Form -->
        <form action="{{ route('instructor.exams.store') }}" method="POST" enctype="multipart/form-data" id="examForm">
            @csrf
            
            <!-- Basic Information Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Basic Information</h2>
                    <p class="text-sm text-gray-600 mt-1">Set up the fundamental details of your exam</p>
                </div>
                <div class="p-6 space-y-6">
                    <!-- Title and Course -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Exam Title</label>
                            <input type="text" id="title" name="title" value="{{ old('title') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                   placeholder="e.g., Midterm Examination" required>
                        </div>
                        <div>
                            <label for="course_id" class="block text-sm font-medium text-gray-700 mb-2">Course</label>
                            <select id="course_id" name="course_id" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" 
                                    required>
                                <option value="">Select a course</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                        {{ $course->title }} ({{ $course->course_code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea id="description" name="description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                  placeholder="Provide exam instructions and details...">{{ old('description') }}</textarea>
                    </div>

                    <!-- Duration and Timing -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="duration_minutes" class="block text-sm font-medium text-gray-700 mb-2">Duration (minutes)</label>
                            <input type="number" id="duration_minutes" name="duration_minutes" value="{{ old('duration_minutes', 60) }}" 
                                   min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                   required>
                        </div>
                        <div>
                            <label for="start_time" class="block text-sm font-medium text-gray-700 mb-2">Start Time</label>
                            <input type="datetime-local" id="start_time" name="start_time" value="{{ old('start_time') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                   required>
                        </div>
                        <div>
                            <label for="end_time" class="block text-sm font-medium text-gray-700 mb-2">End Time</label>
                            <input type="datetime-local" id="end_time" name="end_time" value="{{ old('end_time') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                   required>
                        </div>
                    </div>

                    <!-- Advanced Settings -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <input type="checkbox" id="auto_grade_mcq" name="auto_grade_mcq" value="1" 
                                       {{ old('auto_grade_mcq', true) ? 'checked' : '' }}
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                <label for="auto_grade_mcq" class="ml-2 text-sm text-gray-700">Auto-grade MCQ questions</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" id="show_results_immediately" name="show_results_immediately" value="1" 
                                       {{ old('show_results_immediately') ? 'checked' : '' }}
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                <label for="show_results_immediately" class="ml-2 text-sm text-gray-700">Show results immediately after submission</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" id="prevent_navigation" name="prevent_navigation" value="1" 
                                       {{ old('prevent_navigation') ? 'checked' : '' }}
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                <label for="prevent_navigation" class="ml-2 text-sm text-gray-700">Show navigation warning during exam</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" id="shuffle_questions" name="shuffle_questions" value="1" 
                                       {{ old('shuffle_questions') ? 'checked' : '' }}
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                <label for="shuffle_questions" class="ml-2 text-sm text-gray-700">Shuffle question order for each student</label>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label for="max_attempts" class="block text-sm font-medium text-gray-700 mb-2">Maximum Attempts</label>
                                <input type="number" id="max_attempts" name="max_attempts" value="{{ old('max_attempts', 1) }}" 
                                       min="1" max="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                <p class="text-xs text-gray-500 mt-1">Number of times a student can take this exam</p>
                            </div>
                            <div>
                                <label for="passing_score" class="block text-sm font-medium text-gray-700 mb-2">Passing Score (%)</label>
                                <input type="number" id="passing_score" name="passing_score" value="{{ old('passing_score', 60) }}" 
                                       min="0" max="100" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                <p class="text-xs text-gray-500 mt-1">Minimum score required to pass</p>
                            </div>
                        </div>
                    </div>

                    <!-- File Attachments -->
                    <div>
                        <label for="attachments" class="block text-sm font-medium text-gray-700 mb-2">Attachments (Optional)</label>
                        <input type="file" id="attachments" name="attachments[]" multiple 
                               accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <p class="text-xs text-gray-500 mt-1">Supported formats: PDF, DOC, DOCX, TXT, JPG, PNG (Max 10MB per file)</p>
                    </div>
                </div>
            </div>

            <!-- Questions Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Questions</h2>
                            <p class="text-sm text-gray-600 mt-1">Add and configure exam questions</p>
                        </div>
                        <button type="button" id="addQuestionBtn" 
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add Question
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <div id="questionsContainer">
                        <!-- Questions will be added dynamically -->
                    </div>
                    <div id="noQuestions" class="text-center py-8 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p>No questions added yet. Click "Add Question" to get started.</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end">
                <div class="flex items-center space-x-4">
                    <button type="submit" id="submitBtn"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Create & Publish Exam
                    </button>
                </div>
            </div>
        </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
@vite('resources/js/pages/exams/create.js')
@endsection
