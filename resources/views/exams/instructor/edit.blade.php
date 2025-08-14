@extends('layouts.dashboard')

@section('title', 'Edit Exam')

@section('content')
<div class="px-0 pt-2 md:pt-0">
    <div class="py-2 md:py-4">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Edit Exam</h1>
                    <p class="mt-2 text-gray-600">Update your exam configuration and questions</p>
                </div>
                <a href="{{ route('instructor.exams.show', $exam) }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Exam
                </a>
            </div>
        </div>

        <!-- Main Form -->
        <form action="{{ route('instructor.exams.update', $exam) }}" method="POST" enctype="multipart/form-data" id="examForm">
            @csrf
            @method('PUT')
            
            <!-- Basic Information Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Basic Information</h2>
                    <p class="text-sm text-gray-600 mt-1">Update the fundamental details of your exam</p>
                </div>
                <div class="p-6 space-y-6">
                    <!-- Title and Course -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Exam Title</label>
                            <input type="text" id="title" name="title" value="{{ old('title', $exam->title) }}" 
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
                                    <option value="{{ $course->id }}" {{ old('course_id', $exam->course_id) == $course->id ? 'selected' : '' }}>
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
                                  placeholder="Provide exam instructions and details...">{{ old('description', $exam->description) }}</textarea>
                    </div>

                    <!-- Duration and Timing -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="duration_minutes" class="block text-sm font-medium text-gray-700 mb-2">Duration (minutes)</label>
                            <input type="number" id="duration_minutes" name="duration_minutes" value="{{ old('duration_minutes', $exam->duration_minutes) }}" 
                                   min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                   required>
                        </div>
                        <div>
                            <label for="start_time" class="block text-sm font-medium text-gray-700 mb-2">Start Time</label>
                            <input type="datetime-local" id="start_time" name="start_time" value="{{ old('start_time', $exam->start_time->format('Y-m-d\TH:i')) }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                   required>
                        </div>
                        <div>
                            <label for="end_time" class="block text-sm font-medium text-gray-700 mb-2">End Time</label>
                            <input type="datetime-local" id="end_time" name="end_time" value="{{ old('end_time', $exam->end_time->format('Y-m-d\TH:i')) }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                   required>
                        </div>
                    </div>

                    <!-- Settings -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <input type="checkbox" id="auto_grade_mcq" name="auto_grade_mcq" value="1" 
                                       {{ old('auto_grade_mcq', $exam->auto_grade_mcq) ? 'checked' : '' }}
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                <label for="auto_grade_mcq" class="ml-2 text-sm text-gray-700">Auto-grade MCQ questions</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" id="show_results_immediately" name="show_results_immediately" value="1" 
                                       {{ old('show_results_immediately', $exam->show_results_immediately) ? 'checked' : '' }}
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                <label for="show_results_immediately" class="ml-2 text-sm text-gray-700">Show results immediately after submission</label>
                            </div>
                        </div>
                    </div>

                    <!-- Current Attachments -->
                    @if($exam->attachments && count($exam->attachments) > 0)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Current Attachments</label>
                            <div class="space-y-2">
                                @foreach($exam->attachments as $attachment)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                            </svg>
                                            <span class="text-sm text-gray-900">{{ $attachment['file_name'] }}</span>
                                        </div>
                                        <a href="{{ $attachment['url'] }}" target="_blank" 
                                           class="text-blue-600 hover:text-blue-800 text-sm">View</a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- New File Attachments -->
                    <div>
                        <label for="attachments" class="block text-sm font-medium text-gray-700 mb-2">Add New Attachments (Optional)</label>
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
                            <p class="text-sm text-gray-600 mt-1">Update exam questions</p>
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
                        <!-- Existing questions will be populated -->
                    </div>
                    <div id="noQuestions" class="text-center py-8 text-gray-500" style="display: none;">
                        <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p>No questions added yet. Click "Add Question" to get started.</p>
                    </div>
                </div>
            </div>

            <!-- Cohort Assignment -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
                <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Cohort Assignment</h2>
                    <button type="button" id="addCohortBtn" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-plus mr-2"></i>Add Cohort
                    </button>
                </div>
                <div class="p-6 space-y-4" id="cohortsContainer">
                    @foreach($exam->cohorts as $i => $cohort)
                    <div class="border border-gray-200 p-4 rounded-lg space-y-4 relative" data-index="{{ $i }}">
                        <button type="button" class="absolute top-2 right-2 text-red-600 removeCohort"><i class="fas fa-times-circle"></i></button>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Cohort Name</label>
                                <input name="cohorts[{{ $i }}][cohort_name]" value="{{ $cohort->cohort_name }}" class="w-full mt-1 px-3 py-2 border rounded-lg" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Description</label>
                                <input name="cohorts[{{ $i }}][description]" value="{{ $cohort->description }}" class="w-full mt-1 px-3 py-2 border rounded-lg">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Start Time</label>
                                <input type="datetime-local" name="cohorts[{{ $i }}][start_time]" value="{{ $cohort->start_time->format('Y-m-d\TH:i') }}" class="w-full mt-1 px-3 py-2 border rounded-lg" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">End Time</label>
                                <input type="datetime-local" name="cohorts[{{ $i }}][end_time]" value="{{ $cohort->end_time->format('Y-m-d\TH:i') }}" class="w-full mt-1 px-3 py-2 border rounded-lg" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Students</label>
                                <select multiple name="cohorts[{{ $i }}][student_ids][]" class="w-full mt-1 px-3 py-2 border rounded-lg" required>
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}" {{ in_array($student->id, $cohort->student_ids) ? 'selected' : '' }}>
                                            {{ $student->first_name }} {{ $student->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">Status:</span>
                    <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="draft" {{ old('status', $exam->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status', $exam->status) == 'published' ? 'selected' : '' }}>Published</option>
                    </select>
                </div>
                <div class="flex items-center space-x-4">
                    <button type="button" onclick="saveDraft()" 
                            class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        Save as Draft
                    </button>
                    <button type="submit" id="submitBtn"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Update Exam
                    </button>
                </div>
            </div>
        </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Pass existing exam data to JavaScript
    window.examData = {
        questions: @json($exam->questions)
    };
</script>
@vite('resources/js/pages/exams/create.js')
@endpush

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('cohortsContainer');
        document.getElementById('addCohortBtn').addEventListener('click', () => {
            const index = container.children.length;
            const cohortHtml = `
                <div class="border border-gray-200 p-4 rounded-lg space-y-4 relative" data-index="${index}">
                    <button type="button" class="absolute top-2 right-2 text-red-600 removeCohort"><i class="fas fa-times-circle"></i></button>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Cohort Name</label>
                            <input name="cohorts[${index}][cohort_name]" class="w-full mt-1 px-3 py-2 border rounded-lg" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <input name="cohorts[${index}][description]" class="w-full mt-1 px-3 py-2 border rounded-lg">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Start Time</label>
                            <input type="datetime-local" name="cohorts[${index}][start_time]" class="w-full mt-1 px-3 py-2 border rounded-lg" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">End Time</label>
                            <input type="datetime-local" name="cohorts[${index}][end_time]" class="w-full mt-1 px-3 py-2 border rounded-lg" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Students</label>
                            <select multiple name="cohorts[${index}][student_ids][]" class="w-full mt-1 px-3 py-2 border rounded-lg" required>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}">
                                        {{ $student->first_name }} {{ $student->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', cohortHtml);
            container.querySelectorAll('.removeCohort').forEach(btn => btn.onclick = () => btn.closest('[data-index]').remove());
        });
    });
</script>
@endsection
