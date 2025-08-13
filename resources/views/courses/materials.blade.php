@extends('layouts.dashboard')

@section('title', $course->title)

@section('styles')
<style>
    .section-card {
        transition: all 0.3s ease;
    }

    .section-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    .expandable-section {
        transition: max-height 0.3s ease;
        overflow: hidden;
    }

    .add-section-btn {
        border: 2px dashed #d1d5db;
        transition: all 0.3s ease;
    }

    .add-section-btn:hover {
        border-color: #6366f1;
        background-color: #f8fafc;
    }

    .material-item {
        transition: all 0.2s ease;
    }

    .material-item:hover {
        background-color: #f9fafb;
    }

    /* Toast Notification Styles */
    .toast-container {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 10px;
        max-width: 400px;
        width: 100%;
        padding: 0 20px;
    }

    .toast {
        padding: 16px 20px;
        border-radius: 12px;
        color: white;
        font-weight: 500;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        transform: translateX(400px);
        opacity: 0;
        transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .toast.show {
        transform: translateX(0);
        opacity: 1;
    }

    .toast.success {
        background: linear-gradient(135deg, #10b981, #059669);
    }

    .toast.error {
        background: linear-gradient(135deg, #ef4444, #dc2626);
    }

    .toast.warning {
        background: linear-gradient(135deg, #f59e0b, #d97706);
    }

    .toast.info {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
    }

    .toast-icon {
        font-size: 20px;
        flex-shrink: 0;
    }

    .toast-close {
        margin-left: auto;
        cursor: pointer;
        padding: 4px;
        border-radius: 4px;
        transition: background-color 0.2s;
    }

    .toast-close:hover {
        background-color: rgba(255, 255, 255, 0.2);
    }

    /* File Upload Styles */
    .file-upload-area {
        transition: all 0.3s ease;
    }

    .file-upload-area:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .file-upload-area.drag-over {
        border-color: #6366f1;
        background-color: #eef2ff;
        transform: scale(1.02);
    }

    .upload-content, .file-selected {
        transition: all 0.3s ease;
    }

    /* Flash Message Animations */
    @keyframes slide-down {
        0% {
            transform: translate(-50%, -100%);
            opacity: 0;
        }
        100% {
            transform: translate(-50%, 0);
            opacity: 1;
        }
    }

    .animate-slide-down {
        animation: slide-down 0.4s ease-out forwards;
    }

    /* Auto-hide flash messages */
    .flash-message {
        animation: slide-down 0.4s ease-out forwards, fade-out 0.4s ease-out 4.6s forwards;
    }

    @keyframes fade-out {
        0% {
            opacity: 1;
            transform: translate(-50%, 0);
        }
        100% {
            opacity: 0;
            transform: translate(-50%, -20px);
        }
    }
</style>
@endsection

@section('content')
<!-- Flash Messages at Top -->
@if(session('success'))
<div class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 w-full max-w-md px-4">
    <div class="bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-center justify-between animate-slide-down">
        <div class="flex items-center">
            <i class="fas fa-check-circle text-xl mr-3"></i>
            <div>
                <strong class="font-semibold">Success!</strong>
                <p class="text-sm opacity-90">{{ session('success') }}</p>
            </div>
        </div>
        <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>
@endif

@if(session('error'))
<div class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 w-full max-w-md px-4">
    <div class="bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-center justify-between animate-slide-down">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle text-xl mr-3"></i>
            <div>
                <strong class="font-semibold">Error!</strong>
                <p class="text-sm opacity-90">{{ session('error') }}</p>
            </div>
        </div>
        <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>
@endif

@if($errors->any())
<div class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 w-full max-w-md px-4">
    <div class="bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg animate-slide-down">
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle text-xl mr-3"></i>
                <strong class="font-semibold">Validation Errors!</strong>
            </div>
            <button onclick="this.parentElement.parentElement.parentElement.remove()" class="text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <ul class="text-sm opacity-90 space-y-1">
            @foreach($errors->all() as $error)
                <li>• {{ $error }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

<div id="courseViewRoot" data-course-id="{{ $course->id }}" class="course-view max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Course Header -->
    <div class="bg-gradient-to-r from-indigo-600 bg-purple-600 text-white rounded-xl p-8 mb-8">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold mb-2">{{ $course->title }}</h1>
                <p class="text-indigo-100 mb-4">{{ $course->code }} • {{ $course->credits }} Credits</p>
                <p class="text-indigo-50">{{ $course->description }}</p>
            </div>
            <div class="text-right">
                <div class="bg-white bg-opacity-20 rounded-lg p-4 mb-4">
                    <div class="text-2xl font-bold">{{ $materials->count() }}</div>
                    <div class="text-sm text-indigo-100">Total Materials</div>
                </div>
                <a href="{{ route('courses.manage') }}" class="inline-flex items-center px-4 py-2 bg-white text-indigo-600 rounded-lg hover:bg-indigo-50">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Courses
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Course Materials Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sticky top-24">
                <h3 class="font-semibold text-gray-800 mb-4">Course Materials</h3>
                <div class="space-y-3">
                    @php
                        $sectionGroups = $materials->groupBy('section')->filter(function($materials, $section) {
                            return !empty(trim($section));
                        });
                    @endphp
                    @foreach($sectionGroups as $section => $sectionMaterials)
                    <div class="material-item p-3 rounded-lg border border-gray-200 cursor-pointer" onclick="scrollToSection('material-{{ $section }}')">
                        <div class="flex items-center">
                            <i class="fas fa-folder text-gray-400 mr-3"></i>
                            <div>
                                <p class="font-medium text-sm">{{ $section }}</p>
                                <p class="text-xs text-gray-500">{{ $sectionMaterials->count() }} items</p>
                            </div>
                        </div>
                    </div>
                    @endforeach

                    <button id="addMaterialButton" onclick="showModal('materialModal')" class="w-full p-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-500 hover:border-indigo-500 hover:text-indigo-500 transition-colors">
                        <i class="fas fa-plus mr-2"></i>Add Material
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="lg:col-span-3">
            <!-- Course Sections -->
            <div class="space-y-6">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-bold text-gray-800">Course Content</h2>
                    <button onclick="showModal('sectionModal')" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        <i class="fas fa-plus mr-2"></i>Add Section
                    </button>
                </div>

                <!-- Dynamic Sections -->
                <div id="courseSections">
                    @php
                        // Get stored sections from database
                        $storedSections = json_decode($course->sections ?? '[]', true) ?: [];
                        
                        // Get sections from materials (only sections that have actual materials)
                        $materialSections = $materials->groupBy('section')->filter(function($materials, $sectionName) {
                            return !empty(trim($sectionName)) && $materials->count() > 0;
                        });
                        
                        // Merge stored sections (empty sections) with material sections
                        $allSections = collect();
                        
                        // Add stored sections that don't have materials yet
                        foreach($storedSections as $storedSection) {
                            if (!empty(trim($storedSection)) && !$materialSections->has($storedSection)) {
                                $allSections->put($storedSection, collect());
                            }
                        }
                        
                        // Add sections with materials
                        $allSections = $allSections->merge($materialSections);
                    @endphp

                    @forelse($allSections as $sectionName => $sectionMaterials)
                    <div class="section-card bg-white rounded-xl shadow-sm border border-gray-200 p-6" data-section="{{ $sectionName }}">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-semibold text-gray-800">{{ $sectionName ?: 'General' }}</h3>
                            <div class="flex items-center space-x-2">
                                <button onclick="editSection('{{ $sectionName }}')" class="text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="toggleSection('{{ $sectionName }}')" class="text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-chevron-down section-toggle"></i>
                                </button>
                            </div>
                        </div>

                        <div class="expandable-section section-content" style="max-height: none;">
                            <div class="space-y-4">
                                @if($sectionMaterials->count() > 0)
                                    @foreach($sectionMaterials as $material)
                                    <div id="material-{{ $material->id }}" class="bg-gray-50 rounded-lg p-4 {{ $material->is_private ? 'border-l-4 border-red-400' : '' }}">
                                        <div class="flex justify-between items-start">
                                            <div class="flex-1">
                                                <div class="flex items-center mb-2">
                                                    <h4 class="font-medium text-gray-800">{{ $material->title }}</h4>
                                                    @if($material->is_private)
                                                    <span class="ml-2 px-2 py-1 bg-red-100 text-red-700 text-xs rounded-full flex items-center">
                                                        <i class="fas fa-eye-slash mr-1"></i>Private
                                                    </span>
                                                    @else
                                                    <span class="ml-2 px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full flex items-center">
                                                        <i class="fas fa-eye mr-1"></i>Public
                                                    </span>
                                                    @endif
                                                </div>
                                                <p class="text-gray-600 text-sm mb-3">{{ $material->description }}</p>

                                                @if($material->type === 'text' && $material->content)
                                                <div class="prose max-w-none bg-white p-4 rounded border">
                                                    {!! nl2br(e($material->content)) !!}
                                                </div>
                                                @elseif($material->type === 'file' && $material->file_name)
                                                <div class="bg-white p-4 rounded border">
                                                    <div class="flex items-center justify-between">
                                                        <div class="flex items-center">
                                                            <i class="fas fa-file-{{ $material->file_type === 'pdf' ? 'pdf' : ($material->file_type === 'doc' || $material->file_type === 'docx' ? 'word' : ($material->file_type === 'ppt' || $material->file_type === 'pptx' ? 'powerpoint' : 'alt')) }} text-2xl text-indigo-600 mr-3"></i>
                                                            <div>
                                                                <p class="font-medium text-gray-800">{{ $material->file_name }}</p>
                                                                <p class="text-sm text-gray-500">{{ number_format($material->file_size / 1024, 1) }} KB</p>
                                                            </div>
                                                        </div>
                                                        <a href="{{ route('courses.materials.download', [$course, $material]) }}" 
                                                           class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                                                            @if($material->google_drive_url)
                                                                <i class="fab fa-google-drive mr-2"></i>View on Drive
                                                            @else
                                                                <i class="fas fa-download mr-2"></i>Download
                                                            @endif
                                                        </a>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                            <div class="flex items-center space-x-2 ml-4">
                                                <button onclick="toggleMaterialPrivacy({{ $material->id }}, {{ $material->is_private ? 'true' : 'false' }})"
                                                    class="text-{{ $material->is_private ? 'red' : 'green' }}-400 hover:text-{{ $material->is_private ? 'red' : 'green' }}-600"
                                                    title="{{ $material->is_private ? 'Make Public' : 'Make Private' }}">
                                                    <i class="fas fa-{{ $material->is_private ? 'eye-slash' : 'eye' }}"></i>
                                                </button>
                                                <button onclick="editMaterial({{ $material->id }})" class="text-blue-400 hover:text-blue-600" title="Edit Material">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="{{ route('courses.materials.destroy', [$course, $material]) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this material?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-400 hover:text-red-600" title="Delete Material">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                    <div class="text-center py-8">
                                        <i class="fas fa-folder-open text-gray-300 text-4xl mb-3"></i>
                                        <p class="text-gray-500">This section is empty. Add some content to get started.</p>
                                    </div>
                                @endif

                                <!-- Add content button -->
                                <button onclick="showModal('materialModal'); document.getElementById('materialSection').value='{{ $sectionName }}'" class="w-full p-4 border-2 border-dashed border-gray-300 rounded-lg text-gray-500 hover:border-indigo-500 hover:text-indigo-500 transition-colors add-section-btn">
                                    <i class="fas fa-plus mr-2"></i>Add Content to This Section
                                </button>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-book text-gray-400 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-800 mb-2">No Course Content Yet</h3>
                        <p class="text-gray-500 mb-4">Start building your course by adding sections and materials.</p>
                        <button onclick="showModal('sectionModal')" class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                            <i class="fas fa-plus mr-2"></i>Add First Section
                        </button>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification Container -->
<div id="toastContainer" class="toast-container"></div>

<!-- Add Section Modal -->
<div id="sectionModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-xl max-w-md w-full p-6 border border-gray-300 shadow-2xl">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Add New Section</h3>
        <form id="sectionForm" action="{{ route('courses.sections.store', $course) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Section Name</label>
                <input type="text" id="sectionName" name="section_name" placeholder="e.g., Week 1: Introduction"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="hideModal('sectionModal')" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    Create Section
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Add Material Modal -->
<div id="materialModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Add Course Material</h3>
        <form id="materialForm" action="{{ route('courses.materials.store', $course) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                        <input type="text" id="materialTitle" name="title" placeholder="Material title" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Section</label>
                        <select id="materialSection" name="section_name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select Section</option>
                            @php
                                $storedSections = json_decode($course->sections ?? '[]', true);
                                $materialSections = $course->materials->pluck('section')->unique()->filter()->toArray();
                                $allAvailableSections = array_unique(array_merge($storedSections, $materialSections));
                            @endphp
                            @foreach($allAvailableSections as $section)
                            <option value="{{ $section }}">{{ $section }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                    <select id="materialType" name="material_type" onchange="toggleMaterialFields()" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="text">Text Content</option>
                        <option value="file" selected>File Upload</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea id="materialDescription" name="description" rows="2" placeholder="Brief description"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Privacy Settings</label>
                    <div class="flex items-center space-x-4">
                        <label class="flex items-center">
                            <input type="radio" name="is_private" value="0" checked class="mr-2">
                            <i class="fas fa-eye text-green-500 mr-1"></i>
                            <span>Public (Students can see)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="is_private" value="1" class="mr-2">
                            <i class="fas fa-eye-slash text-red-500 mr-1"></i>
                            <span>Private (Only you can see)</span>
                        </label>
                    </div>
                </div>

                <!-- File Upload Field -->
                <div id="fileField" class="material-field">
                    <label class="block text-sm font-medium text-gray-700 mb-3">File Upload</label>
                    <div class="file-upload-area border-2 border-dashed border-indigo-300 rounded-xl p-8 text-center hover:border-indigo-500 hover:bg-indigo-50 transition-all duration-200">
                        <div class="flex flex-col items-center">
                            <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mb-4">
                                <i class="fas fa-cloud-upload-alt text-2xl text-indigo-600"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-800 mb-2">Choose File to Upload</h3>
                            <p class="text-gray-500 mb-4">Drag and drop or click to browse</p>
                            <input type="file" id="materialFile" name="file" accept=".pdf,.ppt,.pptx,.doc,.docx,.jpg,.jpeg,.png,.gif" 
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            <p class="text-xs text-gray-400 mt-2">Supported: PDF, PPT, DOC, Images (Max: 10MB)</p>
                        </div>
                    </div>
                </div>

                <!-- Text Content Field -->
                <div id="textField" class="material-field" style="display: none;">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Content</label>
                    <textarea id="materialContent" name="content" rows="6" placeholder="Enter your text content here..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="closeMaterialModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    Add Material
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Material Modal -->
<div id="editMaterialModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto p-6 border border-gray-300 shadow-2xl">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Edit Material</h3>
        <form id="editMaterialForm" enctype="multipart/form-data">
            <input type="hidden" id="editMaterialId">
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                        <input type="text" id="editMaterialTitle" placeholder="Material title"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Section</label>
                        <select id="editMaterialSection" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select Section</option>
                            @php
                                $storedSections = json_decode($course->sections ?? '[]', true);
                                $materialSections = $materials->pluck('section')->unique()->filter()->toArray();
                                $allAvailableSections = array_unique(array_merge($storedSections, $materialSections));
                            @endphp
                            @foreach($allAvailableSections as $section)
                            <option value="{{ $section }}">{{ $section }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea id="editMaterialDescription" rows="2" placeholder="Brief description"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Privacy Settings</label>
                    <div class="flex items-center space-x-4">
                        <label class="flex items-center">
                            <input type="radio" name="editPrivacy" value="0" id="editPublic" class="mr-2">
                            <i class="fas fa-eye text-green-500 mr-1"></i>
                            <span>Public (Students can see)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="editPrivacy" value="1" id="editPrivate" class="mr-2">
                            <i class="fas fa-eye-slash text-red-500 mr-1"></i>
                            <span>Private (Only you can see)</span>
                        </label>
                    </div>
                </div>

                <!-- Content based on type - will be populated dynamically -->
                <div id="editContentContainer">
                    <!-- Dynamic content fields will be inserted here -->
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="hideModal('editMaterialModal')" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    Update Material
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
@vite(['resources/js/pages/courses/materials.js'])
@endsection


