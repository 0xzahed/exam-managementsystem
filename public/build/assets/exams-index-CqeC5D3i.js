document.addEventListener("DOMContentLoaded",function(){window.filterExams=function(i,s){document.querySelectorAll(".filter-btn").forEach(e=>{e.classList.remove("active","bg-blue-600","text-white"),e.classList.add("bg-gray-100","text-gray-700")}),s.target.classList.remove("bg-gray-100","text-gray-700"),s.target.classList.add("active","bg-blue-600","text-white"),document.querySelectorAll(".exam-card").forEach(e=>{let a=!0;switch(i){case"draft":a=e.dataset.status==="draft";break;case"available":a=e.dataset.availability==="available";break;case"completed":a=e.dataset.completion==="completed";break;case"pending":a=e.dataset.completion==="pending";break;case"all":default:a=!0;break}a?(e.style.display="block",e.classList.add("fade-in")):(e.style.display="none",e.classList.remove("fade-in"))})},document.querySelectorAll(".exam-card").forEach((i,s)=>{i.style.animationDelay=`${s*.1}s`,i.classList.add("fade-in")});class o{constructor(){this.updateInterval=3e4,this.timers=new Map,this.init()}init(){this.updateStatuses(),setInterval(()=>{this.updateStatuses()},this.updateInterval),this.initCountdownTimers()}async updateStatuses(){try{const s=await fetch("/student/exams/status",{method:"GET",headers:{"Content-Type":"application/json","X-Requested-With":"XMLHttpRequest","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]')?.getAttribute("content")}});if(s.ok){const t=await s.json();this.updateExamCards(t.exams),this.updateStatistics(t.statistics)}}catch(s){console.error("Error updating exam statuses:",s)}}updateExamCards(s){s.forEach(t=>{const e=document.querySelector(`[data-exam-id="${t.id}"]`);e&&(e.dataset.status=t.status,e.dataset.completion=t.is_completed?"completed":"pending",e.dataset.availability=t.can_take?"available":"unavailable",this.updateStatusBadges(e,t),this.updateTimeStatus(e,t),this.updateActionButton(e,t),this.updateCountdown(e,t))})}updateStatusBadges(s,t){const e=s.querySelector(".status-badges");if(!e)return;let a="";t.course&&(a+=`<span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full">${t.course}</span>`),a+=`<span class="text-sm font-medium px-3 py-1 rounded-full ${{published:"bg-green-100 text-green-800",draft:"bg-yellow-100 text-yellow-800",closed:"bg-gray-100 text-gray-800"}[t.status]||"bg-gray-100 text-gray-800"}">${t.status.charAt(0).toUpperCase()+t.status.slice(1)}</span>`,t.is_completed?a+='<span class="bg-green-100 text-green-800 text-sm font-medium px-3 py-1 rounded-full"><i class="fas fa-check mr-1"></i>Completed</span>':t.can_take?a+='<span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full"><i class="fas fa-play mr-1"></i>Available</span>':a+='<span class="bg-gray-100 text-gray-800 text-sm font-medium px-3 py-1 rounded-full"><i class="fas fa-lock mr-1"></i>Not Available</span>',e.innerHTML=a}updateTimeStatus(s,t){const e=s.querySelector(".time-status");if(!e)return;let a="";t.is_active?a=`
                    <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-check-circle text-green-500"></i>
                            <span class="text-green-800 font-medium">Exam is currently active and available</span>
                        </div>
                    </div>`:t.time_until_start>0?a=`
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-clock text-yellow-500"></i>
                            <span class="text-yellow-800 font-medium">Starts in ${this.formatTimeRemaining(t.time_until_start)}</span>
                        </div>
                        <div class="countdown-timer mt-2 text-lg font-bold text-yellow-900" data-target="${t.start_timestamp}"></div>
                    </div>`:t.time_until_end<0&&(a=`
                    <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-times-circle text-red-500"></i>
                            <span class="text-red-800 font-medium">Exam has ended</span>
                        </div>
                    </div>`),e.innerHTML=a}updateActionButton(s,t){const e=s.querySelector(".action-buttons");if(!e)return;let a="";t.can_take?a=`
                    <form action="/student/exams/${t.id}/start" method="POST" class="inline w-full">
                        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.getAttribute("content")}">
                        <button type="submit" class="w-full bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white text-center py-3 px-4 rounded-lg font-medium flex items-center justify-center gap-2">
                            <i class="fas fa-play"></i>
                            Start Exam
                        </button>
                    </form>`:t.is_completed?a=`
                    <a href="/student/exams/${t.id}/result" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-center py-3 px-4 rounded-lg font-medium flex items-center justify-center gap-2">
                        <i class="fas fa-chart-bar"></i>
                        View Results
                    </a>`:t.time_until_start>0?a=`
                    <button disabled class="w-full bg-yellow-300 text-yellow-700 text-center py-3 px-4 rounded-lg font-medium cursor-not-allowed flex items-center justify-center gap-2">
                        <i class="fas fa-clock"></i>
                        Not Started Yet
                    </button>`:t.time_until_end<0?a=`
                    <button disabled class="w-full bg-red-300 text-red-700 text-center py-3 px-4 rounded-lg font-medium cursor-not-allowed flex items-center justify-center gap-2">
                        <i class="fas fa-times-circle"></i>
                        Exam Ended
                    </button>`:a=`
                    <button disabled class="w-full bg-gray-300 text-gray-500 text-center py-3 px-4 rounded-lg font-medium cursor-not-allowed flex items-center justify-center gap-2">
                        <i class="fas fa-lock"></i>
                        Not Available
                    </button>`,e.innerHTML=a+`
                <a href="#" onclick="showExamDetails(${t.id})" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 text-center py-3 px-4 rounded-lg font-medium flex items-center justify-center gap-2">
                    <i class="fas fa-info-circle"></i>
                    Exam Details
                </a>`}updateCountdown(s,t){const e=s.querySelector(".countdown-timer");if(!e)return;const a=parseInt(e.dataset.target);if(isNaN(a))return;const n=this.timers.get(e);n&&clearInterval(n);const r=setInterval(()=>{const u=Math.floor(Date.now()/1e3),l=a-u;l<=0?(clearInterval(r),this.timers.delete(e),e.textContent="Starting now...",setTimeout(()=>this.updateStatuses(),1e3)):e.textContent=this.formatTimeRemaining(l)},1e3);this.timers.set(e,r)}initCountdownTimers(){document.querySelectorAll(".countdown-timer").forEach(s=>{const t=parseInt(s.dataset.target);isNaN(t)||this.updateSingleCountdown(s,t)})}updateSingleCountdown(s,t){const e=setInterval(()=>{const a=Math.floor(Date.now()/1e3),n=t-a;n<=0?(clearInterval(e),s.textContent="Starting now...",setTimeout(()=>this.updateStatuses(),1e3)):s.textContent=this.formatTimeRemaining(n)},1e3);this.timers.set(s,e)}formatTimeRemaining(s){if(s<60)return`${s} second${s!==1?"s":""}`;if(s<3600){const t=Math.floor(s/60);return`${t} minute${t!==1?"s":""}`}else if(s<86400){const t=Math.floor(s/3600),e=Math.floor(s%3600/60);return`${t}h ${e}m`}else{const t=Math.floor(s/86400),e=Math.floor(s%86400/3600);return`${t}d ${e}h`}}updateStatistics(s){const t=document.querySelector(".stat-available"),e=document.querySelector(".stat-completed"),a=document.querySelector(".stat-pending"),n=document.querySelector(".stat-average");t&&(t.textContent=s.available),e&&(e.textContent=s.completed),a&&(a.textContent=s.pending),n&&(n.textContent=s.average_score?`${s.average_score}%`:"N/A")}}new o});
