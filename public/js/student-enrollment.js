// Student Enrollment JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // CSRF Token setup for AJAX requests
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Set up AJAX headers
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': csrfToken
        }
    });
});

let currentCourseId = null;

/**
 * Show enrollment modal
 */
function showEnrollModal(courseId, courseTitle, courseCode) {
    currentCourseId = courseId;
    document.getElementById('modalCourseName').textContent = `${courseCode} - ${courseTitle}`;
    document.getElementById('coursePassword').value = '';
    document.getElementById('enrollModal').classList.remove('hidden');
    
    // Focus on password input
    setTimeout(() => {
        document.getElementById('coursePassword').focus();
    }, 100);
}

/**
 * Close enrollment modal
 */
function closeEnrollModal() {
    document.getElementById('enrollModal').classList.add('hidden');
    currentCourseId = null;
    document.getElementById('coursePassword').value = '';
    
    // Clear any previous error states
    const passwordInput = document.getElementById('coursePassword');
    passwordInput.classList.remove('border-red-500', 'focus:ring-red-500', 'focus:border-red-500');
}

/**
 * Handle enrollment form submission
 */
document.getElementById('enrollForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!currentCourseId) {
        showAlert('Error: Course not selected', 'error');
        return;
    }
    
    const password = document.getElementById('coursePassword').value.trim();
    if (!password) {
        showPasswordError('Please enter the course password');
        return;
    }
    
    // Show loading state
    setEnrollButtonLoading(true);
    
    // Make AJAX request
    $.ajax({
        url: `/courses/${currentCourseId}/enroll`,
        method: 'POST',
        data: {
            password: password,
            _token: csrfToken
        },
        success: function(response) {
            if (response.success) {
                showAlert(response.message, 'success');
                closeEnrollModal();
                
                // Refresh page after 2 seconds to show updated course list
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                showAlert(response.message, 'error');
                clearPasswordError();
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            if (response && response.message) {
                if (response.message.includes('password')) {
                    showPasswordError(response.message);
                } else {
                    showAlert(response.message, 'error');
                }
            } else {
                showAlert('Failed to enroll in course. Please try again.', 'error');
            }
        },
        complete: function() {
            setEnrollButtonLoading(false);
        }
    });
});

/**
 * Set loading state for enroll button
 */
function setEnrollButtonLoading(loading) {
    const button = document.getElementById('enrollButton');
    const spinner = document.getElementById('enrollSpinner');
    
    if (loading) {
        button.disabled = true;
        spinner.classList.remove('hidden');
        button.textContent = ' Enrolling...';
    } else {
        button.disabled = false;
        spinner.classList.add('hidden');
        button.textContent = 'Enroll';
    }
}

/**
 * Show password error
 */
function showPasswordError(message) {
    const passwordInput = document.getElementById('coursePassword');
    const errorElement = document.getElementById('passwordError') || createPasswordError();
    
    passwordInput.classList.add('border-red-500', 'focus:ring-red-500', 'focus:border-red-500');
    errorElement.textContent = message;
    errorElement.classList.remove('hidden');
}

/**
 * Clear password error
 */
function clearPasswordError() {
    const passwordInput = document.getElementById('coursePassword');
    const errorElement = document.getElementById('passwordError');
    
    passwordInput.classList.remove('border-red-500', 'focus:ring-red-500', 'focus:border-red-500');
    if (errorElement) {
        errorElement.classList.add('hidden');
    }
}

/**
 * Create password error element
 */
function createPasswordError() {
    const errorElement = document.createElement('p');
    errorElement.id = 'passwordError';
    errorElement.className = 'text-red-600 text-xs mt-1 hidden';
    
    const passwordInput = document.getElementById('coursePassword');
    passwordInput.parentNode.appendChild(errorElement);
    
    return errorElement;
}

/**
 * Show alert message
 */
function showAlert(message, type = 'info') {
    const alertContainer = document.getElementById('alertContainer');
    const alertId = 'alert-' + Date.now();
    
    const alertTypes = {
        'success': {
            bgColor: 'bg-green-100',
            borderColor: 'border-green-400',
            textColor: 'text-green-700',
            icon: 'fas fa-check-circle'
        },
        'error': {
            bgColor: 'bg-red-100',
            borderColor: 'border-red-400',
            textColor: 'text-red-700',
            icon: 'fas fa-exclamation-circle'
        },
        'warning': {
            bgColor: 'bg-yellow-100',
            borderColor: 'border-yellow-400',
            textColor: 'text-yellow-700',
            icon: 'fas fa-exclamation-triangle'
        },
        'info': {
            bgColor: 'bg-blue-100',
            borderColor: 'border-blue-400',
            textColor: 'text-blue-700',
            icon: 'fas fa-info-circle'
        }
    };
    
    const alertStyle = alertTypes[type] || alertTypes['info'];
    
    const alertElement = document.createElement('div');
    alertElement.id = alertId;
    alertElement.className = `${alertStyle.bgColor} ${alertStyle.borderColor} ${alertStyle.textColor} px-4 py-3 rounded border shadow-lg max-w-sm transition-all duration-300 transform translate-x-full`;
    alertElement.innerHTML = `
        <div class="flex items-center">
            <i class="${alertStyle.icon} mr-3"></i>
            <div class="flex-1">
                <p class="text-sm font-medium">${message}</p>
            </div>
            <button onclick="removeAlert('${alertId}')" class="ml-3 text-lg font-semibold leading-none hover:opacity-75">
                &times;
            </button>
        </div>
    `;
    
    alertContainer.appendChild(alertElement);
    
    // Animate in
    setTimeout(() => {
        alertElement.classList.remove('translate-x-full');
    }, 10);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        removeAlert(alertId);
    }, 5000);
}

/**
 * Remove alert message
 */
function removeAlert(alertId) {
    const alertElement = document.getElementById(alertId);
    if (alertElement) {
        alertElement.classList.add('translate-x-full');
        setTimeout(() => {
            alertElement.remove();
        }, 300);
    }
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (!document.getElementById('enrollModal').classList.contains('hidden')) {
            closeEnrollModal();
        }
    }
});

// Close modal when clicking outside
document.getElementById('enrollModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEnrollModal();
    }
});
