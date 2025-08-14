// Assignment Show Page JavaScript - UI Interactions Only
document.addEventListener('DOMContentLoaded', function() {
    initializeFileUpload();
    initializeDragAndDrop();
    initializeFormUI();
    initializeTooltips();
});

/**
 * Initialize file upload UI functionality
 */
function initializeFileUpload() {
    const fileInput = document.getElementById('fileInput');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    
    if (!fileInput) return;

    fileInput.addEventListener('change', function(e) {
        const files = e.target.files;
        if (files.length > 0) {
            displaySelectedFiles(files);
        } else {
            hideFileInfo();
        }
    });
}

/**
 * Initialize drag and drop functionality
 */
function initializeDragAndDrop() {
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    
    if (!dropZone || !fileInput) return;

    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });

    // Highlight drop zone when item is dragged over it
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, unhighlight, false);
    });

    // Handle dropped files
    dropZone.addEventListener('drop', handleDrop, false);

    // Make drop zone clickable
    dropZone.addEventListener('click', function(e) {
        if (e.target === dropZone || e.target.closest('#dropZoneContent')) {
            fileInput.click();
        }
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    function highlight() {
        dropZone.classList.add('border-blue-400', 'bg-blue-50');
    }

    function unhighlight() {
        dropZone.classList.remove('border-blue-400', 'bg-blue-50');
    }

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        fileInput.files = files;
        displaySelectedFiles(files);
    }
}

/**
 * Display selected files information - UI only
 */
function displaySelectedFiles(files) {
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    
    if (!fileInfo || !fileName) return;

    if (files.length === 1) {
        fileName.textContent = `${files[0].name} (${formatFileSize(files[0].size)})`;
    } else {
        fileName.textContent = `${files.length} files selected`;
    }
    
    fileInfo.classList.remove('hidden');
}

/**
 * Hide file information
 */
function hideFileInfo() {
    const fileInfo = document.getElementById('fileInfo');
    if (fileInfo) {
        fileInfo.classList.add('hidden');
    }
}

/**
 * Clear selected file
 */
function clearFile() {
    const fileInput = document.getElementById('fileInput');
    if (fileInput) {
        fileInput.value = '';
        hideFileInfo();
    }
}

/**
 * Format file size in human readable format
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

/**
 * Initialize form UI interactions and validation
 */
function initializeFormUI() {
    const form = document.getElementById('submissionForm');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        // Get assignment submission type from data attribute or form context
        const submissionTypeElement = document.querySelector('[data-submission-type]');
        const submissionType = submissionTypeElement ? submissionTypeElement.dataset.submissionType : 'file';
        
        // Validate based on submission type
        const fileInput = document.getElementById('fileInput');
        const textInput = document.querySelector('textarea[name="submission_text"]');
        
        let hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
        let hasText = textInput && textInput.value.trim().length > 0;
        
        // Validation logic
        if (submissionType === 'file' && !hasFile) {
            e.preventDefault();
            showError('Please select at least one file to upload.');
            return false;
        }
        
        if (submissionType === 'text' && !hasText) {
            e.preventDefault();
            showError('Please enter your assignment text.');
            return false;
        }
        
        if (submissionType === 'both' && !hasFile && !hasText) {
            e.preventDefault();
            showError('Please provide either a file upload or text submission.');
            return false;
        }
        
        // Show loading state
        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            const originalText = submitButton.innerHTML;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Submitting...';
            submitButton.disabled = true;
            
            // Re-enable button after 30 seconds as fallback
            setTimeout(() => {
                submitButton.innerHTML = originalText;
                submitButton.disabled = false;
            }, 30000);
        }
    });
}

/**
 * Initialize tooltips for better UX - UI only
 */
function initializeTooltips() {
    const statusBadges = document.querySelectorAll('[data-tooltip]');
    statusBadges.forEach(badge => {
        badge.addEventListener('mouseenter', showTooltip);
        badge.addEventListener('mouseleave', hideTooltip);
    });
}

/**
 * Show tooltip - UI interaction
 */
function showTooltip(e) {
    const tooltip = document.createElement('div');
    tooltip.className = 'absolute bg-gray-800 text-white text-xs rounded py-1 px-2 z-50';
    tooltip.textContent = e.target.dataset.tooltip;
    tooltip.id = 'tooltip';
    
    document.body.appendChild(tooltip);
    
    // Position tooltip
    const rect = e.target.getBoundingClientRect();
    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
    tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + 'px';
}

/**
 * Hide tooltip - UI interaction
 */
function hideTooltip() {
    const tooltip = document.getElementById('tooltip');
    if (tooltip) {
        tooltip.remove();
    }
}

// Make functions globally available for UI interactions only
window.clearFile = clearFile;

/**
 * Show error message using notification system
 */
function showError(message) {
    if (window.notificationManager) {
        window.notificationManager.show(message, 'error');
    } else if (window.showError) {
        window.showError(message);
    } else {
        alert(message);
    }
}
window.showSuccessMessage = showSuccessMessage;
