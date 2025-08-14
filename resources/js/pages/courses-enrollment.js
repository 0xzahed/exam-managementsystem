// Student Enrollment JavaScript - Simple UI
document.addEventListener('DOMContentLoaded', function() {
    initializeEnrollmentUI();
});

let currentCourseId = null;
let currentCourseName = '';

/**
 * Initialize enrollment UI system
 */
function initializeEnrollmentUI() {
    // Basic initialization only
    setupModalEvents();
}

/**
 * Setup modal events
 */
function setupModalEvents() {
    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeEnrollModal();
        }
    });
    
    // Close modal on outside click
    const enrollModal = document.getElementById('enrollModal');
    if (enrollModal) {
        enrollModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeEnrollModal();
            }
        });
    }
}

/**
 * Show enrollment modal - Simple popup
 */
function enrollCourse(courseId) {
    currentCourseId = courseId;
    
    // Find course name from the card
    const courseCard = document.querySelector(`[onclick*="enrollCourse('${courseId}')"]`)?.closest('.course-card');
    const courseName = courseCard?.querySelector('h3')?.textContent?.trim() || 'this course';
    
    currentCourseName = courseName;
    
    // Update modal content
    const enrollMessage = document.getElementById('enrollMessage');
    const passwordSection = document.getElementById('passwordSection');
    const modal = document.getElementById('enrollModal');
    
    if (enrollMessage) {
        enrollMessage.textContent = `Are you sure you want to enroll in ${courseName}?`;
    }
    
    // Show password section
    if (passwordSection) {
        passwordSection.classList.remove('hidden');
    }
    
    // Show modal
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    
    // Focus on password input
    const passwordInput = document.getElementById('coursePassword');
    if (passwordInput) {
        passwordInput.value = '';
        passwordInput.focus();
    }
    
    // Clear any previous errors
    clearPasswordError();
}

/**
 * Confirm enrollment - Submit to controller
 */
function confirmEnrollment() {
    if (!currentCourseId) {
        showError('Course not selected. Please try again.');
        return;
    }
    
    const passwordInput = document.getElementById('coursePassword');
    const password = passwordInput?.value.trim();
    
    if (!password) {
        showPasswordError('Please enter the course password.');
        passwordInput?.focus();
        return;
    }
    
    // Clear errors
    clearPasswordError();
    
    // Show loading state
    setLoadingState(true);
    
    // Submit to controller
    submitEnrollment(password);
}

/**
 * Submit enrollment to PHP controller
 */
function submitEnrollment(password) {
    const formData = new FormData();
    formData.append('password', password);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    fetch(`/student/courses/${currentCourseId}/enroll`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
    .then(response => response.json())
    .then(data => {
        setLoadingState(false);
        
        if (data.success) {
            closeEnrollModal();
            showSuccessModal(data.message);
            } else {
                if (data.message && data.message.toLowerCase().includes('password')) {
                    showPasswordError(data.message);
                } else {
                    showError(data.message || 'Enrollment failed. Please try again.');
                }
            }
    })
    .catch(error => {
        console.error('Enrollment error:', error);
        setLoadingState(false);
        showError('Network error. Please check your connection and try again.');
    });
}

/**
 * Show password error
 */
function showPasswordError(message) {
    const passwordInput = document.getElementById('coursePassword');
    let errorElement = document.getElementById('passwordError');
    
    if (!errorElement) {
        errorElement = document.createElement('p');
        errorElement.id = 'passwordError';
        errorElement.className = 'text-red-600 text-sm mt-2';
        passwordInput?.parentNode?.appendChild(errorElement);
    }
    
    if (passwordInput) {
        passwordInput.classList.add('border-red-500');
    }
    
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.style.display = 'block';
    }
}

/**
 * Clear password error
 */
function clearPasswordError() {
    const passwordInput = document.getElementById('coursePassword');
    const errorElement = document.getElementById('passwordError');
    
    if (passwordInput) {
        passwordInput.classList.remove('border-red-500');
    }
    
    if (errorElement) {
        errorElement.style.display = 'none';
    }
}

/**
 * Set loading state
 */
function setLoadingState(loading) {
    const confirmButton = document.querySelector('#enrollModal button[onclick="confirmEnrollment()"]');
    const passwordInput = document.getElementById('coursePassword');
    
    if (loading) {
        if (confirmButton) {
            confirmButton.disabled = true;
            confirmButton.textContent = 'Enrolling...';
        }
        if (passwordInput) {
            passwordInput.disabled = true;
        }
    } else {
        if (confirmButton) {
            confirmButton.disabled = false;
            confirmButton.textContent = 'Confirm';
        }
        if (passwordInput) {
            passwordInput.disabled = false;
        }
    }
}

/**
 * Show success modal
 */
function showSuccessModal(message) {
    const modal = document.getElementById('successModal');
    const successMessage = document.getElementById('successMessage');
    
    if (modal && successMessage) {
        successMessage.textContent = message || `Successfully enrolled in ${currentCourseName}!`;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

/**
 * Close enrollment modal
 */
function closeEnrollModal() {
    const modal = document.getElementById('enrollModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    
    // Reset form
    const passwordInput = document.getElementById('coursePassword');
    if (passwordInput) {
        passwordInput.value = '';
    }
    
    clearPasswordError();
    setLoadingState(false);
    
    currentCourseId = null;
    currentCourseName = '';
}

/**
 * Close success modal and redirect
 */
function redirectToMyCourses() {
    window.location.href = '/my-courses';
}

/**
 * Close any modal
 */
function closeModal() {
    const modals = ['courseModal', 'enrollModal', 'successModal'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    });
}

// Expose functions globally
window.enrollCourse = enrollCourse;
window.confirmEnrollment = confirmEnrollment;
window.closeEnrollModal = closeEnrollModal;
window.redirectToMyCourses = redirectToMyCourses;
window.closeModal = closeModal;

console.log('Enrollment UI loaded');
