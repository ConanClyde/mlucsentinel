/**
 * Notification Settings - Browser Notifications
 */

// Initialize notifications - request permission on first visit
export function initializeNotifications() {
    if (!('Notification' in window)) {
        checkNotificationPermission();
        return;
    }
    
    const preference = localStorage.getItem('browserNotifications');
    
    // If user hasn't made a choice yet, automatically request permission
    if (!preference && Notification.permission === 'default') {
        Notification.requestPermission().then(function(permission) {
            if (permission === 'granted') {
                localStorage.setItem('browserNotifications', 'enabled');
                showNotification('Browser notifications enabled! You can disable them anytime in settings.', 'success');
                checkNotificationPermission();
            } else {
                // User denied, don't ask again
                localStorage.setItem('browserNotifications', 'disabled');
                checkNotificationPermission();
            }
        });
    } else {
        // User has already made a choice, just update the UI
        checkNotificationPermission();
    }
}

// Check and update notification permission status
export function checkNotificationPermission() {
    const toggle = document.getElementById('browser-notifications-toggle');
    const statusText = document.getElementById('notification-status');
    
    if (!toggle || !statusText) return;
    
    // Clear previous status classes
    statusText.className = 'text-xs mt-1';
    
    if (!('Notification' in window)) {
        statusText.textContent = 'Browser notifications not supported';
        statusText.classList.add('text-red-600', 'dark:text-red-400');
        toggle.disabled = true;
        return;
    }
    
    const preference = localStorage.getItem('browserNotifications') || 'disabled';
    
    if (Notification.permission === 'granted' && preference === 'enabled') {
        toggle.checked = true;
        statusText.textContent = 'Notifications enabled';
        statusText.classList.add('text-green-600', 'dark:text-green-400');
    } else if (Notification.permission === 'denied') {
        toggle.checked = false;
        statusText.textContent = 'Notifications blocked by browser. Please enable in browser settings.';
        statusText.classList.add('text-red-600', 'dark:text-red-400');
        toggle.disabled = true;
    } else if (Notification.permission === 'default' || preference === 'disabled') {
        toggle.checked = false;
        statusText.textContent = preference === 'disabled' && Notification.permission === 'granted' 
            ? 'Notifications disabled' 
            : 'Click the toggle to enable notifications';
        statusText.classList.add('text-gray-600', 'dark:text-gray-400');
    }
}

// Show notification function
export function showNotification(message, type = 'success') {
    // Create notification element
    const notification = document.createElement('div');
    const bgColor = type === 'error' ? 'bg-red-500' : type === 'warning' ? 'bg-yellow-500' : 'bg-green-500';
    notification.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-opacity duration-300`;
    notification.textContent = message;
    
    // Add to body
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Toggle browser notifications
export function toggleBrowserNotifications(checkbox) {
    if (!('Notification' in window)) {
        showNotification('Your browser does not support notifications', 'error');
        checkbox.checked = false;
        return;
    }
    
    if (checkbox.checked) {
        // User wants to enable notifications
        if (Notification.permission === 'granted') {
            localStorage.setItem('browserNotifications', 'enabled');
            showNotification('Browser notifications enabled!', 'success');
            checkNotificationPermission();
        } else if (Notification.permission === 'denied') {
            showNotification('Notifications are blocked. Please enable them in your browser settings.', 'error');
            checkbox.checked = false;
        } else {
            // Request permission
            Notification.requestPermission().then(function(permission) {
                if (permission === 'granted') {
                    localStorage.setItem('browserNotifications', 'enabled');
                    showNotification('Browser notifications enabled!', 'success');
                    checkNotificationPermission();
                    
                    // Show a welcome notification
                    new Notification('MLUC Sentinel', {
                        body: 'You will now receive browser notifications!',
                        icon: '/favicon.ico'
                    });
                } else {
                    showNotification('Permission denied. Please enable notifications in your browser.', 'error');
                    checkbox.checked = false;
                    checkNotificationPermission();
                }
            });
        }
    } else {
        // User wants to disable notifications
        localStorage.setItem('browserNotifications', 'disabled');
        showNotification('Browser notifications disabled', 'success');
        checkNotificationPermission();
    }
}

// Make functions globally available
window.toggleBrowserNotifications = toggleBrowserNotifications;
window.showNotification = showNotification;

