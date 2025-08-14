@extends('layouts.dashboard')

@section('title', 'Announcement Details')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $announcement->title }}</h1>
                    <div class="flex items-center mt-2 space-x-4 text-sm text-gray-600">
                        <div class="flex items-center">
                            <i class="fas fa-user mr-1"></i>
                            {{ $announcement->instructor->first_name }} {{ $announcement->instructor->last_name }}
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-calendar mr-1"></i>
                            {{ $announcement->created_at->format('M d, Y') }}
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-clock mr-1"></i>
                            {{ $announcement->created_at->format('h:i A') }}
                        </div>
                        @if($announcement->course)
                        <div class="flex items-center">
                            <i class="fas fa-book mr-1"></i>
                            {{ $announcement->course->title }}
                        </div>
                        @endif
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <!-- Priority Badge -->
                    @php
                        $priorityColors = [
                            'high' => 'bg-red-100 text-red-800',
                            'medium' => 'bg-yellow-100 text-yellow-800',
                            'low' => 'bg-green-100 text-green-800'
                        ];
                        $priorityColor = $priorityColors[$announcement->priority] ?? 'bg-gray-100 text-gray-800';
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $priorityColor }}">
                        <i class="fas fa-flag mr-1"></i>
                        {{ ucfirst($announcement->priority) }} Priority
                    </span>
                    
                    @if(Auth::user()->role === 'instructor' && $announcement->instructor_id === Auth::id())
                    <!-- Edit Button for Instructor -->
                    <a href="{{ route('instructor.announcements.edit', $announcement) }}" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <i class="fas fa-edit mr-1"></i>
                        Edit
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-6">
            <div class="prose max-w-none">
                {!! nl2br(e($announcement->content)) !!}
            </div>
        </div>
    </div>

    <!-- Back Button -->
    <div class="mt-6 flex justify-between">
        <a href="{{ Auth::user()->role === 'instructor' ? route('instructor.announcements.index') : route('student.announcements.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
            <i class="fas fa-arrow-left mr-2"></i>
            Back to Announcements
        </a>
        
        @if(Auth::user()->role === 'instructor' && $announcement->instructor_id === Auth::id())
        <!-- Delete Button -->
        <form action="{{ route('instructor.announcements.destroy', $announcement) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this announcement?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md text-sm font-medium text-red-700 bg-white hover:bg-red-50">
                <i class="fas fa-trash mr-2"></i>
                Delete
            </button>
        </form>
        @endif
    </div>
</div>
@endsection
