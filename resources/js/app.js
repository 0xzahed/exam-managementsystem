import './bootstrap';

// Import page-specific JavaScript files
import './pages/welcome.js';
import './pages/auth.js';

// Global notification utility
window.showNotification = function(message, type = 'success') {
    // Remove any existing notifications first
    const existingNotifications = document.querySelectorAll('.notification-container');
    existingNotifications.forEach(notification => notification.remove());
    
    // Create notification container
    const container = document.createElement('div');
    container.className = 'notification-container mb-6 space-y-4';
    
    // Define colors and icons based on type
    const typeConfig = {
        success: {
            bg: 'bg-green-50',
            border: 'border-l-4 border-green-500',
            iconBg: 'bg-green-100',
            iconColor: 'text-green-500',
            textColor: 'text-green-800',
            textColorSecondary: 'text-green-700',
            icon: 'fas fa-check-circle',
            title: 'Success!'
        },
        error: {
            bg: 'bg-red-50',
            border: 'border-l-4 border-red-500',
            iconBg: 'bg-red-100',
            iconColor: 'text-red-500',
            textColor: 'text-red-800',
            textColorSecondary: 'text-red-700',
            icon: 'fas fa-exclamation-circle',
            title: 'Error!'
        },
        warning: {
            bg: 'bg-yellow-50',
            border: 'border-l-4 border-yellow-500',
            iconBg: 'bg-yellow-100',
            iconColor: 'text-yellow-500',
            textColor: 'text-yellow-800',
            textColorSecondary: 'text-yellow-700',
            icon: 'fas fa-exclamation-triangle',
            title: 'Warning!'
        },
        info: {
            bg: 'bg-blue-50',
            border: 'border-l-4 border-blue-500',
            iconBg: 'bg-blue-100',
            iconColor: 'text-blue-500',
            textColor: 'text-blue-800',
            textColorSecondary: 'text-blue-700',
            icon: 'fas fa-info-circle',
            title: 'Information'
        }
    };
    
    const config = typeConfig[type] || typeConfig['info'];
    
    // Create notification HTML
    container.innerHTML = `
        <div class="${config.bg} ${config.border} p-4 rounded-lg shadow-sm">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <div class="${config.iconBg} p-2 rounded-full">
                        <i class="${config.icon} ${config.iconColor} text-lg"></i>
                    </div>
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-sm font-medium ${config.textColor}">${config.title}</h3>
                    <p class="mt-1 text-sm ${config.textColorSecondary}">${message}</p>
                </div>
                <div class="flex-shrink-0 ml-3">
                    <button type="button" class="inline-flex rounded-md p-1.5 ${config.iconColor} hover:${config.iconBg} focus:outline-none focus:ring-2 focus:ring-${type}-500 focus:ring-offset-2" onclick="this.parentElement.parentElement.parentElement.parentElement.remove()">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    // Find where to insert the notification
    const mainContent = document.querySelector('.max-w-7xl.mx-auto, .container.mx-auto, main');
    if (mainContent) {
        // Insert at the beginning of main content
        mainContent.insertBefore(container, mainContent.firstChild);
    } else {
        // Fallback: insert at beginning of body
        document.body.insertBefore(container, document.body.firstChild);
    }
    
    // Auto-remove after 8 seconds
    setTimeout(() => {
        if (container && container.parentNode) {
            container.style.transition = 'opacity 0.5s ease-out';
            container.style.opacity = '0';
            setTimeout(() => {
                container.remove();
            }, 500);
        }
    }, 8000);
};

// Global success notification shortcut
window.showSuccess = function(message) {
    showNotification(message, 'success');
};

// Global error notification shortcut
window.showError = function(message) {
    showNotification(message, 'error');
};

// Global warning notification shortcut
window.showWarning = function(message) {
    showNotification(message, 'warning');
};

// Global info notification shortcut
window.showInfo = function(message) {
    showNotification(message, 'info');
};
