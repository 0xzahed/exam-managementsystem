// Materials Page - Enhanced UI Interactions
console.log('Materials.js loading...');

// Ensure DOM is ready before initializing
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeMaterials);
} else {
    initializeMaterials();
}

function initializeMaterials() {
    console.log('Materials.js initialized');
    
    // Expose functions globally for Blade template
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
    
    // Initialize components
    setupFileUpload();
    setupMaterialTypeToggle();
}

// Modal control functions
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        console.log('Showing modal:', modalId);
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    } else {
        console.error('Modal not found:', modalId);
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
    
    if (!uploadArea || !fileInput) {
        console.warn('File upload elements not found');
        return;
    }
    
    console.log('Setting up file upload');
    
    // Click handler for upload area
    uploadArea.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        console.log('Upload area clicked');
        fileInput.click();
    });
    
    // File input change handler
    fileInput.addEventListener('change', (e) => {
        console.log('File input changed');
        handleFileSelect(e);
    });
    
    // Drag and drop functionality
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('border-indigo-500', 'bg-indigo-50');
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

// File selection handler
function handleFileSelect(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    console.log('File selected:', file.name);
    
    const uploadArea = document.getElementById('fileUploadArea');
    const uploadContent = uploadArea?.querySelector('.upload-content');
    const fileSelected = uploadArea?.querySelector('.file-selected');
    const fileName = document.getElementById('selectedFileName');
    const fileSize = document.getElementById('selectedFileSize');
    
    if (fileName) fileName.textContent = file.name;
    if (fileSize) fileSize.textContent = `Size: ${(file.size / 1024 / 1024).toFixed(2)} MB`;
    
    // Toggle visibility
    if (uploadContent) uploadContent.classList.add('hidden');
    if (fileSelected) fileSelected.classList.remove('hidden');
    
    // Visual feedback
    if (uploadArea) {
        uploadArea.classList.remove('border-gray-300');
        uploadArea.classList.add('border-green-400', 'bg-green-50');
    }
}

function clearFileSelection() {
    console.log('Clearing file selection');
    
    const fileInput = document.getElementById('materialFile');
    const uploadArea = document.getElementById('fileUploadArea');
    const uploadContent = uploadArea?.querySelector('.upload-content');
    const fileSelected = uploadArea?.querySelector('.file-selected');
    
    if (fileInput) fileInput.value = '';
    
    // Toggle visibility
    if (fileSelected) fileSelected.classList.add('hidden');
    if (uploadContent) uploadContent.classList.remove('hidden');
    
    // Reset visual feedback
    if (uploadArea) {
        uploadArea.classList.remove('border-green-400', 'bg-green-50');
        uploadArea.classList.add('border-gray-300');
    }
}
