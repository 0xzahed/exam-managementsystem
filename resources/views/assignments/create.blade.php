@extends('layouts.dashboard')

@section('title', 'Create Assignment')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-4xl">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200 rounded-lg mb-6">
        <div class="px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <a href="{{ route('instructor.dashboard') }}"
                        class="mr-4 p-2 text-gray-600 hover:text-gray-800 rounded-lg hover:bg-gray-100"
                        title="Back to Dashboard">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </a>
                    <i class="fas fa-tasks text-2xl text-indigo-600 mr-3"></i>
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">Create Assignment</h1>
                        <p class="text-sm text-gray-500">Create new assignment for your students</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <button type="button" onclick="saveDraft()"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium">
                        <i class="fas fa-save mr-2"></i>Save Draft
                    </button>
                    <button type="button" onclick="publishAssignment()"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium">
                        <i class="fas fa-paper-plane mr-2"></i>Publish
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Assignment Form -->
    <form id="assignmentForm" method="POST" action="{{ route('instructor.assignments.store') }}" enctype="multipart/form-data">
        @csrf

        <!-- Basic Information -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-info-circle text-indigo-600 mr-2"></i>
                Assignment Details
            </h3>

            <div class="space-y-4">
                <!-- Course Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Course *</label>
                    <select name="course_id" id="courseId" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="">Choose a course...</option>
                        @foreach($courses as $course)
                        <option value="{{ $course->id }}">{{ $course->code }} - {{ $course->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Assignment Title -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Assignment Title *</label>
                        <input type="text" name="title" id="assignmentTitle" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                            placeholder="Enter assignment title">
                    </div>

                    <!-- Assignment Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Type *</label>
                        <input type="text" readonly value="Assignment" class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-600">
                        <input type="hidden" name="assignment_type" value="assignment">
                    </div>
                </div>

                <!-- Short Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Short Description</label>
                    <input type="text" name="short_description" id="shortDescription"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        placeholder="Brief description (optional)">
                </div>
            </div>
        </div>

        <!-- Dates -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-calendar-alt text-indigo-600 mr-2"></i>
                Assignment Dates
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Assign Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Assign Date *</label>
                    <input type="datetime-local" name="assign_date" id="assignDate" required
                        min="{{ now()->format('Y-m-d\TH:i') }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        value="{{ now()->format('Y-m-d\TH:i') }}">
                </div>

                <!-- Due Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Due Date *</label>
                    <input type="datetime-local" name="due_date" id="dueDate" required
                        min="{{ now()->format('Y-m-d\TH:i') }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        value="{{ now()->addDays(7)->format('Y-m-d\TH:i') }}">
                </div>
            </div>
        </div>

        <!-- Instructions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-clipboard-list text-indigo-600 mr-2"></i>
                Instructions
            </h3>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Assignment Instructions *</label>
                <textarea name="instructions" id="instructions" rows="10" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                    placeholder="Provide detailed instructions for the assignment..."></textarea>
            </div>
        </div>

        <!-- Instructor Files -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-paperclip text-indigo-600 mr-2"></i>
                Assignment Files (Optional)
            </h3>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Attach Files</label>
                <p class="text-sm text-gray-500 mb-3">Upload any files that students might need (templates, resources, etc.)</p>
                <input type="file" name="instructor_files[]" id="instructorFiles" multiple
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                    accept=".pdf,.doc,.docx,.txt,.zip,.rar">
                <p class="text-xs text-gray-400 mt-1">Supported: PDF, DOC, DOCX, TXT, ZIP, RAR (Max 10MB each)</p>
            </div>
        </div>

        <!-- Submission Settings -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-upload text-indigo-600 mr-2"></i>
                Student Submission Settings
            </h3>

            <div class="space-y-4">
                <!-- Submission Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Students Can Submit *</label>
                    <select name="submission_type" id="submissionType" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="both" selected>Files + Text</option>
                        <option value="file">Files Only</option>
                        <option value="text">Text Only</option>
                    </select>
                </div>

                <!-- File Settings -->
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Allowed File Types</label>
                        <div class="flex flex-wrap gap-3">
                            <label class="flex items-center">
                                <input type="checkbox" name="allowed_file_types[]" value="pdf" checked class="rounded mr-2">
                                <span class="text-sm">PDF</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="allowed_file_types[]" value="docx" checked class="rounded mr-2">
                                <span class="text-sm">DOCX</span>
                            </label>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Students can submit PDF and DOCX files</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Settings -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-cog text-indigo-600 mr-2"></i>
                Assignment Settings
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Late Submission -->
                <div>
                    <label class="flex items-center">
                        <input type="hidden" name="allow_late_submission" value="0">
                        <input type="checkbox" name="allow_late_submission" value="1" class="rounded mr-3">
                        <span class="text-sm font-medium text-gray-700">Allow Late Submission</span>
                    </label>
                    <p class="text-xs text-gray-500 mt-1">Students can submit after due date</p>
                </div>

                <!-- Notifications -->
                <div>
                    <label class="flex items-center">
                        <input type="hidden" name="notify_on_assign" value="0">
                        <input type="checkbox" name="notify_on_assign" value="1" checked class="rounded mr-3">
                        <span class="text-sm font-medium text-gray-700">Notify Students via Email</span>
                    </label>
                    <p class="text-xs text-gray-500 mt-1">Send email to all enrolled students when published</p>
                </div>

                <!-- Total Marks -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Total Marks *</label>
                    <input type="number" name="marks" id="marks" min="1" max="100" value="5" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        placeholder="Enter total marks">
                </div>

                <!-- Max Attempts -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Max Attempts *</label>
                    <input type="number" name="max_attempts" id="maxAttempts" min="1" max="5" value="1" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        placeholder="Number of attempts allowed">
                </div>
            </div>
        </div>

        <!-- Hidden Fields -->
        <input type="hidden" name="assign_to" value="all">
        <input type="hidden" name="status" id="assignmentStatus" value="draft">
        <input type="hidden" name="grading_type" value="points">
        <input type="hidden" name="grade_display" value="immediately">
        
        <!-- Hidden boolean fields with default values -->
        <input type="hidden" name="limit_attempts" value="0">
        <input type="hidden" name="notify_on_submission" value="0">
        <input type="hidden" name="send_reminders" value="0">
        <input type="hidden" name="notify_late_submission" value="0">

        <!-- Action Buttons -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex justify-between items-center">
                <a href="{{ route('instructor.assignments.index') }}" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium">
                    <i class="fas fa-times mr-2"></i>Cancel
                </a>
                <div class="space-x-3">
                    <button type="button" onclick="saveDraft()" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium">
                        <i class="fas fa-save mr-2"></i>Save Draft
                    </button>
                    <button type="button" onclick="publishAssignment()" class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium">
                        <i class="fas fa-paper-plane mr-2"></i>Publish Assignment
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
    <script src="https://cdn.tiny.cloud/1/4gukmwnqwk4bolj1vsoqqsxtiqtz8984n4baxsqeratjgw5g/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
    <script src="{{ asset('js/tinymce-config.js') }}"></script>
    @vite('resources/js/pages/assignments/create.js')
@endsection
