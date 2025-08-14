@extends('layouts.dashboard')

@section('title', 'View Submission - ' . $submission->assignment_title)

@section('content')
<div class="px-4 py-8 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">📄 View Submission</h1>
                <p class="text-gray-600 mt-1">{{ $submission->assignment_title }} - {{ $submission->course_title }}</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('instructor.assignments.submissions', ['assignment' => $submission->assignment_id]) }}"
                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i>
                    Back to Submissions
                </a>
                @if(!empty($submissionFiles) || $submission->google_drive_url)
                <a href="{{ route('instructor.assignments.submissions.download', $submission->id) }}"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-2">
                    <i class="fas fa-download"></i>
                    Download Files
                </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Student Information Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">👤 Student Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-600">Student Name</p>
                <p class="font-medium text-gray-900">{{ $submission->first_name }} {{ $submission->last_name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Email</p>
                <p class="font-medium text-gray-900">{{ $submission->student_email }}</p>
            </div>
        </div>
    </div>

    <!-- Submission Details Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">📝 Submission Details</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div>
                <p class="text-sm text-gray-600">Submitted Date</p>
                <p class="font-medium text-gray-900">{{ $submission->submitted_at->format('M d, Y h:i A') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Due Date</p>
                <p class="font-medium text-gray-900">{{ $submission->due_date->format('M d, Y h:i A') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Status</p>
                @if($submission->is_late)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Late by {{ $submission->days_late }} day{{ $submission->days_late !== 1 ? 's' : '' }}
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <i class="fas fa-check-circle mr-1"></i>
                        On Time
                    </span>
                @endif
            </div>
        </div>

        <!-- Grade Section -->
        <div class="border-t border-gray-200 pt-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-md font-medium text-gray-900">Grade</h3>
                <span class="text-lg font-semibold text-gray-900">
                    {{ $submission->grade ?? 'Not Graded' }} / {{ $submission->total_marks }}
                </span>
            </div>
            
            @if(!$submission->grade)
            <form action="{{ route('instructor.assignments.submissions.grade', $submission->id) }}" method="POST" class="space-y-3">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Grade</label>
                        <input type="number" 
                               name="grade" 
                               placeholder="Enter grade (0-{{ $submission->total_marks }})"
                               min="0" 
                               max="{{ $submission->total_marks }}"
                               step="0.1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                               required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Feedback</label>
                        <input type="text" 
                               name="feedback" 
                               placeholder="Feedback (optional)"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium transition-colors flex items-center gap-2">
                        <i class="fas fa-check"></i>
                        Grade Submission
                    </button>
                </div>
            </form>
            @else
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="font-medium text-green-800">Grade: {{ $submission->grade }} / {{ $submission->total_marks }}</p>
                        @if($submission->feedback)
                        <p class="text-green-700 mt-1">{{ $submission->feedback }}</p>
                        @endif
                        @if($submission->graded_at)
                        <p class="text-xs text-green-600 mt-1">
                            Graded on {{ \Carbon\Carbon::parse($submission->graded_at)->format('M d, Y h:i A') }}
                        </p>
                        @endif
                    </div>
                    <button onclick="showUpdateGradeForm()" class="text-green-600 hover:text-green-800">
                        <i class="fas fa-edit"></i>
                    </button>
                </div>
            </div>

            <!-- Update Grade Form (Hidden by default) -->
            <div id="updateGradeForm" class="hidden mt-4 bg-gray-50 border border-gray-200 rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-900 mb-3">Update Grade</h4>
                <form action="{{ route('instructor.assignments.submissions.grade', $submission->id) }}" method="POST" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Grade</label>
                            <input type="number" 
                                   name="grade" 
                                   value="{{ $submission->grade }}"
                                   min="0" 
                                   max="{{ $submission->total_marks }}"
                                   step="0.1"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                   required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Feedback</label>
                            <input type="text" 
                                   name="feedback" 
                                   value="{{ $submission->feedback }}"
                                   placeholder="Feedback (optional)"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" onclick="hideUpdateGradeForm()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">
                            Update Grade
                        </button>
                    </div>
                </form>
            </div>
            @endif
        </div>
    </div>

    <!-- Text Submission Content -->
    @if($submission->content)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">📝 Text Submission</h2>
        <div class="prose max-w-none">
            <div class="bg-gray-50 rounded-lg p-4 whitespace-pre-wrap">{{ $submission->content }}</div>
        </div>
    </div>
    @endif

    <!-- File Submissions -->
    @if($submission->google_drive_url || !empty($submissionFiles))
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">📎 File Submissions</h2>
        
        @if($submission->google_drive_url)
        <div class="space-y-3">
            <div class="flex items-center justify-between p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex items-center">
                    <i class="fab fa-google-drive text-2xl text-blue-600 mr-3"></i>
                    <div>
                        <p class="font-medium text-gray-900">Assignment File (Google Drive)</p>
                        <p class="text-sm text-gray-600">Uploaded to Google Drive</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ $submission->google_drive_url }}" 
                       target="_blank"
                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        <i class="fas fa-external-link-alt mr-1"></i>
                        View in Drive
                    </a>
                    <a href="{{ route('instructor.assignments.submissions.download', $submission->id) }}" 
                       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        <i class="fas fa-download mr-1"></i>
                        Download
                    </a>
                </div>
            </div>
        </div>
        @endif

    </div>
    @endif

    <!-- Student Comments -->
    @if($submission->comments)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">💬 Student Comments</h2>
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <p class="text-gray-900 whitespace-pre-wrap">{{ $submission->comments }}</p>
        </div>
    </div>
    @endif
</div>

<script>
function showUpdateGradeForm() {
    const form = document.getElementById('updateGradeForm');
    form.classList.remove('hidden');
    // Focus on the grade input
    const gradeInput = form.querySelector('input[name="grade"]');
    if (gradeInput) {
        gradeInput.focus();
        gradeInput.select();
    }
}

function hideUpdateGradeForm() {
    document.getElementById('updateGradeForm').classList.add('hidden');
}

// Handle escape key to close the form
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        hideUpdateGradeForm();
    }
});
</script>
@endsection
