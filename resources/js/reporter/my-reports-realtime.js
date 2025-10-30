/**
 * Real-time updates for Reporter My Reports
 */

class MyReportsRealtime {
    constructor() {
        this.isInitialized = false;
        this.currentUserId = null;
    }

    init() {
        // Only initialize on my-reports page
        if (!window.location.pathname.includes('/my-reports')) {
            return;
        }

        // Get current user ID
        const userIdElement = document.querySelector('[data-user-id]');
        if (userIdElement) {
            this.currentUserId = parseInt(userIdElement.dataset.userId);
        }

        this.setupEcho();
        this.isInitialized = true;
        console.log('My Reports real-time initialized');
    }

    setupEcho() {
        if (typeof window.Echo === 'undefined') {
            console.warn('Echo not available for my reports real-time');
            return;
        }

        // Listen to reports channel
        window.Echo.channel('reports')
            .listen('.report.status-updated', (e) => {
                console.log('Report status updated event received:', e);
                this.handleReportStatusUpdated(e.report);
            });

        this.updateConnectionStatus(true);
    }

    handleReportStatusUpdated(report) {
        // Only update if this is the current user's report
        if (report.reported_by !== this.currentUserId) {
            return;
        }

        // Update report in table
        this.updateReportInTable(report);
        
        const violatorName = report.violator_vehicle?.user 
            ? `${report.violator_vehicle.user.first_name} ${report.violator_vehicle.user.last_name}`
            : 'Unknown';
        const statusText = this.capitalizeFirst(report.status);
        
        // Show in-app notification
        this.showSystemNotification('Report Status Updated', `Your report against ${violatorName} has been ${statusText}`);
        
        // Show browser notification
        this.showBrowserNotification('Report Status Updated', `Your report against ${violatorName} has been ${statusText}`);
    }

    updateReportInTable(report) {
        const row = document.querySelector(`tr[data-id="${report.id}"]`);
        if (!row) {
            console.log('Report row not found');
            return;
        }

        // Update status badge
        const statusCell = row.querySelector('.status-badge');
        if (statusCell) {
            statusCell.className = 'status-badge ' + this.getStatusClass(report.status);
            statusCell.textContent = this.capitalizeFirst(report.status);
        }

        // Add pulse animation
        row.classList.add('animate-pulse');
        setTimeout(() => row.classList.remove('animate-pulse'), 1000);

        // Update reports array if it exists
        if (window.reports) {
            const index = window.reports.findIndex(r => r.id === report.id);
            if (index !== -1) {
                window.reports[index] = report;
            }
        }
    }

    getStatusClass(status) {
        const classes = {
            'pending': 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200',
            'approved': 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200',
            'rejected': 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200'
        };
        return classes[status] || 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200';
    }

    capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    showSystemNotification(title, message) {
        // Add to system notifications (in-app)
        if (typeof window.addNotification === 'function') {
            window.addNotification({
                type: 'report_update',
                title: title,
                message: message,
                timestamp: new Date().toISOString()
            });
        }
    }

    showBrowserNotification(title, message) {
        // Request permission if not granted
        if (Notification.permission === 'default') {
            Notification.requestPermission();
        }
        
        // Show browser notification if permission granted
        if (Notification.permission === 'granted') {
            new Notification(title, {
                body: message,
                icon: '/favicon.ico',
                badge: '/favicon.ico',
                tag: 'report-notification'
            });
        }
    }

    updateConnectionStatus(connected) {
        console.log('My Reports real-time connection:', connected ? 'Connected' : 'Disconnected');
    }
}

// Auto-initialize
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        const manager = new MyReportsRealtime();
        manager.init();
        window.myReportsRealtimeManager = manager;
    });
} else {
    const manager = new MyReportsRealtime();
    manager.init();
    window.myReportsRealtimeManager = manager;
}

export default MyReportsRealtime;
