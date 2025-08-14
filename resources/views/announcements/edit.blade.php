@extends('layouts.dashboard')

@section('title', 'Edit Announcement')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h1 class="text-2xl font-bold text-gray-900">Edit Announcement</h1>
            <p class="text-gray-600 mt-1">Update your announcement details</p>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <form action="{{ route('instructor.announcements.update', $announcement) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            
            <!-- Display Errors -->
            @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">There were some errors with your submission</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-6">
                <!-- Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-heading mr-1"></i>
                        Title *
                    </label>
                    <input type="text" id="title" name="title" value="{{ old('title', $announcement->title) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Course Selection -->
                <div>
                    <label for="course_id" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-book mr-1"></i>
                        Course *
                    </label>
                    <select id="course_id" name="course_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select a course</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" {{ (old('course_id', $announcement->course_id) == $course->id) ? 'selected' : '' }}>
                                {{ $course->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Priority -->
                <div>
                    <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-flag mr-1"></i>
                        Priority
                    </label>
                    <select id="priority" name="priority"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="low" {{ (old('priority', $announcement->priority) == 'low') ? 'selected' : '' }}>Low Priority</option>
                        <option value="medium" {{ (old('priority', $announcement->priority) == 'medium') ? 'selected' : '' }}>Medium Priority</option>
                        <option value="high" {{ (old('priority', $announcement->priority) == 'high') ? 'selected' : '' }}>High Priority</option>
                    </select>
                </div>

                <!-- Content -->
                <div>
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-align-left mr-1"></i>
                        Content *
                    </label>
                    <textarea id="content" name="content" rows="8" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                              placeholder="Enter your announcement content...">{{ old('content', $announcement->content) }}</textarea>
                </div>

                <!-- Email Notification -->
                <div class="flex items-center">
                    <input type="checkbox" id="send_email" name="send_email" value="1" 
                           {{ (old('send_email', $announcement->send_email) ? 'checked' : '') }}
                           {{ $announcement->sent_at ? 'disabled' : '' }}
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="send_email" class="ml-2 block text-sm text-gray-700">
                        <i class="fas fa-envelope mr-1"></i>
                        Send email notification to enrolled students
                        @if($announcement->sent_at)
                            <span class="text-green-600 text-xs">(Already sent on {{ $announcement->sent_at->format('M d, Y h:i A') }})</span>
                        @endif
                    </label>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-between items-center pt-6 border-t border-gray-200 mt-6">
                <a href="{{ route('instructor.announcements.show', $announcement) }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-times mr-2"></i>
                    Cancel
                </a>
                
                <button type="submit" 
                        class="inline-flex items-center px-6 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-save mr-2"></i>
                    Update Announcement
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
