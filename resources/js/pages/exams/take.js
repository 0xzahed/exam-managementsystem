// Student Exam Taking JavaScript Module
class ExamTaker {
    constructor() {
        this.examConfig = window.examConfig;
        this.timer = null;
        this.autoSaveTimer = null;
        this.answers = {};
        this.timeRemaining = 0;
        this.hasUnsavedChanges = false;
        this.isSubmitting = false;
        
        this.init();
    }

    init() {
        this.calculateTimeRemaining();
        this.startTimer();
        this.startAutoSave();
        this.bindEvents();
        this.loadExistingAnswers();
        this.setupNavigationWarning();
    }

    calculateTimeRemaining() {
        const startTime = new Date(this.examConfig.startTime);
        const now = new Date();
        const elapsedMinutes = Math.floor((now - startTime) / (1000 * 60));
        this.timeRemaining = Math.max(0, this.examConfig.durationMinutes - elapsedMinutes);
    }

    startTimer() {
        this.updateTimerDisplay();
        
        this.timer = setInterval(() => {
            this.timeRemaining--;
            this.updateTimerDisplay();
            
            // Auto-submit when time is up
            if (this.timeRemaining <= 0) {
                this.autoSubmitExam();
            }
            
            // Warning when 5 minutes left
            if (this.timeRemaining === 5 * 60) {
                this.showTimeWarning();
            }
        }, 1000);
    }

    updateTimerDisplay() {
        const hours = Math.floor(this.timeRemaining / 3600);
        const minutes = Math.floor((this.timeRemaining % 3600) / 60);
        const seconds = this.timeRemaining % 60;
        
        const timerElement = document.getElementById('timer');
        if (timerElement) {
            const timeString = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            timerElement.textContent = timeString;
            
            // Change color based on time remaining
            if (this.timeRemaining <= 5 * 60) { // 5 minutes
                timerElement.className = 'font-mono text-lg font-bold text-red-600';
            } else if (this.timeRemaining <= 15 * 60) { // 15 minutes
                timerElement.className = 'font-mono text-lg font-bold text-yellow-600';
            } else {
                timerElement.className = 'font-mono text-lg font-bold text-green-600';
            }
        }
    }

    showTimeWarning() {
        // Create time warning notification
        const notification = document.createElement('div');
        notification.className = 'fixed top-20 right-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4 shadow-lg z-40 max-w-sm';
        notification.innerHTML = `
            <div class="flex">
                <svg class="w-5 h-5 text-yellow-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                <div>
                    <h4 class="text-yellow-800 font-medium">Time Warning</h4>
                    <p class="text-yellow-700 text-sm mt-1">Only 5 minutes remaining! Please finish your exam soon.</p>
                </div>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Remove notification after 10 seconds
        setTimeout(() => {
            notification.remove();
        }, 10000);
    }

    startAutoSave() {
        this.autoSaveTimer = setInterval(() => {
            this.autoSaveAnswers();
        }, this.examConfig.autoSaveInterval);
    }

    bindEvents() {
        // Answer change events
        this.bindAnswerEvents();
        
        // Submit button events
        document.getElementById('submitExamBtn')?.addEventListener('click', () => {
            this.showSubmitConfirmation();
        });
        
        document.getElementById('finalSubmit')?.addEventListener('click', () => {
            this.showSubmitConfirmation();
        });
        
        // Submit confirmation modal events
        document.getElementById('confirmSubmit')?.addEventListener('click', () => {
            this.submitExam();
        });
        
        document.getElementById('cancelSubmit')?.addEventListener('click', () => {
            this.hideSubmitConfirmation();
        });
        
        // Question navigation
        document.querySelectorAll('.question-nav-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.scrollToQuestion(parseInt(e.target.dataset.questionIndex));
            });
        });
        
        // Review answers button
        document.getElementById('reviewAnswers')?.addEventListener('click', () => {
            this.reviewAnswers();
        });
    }

    bindAnswerEvents() {
        // MCQ radio buttons
        document.querySelectorAll('input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                this.saveAnswer(e.target.dataset.questionId, e.target.value, 'mcq');
            });
        });
        
        // Short answer textareas
        document.querySelectorAll('textarea[data-question-id]').forEach(textarea => {
            textarea.addEventListener('input', (e) => {
                this.hasUnsavedChanges = true;
                this.updateAutoSaveStatus('saving');
                
                // Debounced save
                clearTimeout(this.saveTimeout);
                this.saveTimeout = setTimeout(() => {
                    this.saveAnswer(e.target.dataset.questionId, e.target.value, 'short_answer');
                }, 2000);
            });
        });
        
        // File upload inputs
        document.querySelectorAll('input[type="file"]').forEach(fileInput => {
            fileInput.addEventListener('change', (e) => {
                this.handleFileUpload(e);
            });
        });
    }

    saveAnswer(questionId, answer, type) {
        this.answers[questionId] = {
            answer: answer,
            type: type,
            timestamp: new Date().toISOString()
        };
        
        this.updateAnswerStatus(questionId, true);
        this.updateQuestionNavigation();
        this.hasUnsavedChanges = true;
    }

    async handleFileUpload(event) {
        const file = event.target.files[0];
        const questionId = event.target.dataset.questionId;
        
        if (!file) return;
        
        // Validate file size (10MB)
        if (file.size > 10 * 1024 * 1024) {
            alert('File size must be less than 10MB');
            event.target.value = '';
            return;
        }
        
        // Show upload progress
        const questionCard = event.target.closest('.question-card');
        const uploadedFileDiv = questionCard.querySelector('.uploaded-file');
        const fileNameSpan = uploadedFileDiv.querySelector('.file-name');
        
        fileNameSpan.textContent = file.name;
        uploadedFileDiv.classList.remove('hidden');
        
        // Upload file
        const formData = new FormData();
        formData.append('file', file);
        formData.append('question_id', questionId);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        
        try {
            const response = await fetch(this.examConfig.routes.saveAnswer, {
                method: 'POST',
                body: formData
            });
            
            if (response.ok) {
                const result = await response.json();
                this.saveAnswer(questionId, result.file_info, 'file_upload');
            } else {
                throw new Error('Upload failed');
            }
        } catch (error) {
            console.error('File upload error:', error);
            alert('Failed to upload file. Please try again.');
            uploadedFileDiv.classList.add('hidden');
            event.target.value = '';
        }
        
        // Remove file event
        uploadedFileDiv.querySelector('.remove-file')?.addEventListener('click', () => {
            event.target.value = '';
            uploadedFileDiv.classList.add('hidden');
            delete this.answers[questionId];
            this.updateAnswerStatus(questionId, false);
            this.updateQuestionNavigation();
        });
    }

    async autoSaveAnswers() {
        if (!this.hasUnsavedChanges || this.isSubmitting) return;
        
        this.updateAutoSaveStatus('saving');
        
        try {
            const response = await fetch(this.examConfig.routes.saveAnswer, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    answers: this.answers,
                    attempt_id: this.examConfig.attemptId
                })
            });
            
            if (response.ok) {
                this.hasUnsavedChanges = false;
                this.updateAutoSaveStatus('saved');
            }
        } catch (error) {
            console.error('Auto-save error:', error);
            this.updateAutoSaveStatus('error');
        }
    }

    updateAutoSaveStatus(status) {
        const statusElement = document.getElementById('autoSaveStatus');
        const savingSpan = statusElement?.querySelector('.saving');
        const savedSpan = statusElement?.querySelector('.saved');
        
        if (status === 'saving') {
            savingSpan?.classList.remove('hidden');
            savedSpan?.classList.add('hidden');
        } else if (status === 'saved') {
            savingSpan?.classList.add('hidden');
            savedSpan?.classList.remove('hidden');
        } else if (status === 'error') {
            savingSpan?.classList.add('hidden');
            savedSpan?.classList.add('hidden');
        }
        
        // Update individual question save status
        document.querySelectorAll('.question-card').forEach(card => {
            const indicator = card.querySelector('.auto-save-indicator');
            const savingSpan = indicator?.querySelector('.saving');
            const savedSpan = indicator?.querySelector('.saved');
            
            if (status === 'saving') {
                savingSpan?.classList.remove('hidden');
                savedSpan?.classList.add('hidden');
            } else {
                savingSpan?.classList.add('hidden');
                savedSpan?.classList.remove('hidden');
            }
        });
    }

    updateAnswerStatus(questionId, isAnswered) {
        const questionCard = document.querySelector(`[data-question-id="${questionId}"]`);
        const unansweredSpan = questionCard?.querySelector('.unanswered');
        const answeredSpan = questionCard?.querySelector('.answered');
        
        if (isAnswered) {
            unansweredSpan?.classList.add('hidden');
            answeredSpan?.classList.remove('hidden');
        } else {
            unansweredSpan?.classList.remove('hidden');
            answeredSpan?.classList.add('hidden');
        }
    }

    updateQuestionNavigation() {
        const answeredCount = Object.keys(this.answers).length;
        
        document.querySelectorAll('.question-nav-btn').forEach(btn => {
            const questionId = btn.dataset.questionId;
            
            if (this.answers[questionId]) {
                btn.classList.remove('border-gray-300', 'bg-white');
                btn.classList.add('bg-green-100', 'border-green-300', 'text-green-800');
            } else {
                btn.classList.remove('bg-green-100', 'border-green-300', 'text-green-800');
                btn.classList.add('border-gray-300', 'bg-white');
            }
        });
        
        // Update answered count in submit modal
        const answeredCountSpan = document.querySelector('.answered-count');
        if (answeredCountSpan) {
            answeredCountSpan.textContent = answeredCount;
        }
    }

    scrollToQuestion(index) {
        const questionCards = document.querySelectorAll('.question-card');
        if (questionCards[index]) {
            questionCards[index].scrollIntoView({ 
                behavior: 'smooth', 
                block: 'start' 
            });
            
            // Highlight current question in navigation
            document.querySelectorAll('.question-nav-btn').forEach(btn => {
                btn.classList.remove('bg-blue-100', 'border-blue-300', 'text-blue-800');
            });
            
            const currentBtn = document.querySelector(`[data-question-index="${index}"]`);
            if (currentBtn && !this.answers[currentBtn.dataset.questionId]) {
                currentBtn.classList.add('bg-blue-100', 'border-blue-300', 'text-blue-800');
            }
        }
    }

    reviewAnswers() {
        // Scroll to top and highlight unanswered questions
        window.scrollTo({ top: 0, behavior: 'smooth' });
        
        // Find unanswered questions
        const unansweredQuestions = [];
        document.querySelectorAll('.question-card').forEach((card, index) => {
            const questionId = card.dataset.questionId;
            if (!this.answers[questionId]) {
                unansweredQuestions.push(index + 1);
                card.classList.add('ring-2', 'ring-yellow-300');
                setTimeout(() => {
                    card.classList.remove('ring-2', 'ring-yellow-300');
                }, 3000);
            }
        });
        
        if (unansweredQuestions.length > 0) {
            alert(`You have ${unansweredQuestions.length} unanswered questions: ${unansweredQuestions.join(', ')}`);
        }
    }

    showSubmitConfirmation() {
        const modal = document.getElementById('submitConfirmation');
        if (modal) {
            modal.classList.remove('hidden');
            this.updateQuestionNavigation(); // Update answered count
        }
    }

    hideSubmitConfirmation() {
        const modal = document.getElementById('submitConfirmation');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    async submitExam() {
        if (this.isSubmitting) return;
        
        this.isSubmitting = true;
        this.hideSubmitConfirmation();
        
        // Clear timers
        clearInterval(this.timer);
        clearInterval(this.autoSaveTimer);
        
        // Show submission overlay
        this.showSubmissionOverlay();
        
        try {
            const response = await fetch(this.examConfig.routes.submit, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    answers: this.answers,
                    attempt_id: this.examConfig.attemptId,
                    time_spent: this.examConfig.durationMinutes - this.timeRemaining
                })
            });
            
            if (response.ok) {
                const result = await response.json();
                window.location.href = result.redirect_url;
            } else {
                throw new Error('Submission failed');
            }
        } catch (error) {
            console.error('Submission error:', error);
            alert('Failed to submit exam. Please try again.');
            this.isSubmitting = false;
            this.hideSubmissionOverlay();
            this.startTimer();
            this.startAutoSave();
        }
    }

    async autoSubmitExam() {
        clearInterval(this.timer);
        clearInterval(this.autoSaveTimer);
        
        this.showSubmissionOverlay('Time is up! Auto-submitting your exam...');
        
        try {
            const response = await fetch(this.examConfig.routes.submit, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    answers: this.answers,
                    attempt_id: this.examConfig.attemptId,
                    time_spent: this.examConfig.durationMinutes,
                    auto_submit: true
                })
            });
            
            if (response.ok) {
                const result = await response.json();
                window.location.href = result.redirect_url;
            }
        } catch (error) {
            console.error('Auto-submit error:', error);
        }
    }

    showSubmissionOverlay(message = 'Submitting your exam...') {
        const overlay = document.createElement('div');
        overlay.id = 'submissionOverlay';
        overlay.className = 'fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center';
        overlay.innerHTML = `
            <div class="bg-white rounded-xl p-8 text-center max-w-md mx-4">
                <div class="animate-spin w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full mx-auto mb-4"></div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">${message}</h3>
                <p class="text-gray-600">Please wait while we process your submission...</p>
            </div>
        `;
        
        document.body.appendChild(overlay);
    }

    hideSubmissionOverlay() {
        const overlay = document.getElementById('submissionOverlay');
        if (overlay) {
            overlay.remove();
        }
    }

    setupNavigationWarning() {
        // Prevent accidental navigation
        window.addEventListener('beforeunload', (e) => {
            if (this.hasUnsavedChanges && !this.isSubmitting) {
                e.preventDefault();
                e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
                return e.returnValue;
            }
        });
        
        // Show custom navigation warning
        const warningModal = document.getElementById('navigationWarning');
        const stayBtn = document.getElementById('stayOnPage');
        const leaveBtn = document.getElementById('leavePage');
        
        let pendingNavigation = null;
        
        // Intercept navigation attempts
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (link && link.href && !link.href.includes('#') && this.hasUnsavedChanges && !this.isSubmitting) {
                e.preventDefault();
                pendingNavigation = link.href;
                warningModal?.classList.remove('hidden');
            }
        });
        
        stayBtn?.addEventListener('click', () => {
            warningModal?.classList.add('hidden');
            pendingNavigation = null;
        });
        
        leaveBtn?.addEventListener('click', () => {
            if (pendingNavigation) {
                window.location.href = pendingNavigation;
            }
        });
    }

    loadExistingAnswers() {
        // Load any existing answers from previous saves
        // This would typically come from the server
        // For now, we'll check form values
        
        document.querySelectorAll('input[type="radio"]:checked').forEach(radio => {
            this.saveAnswer(radio.dataset.questionId, radio.value, 'mcq');
        });
        
        document.querySelectorAll('textarea[data-question-id]').forEach(textarea => {
            if (textarea.value.trim()) {
                this.saveAnswer(textarea.dataset.questionId, textarea.value, 'short_answer');
            }
        });
        
        this.updateQuestionNavigation();
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    if (window.examConfig) {
        new ExamTaker();
    }
});
