// Exam Index Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Filter functionality
    window.filterExams = function(filterType, event) {
        // Remove active class from all buttons
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('active', 'bg-blue-600', 'text-white');
            btn.classList.add('bg-gray-100', 'text-gray-700');
        });
        
        // Add active class to clicked button
        event.target.classList.remove('bg-gray-100', 'text-gray-700');
        event.target.classList.add('active', 'bg-blue-600', 'text-white');
        
            // Filter logic
        const examCards = document.querySelectorAll('.exam-card');
        
        examCards.forEach(card => {
            let shouldShow = true;
            
            switch(filterType) {
                case 'draft':
                    shouldShow = card.dataset.status === 'draft';
                    break;
                case 'available':
                    shouldShow = card.dataset.availability === 'available';
                    break;
                case 'completed':
                    shouldShow = card.dataset.completion === 'completed';
                    break;
                case 'pending':
                    shouldShow = card.dataset.completion === 'pending';
                    break;
                case 'all':
                default:
                    shouldShow = true;
                    break;
            }
            
            if (shouldShow) {
                card.style.display = 'block';
                card.classList.add('fade-in');
            } else {
                card.style.display = 'none';
                card.classList.remove('fade-in');
            }
        });
    };
    
    // Add fade-in animation to exam cards
    const examCards = document.querySelectorAll('.exam-card');
    examCards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        card.classList.add('fade-in');
    });

    // Real-time exam status updates
    class ExamStatusUpdater {
        constructor() {
            this.updateInterval = 30000; // 30 seconds
            this.timers = new Map();
            this.init();
        }

        init() {
            this.updateStatuses();
            setInterval(() => {
                this.updateStatuses();
            }, this.updateInterval);
            
            // Initialize countdown timers for upcoming exams
            this.initCountdownTimers();
        }

        async updateStatuses() {
            try {
                const response = await fetch('/student/exams/status', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    this.updateExamCards(data.exams);
                    this.updateStatistics(data.statistics);
                }
            } catch (error) {
                console.error('Error updating exam statuses:', error);
            }
        }

        updateExamCards(exams) {
            exams.forEach(examData => {
                const examCard = document.querySelector(`[data-exam-id="${examData.id}"]`);
                if (!examCard) return;

                // Update card datasets
                examCard.dataset.status = examData.status;
                examCard.dataset.completion = examData.is_completed ? 'completed' : 'pending';
                examCard.dataset.availability = examData.can_take ? 'available' : 'unavailable';

                // Update status badges
                this.updateStatusBadges(examCard, examData);
                
                // Update time status
                this.updateTimeStatus(examCard, examData);
                
                // Update action button
                this.updateActionButton(examCard, examData);
                
                // Update countdown timer if needed
                this.updateCountdown(examCard, examData);
            });
        }

        updateStatusBadges(examCard, examData) {
            const statusContainer = examCard.querySelector('.status-badges');
            if (!statusContainer) return;

            let statusHTML = '';
            
            // Course badge
            if (examData.course) {
                statusHTML += `<span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full">${examData.course}</span>`;
            }

            // Status badge
            const statusColors = {
                'published': 'bg-green-100 text-green-800',
                'draft': 'bg-yellow-100 text-yellow-800',
                'closed': 'bg-gray-100 text-gray-800'
            };
            statusHTML += `<span class="text-sm font-medium px-3 py-1 rounded-full ${statusColors[examData.status] || 'bg-gray-100 text-gray-800'}">${examData.status.charAt(0).toUpperCase() + examData.status.slice(1)}</span>`;

            // Completion/availability badge
            if (examData.is_completed) {
                statusHTML += `<span class="bg-green-100 text-green-800 text-sm font-medium px-3 py-1 rounded-full"><i class="fas fa-check mr-1"></i>Completed</span>`;
            } else if (examData.can_take) {
                statusHTML += `<span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full"><i class="fas fa-play mr-1"></i>Available</span>`;
            } else {
                statusHTML += `<span class="bg-gray-100 text-gray-800 text-sm font-medium px-3 py-1 rounded-full"><i class="fas fa-lock mr-1"></i>Not Available</span>`;
            }

            statusContainer.innerHTML = statusHTML;
        }

        updateTimeStatus(examCard, examData) {
            const timeStatusContainer = examCard.querySelector('.time-status');
            if (!timeStatusContainer) return;

            let timeStatusHTML = '';

            if (examData.is_active) {
                timeStatusHTML = `
                    <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-check-circle text-green-500"></i>
                            <span class="text-green-800 font-medium">Exam is currently active and available</span>
                        </div>
                    </div>`;
            } else if (examData.time_until_start > 0) {
                const timeStr = this.formatTimeRemaining(examData.time_until_start);
                timeStatusHTML = `
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-clock text-yellow-500"></i>
                            <span class="text-yellow-800 font-medium">Starts in ${timeStr}</span>
                        </div>
                        <div class="countdown-timer mt-2 text-lg font-bold text-yellow-900" data-target="${examData.start_timestamp}"></div>
                    </div>`;
            } else if (examData.time_until_end < 0) {
                timeStatusHTML = `
                    <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-times-circle text-red-500"></i>
                            <span class="text-red-800 font-medium">Exam has ended</span>
                        </div>
                    </div>`;
            }

            timeStatusContainer.innerHTML = timeStatusHTML;
        }

        updateActionButton(examCard, examData) {
            const actionContainer = examCard.querySelector('.action-buttons');
            if (!actionContainer) return;

            let buttonHTML = '';

            if (examData.can_take) {
                buttonHTML = `
                    <form action="/student/exams/${examData.id}/start" method="POST" class="inline w-full">
                        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')}">
                        <button type="submit" class="w-full bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white text-center py-3 px-4 rounded-lg font-medium flex items-center justify-center gap-2">
                            <i class="fas fa-play"></i>
                            Start Exam
                        </button>
                    </form>`;
            } else if (examData.is_completed) {
                buttonHTML = `
                    <a href="/student/exams/${examData.id}/result" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-center py-3 px-4 rounded-lg font-medium flex items-center justify-center gap-2">
                        <i class="fas fa-chart-bar"></i>
                        View Results
                    </a>`;
            } else if (examData.time_until_start > 0) {
                buttonHTML = `
                    <button disabled class="w-full bg-yellow-300 text-yellow-700 text-center py-3 px-4 rounded-lg font-medium cursor-not-allowed flex items-center justify-center gap-2">
                        <i class="fas fa-clock"></i>
                        Not Started Yet
                    </button>`;
            } else if (examData.time_until_end < 0) {
                buttonHTML = `
                    <button disabled class="w-full bg-red-300 text-red-700 text-center py-3 px-4 rounded-lg font-medium cursor-not-allowed flex items-center justify-center gap-2">
                        <i class="fas fa-times-circle"></i>
                        Exam Ended
                    </button>`;
            } else {
                buttonHTML = `
                    <button disabled class="w-full bg-gray-300 text-gray-500 text-center py-3 px-4 rounded-lg font-medium cursor-not-allowed flex items-center justify-center gap-2">
                        <i class="fas fa-lock"></i>
                        Not Available
                    </button>`;
            }

            actionContainer.innerHTML = buttonHTML + `
                <a href="#" onclick="showExamDetails(${examData.id})" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 text-center py-3 px-4 rounded-lg font-medium flex items-center justify-center gap-2">
                    <i class="fas fa-info-circle"></i>
                    Exam Details
                </a>`;
        }

        updateCountdown(examCard, examData) {
            const countdownElement = examCard.querySelector('.countdown-timer');
            if (!countdownElement) return;

            const targetTime = parseInt(countdownElement.dataset.target);
            if (isNaN(targetTime)) return;

            // Clear existing timer for this element
            const existingTimer = this.timers.get(countdownElement);
            if (existingTimer) {
                clearInterval(existingTimer);
            }

            // Start new countdown timer
            const timer = setInterval(() => {
                const now = Math.floor(Date.now() / 1000);
                const timeLeft = targetTime - now;

                if (timeLeft <= 0) {
                    clearInterval(timer);
                    this.timers.delete(countdownElement);
                    countdownElement.textContent = 'Starting now...';
                    // Trigger status update when countdown reaches zero
                    setTimeout(() => this.updateStatuses(), 1000);
                } else {
                    countdownElement.textContent = this.formatTimeRemaining(timeLeft);
                }
            }, 1000);

            this.timers.set(countdownElement, timer);
        }

        initCountdownTimers() {
            document.querySelectorAll('.countdown-timer').forEach(element => {
                const targetTime = parseInt(element.dataset.target);
                if (!isNaN(targetTime)) {
                    this.updateSingleCountdown(element, targetTime);
                }
            });
        }

        updateSingleCountdown(element, targetTime) {
            const timer = setInterval(() => {
                const now = Math.floor(Date.now() / 1000);
                const timeLeft = targetTime - now;

                if (timeLeft <= 0) {
                    clearInterval(timer);
                    element.textContent = 'Starting now...';
                    // Trigger status update when countdown reaches zero
                    setTimeout(() => this.updateStatuses(), 1000);
                } else {
                    element.textContent = this.formatTimeRemaining(timeLeft);
                }
            }, 1000);

            this.timers.set(element, timer);
        }

        formatTimeRemaining(seconds) {
            if (seconds < 60) {
                return `${seconds} second${seconds !== 1 ? 's' : ''}`;
            } else if (seconds < 3600) {
                const minutes = Math.floor(seconds / 60);
                return `${minutes} minute${minutes !== 1 ? 's' : ''}`;
            } else if (seconds < 86400) {
                const hours = Math.floor(seconds / 3600);
                const minutes = Math.floor((seconds % 3600) / 60);
                return `${hours}h ${minutes}m`;
            } else {
                const days = Math.floor(seconds / 86400);
                const hours = Math.floor((seconds % 86400) / 3600);
                return `${days}d ${hours}h`;
            }
        }

        updateStatistics(stats) {
            // Update the statistics cards
            const availableElement = document.querySelector('.stat-available');
            const completedElement = document.querySelector('.stat-completed');
            const pendingElement = document.querySelector('.stat-pending');
            const averageElement = document.querySelector('.stat-average');

            if (availableElement) availableElement.textContent = stats.available;
            if (completedElement) completedElement.textContent = stats.completed;
            if (pendingElement) pendingElement.textContent = stats.pending;
            if (averageElement) averageElement.textContent = stats.average_score ? `${stats.average_score}%` : 'N/A';
        }
    }

    // Initialize the status updater
    new ExamStatusUpdater();
});
