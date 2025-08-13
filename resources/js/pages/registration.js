document.addEventListener('DOMContentLoaded', function() {
    // Role card selection functionality
    const roleCards = document.querySelectorAll('.role-card');
    const selectedRoleInput = document.getElementById('selectedRole');
    const idField = document.getElementById('idField');
    const idLabel = document.getElementById('idLabel');
    const userId = document.getElementById('userId');
    const studentIdHidden = document.getElementById('student_id');
    const employeeIdHidden = document.getElementById('employee_id');
    const roleError = document.getElementById('roleError');
    
    // Initialize role selection if there's an old value
    const oldRole = selectedRoleInput?.value;
    if (oldRole) {
        const selectedCard = document.querySelector(`[data-role="${oldRole}"]`);
        if (selectedCard) {
            selectedCard.classList.add('selected');
            updateIdField(oldRole);
        }
    }
    
    roleCards.forEach(card => {
        card.addEventListener('click', function() {
            // Remove selected class from all cards
            roleCards.forEach(c => c.classList.remove('selected'));
            
            // Add selected class to clicked card
            this.classList.add('selected');
            
            // Get role value
            const role = this.getAttribute('data-role');
            if (selectedRoleInput) {
                selectedRoleInput.value = role;
            }
            
            // Hide role error
            if (roleError) {
                roleError.classList.add('hidden');
            }
            
            // Update ID field
            updateIdField(role);
        });
    });
    
    function updateIdField(role) {
        if (!idField || !idLabel || !userId) return;
        
        if (role === 'student') {
            idLabel.textContent = 'Student ID';
            userId.placeholder = '221-15-4716';
            userId.setAttribute('required', '');
            idField.classList.remove('hidden');
        } else if (role === 'instructor') {
            idLabel.textContent = 'Employee ID';
            userId.placeholder = 'EMP-12345';
            userId.setAttribute('required', '');
            idField.classList.remove('hidden');
        } else {
            idField.classList.add('hidden');
            userId.removeAttribute('required');
        }
    }
    
    // Update hidden fields when user_id changes
    if (userId) {
        userId.addEventListener('input', function() {
            const role = selectedRoleInput?.value;
            if (role === 'student' && studentIdHidden) {
                studentIdHidden.value = this.value;
                if (employeeIdHidden) employeeIdHidden.value = '';
            } else if (role === 'instructor' && employeeIdHidden) {
                employeeIdHidden.value = this.value;
                if (studentIdHidden) studentIdHidden.value = '';
            }
        });
    }
    
    // Password toggle functionality
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            const icon = this.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            }
        });
    }
    
    // Form validation
    const form = document.getElementById('registrationForm');
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const submitLoader = document.getElementById('submitLoader');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            // Check if role is selected
            if (!selectedRoleInput?.value) {
                e.preventDefault();
                if (roleError) {
                    roleError.classList.remove('hidden');
                    roleError.scrollIntoView({ behavior: 'smooth' });
                }
                return false;
            }
            
            // Show loading state
            if (submitText) submitText.textContent = 'Creating Account...';
            if (submitLoader) submitLoader.classList.remove('hidden');
            if (submitBtn) submitBtn.disabled = true;
        });
    }
    
    // Password confirmation validation
    const passwordConfirm = document.getElementById('password_confirmation');
    if (passwordInput && passwordConfirm) {
        function validatePasswords() {
            if (passwordConfirm.value && passwordInput.value !== passwordConfirm.value) {
                passwordConfirm.setCustomValidity('Passwords do not match');
            } else {
                passwordConfirm.setCustomValidity('');
            }
        }
        
        passwordInput.addEventListener('input', validatePasswords);
        passwordConfirm.addEventListener('input', validatePasswords);
    }
});
