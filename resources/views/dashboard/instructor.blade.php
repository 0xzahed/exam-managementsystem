@extends('layouts.dashboard')
@section('title','Instructor Dashboard')
@section('page-title', 'Instructor Dashboard')

@section('content')
<div class="px-0 pt-2 md:pt-0">
    <div class="py-2 md:py-4">
            <!-- Welcome Section -->
            <div class="p-8 mb-8 text-white rounded-2xl bg-blue-600">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="mb-2 text-3xl font-bold text-white">Good morning, {{ Auth::user()->first_name }} {{ Auth::user()->last_name }}, 👨‍🏫</h2>
                        <p class="text-white/90">You have {{ $pendingGrades ?? 0 }} assignments to grade and {{ $todayClasses ?? 0 }} classes scheduled today.</p>
                        
                        <!-- User Status Display -->
                        <div class="mt-3 flex items-center space-x-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                @if(Auth::user()->status === 'active') bg-white/20 text-white border border-white/30 @endif
                                @if(Auth::user()->status === 'inactive') bg-gray-100 text-gray-800 @endif  
                                @if(Auth::user()->status === 'suspended') bg-red-100 text-red-800 @endif">
                                <i class="fas fa-circle text-xs mr-2"></i>
                                {{ ucfirst(Auth::user()->status ?? 'active') }}
                            </span>
                            @if(Auth::user()->last_login_at)
                            <span class="text-white/80 text-xs">
                                <i class="fas fa-clock mr-1"></i>
                                Last active: {{ Auth::user()->last_login_at->diffForHumans() }}
                            </span>
                            @endif
                        </div>
                    </div>
                    <div class="hidden md:block">
                        <div class="text-right">
                            <p class="text-sm text-white/80">Current Semester</p>
                            <p class="text-xl font-semibold text-white">{{ date('F Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-4">
                <a href="{{ route('courses.manage') }}" class="p-6 bg-white border border-gray-200 shadow-sm rounded-xl card-hover hover:shadow-md ">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-lg">
                            <i class="text-xl text-blue-600 fas fa-chalkboard-teacher"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Active Courses</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $courses->count() ?? 0 }}</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('students.index') }}" class="p-6 bg-white border border-gray-200 shadow-sm rounded-xl card-hover hover:shadow-md ">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-lg">
                            <i class="text-xl text-green-600 fas fa-users"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Total Students</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $totalStudents ?? 0 }}</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('instructor.gradebook.index') }}" class="p-6 bg-white border border-gray-200 shadow-sm rounded-xl card-hover hover:shadow-md ">
                    <div class="flex items-center">
                        <div class="p-3 bg-orange-100 rounded-lg">
                            <i class="text-xl text-orange-600 fas fa-clipboard-check"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Pending Grades</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $pendingGrades ?? 0 }}</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('instructor.exams.index') }}" class="p-6 bg-white border border-gray-200 shadow-sm rounded-xl card-hover hover:shadow-md ">
                    <div class="flex items-center">
                        <div class="p-3 bg-purple-100 rounded-lg">
                            <i class="text-xl text-purple-600 fas fa-calendar-check"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Active Exams</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $activeExams ?? 0 }}</p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Main Dashboard Grid -->
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <!-- Left Column -->
                <div class="space-y-8 lg:col-span-2">
                    <!-- My Courses -->
                    <div class="bg-white border border-gray-200 shadow-sm rounded-xl">
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-800">My Courses</h3>
                                <div class="flex space-x-3">
                                    <a href="{{ route('courses.manage') }}" class="text-sm font-medium text-purple-600 hover:text-purple-700">View All</a>
                                </div>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                @if($courses && $courses->count() > 0)
                                @foreach($courses as $course)
                                <!-- Course {{ $loop->iteration }} -->
                                <a href="{{ route('courses.materials', $course->id) }}" class="block">
                                    <div class="flex items-center justify-between p-4 ">
                                        <div class="flex items-center">
                                            <div class="flex items-center justify-center w-12 h-12 {{ $loop->first ? 'bg-blue-600' : 'bg-green-600' }} rounded-lg">
                                                <i class="text-white {{ $loop->first ? 'fas fa-laptop-code' : 'fab fa-java' }}"></i>
                                            </div>
                                            <div class="ml-4">
                                                <h4 class="font-medium text-gray-800">{{ $course->title }}</h4>
                                                <p class="text-sm text-gray-600">{{ $course->code }} • {{ $course->students->count() ?? 0 }} students</p>
                                                <p class="text-xs text-gray-500">{{ $course->assignments->count() ?? 0 }} assignments • {{ $course->exams->count() ?? 0 }} exams</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="px-2 py-1 text-sm text-green-700 bg-green-100 rounded-full">Active</span>
                                            <p class="mt-1 text-xs text-gray-500">{{ $course->schedule ?? 'Schedule TBA' }}</p>
                                        </div>
                                    </div>
                                </a>
                                @endforeach
                                @else
                                <!-- Default courses if no data -->
                                <div class="p-4 text-center text-gray-500">
                                    <i class="mb-2 text-3xl text-gray-400 fas fa-chalkboard-teacher"></i>
                                    <p class="font-medium">No courses available</p>

                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Pending Assignments to Grade -->
                    <div class="bg-white border border-gray-200 shadow-sm rounded-xl">
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-800">Assignments to Grade</h3>
                                <a href="{{ route('instructor.gradebook.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">View All</a>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                @if($pendingAssignments && $pendingAssignments->count() > 0)
                                @foreach($pendingAssignments as $assignment)
                                <div class="flex items-center justify-between p-4 border border-orange-200 rounded-lg bg-orange-50 hover:bg-orange-100 ">
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 mr-4 bg-orange-500 rounded-full"></div>
                                        <div>
                                            <h4 class="font-medium text-gray-800">{{ $assignment->title }} - Marks Pending</h4>
                                            <p class="text-sm text-gray-600">{{ $assignment->course->title }} • {{ $assignment->submissions()->whereNull('grade')->count() }} submissions pending</p>
                                        </div>
                                    </div>
                                    <a href="{{ route('instructor.assignments.submissions', $assignment->id) }}"
                                        class="px-4 py-2 text-sm font-medium text-white bg-orange-600 rounded-lg hover:bg-orange-700 ">
                                        Grade Now
                                    </a>
                                </div>
                                @endforeach
                                @else
                                <div class="p-4 text-center text-gray-500">
                                    <i class="mb-2 text-3xl text-green-500 fas fa-check-circle"></i>
                                    <p class="font-medium">All assignments are graded!</p>
                                    <p class="text-sm">No pending grading tasks at the moment.</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activities -->
                    <div class="bg-white border border-gray-200 shadow-sm rounded-xl">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">Recent Activities</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                @if($recentActivities && $recentActivities->count() > 0)
                                @foreach($recentActivities as $activity)
                                <div class="flex items-center space-x-3">
                                    <div class="flex items-center justify-center w-8 h-8 bg-gray-100 rounded-full">
                                        <i class="text-xs {{ $activity->color ?? 'text-gray-600' }} {{ $activity->icon ?? 'fas fa-circle' }}"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm text-gray-800">{{ $activity->description }}</p>
                                        <p class="text-xs text-gray-500">{{ $activity->time_ago }}</p>
                                    </div>
                                </div>
                                @endforeach
                                @else
                                <!-- Default activities if no data -->
                                <div class="flex items-center space-x-3">
                                    <div class="flex items-center justify-center w-8 h-8 bg-blue-100 rounded-full">
                                        <i class="text-xs text-blue-600 fas fa-upload"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm text-gray-800">No recent activity</p>
                                        <p class="text-xs text-gray-500">Student activities will appear here</p>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-8">
                    <!-- Today's Schedule -->
                    <div class="bg-white border border-gray-200 shadow-sm rounded-xl">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">Today's Schedule</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                @if($todaySchedule && $todaySchedule->count() > 0)
                                @foreach($todaySchedule as $schedule)
                                <div class="flex items-center p-3 border-l-4 {{ $loop->first ? 'border-blue-500 bg-blue-50' : 'border-green-500 bg-green-50' }} rounded-lg">
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-gray-800">{{ $schedule->course_name }}</h4>
                                        <p class="text-xs text-gray-600">{{ $schedule->location }} • {{ $schedule->time_range }}</p>
                                    </div>
                                    <span class="px-2 py-1 text-xs text-white {{ $loop->first ? 'bg-blue-600' : 'bg-green-600' }} rounded-full">
                                        {{ $loop->first ? 'Next' : 'Later' }}
                                    </span>
                                </div>
                                @endforeach
                                @else
                                <!-- Default schedule if no data -->
                                <div class="p-4 text-center text-gray-500">
                                    <i class="mb-2 text-3xl fas fa-calendar-day"></i>
                                    <p class="font-medium">No classes scheduled</p>
                                    <p class="text-sm">Enjoy your free day!</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Recent Announcements -->
                    <div class="bg-white border border-gray-200 shadow-sm rounded-xl">
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-800">Recent Announcements</h3>
                                <a href="{{ route('instructor.announcements.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">View All</a>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                @if($recentAnnouncements && $recentAnnouncements->count() > 0)
                                @foreach($recentAnnouncements as $announcement)
                                <a href="{{ route('instructor.announcements.show', $announcement->id) }}" class="block">
                                    <div class="border-l-4 border-blue-500 pl-4 hover:bg-gray-50 rounded-r ">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <h4 class="font-medium text-gray-800 text-sm">{{ $announcement->title }}</h4>
                                                <p class="text-xs text-gray-600 mt-1">{{ $announcement->course->title ?? 'General' }}</p>
                                                <p class="text-xs text-gray-500 mt-1">{{ $announcement->created_at->diffForHumans() }}</p>
                                                @if($announcement->content)
                                                <p class="text-xs text-gray-600 mt-2 line-clamp-2">{{ Str::limit($announcement->content, 100) }}</p>
                                                @endif
                                            </div>
                                            <div class="text-blue-600 hover:text-blue-800 text-xs ml-4">
                                                <i class="fas fa-chevron-right"></i>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                                @endforeach
                                @else
                                <div class="text-center py-4">
                                    <i class="fas fa-bullhorn text-gray-400 text-2xl mb-2"></i>
                                    <p class="text-gray-500 text-sm">No recent announcements.</p>
                                    <p class="text-gray-400 text-xs mt-1">Create announcements to keep students updated.</p>
                                    <a href="{{ route('instructor.announcements.create') }}" class="inline-flex items-center px-3 py-1 mt-2 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 ">
                                        <i class="fas fa-plus mr-1"></i>Create Announcement
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions
                    <div class="bg-white border border-gray-200 shadow-sm rounded-xl">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">Quick Actions</h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-2 gap-3">
                                <a href="{{ route('instructor.assignments.create') }}"
                                    class="p-3 text-center ">
                                    <i class="mb-2 text-lg text-blue-600 fas fa-plus"></i>
                                    <p class="text-xs font-medium text-blue-700">Create Assignment</p>
                                </a>

                                <a href="{{ route('instructor.announcements.create') }}"
                                    class="p-3 text-center ">
                                    <i class="mb-2 text-lg text-green-600 fas fa-bullhorn"></i>
                                    <p class="text-xs font-medium text-green-700">Send Announcement</p>
                                </a>

                                <a href="{{ route('instructor.exams.create') }}"
                                    class="p-3 text-center ">
                                    <i class="mb-2 text-lg text-purple-600 fas fa-clipboard-check"></i>
                                    <p class="text-xs font-medium text-purple-700">Create Exam</p>
                                </a>

                                <a href="{{ route('instructor.gradebook.index') }}"
                                    class="p-3 text-center ">
                                    <i class="mb-2 text-lg text-orange-600 fas fa-chart-bar"></i>
                                    <p class="text-xs font-medium text-orange-700">View Gradebook</p>
                                </a>
                            </div>
                        </div>
                    </div> -->
                </div>
        </div>
    </div>
</div>
@endsection
