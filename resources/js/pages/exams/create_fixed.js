// Exam Edit Form JavaScript - Fixed and Simplified
document.addEventListener('DOMContentLoaded', function() {
    console.log('Exam form JavaScript loaded');
    console.log('Window exam data:', window.examData);
    
    // Initialize question counter
    let questionCounter = 0;
    
    // Get DOM elements
    const questionsContainer = document.getElementById('questionsContainer');
    const noQuestions = document.getElementById('noQuestions');
    const addQuestionBtn = document.getElementById('addQuestionBtn');
    
    console.log('Questions container found:', !!questionsContainer);
    console.log('Add question button found:', !!addQuestionBtn);
    console.log('No questions element found:', !!noQuestions);
    
    // Load existing questions if in edit mode
    if (window.examData && window.examData.isEditMode && window.examData.questions) {
        console.log('Loading existing questions:', window.examData.questions);
        
        if (window.examData.questions.length > 0) {
            // Hide no questions message
            if (noQuestions) {
                noQuestions.style.display = 'none';
            }
            
            // Load each question
            window.examData.questions.forEach((question, index) => {
                console.log('Loading question:', index + 1, question);
                addExistingQuestion(question, index);
                questionCounter = index + 1;
            });
        } else {
            // Show no questions message
            if (noQuestions) {
                noQuestions.style.display = 'block';
            }
        }
    }
    
    // Add question button click handler
    if (addQuestionBtn) {
        addQuestionBtn.addEventListener('click', function() {
            console.log('Add question button clicked');
            addNewQuestion();
        });
    }
    
    // Function to add new empty question
    function addNewQuestion() {
        if (!questionsContainer) {
            console.error('Questions container not found!');
            return;
        }
        
        questionCounter++;
        console.log('Adding new question, counter:', questionCounter);
        
        const questionHtml = createQuestionHTML(questionCounter, {});
        questionsContainer.insertAdjacentHTML('beforeend', questionHtml);
        
        // Hide no questions message
        if (noQuestions) {
            noQuestions.style.display = 'none';
        }
        
        // Add event listeners to the new question
        setupQuestionEventListeners(questionsContainer.lastElementChild, questionCounter);
        
        console.log('Question added successfully');
    }
    
    // Function to add existing question
    function addExistingQuestion(questionData, index) {
        if (!questionsContainer) {
            console.error('Questions container not found!');
            return;
        }
        
        const questionNumber = index + 1;
        console.log('Adding existing question:', questionNumber, questionData);
        
        const questionHtml = createQuestionHTML(questionNumber, questionData);
        questionsContainer.insertAdjacentHTML('beforeend', questionHtml);
        
        // Setup event listeners
        setupQuestionEventListeners(questionsContainer.lastElementChild, questionNumber);
        
        // Populate data for existing question
        populateQuestionData(questionsContainer.lastElementChild, questionData, questionNumber);
        
        console.log('Existing question loaded successfully');
    }
    
    // Function to create question HTML
    function createQuestionHTML(questionNumber, questionData) {
        const questionIndex = questionNumber - 1; // Array index
        
        return `
            <div class="question-item bg-gray-50 rounded-lg p-4 mb-4 border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-lg font-medium text-gray-900">Question ${questionNumber}</h4>
                    <button type="button" class="remove-question text-red-600 hover:text-red-800">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Question Type</label>
                        <select name="questions[${questionIndex}][type]" class="question-type w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="mcq" ${questionData.type === 'mcq' ? 'selected' : ''}>Multiple Choice (MCQ)</option>
                            <option value="short_answer" ${questionData.type === 'short_answer' ? 'selected' : ''}>Short Answer</option>
                            <option value="file_upload" ${questionData.type === 'file_upload' ? 'selected' : ''}>File Upload</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Marks</label>
                        <input type="number" name="questions[${questionIndex}][points]" min="1" value="${questionData.points || 5}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Question Text</label>
                    <textarea name="questions[${questionIndex}][question]" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                              placeholder="Enter your question here..." required>${questionData.question || ''}</textarea>
                </div>
                
                <!-- MCQ Options -->
                <div class="mcq-options" style="${questionData.type === 'mcq' ? '' : 'display: none;'}">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Answer Options</label>
                    <div class="space-y-2">
                        ${['A', 'B', 'C', 'D'].map(letter => `
                            <div class="flex items-center">
                                <input type="radio" name="questions[${questionIndex}][correct_answer]" value="${letter}" 
                                       ${questionData.correct_answer === letter ? 'checked' : ''} 
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500">
                                <label class="ml-2 text-sm text-gray-700">${letter})</label>
                                <input type="text" name="questions[${questionIndex}][options][${letter}]" 
                                       value="${questionData.options && questionData.options[letter] ? questionData.options[letter] : ''}" 
                                       class="ml-2 flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                       placeholder="Option ${letter}">
                            </div>
                        `).join('')}
                    </div>
                </div>
                
                <!-- Short Answer Options -->
                <div class="short-answer-options" style="${questionData.type === 'short_answer' ? '' : 'display: none;'}">
                    <div class="mb-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Expected Answer Length</label>
                        <select name="questions[${questionIndex}][answer_length]" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="short" ${questionData.answer_length === 'short' ? 'selected' : ''}>Short (1-2 sentences)</option>
                            <option value="medium" ${questionData.answer_length === 'medium' ? 'selected' : ''}>Medium (1-2 paragraphs)</option>
                            <option value="long" ${questionData.answer_length === 'long' ? 'selected' : ''}>Long (3+ paragraphs)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Sample Answer (Optional)</label>
                        <textarea name="questions[${questionIndex}][sample_answer]" rows="2" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                  placeholder="Provide a sample answer for reference...">${questionData.sample_answer || ''}</textarea>
                    </div>
                </div>
                
                <!-- File Upload Options -->
                <div class="file-upload-options" style="${questionData.type === 'file_upload' ? '' : 'display: none;'}">
                    <div class="mb-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Allowed File Types</label>
                        <div class="flex flex-wrap gap-3">
                            ${['pdf', 'docx', 'jpg', 'png'].map(fileType => `
                                <label class="flex items-center">
                                    <input type="checkbox" name="questions[${questionIndex}][allowed_files][]" value="${fileType}" 
                                           ${questionData.allowed_files && questionData.allowed_files.includes(fileType) ? 'checked' : (fileType === 'pdf' || fileType === 'docx' ? 'checked' : '')} 
                                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">${fileType.toUpperCase()}</span>
                                </label>
                            `).join('')}
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Maximum File Size (MB)</label>
                        <input type="number" name="questions[${questionIndex}][max_file_size]" min="1" max="50" 
                               value="${questionData.max_file_size || 10}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>
        `;
    }
    
    // Function to setup event listeners for a question
    function setupQuestionEventListeners(questionElement, questionNumber) {
        // Remove button handler
        const removeBtn = questionElement.querySelector('.remove-question');
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to remove this question?')) {
                    questionElement.remove();
                    
                    // Show no questions message if container is empty
                    if (questionsContainer.children.length === 0 && noQuestions) {
                        noQuestions.style.display = 'block';
                    }
                    
                    // Update question numbers
                    updateQuestionNumbers();
                }
            });
        }
        
        // Question type change handler
        const typeSelect = questionElement.querySelector('.question-type');
        if (typeSelect) {
            typeSelect.addEventListener('change', function() {
                toggleQuestionOptions(questionElement, this.value);
            });
            
            // Initialize with current value
            toggleQuestionOptions(questionElement, typeSelect.value);
        }
    }
    
    // Function to populate existing question data
    function populateQuestionData(questionElement, questionData, questionNumber) {
        // Data is already populated in the HTML template
        // Just trigger the type change to show correct options
        const typeSelect = questionElement.querySelector('.question-type');
        if (typeSelect) {
            toggleQuestionOptions(questionElement, typeSelect.value);
        }
    }
    
    // Function to toggle question options based on type
    function toggleQuestionOptions(questionElement, type) {
        const mcqOptions = questionElement.querySelector('.mcq-options');
        const shortAnswerOptions = questionElement.querySelector('.short-answer-options');
        const fileUploadOptions = questionElement.querySelector('.file-upload-options');
        
        // Hide all options first
        if (mcqOptions) mcqOptions.style.display = 'none';
        if (shortAnswerOptions) shortAnswerOptions.style.display = 'none';
        if (fileUploadOptions) fileUploadOptions.style.display = 'none';
        
        // Show relevant options
        switch(type) {
            case 'mcq':
                if (mcqOptions) mcqOptions.style.display = 'block';
                break;
            case 'short_answer':
                if (shortAnswerOptions) shortAnswerOptions.style.display = 'block';
                break;
            case 'file_upload':
                if (fileUploadOptions) fileUploadOptions.style.display = 'block';
                break;
        }
    }
    
    // Function to update question numbers
    function updateQuestionNumbers() {
        const questions = questionsContainer.querySelectorAll('.question-item');
        questions.forEach((question, index) => {
            const title = question.querySelector('h4');
            const questionNumber = index + 1;
            
            if (title) {
                title.textContent = `Question ${questionNumber}`;
            }
            
            // Update form field names
            const inputs = question.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                if (input.name && input.name.includes('questions[')) {
                    const fieldName = input.name.replace(/questions\[\d+\]/, `questions[${index}]`);
                    input.name = fieldName;
                }
            });
        });
        
        questionCounter = questions.length;
    }
    
    console.log('Exam edit form JavaScript initialized successfully');
});
