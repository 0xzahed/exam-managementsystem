// Course Creation Form JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Animation for form card
    const courseCard = document.getElementById('createCourseCard');
    if (courseCard) {
        courseCard.classList.add('animate-fade-in');
    }

    // Form validation
    const form = document.querySelector('form');
    const requiredFields = form.querySelectorAll('[required]');

    // Real-time validation feedback
    requiredFields.forEach(field => {
        field.addEventListener('blur', function() {
            validateField(this);
        });
    });

    // Course code formatting
    const courseCodeInput = document.querySelector('input[name="code"]');
    if (courseCodeInput) {
        courseCodeInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    }

    // Form submission
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!validateField(field)) {
                isValid = false;
            }
        });

        if (!isValid) {
            e.preventDefault();
            showError('Please fill in all required fields correctly.');
        }
    });

    function validateField(field) {
        const value = field.value.trim();
        let isValid = true;
        
        // Remove previous error styling
        field.classList.remove('border-red-500', 'bg-red-50');
        
        if (field.hasAttribute('required') && !value) {
            isValid = false;
        }

        // Specific validation rules
        if (field.name === 'code' && value) {
            if (!/^[A-Z]{3}\d{3}$/.test(value)) {
                isValid = false;
            }
        }

        if (field.name === 'max_students' && value) {
            const num = parseInt(value);
            if (num < 1 || num > 200) {
                isValid = false;
            }
        }

        // Apply error styling
        if (!isValid) {
            field.classList.add('border-red-500', 'bg-red-50');
        }

        return isValid;
    }
});

// CSS animations
const style = document.createElement('style');
style.textContent = `
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
`;
document.head.appendChild(style);
