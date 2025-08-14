import './bootstrap';

// Import page-specific JavaScript files
import './pages/welcome.js';
import './pages/auth.js';

// Global notification utility (disabled; use server-side flash only)
window.showNotification = function(message, type = 'info') {
    console.log(`[${type}]`, message);
};

// Global success notification shortcut
window.showSuccess = function(message) { console.log('[success]', message); };

// Global error notification shortcut
window.showError = function(message) { console.log('[error]', message); };

// Global warning notification shortcut
window.showWarning = function(message) { console.log('[warning]', message); };

// Global info notification shortcut
window.showInfo = function(message) { console.log('[info]', message); };

// Soft-disable blocking alerts to enforce unified flash UI
window.alert = function(message) {
    console.log('[alert]', message);
};
