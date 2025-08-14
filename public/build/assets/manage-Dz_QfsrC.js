console.log("Courses manage page loaded");let a=null,c=[];window.viewStudents=y;window.closeStudentsModal=r;window.removeStudent=g;window.exportStudentList=w;window.openEditModal=p;window.closeEditModal=E;window.openCreateModal=x;window.closeCreateModal=h;function y(e){a=e;const t=document.getElementById("studentsModal");document.body.style.overflow="hidden",t.classList.remove("hidden"),t.classList.add("flex"),t.style.opacity="0",t.style.transform="scale(0.95)",setTimeout(()=>{t.style.opacity="1",t.style.transform="scale(1)",t.style.transition="all 0.3s ease-out"},10),f(e)}function r(){const e=document.getElementById("studentsModal");document.body.style.overflow="",e.style.opacity="0",e.style.transform="scale(0.95)",setTimeout(()=>{e.classList.add("hidden"),e.classList.remove("flex"),e.style.opacity="",e.style.transform="",e.style.transition=""},200),a=null,c=[]}async function f(e){try{const o=await(await fetch(`/courses/${e}/students`)).json();if(o.error)throw new Error(o.error);c=o.students,i(o)}catch(t){console.error("Error loading students:",t),v(t.message)}}function i(e){const t=Array.isArray(e.students)?e.students:[];c=t,document.getElementById("courseInfo").textContent=`${e.course_code} - ${e.course_title}`,document.getElementById("studentCount").textContent=`${e.count} students`;const o=document.getElementById("studentsTableBody");if(t.length===0){o.innerHTML=`
            <div class="flex items-center justify-center py-12">
                <div class="text-center">
                    <i class="mb-4 text-4xl text-gray-400 fas fa-users"></i>
                    <p class="text-gray-500">No students enrolled yet</p>
                </div>
            </div>
        `;return}o.innerHTML=t.map(s=>`
        <div class="px-6 py-4 hover:bg-gray-50" data-student-id="${s.id}">
            <div class="grid grid-cols-12 gap-4 text-sm">
                <div class="col-span-1 font-medium text-gray-900">${s.serial}</div>
                <div class="col-span-3">
                    <div class="font-medium text-gray-900">${s.name}</div>
                </div>
                <div class="col-span-2 text-gray-600">${s.student_id}</div>
                <div class="col-span-3 text-gray-600">${s.email}</div>
                <div class="col-span-2 text-gray-600">${s.enrolled_at}</div>
                <div class="col-span-1">
                    <button onclick="removeStudent(${s.id}, '${s.name}')" 
                            class="px-3 py-1 text-xs text-white bg-red-600 rounded hover:bg-red-700"
                            title="Remove student">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    `).join("")}function v(e){const t=document.getElementById("studentsTableBody");t.innerHTML=`
        <div class="flex items-center justify-center py-12">
            <div class="text-center">
                <i class="mb-4 text-4xl text-red-400 fas fa-exclamation-triangle"></i>
                <p class="text-red-500">Error: ${e}</p>
                <button onclick="loadStudents(currentCourseId)" class="px-4 py-2 mt-3 text-sm text-white bg-indigo-600 rounded hover:bg-indigo-700">
                    Try Again
                </button>
            </div>
        </div>
    `}async function g(e,t){if(confirm(`Are you sure you want to remove ${t} from this course?`))try{const s=await(await fetch(`/courses/${a}/students/${e}`,{method:"DELETE",headers:{"X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content"),Accept:"application/json"}})).json();if(s.success){const d=document.querySelector(`[data-student-id="${e}"]`);d&&d.remove(),c=c.filter(n=>n.id!==e),document.getElementById("studentCount").textContent=`${c.length} students`,showSuccess(s.message),c.length===0&&i({students:[],count:0,course_code:"",course_title:""})}else throw new Error(s.message||"Failed to remove student")}catch(o){console.error("Error removing student:",o),showError(o.message)}}function w(){if(c.length===0){showError("No students to export");return}const t=[["#","Name","Student ID","Email","Enrolled Date"].join(","),...c.map(n=>[n.serial,`"${n.name}"`,n.student_id,n.email,n.enrolled_at].join(","))].join(`
`),o=new Blob([t],{type:"text/csv"}),s=window.URL.createObjectURL(o),d=document.createElement("a");d.href=s,d.download=`students-course-${a}.csv`,d.click(),window.URL.revokeObjectURL(s),showSuccess("Student list exported successfully")}function p(e){const t=e.dataset.courseId,o=e.dataset.courseTitle,s=e.dataset.courseCode,d=e.dataset.coursePrerequisites,n=e.dataset.courseCapacity,u=e.dataset.coursePassword;document.getElementById("edit_course_id").value=t,document.getElementById("edit_title").value=o,document.getElementById("edit_code").value=s,document.getElementById("edit_prerequisites").value=d||"",document.getElementById("edit_capacity").value=n,document.getElementById("edit_password").value=u;const m=document.getElementById("editCourseForm");m.action=`/courses/${t}`;const l=document.getElementById("editCourseModal");l.classList.remove("hidden"),l.classList.add("flex")}function E(){const e=document.getElementById("editCourseModal");e.classList.add("hidden"),e.classList.remove("flex")}function x(){const e=document.getElementById("createCourseModal");e.classList.remove("hidden"),e.classList.add("flex")}function h(){const e=document.getElementById("createCourseModal");e.classList.add("hidden"),e.classList.remove("flex")}document.addEventListener("DOMContentLoaded",function(){const e=document.getElementById("searchStudents");e&&e.addEventListener("input",function(o){const s=o.target.value.toLowerCase(),d=c.filter(n=>n.name.toLowerCase().includes(s)||n.email.toLowerCase().includes(s)||n.student_id.toLowerCase().includes(s));i({students:d,count:d.length,course_code:"",course_title:""})});const t=document.getElementById("studentsModal");t&&t.addEventListener("click",function(o){o.target===t&&r()}),document.addEventListener("keydown",function(o){if(o.key==="Escape"){const s=document.getElementById("studentsModal");s&&!s.classList.contains("hidden")&&r()}});try{window.__COURSES__&&console.log("Courses data loaded:",window.__COURSES__.length,"courses")}catch{console.log("No courses data available")}});
