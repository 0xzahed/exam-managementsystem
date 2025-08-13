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

                <!-- Success/Error Messages -->
                @if (session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 rounded-xl p-4">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
                        <div>
                            <h3 class="text-green-800 font-medium">Success!</h3>
                            <p class="text-green-700 text-sm">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
                @endif

                @if (session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-500 text-xl mr-3"></i>
                        <div>
                            <h3 class="text-red-800 font-medium">Error</h3>
                            <p class="text-red-700 text-sm">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Quick Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200 card-hover">
                        <div class="flex items-center">
                            <div class="p-3 bg-blue-100 rounded-lg">
                                <i class="fas fa-book text-blue-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-600">Total Courses</p>
                                <p class="text-2xl font-bold text-gray-800">0</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200 card-hover">
                        <div class="flex items-center">
                            <div class="p-3 bg-orange-100 rounded-lg">
                                <i class="fas fa-tasks text-orange-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-600">Pending Assignments</p>
                                <p class="text-2xl font-bold text-gray-800">{{ $pendingAssignments }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200 card-hover">
                        <div class="flex items-center">
                            <div class="p-3 bg-green-100 rounded-lg">
                                <i class="fas fa-chart-line text-green-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-600">Average Grade</p>
                                <p class="text-2xl font-bold text-gray-800">{{ $averageGrade ? $averageGrade . '%' : 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200 card-hover">
                        <div class="flex items-center">
                            <div class="p-3 bg-purple-100 rounded-lg">
                                <i class="fas fa-clock text-purple-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-600">Upcoming Exams</p>
                                <p class="text-2xl font-bold text-gray-800">{{ $upcomingExams }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Dashboard Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left Column -->
                    <div class="lg:col-span-2 space-y-8">
                        <!-- My Courses -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                            <div class="p-6 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-gray-800">My Courses</h3>
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="text-center py-4">
                                    <i class="fas fa-book text-gray-400 text-2xl mb-2"></i>
                                    <p class="text-gray-500 text-sm">Course enrollment feature coming soon.</p>
                                    <p class="text-gray-400 text-xs mt-1">You'll be able to enroll in courses here.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Upcoming Assignments -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                            <div class="p-6 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-800">Upcoming Assignments</h3>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    @forelse($assignments as $assignment)
                                    <div class="flex items-center justify-between p-4 border border-red-200 bg-red-50 rounded-lg mb-2">
                                        <div class="flex items-center">
                                            <div class="w-3 h-3 bg-red-500 rounded-full mr-4"></div>
                                            <div>
                                                <h4 class="font-medium text-gray-800">{{ $assignment->title }}</h4>
                                                <p class="text-sm text-gray-600">{{ $assignment->course->title ?? '' }} • Due {{ $assignment->due_date }}</p>
                                            </div>
                                        </div>
                                        <button class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium">
                                            Submit
                                        </button>
                                    </div>
                                    @empty
                                    <div class="text-gray-500">No upcoming assignments.</div>
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
                                    <span class="text-gray-500 text-sm">Coming Soon</span>
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    @forelse($announcements as $announcement)
                                    <a href="{{ route('announcements.show', $announcement->id) }}" class="block">
                                        <div class="border-l-4 border-indigo-500 pl-4 hover:bg-gray-50 rounded-r transition-colors cursor-pointer">
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
                                <h3 class="text-lg font-semibold text-gray-800">Upcoming Exams</h3>
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
                                        <button class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 text-sm font-medium">
                                            Review Materials
                                        </button>
                                    </div>
                                    @empty
                                    <div class="text-gray-500">No upcoming exams.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <!-- Recent Grades -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                            <div class="p-6 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-800">Recent Grades</h3>
                            </div>
                            <div class="p-6">
                                <div class="space-y-3">
                                    @forelse($grades as $grade)
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-gray-800">{{ $grade['title'] }}</p>
                                            <p class="text-xs text-gray-600">{{ $grade['course'] }}</p>
                                        </div>
                                        <span class="text-lg font-bold {{ $grade['color'] }}">{{ $grade['score'] }}</span>
                                    </div>
                                    @empty
                                    <div class="text-gray-500">No recent grades.</div>
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

<!--  -->