// Assignment Creation JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initializeAssignmentForm();
    setupFormValidation();
    setupDateValidation();
    setupFileUpload();
});

/**
 * Initialize the assignment form
 */
function initializeAssignmentForm() {
    console.log('Assignment form initialized');
    
    // Set default dates if not already set
    setDefaultDates();
    
    // Setup submission type change handler
    const submissionType = document.getElementById('submissionType');
    if (submissionType) {
        submissionType.addEventListener('change', handleSubmissionTypeChange);
    }
    
    // Setup course selection change handler
    const courseSelect = document.getElementById('courseId');
    if (courseSelect) {
        courseSelect.addEventListener('change', handleCourseChange);
    }
}

/**
 * Set default dates for the assignment
 */
function setDefaultDates() {
    const assignDate = document.getElementById('assignDate');
    const dueDate = document.getElementById('dueDate');
    
    if (assignDate && !assignDate.value) {
        const now = new Date();
        assignDate.value = formatDateForInput(now);
    }
    
    if (dueDate && !dueDate.value) {
        const weekFromNow = new Date();
        weekFromNow.setDate(weekFromNow.getDate() + 7);
        dueDate.value = formatDateForInput(weekFromNow);
    }
}

/**
 * Format date for datetime-local input
 */
function formatDateForInput(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    
    return `${year}-${month}-${day}T${hours}:${minutes}`;
}

/**
 * Setup form validation
 */
function setupFormValidation() {
    const form = document.getElementById('assignmentForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                showError('Please fill in all required fields correctly.');
            }
        });
    }
}

/**
 * Setup date validation
 */
function setupDateValidation() {
    const assignDate = document.getElementById('assignDate');
    const dueDate = document.getElementById('dueDate');
    
    if (assignDate && dueDate) {
        assignDate.addEventListener('change', validateDates);
        dueDate.addEventListener('change', validateDates);
    }
}

/**
 * Validate dates
 */
function validateDates() {
    const assignDate = document.getElementById('assignDate');
    const dueDate = document.getElementById('dueDate');
    
    if (assignDate.value && dueDate.value) {
        const assign = new Date(assignDate.value);
        const due = new Date(dueDate.value);
        
        if (due <= assign) {
            showError('Due date must be after assign date.');
            dueDate.setCustomValidity('Due date must be after assign date');
        } else {
            dueDate.setCustomValidity('');
            hideError();
        }
    }
}

/**
 * Handle submission type change
 */
function handleSubmissionTypeChange() {
    const submissionType = document.getElementById('submissionType');
    const fileTypesSection = submissionType.closest('.space-y-4').querySelector('[data-file-types]');
    
    if (submissionType.value === 'text') {
        // Hide file types section for text-only submissions
        if (fileTypesSection) {
            fileTypesSection.style.display = 'none';
        }
    } else {
        // Show file types section
        if (fileTypesSection) {
            fileTypesSection.style.display = 'block';
        }
    }
}

/**
 * Handle course selection change
 */
function handleCourseChange() {
    const courseSelect = document.getElementById('courseId');
    console.log('Selected course:', courseSelect.value);
    
    // You can add additional logic here if needed
    // For example, loading course-specific settings
}

/**
 * Setup file upload handling
 */
function setupFileUpload() {
    const fileInput = document.getElementById('instructorFiles');
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            validateFileUpload(e.target.files);
        });
    }
}

/**
 * Validate file upload
 */
function validateFileUpload(files) {
    const maxSize = 10 * 1024 * 1024; // 10MB
    const allowedTypes = ['.pdf', '.docx'];
    
    for (let file of files) {
        // Check file size
        if (file.size > maxSize) {
            showError(`File "${file.name}" is too large. Maximum size is 10MB.`);
            return false;
        }
        
        // Check file type
        const extension = '.' + file.name.split('.').pop().toLowerCase();
        if (!allowedTypes.includes(extension)) {
            showError(`File type "${extension}" is not allowed for "${file.name}". Only PDF and DOCX files are allowed.`);
            return false;
        }
    }
    
    hideError();
    return true;
}

/**
 * Validate the entire form
 */
function validateForm() {
    const requiredFields = [
        'courseId',
        'assignmentTitle',
        'assignDate',
        'dueDate',
        'marks'
    ];
    
    let isValid = true;
    
    for (let fieldId of requiredFields) {
        const field = document.getElementById(fieldId);
        if (field && !field.value.trim()) {
            field.classList.add('border-red-500');
            isValid = false;
        } else if (field) {
            field.classList.remove('border-red-500');
        }
    }
    
    // Validate TinyMCE instructions content
    if (typeof tinymce !== 'undefined') {
        const instructionsEditor = tinymce.get('instructions');
        if (instructionsEditor) {
            const content = instructionsEditor.getContent({format: 'text'}).trim();
            if (!content) {
                showError('Assignment instructions are required.');
                isValid = false;
            }
        }
    } else {
        // Fallback for regular textarea
        const instructionsField = document.getElementById('instructions');
        if (instructionsField && !instructionsField.value.trim()) {
            instructionsField.classList.add('border-red-500');
            isValid = false;
        }
    }
    
    // Validate dates
    const assignDate = document.getElementById('assignDate');
    const dueDate = document.getElementById('dueDate');
    
    if (assignDate.value && dueDate.value) {
        const assign = new Date(assignDate.value);
        const due = new Date(dueDate.value);
        
        if (due <= assign) {
            isValid = false;
        }
    }
    
    // Validate marks
    const marks = document.getElementById('marks');
    if (marks && (marks.value < 1 || marks.value > 100)) {
        marks.classList.add('border-red-500');
        isValid = false;
    }
    
    // Validate max attempts
    const maxAttempts = document.getElementById('maxAttempts');
    if (maxAttempts && (maxAttempts.value < 1 || maxAttempts.value > 5)) {
        maxAttempts.classList.add('border-red-500');
        isValid = false;
    }
    
    return isValid;
}

/**
 * Save assignment as draft
 */
function saveDraft() {
    console.log('Saving draft...');
    
    const statusInput = document.getElementById('assignmentStatus');
    if (statusInput) {
        statusInput.value = 'draft';
    }
    
    // Save TinyMCE content before validation
    if (typeof tinymce !== 'undefined') {
        const editor = tinymce.get('instructions');
        if (editor) {
            editor.save();
        }
    }
    
    if (validateForm()) {
        showLoading('Saving draft...');
        document.getElementById('assignmentForm').submit();
    } else {
        showError('Please fill in all required fields before saving.');
    }
}

/**
 * Publish assignment
 */
function publishAssignment() {
    console.log('Publishing assignment...');
    
    const statusInput = document.getElementById('assignmentStatus');
    if (statusInput) {
        statusInput.value = 'published';
    }
    
    // Save TinyMCE content before validation
    if (typeof tinymce !== 'undefined') {
        const editor = tinymce.get('instructions');
        if (editor) {
            editor.save();
        }
    }
    
    if (validateForm()) {
        // Show confirmation dialog
        if (confirm('Are you sure you want to publish this assignment? Students will be notified immediately.')) {
            showLoading('Publishing assignment...');
            document.getElementById('assignmentForm').submit();
        }
    } else {
        showError('Please fill in all required fields before publishing.');
    }
}

/**
 * Update existing assignment
 */
function updateAssignment() {
    console.log('Updating assignment...');
    
    const statusInput = document.getElementById('assignmentStatus');
    if (statusInput) {
        statusInput.value = 'published';
    }
    
    // Save TinyMCE content before validation
    if (typeof tinymce !== 'undefined') {
        const editor = tinymce.get('instructions');
        if (editor) {
            editor.save();
        }
    }
    
    if (validateForm()) {
        // Show confirmation dialog
        if (confirm('Are you sure you want to update this assignment?')) {
            showLoading('Updating assignment...');
            document.getElementById('assignmentForm').submit();
        }
    } else {
        showError('Please fill in all required fields before updating.');
    }
}

/**
 * Hide error message
 */
function hideError() {
    const errorDiv = document.getElementById('error-message');
    if (errorDiv) {
        errorDiv.remove();
    }
}

/**
 * Show loading message
 */
function showLoading(message) {
    hideError();
    
    const loadingDiv = document.createElement('div');
    loadingDiv.className = 'fixed top-4 right-4 bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded z-50';
    loadingDiv.innerHTML = `
        <div class="flex items-center">
            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-700 mr-2"></div>
            <span>${message}</span>
        </div>
    `;
    loadingDiv.id = 'loading-message';
    
    document.body.appendChild(loadingDiv);
}

/**
 * Hide loading message
 */
function hideLoading() {
    const loadingDiv = document.getElementById('loading-message');
    if (loadingDiv) {
        loadingDiv.remove();
    }
}

/**
 * Hide success message
 */
function hideSuccess() {
    const successDiv = document.getElementById('success-message');
    if (successDiv) {
        successDiv.remove();
    }
}

// Expose functions globally for onclick handlers
window.saveDraft = saveDraft;
window.publishAssignment = publishAssignment;
window.updateAssignment = updateAssignment;
window.hideError = hideError;
