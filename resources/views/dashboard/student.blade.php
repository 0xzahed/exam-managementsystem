@extends('layouts.dashboard')
@section('title','Student Dashboard')
@section('page-title', 'Student Dashboard')

@section('content')
<div class="px-0 pt-2 md:pt-0">
    <div class="py-2 md:py-4">
                <!-- Welcome Section -->
                <div class="student-header bg-gradient-to-r from-indigo-600 bg-purple-600 to-pink-600 rounded-2xl px-8 py-6 mb-6 text-white shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-3xl font-bold mb-2 text-white">Welcome back, {{ $user->first_name }}! 👋</h2>
                            <p class="text-white/90">You have {{ $pendingAssignments }} assignment{{ $pendingAssignments == 1 ? '' : 's' }} due this week and {{ $upcomingExams }} upcoming exam{{ $upcomingExams == 1 ? '' : 's' }}.</p>
                            
                            <!-- User Status Display -->
                            <div class="mt-3 flex items-center space-x-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                    @if($user->status === 'active') bg-green-100 text-green-800 @endif
                                    @if($user->status === 'inactive') bg-gray-100 text-gray-800 @endif  
                                    @if($user->status === 'suspended') bg-red-100 text-red-800 @endif">
                                    <i class="fas fa-circle text-xs mr-2"></i>
                                    {{ ucfirst($user->status) }}
                                </span>
                                @if($user->last_login_at)
                                <span class="text-white/80 text-xs">
                                    <i class="fas fa-clock mr-1"></i>
                                    Last login: {{ $user->last_login_at->diffForHumans() }}
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="hidden md:block">
                            <div class="text-right">
                                <p class="text-white/80 text-sm">Current Semester</p>
                                <p class="text-xl font-semibold text-white">Summer 2025</p>
                            </div>
                        </div>
                    </div>
                </div>

                

                <!-- Quick Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <a href="{{ route('student.courses.my') }}" class="bg-white rounded-xl p-6 shadow-sm border border-gray-200 card-hover hover:shadow-md ">
                        <div class="flex items-center">
                            <div class="p-3 bg-blue-100 rounded-lg">
                                <i class="fas fa-book text-blue-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-600">Total Courses</p>
                                <p class="text-2xl font-bold text-gray-800">{{ $totalEnrolledCourses }}</p>
                            </div>
                        </div>
                    </a>
                    <a href="{{ route('assignments.index') }}" class="bg-white rounded-xl p-6 shadow-sm border border-gray-200 card-hover hover:shadow-md ">
                        <div class="flex items-center">
                            <div class="p-3 bg-orange-100 rounded-lg">
                                <i class="fas fa-tasks text-orange-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-600">Pending Assignments</p>
                                <p class="text-2xl font-bold text-gray-800">{{ $pendingAssignments }}</p>
                            </div>
                        </div>
                    </a>
                    <a href="{{ route('student.grades.index') }}" class="bg-white rounded-xl p-6 shadow-sm border border-gray-200 card-hover hover:shadow-md ">
                        <div class="flex items-center">
                            <div class="p-3 bg-green-100 rounded-lg">
                                <i class="fas fa-chart-line text-green-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-600">Average Grade</p>
                                <p class="text-2xl font-bold text-gray-800">{{ $averageGrade ? $averageGrade . '%' : 'N/A' }}</p>
                            </div>
                        </div>
                    </a>
                    <a href="{{ route('student.exams.index') }}" class="bg-white rounded-xl p-6 shadow-sm border border-gray-200 card-hover hover:shadow-md ">
                        <div class="flex items-center">
                            <div class="p-3 bg-purple-100 rounded-lg">
                                <i class="fas fa-clock text-purple-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-600">Upcoming Exams</p>
                                <p class="text-2xl font-bold text-gray-800">{{ $upcomingExams }}</p>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Main Dashboard Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left Column -->
                    <div class="lg:col-span-2 space-y-8">
                        <!-- My Courses -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                            <div class="p-6 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-gray-800">My Courses ({{ $totalEnrolledCourses }})</h3>
    
                                </div>
                            </div>
                            <div class="p-6">
                                @forelse($enrolledCourses as $course)
                                    <div class="flex items-center justify-between p-4 border border-gray-200 bg-gray-50 rounded-lg mb-3 hover:bg-gray-100 ">
                                        <div class="flex items-center">
                                            <div class="w-12 h-12 bg-blue-500 text-white rounded-lg flex items-center justify-center mr-4">
                                                <i class="fas fa-book text-lg"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-semibold text-gray-800">{{ $course->title }}</h4>
                                                <p class="text-sm text-gray-600">{{ $course->code }} • {{ $course->instructor->first_name ?? '' }} {{ $course->instructor->last_name ?? '' }}</p>
                                                <p class="text-xs text-gray-500">Enrolled: {{ $course->pivot->enrolled_at ? \Carbon\Carbon::parse($course->pivot->enrolled_at)->format('M d, Y') : 'N/A' }}</p>
                                            </div>
                                        </div>
 
                                    </div>
                                @empty
                                    <div class="text-center py-8">
                                        <i class="fas fa-book text-gray-400 text-3xl mb-3"></i>
                                        <p class="text-gray-500 text-sm mb-2">No courses enrolled yet</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Upcoming Assignments -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                            <div class="p-6 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-gray-800">Upcoming Assignments</h3>
                                    <a href="{{ route('assignments.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">

                                    </a>
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    @forelse($assignments as $assignment)
                                    <div class="flex items-center justify-between p-4 border border-red-200 bg-red-50 rounded-lg mb-2 hover:bg-red-100 ">
                                        <div class="flex items-center">
                                            <div class="w-3 h-3 bg-red-500 rounded-full mr-4"></div>
                                            <div>
                                                <h4 class="font-medium text-gray-800">{{ $assignment->title }}</h4>
                                                <p class="text-sm text-gray-600">{{ $assignment->course->title ?? '' }} • Due {{ \Carbon\Carbon::parse($assignment->due_date)->format('M d, Y') }}</p>
                                            </div>
                                        </div>
                                        <a href="{{ route('assignments.show', $assignment->id) }}" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium ">
                                            Submit
                                        </a>
                                    </div>
                                    @empty
                                    <div class="text-center py-4">
                                        <i class="fas fa-check-circle text-green-500 text-2xl mb-2"></i>
                                        <p class="text-gray-500 text-sm">No upcoming assignments.</p>
                                        <p class="text-gray-400 text-xs mt-1">You're all caught up!</p>
                                    </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activities -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                            <div class="p-6 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-800">Recent Activities</h3>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    @forelse($recentActivities as $activity)
                                    <div class="flex items-center space-x-3">
                                        <div class="flex items-center justify-center w-8 h-8 bg-gray-100 rounded-full">
                                            <i class="text-xs {{ $activity->color ?? 'text-gray-600' }} {{ $activity->icon ?? 'fas fa-circle' }}"></i>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-sm text-gray-800">{{ $activity->message ?? $activity->description }}</p>
                                            <p class="text-xs text-gray-500">{{ $activity->time ?? $activity->time_ago }}</p>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="text-center py-4">
                                        <i class="fas fa-clock text-gray-400 text-2xl mb-2"></i>
                                        <p class="text-gray-500 text-sm">No recent activities.</p>
                                        <p class="text-gray-400 text-xs mt-1">Your activities will appear here.</p>
                                    </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-8">
                        <!-- Recent Announcements -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                            <div class="p-6 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-gray-800">Recent Announcements</h3>
                                    <a href="{{ route('student.announcements.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">

                                    </a>
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    @forelse($announcements as $announcement)
                                    <a href="{{ route('student.announcements.show', $announcement->id) }}" class="block">
                                        <div class="border-l-4 border-indigo-500 pl-4 hover:bg-gray-50 rounded-r ">
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    <h4 class="font-medium text-gray-800 text-sm">{{ $announcement->title }}</h4>
                                                    <p class="text-xs text-gray-600 mt-1">{{ $announcement->course->title ?? 'General' }}</p>
                                                    <p class="text-xs text-gray-500 mt-1">{{ $announcement->created_at->diffForHumans() }}</p>
                                                    @if($announcement->content)
                                                    <p class="text-xs text-gray-600 mt-2 line-clamp-2">{{ Str::limit($announcement->content, 100) }}</p>
                                                    @endif
                                                </div>
                                                <div class="text-indigo-600 hover:text-indigo-800 text-xs ml-4">
                                                    <i class="fas fa-chevron-right"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                    @empty
                                    <div class="text-center py-4">
                                        <i class="fas fa-bullhorn text-gray-400 text-2xl mb-2"></i>
                                        <p class="text-gray-500 text-sm">No recent announcements.</p>
                                        <p class="text-gray-400 text-xs mt-1">Check back later for updates from your instructors.</p>
                                    </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <!-- Upcoming Exams -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                            <div class="p-6 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-gray-800">Upcoming Exams</h3>
                                    <a href="{{ route('student.exams.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">

                                    </a>
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    @forelse($exams as $exam)
                                    <div class="p-4 bg-gradient-to-r {{ $exam['bg_from'] }} {{ $exam['bg_to'] }} rounded-lg border {{ $exam['border'] }}">
                                        <div class="flex items-center justify-between mb-2">
                                            <h4 class="font-medium text-gray-800">{{ $exam['title'] }}</h4>
                                            <span class="text-xs {{ $exam['badge_bg'] }} text-white px-2 py-1 rounded-full">{{ $exam['badge'] }}</span>
                                        </div>
                                        <p class="text-sm text-gray-600 mb-3">Duration: {{ $exam['duration'] }} • {{ $exam['type'] }}</p>
                                        @if(isset($exam['route']))
                                        <a href="{{ $exam['route'] }}" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 text-sm font-medium ">
                                            @if($exam['can_take'])
                                                Take Exam
                                            @else
                                                View Result
                                            @endif
                                        </a>
                                        @else
                                        <button class="w-full bg-gray-600 text-white py-2 rounded-lg text-sm font-medium cursor-not-allowed">
                                            Review Materials
                                        </button>
                                        @endif
                                    </div>
                                    @empty
                                    <div class="text-center py-4">
                                        <i class="fas fa-clipboard-check text-gray-400 text-2xl mb-2"></i>
                                        <p class="text-gray-500 text-sm">No upcoming exams.</p>
                                        <p class="text-gray-400 text-xs mt-1">Check back later for exam schedules.</p>
                                    </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <!-- Recent Grades -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                            <div class="p-6 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-gray-800">Recent Grades</h3>
                                    <a href="{{ route('student.grades.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    </a>
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="space-y-3">
                                    @forelse($grades as $grade)
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-gray-800">{{ $grade['title'] }}</p>
                                            <p class="text-xs text-gray-600">{{ $grade['course'] }} • {{ $grade['type'] ?? 'Assignment' }}</p>
                                            <p class="text-xs text-gray-500">{{ $grade['date'] }}</p>
                                        </div>
                                        <span class="text-lg font-bold {{ $grade['color'] }}">{{ $grade['percentage'] }}</span>
                                    </div>
                                    @empty
                                    <div class="text-center py-4">
                                        <i class="fas fa-chart-bar text-gray-400 text-2xl mb-2"></i>
                                        <p class="text-gray-500 text-sm">No recent grades.</p>
                                        <p class="text-gray-400 text-xs mt-1">Your grades will appear here once graded.</p>
                                    </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</div>
@endsection
