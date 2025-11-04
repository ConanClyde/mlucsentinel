/**
 * Real-time Patrol Logs Updates Module
 * 
 * This module handles real-time updates for patrol check-ins
 * using Laravel Echo and Reverb WebSocket server.
 */

class PatrolLogsRealtime {
    constructor() {
        this.connectionStatus = null;
        this.tableBody = null;
        this.channel = null;
        this.retryCount = 0;
        this.maxRetries = 5;
    }

    /**
     * Initialize the real-time connection
     */
    init() {
        this.connectionStatus = document.getElementById('patrol-connectionStatus');
        this.tableBody = document.getElementById('patrol-logs-table-body');

        if (!this.connectionStatus || !this.tableBody) {
            console.error('Required DOM elements not found for patrol logs real-time');
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
                console.log(`Echo not ready for patrol logs, retrying... (${this.retryCount}/${this.maxRetries})`);
                setTimeout(() => this.setupEcho(), 500);
            } else {
                console.error('Echo failed to initialize after maximum retries');
                this.updateConnectionStatus('error');
            }
            return;
        }

        console.log('Setting up Echo connection for patrol logs...');
        this.updateConnectionStatus('connecting');

        try {
            // Subscribe to the patrol-logs channel
            this.channel = window.Echo.channel('patrol-logs');

            // Listen for patrol-log.created events
            this.channel.listen('.patrol-log.created', (event) => {
                console.log('Received patrol log broadcast event:', event);
                this.handlePatrolLogCreated(event);
            });

            // Handle connection success
            this.channel.subscribed(() => {
                console.log('Successfully subscribed to patrol-logs channel');
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
     * Handle patrol log created events
     */
    handlePatrolLogCreated(event) {
        const { patrolLog } = event;

        this.addPatrolLog(patrolLog);
        this.showBrowserNotification(
            'New Patrol Check-In',
            `${patrolLog.security_user.first_name} ${patrolLog.security_user.last_name} checked in at ${patrolLog.map_location.name} (${patrolLog.map_location.short_code})`,
            patrolLog.id
        );
    }

    /**
     * Add a new patrol log to the table
     */
    addPatrolLog(patrolLog) {
        if (!this.tableBody) return;

        // Check if empty state exists and remove it
        const emptyState = this.tableBody.querySelector('td[colspan]');
        if (emptyState) {
            emptyState.closest('tr').remove();
        }

        // Create new row
        const row = this.createPatrolLogRow(patrolLog);

        // Add animation class
        row.classList.add('animate-fade-in');
        
        // Insert at the beginning
        this.tableBody.insertBefore(row, this.tableBody.firstChild);

        // Remove animation class after animation completes
        setTimeout(() => {
            row.classList.remove('animate-fade-in');
        }, 500);
    }

    /**
     * Create a patrol log table row
     */
    createPatrolLogRow(patrolLog) {
        const row = document.createElement('tr');
        row.className = 'hover:bg-gray-50 dark:hover:bg-[#2a2a2a] transition-colors border-b border-[#e3e3e0] dark:border-[#3E3E3A]';
        row.setAttribute('data-patrol-log-id', patrolLog.id);

        // Format date
        const date = new Date(patrolLog.checked_in_at);
        const formattedDate = date.toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });

        row.innerHTML = `
            <td class="py-2 px-3 text-sm text-[#1b1b18] dark:text-[#EDEDEC]">${this.escapeHtml(formattedDate)}</td>
            <td class="py-2 px-3 text-sm text-[#1b1b18] dark:text-[#EDEDEC]">${this.escapeHtml(patrolLog.security_user.first_name)} ${this.escapeHtml(patrolLog.security_user.last_name)}</td>
            <td class="py-2 px-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">${this.escapeHtml(patrolLog.security_user.email)}</td>
            <td class="py-2 px-3 text-sm text-[#1b1b18] dark:text-[#EDEDEC]">${this.escapeHtml(patrolLog.map_location.name)}</td>
            <td class="py-2 px-3 text-sm">
                <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded text-xs font-medium">
                    ${this.escapeHtml(patrolLog.map_location.short_code)}
                </span>
            </td>
            <td class="py-2 px-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                ${patrolLog.notes ? this.escapeHtml(patrolLog.notes) : '<span class="text-gray-400 dark:text-gray-600 italic">No notes</span>'}
            </td>
        `;

        return row;
    }

    /**
     * Show browser notification
     */
    showBrowserNotification(title, message, patrolLogId) {
        // Check if user has enabled browser notifications
        const notificationsEnabled = localStorage.getItem('browserNotificationsEnabled') === 'true';
        
        if (!notificationsEnabled || Notification.permission !== 'granted') {
            // Fallback to in-page notification
            this.displayNotification(title, message);
            return;
        }

        // Create browser notification
        const notification = new Notification(title, {
            body: message,
            icon: '/images/logo.png',
            badge: '/images/logo.png',
            tag: `patrol-log-${patrolLogId}-${Date.now()}`,
        });

        // Auto-close notification after 5 seconds
        setTimeout(() => notification.close(), 5000);

        // Also show in-page notification
        this.displayNotification(title, message);
    }

    /**
     * Display in-page notification
     */
    displayNotification(title, message) {
        if (typeof showNotification === 'function') {
            showNotification(`🚨 ${message}`, 'success');
        }
    }

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        if (text === null || text === undefined) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }

    /**
     * Disconnect from the channel
     */
    disconnect() {
        if (this.channel) {
            window.Echo.leave('patrol-logs');
            this.channel = null;
            this.updateConnectionStatus('disconnected');
            console.log('Disconnected from patrol-logs channel');
        }
    }
}

// Initialize and make available globally
if (typeof window !== 'undefined') {
    window.PatrolLogsRealtime = PatrolLogsRealtime;
}

