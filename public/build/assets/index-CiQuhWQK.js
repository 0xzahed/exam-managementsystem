document.addEventListener("DOMContentLoaded",function(){m()});function m(){const e=document.getElementById("searchInput"),s=document.getElementById("courseFilter");e&&e.addEventListener("input",a),s&&s.addEventListener("change",a)}function a(){const e=document.getElementById("searchInput").value.toLowerCase(),s=document.getElementById("courseFilter").value;document.querySelectorAll(".student-card").forEach(n=>{const o=n.dataset.studentName,i=n.dataset.courseIds.split(","),d=o.includes(e),l=!s||i.includes(s);d&&l?n.style.display="block":n.style.display="none"})}function c(e){document.getElementById("studentDetailContent").innerHTML=`
        <div class="text-center py-8">
            <i class="fas fa-spinner fa-spin text-3xl text-gray-400 mb-4"></i>
            <p class="text-gray-600">Loading student details...</p>
        </div>
    `,document.getElementById("studentDetailModal").classList.remove("hidden"),document.getElementById("studentDetailModal").classList.add("flex"),fetch(`/students/${e}`).then(s=>s.json()).then(s=>{if(s.success)p(s.student);else throw new Error(s.message||"Failed to load student details")}).catch(s=>{console.error("Error:",s),document.getElementById("studentDetailContent").innerHTML=`
                <div class="text-center py-8">
                    <i class="fas fa-exclamation-triangle text-3xl text-red-400 mb-4"></i>
                    <p class="text-red-600">Failed to load student details</p>
                </div>
            `})}function p(e){const s=`
        <div class="space-y-6">
            <!-- Student Header -->
            <div class="flex items-center space-x-6 p-6 bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl">
                <div class="w-20 h-20 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-2xl">
                    ${e.name.substring(0,2).toUpperCase()}
                </div>
                <div class="flex-1">
                    <h4 class="text-2xl font-bold text-gray-800">${e.name}</h4>
                    <p class="text-gray-600 text-lg">${e.email}</p>
                    <p class="text-sm text-gray-500 mt-1">Joined: ${new Date(e.created_at).toLocaleDateString()}</p>
                </div>
                <div class="flex gap-2">
                    <button onclick="messageStudent(${e.id})" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-envelope mr-2"></i>Message
                    </button>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100">Enrolled Courses</p>
                            <p class="text-2xl font-bold">${e.enrolled_courses.length}</p>
                        </div>
                        <i class="fas fa-book text-2xl opacity-80"></i>
                    </div>
                </div>
                <div class="p-4 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100">Active Status</p>
                            <p class="text-lg font-bold">${e.enrolled_courses.filter(t=>t.pivot.status==="active"||!t.pivot.status).length} Active</p>
                        </div>
                        <i class="fas fa-check-circle text-2xl opacity-80"></i>
                    </div>
                </div>
                <div class="p-4 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100">Member Since</p>
                            <p class="text-sm font-bold">${g(e.created_at)}</p>
                        </div>
                        <i class="fas fa-calendar text-2xl opacity-80"></i>
                    </div>
                </div>
            </div>
            
            <!-- Enrolled Courses -->
            <div class="bg-white border border-gray-200 rounded-xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <h5 class="text-xl font-bold text-gray-800">Enrolled Courses</h5>
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">
                        ${e.enrolled_courses.length} ${e.enrolled_courses.length===1?"Course":"Courses"}
                    </span>
                </div>
                <div class="space-y-4">
                    ${e.enrolled_courses.length>0?e.enrolled_courses.map(t=>`
                            <div class="flex justify-between items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h6 class="text-lg font-bold text-gray-800">${t.title}</h6>
                                        <span class="px-3 py-1 text-xs font-medium ${t.pivot.status==="active"||!t.pivot.status?"bg-green-100 text-green-800":"bg-gray-100 text-gray-600"} rounded-full">
                                            ${t.pivot.status||"Active"}
                                        </span>
                                    </div>
                                    <p class="text-gray-600 mb-1">${t.code}</p>
                                    ${t.description?`<p class="text-sm text-gray-500 mb-2">${t.description.length>100?t.description.substring(0,100)+"...":t.description}</p>`:""}
                                    <div class="flex items-center gap-4 text-sm text-gray-500">
                                        <span><i class="fas fa-calendar mr-1"></i>Enrolled: ${new Date(t.pivot.enrolled_at||t.pivot.created_at).toLocaleDateString()}</span>
                                        ${t.credits?`<span><i class="fas fa-award mr-1"></i>${t.credits} Credits</span>`:""}
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 ml-4">
                                    <button onclick="removeCourseConfirm(${e.id}, ${t.id}, '${t.title}')" class="px-3 py-2 bg-red-100 text-red-700 text-sm rounded-lg hover:bg-red-200 transition-colors">
                                        <i class="fas fa-times mr-1"></i>Remove
                                    </button>
                                </div>
                            </div>
                        `).join(""):'<div class="text-center py-8"><i class="fas fa-book-open text-4xl text-gray-300 mb-4"></i><p class="text-gray-500">No courses enrolled yet</p></div>'}
                </div>
            </div>
        </div>
    `;document.getElementById("studentDetailContent").innerHTML=s}function g(e){const s=new Date(e),n=Math.floor((new Date-s)/1e3),o=Math.floor(n/60),i=Math.floor(o/60),d=Math.floor(i/24),l=Math.floor(d/30),r=Math.floor(l/12);return r>0?`${r} year${r>1?"s":""} ago`:l>0?`${l} month${l>1?"s":""} ago`:d>0?`${d} day${d>1?"s":""} ago`:i>0?`${i} hour${i>1?"s":""} ago`:o>0?`${o} minute${o>1?"s":""} ago`:"Just now"}function v(e,s,t){confirm(`Are you sure you want to remove this student from "${t}"?`)&&u(e,s)}function u(e,s){fetch(`/students/${e}/courses/${s}`,{method:"DELETE",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content")}}).then(t=>t.json()).then(t=>{if(t.success){showSuccess("Student removed from course successfully!");const n=document.querySelector('[onclick*="viewStudent"]')?.onclick.toString().match(/\d+/)?.[0];n&&setTimeout(()=>c(n),1e3),setTimeout(()=>window.location.reload(),1500)}else showError(t.message||"Failed to remove student")}).catch(t=>{console.error("Error:",t),showError("An error occurred while removing student")})}function f(){const e=document.getElementById("studentDetailModal");e&&(e.classList.add("hidden"),e.classList.remove("flex"))}function x(e){showInfo("Message functionality will be implemented soon!")}function h(){showInfo("Export functionality will be implemented soon!")}document.addEventListener("click",function(e){const s=document.getElementById("studentDetailModal");s&&e.target===s&&f()});window.filterStudents=a;window.viewStudent=c;window.hideStudentModal=f;window.messageStudent=x;window.exportStudents=h;window.removeCourseConfirm=v;window.removeCourse=u;
