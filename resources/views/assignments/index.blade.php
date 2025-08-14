@extends('layouts.dashboard')

@section('title', 'Assignments')

@section('content')
<div class="px-4 py-8 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">📝 Assignments</h1>
                <p class="text-gray-600 mt-1">
                    @if(auth()->user()->role === 'instructor')
                    Manage and track assignment submissions
                    @else
                    View and submit your assignments
                    @endif
                </p>
            </div>
            @if(auth()->user()->role === 'instructor')
            <a href="{{ route('instructor.assignments.create') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-2 whitespace-nowrap">
                <i class="fas fa-plus"></i>
                Create Assignment
            </a>
            @endif
        </div>
    </div>

    <!-- Flash messages are now handled by the central notification system -->

    <!-- Statistics Cards -->
    @if(auth()->user()->role === 'instructor')
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6">
        <div class="bg-gradient-to-br bg-blue-500 to-blue-600 rounded-xl p-4 sm:p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium">Total Assignments</p>
                    <p class="text-2xl sm:text-3xl font-bold">{{ $assignments->count() }}</p>
                </div>
                <i class="fas fa-tasks text-2xl text-blue-200"></i>
            </div>
        </div>

        <div class="bg-gradient-to-br bg-green-500 to-green-600 rounded-xl p-4 sm:p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium">Active</p>
                    <p class="text-2xl sm:text-3xl font-bold">{{ $assignments->where('status', 'published')->count() }}</p>
                </div>
                <i class="fas fa-play-circle text-2xl text-green-200"></i>
            </div>
        </div>

        <div class="bg-gradient-to-br bg-yellow-500 to-orange-500 rounded-xl p-4 sm:p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-yellow-100 text-sm font-medium">Draft</p>
                    <p class="text-2xl sm:text-3xl font-bold">{{ $assignments->where('status', 'draft')->count() }}</p>
                </div>
                <i class="fas fa-edit text-2xl text-yellow-200"></i>
            </div>
        </div>

        <div class="bg-gradient-to-br bg-purple-500 to-purple-600 rounded-xl p-4 sm:p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium">Submissions</p>
                    <p class="text-2xl sm:text-3xl font-bold">{{ $assignments->sum('submission_count') }}</p>
                </div>
                <i class="fas fa-upload text-2xl text-purple-200"></i>
            </div>
        </div>
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 mb-6">
        <div class="bg-gradient-to-br bg-blue-500 to-blue-600 rounded-xl p-4 sm:p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium">Total Assignments</p>
                    <p class="text-2xl sm:text-3xl font-bold">{{ $assignments->count() }}</p>
                </div>
                <i class="fas fa-tasks text-2xl text-blue-200"></i>
            </div>
        </div>

        <div class="bg-gradient-to-br bg-green-500 to-green-600 rounded-xl p-4 sm:p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium">Submitted</p>
                    <p class="text-2xl sm:text-3xl font-bold">0</p>
                </div>
                <i class="fas fa-check-circle text-2xl text-green-200"></i>
            </div>
        </div>

        <div class="bg-gradient-to-br bg-red-500 to-red-600 rounded-xl p-4 sm:p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100 text-sm font-medium">Pending</p>
                    <p class="text-2xl sm:text-3xl font-bold">{{ $assignments->count() }}</p>
                </div>
                <i class="fas fa-clock text-2xl text-red-200"></i>
            </div>
        </div>
    </div>
    @endif

    <!-- Filter Tabs -->
    <div class="mb-6">
        <div class="flex flex-wrap gap-2 bg-white rounded-lg p-2 shadow-sm border border-gray-200">
            <button onclick="filterAssignments('all')" id="allBtn"
                class="filter-btn px-4 py-2 rounded-lg font-medium transition-colors bg-blue-600 text-white">
                All Assignments
            </button>
            @if(auth()->user()->role === 'student')
            <button onclick="filterAssignments('pending')" id="pendingBtn"
                class="filter-btn px-4 py-2 rounded-lg font-medium transition-colors bg-gray-100 text-gray-700">
                Pending
            </button>
            <button onclick="filterAssignments('submitted')" id="submittedBtn"
                class="filter-btn px-4 py-2 rounded-lg font-medium transition-colors bg-gray-100 text-gray-700">
                Submitted
            </button>
            <button onclick="filterAssignments('overdue')" id="overdueBtn"
                class="filter-btn px-4 py-2 rounded-lg font-medium transition-colors bg-gray-100 text-gray-700">
                Overdue
            </button>
            @else
            <button onclick="filterAssignments('active')" id="activeBtn"
                class="filter-btn px-4 py-2 rounded-lg font-medium transition-colors bg-gray-100 text-gray-700">
                Active
            </button>
            <button onclick="filterAssignments('draft')" id="draftBtn"
                class="filter-btn px-4 py-2 rounded-lg font-medium transition-colors bg-gray-100 text-gray-700">
                Draft
            </button>
            <button onclick="filterAssignments('closed')" id="closedBtn"
                class="filter-btn px-4 py-2 rounded-lg font-medium transition-colors bg-gray-100 text-gray-700">
                Closed
            </button>
            @endif
        </div>
    </div>



    <!-- Assignments Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-6" id="assignmentsGrid">
        @forelse($assignments as $assignment)
        <div class="assignment-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition-all duration-300"
            data-status="{{ $assignment->status ?? 'active' }}"
            data-submission="{{ $assignment->submission_status ?? 'pending' }}">

            <!-- Assignment Header -->
            <div class="bg-gradient-to-r from-blue-50 to-purple-50 p-4 sm:p-6 border-b border-gray-100">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg sm:text-xl font-semibold text-gray-900 mb-2 line-clamp-2">{{ $assignment->title }}</h3>
                        <div class="flex flex-wrap items-center gap-2 mb-2">
                            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-1 rounded-full">
                                {{ $assignment->course->title ?? 'General' }}
                            </span>
                            <span class="px-2 py-1 text-xs font-medium rounded-full 
                                {{ ($assignment->status ?? 'active') === 'published' ? 'bg-green-100 text-green-800' : 
                                   (($assignment->status ?? 'active') === 'draft' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ ucfirst($assignment->status ?? 'Active') }}
                            </span>
                            @if(auth()->user()->role === 'student' && isset($assignment->submission_status))
                            <span class="px-2 py-1 text-xs font-medium rounded-full 
                                {{ $assignment->submission_status === 'submitted' ? 'bg-green-100 text-green-800' : 
                                   ($assignment->submission_status === 'overdue' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                {{ ucfirst($assignment->submission_status) }}
                            </span>
                            @endif
                        </div>
                    </div>
                </div>

                <p class="text-gray-600 text-sm line-clamp-2 mb-3">{{ $assignment->short_description ?? 'No description available.' }}</p>

                <!-- Assignment Meta -->
                <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500">
                    <div class="flex items-center bg-white rounded-full px-2 py-1">
                        <i class="fas fa-calendar mr-1 text-blue-500"></i>
                        <span>Due: {{ $assignment->formatted_due_date }}</span>
                    </div>
                    @if(auth()->user()->role === 'instructor')
                    <div class="flex items-center bg-white rounded-full px-2 py-1">
                        <i class="fas fa-users mr-1 text-green-500"></i>
                        <span>{{ $assignment->submission_count }} submissions</span>
                    </div>
                    @endif
                    <div class="flex items-center bg-white rounded-full px-2 py-1">
                        <i class="fas fa-star mr-1 text-yellow-500"></i>
                        <span>{{ $assignment->marks }} pts</span>
                    </div>
                </div>
            </div>

            <!-- Assignment Actions -->
            <div class="p-4 sm:p-6">
                @if(auth()->user()->role === 'instructor')
                <div class="grid grid-cols-3 gap-2 sm:gap-3">
                    <a href="{{ route('instructor.assignments.show', $assignment) }}"
                        class="bg-blue-600 hover:bg-blue-700 text-white text-center py-2 sm:py-3 px-3 sm:px-4 rounded-lg font-medium transition-all duration-200 flex items-center justify-center gap-2 text-sm">
                        <i class="fas fa-eye text-xs"></i>
                        View
                    </a>
                    <a href="{{ route('instructor.assignments.edit', $assignment) }}"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-center py-2 sm:py-3 px-3 sm:px-4 rounded-lg font-medium transition-all duration-200 flex items-center justify-center gap-2 text-sm">
                        <i class="fas fa-edit text-xs"></i>
                        Edit
                    </a>
                    <form action="{{ route('instructor.assignments.destroy', $assignment) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this assignment?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-100 hover:bg-red-200 text-red-700 text-center py-2 sm:py-3 px-3 sm:px-4 rounded-lg font-medium transition-all duration-200 flex items-center justify-center gap-2 text-sm">
                            <i class="fas fa-trash text-xs"></i>
                            Delete
                        </button>
                    </form>
                </div>
                @else
                <div class="space-y-2">
                    @if(($assignment->submission_status ?? 'pending') === 'submitted')
                    <div class="bg-green-50 border border-green-200 rounded-lg p-3 text-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        <span class="text-green-700 font-medium">Submitted</span>
                    </div>
                    @elseif($assignment->isOverdue() && $assignment->status === 'published')
                    <div class="bg-red-50 border border-red-200 rounded-lg p-3 text-center">
                        <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                        <span class="text-red-700 font-medium">Overdue</span>
                    </div>
                    @else
                    <a href="{{ route('assignments.show', $assignment) }}"
                        class="block bg-blue-600 hover:bg-blue-700 text-white text-center py-2 sm:py-3 px-3 sm:px-4 rounded-lg font-medium transition-all duration-200">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Submit Assignment
                    </a>
                    @endif

                </div>

            </div>
            @endif

            <!-- Due Date Indicator -->
            <div class="mt-4 pt-4 border-t border-gray-100">
                <div class="text-center text-sm">
                    @if($assignment->isOverdue() && $assignment->status === 'published')
                    <span class="text-red-600 font-medium">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Overdue by {{ abs($assignment->days_until_due) }} day{{ abs($assignment->days_until_due) !== 1 ? 's' : '' }}
                    </span>
                    @elseif($assignment->days_until_due <= 1 && $assignment->days_until_due >= 0)
                        <span class="text-orange-600 font-medium">
                            <i class="fas fa-clock mr-1"></i>
                            Due {{ $assignment->days_until_due === 0 ? 'today' : 'tomorrow' }}
                        </span>
                        @else
                        <span class="text-gray-600">
                            <i class="fas fa-calendar mr-1"></i>
                            {{ $assignment->days_until_due }} day{{ $assignment->days_until_due !== 1 ? 's' : '' }} remaining
                        </span>
                        @endif
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-full text-center py-12">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
            <i class="fas fa-tasks text-2xl text-gray-400"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No assignments found</h3>
        <p class="text-gray-500 mb-4">
            @if(auth()->user()->role === 'instructor')
            Create your first assignment to get started
            @else
            No assignments have been posted yet
            @endif
        </p>
        @if(auth()->user()->role === 'instructor')
        <a href="{{ route('instructor.assignments.create') }}"
            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
            <i class="fas fa-plus mr-2"></i>
            Create Assignment
        </a>
        @endif
    </div>
    @endforelse
</div>
</div>

<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>

<script>
    function filterAssignments(filter) {
        // Remove active class from all buttons
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('bg-blue-600', 'text-white');
            btn.classList.add('bg-gray-100', 'text-gray-700');
        });

        // Add active class to clicked button
        document.getElementById(filter + 'Btn').classList.add('bg-blue-600', 'text-white');
        document.getElementById(filter + 'Btn').classList.remove('bg-gray-100', 'text-gray-700');

        // Show/hide assignment cards based on filter
        document.querySelectorAll('.assignment-card').forEach(card => {
            if (filter === 'all') {
                card.style.display = 'block';
            } else if (filter === 'active' || filter === 'published') {
                card.style.display = card.dataset.status === 'published' ? 'block' : 'none';
            } else if (filter === 'draft') {
                card.style.display = card.dataset.status === 'draft' ? 'block' : 'none';
            } else if (filter === 'pending') {
                card.style.display = card.dataset.submission === 'pending' ? 'block' : 'none';
            } else if (filter === 'submitted') {
                card.style.display = card.dataset.submission === 'submitted' ? 'block' : 'none';
            } else if (filter === 'overdue') {
                card.style.display = card.dataset.submission === 'overdue' ? 'block' : 'none';
            } else {
                card.style.display = 'block';
            }
        });
    }
</script>
@endsection