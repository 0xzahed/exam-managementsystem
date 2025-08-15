// Student Exam Index - Real-time Status Updates
document.addEventListener('DOMContentLoaded', function() {
    // Initialize exam status management
    initExamStatusUpdates();
    
    function initExamStatusUpdates() {
        const examCards = document.querySelectorAll('.exam-card');
        
        if (examCards.length === 0) {
            console.log('No exam cards found');
            return;
        }
        
        // Update exam statuses every 30 seconds
        setInterval(updateExamStatuses, 30000);
        
        // Initial status update
        updateExamStatuses();
        
        // Setup countdown timers for exams that haven't started
        setupCountdownTimers();
    }
    
    function updateExamStatuses() {
        const examCards = document.querySelectorAll('.exam-card');
        
        examCards.forEach(async (card) => {
            const examId = card.dataset.examId;
            if (!examId) return;
            
            try {
                const response = await fetch(`/student/exams/${examId}/status`);
                if (response.ok) {
                    const data = await response.json();
                    updateExamCard(card, data);
                }
            } catch (error) {
                console.error('Failed to update exam status:', error);
            }
        });
    }
    
    function updateExamCard(card, statusData) {
        const statusBadge = card.querySelector('.status-badge');
        const actionButton = card.querySelector('.action-button');
        const countdownElement = card.querySelector('.countdown-timer');
        
        // Update status badge
        if (statusBadge) {
            statusBadge.className = 'status-badge px-3 py-1 text-sm font-medium rounded-full';
            
            switch (statusData.status) {
                case 'not_started':
                    statusBadge.classList.add('bg-yellow-100', 'text-yellow-800');
                    statusBadge.textContent = 'Not Started';
                    break;
                case 'available':
                    statusBadge.classList.add('bg-green-100', 'text-green-800');
                    statusBadge.textContent = 'Available';
                    break;
                case 'ended':
                    statusBadge.classList.add('bg-red-100', 'text-red-800');
                    statusBadge.textContent = 'Ended';
                    break;
                case 'in_progress':
                    statusBadge.classList.add('bg-blue-100', 'text-blue-800');
                    statusBadge.textContent = 'In Progress';
                    break;
                case 'completed':
                    statusBadge.classList.add('bg-gray-100', 'text-gray-800');
                    statusBadge.textContent = 'Completed';
                    break;
            }
        }
        
        // Update action button
        if (actionButton) {
            updateActionButton(actionButton, statusData);
        }
        
        // Update countdown timer
        if (countdownElement && statusData.time_until_start > 0) {
            updateCountdown(countdownElement, statusData.time_until_start);
        } else if (countdownElement) {
            countdownElement.style.display = 'none';
        }
    }
    
    function updateActionButton(button, statusData) {
        // Reset button classes
        button.className = 'action-button px-4 py-2 text-sm font-medium rounded-lg transition-colors';
        
        switch (statusData.status) {
            case 'not_started':
                button.classList.add('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
                button.textContent = 'Not Started Yet';
                button.disabled = true;
                button.onclick = null;
                break;
                
            case 'available':
                if (statusData.has_attempt) {
                    if (statusData.attempt_status === 'in_progress') {
                        button.classList.add('bg-blue-600', 'text-white', 'hover:bg-blue-700');
                        button.textContent = 'Continue Exam';
                        button.disabled = false;
                        button.onclick = () => window.location.href = `/student/exams/${statusData.exam_id}/take`;
                    } else {
                        button.classList.add('bg-green-600', 'text-white', 'hover:bg-green-700');
                        button.textContent = 'View Results';
                        button.disabled = false;
                        button.onclick = () => window.location.href = `/student/exams/${statusData.exam_id}/result`;
                    }
                } else if (statusData.can_take) {
                    button.classList.add('bg-green-600', 'text-white', 'hover:bg-green-700');
                    button.textContent = 'Start Exam';
                    button.disabled = false;
                    button.onclick = () => startExam(statusData.exam_id);
                } else {
                    button.classList.add('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
                    button.textContent = 'Max Attempts Reached';
                    button.disabled = true;
                }
                break;
                
            case 'ended':
                if (statusData.has_attempt) {
                    button.classList.add('bg-blue-600', 'text-white', 'hover:bg-blue-700');
                    button.textContent = 'View Results';
                    button.disabled = false;
                    button.onclick = () => window.location.href = `/student/exams/${statusData.exam_id}/result`;
                } else {
                    button.classList.add('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
                    button.textContent = 'Exam Ended';
                    button.disabled = true;
                }
                break;
                
            case 'completed':
                button.classList.add('bg-blue-600', 'text-white', 'hover:bg-blue-700');
                button.textContent = 'View Results';
                button.disabled = false;
                button.onclick = () => window.location.href = `/student/exams/${statusData.exam_id}/result`;
                break;
        }
    }
    
    function setupCountdownTimers() {
        const countdownElements = document.querySelectorAll('.countdown-timer');
        
        countdownElements.forEach(element => {
            const timeUntilStart = parseInt(element.dataset.timeUntilStart);
            if (timeUntilStart > 0) {
                startCountdown(element, timeUntilStart);
            }
        });
    }
    
    function startCountdown(element, seconds) {
        let remaining = seconds;
        
        const updateCountdown = () => {
            if (remaining <= 0) {
                element.textContent = 'Starting now...';
                element.classList.add('text-green-600', 'font-bold');
                setTimeout(() => {
                    location.reload(); // Refresh to show updated status
                }, 2000);
                return;
            }
            
            const hours = Math.floor(remaining / 3600);
            const minutes = Math.floor((remaining % 3600) / 60);
            const secs = remaining % 60;
            
            element.textContent = `Starts in ${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
            remaining--;
        };
        
        updateCountdown();
        setInterval(updateCountdown, 1000);
    }
    
    function updateCountdown(element, timeUntilStart) {
        const hours = Math.floor(timeUntilStart / 3600);
        const minutes = Math.floor((timeUntilStart % 3600) / 60);
        const seconds = timeUntilStart % 60;
        
        element.textContent = `Starts in ${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }
    
    // Handle start exam button clicks
    window.startExam = function(examId) {
        // Show confirmation dialog for better UX
        if (confirm('Are you ready to start this exam? Once started, the timer will begin counting down.')) {
            // Submit form to start exam
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/student/exams/${examId}/start`;
            
            // Add CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken.content;
                form.appendChild(csrfInput);
            }
            
            document.body.appendChild(form);
            form.submit();
        }
    };
    
    console.log('Student exam index JavaScript loaded');
});
