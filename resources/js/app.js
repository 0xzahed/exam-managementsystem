import './bootstrap';
import './pages/auth';

// Import page-specific JavaScript files
import './pages/welcome.js';
import './pages/auth.js';

// Global notification utility
import './bootstrap';
import './utils/NotificationManager';

// Legacy notification function for backwards compatibility
// This is now handled by NotificationManager but kept for existing code
window.showNotification = function(message, type = 'success') {
    if (window.notificationManager) {
        window.notificationManager.show(message, type);
    } else {
        // Fallback for early calls before NotificationManager is ready
        console.log(`[${type}] ${message}`);
    }
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
