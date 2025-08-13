@extends('layouts.dashboard')

@section('title', 'Course Details - ' . $course->title)

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Course Header -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl shadow-lg p-8 text-white mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div class="flex-1">
                <h1 class="text-3xl lg:text-4xl font-bold mb-2">{{ $course->title }}</h1>
                <p class="text-indigo-100 text-lg mb-4">{{ $course->description }}</p>
                <div class="flex items-center space-x-6">
                    <div class="flex items-center">
                        <i class="fas fa-user-tie mr-2"></i>
                        <span>{{ $course->instructor->first_name }} {{ $course->instructor->last_name }}</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-calendar mr-2"></i>
                        <span>{{ $course->created_at->format('M Y') }}</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-users mr-2"></i>
                        <span>{{ $course->students->count() }} students</span>
                    </div>
                </div>
            </div>
            <div class="mt-6 lg:mt-0">
                <div class="bg-white bg-opacity-20 rounded-lg p-4 text-center">
                    <i class="fas fa-graduation-cap text-3xl mb-2"></i>
                    <p class="text-sm font-medium">Enrolled Student</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Materials Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sticky top-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-book text-indigo-600 mr-2"></i>Course Materials
                </h3>
                
                @php
                    $materialSections = $course->materials->groupBy('section');
                    $totalMaterials = $course->materials->count();
                @endphp

                @if($totalMaterials > 0)
                <div class="space-y-3">
                    @foreach($materialSections as $sectionName => $materials)
                    <div class="border border-gray-100 rounded-lg p-3">
                        <div class="flex items-center justify-between cursor-pointer" onclick="scrollToSection('section-{{ Str::slug($sectionName) }}')">
                            <h4 class="font-medium text-gray-700 text-sm">{{ $sectionName ?: 'General' }}</h4>
                            <span class="bg-indigo-100 text-indigo-700 text-xs px-2 py-1 rounded-full">{{ $materials->count() }}</span>
                        </div>
                        <div class="mt-2 space-y-1">
                            @foreach($materials as $material)
                            <div class="p-2 rounded hover:bg-gray-50 cursor-pointer text-xs" onclick="scrollToSection('material-{{ $material->id }}')">
                                <div class="flex items-center">
                                    <i class="fas fa-{{ $material->type === 'file' ? 'file-alt' : 'align-left' }} text-gray-400 mr-2"></i>
                                    <span class="text-gray-600 truncate">{{ $material->title }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach

                    <div class="pt-3 border-t border-gray-100">
                        <div class="text-xs text-gray-500 text-center">
                            Total: {{ $totalMaterials }} materials
                        </div>
                    </div>
                </div>
                @else
                <div class="text-center py-6">
                    <i class="fas fa-folder-open text-gray-300 text-2xl mb-2"></i>
                    <p class="text-sm text-gray-500">No materials yet</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="lg:col-span-3">
            <!-- Course Sections -->
            <div class="space-y-6">
                @php
                    $materialSections = $course->materials->groupBy('section');
                @endphp

                @forelse($materialSections as $sectionName => $materials)
                <div id="section-{{ Str::slug($sectionName) }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold text-gray-800">{{ $sectionName ?: 'General' }}</h3>
                        <span class="bg-indigo-100 text-indigo-800 text-sm px-3 py-1 rounded-full">{{ $materials->count() }} items</span>
                    </div>

                    <div class="space-y-4">
                        @foreach($materials as $material)
                        <div id="material-{{ $material->id }}" class="bg-gray-50 rounded-lg p-4 transition-all hover:shadow-md">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-800 mb-2">{{ $material->title }}</h4>
                                    @if($material->description)
                                    <p class="text-gray-600 text-sm mb-3">{{ $material->description }}</p>
                                    @endif

                                    @if($material->type === 'text' && $material->content)
                                    <div class="prose max-w-none bg-white p-4 rounded border">
                                        {!! nl2br(e($material->content)) !!}
                                    </div>
                                    @elseif($material->type === 'file' && $material->file_path)
                                    <div class="bg-white p-4 rounded border">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <i class="fas fa-file-{{ $material->file_type === 'pdf' ? 'pdf' : ($material->file_type === 'doc' || $material->file_type === 'docx' ? 'word' : ($material->file_type === 'ppt' || $material->file_type === 'pptx' ? 'powerpoint' : 'alt')) }} text-2xl text-indigo-600 mr-3"></i>
                                                <div>
                                                    <p class="font-medium text-gray-800">{{ $material->file_name ?? $material->title }}</p>
                                                    <p class="text-sm text-gray-500">{{ isset($material->file_size) ? number_format($material->file_size / 1024, 1) . ' KB' : 'File' }}</p>
                                                </div>
                                            </div>
                                            @if($material->google_drive_url)
                                            <a href="{{ $material->google_drive_url }}" target="_blank"
                                               class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                                                <i class="fab fa-google-drive mr-2"></i>View on Drive
                                            </a>
                                            @elseif($material->file_path)
                                            <a href="{{ route('courses.materials.download', [$course, $material]) }}" 
                                               class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                                                <i class="fas fa-download mr-2"></i>Download
                                            </a>
                                            @endif
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @empty
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-book text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-800 mb-2">No Course Content Yet</h3>
                    <p class="text-gray-500 mb-4">Your instructor hasn't added any course materials yet. Check back later for updates.</p>
                </div>
                @endforelse

                <!-- Assignments Section -->
                @if($assignments && $assignments->count() > 0)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold text-gray-800">
                            <i class="fas fa-tasks text-blue-600 mr-2"></i>Assignments
                        </h3>
                        <span class="bg-blue-100 text-blue-800 text-sm px-3 py-1 rounded-full">{{ $assignments->count() }}</span>
                    </div>

                    <div class="space-y-4">
                        @foreach($assignments as $assignment)
                        <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-800 mb-1">{{ $assignment->title }}</h4>
                                    <p class="text-gray-600 text-sm mb-3">{{ Str::limit($assignment->description, 120) }}</p>
                                    <div class="flex items-center text-sm text-gray-600 space-x-4">
                                        <span><i class="fas fa-calendar mr-1"></i>Due: {{ $assignment->due_date ? $assignment->due_date->format('M j, Y') : 'No due date' }}</span>
                                        <span><i class="fas fa-star mr-1"></i>{{ $assignment->points ?? 0 }} points</span>
                                    </div>
                                </div>
                                <a href="{{ route('assignments.show', $assignment->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-eye mr-2"></i>View
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification Container -->
<div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>

<script>
    function scrollToSection(sectionId) {
        const element = document.getElementById(sectionId);
        if (element) {
            element.scrollIntoView({ 
                behavior: 'smooth',
                block: 'start'
            });
            
            // Add highlight effect
            element.classList.add('ring-2', 'ring-indigo-300', 'ring-opacity-50');
            setTimeout(() => {
                element.classList.remove('ring-2', 'ring-indigo-300', 'ring-opacity-50');
            }, 2000);
        }
    }

    // Toast notification function
    function showToast(message, type = 'info') {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        
        const colors = {
            success: 'bg-green-500 text-white',
            error: 'bg-red-500 text-white',
            info: 'bg-blue-500 text-white',
            warning: 'bg-yellow-500 text-white'
        };
        
        toast.className = `${colors[type]} px-4 py-3 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full opacity-0`;
        toast.innerHTML = `
            <div class="flex items-center">
                <span class="mr-2">${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        container.appendChild(toast);
        
        // Animate in
        setTimeout(() => {
            toast.classList.remove('translate-x-full', 'opacity-0');
        }, 100);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            toast.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }
</script>

@if($course->materials && $course->materials->count() > 0)
<script>
    // Custom JavaScript for course details interactivity
    document.addEventListener('DOMContentLoaded', function() {
        // Add smooth scroll behavior for material navigation
        const materialItems = document.querySelectorAll('[onclick*="scrollToSection"]');
        materialItems.forEach(item => {
            item.addEventListener('click', function() {
                // Add active state to clicked item
                materialItems.forEach(mi => mi.classList.remove('bg-indigo-50'));
                this.classList.add('bg-indigo-50');
            });
        });
        
        // Intersection Observer for active section highlighting
        const sections = document.querySelectorAll('[id^="section-"], [id^="material-"]');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // Update active sidebar item
                    const targetId = entry.target.id;
                    const sidebarItem = document.querySelector(`[onclick*="${targetId}"]`);
                    if (sidebarItem) {
                        document.querySelectorAll('.bg-indigo-50').forEach(item => 
                            item.classList.remove('bg-indigo-50')
                        );
                        sidebarItem.classList.add('bg-indigo-50');
                    }
                }
            });
        }, { rootMargin: '-20% 0px -70% 0px' });
        
        sections.forEach(section => observer.observe(section));
    });
</script>
@endif
@endsection
