@extends('layouts.dashboard')

@section('title', 'Create Announcement')

@section('head')
<!-- TinyMCE -->
<script src="https://cdn.tiny.cloud/1/4gukmwnqwk4bolj1vsoqqsxtiqtz8984n4baxsqeratjgw5g/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
@endsection

@section('content')
<div class="container mx-auto px-4 py-6 max-w-4xl">
	<!-- Page Header -->
	<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
		<div class="flex items-center justify-between">
			<div>
				<h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Create Announcement</h1>
				<p class="text-gray-600">Share important updates with your students</p>
			</div>
			                <a href="{{ route('instructor.announcements.index') }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Back</a>
		</div>
	</div>

	<!-- Form Card -->
	<div class="bg-white rounded-xl shadow-sm border border-gray-200">
		                <form method="POST" action="{{ route('instructor.announcements.store') }}" class="p-6 sm:p-8">
			@csrf

			<!-- Title -->
			<div class="mb-6">
				<label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title <span class="text-red-500">*</span></label>
				<input type="text" id="title" name="title" value="{{ old('title') }}" required
					class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
					placeholder="e.g., Quiz tomorrow, Assignment deadline extended...">
				@error('title')
				<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
				@enderror
			</div>

			<!-- Course Selection -->
			<div class="mb-6">
				<label for="course_id" class="block text-sm font-medium text-gray-700 mb-2">Course <span class="text-red-500">*</span></label>
				<select id="course_id" name="course_id" required
					class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
					@if($courses->isEmpty()) disabled @endif>
					<option value="">Select a course...</option>
					@foreach($courses as $course)
					<option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
						{{ $course->title }}
					</option>
					@endforeach
				</select>
				@if($courses->isEmpty())
				<p class="mt-2 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded p-2">
					You don't have any courses yet. Create a course first.
				</p>
				@endif
				@error('course_id')
				<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
				@enderror
			</div>

			<!-- Priority Selection -->
			<div class="mb-6">
				<label for="priority" class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
				<select id="priority" name="priority"
					class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
					<option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low Priority</option>
					<option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium Priority</option>
					<option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High Priority</option>
				</select>
				@error('priority')
				<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
				@enderror
			</div>

			<!-- Content -->
			<div class="mb-6">
				<label for="content" class="block text-sm font-medium text-gray-700 mb-2">Announcement Details <span class="text-red-500">*</span></label>
				<textarea id="content" name="content" rows="8" required class="tinymce w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
					placeholder="Enter the announcement details...">{{ old('content') }}</textarea>
				@error('content')
				<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
				@enderror
			</div>

			<!-- Email Notification Option -->
			<div class="mb-8">
				<div class="flex items-center">
					<input type="checkbox" id="send_email" name="send_email" value="1" 
						{{ old('send_email', '1') ? 'checked' : '' }}
						class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
					<label for="send_email" class="ml-2 block text-sm text-gray-700">
						Send email notifications to all enrolled students
					</label>
				</div>
				<p class="mt-1 text-xs text-gray-500">Students will receive an email with the announcement details in addition to the in-app notification.</p>
			</div>

			<div class="flex items-center justify-end gap-3">
				                <a href="{{ route('instructor.announcements.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Cancel</a>
				<button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
					<i class="fas fa-bullhorn mr-2"></i>
					Create Announcement
				</button>
			</div>
		</form>
	</div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.tiny.cloud/1/4gukmwnqwk4bolj1vsoqqsxtiqtz8984n4baxsqeratjgw5g/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
<script src="{{ asset('js/tinymce-config.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize TinyMCE for announcements
    TinyMCEConfigs.announcement();
});
</script>
@endsection
