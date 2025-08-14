document.addEventListener("DOMContentLoaded",function(){const r=document.getElementById("createCourseCard");r&&r.classList.add("animate-fade-in");const a=document.querySelector("form"),s=a.querySelectorAll("[required]");s.forEach(e=>{e.addEventListener("blur",function(){o(this)})});const i=document.querySelector('input[name="code"]');i&&i.addEventListener("input",function(){this.value=this.value.toUpperCase()}),a.addEventListener("submit",function(e){let t=!0;s.forEach(n=>{o(n)||(t=!1)}),t||(e.preventDefault(),showError("Please fill in all required fields correctly."))});function o(e){const t=e.value.trim();let n=!0;if(e.classList.remove("border-red-500","bg-red-50"),e.hasAttribute("required")&&!t&&(n=!1),e.name==="code"&&t&&(/^[A-Z]{3}\d{3}$/.test(t)||(n=!1)),e.name==="max_students"&&t){const d=parseInt(t);(d<1||d>200)&&(n=!1)}return n||e.classList.add("border-red-500","bg-red-50"),n}});const u=document.createElement("style");u.textContent=`
    .animate-fade-in {
        animation: fadeIn 0.5s ease-in-out;
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;document.head.appendChild(u);
