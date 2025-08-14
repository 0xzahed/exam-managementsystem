document.addEventListener('DOMContentLoaded', function() {
    // Initialize announcements page functionality without animations
    
    // Add confirmation for delete buttons
    const deleteButtons = document.querySelectorAll('button[type="submit"]');
    deleteButtons.forEach(button => {
        if (button.closest('form') && button.closest('form').action.includes('destroy')) {
            button.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to delete this announcement? This action cannot be undone.')) {
                    e.preventDefault();
                }
            });
        }
    });

    // Add loading state for forms
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitButton = this.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
            }
        });
    });

    // Add search functionality (if search input exists)
    const searchInput = document.querySelector('input[type="search"]');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const announcements = document.querySelectorAll('.bg-white.rounded-xl');
            
            announcements.forEach(announcement => {
                const title = announcement.querySelector('h3').textContent.toLowerCase();
                const content = announcement.querySelector('.text-gray-600').textContent.toLowerCase();
                const course = announcement.querySelector('.bg-blue-100').textContent.toLowerCase();
                
                if (title.includes(searchTerm) || content.includes(searchTerm) || course.includes(searchTerm)) {
                    announcement.style.display = 'block';
                } else {
                    announcement.style.display = 'none';
                }
            });
        });
    }

    // Add priority filter functionality
    const priorityFilter = document.querySelector('select[name="priority"]');
    if (priorityFilter) {
        priorityFilter.addEventListener('change', function() {
            const selectedPriority = this.value;
            const announcements = document.querySelectorAll('.bg-white.rounded-xl');
            
            announcements.forEach(announcement => {
                const priorityBadge = announcement.querySelector('[class*="bg-"]');
                if (priorityBadge) {
                    const priority = priorityBadge.textContent.toLowerCase();
                    if (selectedPriority === '' || priority.includes(selectedPriority)) {
                        announcement.style.display = 'block';
                    } else {
                        announcement.style.display = 'none';
                    }
                }
            });
        });
    }

    // Add course filter functionality
    const courseFilter = document.querySelector('select[name="course"]');
    if (courseFilter) {
        courseFilter.addEventListener('change', function() {
            const selectedCourse = this.value;
            const announcements = document.querySelectorAll('.bg-white.rounded-xl');
            
            announcements.forEach(announcement => {
                const courseBadge = announcement.querySelector('.bg-blue-100');
                if (courseBadge) {
                    const course = courseBadge.textContent.toLowerCase();
                    if (selectedCourse === '' || course.includes(selectedCourse.toLowerCase())) {
                        announcement.style.display = 'block';
                    } else {
                        announcement.style.display = 'none';
                    }
                }
            });
        });
    }

    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + N for new announcement (instructor only)
        if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
            const newAnnouncementBtn = document.querySelector('a[href*="announcements/create"]');
            if (newAnnouncementBtn && newAnnouncementBtn.style.display !== 'none') {
                e.preventDefault();
                newAnnouncementBtn.click();
            }
        }
        
        // Escape key to focus on search
        if (e.key === 'Escape') {
            const searchInput = document.querySelector('input[type="search"]');
            if (searchInput) {
                searchInput.focus();
            }
        }
    });

    console.log('Announcements page initialized successfully!');
});
