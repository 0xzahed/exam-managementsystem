import './bootstrap';
import './pages/auth';

// Import page-specific JavaScript files
import './pages/welcome.js';
import './pages/auth.js';

// Global notification utility
window.showNotification = function(message, type = 'info') {
    const colors = {
        success: 'bg-green-500 text-white border-green-600',
        error: 'bg-red-500 text-white border-red-600',
        warning: 'bg-yellow-500 text-white border-yellow-600',
        info: 'bg-blue-500 text-white border-blue-600'
    };

    const icons = {
        success: 'fas fa-check-circle',
        error: 'fas fa-times-circle',
        warning: 'fas fa-exclamation-triangle',
        info: 'fas fa-info-circle'
    };

    // Create toast container if it doesn't exist
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'fixed top-4 right-4 z-50 space-y-2';
        document.body.appendChild(container);
    }

    // Create toast element
    const toast = document.createElement('div');
    toast.className = `${colors[type]} px-4 py-3 rounded-lg shadow-lg border transform transition-all duration-300 translate-x-full opacity-0 max-w-sm`;
    toast.innerHTML = `
        <div class="flex items-center">
            <i class="${icons[type]} mr-3"></i>
            <span class="flex-1">${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-3 text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;

    container.appendChild(toast);

    // Animate in
    setTimeout(() => {
        toast.classList.remove('translate-x-full', 'opacity-0');
    }, 100);

    // Auto remove after 5 seconds
    setTimeout(() => {
        toast.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => toast.remove(), 300);
    }, 5000);
};

// Global success notification shortcut
window.showSuccess = function(message) { window.showNotification(message, 'success'); };

// Global error notification shortcut
window.showError = function(message) { window.showNotification(message, 'error'); };

// Global warning notification shortcut
window.showWarning = function(message) { window.showNotification(message, 'warning'); };

// Global info notification shortcut
window.showInfo = function(message) { window.showNotification(message, 'info'); };

// Soft-disable blocking alerts to enforce unified flash UI
window.alert = function(message) {
    console.log('[alert]', message);
};
