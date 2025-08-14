// Courses Manage Page JavaScript
console.log('Courses manage page loaded');

// Global variables
let currentCourseId = null;
let studentsData = [];

// Make functions globally available
window.viewStudents = viewStudents;
window.closeStudentsModal = closeStudentsModal;
window.removeStudent = removeStudent;
window.exportStudentList = exportStudentList;
window.openEditModal = openEditModal;
window.closeEditModal = closeEditModal;
window.openCreateModal = openCreateModal;
window.closeCreateModal = closeCreateModal;

// Students modal functions
function viewStudents(courseId) {
    currentCourseId = courseId;
    const modal = document.getElementById('studentsModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    loadStudents(courseId);
}

function closeStudentsModal() {
    const modal = document.getElementById('studentsModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    currentCourseId = null;
    studentsData = [];
}

async function loadStudents(courseId) {
    try {
        const response = await fetch(`/courses/${courseId}/students`);
        const data = await response.json();
        
        if (data.error) {
            throw new Error(data.error);
        }
        
        studentsData = data.students;
        updateStudentsDisplay(data);
        
    } catch (error) {
        console.error('Error loading students:', error);
        showStudentsError(error.message);
    }
}

function updateStudentsDisplay(data) {
    // Ensure students array is defined
    const students = Array.isArray(data.students) ? data.students : [];
    studentsData = students; // update global data
    // Update course info
    document.getElementById('courseInfo').textContent = `${data.course_code} - ${data.course_title}`;
    document.getElementById('studentCount').textContent = `${data.count} students`;
    
    // Update students table
    const tbody = document.getElementById('studentsTableBody');
    
    if (students.length === 0) {
        tbody.innerHTML = `
            <div class="flex items-center justify-center py-12">
                <div class="text-center">
                    <i class="mb-4 text-4xl text-gray-400 fas fa-users"></i>
                    <p class="text-gray-500">No students enrolled yet</p>
                </div>
            </div>
        `;
        return;
    }
    
    tbody.innerHTML = students.map(student => `
        <div class="px-6 py-4 hover:bg-gray-50" data-student-id="${student.id}">
            <div class="grid grid-cols-12 gap-4 text-sm">
                <div class="col-span-1 font-medium text-gray-900">${student.serial}</div>
                <div class="col-span-3">
                    <div class="font-medium text-gray-900">${student.name}</div>
                </div>
                <div class="col-span-2 text-gray-600">${student.student_id}</div>
                <div class="col-span-3 text-gray-600">${student.email}</div>
                <div class="col-span-2 text-gray-600">${student.enrolled_at}</div>
                <div class="col-span-1">
                    <button onclick="removeStudent(${student.id}, '${student.name}')" 
                            class="px-3 py-1 text-xs text-white bg-red-600 rounded hover:bg-red-700"
                            title="Remove student">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

function showStudentsError(message) {
    const tbody = document.getElementById('studentsTableBody');
    tbody.innerHTML = `
        <div class="flex items-center justify-center py-12">
            <div class="text-center">
                <i class="mb-4 text-4xl text-red-400 fas fa-exclamation-triangle"></i>
                <p class="text-red-500">Error: ${message}</p>
                <button onclick="loadStudents(currentCourseId)" class="px-4 py-2 mt-3 text-sm text-white bg-indigo-600 rounded hover:bg-indigo-700">
                    Try Again
                </button>
            </div>
        </div>
    `;
}

async function removeStudent(studentId, studentName) {
    if (!confirm(`Are you sure you want to remove ${studentName} from this course?`)) {
        return;
    }
    
    try {
        const response = await fetch(`/courses/${currentCourseId}/students/${studentId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Remove student from UI
            const studentRow = document.querySelector(`[data-student-id="${studentId}"]`);
            if (studentRow) {
                studentRow.remove();
            }
            
            // Update count
            studentsData = studentsData.filter(s => s.id !== studentId);
            document.getElementById('studentCount').textContent = `${studentsData.length} students`;
            
            // Show success message
            showSuccess(data.message);
            
            // If no students left, show empty state
            if (studentsData.length === 0) {
                updateStudentsDisplay({ students: [], count: 0, course_code: '', course_title: '' });
            }
            
        } else {
            throw new Error(data.message || 'Failed to remove student');
        }
        
    } catch (error) {
        console.error('Error removing student:', error);
        showError(error.message);
    }
}

function exportStudentList() {
    if (studentsData.length === 0) {
        showError('No students to export');
        return;
    }
    
    // Create CSV content
    const headers = ['#', 'Name', 'Student ID', 'Email', 'Enrolled Date'];
    const csvContent = [
        headers.join(','),
        ...studentsData.map(student => [
            student.serial,
            `"${student.name}"`,
            student.student_id,
            student.email,
            student.enrolled_at
        ].join(','))
    ].join('\n');
    
    // Download CSV
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `students-course-${currentCourseId}.csv`;
    a.click();
    window.URL.revokeObjectURL(url);
    
    showSuccess('Student list exported successfully');
}

// Edit modal functions
function openEditModal(button) {
    const courseId = button.dataset.courseId;
    const courseTitle = button.dataset.courseTitle;
    const courseCode = button.dataset.courseCode;
    const coursePrerequisites = button.dataset.coursePrerequisites;
    const courseCapacity = button.dataset.courseCapacity;
    const coursePassword = button.dataset.coursePassword;

    // Populate form fields
    document.getElementById('edit_course_id').value = courseId;
    document.getElementById('edit_title').value = courseTitle;
    document.getElementById('edit_code').value = courseCode;
    document.getElementById('edit_prerequisites').value = coursePrerequisites || '';
    document.getElementById('edit_capacity').value = courseCapacity;
    document.getElementById('edit_password').value = coursePassword;

    // Update form action
    const form = document.getElementById('editCourseForm');
    form.action = `/courses/${courseId}`;

    // Show modal
    const modal = document.getElementById('editCourseModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeEditModal() {
    const modal = document.getElementById('editCourseModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

// Create modal functions
function openCreateModal() {
    const modal = document.getElementById('createCourseModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeCreateModal() {
    const modal = document.getElementById('createCourseModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

// Deprecated local toast removed; using global helpers

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('searchStudents');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const filteredStudents = studentsData.filter(student => 
                student.name.toLowerCase().includes(searchTerm) ||
                student.email.toLowerCase().includes(searchTerm) ||
                student.student_id.toLowerCase().includes(searchTerm)
            );
            
            updateStudentsDisplay({
                students: filteredStudents,
                count: filteredStudents.length,
                course_code: '',
                course_title: ''
            });
        });
    }
    
    // Initialize courses data if available
    try {
        if (window.__COURSES__) {
            console.log('Courses data loaded:', window.__COURSES__.length, 'courses');
        }
    } catch (e) {
        console.log('No courses data available');
    }
});
