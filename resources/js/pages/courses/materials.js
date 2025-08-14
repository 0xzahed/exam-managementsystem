// Materials Page - UI Interactions Only
console.log('Materials.js loading...');

// Global functions for modal control
window.showModal = function(modalId) {
    console.log('showModal called with:', modalId);
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        console.log('Modal shown:', modalId);
    } else {
        console.error('Modal not found:', modalId);
    }
};

window.hideModal = function(modalId) {
    console.log('hideModal called with:', modalId);
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        console.log('Modal hidden:', modalId);
    }
};

window.closeMaterialModal = function() {
    hideModal('materialModal');
    clearFileSelection();
};

window.closeSectionModal = function() {
    hideModal('sectionModal');
};

window.closeEditMaterialModal = function() {
    hideModal('editMaterialModal');
};

// Toggle material fields
window.toggleMaterialFields = function() {
    const materialType = document.getElementById('materialType');
    if (!materialType) return;
    
    const selectedType = materialType.value;
    const fileField = document.getElementById('fileField');
    const textField = document.getElementById('textField');
    
    // Hide all fields first
    if (fileField) fileField.style.display = 'none';
    if (textField) textField.style.display = 'none';
    
    // Show relevant field
    if (selectedType === 'file' && fileField) {
        fileField.style.display = 'block';
    } else if (selectedType === 'text' && textField) {
        textField.style.display = 'block';
    }
};

// File handling functions
window.clearFileSelection = function() {
    console.log('Clearing file selection');
    
    const fileInput = document.getElementById('materialFile');
    const uploadArea = document.getElementById('fileUploadArea');
    const uploadContent = uploadArea?.querySelector('.upload-content');
    const fileSelected = uploadArea?.querySelector('.file-selected');
    
    if (fileInput) {
        fileInput.value = '';
        console.log('File input cleared');
    }
    
    // Show upload content, hide file selected
    if (uploadContent) {
        uploadContent.style.display = 'block';
        uploadContent.classList.remove('hidden');
        console.log('Upload content shown');
    }
    
    if (fileSelected) {
        fileSelected.style.display = 'none';
        fileSelected.classList.add('hidden');
        console.log('File selected area hidden');
    }
    
    // Reset visual feedback
    if (uploadArea) {
        uploadArea.classList.remove('border-green-400', 'bg-green-50');
        uploadArea.classList.add('border-gray-300');
        console.log('Upload area reset to default style');
    }
};

// Section functions
window.toggleSection = function(sectionName) {
    const section = document.querySelector(`[data-section="${sectionName}"]`);
    if (section) {
        const content = section.querySelector('.section-content');
        const toggle = section.querySelector('.section-toggle');
        
        if (content.style.maxHeight === '0px' || !content.style.maxHeight) {
            content.style.maxHeight = content.scrollHeight + 'px';
            toggle.style.transform = 'rotate(180deg)';
        } else {
            content.style.maxHeight = '0px';
            toggle.style.transform = 'rotate(0deg)';
        }
    }
};

window.editSection = function(sectionName) {
    // Implementation for editing section
    console.log('Edit section:', sectionName);
};

window.scrollToSection = function(sectionId) {
    const element = document.getElementById(sectionId);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth' });
    }
};

// Material functions
window.editMaterial = function(materialId) {
    showModal('editMaterialModal');
    const idInput = document.getElementById('editMaterialId');
    if (idInput) idInput.value = materialId;
    
    // Show loading state
    showInfo('Loading material data...');
    
    // Fetch material data and populate the form
    fetch(`/courses/${getCourseId()}/materials/${materialId}/edit`)
        .then(response => response.json())
        .then(data => {
            if (data.material) {
                populateEditForm(data.material);
                showSuccess('Material data loaded successfully');
            } else {
                showError('Material data not found');
            }
        })
        .catch(error => {
            console.error('Error fetching material data:', error);
            showError('Failed to load material data');
        });
};

// Populate edit form with material data
function populateEditForm(material) {
    document.getElementById('editMaterialId').value = material.id;
    document.getElementById('editMaterialTitle').value = material.title;
    document.getElementById('editMaterialDescription').value = material.description;
    document.getElementById('editMaterialSection').value = material.section;
    
    // Set privacy radio button
    if (material.is_private) {
        document.getElementById('editPrivate').checked = true;
    } else {
        document.getElementById('editPublic').checked = true;
    }
    
    // Populate content based on type
    const contentContainer = document.getElementById('editContentContainer');
    contentContainer.innerHTML = '';
    
    if (material.type === 'text') {
        contentContainer.innerHTML = `
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Content</label>
                <textarea name="content" rows="6" placeholder="Enter your text content here..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">${material.content || ''}</textarea>
            </div>
        `;
    } else if (material.type === 'file') {
        contentContainer.innerHTML = `
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Current File</label>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-600">${material.file_name || 'No file'}</p>
                    <p class="text-xs text-gray-500">${material.file_size ? (material.file_size / 1024).toFixed(1) + ' KB' : ''}</p>
                </div>
                <label class="block text-sm font-medium text-gray-700 mb-2 mt-4">Upload New File (Optional)</label>
                <input type="file" name="file" accept=".pdf,.ppt,.pptx,.doc,.docx,.jpg,.jpeg,.png,.gif" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
        `;
    }
    
    // Update form action
    const editForm = document.getElementById('editMaterialForm');
    editForm.action = `/courses/${getCourseId()}/materials/${material.id}`;
    editForm.method = 'POST';
    
    // Add method override for PUT request
    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'PUT';
    editForm.appendChild(methodInput);
    
    // Add CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    editForm.appendChild(csrfInput);
};

// Get course ID from the page
function getCourseId() {
    const courseViewRoot = document.getElementById('courseViewRoot');
    return courseViewRoot ? courseViewRoot.getAttribute('data-course-id') : null;
}

window.toggleMaterialPrivacy = function(materialId, isPrivate) {
    console.log('Toggle privacy for material:', materialId, 'isPrivate:', isPrivate);
    
    // Make AJAX request to toggle privacy
    const courseId = getCourseId();
    if (!courseId) {
        showError('Course ID not found');
        return;
    }
    
    const url = `/courses/${courseId}/materials/${materialId}/privacy`;
    const newPrivacy = !isPrivate;
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            is_private: newPrivacy
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess(data.message || 'Privacy setting updated successfully');
            // Optionally reload the page or update the UI
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showError(data.message || 'Failed to update privacy setting');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('An error occurred while updating privacy setting');
    });
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Materials.js initialized - DOM ready');
    
    // Setup file upload functionality
    setupFileUpload();
    
    // Setup material type toggle
    setupMaterialTypeToggle();
    
    console.log('All functions initialized');
});

// Setup file upload functionality
function setupFileUpload() {
    const uploadArea = document.getElementById('fileUploadArea');
    const fileInput = document.getElementById('materialFile');
    
    if (!uploadArea || !fileInput) {
        console.warn('File upload elements not found');
        return;
    }
    
    console.log('Setting up file upload');
    
    // File input change handler
    fileInput.addEventListener('change', function(event) {
        console.log('File input change event fired');
        const file = event.target.files[0];
        if (file) {
            console.log('File selected:', file.name);
            showSelectedFile(file);
        }
    });
    
    // Drag and drop functionality
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('border-indigo-500', 'bg-indigo-50');
        console.log('File dragged over upload area');
    });
    
    uploadArea.addEventListener('dragleave', (e) => {
        e.preventDefault();
        if (!uploadArea.contains(e.relatedTarget)) {
            uploadArea.classList.remove('border-indigo-500', 'bg-indigo-50');
            uploadArea.classList.add('border-gray-300');
        }
    });
    
    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        console.log('File dropped in upload area');
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            const file = files[0];
            if (file) {
                showSelectedFile(file);
            }
        }
    });
}

// Setup material type toggle
function setupMaterialTypeToggle() {
    const materialType = document.getElementById('materialType');
    if (materialType) {
        materialType.addEventListener('change', toggleMaterialFields);
        toggleMaterialFields(); // Initialize with current value
    }
}

// File selection display
function showSelectedFile(file) {
    console.log('showSelectedFile called with:', file.name);
    
    // Check file size (max 10MB)
    const maxSize = 10 * 1024 * 1024; // 10MB in bytes
    if (file.size > maxSize) {
        showError('File size exceeds 10MB limit. Please choose a smaller file.');
        clearFileSelection();
        return;
    }
    
    // Check file type
    const allowedTypes = [
        'application/pdf',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif'
    ];
    
    if (!allowedTypes.includes(file.type)) {
        showError('Invalid file type. Please upload PDF, PPT, DOC, or image files only.');
        clearFileSelection();
        return;
    }
    
    const uploadArea = document.getElementById('fileUploadArea');
    const uploadContent = uploadArea?.querySelector('.upload-content');
    const fileSelected = uploadArea?.querySelector('.file-selected');
    const fileName = document.getElementById('selectedFileName');
    const fileSize = document.getElementById('selectedFileSize');
    
    // Update file information
    if (fileName) {
        fileName.textContent = file.name;
        console.log('Set fileName to:', file.name);
    }
    
    if (fileSize) {
        const sizeMB = (file.size / 1024 / 1024).toFixed(2);
        fileSize.textContent = `Size: ${sizeMB} MB`;
        console.log('Set fileSize to:', `Size: ${sizeMB} MB`);
    }
    
    // Toggle visibility
    if (uploadContent) {
        uploadContent.style.display = 'none';
        console.log('Hidden upload content');
    }
    
    if (fileSelected) {
        fileSelected.style.display = 'block';
        fileSelected.classList.remove('hidden');
        console.log('Showing file selected area');
    }
    
    // Apply visual feedback
    if (uploadArea) {
        uploadArea.classList.remove('border-gray-300', 'hover:border-indigo-500', 'hover:bg-indigo-50');
        uploadArea.classList.add('border-green-400', 'bg-green-50');
        console.log('Applied green visual feedback');
    }
    
    showSuccess(`File "${file.name}" selected successfully!`);
    console.log('File selection display completed!');
}

console.log('Materials.js loaded successfully');


