/**
 * Centralized Notification Manager
 * Handles all success/error/warning/info messages across the application
 * Prevents duplicate notifications and ensures consistent UX
 */

class NotificationManager {
    constructor() {
        this.activeNotifications = new Set();
        this.lastNotification = null;
        this.lastNotificationTime = 0;
        this.duplicateThreshold = 1000; // 1 second to prevent duplicates
        
        this.init();
    }
    
    init() {
        // Create notification container if it doesn't exist
        if (!document.getElementById('notification-container')) {
            const container = document.createElement('div');
            container.id = 'notification-container';
            container.className = 'fixed top-4 left-1/2 transform -translate-x-1/2 z-50 space-y-2';
            container.style.zIndex = '9999';
            container.style.minWidth = '400px';
            container.style.maxWidth = '600px';
            container.style.width = 'auto';
            document.body.appendChild(container);
        }
        
        // Process initial session messages
        this.processSessionMessages();
        
        // Override default alert to use our system
        this.overrideDefaultAlerts();
    }
    
    /**
     * Show notification with duplicate prevention
     */
    show(message, type = 'info', duration = 5000) {
        // Prevent duplicate notifications
        if (this.isDuplicate(message, type)) {
            return;
        }
        
        // Clean message text
        const cleanMessage = this.cleanMessage(message);
        
        // Create notification
        const notification = this.createNotification(cleanMessage, type);
        
        // Add to container
        const container = document.getElementById('notification-container');
        container.appendChild(notification);
        
        // Track active notification
        this.trackNotification(cleanMessage, type);
        
        // Auto remove
        setTimeout(() => {
            this.removeNotification(notification, cleanMessage);
        }, duration);
        
        // Animate in from top
        requestAnimationFrame(() => {
            notification.classList.remove('-translate-y-full', 'opacity-0');
        });
    }
    
    /**
     * Check if notification is duplicate
     */
    isDuplicate(message, type) {
        const cleanMessage = this.cleanMessage(message);
        const now = Date.now();
        
        if (this.lastNotification === `${type}:${cleanMessage}` && 
            (now - this.lastNotificationTime) < this.duplicateThreshold) {
            return true;
        }
        
        return this.activeNotifications.has(`${type}:${cleanMessage}`);
    }
    
    /**
     * Clean and normalize message text
     */
    cleanMessage(message) {
        if (typeof message !== 'string') {
            message = String(message);
        }
        
        return message
            .replace(/\s+/g, ' ')
            .trim()
            .replace(/^["']|["']$/g, ''); // Remove quotes
    }
    
    /**
     * Create notification element
     */
    createNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `transform transition-all duration-300 -translate-y-full opacity-0 w-full shadow-lg rounded-lg pointer-events-auto ${this.getTypeClasses(type)}`;
        
        const icon = this.getTypeIcon(type);
        
        notification.innerHTML = `
            <div class="p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center flex-1">
                        <div class="flex-shrink-0 mr-3">
                            <i class="${icon} text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium whitespace-nowrap overflow-hidden text-ellipsis">${this.escapeHtml(message)}</p>
                        </div>
                    </div>
                    <div class="flex-shrink-0 ml-4">
                        <button class="rounded-md inline-flex text-gray-400 hover:text-gray-600 focus:outline-none" onclick="this.parentElement.parentElement.parentElement.remove()">
                            <span class="sr-only">Close</span>
                            <i class="fas fa-times text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        return notification;
    }
    
    /**
     * Get CSS classes for notification type
     */
    getTypeClasses(type) {
        const classes = {
            'success': 'bg-green-50 border border-green-200 text-green-800',
            'error': 'bg-red-50 border border-red-200 text-red-800',
            'warning': 'bg-yellow-50 border border-yellow-200 text-yellow-800',
            'info': 'bg-blue-50 border border-blue-200 text-blue-800'
        };
        
        return classes[type] || classes.info;
    }
    
    /**
     * Get icon for notification type
     */
    getTypeIcon(type) {
        const icons = {
            'success': 'fas fa-check-circle text-green-600',
            'error': 'fas fa-exclamation-circle text-red-600',
            'warning': 'fas fa-exclamation-triangle text-yellow-600',
            'info': 'fas fa-info-circle text-blue-600'
        };
        
        return icons[type] || icons.info;
    }
    
    /**
     * Track active notification
     */
    trackNotification(message, type) {
        const key = `${type}:${message}`;
        this.activeNotifications.add(key);
        this.lastNotification = key;
        this.lastNotificationTime = Date.now();
    }
    
    /**
     * Remove notification and tracking
     */
    removeNotification(element, message) {
        if (element && element.parentNode) {
            element.classList.add('-translate-y-full', 'opacity-0');
            setTimeout(() => {
                if (element.parentNode) {
                    element.remove();
                }
            }, 300);
        }
        
        // Clean up tracking for all types
        ['success', 'error', 'warning', 'info'].forEach(type => {
            this.activeNotifications.delete(`${type}:${message}`);
        });
    }
    
    /**
     * Process session messages from server
     */
    processSessionMessages() {
        // Check for session success message
        const successMessage = this.getSessionMessage('success');
        if (successMessage) {
            this.show(successMessage, 'success');
        }
        
        // Check for session error message
        const errorMessage = this.getSessionMessage('error');
        if (errorMessage) {
            this.show(errorMessage, 'error');
        }
        
        // Check for data attributes (auth pages)
        const bodySuccess = document.body.getAttribute('data-session-success');
        if (bodySuccess) {
            this.show(bodySuccess, 'success');
        }
        
        const bodyError = document.body.getAttribute('data-session-error');
        if (bodyError) {
            this.show(bodyError, 'error');
        }
    }
    
    /**
     * Get session message from meta tag or global variable
     */
    getSessionMessage(type) {
        // Try meta tag first
        const meta = document.querySelector(`meta[name="session-${type}"]`);
        if (meta) {
            return meta.getAttribute('content');
        }
        
        // Try global variable
        if (window.sessionMessages && window.sessionMessages[type]) {
            return window.sessionMessages[type];
        }
        
        return null;
    }
    
    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    /**
     * Override default browser alerts
     */
    overrideDefaultAlerts() {
        // Override alert to use our notification system
        window.alert = (message) => {
            this.show(message, 'info');
        };
        
        // Add global notification functions
        window.showSuccess = (message) => this.show(message, 'success');
        window.showError = (message) => this.show(message, 'error');
        window.showWarning = (message) => this.show(message, 'warning');
        window.showInfo = (message) => this.show(message, 'info');
        
        // Alias for compatibility
        window.showNotification = (message, type = 'info') => this.show(message, type);
    }
    
    /**
     * Clear all notifications
     */
    clearAll() {
        const container = document.getElementById('notification-container');
        if (container) {
            container.innerHTML = '';
        }
        this.activeNotifications.clear();
    }
    
    /**
     * Remove flash messages from DOM after processing
     */
    hideFlashMessages() {
        const flashContainer = document.querySelector('.flash-messages, [class*="flash"]');
        if (flashContainer) {
            flashContainer.style.display = 'none';
        }
    }
}

// Initialize notification manager when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.notificationManager = new NotificationManager();
    
    // Hide traditional flash messages after a short delay to allow processing
    setTimeout(() => {
        window.notificationManager.hideFlashMessages();
    }, 100);
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = NotificationManager;
}
