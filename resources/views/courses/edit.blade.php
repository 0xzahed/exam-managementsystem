@extends('layouts.dashboard')
@section('title','Edit Course')
@section('content')
<div class="container mx-auto px-6 py-8">
  <div id="editCourseCard" class="w-full max-w-4xl mx-auto bg-white rounded-2xl shadow-xl border border-gray-200 animate-fade-in">
      <div class="bg-gradient-to-r from-yellow-600 to-orange-600 px-6 py-5 flex items-center justify-between rounded-t-2xl">
          <h3 class="text-xl font-semibold text-white flex items-center"><i class="fas fa-edit mr-2"></i>Edit Course</h3>
      </div>
      <form action="{{ route('courses.update', $course) }}" method="POST" class="p-6 space-y-8">
          @csrf
          @method('PUT')
          
          <!-- Course Title and Code -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Course Title/Name <span class="text-red-500">*</span></label>
                  <input type="text" name="title" value="{{ old('title', $course->title) }}" placeholder="e.g., Data Structures & Algorithms" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 bg-gray-50" required>
              </div>
              <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Course Code <span class="text-red-500">*</span></label>
                  <input type="text" name="code" value="{{ old('code', $course->code) }}" placeholder="e.g., CSE101, ENG201" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 bg-gray-50" required>
              </div>
          </div>

          <!-- Course Description -->
          <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Course Description <span class="text-red-500">*</span></label>
              <textarea name="description" id="description" rows="8" placeholder="Describe the course content, objectives, and learning outcomes..." class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 bg-gray-50" required>{{ old('description', $course->description) }}</textarea>
          </div>

          <!-- Credits and Department -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Credits/Credit Hours <span class="text-red-500">*</span></label>
                  <select name="credits" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 bg-gray-50" required>
                      <option value="">Select Credits</option>
                      <option value="1" {{ old('credits', $course->credits) == '1' ? 'selected' : '' }}>1 Credit</option>
                      <option value="2" {{ old('credits', $course->credits) == '2' ? 'selected' : '' }}>2 Credits</option>
                      <option value="3" {{ old('credits', $course->credits) == '3' ? 'selected' : '' }}>3 Credits</option>
                      <option value="4" {{ old('credits', $course->credits) == '4' ? 'selected' : '' }}>4 Credits</option>
                  </select>
              </div>
              <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Department <span class="text-red-500">*</span></label>
                  <select name="department" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 bg-gray-50" required>
                      <option value="">Select Department</option>
                      <option value="CSE" {{ old('department', $course->department) == 'CSE' ? 'selected' : '' }}>Computer Science & Engineering</option>
                      <option value="SWE" {{ old('department', $course->department) == 'SWE' ? 'selected' : '' }}>Software Engineering</option>
                  </select>
              </div>
          </div>

          <!-- Semester -->
          <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Semester <span class="text-red-500">*</span></label>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <select name="semester_type" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 bg-gray-50" required>
                      <option value="">Select Semester</option>
                      <option value="Spring" {{ old('semester_type', $course->semester_type) == 'Spring' ? 'selected' : '' }}>Spring</option>
                      <option value="Summer" {{ old('semester_type', $course->semester_type) == 'Summer' ? 'selected' : '' }}>Summer</option>
                      <option value="Fall" {{ old('semester_type', $course->semester_type) == 'Fall' ? 'selected' : '' }}>Fall</option>
                  </select>
                  <select name="year" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 bg-gray-50" required>
                      <option value="">Select Year</option>
                      <option value="2025" {{ old('year', $course->year) == '2025' ? 'selected' : '' }}>2025</option>
                      <option value="2026" {{ old('year', $course->year) == '2026' ? 'selected' : '' }}>2026</option>
                  </select>
              </div>
          </div>

          <!-- Maximum Students and Prerequisites -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Maximum Students Limit <span class="text-red-500">*</span></label>
                  <input type="number" name="max_students" value="{{ old('max_students', $course->max_students) }}" placeholder="50" min="1" max="200" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 bg-gray-50" required>
              </div>
              <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Prerequisites</label>
                  <input type="text" name="prerequisites" value="{{ old('prerequisites', $course->prerequisites) }}" placeholder="e.g., CSE101, MATH201" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 bg-gray-50">
                  <p class="text-xs text-gray-500 mt-1">Comma separated course codes (optional)</p>
              </div>
          </div>

          <!-- Course Password -->
          <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Course Password <span class="text-red-500">*</span></label>
              <input type="text" name="password" value="{{ old('password', $course->password) }}" placeholder="Enter enrollment password" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 bg-gray-50" required>
              <p class="text-xs text-gray-500 mt-1">Students will need this password to enroll in the course</p>
          </div>

          <!-- Submit Buttons -->
          <div class="flex justify-end space-x-3 pt-6 border-t border-gray-100">
              <a href="{{ route('courses.manage') }}" class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium flex items-center"><i class="fas fa-times mr-2"></i>Cancel</a>
              <button type="submit" class="px-6 py-2.5 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 text-sm font-medium shadow-sm flex items-center"><i class="fas fa-save mr-2"></i>Update Course</button>
          </div>
      </form>
  </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.tiny.cloud/1/4gukmwnqwk4bolj1vsoqqsxtiqtz8984n4baxsqeratjgw5g/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
<script src="{{ asset('js/tinymce-config.js') }}"></script>
@endsection
