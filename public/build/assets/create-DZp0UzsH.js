document.addEventListener("DOMContentLoaded",function(){let v=0;const p=document.getElementById("questionsContainer"),f=document.getElementById("noQuestions"),h=document.getElementById("addQuestionBtn");h&&h.addEventListener("click",function(){console.log("Add question button clicked"),Q()});function Q(){console.log("Adding question, current counter:",v),v++;const e=L(v);p.appendChild(e),f&&(f.style.display="none"),I(e),console.log("Question added successfully")}function L(e){const o=document.createElement("div");return o.className="question-item bg-gray-50 rounded-lg p-4 mb-4 border border-gray-200",o.innerHTML=`
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-lg font-medium text-gray-900">Question ${e}</h4>
                <button type="button" class="remove-question text-red-600 hover:text-red-800" onclick="removeQuestion(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Question Type</label>
                    <select name="questions[${e}][type]" class="question-type w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="toggleQuestionOptions(this, ${e})">
                        <option value="mcq">Multiple Choice (MCQ)</option>
                        <option value="short_answer">Short Answer</option>
                        <option value="file_upload">File Upload</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Marks</label>
                    <input type="number" name="questions[${e}][points]" min="1" value="5" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                </div>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Question Text</label>
                <textarea name="questions[${e}][question]" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter your question here..." required></textarea>
            </div>
            
            <!-- MCQ Options (shown by default) -->
            <div class="mcq-options" id="mcq-options-${e}">
                <label class="block text-sm font-medium text-gray-700 mb-2">Answer Options</label>
                <div class="space-y-2">
                    <div class="flex items-center">
                        <input type="radio" name="questions[${e}][correct_answer]" value="A" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500">
                        <label class="ml-2 text-sm text-gray-700">A)</label>
                        <input type="text" name="questions[${e}][options][A]" class="ml-2 flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Option A">
                    </div>
                    <div class="flex items-center">
                        <input type="radio" name="questions[${e}][correct_answer]" value="B" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500">
                        <label class="ml-2 text-sm text-gray-700">B)</label>
                        <input type="text" name="questions[${e}][options][B]" class="ml-2 flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Option B">
                    </div>
                    <div class="flex items-center">
                        <input type="radio" name="questions[${e}][correct_answer]" value="C" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500">
                        <label class="ml-2 text-sm text-gray-700">C)</label>
                        <input type="text" name="questions[${e}][options][C]" class="ml-2 flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Option C">
                    </div>
                    <div class="flex items-center">
                        <input type="radio" name="questions[${e}][correct_answer]" value="D" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500">
                        <label class="ml-2 text-sm text-gray-700">D)</label>
                        <input type="text" name="questions[${e}][options][D]" class="ml-2 flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Option D">
                    </div>
                </div>
            </div>
            
            <!-- Short Answer Options (hidden by default) -->
            <div class="short-answer-options hidden" id="short-answer-options-${e}">
                <div class="mb-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Expected Answer Length</label>
                    <select name="questions[${e}][answer_length]" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="short">Short (1-2 sentences)</option>
                        <option value="medium">Medium (1-2 paragraphs)</option>
                        <option value="long">Long (3+ paragraphs)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sample Answer (Optional)</label>
                    <textarea name="questions[${e}][sample_answer]" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Provide a sample answer for reference..."></textarea>
                </div>
            </div>
            
            <!-- File Upload Options (hidden by default) -->
            <div class="file-upload-options hidden" id="file-upload-options-${e}">
                <div class="mb-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Allowed File Types</label>
                    <div class="flex flex-wrap gap-3">
                        <label class="flex items-center">
                            <input type="checkbox" name="questions[${e}][allowed_files][]" value="pdf" checked class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">PDF</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="questions[${e}][allowed_files][]" value="docx" checked class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">DOCX</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="questions[${e}][allowed_files][]" value="jpg" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">JPG</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="questions[${e}][allowed_files][]" value="png" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">PNG</span>
                        </label>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Maximum File Size (MB)</label>
                    <input type="number" name="questions[${e}][max_file_size]" min="1" max="50" value="10" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
        `,o}function I(e){const o=e.querySelector(".question-type");if(o){o.addEventListener("change",function(){const t=e.querySelector("h4").textContent.match(/\d+/)[0];toggleQuestionOptions(this,t)});const n=e.querySelector("h4").textContent.match(/\d+/)[0];toggleQuestionOptions(o,n)}}window.removeQuestion=function(e){e.closest(".question-item").remove(),p.children.length===0&&f&&(f.style.display="block"),C()},window.toggleQuestionOptions=function(e,o){const n=e.closest(".question-item"),t=n.querySelector(`#mcq-options-${o}`),a=n.querySelector(`#short-answer-options-${o}`),i=n.querySelector(`#file-upload-options-${o}`);switch([t,a,i].forEach(s=>{s&&(s.classList.add("hidden"),s.querySelectorAll("input, textarea, select").forEach(l=>{l.removeAttribute("required")}))}),e.value){case"mcq":t&&(t.classList.remove("hidden"),t.querySelectorAll('input[name*="[options]"]').forEach(s=>{s.setAttribute("required","required")}),t.querySelectorAll('input[name*="[correct_answer]"]').forEach(s=>{s.setAttribute("required","required")}));break;case"short_answer":a&&a.classList.remove("hidden");break;case"file_upload":i&&i.classList.remove("hidden");break}};function C(){p.querySelectorAll(".question-item").forEach((o,n)=>{const t=o.querySelector("h4");t&&(t.textContent=`Question ${n+1}`)})}const b=document.getElementById("examForm");b&&b.addEventListener("submit",function(e){console.log("Form submit triggered"),console.log("Form data being submitted:");const o=new FormData(b);for(let[r,c]of o.entries())console.log(r,c);const n=document.getElementById("title").value.trim(),t=document.getElementById("course_id").value,a=document.getElementById("start_time").value,i=document.getElementById("end_time").value,s=p.querySelectorAll(".question-item");if(!n.trim())return showError("Please enter an exam title."),!1;if(!t)return showError("Please select a course."),!1;if(!a||!i)return showError("Please set start and end times."),!1;if(s.length===0)return showError("Please add at least one question."),!1;let l=null;if(s.forEach((r,c)=>{const _=r.querySelector('select[name*="[type]"]').value,T=r.querySelector('textarea[name*="[question]"]').value.trim(),A=r.querySelector('input[name*="[points]"]').value;if(!T){l=`Question ${c+1}: Please enter question text.`;return}if(!A||A<1){l=`Question ${c+1}: Please enter valid marks (minimum 1).`;return}if(_==="mcq"){const B=r.querySelectorAll('input[name*="[options]"]'),O=r.querySelector('input[name*="[correct_answer]"]:checked');let k=0;if(B.forEach(F=>{F.value.trim()||k++}),k>0){l=`Question ${c+1}: Please fill in all MCQ options.`;return}if(!O){l=`Question ${c+1}: Please select the correct answer for MCQ.`;return}}else if(_==="file_upload"&&r.querySelectorAll('input[name*="[allowed_files]"]:checked').length===0){l=`Question ${c+1}: Please select at least one allowed file type.`;return}}),l){e.preventDefault(),showError(l);return}document.querySelectorAll('button[type="submit"]').forEach(r=>{r.disabled=!0,r.innerHTML='<i class="fas fa-spinner fa-spin mr-2"></i>Creating...'}),console.log("Form validation passed, submitting...")});const q=document.getElementById("createAnotherBtn"),w=document.getElementById("goToDetailsBtn"),E=document.getElementById("submitBtn");q&&q.addEventListener("click",function(){document.querySelectorAll('input[name="create_another"], input[name="go_to_details"]').forEach(e=>{e.remove()})}),w&&w.addEventListener("click",function(){document.querySelectorAll('input[name="create_another"], input[name="go_to_details"]').forEach(e=>{e.remove()})}),E&&E.addEventListener("click",function(){document.querySelectorAll('input[name="create_another"], input[name="go_to_details"]').forEach(e=>{e.remove()})}),window.debugFormData=function(){const e=new FormData(b);console.log("Form data debug:");for(let[n,t]of e.entries())console.log(n,t);const o=p.querySelectorAll(".question-item");console.log("Questions found:",o.length),o.forEach((n,t)=>{const a=n.querySelector('select[name*="[type]"]').value;console.log(`Question ${t+1} type:`,a)})};const d=document.getElementById("start_time"),S=document.getElementById("end_time"),g=document.getElementById("duration_minutes"),x=document.getElementById("examCountdown"),u=document.getElementById("countdownTimer");let m=null;d&&S&&g&&(d.addEventListener("change",function(){$(),D()}),g.addEventListener("change",$));function $(){if(d.value&&g.value){const e=new Date(d.value),o=parseInt(g.value),n=new Date(e.getTime()+o*60*1e3);S.value=n.toISOString().slice(0,16)}}function D(){if(m&&clearInterval(m),!d.value){x.classList.add("hidden");return}const e=new Date(d.value);if(e<=new Date){x.classList.add("hidden");return}x.classList.remove("hidden"),m=setInterval(()=>{const t=e-new Date;if(t<=0){u.textContent="Exam time has started!",u.classList.remove("text-blue-600"),u.classList.add("text-green-600"),clearInterval(m);return}const a=Math.floor(t/(1e3*60*60*24)),i=Math.floor(t%(1e3*60*60*24)/(1e3*60*60)),s=Math.floor(t%(1e3*60*60)/(1e3*60)),l=Math.floor(t%(1e3*60)/1e3);let y="";a>0?y=`${a}d ${i.toString().padStart(2,"0")}:${s.toString().padStart(2,"0")}:${l.toString().padStart(2,"0")}`:y=`${i.toString().padStart(2,"0")}:${s.toString().padStart(2,"0")}:${l.toString().padStart(2,"0")}`,u.textContent=y,t<=300*1e3?u.className="font-medium text-red-600":t<=1800*1e3?u.className="font-medium text-yellow-600":u.className="font-medium text-blue-600"},1e3)}window.addEventListener("beforeunload",()=>{m&&clearInterval(m)}),console.log("Exam creation form JavaScript loaded")});
