// Exam Creation Form JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Question management
    let questionCounter = 0;
    const questionsContainer = document.getElementById('questionsContainer');
    const noQuestions = document.getElementById('noQuestions');
    const addQuestionBtn = document.getElementById('addQuestionBtn');

    if (addQuestionBtn) {
        addQuestionBtn.addEventListener('click', function() {
            console.log('Add question button clicked');
            addQuestion();
        });
    }

    function addQuestion() {
        console.log('Adding question, current counter:', questionCounter);
        questionCounter++;
        const questionDiv = createQuestionElement(questionCounter);
        questionsContainer.appendChild(questionDiv);
        
        // Hide "no questions" message
        if (noQuestions) {
            noQuestions.style.display = 'none';
        }

        // Add event listeners to the new question
        setupQuestionEventListeners(questionDiv);
        console.log('Question added successfully');
    }

    function createQuestionElement(counter) {
        const questionDiv = document.createElement('div');
        questionDiv.className = 'question-item bg-gray-50 rounded-lg p-4 mb-4 border border-gray-200';
        questionDiv.innerHTML = `
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-lg font-medium text-gray-900">Question ${counter}</h4>
                <button type="button" class="remove-question text-red-600 hover:text-red-800" onclick="removeQuestion(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Question Type</label>
                    <select name="questions[${counter}][type]" class="question-type w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="toggleQuestionOptions(this, ${counter})">
                        <option value="mcq">Multiple Choice (MCQ)</option>
                        <option value="short_answer">Short Answer</option>
                        <option value="file_upload">File Upload</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Points</label>
                    <input type="number" name="questions[${counter}][points]" min="1" value="5" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                </div>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Question Text</label>
                <textarea name="questions[${counter}][question]" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter your question here..." required></textarea>
            </div>
            
            <!-- MCQ Options (shown by default) -->
            <div class="mcq-options" id="mcq-options-${counter}">
                <label class="block text-sm font-medium text-gray-700 mb-2">Answer Options</label>
                <div class="space-y-2">
                    <div class="flex items-center">
                        <input type="radio" name="questions[${counter}][correct_answer]" value="A" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500">
                        <label class="ml-2 text-sm text-gray-700">A)</label>
                        <input type="text" name="questions[${counter}][options][A]" class="ml-2 flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Option A">
                    </div>
                    <div class="flex items-center">
                        <input type="radio" name="questions[${counter}][correct_answer]" value="B" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500">
                        <label class="ml-2 text-sm text-gray-700">B)</label>
                        <input type="text" name="questions[${counter}][options][B]" class="ml-2 flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Option B">
                    </div>
                    <div class="flex items-center">
                        <input type="radio" name="questions[${counter}][correct_answer]" value="C" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500">
                        <label class="ml-2 text-sm text-gray-700">C)</label>
                        <input type="text" name="questions[${counter}][options][C]" class="ml-2 flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Option C">
                    </div>
                    <div class="flex items-center">
                        <input type="radio" name="questions[${counter}][correct_answer]" value="D" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500">
                        <label class="ml-2 text-sm text-gray-700">D)</label>
                        <input type="text" name="questions[${counter}][options][D]" class="ml-2 flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Option D">
                    </div>
                </div>
            </div>
            
            <!-- Short Answer Options (hidden by default) -->
            <div class="short-answer-options hidden" id="short-answer-options-${counter}">
                <div class="mb-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Expected Answer Length</label>
                    <select name="questions[${counter}][answer_length]" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="short">Short (1-2 sentences)</option>
                        <option value="medium">Medium (1-2 paragraphs)</option>
                        <option value="long">Long (3+ paragraphs)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sample Answer (Optional)</label>
                    <textarea name="questions[${counter}][sample_answer]" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Provide a sample answer for reference..."></textarea>
                </div>
            </div>
            
            <!-- File Upload Options (hidden by default) -->
            <div class="file-upload-options hidden" id="file-upload-options-${counter}">
                <div class="mb-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Allowed File Types</label>
                    <div class="flex flex-wrap gap-3">
                        <label class="flex items-center">
                            <input type="checkbox" name="questions[${counter}][allowed_files][]" value="pdf" checked class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">PDF</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="questions[${counter}][allowed_files][]" value="docx" checked class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">DOCX</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="questions[${counter}][allowed_files][]" value="jpg" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">JPG</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="questions[${counter}][allowed_files][]" value="png" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">PNG</span>
                        </label>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Maximum File Size (MB)</label>
                    <input type="number" name="questions[${counter}][max_file_size]" min="1" max="50" value="10" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
        `;
        
        return questionDiv;
    }

    function setupQuestionEventListeners(questionDiv) {
        const questionType = questionDiv.querySelector('.question-type');
        if (questionType) {
            questionType.addEventListener('change', function() {
                const questionNumber = questionDiv.querySelector('h4').textContent.match(/\d+/)[0];
                toggleQuestionOptions(this, questionNumber);
            });
            
            // Trigger initial setup for default selected type
            const questionNumber = questionDiv.querySelector('h4').textContent.match(/\d+/)[0];
            toggleQuestionOptions(questionType, questionNumber);
        }
    }

    // Global function for removing questions
    window.removeQuestion = function(button) {
        const questionItem = button.closest('.question-item');
        questionItem.remove();
        
        // Show "no questions" message if no questions left
        if (questionsContainer.children.length === 0 && noQuestions) {
            noQuestions.style.display = 'block';
        }
        
        // Update question numbers
        updateQuestionNumbers();
    };

    // Global function for toggling question options
    window.toggleQuestionOptions = function(select, questionNumber) {
        const questionDiv = select.closest('.question-item');
        const mcqOptions = questionDiv.querySelector(`#mcq-options-${questionNumber}`);
        const shortAnswerOptions = questionDiv.querySelector(`#short-answer-options-${questionNumber}`);
        const fileUploadOptions = questionDiv.querySelector(`#file-upload-options-${questionNumber}`);
        
        // Hide all options first and remove required attributes
        [mcqOptions, shortAnswerOptions, fileUploadOptions].forEach(div => {
            if (div) {
                div.classList.add('hidden');
                // Remove required from all inputs in this section
                div.querySelectorAll('input, textarea, select').forEach(input => {
                    input.removeAttribute('required');
                });
            }
        });
        
        // Show relevant options based on selection and set required attributes
        switch(select.value) {
            case 'mcq':
                if (mcqOptions) {
                    mcqOptions.classList.remove('hidden');
                    // Set required for MCQ fields
                    mcqOptions.querySelectorAll('input[name*="[options]"]').forEach(input => {
                        input.setAttribute('required', 'required');
                    });
                    mcqOptions.querySelectorAll('input[name*="[correct_answer]"]').forEach(input => {
                        input.setAttribute('required', 'required');
                    });
                }
                break;
            case 'short_answer':
                if (shortAnswerOptions) {
                    shortAnswerOptions.classList.remove('hidden');
                }
                break;
            case 'file_upload':
                if (fileUploadOptions) {
                    fileUploadOptions.classList.remove('hidden');
                }
                break;
        }
    };

    // Function to update question numbers
    function updateQuestionNumbers() {
        const questions = questionsContainer.querySelectorAll('.question-item');
        questions.forEach((question, index) => {
            const title = question.querySelector('h4');
            if (title) {
                title.textContent = `Question ${index + 1}`;
            }
        });
    }

    // Form validation and submission
    const examForm = document.getElementById('examForm');
    if (examForm) {
        examForm.addEventListener('submit', function(e) {
            console.log('Form submit triggered');
            
            // Add debugging
            console.log('Form data being submitted:');
            const formData = new FormData(examForm);
            for (let [key, value] of formData.entries()) {
                console.log(key, value);
            }
            
            // Check if basic validation passes
            const title = document.getElementById('title').value.trim();
            const courseId = document.getElementById('course_id').value;
            const startTime = document.getElementById('start_time').value;
            const endTime = document.getElementById('end_time').value;
            const questions = questionsContainer.querySelectorAll('.question-item');
            
            if (!title) {
                e.preventDefault();
                showError('Please enter an exam title.');
                return;
            }
            
            if (!courseId) {
                e.preventDefault();
                showError('Please select a course.');
                return;
            }
            
            if (!startTime || !endTime) {
                e.preventDefault();
                showError('Please set start and end times.');
                return;
            }
            
            if (questions.length === 0) {
                e.preventDefault();
                showError('Please add at least one question.');
                return;
            }
            
            // Validate each question based on its type
            let validationError = null;
            questions.forEach((questionItem, index) => {
                const questionType = questionItem.querySelector('select[name*="[type]"]').value;
                const questionText = questionItem.querySelector('textarea[name*="[question]"]').value.trim();
                const questionPoints = questionItem.querySelector('input[name*="[points]"]').value;
                
                if (!questionText) {
                    validationError = `Question ${index + 1}: Please enter question text.`;
                    return;
                }
                
                if (!questionPoints || questionPoints < 1) {
                    validationError = `Question ${index + 1}: Please enter valid points (minimum 1).`;
                    return;
                }
                
                // Type-specific validation
                if (questionType === 'mcq') {
                    const options = questionItem.querySelectorAll('input[name*="[options]"]');
                    const correctAnswer = questionItem.querySelector('input[name*="[correct_answer]"]:checked');
                    
                    let emptyOptions = 0;
                    options.forEach(option => {
                        if (!option.value.trim()) emptyOptions++;
                    });
                    
                    if (emptyOptions > 0) {
                        validationError = `Question ${index + 1}: Please fill in all MCQ options.`;
                        return;
                    }
                    
                    if (!correctAnswer) {
                        validationError = `Question ${index + 1}: Please select the correct answer for MCQ.`;
                        return;
                    }
                } else if (questionType === 'file_upload') {
                    const allowedFiles = questionItem.querySelectorAll('input[name*="[allowed_files]"]:checked');
                    if (allowedFiles.length === 0) {
                        validationError = `Question ${index + 1}: Please select at least one allowed file type.`;
                        return;
                    }
                }
            });
            
            if (validationError) {
                e.preventDefault();
                showError(validationError);
                return;
            }
            
            // Show loading state
            const submitButtons = document.querySelectorAll('button[type="submit"]');
            submitButtons.forEach(btn => {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creating...';
            });
            
            console.log('Form validation passed, submitting...');
        });
    }

    // Add click handlers for action buttons
    const createAnotherBtn = document.getElementById('createAnotherBtn');
    const goToDetailsBtn = document.getElementById('goToDetailsBtn');
    const submitBtn = document.getElementById('submitBtn');
    
    if (createAnotherBtn) {
        createAnotherBtn.addEventListener('click', function() {
            // Remove other action values
            document.querySelectorAll('input[name="create_another"], input[name="go_to_details"]').forEach(input => {
                input.remove();
            });
        });
    }
    
    if (goToDetailsBtn) {
        goToDetailsBtn.addEventListener('click', function() {
            // Remove other action values
            document.querySelectorAll('input[name="create_another"], input[name="go_to_details"]').forEach(input => {
                input.remove();
            });
        });
    }
    
    if (submitBtn) {
        submitBtn.addEventListener('click', function() {
            // Remove other action values
            document.querySelectorAll('input[name="create_another"], input[name="go_to_details"]').forEach(input => {
                input.remove();
            });
        });
    }

    function validateForm() {
        const title = document.getElementById('title').value.trim();
        const courseId = document.getElementById('course_id').value;
        const duration = document.getElementById('duration_minutes').value;
        const startTime = document.getElementById('start_time').value;
        const endTime = document.getElementById('end_time').value;
        
        if (!title || !courseId || !duration || !startTime || !endTime) {
            return false;
        }
        
        const questions = questionsContainer.querySelectorAll('.question-item');
        if (questions.length === 0) {
            return false;
        }
        
        return true;
    }

    // Debug function to check form data
    window.debugFormData = function() {
        const formData = new FormData(examForm);
        console.log('Form data debug:');
        for (let [key, value] of formData.entries()) {
            console.log(key, value);
        }
        
        const questions = questionsContainer.querySelectorAll('.question-item');
        console.log('Questions found:', questions.length);
        
        questions.forEach((questionItem, index) => {
            const questionType = questionItem.querySelector('select[name*="[type]"]').value;
            console.log(`Question ${index + 1} type:`, questionType);
        });
    };

    // Auto-set end time based on start time and duration
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');
    const durationInput = document.getElementById('duration_minutes');

    if (startTimeInput && endTimeInput && durationInput) {
        startTimeInput.addEventListener('change', updateEndTime);
        durationInput.addEventListener('change', updateEndTime);
    }

    function updateEndTime() {
        if (startTimeInput.value && durationInput.value) {
            const startTime = new Date(startTimeInput.value);
            const duration = parseInt(durationInput.value);
            const endTime = new Date(startTime.getTime() + (duration * 60 * 1000));
            
            endTimeInput.value = endTime.toISOString().slice(0, 16);
        }
    }

    // Don't auto-initialize, let user add questions manually
    console.log('Exam creation form JavaScript loaded');
});
