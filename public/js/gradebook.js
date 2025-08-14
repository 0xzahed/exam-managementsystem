/**
 * Gradebook JavaScript functionality
 * Handles grade editing, bulk operations, and real-time updates
 */

class Gradebook {
    constructor() {
        this.initializeEventListeners();
        this.initializeTooltips();
    }

    /**
     * Initialize all event listeners
     */
    initializeEventListeners() {
        // Grade edit modal events
        this.initializeGradeModal();
        
        // Bulk operations
        this.initializeBulkOperations();
        
        // Search and filter
        this.initializeSearchAndFilter();
        
        // Export functionality
        this.initializeExport();
    }

    /**
     * Initialize grade editing modal
     */
    initializeGradeModal() {
        const gradeModal = document.getElementById('gradeModal');
        if (!gradeModal) return;

        // Close modal when clicking outside
        gradeModal.addEventListener('click', (e) => {
            if (e.target === gradeModal) {
                this.closeGradeModal();
            }
        });

        // Handle form submission
        const gradeForm = document.getElementById('gradeForm');
        if (gradeForm) {
            gradeForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.updateGrade();
            });
        }
    }

    /**
     * Initialize bulk operations
     */
    initializeBulkOperations() {
        const bulkUpdateBtn = document.getElementById('bulkUpdateBtn');
        if (bulkUpdateBtn) {
            bulkUpdateBtn.addEventListener('click', () => {
                this.showBulkUpdateModal();
            });
        }
    }

    /**
     * Initialize search and filter functionality
     */
    initializeSearchAndFilter() {
        const searchInput = document.getElementById('gradeSearch');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.filterGrades(e.target.value);
            });
        }

        const filterSelect = document.getElementById('gradeFilter');
        if (filterSelect) {
            filterSelect.addEventListener('change', (e) => {
                this.filterGradesByType(e.target.value);
            });
        }
    }

    /**
     * Initialize export functionality
     */
    initializeExport() {
        const exportBtn = document.getElementById('exportGrades');
        if (exportBtn) {
            exportBtn.addEventListener('click', () => {
                this.exportGrades();
            });
        }
    }

    /**
     * Initialize tooltips
     */
    initializeTooltips() {
        // Initialize any tooltip libraries or custom tooltips
        const tooltipElements = document.querySelectorAll('[data-tooltip]');
        tooltipElements.forEach(element => {
            this.createTooltip(element);
        });
    }

    /**
     * Create custom tooltip
     */
    createTooltip(element) {
        const tooltipText = element.getAttribute('data-tooltip');
        if (!tooltipText) return;

        element.addEventListener('mouseenter', (e) => {
            const tooltip = document.createElement('div');
            tooltip.className = 'absolute z-50 px-2 py-1 text-xs text-white bg-gray-900 rounded shadow-lg';
            tooltip.textContent = tooltipText;
            tooltip.style.top = (e.pageY - 30) + 'px';
            tooltip.style.left = e.pageX + 'px';
            
            document.body.appendChild(tooltip);
            element.tooltip = tooltip;
        });

        element.addEventListener('mouseleave', () => {
            if (element.tooltip) {
                element.tooltip.remove();
                element.tooltip = null;
            }
        });
    }

    /**
     * Open grade edit modal
     */
    editGrade(gradeId, pointsEarned, feedback, totalPoints) {
        document.getElementById('gradeId').value = gradeId;
        document.getElementById('pointsEarned').value = pointsEarned;
        document.getElementById('feedback').value = feedback || '';
        document.getElementById('totalPoints').textContent = totalPoints;
        
        // Show modal
        document.getElementById('gradeModal').classList.remove('hidden');
        
        // Focus on points input
        document.getElementById('pointsEarned').focus();
    }

    /**
     * Close grade edit modal
     */
    closeGradeModal() {
        document.getElementById('gradeModal').classList.add('hidden');
        
        // Reset form
        document.getElementById('gradeForm').reset();
    }

    /**
     * Update grade via AJAX
     */
    async updateGrade() {
        const form = document.getElementById('gradeForm');
        const formData = new FormData(form);
        
        try {
            const response = await fetch('/instructor/gradebook/update-grade', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await response.json();
            
            if (data.success) {
                this.showNotification('Grade updated successfully!', 'success');
                this.closeGradeModal();
                
                // Reload page to show updated grades
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                this.showNotification('Error updating grade: ' + data.message, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification('Error updating grade. Please try again.', 'error');
        }
    }

    /**
     * Show bulk update modal
     */
    showBulkUpdateModal() {
        // Implementation for bulk update modal
        console.log('Bulk update modal would open here');
    }

    /**
     * Filter grades by search term
     */
    filterGrades(searchTerm) {
        const gradeRows = document.querySelectorAll('.grade-row');
        const searchLower = searchTerm.toLowerCase();

        gradeRows.forEach(row => {
            const studentName = row.querySelector('.student-name')?.textContent?.toLowerCase() || '';
            const studentEmail = row.querySelector('.student-email')?.textContent?.toLowerCase() || '';
            
            if (studentName.includes(searchLower) || studentEmail.includes(searchLower)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    /**
     * Filter grades by type
     */
    filterGradesByType(filterType) {
        const gradeCells = document.querySelectorAll('.grade-cell');
        
        gradeCells.forEach(cell => {
            if (filterType === 'all') {
                cell.style.display = '';
            } else if (filterType === 'assignments' && cell.classList.contains('assignment-grade')) {
                cell.style.display = '';
            } else if (filterType === 'exams' && cell.classList.contains('exam-grade')) {
                cell.style.display = '';
            } else {
                cell.style.display = 'none';
            }
        });
    }

    /**
     * Export grades
     */
    exportGrades() {
        const courseId = document.querySelector('[data-course-id]')?.getAttribute('data-course-id');
        if (courseId) {
            window.location.href = `/instructor/gradebook/${courseId}/export`;
        }
    }

    /**
     * Show notification
     */
    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full`;
        
        // Set colors based on type
        switch (type) {
            case 'success':
                notification.className += ' bg-green-500 text-white';
                break;
            case 'error':
                notification.className += ' bg-red-500 text-white';
                break;
            case 'warning':
                notification.className += ' bg-yellow-500 text-white';
                break;
            default:
                notification.className += ' bg-blue-500 text-white';
        }
        
        notification.textContent = message;
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 5000);
    }

    /**
     * Calculate grade statistics
     */
    calculateStatistics() {
        const gradeCells = document.querySelectorAll('.grade-cell[data-score]');
        const scores = Array.from(gradeCells).map(cell => parseFloat(cell.getAttribute('data-score')));
        
        if (scores.length === 0) return { average: 0, highest: 0, lowest: 0, count: 0 };
        
        const average = scores.reduce((sum, score) => sum + score, 0) / scores.length;
        const highest = Math.max(...scores);
        const lowest = Math.min(...scores);
        
        return {
            average: Math.round(average * 100) / 100,
            highest: Math.round(highest * 100) / 100,
            lowest: Math.round(lowest * 100) / 100,
            count: scores.length
        };
    }

    /**
     * Update statistics display
     */
    updateStatistics() {
        const stats = this.calculateStatistics();
        
        // Update statistics elements if they exist
        const avgElement = document.getElementById('averageGrade');
        if (avgElement) avgElement.textContent = stats.average + '%';
        
        const highestElement = document.getElementById('highestGrade');
        if (highestElement) highestElement.textContent = stats.highest + '%';
        
        const lowestElement = document.getElementById('lowestGrade');
        if (lowestElement) lowestElement.textContent = stats.lowest + '%';
        
        const countElement = document.getElementById('gradeCount');
        if (countElement) countElement.textContent = stats.count;
    }

    /**
     * Initialize keyboard shortcuts
     */
    initializeKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Escape key closes modal
            if (e.key === 'Escape') {
                this.closeGradeModal();
            }
            
            // Ctrl/Cmd + S saves grade
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                const gradeForm = document.getElementById('gradeForm');
                if (gradeForm && !gradeForm.classList.contains('hidden')) {
                    this.updateGrade();
                }
            }
        });
    }
}

// Initialize gradebook when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.gradebook = new Gradebook();
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = Gradebook;
}
