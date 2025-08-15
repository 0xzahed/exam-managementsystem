document.addEventListener("DOMContentLoaded",function(){console.log("=== JAVASCRIPT LOADED ==="),console.log("Exam form JavaScript loaded"),console.log("Window exam data exists:",!!window.examData),console.log("Window exam data:",window.examData);let a=0,c=0;const l=document.getElementById("questionsContainer"),i=document.getElementById("noQuestions"),u=document.getElementById("addQuestionBtn"),d=document.getElementById("cohortsContainer"),m=document.getElementById("addCohortBtn");if(console.log("=== DOM ELEMENTS CHECK ==="),console.log("Questions container found:",!!l),console.log("Add question button found:",!!u),console.log("No questions element found:",!!i),console.log("Cohorts container found:",!!d),console.log("Add cohort button found:",!!m),!l){console.error("CRITICAL: questionsContainer not found!");return}if(!u){console.error("CRITICAL: addQuestionBtn not found!");return}console.log("=== CHECKING EDIT MODE DATA ==="),window.examData&&window.examData.isEditMode&&window.examData.questions?(console.log("Edit mode detected"),console.log("Questions array:",window.examData.questions),console.log("Questions length:",window.examData.questions.length),window.examData.questions.length>0?(console.log("=== LOADING EXISTING QUESTIONS ==="),i&&(i.style.display="none"),window.examData.questions.forEach((o,e)=>{console.log(`Loading question ${e+1}:`,o),x(o,e),a=e+1})):(console.log("No existing questions found, showing no questions message"),i&&(i.style.display="block"))):(console.log("Not in edit mode or no questions data"),console.log("window.examData exists:",!!window.examData),console.log("isEditMode:",window.examData?window.examData.isEditMode:"N/A"),console.log("questions exists:",window.examData?!!window.examData.questions:"N/A")),u&&(u.addEventListener("click",function(){console.log("=== ADD QUESTION CLICKED ==="),y()}),console.log("Add question button event listener added")),m&&(m.addEventListener("click",function(){console.log("=== ADD COHORT CLICKED ==="),w()}),console.log("Add cohort button event listener added"));function y(){if(!l){console.error("Questions container not found!");return}a++,console.log("Adding new question, counter:",a);const o=p(a,{});l.insertAdjacentHTML("beforeend",o),i&&(i.style.display="none"),f(l.lastElementChild),console.log("Question added successfully")}function x(o,e){if(!l){console.error("Questions container not found!");return}const t=e+1;console.log("Adding existing question:",t,o);const s=p(t,o);l.insertAdjacentHTML("beforeend",s),f(l.lastElementChild),h(l.lastElementChild),console.log("Existing question loaded successfully")}function p(o,e){const t=o-1;return`
            <div class="question-item bg-gray-50 rounded-lg p-4 mb-4 border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-lg font-medium text-gray-900">Question ${o}</h4>
                    <button type="button" class="remove-question text-red-600 hover:text-red-800">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Question Type</label>
                        <select name="questions[${t}][type]" class="question-type w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="mcq" ${e.type==="mcq"?"selected":""}>Multiple Choice (MCQ)</option>
                            <option value="short_answer" ${e.type==="short_answer"?"selected":""}>Short Answer</option>
                            <option value="file_upload" ${e.type==="file_upload"?"selected":""}>File Upload</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Marks</label>
                        <input type="number" name="questions[${t}][points]" min="1" value="${e.points||5}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Question Text</label>
                    <textarea name="questions[${t}][question]" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                              placeholder="Enter your question here..." required>${e.question||""}</textarea>
                </div>
                
                <!-- MCQ Options -->
                <div class="mcq-options" style="${e.type==="mcq"?"":"display: none;"}">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Answer Options</label>
                    <div class="space-y-2">
                        ${["A","B","C","D"].map(s=>`
                            <div class="flex items-center">
                                <input type="radio" name="questions[${t}][correct_answer]" value="${s}" 
                                       ${e.correct_answer===s?"checked":""} 
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500">
                                <label class="ml-2 text-sm text-gray-700">${s})</label>
                                <input type="text" name="questions[${t}][options][${s}]" 
                                       value="${e.options&&e.options[s]?e.options[s]:""}" 
                                       class="ml-2 flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                       placeholder="Option ${s}">
                            </div>
                        `).join("")}
                    </div>
                </div>
                
                <!-- Short Answer Options -->
                <div class="short-answer-options" style="${e.type==="short_answer"?"":"display: none;"}">
                    <div class="mb-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Expected Answer Length</label>
                        <select name="questions[${t}][answer_length]" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="short" ${e.answer_length==="short"?"selected":""}>Short (1-2 sentences)</option>
                            <option value="medium" ${e.answer_length==="medium"?"selected":""}>Medium (1-2 paragraphs)</option>
                            <option value="long" ${e.answer_length==="long"?"selected":""}>Long (3+ paragraphs)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Sample Answer (Optional)</label>
                        <textarea name="questions[${t}][sample_answer]" rows="2" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                  placeholder="Provide a sample answer for reference...">${e.sample_answer||""}</textarea>
                    </div>
                </div>
                
                <!-- File Upload Options -->
                <div class="file-upload-options" style="${e.type==="file_upload"?"":"display: none;"}">
                    <div class="mb-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Allowed File Types</label>
                        <div class="flex flex-wrap gap-3">
                            ${["pdf","docx","jpg","png"].map(s=>`
                                <label class="flex items-center">
                                    <input type="checkbox" name="questions[${t}][allowed_files][]" value="${s}" 
                                           ${e.allowed_files&&e.allowed_files.includes(s)||s==="pdf"||s==="docx"?"checked":""} 
                                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">${s.toUpperCase()}</span>
                                </label>
                            `).join("")}
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Maximum File Size (MB)</label>
                        <input type="number" name="questions[${t}][max_file_size]" min="1" max="50" 
                               value="${e.max_file_size||10}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>
        `}function f(o,e){const t=o.querySelector(".remove-question");t&&t.addEventListener("click",function(){confirm("Are you sure you want to remove this question?")&&(o.remove(),l.children.length===0&&i&&(i.style.display="block"),v())});const s=o.querySelector(".question-type");s&&(s.addEventListener("change",function(){b(o,this.value)}),b(o,s.value))}function h(o,e,t){const s=o.querySelector(".question-type");s&&b(o,s.value)}function b(o,e){const t=o.querySelector(".mcq-options"),s=o.querySelector(".short-answer-options"),n=o.querySelector(".file-upload-options");switch(t&&(t.style.display="none"),s&&(s.style.display="none"),n&&(n.style.display="none"),e){case"mcq":t&&(t.style.display="block");break;case"short_answer":s&&(s.style.display="block");break;case"file_upload":n&&(n.style.display="block");break}}function v(){const o=l.querySelectorAll(".question-item");o.forEach((e,t)=>{const s=e.querySelector("h4"),n=t+1;s&&(s.textContent=`Question ${n}`),e.querySelectorAll("input, textarea, select").forEach(r=>{if(r.name&&r.name.includes("questions[")){const g=r.name.replace(/questions\[\d+\]/,`questions[${t}]`);r.name=g}})}),a=o.length}function w(){if(!d){console.error("Cohorts container not found!");return}c++,console.log("Adding new cohort, counter:",c);const o=$(c,{});d.insertAdjacentHTML("beforeend",o),q(d.lastElementChild),console.log("Cohort added successfully")}function $(o,e){const t=o-1,s=window.examData&&window.examData.students?window.examData.students.map(n=>`<option value="${n.id}" ${e.student_ids&&e.student_ids.includes(n.id)?"selected":""}>${n.name} (${n.email})</option>`).join(""):"";return`
            <div class="cohort-item bg-gray-50 rounded-lg p-4 mb-4 border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-lg font-medium text-gray-900">Cohort ${o}</h4>
                    <button type="button" class="remove-cohort text-red-600 hover:text-red-800">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Cohort Name</label>
                        <input type="text" name="cohorts[${t}][name]" value="${e.name||""}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                               placeholder="Enter cohort name..." required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Max Attempts</label>
                        <input type="number" name="cohorts[${t}][max_attempts]" min="1" value="${e.max_attempts||1}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Start Time (Optional)</label>
                        <input type="datetime-local" name="cohorts[${t}][start_time]" 
                               value="${e.start_time||""}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">End Time (Optional)</label>
                        <input type="datetime-local" name="cohorts[${t}][end_time]" 
                               value="${e.end_time||""}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Assign Students</label>
                    <select name="cohorts[${t}][student_ids][]" multiple 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            style="height: 120px;">
                        ${s}
                    </select>
                    <p class="text-sm text-gray-500 mt-1">Hold Ctrl (Cmd on Mac) to select multiple students</p>
                </div>
            </div>
        `}function q(o,e){const t=o.querySelector(".remove-cohort");t&&t.addEventListener("click",function(){confirm("Are you sure you want to remove this cohort?")&&(o.remove(),C())})}function C(){const o=d.querySelectorAll(".cohort-item");o.forEach((e,t)=>{const s=e.querySelector("h4"),n=t+1;s&&(s.textContent=`Cohort ${n}`),e.querySelectorAll("input, select").forEach(r=>{if(r.name&&r.name.includes("cohorts[")){const g=r.name.replace(/cohorts\[\d+\]/,`cohorts[${t}]`);r.name=g}})}),c=o.length}console.log("Exam edit form JavaScript initialized successfully")});
