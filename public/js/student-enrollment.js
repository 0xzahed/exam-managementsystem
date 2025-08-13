// Enhanced Student Enrollment JavaScript - UI Only
document.addEventListener('DOMContentLoaded', function() {
    initializeEnrollmentUI();
    initializeSearchAndFilter();
    addCustomStyles();
});

let currentCourseId = null;
let currentCourseName = '';

/**
 * Initialize enrollment UI system
 */
function initializeEnrollmentUI() {
    setupEventListeners();
}

/**
 * Set up event listeners
 */
function setupEventListeners() {
    // Form submission - handled by PHP controller
    const enrollForm = document.getElementById('enrollForm');
    if (enrollForm) {
        enrollForm.addEventListener('submit', handleFormSubmit);
    }
    
    // Modal close events
    const enrollModal = document.getElementById('enrollModal');
    if (enrollModal) {
        enrollModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeEnrollModal();
            }
        });
    }
    
    // Keyboard events
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !document.getElementById('enrollModal').classList.contains('hidden')) {
            closeEnrollModal();
        }
    });
}

/**
 * Handle form submission - UI changes only
 */
function handleFormSubmit(e) {
    e.preventDefault();
    
    if (!currentCourseId) {
        showNotification('error', 'Course not selected. Please try again.');
        return;
    }
    
    const password = document.getElementById('coursePassword').value.trim();
    
    if (!password) {
        showPasswordError('Please enter the course password.');
        document.getElementById('coursePassword').focus();
        return;
    }
    
    // Clear any previous errors
    clearPasswordError();
    
    // Show loading state
    setLoadingState(true);
    
    // Submit form to PHP controller
    submitEnrollmentForm();
}

/**
 * Submit form to PHP controller
 */
function submitEnrollmentForm() {
    const form = document.getElementById('enrollForm');
    const formData = new FormData(form);
    
    fetch(`/student/courses/${currentCourseId}/enroll`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        setLoadingState(false);
        
        if (data.success) {
            showNotification('success', data.message || 'Successfully enrolled in the course!');
            closeEnrollModal();
            
            // Redirect to my courses page
            setTimeout(() => {
                window.location.href = '/student/courses/my';
            }, 2000);
        } else {
            if (data.message && data.message.toLowerCase().includes('password')) {
                showPasswordError(data.message);
            } else {
                showNotification('error', data.message || 'Enrollment failed. Please try again.');
            }
        }
    })
    .catch(error => {
        console.error('Enrollment error:', error);
        setLoadingState(false);
        showNotification('error', 'Network error. Please check your connection and try again.');
    });
}

/**
 * Show enrollment modal with animations
 */
function showEnrollModal(courseId, courseTitle, courseCode) {
    currentCourseId = courseId;
    currentCourseName = `${courseCode} - ${courseTitle}`;
    
    // Update modal content
    document.getElementById('modalCourseName').textContent = currentCourseName;
    document.getElementById('coursePassword').value = '';
    clearPasswordError();
    
    // Show modal with animation
    const modal = document.getElementById('enrollModal');
    const modalContent = modal.querySelector('.bg-white');
    
    modal.classList.remove('hidden');
    
    // Animate modal appearance
    setTimeout(() => {
        modalContent.style.transform = 'scale(1)';
        modalContent.style.opacity = '1';
    }, 10);
    
    // Focus on password input
    setTimeout(() => {
        document.getElementById('coursePassword').focus();
    }, 200);
}

/**
 * Close enrollment modal with animations
 */
function closeEnrollModal() {
    const modal = document.getElementById('enrollModal');
    const modalContent = modal.querySelector('.bg-white');
    
    // Animate modal disappearance
    modalContent.style.transform = 'scale(0.95)';
    modalContent.style.opacity = '0';
    
    setTimeout(() => {
        modal.classList.add('hidden');
        resetModalState();
    }, 200);
}

/**
 * Reset modal state
 */
function resetModalState() {
    currentCourseId = null;
    currentCourseName = '';
    
    const form = document.getElementById('enrollForm');
    if (form) form.reset();
    
    clearPasswordError();
    setLoadingState(false);
}

/**
 * Set loading state for form elements
 */
function setLoadingState(loading) {
    const button = document.getElementById('enrollButton');
    const spinner = document.getElementById('enrollSpinner');
    const icon = document.getElementById('enrollIcon');
    const passwordInput = document.getElementById('coursePassword');
    
    if (loading) {
        button.disabled = true;
        button.classList.add('opacity-75', 'cursor-not-allowed');
        spinner.classList.remove('hidden');
        if (icon) icon.classList.add('hidden');
        if (passwordInput) passwordInput.disabled = true;
    } else {
        button.disabled = false;
        button.classList.remove('opacity-75', 'cursor-not-allowed');
        spinner.classList.add('hidden');
        if (icon) icon.classList.remove('hidden');
        if (passwordInput) passwordInput.disabled = false;
    }
}

/**
 * Show password validation error
 */
function showPasswordError(message) {
    const passwordInput = document.getElementById('coursePassword');
    let errorElement = document.getElementById('passwordError');
    
    if (!errorElement) {
        errorElement = document.createElement('p');
        errorElement.id = 'passwordError';
        errorElement.className = 'text-red-600 text-sm mt-2 flex items-center animate-fadeIn';
        passwordInput.parentNode.appendChild(errorElement);
    }
    
    passwordInput.classList.add('border-red-500', 'focus:ring-red-500', 'focus:border-red-500');
    errorElement.innerHTML = `<i class="fas fa-exclamation-triangle mr-2"></i>${message}`;
    errorElement.classList.remove('hidden');
}

/**
 * Clear password validation error
 */
function clearPasswordError() {
    const passwordInput = document.getElementById('coursePassword');
    const errorElement = document.getElementById('passwordError');
    
    if (passwordInput) {
        passwordInput.classList.remove('border-red-500', 'focus:ring-red-500', 'focus:border-red-500');
    }
    
    if (errorElement) {
        errorElement.classList.add('hidden');
    }
}

/**
 * Enhanced notification system
 */
function showNotification(type, message, duration = 5000) {
    const container = document.getElementById('alertContainer');
    if (!container) return;
    
    const alertId = 'alert-' + Date.now();
    
    const alertConfig = {
        success: {
            bgColor: 'bg-gradient-to-r from-green-500 to-emerald-500',
            icon: 'fa-check-circle',
            title: 'Success!'
        },
        error: {
            bgColor: 'bg-gradient-to-r from-red-500 to-pink-500',
            icon: 'fa-exclamation-circle',
            title: 'Error!'
        },
        warning: {
            bgColor: 'bg-gradient-to-r from-yellow-500 to-orange-500',
            icon: 'fa-exclamation-triangle',
            title: 'Warning!'
        },
        info: {
            bgColor: 'bg-gradient-to-r from-blue-500 to-indigo-500',
            icon: 'fa-info-circle',
            title: 'Info'
        }
    };
    
    const config = alertConfig[type] || alertConfig.info;
    
    const alertElement = document.createElement('div');
    alertElement.id = alertId;
    alertElement.className = 'transform transition-all duration-300 translate-x-full opacity-0 mb-3';
    
    alertElement.innerHTML = `
        <div class="${config.bgColor} text-white rounded-lg shadow-xl max-w-sm w-full overflow-hidden">
            <div class="p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas ${config.icon} text-xl"></i>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-semibold">${config.title}</p>
                        <p class="text-sm mt-1 leading-relaxed">${message}</p>
                    </div>
                    <button onclick="removeNotification('${alertId}')" 
                            class="ml-4 text-white hover:text-gray-200 transition-colors duration-200 p-1 rounded hover:bg-white hover:bg-opacity-20">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>
            </div>
            <div class="h-1 bg-white bg-opacity-30">
                <div class="h-full bg-white transition-all ease-linear" 
                     style="width: 100%; animation: progressBar ${duration}ms linear forwards;" 
                     id="${alertId}-progress"></div>
            </div>
        </div>
    `;
    
    container.appendChild(alertElement);
    
    // Animate in
    setTimeout(() => {
        alertElement.classList.remove('translate-x-full', 'opacity-0');
        alertElement.classList.add('translate-x-0', 'opacity-100');
    }, 100);
    
    // Auto remove
    setTimeout(() => {
        removeNotification(alertId);
    }, duration);
}

/**
 * Remove notification with animation
 */
function removeNotification(alertId) {
    const alertElement = document.getElementById(alertId);
    if (alertElement) {
        alertElement.classList.remove('translate-x-0', 'opacity-100');
        alertElement.classList.add('translate-x-full', 'opacity-0');
        
        setTimeout(() => {
            alertElement.remove();
        }, 300);
    }
}

/**
 * Initialize search and filter functionality
 */
function initializeSearchAndFilter() {
    const searchInput = document.getElementById('courseSearch');
    const departmentFilter = document.getElementById('departmentFilter');
    
    if (searchInput) {
        searchInput.addEventListener('input', debounce(filterCourses, 300));
    }
    
    if (departmentFilter) {
        departmentFilter.addEventListener('change', filterCourses);
    }
}

/**
 * Debounce function for search optimization
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Enhanced course filtering
 */
function filterCourses() {
    const searchTerm = document.getElementById('courseSearch')?.value.toLowerCase() || '';
    const selectedDepartment = document.getElementById('departmentFilter')?.value || '';
    const courseCards = document.querySelectorAll('.course-card');
    
    let visibleCount = 0;
    
    courseCards.forEach(card => {
        const title = card.querySelector('.course-title')?.textContent.toLowerCase() || '';
        const code = card.querySelector('.course-code')?.textContent.toLowerCase() || '';
        const instructor = card.querySelector('.instructor-name')?.textContent.toLowerCase() || '';
        const department = card.querySelector('[class*="fa-building"]')?.parentElement?.textContent?.trim() || '';
        
        const matchesSearch = !searchTerm || 
                            title.includes(searchTerm) || 
                            code.includes(searchTerm) || 
                            instructor.includes(searchTerm);
        
        const matchesDepartment = !selectedDepartment || department === selectedDepartment;
        
        if (matchesSearch && matchesDepartment) {
            card.style.display = 'block';
            card.style.animation = 'fadeIn 0.3s ease-in-out';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    // Update no results message
    updateNoResultsMessage(visibleCount);
}

/**
 * Update no results message
 */
function updateNoResultsMessage(visibleCount) {
    const existingMessage = document.getElementById('noResultsMessage');
    const coursesGrid = document.getElementById('coursesGrid');
    
    if (existingMessage) {
        existingMessage.remove();
    }
    
    if (visibleCount === 0 && coursesGrid && document.querySelectorAll('.course-card').length > 0) {
        const noResultsDiv = document.createElement('div');
        noResultsDiv.id = 'noResultsMessage';
        noResultsDiv.className = 'col-span-full text-center py-16 animate-fadeIn';
        noResultsDiv.innerHTML = `
            <div class="mx-auto h-24 w-24 text-gray-300 mb-6">
                <i class="fas fa-search text-6xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No courses found</h3>
            <p class="text-gray-600 mb-4">Try adjusting your search criteria or browse all courses.</p>
            <button onclick="clearFilters()" class="px-6 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 transform hover:scale-105">
                <i class="fas fa-eraser mr-2"></i>
                Clear Filters
            </button>
        `;
        coursesGrid.appendChild(noResultsDiv);
    }
}

/**
 * Clear all filters
 */
function clearFilters() {
    const searchInput = document.getElementById('courseSearch');
    const departmentFilter = document.getElementById('departmentFilter');
    
    if (searchInput) searchInput.value = '';
    if (departmentFilter) departmentFilter.value = '';
    
    filterCourses();
}

/**
 * Add custom CSS animations
 */
function addCustomStyles() {
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes progressBar {
            from { width: 100%; }
            to { width: 0%; }
        }
        
        .animate-fadeIn {
            animation: fadeIn 0.3s ease-in-out;
        }
        
        .course-card {
            transition: all 0.3s ease;
        }
        
        .course-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        #enrollModal .bg-white {
            transform: scale(0.95);
            opacity: 0;
            transition: all 0.2s ease-out;
        }
        
        .nav-item.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
    `;
    document.head.appendChild(style);
}

// Expose functions globally for onclick handlers
window.showEnrollModal = showEnrollModal;
window.closeEnrollModal = closeEnrollModal;
window.removeNotification = removeNotification;
window.clearFilters = clearFilters;
