/**
 * Real-time updates for Reports Management
 */

class ReportsRealtime {
    constructor() {
        this.isInitialized = false;
    }

    init() {
        // Only initialize on reports page
        if (!window.location.pathname.includes('/reports')) {
            return;
        }

        this.setupEcho();
        this.isInitialized = true;
        console.log('Reports real-time initialized');
    }

    setupEcho() {
        if (typeof window.Echo === 'undefined') {
            console.warn('Echo not available for reports real-time');
            return;
        }

        // Listen to student-reports channel (SAS Admin and Global Admin)
        window.Echo.channel('student-reports')
            .listen('.report.created', (e) => {
                console.log('Student report created event received:', e);
                this.handleReportCreated(e.report);
            })
            .listen('.report.status-updated', (e) => {
                console.log('Student report status updated event received:', e);
                this.handleReportStatusUpdated(e.report);
            });

        // Listen to non-student-reports channel (Security Admin, Chancellor Admin, and Global Admin)
        window.Echo.channel('non-student-reports')
            .listen('.report.created', (e) => {
                console.log('Non-student report created event received:', e);
                this.handleReportCreated(e.report);
            })
            .listen('.report.status-updated', (e) => {
                console.log('Non-student report status updated event received:', e);
                this.handleReportStatusUpdated(e.report);
            });

        this.updateConnectionStatus(true);
    }

    handleReportCreated(report) {
        // Check if user should see this report based on their role
        if (!this.shouldShowReport(report)) {
            return;
        }

        // Add report to table
        this.addReportToTable(report);
        
        const violatorName = report.violator_vehicle?.user 
            ? `${report.violator_vehicle.user.first_name} ${report.violator_vehicle.user.last_name}`
            : 'Unknown';
        const violationType = report.violation_type?.name || 'Unknown';
        
        // Show in-app notification
        this.showSystemNotification('New Report Submitted', `New ${violationType} violation reported against ${violatorName}`);
        
        // Show browser notification
        this.showBrowserNotification('New Report Submitted', `New ${violationType} violation reported against ${violatorName}`);
    }

    handleReportStatusUpdated(report) {
        // Check if user should see this report based on their role
        if (!this.shouldShowReport(report)) {
            return;
        }

        // Update report in table
        this.updateReportInTable(report);
        
        const violatorName = report.violator_vehicle?.user 
            ? `${report.violator_vehicle.user.first_name} ${report.violator_vehicle.user.last_name}`
            : 'Unknown';
        
        // Show in-app notification
        this.showSystemNotification('Report Status Updated', `Report against ${violatorName} is now ${report.status}`);
        
        // Show browser notification
        this.showBrowserNotification('Report Status Updated', `Report against ${violatorName} is now ${report.status}`);
    }

    shouldShowReport(report) {
        // Get admin role from page
        const adminRole = document.querySelector('[data-admin-role]')?.dataset.adminRole;
        
        if (!adminRole) return true; // Global admin sees all
        
        const violatorType = report.violator_vehicle?.user?.user_type;
        
        // SAS Admin: Only students
        if (adminRole === 'SAS (Student Affairs & Services)') {
            return violatorType === 'student';
        }
        
        // Chancellor & Security Admin: Non-students
        if (adminRole === 'Chancellor' || adminRole === 'Security') {
            return violatorType !== 'student';
        }
        
        return true;
    }

    addReportToTable(report) {
        const tbody = document.getElementById('reportsTableBody');
        if (!tbody) return;

        // Check if report already exists
        const existingRow = tbody.querySelector(`tr[data-id="${report.id}"]`);
        if (existingRow) {
            console.log('Report already exists, updating instead');
            this.updateReportInTable(report);
            return;
        }

        // Create new row
        const row = this.createReportRow(report);
        
        // Add to top of table with animation
        tbody.insertBefore(row, tbody.firstChild);
        row.classList.add('animate-fade-in');
        
        // Update reports array if it exists
        if (window.reports) {
            window.reports.unshift(report);
        }
    }

    updateReportInTable(report) {
        const row = document.querySelector(`tr[data-id="${report.id}"]`);
        if (!row) {
            console.log('Report row not found, adding new row');
            this.addReportToTable(report);
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

    createReportRow(report) {
        const row = document.createElement('tr');
        row.setAttribute('data-id', report.id);
        row.className = 'border-b border-[#e3e3e0] dark:border-[#3E3E3A] hover:bg-gray-50 dark:hover:bg-[#2a2a2a] transition-colors';

        const violatorName = report.violator_vehicle?.user 
            ? `${report.violator_vehicle.user.first_name} ${report.violator_vehicle.user.last_name}`
            : 'Unknown';
        
        const reporterName = report.reported_by
            ? `${report.reported_by.first_name} ${report.reported_by.last_name}`
            : 'Unknown';

        const vehicleInfo = report.violator_vehicle?.plate_no || report.violator_sticker_number || 'N/A';
        const violationType = report.violation_type?.name || 'N/A';
        const reportedAt = new Date(report.reported_at).toLocaleDateString();

        row.innerHTML = `
            <td class="py-2 px-3 text-sm text-[#1b1b18] dark:text-[#EDEDEC]">${violationType}</td>
            <td class="py-2 px-3 text-sm text-[#1b1b18] dark:text-[#EDEDEC]">${violatorName}</td>
            <td class="py-2 px-3 text-sm text-[#1b1b18] dark:text-[#EDEDEC]">${vehicleInfo}</td>
            <td class="py-2 px-3 text-sm text-[#1b1b18] dark:text-[#EDEDEC]">${reporterName}</td>
            <td class="py-2 px-3 text-sm text-[#1b1b18] dark:text-[#EDEDEC]">${reportedAt}</td>
            <td class="py-2 px-3 text-center">
                <span class="status-badge ${this.getStatusClass(report.status)}">${this.capitalizeFirst(report.status)}</span>
            </td>
            <td class="py-2 px-3 text-center">
                <button onclick="viewReport(${report.id})" class="btn-view" title="View">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                </button>
            </td>
        `;

        return row;
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
        console.log('Reports real-time connection:', connected ? 'Connected' : 'Disconnected');
    }
}

// Auto-initialize
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        const manager = new ReportsRealtime();
        manager.init();
        window.reportsRealtimeManager = manager;
    });
} else {
    const manager = new ReportsRealtime();
    manager.init();
    window.reportsRealtimeManager = manager;
}

export default ReportsRealtime;
