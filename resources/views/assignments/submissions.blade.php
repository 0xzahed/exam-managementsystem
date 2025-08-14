@extends('layouts.dashboard')

@section('title', $assignment->title . ' - Submissions')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-7xl">
    <!-- Page Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex flex-col lg:flex-row justify-between items-start gap-4">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 mb-4">
                    <a href="{{ route('instructor.assignments.show', $assignment) }}"
                        class="text-gray-600 hover:text-gray-900 transition-colors">
                        <i class="fas fa-arrow-left text-lg"></i>
                    </a>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 truncate">
                        {{ $assignment->title }} - Submissions
                    </h1>
                </div>

                <div class="flex flex-wrap items-center gap-3 mb-4">
                    <span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full">
                        {{ $assignment->course->title ?? 'General' }}
                    </span>
                    <span class="bg-green-100 text-green-800 text-sm font-medium px-3 py-1 rounded-full">
                        <i class="fas fa-users mr-1"></i>
                        {{ $assignment->submissions->count() }} Submissions
                    </span>
                    
                    @php
                        $dueDate = \Carbon\Carbon::parse($assignment->due_date);
                        $isOverdue = \Carbon\Carbon::now()->gt($dueDate);
                    @endphp
                    
                    @if($isOverdue)
                    <span class="bg-red-100 text-red-800 text-sm font-medium px-3 py-1 rounded-full">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Overdue
                    </span>
                    @endif
                </div>

                <p class="text-gray-600">
                    <strong>Due Date:</strong> {{ $dueDate->format('M d, Y h:i A') }}
                    <span class="ml-4"><strong>Total Marks:</strong> {{ $assignment->marks }}</span>
                </p>
            </div>
            
            <div class="flex flex-wrap gap-3">
                <!-- <button onclick="updateAssignmentMarks()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-edit mr-2"></i>
                    Update Marks
                </button> -->
                <button onclick="exportSubmissions()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-download mr-2"></i>
                    Export CSV
                </button>
                <button onclick="bulkGrade()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-edit mr-2"></i>
                    Bulk Grade
                </button>
                <button onclick="showAttemptManagementModal()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-redo mr-2"></i>
                    Manage Attempts
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        @php
            $totalSubmissions = $assignment->submissions->count();
            $gradedSubmissions = $assignment->submissions->whereNotNull('grade')->count();
            $pendingSubmissions = $totalSubmissions - $gradedSubmissions;
            $averageGrade = $gradedSubmissions > 0 ? $assignment->submissions->whereNotNull('grade')->avg('grade') : 0;
            $onTimeSubmissions = $assignment->submissions->where('created_at', '<=', $assignment->due_date)->count();
        @endphp
        
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Submissions</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalSubmissions }}</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-file-alt text-blue-600"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Graded</p>
                    <p class="text-2xl font-bold text-green-600">{{ $gradedSubmissions }}</p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-check-circle text-green-600"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Pending</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $pendingSubmissions }}</p>
                </div>
                <div class="bg-orange-100 p-3 rounded-full">
                    <i class="fas fa-clock text-orange-600"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Average Grade</p>
                    <p class="text-2xl font-bold text-purple-600">
                        @if($averageGrade > 0)
                        {{ number_format(($averageGrade / $assignment->marks) * 100, 1) }}%
                        @else
                        0%
                        @endif
                    </p>
                </div>
                <div class="bg-purple-100 p-3 rounded-full">
                    <i class="fas fa-chart-line text-purple-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Submissions List -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <h2 class="text-lg font-semibold text-gray-900">Student Submissions</h2>
                
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="relative">
                        <input type="text" id="searchSubmissions" placeholder="Search students..." 
                               class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                    <select id="filterSubmissions" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="all">All Submissions</option>
                        <option value="graded">Graded</option>
                        <option value="pending">Pending</option>
                        <option value="late">Late Submissions</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            @if($assignment->submissions->count() > 0)
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grade</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Files</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($assignment->submissions as $submission)
                            @php
                                $isLate = \Carbon\Carbon::parse($submission->created_at)->gt($dueDate);
                                $studentFiles = $submission->submission_files ?? [];
                                if (is_string($studentFiles)) {
                                    $studentFiles = json_decode($studentFiles, true) ?? [];
                                }
                            @endphp
                            <tr class="hover:bg-gray-50 submission-row" 
                                data-student-name="{{ $submission->student->first_name }} {{ $submission->student->last_name }}"
                                @if($submission->feedback) data-feedback="{{ $submission->feedback }}" @endif>
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            @if($submission->student->profile_picture)
                                            <img src="{{ $submission->student->profile_picture }}" 
                                                 alt="{{ $submission->student->first_name }}" 
                                                 class="h-10 w-10 rounded-full object-cover">
                                            @else
                                            <div class="h-10 w-10 rounded-full bg-indigo-600 flex items-center justify-center">
                                                <span class="text-sm font-medium text-white">
                                                    {{ substr($submission->student->first_name, 0, 1) }}{{ substr($submission->student->last_name, 0, 1) }}
                                                </span>
                                            </div>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $submission->student->first_name }} {{ $submission->student->last_name }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $submission->student->email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($submission->created_at)->format('M d, Y') }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ \Carbon\Carbon::parse($submission->created_at)->format('h:i A') }}
                                    </div>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($isLate)
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            Late
                                        </span>
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            <i class="fas fa-check mr-1"></i>
                                            On Time
                                        </span>
                                    @endif
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($submission->grade !== null)
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $submission->grade }}/{{ $assignment->marks }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ number_format(($submission->grade / $assignment->marks) * 100, 1) }}%
                                        </div>
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Pending
                                        </span>
                                    @endif
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if(count($studentFiles) > 0)
                                        <div class="text-sm text-gray-900">
                                            {{ count($studentFiles) }} file(s)
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            @foreach(array_slice($studentFiles, 0, 2) as $file)
                                                {{ $file['name'] ?? 'File' }}{{ !$loop->last ? ', ' : '' }}
                                            @endforeach
                                            @if(count($studentFiles) > 2)
                                                ...
                                            @endif
                                        </div>
                                    @elseif($submission->content)
                                        <div class="text-sm text-gray-500">
                                            <i class="fas fa-file-text mr-1"></i>
                                            Text submission
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-500">No files</span>
                                    @endif
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex gap-2">
                                        <button onclick="viewSubmission({{ $submission->id }})" 
                                                class="text-indigo-600 hover:text-indigo-900 p-2 rounded hover:bg-indigo-50" 
                                                title="View Submission">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <!-- <button onclick="gradeSubmission({{ $submission->id }})" 
                                                class="inline-flex items-center px-3 py-1 rounded-md text-sm font-medium transition-colors {{ $submission->grade !== null ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-blue-100 text-blue-800 hover:bg-blue-200' }}"
                                                title="{{ $submission->grade !== null ? 'Edit Grade' : 'Grade Submission' }}">
                                            <i class="fas fa-edit mr-1"></i>
                                            {{ $submission->grade !== null ? 'Edit Grade' : 'Grade' }}
                                        </button> -->
                                        
                                        @if(count($studentFiles) > 0 || $submission->file_path)
                                        <button onclick="downloadSubmission({{ $submission->id }})" 
                                                class="text-blue-600 hover:text-blue-900 p-2 rounded hover:bg-blue-50" 
                                                title="Download Files">
                                            <i class="fas fa-download"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-center py-12">
                    <div class="mx-auto h-12 w-12 text-gray-400 mb-4">
                        <i class="fas fa-inbox text-4xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Submissions Yet</h3>
                    <p class="text-gray-500 mb-4">Students haven't submitted their assignments yet.</p>
                    <div class="text-sm text-gray-400">
                        <p>Share the assignment link with your students to collect submissions.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Grade Modal -->
<div id="gradeModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4 max-h-screen overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Grade Submission</h3>
            <button onclick="closeGradeModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        
        <form id="gradeForm" method="POST" action="" class="space-y-4">
            @csrf
            <input type="hidden" id="submissionIdInput" name="submission_id" value="">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Grade (out of {{ $assignment->marks }})
                </label>
                <input type="number" id="gradeInput" name="grade" min="0" max="{{ $assignment->marks }}" step="0.5"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       placeholder="Enter grade" required>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Feedback (Optional)
                </label>
                <textarea id="feedbackInput" name="feedback" rows="4" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"
                          placeholder="Provide feedback to the student..."></textarea>
            </div>
            
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeGradeModal()" 
                        class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors">
                    Save Grade
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Update Assignment Marks Modal -->
<div id="updateMarksModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Update Assignment Total Marks</h3>
            <button onclick="closeUpdateMarksModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        
        <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <div class="flex">
                <i class="fas fa-exclamation-triangle text-yellow-600 mr-2 mt-1"></i>
                <div class="text-sm text-yellow-700">
                    <p class="font-semibold mb-1">Important:</p>
                    <p>Changing total marks will update all existing grades proportionally and sync with the gradebook.</p>
                </div>
            </div>
        </div>
        
        <form id="updateMarksForm" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Current Total Marks</label>
                <input type="text" value="{{ $assignment->marks }}" readonly 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-600">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">New Total Marks</label>
                <input type="number" id="newMarksInput" min="1" max="1000" step="0.5" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       placeholder="Enter new total marks">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Update Option</label>
                <select id="updateOption" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="proportional">Update existing grades proportionally</option>
                    <option value="keep_grades">Keep existing grades as they are</option>
                </select>
            </div>
            
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeUpdateMarksModal()" 
                        class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors">
                    Update Marks
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
@vite('resources/js/pages/assignments-submissions.js')
@endsection
