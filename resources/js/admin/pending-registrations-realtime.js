/**
 * Real-time Pending Registrations Updates Module
 * 
 * This module handles real-time updates for the pending registrations table
 * using Laravel Echo and Reverb WebSocket server.
 */

class PendingRegistrationsRealtime {
    constructor() {
        this.connectionStatus = null;
        this.tableBody = null;
        this.pendingRegistrations = [];
        this.channel = null;
        this.retryCount = 0;
        this.maxRetries = 5;
    }

    /**
     * Initialize the real-time connection
     */
    init(pendingRegistrations = []) {
        this.pendingRegistrations = pendingRegistrations;
        this.connectionStatus = document.getElementById('connectionStatus');
        this.tableBody = document.getElementById('registrationsTableBody');

        if (!this.connectionStatus || !this.tableBody) {
            console.error('Required DOM elements not found');
            return;
        }

        this.setupEcho();
    }

    /**
     * Setup Laravel Echo connection
     */
    setupEcho() {
        if (!window.Echo) {
            if (this.retryCount < this.maxRetries) {
                this.retryCount++;
                console.log(`Echo not ready, retrying... (${this.retryCount}/${this.maxRetries})`);
                setTimeout(() => this.setupEcho(), 500);
            } else {
                console.error('Echo failed to initialize after maximum retries');
                this.updateConnectionStatus('error');
            }
            return;
        }

        console.log('Setting up Echo connection for pending registrations...');
        this.updateConnectionStatus('connecting');

        try {
            // Subscribe to the administrators channel
            this.channel = window.Echo.channel('administrators');

            // Listen for pending-registration.created events
            this.channel.listen('.pending-registration.created', (event) => {
                console.log('Received pending registration broadcast event:', event);
                this.handlePendingRegistrationCreated(event);
            });

            // Listen for pending-registration.updated events (approved, rejected, deleted)
            this.channel.listen('.pending-registration.updated', (event) => {
                console.log('Received pending registration update event:', event);
                this.handlePendingRegistrationUpdated(event);
            });

            // Handle connection success
            this.channel.subscribed(() => {
                console.log('Successfully subscribed to administrators channel');
                this.updateConnectionStatus('connected');
            });

            // Handle connection errors
            this.channel.error((error) => {
                console.error('Channel subscription error:', error);
                this.updateConnectionStatus('error');
            });

        } catch (error) {
            console.error('Error setting up Echo:', error);
            this.updateConnectionStatus('error');
        }
    }

    /**
     * Update connection status indicator
     */
    updateConnectionStatus(status) {
        if (!this.connectionStatus) return;

        const statusClasses = {
            connecting: 'bg-yellow-500',
            connected: 'bg-green-500',
            error: 'bg-red-500',
            disconnected: 'bg-gray-500'
        };

        // Remove all status classes
        Object.values(statusClasses).forEach(cls => {
            this.connectionStatus.classList.remove(cls);
        });

        // Add the current status class
        this.connectionStatus.classList.add(statusClasses[status] || statusClasses.disconnected);

        // Update title attribute for tooltip
        const statusTexts = {
            connecting: 'Connecting to live updates...',
            connected: 'Connected - Live updates active',
            error: 'Connection error - Updates may be delayed',
            disconnected: 'Disconnected from live updates'
        };

        this.connectionStatus.title = statusTexts[status] || 'Unknown status';
    }

    /**
     * Handle pending registration created events
     */
    handlePendingRegistrationCreated(event) {
        const { pendingRegistration } = event;

        // Add to the beginning of the table
        this.addPendingRegistration(pendingRegistration);

        // Show browser notification
        this.showBrowserNotification(
            'New Pending Registration',
            `${pendingRegistration.first_name} ${pendingRegistration.last_name} (${pendingRegistration.user_type})`,
            pendingRegistration.id
        );

        // Update statistics
        this.updateStatistics();
    }

    /**
     * Handle pending registration updated events (approved, rejected, deleted)
     */
    handlePendingRegistrationUpdated(event) {
        const { pendingRegistration, action, editor } = event;

        switch (action) {
            case 'approved':
                // Remove from table (approved registrations are deleted)
                this.removePendingRegistration(pendingRegistration.id);
                this.showBrowserNotification(
                    'Registration Approved',
                    `${pendingRegistration.first_name} ${pendingRegistration.last_name} has been approved by ${editor}`,
                    null
                );
                break;

            case 'rejected':
                // Remove from table (rejected registrations are deleted)
                this.removePendingRegistration(pendingRegistration.id);
                this.showBrowserNotification(
                    'Registration Rejected',
                    `${pendingRegistration.first_name} ${pendingRegistration.last_name} has been rejected by ${editor}`,
                    null
                );
                break;

            case 'deleted':
                // Remove from table
                this.removePendingRegistration(pendingRegistration.id);
                break;

            default:
                // Update existing row if status changed
                this.updatePendingRegistration(pendingRegistration);
        }

        // Update statistics
        this.updateStatistics();
    }

    /**
     * Add a new pending registration row to the table
     */
    addPendingRegistration(registration) {
        // Check if already exists
        const existingRow = this.tableBody.querySelector(`tr[data-id="${registration.id}"]`);
        
        if (existingRow) {
            // Update existing row instead
            this.updatePendingRegistrationRow(existingRow, registration);
            return;
        }

        // Add to array
        this.pendingRegistrations.unshift(registration);

        // Create new row
        const newRow = this.createPendingRegistrationRow(registration);
        newRow.classList.add('animate-fade-in');

        // Remove empty row if exists
        const emptyRow = this.tableBody.querySelector('tr td[colspan]');
        if (emptyRow) {
            emptyRow.closest('tr').remove();
        }

        // Insert at the beginning
        this.tableBody.insertBefore(newRow, this.tableBody.firstChild);
    }

    /**
     * Create a pending registration table row
     */
    createPendingRegistrationRow(registration) {
        const row = document.createElement('tr');
        row.setAttribute('data-id', registration.id);
        row.className = 'border-b border-[#e3e3e0] dark:border-[#3E3E3A] hover:bg-gray-50 dark:hover:bg-[#161615]';

        const statusBadge = this.getStatusBadge(registration.status);
        const userTypeBadge = this.getUserTypeBadge(registration.user_type);
        
        // Generate avatar color
        const colors = ['#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#6366F1', '#8B5CF6', '#EC4899', '#14B8A6', '#F97316', '#06B6D4'];
        const firstLetter = (registration.first_name || 'U').charAt(0).toUpperCase();
        const hash = firstLetter.charCodeAt(0);
        const avatarColor = colors[hash % colors.length];

        row.innerHTML = `
            <td class="py-2 px-3">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3 text-white font-semibold text-xs flex-shrink-0" style="background-color: ${avatarColor}">
                        ${firstLetter}
                    </div>
                    <div>
                        <div class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${this.escapeHtml(registration.first_name)} ${this.escapeHtml(registration.last_name)}</div>
                        <div class="text-xs text-[#706f6c] dark:text-[#A1A09A]">${this.escapeHtml(registration.email)}</div>
                    </div>
                </div>
            </td>
            <td class="py-2 px-3">${userTypeBadge}</td>
            <td class="py-2 px-3">${statusBadge}</td>
            <td class="py-2 px-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">${this.formatDate(registration.created_at)}</td>
            <td class="py-2 px-3">
                <div class="flex items-center justify-center gap-2">
                    <button onclick="viewRegistration(${registration.id})" class="btn-view" title="View">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    </button>
                    ${registration.status === 'pending' ? `
                        <button onclick="openApproveModal(${registration.id})" class="inline-flex items-center justify-center w-8 h-8 rounded-sm bg-green-600 dark:bg-green-600 text-white hover:bg-green-700 dark:hover:bg-green-700 border border-green-600 dark:border-green-600 hover:border-green-700 dark:hover:border-green-700 transition-all" title="Approve">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        </button>
                        <button onclick="openRejectModal(${registration.id})" class="btn-delete" title="Reject">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                        </button>
                    ` : `
                        <button onclick="deleteRegistration(${registration.id})" class="btn-delete" title="Delete">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    `}
                </div>
            </td>
        `;

        return row;
    }

    /**
     * Remove pending registration from table
     */
    removePendingRegistration(registrationId) {
        const row = this.tableBody.querySelector(`tr[data-id="${registrationId}"]`);
        if (row) {
            row.classList.add('animate-fade-out');
            setTimeout(() => {
                row.remove();
                
                // Check if table is empty
                if (this.tableBody.children.length === 0) {
                    this.tableBody.innerHTML = `
                        <tr>
                            <td colspan="5" class="py-8 px-4 text-center text-[#706f6c] dark:text-[#A1A09A]">
                                No registrations found.
                            </td>
                        </tr>
                    `;
                }
            }, 300);
        }

        // Remove from array
        this.pendingRegistrations = this.pendingRegistrations.filter(
            r => r.id !== registrationId
        );
    }

    /**
     * Update existing pending registration
     */
    updatePendingRegistration(registration) {
        const row = this.tableBody.querySelector(`tr[data-id="${registration.id}"]`);
        if (row) {
            this.updatePendingRegistrationRow(row, registration);
        }
    }

    /**
     * Update existing pending registration row
     */
    updatePendingRegistrationRow(row, registration) {
        const statusBadge = this.getStatusBadge(registration.status);
        const userTypeBadge = this.getUserTypeBadge(registration.user_type);

        const cells = row.querySelectorAll('td');
        if (cells.length >= 4) {
            cells[1].innerHTML = userTypeBadge;
            cells[2].innerHTML = statusBadge;
        }
    }

    /**
     * Get status badge HTML
     */
    getStatusBadge(status) {
        const badges = {
            pending: '<span class="px-2 py-1 text-xs font-medium rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200">Pending</span>',
            approved: '<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Approved</span>',
            rejected: '<span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Rejected</span>'
        };
        return badges[status] || badges.pending;
    }

    /**
     * Get user type badge HTML
     */
    getUserTypeBadge(userType) {
        const badges = {
            student: '<span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">Student</span>',
            staff: '<span class="px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">Staff</span>',
            stakeholder: '<span class="px-2 py-1 text-xs font-medium rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">Stakeholder</span>',
            security: '<span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Security</span>',
            reporter: '<span class="px-2 py-1 text-xs font-medium rounded-full bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-200">Reporter</span>'
        };
        return badges[userType] || '<span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">' + this.escapeHtml(userType) + '</span>';
    }

    /**
     * Format date for display (matches Laravel format: M d, Y)
     */
    formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return `${months[date.getMonth()]} ${date.getDate()}, ${date.getFullYear()}`;
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
     * Show browser notification
     */
    showBrowserNotification(title, message, registrationId) {
        // Check if browser supports notifications
        if (!('Notification' in window)) {
            console.log('This browser does not support desktop notification');
            return;
        }

        // Request permission if not granted
        if (Notification.permission === 'default') {
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    this.createNotification(title, message, registrationId);
                }
            });
        } else if (Notification.permission === 'granted') {
            this.createNotification(title, message, registrationId);
        }
    }

    /**
     * Create and show notification
     */
    createNotification(title, message, registrationId) {
        const notification = new Notification(title, {
            body: message,
            icon: '/favicon.ico',
            tag: `pending-registration-${registrationId}`,
            requireInteraction: false,
        });

        notification.onclick = function() {
            window.focus();
            // Navigate to the registration if possible
            if (typeof viewRegistration === 'function') {
                viewRegistration(registrationId);
            }
            notification.close();
        };

        // Auto close after 5 seconds
        setTimeout(() => {
            notification.close();
        }, 5000);
    }

    /**
     * Update statistics cards
     */
    updateStatistics() {
        // Count pending registrations
        const pendingCount = this.pendingRegistrations.filter(r => r.status === 'pending').length;
        
        // Update pending count in statistics card
        const pendingStat = document.querySelector('[data-stat="pending"]');
        if (pendingStat) {
            const countElement = pendingStat.querySelector('.text-2xl');
            if (countElement) {
                countElement.textContent = pendingCount;
            }
        }

        // Update total count
        const totalStat = document.querySelector('[data-stat="total"]');
        if (totalStat) {
            const countElement = totalStat.querySelector('.text-2xl');
            if (countElement) {
                countElement.textContent = this.pendingRegistrations.length;
            }
        }
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof window.pendingRegistrationsData !== 'undefined') {
            window.pendingRegistrationsRealtime = new PendingRegistrationsRealtime();
            window.pendingRegistrationsRealtime.init(window.pendingRegistrationsData);
        }
    });
} else {
    if (typeof window.pendingRegistrationsData !== 'undefined') {
        window.pendingRegistrationsRealtime = new PendingRegistrationsRealtime();
        window.pendingRegistrationsRealtime.init(window.pendingRegistrationsData);
    }
}

