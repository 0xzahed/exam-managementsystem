@extends('layouts.dashboard')
@section('title', 'Course Enrollment')

@section('styles')
<style>
    .course-card { transition: all 0.3s ease; }
    .course-card:hover { transform: translateY(-4px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
    .filter-tab { transition: all 0.3s ease; }
    .filter-tab.active { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; }
    .fade-in { animation: fadeIn .5s ease-in; }
    @keyframes fadeIn { from {opacity:0; transform: translateY(20px);} to {opacity:1; transform: translateY(0);} }
    .seat-indicator { display:inline-block; width:12px; height:12px; border-radius:50%; margin-right:8px; }
    .seat-available { background:#10b981; }
    .seat-limited { background:#f59e0b; }
    .seat-full { background:#ef4444; }
</style>
@endsection

@section('content')
@php
    // Build details map for JS (keyed by id)
    $courseDetailsData = ($availableCourses ?? collect())->keyBy('id')->map(function($c){
        return [
            'title' => $c->title,
            'course_code' => $c->course_code ?? $c->code ?? $c->title,
            'credits' => $c->credits ?? 0,
            'instructor_name' => $c->instructor_name ?? '',
            'description' => $c->description ?? '',
            'schedule' => $c->schedule ?? '',
            'room' => $c->room ?? '',
            'prerequisites' => $c->prerequisites ?? 'None',
            'max_capacity' => $c->max_capacity ?? ($c->capacity ?? 'Unknown'),
        ];
    })->toArray();
@endphp
<script id="courseDetailsData" type="application/json">@json($courseDetailsData)</script>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" id="enrollmentPageRoot">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-6 rounded-xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm">Enrolled Courses</p>
                    <p class="text-3xl font-bold">{{ $stats['enrolled_courses'] ?? 0 }}</p>
                </div>
                <i class="fas fa-book-open text-3xl text-blue-200"></i>
            </div>
        </div>
        <div class="bg-gradient-to-r from-green-500 to-green-600 text-white p-6 rounded-xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm">Total Credits</p>
                    <p class="text-3xl font-bold">{{ $stats['total_credits'] ?? 0 }}</p>
                </div>
                <i class="fas fa-calculator text-3xl text-green-200"></i>
            </div>
        </div>
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white p-6 rounded-xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm">Available Courses</p>
                    <p class="text-3xl font-bold">{{ $stats['available_courses'] ?? 0 }}</p>
                </div>
                <i class="fas fa-list text-3xl text-purple-200"></i>
            </div>
        </div>
        <div class="bg-gradient-to-r from-orange-500 to-orange-600 text-white p-6 rounded-xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm">Total Courses</p>
                    <p class="text-3xl font-bold">{{ $stats['total_courses'] ?? 0 }}</p>
                </div>
                <i class="fas fa-book text-3xl text-orange-200"></i>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Filters Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 sticky top-24">
                <!-- Search -->
                <div class="p-6 border-b border-gray-200">
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input id="searchInput" type="text" placeholder="Search courses..." class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
                <!-- Status Filters -->
                <div class="p-6 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800 mb-4">Course Status</h3>
                    <div class="space-y-2">
                        <button onclick="filterCourses('all')" class="filter-tab active w-full text-left px-4 py-2 rounded-lg">All Courses</button>
                        <button onclick="filterCourses('available')" class="filter-tab w-full text-left px-4 py-2 rounded-lg text-gray-600 hover:bg-gray-100">Available</button>
                        <button onclick="filterCourses('limited')" class="filter-tab w-full text-left px-4 py-2 rounded-lg text-gray-600 hover:bg-gray-100">Limited Seats</button>
                        <button onclick="filterCourses('full')" class="filter-tab w-full text-left px-4 py-2 rounded-lg text-gray-600 hover:bg-gray-100">Full</button>
                    </div>
                </div>
                <!-- Department Filter -->
                <div class="p-6 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800 mb-4">Department</h3>
                    <select id="departmentFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Departments</option>
                        <option value="CSE">Computer Science</option>
                        <option value="EEE">Electrical Engineering</option>
                        <option value="BBA">Business Administration</option>
                        <option value="ENG">English</option>
                        <option value="MATH">Mathematics</option>
                    </select>
                </div>
                <!-- Credit Filter -->
                <div class="p-6">
                    <h3 class="font-semibold text-gray-800 mb-4">Credits</h3>
                    <div class="space-y-2 text-sm">
                        @foreach([1,2,3,4] as $cr)
                            <label class="flex items-center"><input type="checkbox" class="credit-filter mr-2" value="{{ $cr }}"><span class="text-gray-700">{{ $cr }} Credit{{ $cr>1?'s':'' }}</span></label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Course List -->
        <div class="lg:col-span-3">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Available Courses</h2>
                <div>
                    <select class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option>Sort by Name</option>
                        <option>Sort by Credits</option>
                        <option>Sort by Availability</option>
                    </select>
                </div>
            </div>

            <div id="courseContainer" class="space-y-6">
                @forelse(($availableCourses ?? []) as $course)
                    <div class="course-card bg-white rounded-xl shadow-sm border border-gray-200 p-6"
                        data-status="available"
                        data-department="{{ $course->department }}"
                        data-credits="{{ $course->credits }}"
                        data-has-password="{{ ($course->has_password ?? (!empty($course->password))) ? 'true':'false' }}">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex-1">
                                <div class="flex items-center mb-2">
                                    <h3 class="text-xl font-semibold text-gray-800">{{ $course->title }}</h3>
                                    @php $seats=$course->available_seats; @endphp
                                    @if($seats > 10)
                                        <span class="ml-3 px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full"><span class="seat-indicator seat-available"></span>Available</span>
                                    @elseif($seats > 0)
                                        <span class="ml-3 px-2 py-1 bg-orange-100 text-orange-800 text-xs font-medium rounded-full"><span class="seat-indicator seat-limited"></span>Limited</span>
                                    @else
                                        <span class="ml-3 px-2 py-1 bg-red-100 text-red-800 text-xs font-medium rounded-full"><span class="seat-indicator seat-full"></span>Full</span>
                                    @endif
                                </div>
                                <p class="text-gray-600 mb-2">{{ $course->course_code }} • {{ $course->credits }} Credits • {{ $course->instructor_name ?? 'Instructor TBA' }}</p>
                                <p class="text-gray-500 text-sm">{{ Str::limit($course->description,120) }}</p>
                            </div>
                            <div class="text-right ml-6">
                                <div class="text-2xl font-bold {{ $seats>10 ? 'text-indigo-600' : ($seats>0 ? 'text-orange-600':'text-red-600') }}">{{ $seats }}</div>
                                <div class="text-xs text-gray-500">seats left</div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4 text-sm">
                            <div><span class="text-gray-500">Schedule:</span><p class="font-medium">{{ $course->schedule ?? 'TBA' }}</p></div>
                            <div><span class="text-gray-500">Room:</span><p class="font-medium">{{ $course->room ?? 'TBA' }}</p></div>
                            <div><span class="text-gray-500">Prerequisites:</span><p class="font-medium">{{ $course->prerequisites ?? 'None' }}</p></div>
                            <div><span class="text-gray-500">Capacity:</span><p class="font-medium">{{ $course->max_capacity }} students</p></div>
                        </div>
                        <div class="flex justify-between items-center">
                            <div class="flex items-center space-x-4 text-sm">
                                <button onclick="showCourseDetails('{{ $course->id }}')" class="text-indigo-600 hover:text-indigo-700 font-medium"><i class="fas fa-info-circle mr-1"></i>Details</button>
                                <button class="text-gray-600 hover:text-gray-700"><i class="fas fa-heart mr-1"></i>Wishlist</button>
                            </div>
                            <button onclick="enrollCourse('{{ $course->id }}')" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium"><i class="fas fa-plus mr-2"></i>Enroll</button>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4"><i class="fas fa-book text-gray-400 text-2xl"></i></div>
                        <h3 class="text-lg font-medium text-gray-800 mb-2">No Courses Available</h3>
                        <p class="text-gray-500">There are currently no courses available for enrollment.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Course Details Modal -->
<div id="courseModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto border border-gray-300 shadow-2xl">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-semibold text-gray-800">Course Details</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
            </div>
        </div>
        <div id="modalContent" class="p-6"><!-- populated by JS --></div>
    </div>
</div>

<!-- Enrollment Confirmation Modal -->
<div id="enrollModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-xl max-w-md w-full p-6 border border-gray-300 shadow-2xl">
        <div class="text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4"><i class="fas fa-check text-green-600 text-2xl"></i></div>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Confirm Enrollment</h3>
            <p id="enrollMessage" class="text-gray-600 mb-4"></p>
            <div id="passwordSection" class="mb-6 hidden">
                <label class="block text-sm font-medium text-gray-700 mb-2">Course Password</label>
                <input id="coursePassword" type="password" placeholder="Enter course password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                <p class="text-sm text-gray-500 mt-1">This course requires a password to enroll</p>
            </div>
            <div class="flex space-x-3">
                <button onclick="closeEnrollModal()" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Cancel</button>
                <button onclick="confirmEnrollment()" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Confirm</button>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-xl max-w-md w-full p-6 border border-gray-300 shadow-2xl">
        <div class="text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4"><i class="fas fa-check text-green-600 text-2xl"></i></div>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Enrollment Successful!</h3>
            <p id="successMessage" class="text-gray-600 mb-6"></p>
            <button onclick="redirectToMyCourses()" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 w-full">Go to My Courses</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@vite('resources/js/pages/courses-enrollment.js')
@endsection