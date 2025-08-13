// Materials Page JavaScript Functions

// Show toast notification
function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    toast.innerHTML = `
        <div class="toast-icon">
            <i class="fas fa-${type === 'success' ? 'check-circle' : (type === 'error' ? 'exclamation-circle' : 'info-circle')}"></i>
        </div>
        <div class="flex-1">${message}</div>
        <div class="toast-close" onclick="this.parentElement.remove()">×</div>
    `;
    
    container.appendChild(toast);
    
    // Trigger animation
    setTimeout(() => toast.classList.add('show'), 100);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (toast.parentElement) {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 400);
        }
    }, 5000);
}

// Section management
function addNewSection() {
    document.getElementById('sectionModal').classList.remove('hidden');
    document.getElementById('sectionModal').classList.add('flex');
}

function closeSectionModal() {
    document.getElementById('sectionModal').classList.add('hidden');
    document.getElementById('sectionModal').classList.remove('flex');
    document.getElementById('sectionForm').reset();
}

// Material management
function addNewMaterial() {
    document.getElementById('materialModal').classList.remove('hidden');
    document.getElementById('materialModal').classList.add('flex');
}

function addContentToSection(sectionName) {
    document.getElementById('materialModal').classList.remove('hidden');
    document.getElementById('materialModal').classList.add('flex');
    document.getElementById('materialSection').value = sectionName;
}

function closeMaterialModal() {
    document.getElementById('materialModal').classList.add('hidden');
    document.getElementById('materialModal').classList.remove('flex');
    document.getElementById('materialForm').reset();
    toggleMaterialFields();
}

function toggleMaterialFields() {
    const type = document.getElementById('materialType').value;
    const fileField = document.getElementById('fileField');
    const textField = document.getElementById('textField');
    const fileInput = document.getElementById('materialFile');
    const textInput = document.getElementById('materialContent');
    
    if (type === 'file') {
        fileField.style.display = 'block';
        textField.style.display = 'none';
        fileInput.required = true;
        textInput.required = false;
    } else {
        fileField.style.display = 'none';
        textField.style.display = 'block';
        fileInput.required = false;
        textInput.required = true;
    }
}

// Edit material
function editMaterial(materialId) {
    // This function would fetch material data and populate the edit modal
    // For now, just show the modal
    document.getElementById('editMaterialModal').classList.remove('hidden');
    document.getElementById('editMaterialModal').classList.add('flex');
    document.getElementById('editMaterialId').value = materialId;
}

function closeEditMaterialModal() {
    document.getElementById('editMaterialModal').classList.add('hidden');
    document.getElementById('editMaterialModal').classList.remove('flex');
    document.getElementById('editMaterialForm').reset();
}

// Toggle material privacy
function toggleMaterialPrivacy(materialId, isPrivate) {
    // This would be an AJAX call to update privacy
    console.log(`Toggle privacy for material ${materialId}, current: ${isPrivate}`);
}

// Scroll to section
function scrollToSection(elementId) {
    document.getElementById(elementId)?.scrollIntoView({ behavior: 'smooth' });
}

// Toggle section
function toggleSection(sectionName) {
    const section = document.querySelector(`[data-section="${sectionName}"] .section-content`);
    const toggle = document.querySelector(`[data-section="${sectionName}"] .section-toggle`);
    
    if (section.style.maxHeight === 'none') {
        section.style.maxHeight = '0';
        toggle.style.transform = 'rotate(-90deg)';
    } else {
        section.style.maxHeight = 'none';
        toggle.style.transform = 'rotate(0deg)';
    }
}

// Edit section
function editSection(sectionName) {
    // This would open a modal to edit section name
    console.log(`Edit section: ${sectionName}`);
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Set default material type
    if (document.getElementById('materialType')) {
        toggleMaterialFields();
    }
    
    // Auto-hide flash messages after 5 seconds
    const flashMessages = document.querySelectorAll('[class*="animate-slide-down"]');
    flashMessages.forEach(function(message) {
        setTimeout(function() {
            if (message && message.parentElement) {
                message.style.animation = 'fade-out 0.4s ease-out forwards';
                setTimeout(function() {
                    if (message.parentElement) {
                        message.parentElement.remove();
                    }
                }, 400);
            }
        }, 5000);
    });
});

// Legacy functions for backward compatibility
function toggleAddSection() {
    addNewSection();
}

function toggleAddMaterial(sectionName) {
    if (sectionName) {
        addContentToSection(sectionName);
    } else {
        addNewMaterial();
    }
}
