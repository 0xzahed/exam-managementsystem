/**
 * Instructor Profile Settings Page JavaScript
 * Handles profile updates, password changes, and form validations
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Profile form handling
    const profileForm = document.getElementById('profileForm');
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            const firstName = document.getElementById('first_name').value.trim();
            const lastName = document.getElementById('last_name').value.trim();
            const email = document.getElementById('email').value.trim();
            
            if (!firstName || !lastName || !email) {
                e.preventDefault();
                showError('Please fill in all required fields');
                return;
            }
            
            if (!isValidEmail(email)) {
                e.preventDefault();
                showError('Please enter a valid email address');
                return;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
            }
        });
    }
    
    // Password form handling
    const passwordForm = document.getElementById('passwordForm');
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            const currentPassword = document.getElementById('current_password').value;
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('new_password_confirmation').value;
            
            if (!currentPassword || !newPassword || !confirmPassword) {
                e.preventDefault();
                showError('Please fill in all password fields');
                return;
            }
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                showError('New passwords do not match');
                return;
            }
            
            if (newPassword.length < 8) {
                e.preventDefault();
                showError('Password must be at least 8 characters long');
                return;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Updating...';
            }
        });
        
        // Real-time password confirmation validation
        const newPasswordField = document.getElementById('new_password');
        const confirmPasswordField = document.getElementById('new_password_confirmation');
        
        if (newPasswordField && confirmPasswordField) {
            confirmPasswordField.addEventListener('input', function() {
                const newPassword = newPasswordField.value;
                const confirmPassword = this.value;
                
                const messageElement = document.getElementById('password-match-message');
                if (messageElement) {
                    if (confirmPassword && newPassword !== confirmPassword) {
                        messageElement.textContent = 'Passwords do not match';
                        messageElement.className = 'text-sm text-red-600 mt-1';
                    } else if (confirmPassword && newPassword === confirmPassword) {
                        messageElement.textContent = 'Passwords match';
                        messageElement.className = 'text-sm text-green-600 mt-1';
                    } else {
                        messageElement.textContent = '';
                    }
                }
            });
        }
    }
    
    // Profile picture upload handling
    const profilePictureInput = document.getElementById('profile_picture');
    const profilePreview = document.getElementById('profile-preview');
    
    if (profilePictureInput && profilePreview) {
        profilePictureInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    showError('Please select a valid image file (JPG, PNG, or GIF)');
                    this.value = '';
                    return;
                }
                
                // Validate file size (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    showError('Image file size must be less than 5MB');
                    this.value = '';
                    return;
                }
                
                // Preview the image
                const reader = new FileReader();
                reader.onload = function(e) {
                    profilePreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Auto-save draft functionality for bio
    const bioTextarea = document.getElementById('bio');
    if (bioTextarea) {
        let bioTimeout;
        bioTextarea.addEventListener('input', function() {
            clearTimeout(bioTimeout);
            bioTimeout = setTimeout(() => {
                // Save bio draft to localStorage
                localStorage.setItem('instructor_bio_draft', this.value);
            }, 1000);
        });
        
        // Load bio draft on page load
        const savedBio = localStorage.getItem('instructor_bio_draft');
        if (savedBio && !bioTextarea.value) {
            bioTextarea.value = savedBio;
        }
        
        // Clear draft when form is submitted
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function() {
                localStorage.removeItem('instructor_bio_draft');
            });
        });
    }
    
    // Phone number formatting
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            // Remove all non-digits
            let value = this.value.replace(/\D/g, '');
            
            // Format as (XXX) XXX-XXXX
            if (value.length >= 6) {
                value = `(${value.slice(0, 3)}) ${value.slice(3, 6)}-${value.slice(6, 10)}`;
            } else if (value.length >= 3) {
                value = `(${value.slice(0, 3)}) ${value.slice(3)}`;
            }
            
            this.value = value;
        });
    }
    
    // Department selection handling
    const departmentSelect = document.getElementById('department');
    if (departmentSelect) {
        departmentSelect.addEventListener('change', function() {
            const selectedDept = this.value;
            console.log('Department changed to:', selectedDept);
            
            // You can add department-specific logic here
            // For example, loading related courses or specializations
        });
    }
    
    // Show success message if redirected with success
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('success')) {
        // Use session message if available, otherwise default message
        const successMeta = document.querySelector('meta[name="session-success"]');
        const message = successMeta ? successMeta.getAttribute('content') : 'Profile updated successfully!';
        
        // Use NotificationManager for consistent styling
        if (window.notificationManager) {
            window.notificationManager.show(message, 'success');
        } else {
            // Fallback if NotificationManager not ready
            setTimeout(() => {
                if (window.notificationManager) {
                    window.notificationManager.show(message, 'success');
                } else {
                    showSuccess(message);
                }
            }, 100);
        }
        
        // Clear the URL parameter
        window.history.replaceState({}, document.title, window.location.pathname);
    }
});

// Helper functions
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        isValidEmail,
        showError,
        showSuccess
    };
}
