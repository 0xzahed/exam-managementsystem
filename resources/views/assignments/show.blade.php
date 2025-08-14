@extends('layouts.dashboard')

@section('title', $assignment->title)

@section('content')
<div class="container mx-auto px-4 py-6 max-w-7xl">
    <!-- Page Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex flex-col lg:flex-row justify-between items-start gap-4">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 mb-4">
                    <a href="{{ route('assignments.index') }}"
                        class="text-gray-600 hover:text-gray-900 transition-colors">
                        <i class="fas fa-arrow-left text-lg"></i>
                    </a>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 truncate">{{ $assignment->title }}</h1>
                </div>

                <div class="flex flex-wrap items-center gap-3 mb-4">
                    <span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full">
                        {{ $assignment->course->title ?? 'General' }}
                    </span>
                    <span class="px-3 py-1 text-sm font-medium rounded-full 
                        {{ ($assignment->status ?? 'published') === 'published' ? 'bg-green-100 text-green-800' : 
                           (($assignment->status ?? 'published') === 'draft' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                        {{ ucfirst($assignment->status ?? 'Published') }}
                    </span>
                    
                    @php
                        $dueDate = \Carbon\Carbon::parse($assignment->due_date);
                        $now = \Carbon\Carbon::now();
                        $isOverdue = $now->gt($dueDate);
                        $daysLeft = $now->diffInDays($dueDate, false);
                    @endphp

                    @if($isOverdue)
                        <span class="bg-red-100 text-red-800 text-sm font-medium px-3 py-1 rounded-full" data-tooltip="This assignment is past its due date">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            Overdue
                        </span>
                    @elseif($daysLeft <= 1)
                        <span class="bg-orange-100 text-orange-800 text-sm font-medium px-3 py-1 rounded-full" data-tooltip="Assignment due very soon">
                            <i class="fas fa-clock mr-1"></i>
                            Due {{ $daysLeft === 0 ? 'Today' : 'Tomorrow' }}
                        </span>
                    @else
                        <span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full" data-tooltip="Days remaining until due date">
                            <i class="fas fa-calendar mr-1"></i>
                            {{ $daysLeft }} days left
                        </span>
                    @endif
                </div>

                @if($assignment->short_description)
                    <p class="text-gray-600 text-lg">{{ $assignment->short_description }}</p>
                @endif
            </div>

            <!-- Assignment Actions -->
            @if(auth()->user()->role === 'instructor')
            <div class="w-full lg:w-auto flex flex-col gap-2">
                <div class="flex flex-col sm:flex-row gap-2">
                    <a href="{{ route('instructor.assignments.edit', $assignment) }}"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                        <i class="fas fa-edit"></i>
                        Edit Assignment
                    </a>
                    <a href="{{ route('instructor.assignments.submissions', $assignment) }}"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                        <i class="fas fa-list"></i>
                        View Submissions ({{ $assignment->submissions->count() }})
                    </a>
                    <form action="{{ route('instructor.assignments.destroy', $assignment) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this assignment? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                            <i class="fas fa-trash"></i>
                            Delete Assignment
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Assignment Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Assignment Info -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Assignment Details</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="fas fa-calendar-alt text-blue-500"></i>
                            <span class="font-medium text-gray-700">Due Date</span>
                        </div>
                        <p class="text-gray-900 font-semibold">{{ $dueDate->format('M j, Y \a\t g:i A') }}</p>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="fas fa-star text-yellow-500"></i>
                            <span class="font-medium text-gray-700">Marks</span>
                        </div>
                        <p class="text-gray-900 font-semibold">{{ $assignment->marks ?? 100 }} marks</p>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="fas fa-file-alt text-green-500"></i>
                            <span class="font-medium text-gray-700">Submission Type</span>
                        </div>
                        <p class="text-gray-900 font-semibold">{{ ucfirst($assignment->submission_type ?? 'file') }}</p>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="fas fa-clock text-purple-500"></i>
                            <span class="font-medium text-gray-700">Late Submission</span>
                        </div>
                        <p class="text-gray-900 font-semibold">
                            {{ ($assignment->allow_late_submission ?? false) ? 'Allowed' : 'Not Allowed' }}
                        </p>
                    </div>
                </div>

                @if($assignment->description)
                <div class="prose max-w-none">
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Description</h3>
                    <div class="text-gray-700 whitespace-pre-wrap bg-gray-50 p-4 rounded-lg">{{ $assignment->description }}</div>
                </div>
                @endif

                @if($assignment->instructions)
                <div class="mt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Instructions</h3>
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-lg">
                        <div class="text-gray-700 whitespace-pre-wrap">{{ $assignment->instructions }}</div>
                    </div>
                </div>
                @endif

                <!-- Instructor Files -->
                @if($assignment->instructor_files)
                    @php
                        $instructorFiles = is_string($assignment->instructor_files) ? json_decode($assignment->instructor_files, true) : $assignment->instructor_files;
                    @endphp
                    @if($instructorFiles && count($instructorFiles) > 0)
                    <div class="mt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">
                            <i class="fas fa-paperclip text-gray-600 mr-2"></i>
                            Assignment Files
                        </h3>
                        <div class="space-y-2">
                            @foreach($instructorFiles as $file)
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    @php
                                        $fileExtension = pathinfo($file['name'] ?? 'file', PATHINFO_EXTENSION);
                                        $iconClass = match(strtolower($fileExtension)) {
                                            'pdf' => 'fas fa-file-pdf text-red-500',
                                            'doc', 'docx' => 'fas fa-file-word text-blue-500',
                                            'ppt', 'pptx' => 'fas fa-file-powerpoint text-orange-500',
                                            'xls', 'xlsx' => 'fas fa-file-excel text-green-500',
                                            'jpg', 'jpeg', 'png', 'gif' => 'fas fa-file-image text-purple-500',
                                            default => 'fas fa-file text-gray-500'
                                        };
                                    @endphp
                                    <i class="{{ $iconClass }}"></i>
                                    <div>
                                        <span class="font-medium text-gray-900">{{ $file['name'] ?? 'File' }}</span>
                                        @if(isset($file['size']))
                                        <span class="text-xs text-gray-500 ml-2">({{ number_format($file['size'] / 1024, 1) }} KB)</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    @if(isset($file['drive_link']))
                                    <a href="{{ $file['drive_link'] }}" target="_blank"
                                        class="text-green-600 hover:text-green-800 font-medium transition-colors px-3 py-1 bg-green-50 rounded-md">
                                        <i class="fab fa-google-drive mr-1"></i>
                                        Drive
                                    </a>
                                    @endif
                                    @if(isset($file['local_url']))
                                    <a href="{{ $file['local_url'] }}" target="_blank"
                                        class="text-blue-600 hover:text-blue-800 font-medium transition-colors px-3 py-1 bg-blue-50 rounded-md">
                                        <i class="fas fa-download mr-1"></i>
                                        Download
                                    </a>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                @endif
            </div>

            <!-- Submission Guidelines -->
            @if(($assignment->submission_type === 'file' || $assignment->submission_type === 'both') && auth()->user()->role === 'student')
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                    Submission Guidelines
                </h3>

                <div class="space-y-3 text-sm text-gray-600">
                    @if($assignment->max_file_size_mb ?? 10)
                    <div class="flex items-center gap-2">
                        <i class="fas fa-weight-hanging text-blue-500"></i>
                        <span>Maximum file size: {{ $assignment->max_file_size_mb ?? 10 }}MB per file</span>
                    </div>
                    @endif

                    @if($assignment->allowed_file_types)
                        @php
                            $allowedTypesRaw = $assignment->allowed_file_types;
                            if (is_string($allowedTypesRaw)) {
                                $allowedTypes = json_decode($allowedTypesRaw, true);
                                if (!is_array($allowedTypes)) {
                                    $allowedTypes = explode(',', $allowedTypesRaw);
                                }
                            } elseif (is_array($allowedTypesRaw)) {
                                $allowedTypes = $allowedTypesRaw;
                            } else {
                                $allowedTypes = ['pdf', 'doc', 'docx'];
                            }
                            $allowedTypes = array_filter(array_map('trim', $allowedTypes));
                        @endphp
                        <div class="flex items-center gap-2">
                            <i class="fas fa-file-alt text-green-500"></i>
                            <span>Allowed file types: {{ implode(', ', $allowedTypes) }}</span>
                        </div>
                    @else
                        <div class="flex items-center gap-2">
                            <i class="fas fa-file-alt text-green-500"></i>
                            <span>Allowed file types: pdf, doc, docx, ppt, pptx, xls, xlsx, txt, jpg, jpeg, png</span>
                        </div>
                    @endif

                    @if($assignment->max_attempts ?? null)
                    <div class="flex items-center gap-2">
                        <i class="fas fa-redo text-orange-500"></i>
                        <span>Maximum attempts: {{ $assignment->max_attempts }}</span>
                    </div>
                    @endif
                    
                    @if($userSubmission)
                    <div class="flex items-center gap-2">
                        <i class="fas fa-info-circle text-blue-500"></i>
                        <span>Your attempts: {{ $userSubmission->attempt_number ?? 1 }}/{{ $assignment->max_attempts }}</span>
                    </div>
                    @endif

                    <div class="flex items-center gap-2">
                        <i class="fas fa-upload text-purple-500"></i>
                        <span>Multiple files can be uploaded simultaneously</span>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Submission Section -->
        <div class="space-y-6">
            @if(auth()->user()->role === 'student')
                <!-- Student Submission Form -->
                @php
                    $currentAttempts = $assignment->getStudentAttemptNumber(auth()->id());
                    $maxAttempts = $assignment->max_attempts ?? 3;
                    $canSubmit = $assignment->canStudentSubmit(auth()->id());
                    $attemptsRemaining = $assignment->getStudentRemainingAttempts(auth()->id());
                @endphp

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Your Submission</h3>

                    @if($userSubmission)
                        <!-- Existing Submission -->
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                            <div class="flex items-center gap-2 mb-2">
                                <i class="fas fa-check-circle text-green-500"></i>
                                <span class="font-medium text-green-800">Submitted Successfully</span>
                            </div>
                            <p class="text-green-700 text-sm">
                                Submitted on {{ $userSubmission->created_at ? $userSubmission->created_at->format('M j, Y \a\t g:i A') : 'N/A' }}
                            </p>
                            @if($userSubmission->grade ?? null)
                            <p class="text-green-700 text-sm mt-1">
                                Grade: {{ $userSubmission->grade }}/{{ $assignment->marks ?? 100 }}
                            </p>
                            @endif
                            
                            <!-- Attempt Information -->
                            <div class="mt-3 pt-3 border-t border-green-200">
                                <div class="flex items-center justify-between">
                                    <span class="text-green-700 text-sm font-medium">Attempt {{ $userSubmission->attempt_number ?? 1 }} of {{ $maxAttempts }}</span>
                                    @if($attemptsRemaining > 0)
                                        <span class="text-green-600 text-sm bg-green-100 px-2 py-1 rounded-full">
                                            {{ $attemptsRemaining }} attempt{{ $attemptsRemaining > 1 ? 's' : '' }} remaining
                                        </span>
                                    @else
                                        <span class="text-red-600 text-sm bg-red-100 px-2 py-1 rounded-full">
                                            No attempts remaining
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if($userSubmission->file_path)
                        <div class="mb-4">
                            <a href="{{ route('assignments.download', $userSubmission) }}"
                                class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 font-medium">
                                <i class="fas fa-download"></i>
                                Download Your Submission
                            </a>
                        </div>
                        @endif

                        @if($userSubmission->feedback)
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                            <h4 class="font-medium text-blue-800 mb-2">
                                <i class="fas fa-comment mr-2"></i>
                                Instructor Feedback
                            </h4>
                            <p class="text-blue-700 text-sm whitespace-pre-wrap">{{ $userSubmission->feedback }}</p>
                        </div>
                        @endif
                    @endif

                    @if($canSubmit && (!$isOverdue || ($assignment->allow_late_submission ?? false)))
                        <!-- Submission Form -->
                        <form action="{{ route('assignments.process-submission', $assignment) }}" method="POST" enctype="multipart/form-data" id="submissionForm" data-allowed='@json($assignment->allowed_file_types ?? [])' data-submission-type="{{ $assignment->submission_type }}">
                            @csrf

                            @if($assignment->submission_type === 'file' || $assignment->submission_type === 'both')
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Upload File {{ $userSubmission ? '(Resubmit)' : '' }}
                                </label>
                                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 transition-colors" id="dropZone">
                                    @php
                                        $allowedTypesRaw = $assignment->allowed_file_types ?? ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'jpg', 'jpeg', 'png'];
                                        if (is_string($allowedTypesRaw)) {
                                            $allowedTypes = json_decode($allowedTypesRaw, true);
                                            if (!is_array($allowedTypes)) {
                                                $allowedTypes = explode(',', $allowedTypesRaw);
                                            }
                                        } elseif (is_array($allowedTypesRaw)) {
                                            $allowedTypes = $allowedTypesRaw;
                                        } else {
                                            $allowedTypes = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'jpg', 'jpeg', 'png'];
                                        }
                                        $allowedTypes = array_filter(array_map('trim', $allowedTypes));
                                        if (empty($allowedTypes)) {
                                            $allowedTypes = ['pdf', 'doc', 'docx'];
                                        }
                                        $accept = '.' . implode(',.', $allowedTypes);
                                    @endphp
                                    <input type="file" name="submission_files[]" id="fileInput" class="hidden" multiple accept="{{ $accept }}" @if($assignment->submission_type === 'file') required @endif>
                                    <div id="dropZoneContent">
                                        <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                        <p class="text-gray-600 mb-2">Drag and drop your files here, or click to browse</p>
                                        <button type="button" onclick="document.getElementById('fileInput').click()"
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                            Choose Files
                                        </button>
                                    </div>
                                </div>
                                <div id="fileInfo" class="mt-2 hidden">
                                    <div class="flex items-center gap-2 text-sm text-gray-600">
                                        <i class="fas fa-file"></i>
                                        <span id="fileName"></span>
                                        <button type="button" onclick="clearFile()" class="text-red-600 hover:text-red-800 ml-2">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Allowed: {{ implode(', ', $allowedTypes) }}. Multiple files allowed.
                                    </p>
                                </div>
                            </div>
                            @endif

                            @if($assignment->submission_type === 'text' || $assignment->submission_type === 'both')
                            <div class="mb-4">
                                <label for="submission_text" class="block text-sm font-medium text-gray-700 mb-2">
                                    Text Submission
                                </label>
                                <textarea name="submission_text" id="submission_text" rows="6"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                                    placeholder="Enter your submission text here...">{{ $userSubmission->content ?? '' }}</textarea>
                                @if($assignment->submission_type === 'text')
                                <p class="text-xs text-gray-500 mt-1">File upload is disabled for this assignment.</p>
                                @endif
                            </div>
                            @endif

                            <div class="mb-4">
                                <label for="comments" class="block text-sm font-medium text-gray-700 mb-2">
                                    Comments (Optional)
                                </label>
                                <textarea name="comments" id="comments" rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                                    placeholder="Add any comments for your instructor..."></textarea>
                            </div>

                            @if($isOverdue && ($assignment->allow_late_submission ?? false))
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-exclamation-triangle text-yellow-500"></i>
                                    <span class="font-medium text-yellow-800">Late Submission Warning</span>
                                </div>
                                <p class="text-yellow-700 text-sm mt-1">
                                    This assignment is overdue. Late submission penalties may apply.
                                </p>
                            </div>
                            @endif

                            <button type="submit"
                                class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-medium py-3 px-4 rounded-lg transition-all duration-200 transform hover:-translate-y-0.5 hover:shadow-lg">
                                <i class="fas fa-paper-plane mr-2"></i>
                                {{ $userSubmission ? 'Resubmit Assignment' : 'Submit Assignment' }}
                            </button>
                        </form>
                    @elseif($isOverdue && !($assignment->allow_late_submission ?? false))
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
                            <i class="fas fa-exclamation-circle text-red-500 text-2xl mb-2"></i>
                            <p class="text-red-700 font-medium">Submission Closed</p>
                            <p class="text-red-600 text-sm">This assignment is overdue and late submissions are not allowed.</p>
                        </div>
                    @elseif(!$canSubmit)
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-center">
                            <i class="fas fa-info-circle text-gray-500 text-2xl mb-2"></i>
                            <p class="text-gray-700 font-medium">Maximum Attempts Reached</p>
                            <p class="text-gray-600 text-sm">You have used all {{ $maxAttempts }} submission attempts for this assignment.</p>
                            <p class="text-gray-500 text-xs mt-2">Contact your instructor if you need additional attempts.</p>
                        </div>
                    @endif
                </div>
            @else
                <!-- Instructor View -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-chart-bar text-blue-500 mr-2"></i>
                        Submission Statistics
                    </h3>

                    @php
                        $totalSubmissions = $assignment->submissions->count();
                        $gradedSubmissions = $assignment->submissions->whereNotNull('grade')->count();
                        $pendingSubmissions = $assignment->submissions->whereNull('grade')->count();
                        $averageGrade = $assignment->submissions->whereNotNull('grade')->avg('grade');
                    @endphp

                    <div class="space-y-4">
                        <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg">
                            <span class="text-blue-700 font-medium">Total Submissions</span>
                            <span class="text-blue-900 font-bold">{{ $totalSubmissions }}</span>
                        </div>

                        <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                            <span class="text-green-700 font-medium">Graded</span>
                            <span class="text-green-900 font-bold">{{ $gradedSubmissions }}</span>
                        </div>

                        <div class="flex justify-between items-center p-3 bg-yellow-50 rounded-lg">
                            <span class="text-yellow-700 font-medium">Pending</span>
                            <span class="text-yellow-900 font-bold">{{ $pendingSubmissions }}</span>
                        </div>

                        @if($averageGrade)
                        <div class="flex justify-between items-center p-3 bg-purple-50 rounded-lg">
                            <span class="text-purple-700 font-medium">Average Grade</span>
                            <span class="text-purple-900 font-bold">{{ number_format($averageGrade, 1) }}/{{ $assignment->marks ?? 100 }}</span>
                        </div>
                        @endif
                    </div>

                    <a href="{{ route('instructor.assignments.submissions', $assignment) }}"
                        class="block w-full mt-4 bg-blue-600 hover:bg-blue-700 text-white text-center py-2 px-4 rounded-lg font-medium transition-colors">
                        <i class="fas fa-list mr-2"></i>
                        Grade Submissions
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
@vite('resources/js/pages/assignment-show.js')
@endsection
