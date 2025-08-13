// Materials Page - Enhanced UI Interactions
console.log('Materials.js loading...');

// Test function for immediate availability
window.testShowModal = function() {
    console.log('testShowModal called - this proves JS is loaded');
    const modal = document.getElementById('materialModal');
    console.log('materialModal element:', modal);
    if (modal) {
        modal.style.display = 'flex';
        modal.classList.remove('hidden');
        console.log('Modal should be visible now');
    }
};

// Simple immediate function exposure
window.showModal = function(modalId) {
    console.log('Direct showModal called with:', modalId);
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
    console.log('Direct hideModal called with:', modalId);
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        console.log('Modal hidden:', modalId);
    }
};

console.log('Direct functions exposed');

// Ensure DOM is ready before initializing
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeMaterials);
} else {
    initializeMaterials();
}

function initializeMaterials() {
    console.log('Materials.js initialized - DOM ready');
    
    // Expose functions globally for Blade template (ensure they're available)
    window.showModal = showModal;
    window.hideModal = hideModal;
    window.toggleField = toggleField;
    window.handleFileSelect = handleFileSelect;
    window.clearFileSelection = clearFileSelection;
    
    // Legacy function names for backward compatibility
    window.addNewSection = () => showModal('sectionModal');
    window.addNewMaterial = () => showModal('materialModal');
    window.addContentToSection = (sectionName) => {
        showModal('materialModal');
        const sectionInput = document.getElementById('materialSection');
        if (sectionInput) sectionInput.value = sectionName;
    };
    window.closeSectionModal = () => hideModal('sectionModal');
    window.closeMaterialModal = () => hideModal('materialModal');
    window.closeEditMaterialModal = () => hideModal('editMaterialModal');
    window.toggleMaterialFields = toggleField;
    window.editMaterial = (id) => {
        showModal('editMaterialModal');
        const idInput = document.getElementById('editMaterialId');
        if (idInput) idInput.value = id;
    };
    
    // Add event listeners to all Add Material buttons
    setupAddMaterialButtons();
    
    // Initialize components
    setupFileUpload();
    setupMaterialTypeToggle();
    
    console.log('All functions exposed to window:', {
        showModal: typeof window.showModal,
        hideModal: typeof window.hideModal,
        addNewMaterial: typeof window.addNewMaterial
    });
}

// Setup Add Material buttons with event listeners
function setupAddMaterialButtons() {
    // Find all Add Material buttons
    const addMaterialButtons = document.querySelectorAll('button[onclick*="materialModal"]');
    console.log('Found Add Material buttons:', addMaterialButtons.length);
    
    addMaterialButtons.forEach((button, index) => {
        console.log(`Setting up Add Material button ${index + 1}`);
        
        // Remove existing onclick to avoid conflicts
        button.removeAttribute('onclick');
        
        // Add new click event listener
        button.addEventListener('click', (e) => {
            e.preventDefault();
            console.log(`Add Material button ${index + 1} clicked`);
            showModal('materialModal');
            
            // Check if this button is for a specific section
            const sectionValue = button.getAttribute('data-section') || 
                                button.closest('[data-section]')?.getAttribute('data-section');
            
            if (sectionValue) {
                const sectionInput = document.getElementById('materialSection');
                if (sectionInput) sectionInput.value = sectionValue;
            }
        });
    });
}

// Modal control functions
function showModal(modalId) {
    console.log('showModal called with ID:', modalId);
    const modal = document.getElementById(modalId);
    if (modal) {
        console.log('Modal found, showing modal:', modalId);
        console.log('Modal current classes:', modal.className);
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        console.log('Modal new classes:', modal.className);
        console.log('Modal display style:', window.getComputedStyle(modal).display);
    } else {
        console.error('Modal not found with ID:', modalId);
        console.log('Available elements with IDs:', 
            Array.from(document.querySelectorAll('[id]')).map(el => el.id)
        );
    }
}

function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        console.log('Hiding modal:', modalId);
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        const form = modal.querySelector('form');
        if (form) form.reset();
        // Clear file selection when closing material modal
        if (modalId === 'materialModal') {
            clearFileSelection();
        }
    }
}

// Toggle material type fields
function toggleField() {
    const materialType = document.getElementById('materialType');
    if (!materialType) return;
    
    const selectedType = materialType.value;
    const fileField = document.getElementById('fileField');
    const textField = document.getElementById('textField');
    const urlField = document.getElementById('urlField');
    
    // Hide all fields first
    if (fileField) fileField.style.display = 'none';
    if (textField) textField.style.display = 'none';
    if (urlField) urlField.style.display = 'none';
    
    // Show relevant field
    if (selectedType === 'file' && fileField) {
        fileField.style.display = 'block';
    } else if (selectedType === 'text' && textField) {
        textField.style.display = 'block';
    } else if (selectedType === 'url' && urlField) {
        urlField.style.display = 'block';
    }
}

// Setup file upload functionality
function setupFileUpload() {
    const uploadArea = document.getElementById('fileUploadArea');
    const fileInput = document.getElementById('materialFile');
    const browseButton = document.getElementById('browseFilesButton');
    
    if (!uploadArea || !fileInput) {
        console.warn('File upload elements not found:', {
            uploadArea: !!uploadArea,
            fileInput: !!fileInput,
            browseButton: !!browseButton
        });
        return;
    }
    
    console.log('Setting up file upload - elements found:', {
        uploadArea: !!uploadArea,
        fileInput: !!fileInput,
        browseButton: !!browseButton
    });
    
    // Click handler specifically for browse button
    if (browseButton) {
        browseButton.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            console.log('Browse Files button clicked - opening file dialog');
            fileInput.click();
        });
        console.log('Browse button event listener added');
    } else {
        console.warn('Browse button not found with ID: browseFilesButton');
    }
    
    // Click handler for upload area (except browse button)
    uploadArea.addEventListener('click', (e) => {
        // Don't trigger file input if clicking the browse button specifically
        if (!e.target.closest('#browseFilesButton')) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Upload area clicked - opening file dialog');
            fileInput.click();
        }
    });
    
    // File input change handler - MOST CRITICAL PART
    fileInput.addEventListener('change', function(event) {
        console.log('🔥 FILE INPUT CHANGE EVENT FIRED!');
        console.log('Event target:', event.target);
        console.log('Files in input:', event.target.files);
        console.log('File count:', event.target.files.length);
        
        const file = event.target.files[0];
        if (file) {
            console.log('✅ File detected:', {
                name: file.name,
                size: file.size,
                type: file.type
            });
            showSelectedFile(file);
        } else {
            console.log('❌ No file in input');
        }
    });
    
    uploadArea.addEventListener('dragleave', (e) => {// Additional event listeners for debugging
        e.preventDefault();
        if (!uploadArea.contains(e.relatedTarget)) {
            uploadArea.classList.remove('border-indigo-500', 'bg-indigo-50');
            uploadArea.classList.add('border-gray-300');
        }
    });
    fileInput.addEventListener('input', function(event) {
        console.log('📝 FILE INPUT - INPUT EVENT');
        const file = event.target.files[0];
        if (file) {
            showSelectedFile(file);
        }
    });
    
    console.log('File input event listeners attached');
    
    // Drag and drop functionality
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('border-indigo-500', 'bg-indigo-50');
        console.log('File dragged over upload area');
    });
    
    uploadArea.addEventListener('dragleave', (e) => {
        e.preventDefault();
        if (!uploadArea.classList.contains('border-green-400')) {
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
            handleFileSelect({ target: fileInput });
        }
    });
}

// Setup material type toggle
function setupMaterialTypeToggle() {
    const materialType = document.getElementById('materialType');
    if (materialType) {
        materialType.addEventListener('change', toggleField);
        toggleField(); // Initialize with current value
    }
}

// File selection handler - SIMPLIFIED AND WORKING
function showSelectedFile(file) {
    console.log('🎯 showSelectedFile called with:', file.name);
    
    const uploadArea = document.getElementById('fileUploadArea');
    const uploadContent = uploadArea?.querySelector('.upload-content');
    const fileSelected = uploadArea?.querySelector('.file-selected');
    const fileName = document.getElementById('selectedFileName');
    const fileSize = document.getElementById('selectedFileSize');
    
    console.log('Elements check:', {
        uploadArea: !!uploadArea,
        uploadContent: !!uploadContent,
        fileSelected: !!fileSelected,
        fileName: !!fileName,
        fileSize: !!fileSize
    });
    
    // Update file information
    if (fileName) {
        fileName.textContent = file.name;
        console.log('✅ Set fileName to:', file.name);
    }
    
    if (fileSize) {
        const sizeMB = (file.size / 1024 / 1024).toFixed(2);
        fileSize.textContent = `Size: ${sizeMB} MB`;
        console.log('✅ Set fileSize to:', `Size: ${sizeMB} MB`);
    }
    
    // Toggle visibility - hide upload content, show file selected
    if (uploadContent) {
        uploadContent.style.display = 'none';
        console.log('✅ Hidden upload content');
    }
    
    if (fileSelected) {
        fileSelected.style.display = 'block';
        fileSelected.classList.remove('hidden');
        console.log('✅ Showing file selected area');
    }
    
    // Apply visual feedback to upload area
    if (uploadArea) {
        uploadArea.classList.remove('border-gray-300', 'hover:border-indigo-500', 'hover:bg-indigo-50');
        uploadArea.classList.add('border-green-400', 'bg-green-50');
        console.log('✅ Applied green visual feedback');
    }
    
    console.log('🎉 File selection display completed!');
}

function clearFileSelection() {
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
}


