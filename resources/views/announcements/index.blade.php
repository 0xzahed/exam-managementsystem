@extends('layouts.dashboard')

@section('title', 'Announcements')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-7xl">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">📢 Announcements</h1>
                <p class="text-gray-600 mt-1">Stay updated with course announcements</p>
            </div>
            @if(auth()->user()->role === 'instructor')
                <a href="{{ route('instructor.announcements.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-2 whitespace-nowrap">
                    <i class="fas fa-plus"></i>
                    New Announcement
                </a>
            @endif
        </div>
    </div>

    <!-- Announcements List -->
    <div class="space-y-4">
        @forelse($announcements as $announcement)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
                <div class="flex flex-col lg:flex-row items-start justify-between gap-4">
                    <div class="flex-1 w-full lg:w-auto">
                        <!-- Course Badge -->
                        <div class="flex flex-wrap items-center mb-3 gap-2">
                            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-1 rounded-full">
                                {{ $announcement->course->title }}
                            </span>
                            @if(auth()->user()->role === 'instructor' && $announcement->instructor_id === auth()->id())
                                <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-1 rounded-full">
                                    <i class="fas fa-lock mr-1"></i>Private to You
                                </span>
                            @endif
                            @if($announcement->priority === 'high')
                                <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-1 rounded-full">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>High Priority
                                </span>
                            @elseif($announcement->priority === 'medium')
                                <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-1 rounded-full">
                                    <i class="fas fa-exclamation-circle mr-1"></i>Medium Priority
                                </span>
                            @endif
                        </div>

                        <!-- Title -->
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $announcement->title }}</h3>

                        <!-- Content -->
                        <div class="text-gray-600 mb-4">
                            <div class="break-words">
                                {!! Str::limit(strip_tags($announcement->content), 200) !!}
                            </div>
                            @if(strlen(strip_tags($announcement->content)) > 200)
                                <a href="{{ route('instructor.announcements.show', $announcement) }}" class="text-blue-600 hover:text-blue-800 text-sm mt-1">Read more...</a>
                            @endif
                        </div>

                        <!-- Meta Info -->
                        <div class="flex flex-wrap items-center text-sm text-gray-500 gap-2 sm:gap-4">
                            <div class="flex items-center">
                                <i class="fas fa-user mr-1"></i>
                                <span class="truncate">{{ $announcement->instructor->first_name }} {{ $announcement->instructor->last_name }}</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-clock mr-1"></i>
                                {{ $announcement->created_at->diffForHumans() }}
                            </div>
                            @if($announcement->created_at->format('Y-m-d') !== $announcement->updated_at->format('Y-m-d'))
                                <div class="flex items-center">
                                    <i class="fas fa-edit mr-1"></i>
                                    Updated {{ $announcement->updated_at->diffForHumans() }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Actions for Instructors -->
                    @if(auth()->user()->role === 'instructor' && $announcement->instructor_id === auth()->user()->id)
                        <div class="w-full lg:w-auto">
                            <div class="flex space-x-2 justify-end">
                                <a href="{{ route('instructor.announcements.edit', $announcement) }}" class="text-blue-600 hover:text-blue-800 p-2 rounded-lg hover:bg-blue-50 transition-colors">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('instructor.announcements.destroy', $announcement) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this announcement?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 p-2 rounded-lg hover:bg-red-50 transition-colors">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <!-- Empty State -->
            <div class="text-center py-12">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                    <i class="fas fa-bullhorn text-2xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No announcements yet</h3>
                <p class="text-gray-500 mb-4">
                    @if(auth()->user()->role === 'instructor')
                        You haven't created any announcements yet.
                    @else
                        No announcements from your enrolled courses.
                    @endif
                </p>
                @if(auth()->user()->role === 'instructor')
                    <a href="{{ route('instructor.announcements.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                        <i class="fas fa-plus mr-2"></i>
                        Create First Announcement
                    </a>
                @endif
            </div>
        @endforelse
    </div>
</div>
@endsection

@section('scripts')
    @vite('resources/js/pages/announcements-index.js')
@endsection
