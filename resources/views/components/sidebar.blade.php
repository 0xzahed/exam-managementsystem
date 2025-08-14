<div id="sidebar" class="sidebar -translate-x-full lg:translate-x-0 overflow-hidden">
    <div class="flex flex-col h-full">
        <!-- Sidebar Header -->
        <div class="px-6 py-4 border-b border-gray-200 flex-shrink-0">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center">
                        <span class="text-white font-medium">{{ strtoupper(substr(Auth::user()->first_name,0,1)) }}{{ strtoupper(substr(Auth::user()->last_name,0,1)) }}</span>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-800">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</p>
                        <p class="text-xs text-gray-500">{{ Auth::user()->department ?? 'Department' }}</p>
                    </div>
                </div>
                <!-- Close button for mobile -->
                <button id="closeSidebar" class="lg:hidden text-gray-600 hover:text-gray-900 p-1">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>

        <!-- Navigation Menu with enhanced scrolling -->
        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100 hover:scrollbar-thumb-gray-400">
            @if(Auth::user()->role === 'instructor')
            <!-- Instructor Navigation -->
            <a href="{{ route('instructor.dashboard') }}" class="nav-item flex items-center px-4 py-3 text-sm font-medium {{ request()->routeIs('instructor.dashboard') ? 'bg-purple-100 text-purple-700' : 'text-gray-600' }} rounded-lg hover:bg-gray-100 hover:text-gray-900">
                <i class="fas fa-home mr-3 text-lg"></i>
                Dashboard
            </a>

            <a href="{{ route('courses.manage') }}" class="nav-item flex items-center px-4 py-3 text-sm font-medium {{ request()->routeIs('courses.*') ? 'bg-purple-100 text-purple-700' : 'text-gray-600' }} rounded-lg hover:bg-gray-100 hover:text-gray-900 ">
                <i class="fas fa-chalkboard-teacher mr-3 text-lg"></i>
                My Courses
            </a>

            <a href="{{ route('students.index') }}" class="nav-item flex items-center px-4 py-3 text-sm font-medium {{ request()->routeIs('students.*') ? 'bg-purple-100 text-purple-700' : 'text-gray-600' }} rounded-lg hover:bg-gray-100 hover:text-gray-900 ">
                <i class="fas fa-users mr-3 text-lg"></i>
                Students
            </a>

            <a href="{{ route('instructor.assignments.index') }}" class="nav-item flex items-center px-4 py-3 text-sm font-medium {{ request()->routeIs('instructor.assignments.*') ? 'bg-purple-100 text-purple-700' : 'text-gray-600' }} rounded-lg hover:bg-gray-100 hover:text-gray-900 ">
                <i class="fas fa-tasks mr-3 text-lg"></i>
                Assignments
            </a>

            <a href="{{ route('instructor.exams.index') }}" class="nav-item flex items-center px-4 py-3 text-sm font-medium {{ request()->routeIs('instructor.exams.*') ? 'bg-purple-100 text-purple-700' : 'text-gray-600' }} rounded-lg hover:bg-gray-100 hover:text-gray-900 ">
                <i class="fas fa-clipboard-check mr-3 text-lg"></i>
                Exams
            </a>

            <a href="{{ route('instructor.gradebook.index') }}" class="nav-item flex items-center px-4 py-3 text-sm font-medium {{ request()->routeIs('instructor.gradebook.*') ? 'bg-purple-100 text-purple-700' : 'text-gray-600' }} rounded-lg hover:bg-gray-100 hover:text-gray-900 ">
                <i class="fas fa-book-open mr-3 text-lg"></i>
                Gradebook
            </a>

            <a href="{{ route('instructor.announcements.index') }}" class="nav-item flex items-center px-4 py-3 text-sm font-medium {{ request()->routeIs('instructor.announcements.*') ? 'bg-purple-100 text-purple-700' : 'text-gray-600' }} rounded-lg hover:bg-gray-100 hover:text-gray-900 ">
                <i class="fas fa-bullhorn mr-3 text-lg"></i>
                Announcements
            </a>

            <a href="#" class="nav-item flex items-center px-4 py-3 text-sm font-medium text-gray-600 rounded-lg hover:bg-gray-100 hover:text-gray-900 ">
                <i class="fas fa-calendar-alt mr-3 text-lg"></i>
                Schedule
            </a>

            <!-- Divider -->
            <div class="border-t border-gray-200 my-4"></div>

            <a href="{{ route('courses.create') }}" class="nav-item flex items-center px-4 py-3 text-sm font-medium {{ request()->routeIs('courses.create') ? 'bg-purple-100 text-purple-700' : 'text-gray-600' }} rounded-lg hover:bg-gray-100 hover:text-gray-900 ">
                <i class="fas fa-plus-circle mr-3 text-lg"></i>
                Create Course
            </a>

            <a href="#" class="nav-item flex items-center px-4 py-3 text-sm font-medium text-gray-600 rounded-lg hover:bg-gray-100 hover:text-gray-900 ">
                <i class="fas fa-chart-bar mr-3 text-lg"></i>
                Analytics
            </a>
            @else
            <!-- Student Navigation -->
            <a href="{{ route('student.dashboard') }}" class="nav-item flex items-center px-4 py-3 text-sm font-medium {{ request()->routeIs('student.dashboard') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600' }} rounded-lg hover:bg-gray-100 hover:text-gray-900 ">
                <i class="fas fa-home mr-3 text-lg"></i>
                Dashboard
            </a>

            <a href="{{ route('student.courses.my') }}" class="nav-item flex items-center px-4 py-3 text-sm font-medium {{ request()->routeIs('student.courses.my') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600' }} rounded-lg hover:bg-gray-100 hover:text-gray-900 ">
                <i class="fas fa-book mr-3 text-lg"></i>
                My Courses
            </a>

         

            <a href="{{ route('assignments.index') }}" class="nav-item flex items-center px-4 py-3 text-sm font-medium {{ request()->routeIs('assignments.*') && !request()->routeIs('instructor.*') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600' }} rounded-lg hover:bg-gray-100 hover:text-gray-900 ">
                <i class="fas fa-tasks mr-3 text-lg"></i>
                Assignments
            </a>

            <a href="{{ route('student.exams.index') }}" class="nav-item flex items-center px-4 py-3 text-sm font-medium {{ request()->routeIs('student.exams.*') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600' }} rounded-lg hover:bg-gray-100 hover:text-gray-900 ">
                <i class="fas fa-clipboard-check mr-3 text-lg"></i>
                Exams
            </a>

            <a href="{{ route('student.grades.index') }}" class="nav-item flex items-center px-4 py-3 text-sm font-medium {{ request()->routeIs('student.grades.*') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600' }} rounded-lg hover:bg-gray-100 hover:text-gray-900 ">
                <i class="fas fa-chart-bar mr-3 text-lg"></i>
                My Grades
            </a>

            <a href="{{ route('student.announcements.index') }}" class="nav-item flex items-center px-4 py-3 text-sm font-medium {{ request()->routeIs('student.announcements.*') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600' }} rounded-lg hover:bg-gray-100 hover:text-gray-900 ">
                <i class="fas fa-bullhorn mr-3 text-lg"></i>
                Announcements
            </a>

            <a href="#" class="nav-item flex items-center px-4 py-3 text-sm font-medium text-gray-600 rounded-lg hover:bg-gray-100 hover:text-gray-900 ">
                <i class="fas fa-calendar-alt mr-3 text-lg"></i>
                Schedule
            </a>

            <!-- Divider -->
            <div class="border-t border-gray-200 my-4"></div>
               <a href="{{ route('student.courses.enroll') }}" class="nav-item flex items-center px-4 py-3 text-sm font-medium {{ request()->routeIs('student.courses.enroll') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600' }} rounded-lg hover:bg-gray-100 hover:text-gray-900 ">
                <i class="fas fa-plus-circle mr-3 text-lg"></i>
                Enroll Course
            </a>
            <a href="#" class="nav-item flex items-center px-4 py-3 text-sm font-medium text-gray-600 rounded-lg hover:bg-gray-100 hover:text-gray-900 ">
                <i class="fas fa-download mr-3 text-lg"></i>
                Resources
            </a>
            @endif
        </nav>

        <!-- Sidebar Footer -->
        <div class="px-4 py-4 border-t border-gray-200">
            <a href="{{ route('help.index') }}" class="flex items-center px-4 py-2 text-sm font-medium text-gray-600 rounded-lg hover:bg-gray-100 hover:text-gray-900 ">
                <i class="fas fa-question-circle mr-3 text-lg"></i>
                Help & Support
            </a>
        </div>
    </div>
</div>
