// Assignment Submissions Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initializeSearch();
    initializeFilters();
    initializeModals();
    initializeForms();
});

/**
 * Initialize search functionality
 */
function initializeSearch() {
    const searchInput = document.getElementById('searchSubmissions');
    if (!searchInput) return;

    searchInput.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        filterSubmissions(searchTerm);
    });
}

/**
 * Initialize filter functionality
 */
function initializeFilters() {
    const filterSelect = document.getElementById('filterSubmissions');
    if (!filterSelect) return;

    filterSelect.addEventListener('change', function(e) {
        const filterValue = e.target.value;
        applyStatusFilter(filterValue);
    });
}

/**
 * Filter submissions by search term
 */
function filterSubmissions(searchTerm) {
    const submissionRows = document.querySelectorAll('.submission-row');
    
    submissionRows.forEach(row => {
        const studentName = row.getAttribute('data-student-name').toLowerCase();
        const studentEmail = row.querySelector('.text-gray-500').textContent.toLowerCase();
        
        if (studentName.includes(searchTerm) || studentEmail.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

/**
 * Apply status filter to submissions
 */
function applyStatusFilter(filterValue) {
    const submissionRows = document.querySelectorAll('.submission-row');
    
    submissionRows.forEach(row => {
        const gradeCell = row.cells[3]; // Grade column
        const statusCell = row.cells[2]; // Status column
        
        const hasGrade = !gradeCell.querySelector('.bg-yellow-100'); // Not pending
        const isLate = statusCell.querySelector('.bg-red-100'); // Late submission
        
        let shouldShow = false;
        
        switch(filterValue) {
            case 'all':
                shouldShow = true;
                break;
            case 'graded':
                shouldShow = hasGrade;
                break;
            case 'pending':
                shouldShow = !hasGrade;
                break;
            case 'late':
                shouldShow = !!isLate;
                break;
        }
        
        row.style.display = shouldShow ? '' : 'none';
    });
}

/**
 * Initialize modal functionality
 */
function initializeModals() {
    // Close modals when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('bg-black')) {
            closeAllModals();
        }
    });

    // Close modals with escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAllModals();
        }
    });
}

/**
 * Initialize form handlers
 */
function initializeForms() {
    // Grade form submission
    const gradeForm = document.getElementById('gradeForm');
    if (gradeForm) {
        gradeForm.addEventListener('submit', handleGradeSubmission);
    }

    // Update marks form submission
    const updateMarksForm = document.getElementById('updateMarksForm');
    if (updateMarksForm) {
        updateMarksForm.addEventListener('submit', handleUpdateMarks);
    }
}

/**
 * View submission details
 */
function viewSubmission(submissionId) {
    // Show loading state
    showLoadingToast('Loading submission details...');
    
    // Navigate to submission detail page
    window.location.href = `/assignments/submissions/${submissionId}/view`;
}

/**
 * Open grade modal for specific submission
 */
let currentSubmissionId = null;

function gradeSubmission(submissionId) {
    currentSubmissionId = submissionId;
    
    // Find the submission row to get current data
    const row = document.querySelector(`[onclick*="${submissionId}"]`).closest('tr');
    const gradeCell = row.cells[3];
    
    // Check if submission is already graded
    const currentGradeElement = gradeCell.querySelector('.text-gray-900');
    const feedbackElement = row.querySelector('[data-feedback]');
    
    // Fill modal with existing data if available
    const gradeInput = document.getElementById('gradeInput');
    const feedbackInput = document.getElementById('feedbackInput');
    
    if (currentGradeElement) {
        const gradeText = currentGradeElement.textContent.trim();
        const grade = gradeText.split('/')[0];
        gradeInput.value = grade;
    } else {
        gradeInput.value = '';
    }
    
    if (feedbackElement) {
        feedbackInput.value = feedbackElement.getAttribute('data-feedback') || '';
    } else {
        feedbackInput.value = '';
    }
    
    // Show modal
    const modal = document.getElementById('gradeModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    gradeInput.focus();
}

/**
 * Close grade modal
 */
function closeGradeModal() {
    const modal = document.getElementById('gradeModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    currentSubmissionId = null;
}

/**
 * Handle grade form submission
 */
function handleGradeSubmission(e) {
    e.preventDefault();
    
    if (!currentSubmissionId) return;
    
    const gradeInput = document.getElementById('gradeInput');
    const feedbackInput = document.getElementById('feedbackInput');
    const maxMarks = parseInt(gradeInput.getAttribute('max'));
    
    const grade = parseFloat(gradeInput.value);
    const feedback = feedbackInput.value.trim();
    
    // Validate grade
    if (isNaN(grade) || grade < 0 || grade > maxMarks) {
        showErrorToast(`Grade must be between 0 and ${maxMarks}`);
        return;
    }
    
    // Show loading
    const submitButton = e.target.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
    submitButton.disabled = true;
    
    // Submit grade
    fetch(`/assignments/submissions/${currentSubmissionId}/grade`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            grade: grade,
            feedback: feedback
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccessToast('Grade saved successfully!');
            updateSubmissionRow(currentSubmissionId, grade, maxMarks, feedback);
            closeGradeModal();
            updateStatistics();
        } else {
            showErrorToast(data.message || 'Failed to save grade');
        }
    })
    .catch(error => {
        showErrorToast('Network error occurred');
        console.error('Error:', error);
    })
    .finally(() => {
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    });
}

/**
 * Update submission row with new grade
 */
function updateSubmissionRow(submissionId, grade, maxMarks, feedback) {
    const row = document.querySelector(`[onclick*="gradeSubmission(${submissionId})"]`).closest('tr');
    const gradeCell = row.cells[3];
    const percentage = ((grade / maxMarks) * 100).toFixed(1);
    
    gradeCell.innerHTML = `
        <div class="text-sm font-medium text-gray-900">${grade}/${maxMarks}</div>
        <div class="text-sm text-gray-500">${percentage}%</div>
    `;
    
    // Update action button
    const actionButton = row.querySelector('[onclick*="gradeSubmission"]');
    actionButton.className = actionButton.className.replace('bg-blue-100 text-blue-800 hover:bg-blue-200', 'bg-green-100 text-green-800 hover:bg-green-200');
    actionButton.innerHTML = '<i class="fas fa-edit mr-1"></i>Edit Grade';
    
    // Store feedback data
    if (feedback) {
        row.setAttribute('data-feedback', feedback);
    }
}

/**
 * Download submission files
 */
function downloadSubmission(submissionId) {
    showLoadingToast('Preparing download...');
    
    // Create download link
    const downloadUrl = `/assignments/submissions/${submissionId}/download`;
    const link = document.createElement('a');
    link.href = downloadUrl;
    link.download = '';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    showSuccessToast('Download started');
}

/**
 * Open update assignment marks modal
 */
function updateAssignmentMarks() {
    const modal = document.getElementById('updateMarksModal');
    const newMarksInput = document.getElementById('newMarksInput');
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    newMarksInput.focus();
}

/**
 * Close update marks modal
 */
function closeUpdateMarksModal() {
    const modal = document.getElementById('updateMarksModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

/**
 * Handle update marks form submission
 */
function handleUpdateMarks(e) {
    e.preventDefault();
    
    const newMarksInput = document.getElementById('newMarksInput');
    const updateOption = document.getElementById('updateOption');
    
    const newMarks = parseFloat(newMarksInput.value);
    const option = updateOption.value;
    
    // Validate input
    if (isNaN(newMarks) || newMarks <= 0 || newMarks > 1000) {
        showErrorToast('Please enter a valid marks value between 1 and 1000');
        return;
    }
    
    // Show confirmation
    if (!confirm(`Are you sure you want to update the total marks to ${newMarks}? This will affect all submissions.`)) {
        return;
    }
    
    // Show loading
    const submitButton = e.target.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Updating...';
    submitButton.disabled = true;
    
    // Get assignment ID from URL or data attribute
    const assignmentId = getAssignmentId();
    
    // Submit update
    fetch(`/assignments/${assignmentId}/update-marks`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            new_marks: newMarks,
            update_option: option
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccessToast('Assignment marks updated successfully!');
            closeUpdateMarksModal();
            // Reload page to show updated data
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showErrorToast(data.message || 'Failed to update marks');
        }
    })
    .catch(error => {
        showErrorToast('Network error occurred');
        console.error('Error:', error);
    })
    .finally(() => {
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    });
}

/**
 * Bulk grade functionality
 */
function bulkGrade() {
    const pendingSubmissions = document.querySelectorAll('.bg-yellow-100').length;
    
    if (pendingSubmissions === 0) {
        showInfoToast('No pending submissions to grade');
        return;
    }
    
    const grade = prompt(`Enter grade for all ${pendingSubmissions} pending submissions:`);
    
    if (grade === null) return; // User cancelled
    
    const gradeValue = parseFloat(grade);
    const maxMarks = parseInt(document.getElementById('gradeInput').getAttribute('max'));
    
    if (isNaN(gradeValue) || gradeValue < 0 || gradeValue > maxMarks) {
        showErrorToast(`Grade must be between 0 and ${maxMarks}`);
        return;
    }
    
    if (!confirm(`Are you sure you want to assign ${gradeValue} marks to all pending submissions?`)) {
        return;
    }
    
    // Show loading
    showLoadingToast('Applying bulk grades...');
    
    // Get assignment ID
    const assignmentId = getAssignmentId();
    
    // Submit bulk grade
    fetch(`/assignments/${assignmentId}/bulk-grade`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            grade: gradeValue
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccessToast(`Successfully graded ${data.count} submissions!`);
            // Reload page to show updated data
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showErrorToast(data.message || 'Failed to apply bulk grades');
        }
    })
    .catch(error => {
        showErrorToast('Network error occurred');
        console.error('Error:', error);
    });
}

/**
 * Export submissions to CSV
 */
function exportSubmissions() {
    showLoadingToast('Preparing export...');
    
    const assignmentId = getAssignmentId();
    
    // Create export URL
    const exportUrl = `/assignments/${assignmentId}/export-submissions`;
    
    // Create download link
    const link = document.createElement('a');
    link.href = exportUrl;
    link.download = '';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    showSuccessToast('Export started');
}

/**
 * Update statistics after grading
 */
function updateStatistics() {
    // This would typically refetch and update the statistics
    // For now, we'll reload the page to get fresh data
    setTimeout(() => window.location.reload(), 2000);
}

/**
 * Get assignment ID from URL
 */
function getAssignmentId() {
    const url = window.location.pathname;
    const matches = url.match(/\/assignments\/(\d+)/);
    return matches ? matches[1] : null;
}

/**
 * Close all modals
 */
function closeAllModals() {
    closeGradeModal();
    closeUpdateMarksModal();
}

/**
 * Toast notification functions
 */
function showSuccessToast(message) {
    showToast(message, 'success');
}

function showErrorToast(message) {
    showToast(message, 'error');
}

function showInfoToast(message) {
    showToast(message, 'info');
}

function showLoadingToast(message) {
    showToast(message, 'loading');
}

function showToast(message, type = 'info') {
    // Remove existing toasts
    const existingToast = document.getElementById('toast');
    if (existingToast) {
        existingToast.remove();
    }
    
    // Create toast
    const toast = document.createElement('div');
    toast.id = 'toast';
    toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 transition-all duration-300 ${getToastClasses(type)}`;
    
    // Toast content
    toast.innerHTML = `
        <div class="flex items-center gap-2">
            ${getToastIcon(type)}
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove (except loading toasts)
    if (type !== 'loading') {
        setTimeout(() => {
            if (toast.parentNode) {
                toast.classList.add('opacity-0', 'transform', 'translate-x-full');
                setTimeout(() => toast.remove(), 300);
            }
        }, 4000);
    }
}

function getToastClasses(type) {
    switch(type) {
        case 'success': return 'bg-green-500 text-white';
        case 'error': return 'bg-red-500 text-white';
        case 'loading': return 'bg-blue-500 text-white';
        default: return 'bg-gray-800 text-white';
    }
}

function getToastIcon(type) {
    switch(type) {
        case 'success': return '<i class="fas fa-check-circle"></i>';
        case 'error': return '<i class="fas fa-exclamation-circle"></i>';
        case 'loading': return '<i class="fas fa-spinner fa-spin"></i>';
        default: return '<i class="fas fa-info-circle"></i>';
    }
}

// Make functions globally available
window.viewSubmission = viewSubmission;
window.gradeSubmission = gradeSubmission;
window.downloadSubmission = downloadSubmission;
window.updateAssignmentMarks = updateAssignmentMarks;
window.bulkGrade = bulkGrade;
window.exportSubmissions = exportSubmissions;
window.closeGradeModal = closeGradeModal;
window.closeUpdateMarksModal = closeUpdateMarksModal;
