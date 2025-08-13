// Materials Page - Essential UI Interactions
console.log('Materials.js loaded');

// All required functions for Blade template compatibility
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
    document.getElementById('materialSection').value = sectionName;
};
window.closeSectionModal = () => hideModal('sectionModal');
window.closeMaterialModal = () => hideModal('materialModal');
window.closeEditMaterialModal = () => hideModal('editMaterialModal');
window.toggleMaterialFields = toggleField;
window.editMaterial = (id) => {
    showModal('editMaterialModal');
    document.getElementById('editMaterialId').value = id;
};

// Modal control functions
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
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
    const type = document.getElementById('materialType')?.value;
    const fileField = document.getElementById('fileField');
    const textField = document.getElementById('textField');
    const fileInput = document.getElementById('materialFile');
    const textInput = document.getElementById('materialContent');
    
    if (type === 'file') {
        if (fileField) fileField.style.display = 'block';
        if (textField) textField.style.display = 'none';
        if (fileInput) fileInput.required = true;
        if (textInput) textInput.required = false;
    } else {
        if (fileField) fileField.style.display = 'none';
        if (textField) textField.style.display = 'block';
        if (fileInput) fileInput.required = false;
        if (textInput) textInput.required = true;
    }
}

// File upload handling
function handleFileSelect(event) {
    const file = event.target.files[0];
    if (!file) return;
    
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

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize material type toggle
    if (document.getElementById('materialType')) {
        toggleField();
    }
    
    // File upload area click handler
    const uploadArea = document.getElementById('fileUploadArea');
    const fileInput = document.getElementById('materialFile');
    
    if (uploadArea && fileInput) {
        uploadArea.addEventListener('click', (e) => {
            if (!e.target.closest('button')) {
                fileInput.click();
            }
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
                handleFileSelect({ target: { files } });
            }
        });
    }
});
