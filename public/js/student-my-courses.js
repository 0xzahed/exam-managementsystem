// Student My Courses JavaScript
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

let currentUnenrollCourseId = null;

/**
 * Show unenroll confirmation modal
 */
function showUnenrollModal(courseId, courseTitle, courseCode) {
    currentUnenrollCourseId = courseId;
    document.getElementById('modalUnenrollCourseName').textContent = `${courseCode} - ${courseTitle}`;
    document.getElementById('unenrollModal').classList.remove('hidden');
}

/**
 * Close unenroll modal
 */
function closeUnenrollModal() {
    document.getElementById('unenrollModal').classList.add('hidden');
    currentUnenrollCourseId = null;
}

/**
 * Confirm unenrollment
 */
function confirmUnenroll() {
    if (!currentUnenrollCourseId) {
        showAlert('Error: Course not selected', 'error');
        return;
    }
    
    // Show loading state
    setUnenrollButtonLoading(true);
    
    // Make AJAX request
    $.ajax({
        url: `/my-courses/${currentUnenrollCourseId}/unenroll`,
        method: 'DELETE',
        data: {
            _token: csrfToken
        },
        success: function(response) {
            if (response.success) {
                showAlert(response.message, 'success');
                closeUnenrollModal();
                
                // Remove course from DOM or refresh page
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                showAlert(response.message, 'error');
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            if (response && response.message) {
                showAlert(response.message, 'error');
            } else {
                showAlert('Failed to unenroll from course. Please try again.', 'error');
            }
        },
        complete: function() {
            setUnenrollButtonLoading(false);
        }
    });
}

/**
 * Set loading state for unenroll button
 */
function setUnenrollButtonLoading(loading) {
    const button = document.getElementById('unenrollButton');
    const spinner = document.getElementById('unenrollSpinner');
    
    if (loading) {
        button.disabled = true;
        spinner.classList.remove('hidden');
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Unenrolling...';
    } else {
        button.disabled = false;
        spinner.classList.add('hidden');
        button.innerHTML = 'Yes, Unenroll';
    }
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

/**
 * Filter courses by search term
 */
function filterCourses() {
    const searchTerm = document.getElementById('courseSearch').value.toLowerCase();
    const courseCards = document.querySelectorAll('.course-card');
    
    courseCards.forEach(card => {
        const courseTitle = card.querySelector('.course-title').textContent.toLowerCase();
        const courseCode = card.querySelector('.course-code').textContent.toLowerCase();
        const instructorName = card.querySelector('.instructor-name').textContent.toLowerCase();
        
        if (courseTitle.includes(searchTerm) || 
            courseCode.includes(searchTerm) || 
            instructorName.includes(searchTerm)) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (!document.getElementById('unenrollModal').classList.contains('hidden')) {
            closeUnenrollModal();
        }
    }
});

// Close modal when clicking outside
document.getElementById('unenrollModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeUnenrollModal();
    }
});

// Add smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth'
            });
        }
    });
});

// Enrollment Modal Functionality

/**
 * Event listener for enrollment button
 */
document.getElementById('enrollNewCourseBtn').addEventListener('click', function() {
    showEnrollModal();
});

/**
 * Show enrollment modal - redirect to enrollment page
 */
function showEnrollModal() {
    // Redirect to enrollment page instead of API call
    window.location.href = '/student/enroll';
}
