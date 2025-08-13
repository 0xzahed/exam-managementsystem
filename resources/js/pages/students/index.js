// Students page JavaScript functionality
let students = [];

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    initializeFilters();
});

// Initialize filters
function initializeFilters() {
    const searchInput = document.getElementById('searchInput');
    const courseFilter = document.getElementById('courseFilter');
    
    if (searchInput) {
        searchInput.addEventListener('input', filterStudents);
    }
    
    if (courseFilter) {
        courseFilter.addEventListener('change', filterStudents);
    }
}

// Filter students
function filterStudents() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const selectedCourse = document.getElementById('courseFilter').value;
    const studentCards = document.querySelectorAll('.student-card');
    
    studentCards.forEach(card => {
        const studentName = card.dataset.studentName;
        const courseIds = card.dataset.courseIds.split(',');
        
        const matchesSearch = studentName.includes(searchTerm);
        const matchesCourse = !selectedCourse || courseIds.includes(selectedCourse);
        
        if (matchesSearch && matchesCourse) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

// View student details
function viewStudent(studentId) {
    // Show loading in modal
    document.getElementById('studentDetailContent').innerHTML = `
        <div class="text-center py-8">
            <i class="fas fa-spinner fa-spin text-3xl text-gray-400 mb-4"></i>
            <p class="text-gray-600">Loading student details...</p>
        </div>
    `;
    
    document.getElementById('studentDetailModal').classList.remove('hidden');
    document.getElementById('studentDetailModal').classList.add('flex');
    
    // Fetch student details
    fetch(`/students/${studentId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayStudentDetails(data.student);
            } else {
                throw new Error(data.message || 'Failed to load student details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('studentDetailContent').innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-exclamation-triangle text-3xl text-red-400 mb-4"></i>
                    <p class="text-red-600">Failed to load student details</p>
                </div>
            `;
        });
}

// Display student details in modal
function displayStudentDetails(student) {
    const content = `
        <div class="space-y-6">
            <!-- Student Header -->
            <div class="flex items-center space-x-6 p-6 bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl">
                <div class="w-20 h-20 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-2xl">
                    ${student.name.substring(0, 2).toUpperCase()}
                </div>
                <div class="flex-1">
                    <h4 class="text-2xl font-bold text-gray-800">${student.name}</h4>
                    <p class="text-gray-600 text-lg">${student.email}</p>
                    <p class="text-sm text-gray-500 mt-1">Joined: ${new Date(student.created_at).toLocaleDateString()}</p>
                </div>
                <div class="flex gap-2">
                    <button onclick="messageStudent(${student.id})" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-envelope mr-2"></i>Message
                    </button>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100">Enrolled Courses</p>
                            <p class="text-2xl font-bold">${student.enrolled_courses.length}</p>
                        </div>
                        <i class="fas fa-book text-2xl opacity-80"></i>
                    </div>
                </div>
                <div class="p-4 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100">Active Status</p>
                            <p class="text-lg font-bold">${student.enrolled_courses.filter(c => c.pivot.status === 'active' || !c.pivot.status).length} Active</p>
                        </div>
                        <i class="fas fa-check-circle text-2xl opacity-80"></i>
                    </div>
                </div>
                <div class="p-4 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100">Member Since</p>
                            <p class="text-sm font-bold">${getTimeAgo(student.created_at)}</p>
                        </div>
                        <i class="fas fa-calendar text-2xl opacity-80"></i>
                    </div>
                </div>
            </div>
            
            <!-- Enrolled Courses -->
            <div class="bg-white border border-gray-200 rounded-xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <h5 class="text-xl font-bold text-gray-800">Enrolled Courses</h5>
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">
                        ${student.enrolled_courses.length} ${student.enrolled_courses.length === 1 ? 'Course' : 'Courses'}
                    </span>
                </div>
                <div class="space-y-4">
                    ${student.enrolled_courses.length > 0 ? 
                        student.enrolled_courses.map(course => `
                            <div class="flex justify-between items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h6 class="text-lg font-bold text-gray-800">${course.title}</h6>
                                        <span class="px-3 py-1 text-xs font-medium ${course.pivot.status === 'active' || !course.pivot.status ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'} rounded-full">
                                            ${course.pivot.status || 'Active'}
                                        </span>
                                    </div>
                                    <p class="text-gray-600 mb-1">${course.code}</p>
                                    ${course.description ? `<p class="text-sm text-gray-500 mb-2">${course.description.length > 100 ? course.description.substring(0, 100) + '...' : course.description}</p>` : ''}
                                    <div class="flex items-center gap-4 text-sm text-gray-500">
                                        <span><i class="fas fa-calendar mr-1"></i>Enrolled: ${new Date(course.pivot.enrolled_at || course.pivot.created_at).toLocaleDateString()}</span>
                                        ${course.credits ? `<span><i class="fas fa-award mr-1"></i>${course.credits} Credits</span>` : ''}
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 ml-4">
                                    <button onclick="removeCourseConfirm(${student.id}, ${course.id}, '${course.title}')" class="px-3 py-2 bg-red-100 text-red-700 text-sm rounded-lg hover:bg-red-200 transition-colors">
                                        <i class="fas fa-times mr-1"></i>Remove
                                    </button>
                                </div>
                            </div>
                        `).join('') 
                        : '<div class="text-center py-8"><i class="fas fa-book-open text-4xl text-gray-300 mb-4"></i><p class="text-gray-500">No courses enrolled yet</p></div>'
                    }
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('studentDetailContent').innerHTML = content;
}

// Helper function to get time ago
function getTimeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);
    const diffInMinutes = Math.floor(diffInSeconds / 60);
    const diffInHours = Math.floor(diffInMinutes / 60);
    const diffInDays = Math.floor(diffInHours / 24);
    const diffInMonths = Math.floor(diffInDays / 30);
    const diffInYears = Math.floor(diffInMonths / 12);

    if (diffInYears > 0) return `${diffInYears} year${diffInYears > 1 ? 's' : ''} ago`;
    if (diffInMonths > 0) return `${diffInMonths} month${diffInMonths > 1 ? 's' : ''} ago`;
    if (diffInDays > 0) return `${diffInDays} day${diffInDays > 1 ? 's' : ''} ago`;
    if (diffInHours > 0) return `${diffInHours} hour${diffInHours > 1 ? 's' : ''} ago`;
    if (diffInMinutes > 0) return `${diffInMinutes} minute${diffInMinutes > 1 ? 's' : ''} ago`;
    return 'Just now';
}

// Remove course confirmation
function removeCourseConfirm(studentId, courseId, courseTitle) {
    if (confirm(`Are you sure you want to remove this student from "${courseTitle}"?`)) {
        removeCourse(studentId, courseId);
    }
}

// Remove student from course
function removeCourse(studentId, courseId) {
    fetch(`/students/${studentId}/courses/${courseId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Student removed from course successfully!', 'success');
            // Refresh the modal content
            const currentStudentId = document.querySelector('[onclick*="viewStudent"]')?.onclick.toString().match(/\d+/)?.[0];
            if (currentStudentId) {
                setTimeout(() => viewStudent(currentStudentId), 1000);
            }
            // Refresh the page to update the student list
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showToast(data.message || 'Failed to remove student', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while removing student', 'error');
    });
}

// Hide student modal
function hideStudentModal() {
    const modal = document.getElementById('studentDetailModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

// Message student (placeholder)
function messageStudent(studentId) {
    showToast('Message functionality will be implemented soon!', 'info');
}

// Export students (placeholder)
function exportStudents() {
    showToast('Export functionality will be implemented soon!', 'info');
}

// Toast notification function
function showToast(message, type = 'success') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500' : 
        type === 'error' ? 'bg-red-500' : 
        type === 'info' ? 'bg-blue-500' : 'bg-gray-500'
    } text-white`;
    toast.textContent = message;
    
    // Add to body
    document.body.appendChild(toast);
    
    // Remove after 3 seconds
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    const modal = document.getElementById('studentDetailModal');
    if (modal && e.target === modal) {
        hideStudentModal();
    }
});

// Expose functions globally
window.filterStudents = filterStudents;
window.viewStudent = viewStudent;
window.hideStudentModal = hideStudentModal;
window.messageStudent = messageStudent;
window.exportStudents = exportStudents;
window.removeCourseConfirm = removeCourseConfirm;
window.removeCourse = removeCourse;
