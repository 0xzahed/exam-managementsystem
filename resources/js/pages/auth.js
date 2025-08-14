document.addEventListener('DOMContentLoaded', () => {
  // Login form validation
  const loginForm = document.getElementById('loginForm');
  if (loginForm) {
    const emailInput = document.getElementById('emailInput');
    const passwordInput = document.getElementById('passwordInput');
    const roleInput = document.getElementById('roleInput');

    // role tab switching
    document.querySelectorAll('.role-tab').forEach((tab) => {
      tab.addEventListener('click', () => {
        document.querySelectorAll('.role-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        roleInput.value = tab.dataset.role;
      });
    });

    loginForm.addEventListener('submit', (e) => {
      let hasError = false;

      // domain-enforced email
      const email = emailInput.value.trim();
      const validDomain = /@diu\.edu\.bd$/i.test(email);
      if (!validDomain) {
        hasError = true;
        document.getElementById('emailError')?.classList.remove('hidden');
        emailInput.classList.add('ring-2', 'ring-red-400');
      } else {
        document.getElementById('emailError')?.classList.add('hidden');
        emailInput.classList.remove('ring-2', 'ring-red-400');
      }

      if (!passwordInput.value) {
        hasError = true;
        document.getElementById('passwordError')?.classList.remove('hidden');
      } else {
        document.getElementById('passwordError')?.classList.add('hidden');
      }

      if (hasError) e.preventDefault();
    });
  }

  // Registration form validation
  const regForm = document.getElementById('registrationForm');
  if (regForm) {
    const email = document.getElementById('email');
    const pwd = document.getElementById('password');
    const pwd2 = document.getElementById('password_confirmation');
    const roleHidden = document.getElementById('selectedRole');
    const roleCards = document.querySelectorAll('.role-card');
    const idField = document.getElementById('idField');
    const idLabel = document.getElementById('idLabel');

    roleCards.forEach(card => {
      card.addEventListener('click', () => {
        roleCards.forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        roleHidden.value = card.dataset.role;
        // toggle ID field label depending on role
        idField.classList.remove('hidden');
        idLabel.textContent = card.dataset.role === 'student' ? 'Student ID' : 'Employee ID';
      });
    });

    regForm.addEventListener('submit', (e) => {
      let hasError = false;

      // require role selection
      if (!roleHidden.value) {
        hasError = true;
        document.getElementById('roleError')?.classList.remove('hidden');
      } else {
        document.getElementById('roleError')?.classList.add('hidden');
      }

      // email check
      if (!/@diu\.edu\.bd$/i.test(email.value.trim())) {
        hasError = true;
        email.classList.add('ring-2', 'ring-red-400');
      } else {
        email.classList.remove('ring-2', 'ring-red-400');
      }

      // password strength and confirm
      if (pwd.value.length < 8) {
        hasError = true;
        alert('Password must be at least 8 characters.');
      }
      if (pwd.value !== pwd2.value) {
        hasError = true;
        alert('Password confirmation does not match.');
      }

      // ID must be present once a role picked
      const userId = document.getElementById('userId');
      if (roleHidden.value && !userId.value.trim()) {
        hasError = true;
        userId.classList.add('ring-2', 'ring-red-400');
      } else {
        userId?.classList.remove('ring-2', 'ring-red-400');
      }

      if (hasError) e.preventDefault();
    });
  }
});

// Authentication page functionality - Login & Registration
document.addEventListener('DOMContentLoaded', function() {
    
    // Check if we're on login or registration page
    const isLoginPage = document.getElementById('loginForm') !== null;
    const isRegistrationPage = document.getElementById('registrationForm') !== null;
    
    // ===========================================
    // LOGIN PAGE FUNCTIONALITY
    // ===========================================
    if (isLoginPage) {
        // Role selection functionality
        const roleButtons = document.querySelectorAll('.role-tab');
        const loginForm = document.getElementById('loginForm');
        const emailInput = document.getElementById('emailInput');
        const passwordInput = document.getElementById('passwordInput');
        const loginButton = document.getElementById('loginButton');
        const roleInput = document.getElementById('roleInput');
        
        let selectedRole = 'student'; // Default role
        
        // Role selection handler
        roleButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                roleButtons.forEach(btn => {
                    btn.classList.remove('active');
                });
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Update selected role
                selectedRole = this.getAttribute('data-role');
                roleInput.value = selectedRole;
            });
        });
        
        // Form validation
        function validateLoginForm() {
            const email = emailInput.value.trim();
            const password = passwordInput.value.trim();
            
            // Email validation
            if (!email) {
                showError('Please enter your email');
                return false;
            }
            
            if (!email.includes('@diu.edu.bd')) {
                showError('Please use your @diu.edu.bd email address');
                return false;
            }
            
            // Password validation
            if (!password) {
                showError('Please enter your password');
                return false;
            }
            
            if (password.length < 6) {
                showError('Password must be at least 6 characters long');
                return false;
            }
            
            return true;
        }
        
        // Form submit handler
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!validateLoginForm()) {
                return;
            }
            
            // Show loading state
            loginButton.disabled = true;
            loginButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Signing In...';
            
            // Add selected role to form data
            const roleInput = document.createElement('input');
            roleInput.type = 'hidden';
            roleInput.name = 'role';
            roleInput.value = selectedRole;
            
            // Remove existing role input if any
            const existingRoleInput = this.querySelector('input[name="role"]');
            if (existingRoleInput && existingRoleInput !== document.getElementById('roleInput')) {
                existingRoleInput.remove();
            }
            
            // Make sure roleInput has the right value
            const mainRoleInput = document.getElementById('roleInput');
            if (mainRoleInput) {
                mainRoleInput.value = selectedRole;
            } else {
                this.appendChild(roleInput);
            }
            
            // Submit the form
            this.submit();
        });
        
        // Input focus effects
        [emailInput, passwordInput].forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('ring-2', 'ring-white/30');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('ring-2', 'ring-white/30');
            });
        });
        
        // OAuth button handlers
        const googleLogin = document.getElementById('googleLogin');
        const githubLogin = document.getElementById('githubLogin');
        
        if (googleLogin) {
            googleLogin.addEventListener('click', function(e) {
                e.preventDefault();
                window.location.href = `/auth/google?role=${selectedRole}`;
            });
        }
        
        if (githubLogin) {
            githubLogin.addEventListener('click', function(e) {
                e.preventDefault();
                window.location.href = `/auth/github?role=${selectedRole}`;
            });
        }
        
        // Forgot password handler
        const forgotPasswordLink = document.getElementById('forgotPasswordLink');
        if (forgotPasswordLink) {
            forgotPasswordLink.addEventListener('click', function(e) {
                e.preventDefault();
                alert('Forgot password functionality will be implemented soon!');
            });
        }
        
        // Register link handler
        const registerLink = document.getElementById('registerLink');
        if (registerLink) {
            registerLink.addEventListener('click', function(e) {
                e.preventDefault();
                window.location.href = '/register';
            });
        }
    }
    
    // ===========================================
    // REGISTRATION PAGE FUNCTIONALITY
    // ===========================================
    if (isRegistrationPage) {
        // Form elements
        const registrationForm = document.getElementById('registrationForm');
        const roleCards = document.querySelectorAll('.role-card');
        const selectedRoleInput = document.getElementById('selectedRole');
        const idField = document.getElementById('idField');
        const idLabel = document.getElementById('idLabel');
        const userIdInput = document.getElementById('userId');
        const studentIdInput = document.getElementById('student_id');
        const employeeIdInput = document.getElementById('employee_id');
        const passwordInput = document.getElementById('password');
        const passwordConfirmInput = document.getElementById('password_confirmation');
        const togglePasswordBtn = document.getElementById('togglePassword');
        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        const submitLoader = document.getElementById('submitLoader');
        const roleError = document.getElementById('roleError');
        
        // Session message handling
        const sessionError = document.body.getAttribute('data-session-error');
        const sessionSuccess = document.body.getAttribute('data-session-success');
        
        if (sessionError) {
            showMessage(sessionError, 'error');
        }
        
        if (sessionSuccess) {
            showMessage(sessionSuccess, 'success');
        }
        
        // Role selection functionality
        roleCards.forEach(card => {
            card.addEventListener('click', function() {
                // Remove selected class from all cards
                roleCards.forEach(c => c.classList.remove('selected'));
                
                // Add selected class to clicked card
                this.classList.add('selected');
                
                const role = this.getAttribute('data-role');
                selectedRoleInput.value = role;
                
                // Show/hide ID field based on role
                if (role) {
                    idField.classList.remove('hidden');
                    
                    if (role === 'student') {
                        idLabel.textContent = 'Student ID';
                        userIdInput.placeholder = '221-15-4716';
                        userIdInput.setAttribute('required', 'required');
                    } else if (role === 'instructor') {
                        idLabel.textContent = 'Employee ID';
                        userIdInput.placeholder = 'EMP001';
                        userIdInput.setAttribute('required', 'required');
                    }
                    
                    // Hide role error
                    roleError.classList.add('hidden');
                }
            });
        });
        
        // Password toggle functionality
        if (togglePasswordBtn) {
            togglePasswordBtn.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                const icon = this.querySelector('i');
                if (type === 'text') {
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        }
        
        // Form validation
        function validateRegistrationForm() {
            let isValid = true;
            const errors = [];
            
            // Role validation
            if (!selectedRoleInput.value) {
                roleError.classList.remove('hidden');
                isValid = false;
                errors.push('Please select a role');
            }
            
            // Email validation
            const email = document.getElementById('email').value;
            if (!email.endsWith('@diu.edu.bd')) {
                isValid = false;
                errors.push('Please use your @diu.edu.bd email address');
            }
            
            // Password validation
            const password = passwordInput.value;
            const passwordConfirm = passwordConfirmInput.value;
            
            if (password.length < 8) {
                isValid = false;
                errors.push('Password must be at least 8 characters long');
            }
            
            if (password !== passwordConfirm) {
                isValid = false;
                errors.push('Passwords do not match');
            }
            
            // Password strength validation
            const hasUpperCase = /[A-Z]/.test(password);
            const hasLowerCase = /[a-z]/.test(password);
            const hasNumbers = /\d/.test(password);
            const hasNonalphas = /\W/.test(password);
            
            if (!(hasUpperCase && hasLowerCase && hasNumbers && hasNonalphas)) {
                isValid = false;
                errors.push('Password must contain uppercase, lowercase, numbers, and special characters');
            }
            
            if (!isValid) {
                showMessage(errors.join('<br>'), 'error');
            }
            
            return isValid;
        }
        
        // User ID input handler
        if (userIdInput) {
            userIdInput.addEventListener('input', function() {
                const role = selectedRoleInput.value;
                const value = this.value;
                
                // Update hidden fields based on role
                if (role === 'student') {
                    studentIdInput.value = value;
                    employeeIdInput.value = '';
                } else if (role === 'instructor') {
                    employeeIdInput.value = value;
                    studentIdInput.value = '';
                }
            });
        }
        
        // Form submit handler
        if (registrationForm) {
            registrationForm.addEventListener('submit', function(e) {
                if (!validateRegistrationForm()) {
                    e.preventDefault();
                    return;
                }
                
                // Show loading state
                submitBtn.disabled = true;
                submitText.textContent = 'Creating Account...';
                submitLoader.classList.remove('hidden');
                
                // Update hidden fields one more time before submit
                const role = selectedRoleInput.value;
                const userId = userIdInput.value;
                
                if (role === 'student') {
                    studentIdInput.value = userId;
                    employeeIdInput.value = '';
                } else if (role === 'instructor') {
                    employeeIdInput.value = userId;
                    studentIdInput.value = '';
                }
            });
        }
        
        // Set initial role if provided by old() value
        const oldRole = selectedRoleInput.value;
        if (oldRole) {
            const roleCard = document.querySelector(`[data-role="${oldRole}"]`);
            if (roleCard) {
                roleCard.click();
            }
        }
        
        // Registration-specific input focus effects
        const regInputs = document.querySelectorAll('input[type="text"], input[type="email"], input[type="password"]');
        regInputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.classList.add('ring-2', 'ring-white/30');
            });
            
            input.addEventListener('blur', function() {
                this.classList.remove('ring-2', 'ring-white/30');
            });
        });
    }
    
    // ===========================================
    // SHARED FUNCTIONS
    // ===========================================
    
    // Show error message (shared for both pages)
    function showError(message) {
        // Remove existing error messages
        const existingError = document.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }
        
        // Create new error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message bg-red-500/20 border border-red-400/30 rounded-lg p-3 mt-4 text-red-300 text-sm';
        errorDiv.innerHTML = `<i class="fas fa-exclamation-circle mr-2"></i>${message}`;
        
        // Insert before submit button
        const submitButton = document.getElementById('loginButton') || document.getElementById('submitBtn');
        if (submitButton) {
            submitButton.parentNode.insertBefore(errorDiv, submitButton);
        }
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            errorDiv.remove();
        }, 5000);
    }
    
    // Message display function for registration
    function showMessage(message, type) {
        // Remove existing messages
        const existingMessages = document.querySelectorAll('.dynamic-message');
        existingMessages.forEach(msg => msg.remove());
        
        const messageDiv = document.createElement('div');
        messageDiv.className = `dynamic-message mb-6 p-4 rounded-lg border ${
            type === 'error' 
                ? 'bg-red-50 border-red-200 text-red-800' 
                : 'bg-green-50 border-green-200 text-green-800'
        }`;
        
        messageDiv.innerHTML = `
            <div class="flex">
                <i class="fas ${type === 'error' ? 'fa-exclamation-circle text-red-500' : 'fa-check-circle text-green-500'} mr-3 mt-0.5"></i>
                <div>
                    <h3 class="font-semibold">${type === 'error' ? 'Registration Failed' : 'Success'}</h3>
                    <div class="mt-2 text-sm">${message}</div>
                </div>
            </div>
        `;
        
        // Insert at the top of the form container
        const formContainer = document.querySelector('.glass-effect');
        if (formContainer) {
            formContainer.insertBefore(messageDiv, formContainer.firstChild);
        }
        
        // Auto-remove after 10 seconds
        setTimeout(() => {
            messageDiv.remove();
        }, 10000);
    }
});

// Global functions
window.authPageFunctions = {
    showError: function(message) {
        console.error('Auth Error:', message);
    },
    showMessage: function(message, type) {
        console.log(`Auth ${type}:`, message);
    }
};

// Google OAuth function
window.loginWithGoogle = function(role) {
    // Show loading state
    const buttons = document.querySelectorAll('button[onclick*="loginWithGoogle"]');
    buttons.forEach(btn => {
        btn.disabled = true;
        btn.classList.add('opacity-50', 'cursor-not-allowed');
        
        // Add loading text
        const span = btn.querySelector('span');
        if (span) {
            span.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i>Redirecting...`;
        }
    });
    
    // Redirect to Google OAuth with role parameter
    window.location.href = `/auth/google/${role}`;
};
