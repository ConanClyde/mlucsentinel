/**
 * Real-time Reporter Updates Module
 * 
 * This module handles real-time updates for the reporters table
 * using Laravel Echo and Reverb WebSocket server.
 */

class ReportersRealtime {
    constructor() {
        this.connectionStatus = null;
        this.tableBody = null;
        this.reporters = [];
        this.channel = null;
        this.retryCount = 0;
        this.maxRetries = 5;
    }

    /**
     * Initialize the real-time connection
     */
    init(reporters = []) {
        this.reporters = reporters;
        this.connectionStatus = document.getElementById('connectionStatus');
        this.tableBody = document.getElementById('reportersTableBody');

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

        console.log('Setting up Echo connection...');
        this.updateConnectionStatus('connecting');

        try {
            // Subscribe to the reporters channel
            this.channel = window.Echo.channel('reporters');

            // Listen for reporter.updated events
            this.channel.listen('.reporter.updated', (event) => {
                console.log('Received broadcast event:', event);
                this.handleReporterUpdate(event);
            });

            // Handle connection success
            this.channel.subscribed(() => {
                console.log('Successfully subscribed to reporters channel');
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
     * Handle reporter update events
     */
    handleReporterUpdate(event) {
        const { reporter, action, editor } = event;

        switch (action) {
            case 'created':
                this.addReporter(reporter);
                break;
            case 'updated':
                this.updateReporter(reporter);
                // Only show notification if this update wasn't made by current user and not from profile page
                if (editor && editor !== 'self' && !this.isCurrentUserAction(reporter.id)) {
                    this.showBrowserNotification(
                        'Reporter Updated',
                        `${editor} updated Reporter ${reporter.user.first_name} ${reporter.user.last_name}`,
                        reporter.id,
                        'updated'
                    );
                }
                break;
            case 'deleted':
                this.removeReporter(reporter);
                if (editor && !this.isCurrentUserAction(reporter.id)) {
                    this.showBrowserNotification(
                        'Reporter Removed',
                        `${editor} removed ${reporter.user.first_name} ${reporter.user.last_name}`,
                        null,
                        'deleted'
                    );
                }
                break;
            default:
                console.warn('Unknown action:', action);
        }
    }

    /**
     * Check if the current user just performed this action
     */
    isCurrentUserAction(adminId) {
        // Store recently modified IDs with timestamp
        if (!window._recentActions) {
            window._recentActions = new Map();
        }
        
        const now = Date.now();
        const recent = window._recentActions.get(adminId);
        
        // If action was within last 2 seconds, it's probably from this browser
        if (recent && (now - recent) < 2000) {
            window._recentActions.delete(adminId);
            return true;
        }
        
        return false;
    }

    /**
     * Mark an action as performed by current user
     */
    markUserAction(adminId) {
        if (!window._recentActions) {
            window._recentActions = new Map();
        }
        window._recentActions.set(adminId, Date.now());
    }

    /**
     * Add a new reporter to the table
     */
    addReporter(reporter) {
        // Check if reporter already exists
        const existingRow = this.tableBody.querySelector(`tr[data-id="${reporter.id}"]`);
        
        if (existingRow) {
            this.updateReporter(reporter);
            return;
        }

        // Add to local array
        this.reporters.unshift(reporter);

        // Create and insert the new row
        const newRow = this.createReporterRow(reporter);
        
        // Add animation class
        newRow.classList.add('animate-fade-in');
        
        // Remove "No reporters found" row if it exists
        const emptyRow = this.tableBody.querySelector('tr td[colspan]');
        if (emptyRow) {
            emptyRow.closest('tr').remove();
        }
        
        // Always insert at the very beginning
        this.tableBody.insertBefore(newRow, this.tableBody.firstChild);

        // Remove animation class after animation completes
        setTimeout(() => {
            newRow.classList.remove('animate-fade-in');
        }, 500);
    }

    /**
     * Update an existing reporter in the table
     */
    updateReporter(reporter) {
        const existingRow = this.tableBody.querySelector(`tr[data-id="${reporter.id}"]`);
        
        if (!existingRow) {
            console.log('Reporter not found in table, adding instead');
            this.addReporter(reporter);
            return;
        }

        // Update local array
        const index = this.reporters.findIndex(a => a.id === reporter.id);
        if (index !== -1) {
            this.reporters[index] = reporter;
        }

        // Create new row and replace the old one
        const newRow = this.createReporterRow(reporter);
        
        // Add highlight animation
        newRow.classList.add('animate-highlight');
        
        existingRow.parentNode.replaceChild(newRow, existingRow);

        // Remove animation class after animation completes
        setTimeout(() => {
            newRow.classList.remove('animate-highlight');
        }, 300);
    }

    /**
     * Remove a reporter from the table
     */
    removeReporter(reporter) {
        const existingRow = this.tableBody.querySelector(`tr[data-id="${reporter.id}"]`);
        
        if (!existingRow) {
            console.log('Reporter not found in table for removal');
            return;
        }

        // Remove from local array
        const index = this.reporters.findIndex(a => a.id === reporter.id);
        if (index !== -1) {
            this.reporters.splice(index, 1);
        }

        // Add fade-out animation
        existingRow.classList.add('animate-fade-out');
        
        // Remove the row after animation completes
        setTimeout(() => {
            existingRow.remove();
            
            // Show empty state if no reporters left
            if (this.reporters.length === 0) {
                this.showEmptyState();
            }
        }, 300);
    }

    /**
     * Create a reporter table row
     */
    createReporterRow(reporter) {
        const row = document.createElement('tr');
        row.className = 'border-b border-[#e3e3e0] dark:border-[#3E3E3A] hover:bg-gray-50 dark:hover:bg-[#161615] transition-colors';
        row.setAttribute('data-id', reporter.id);

        const statusClass = reporter.user.is_active
            ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200'
            : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200';

        const statusText = reporter.user.is_active ? 'Active' : 'Inactive';
        const roleName = reporter.reporter_role ? reporter.reporter_role.name : 'No Role';
        const createdDate = new Date(reporter.created_at).toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });

        const initials = `${reporter.user.first_name.charAt(0)}`.toUpperCase();
        const avatarColor = this.getAvatarColor(reporter.user.first_name + reporter.user.last_name);

        row.innerHTML = `
            <td class="py-2 px-3">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3 text-white font-semibold text-xs" style="background-color: ${avatarColor}">
                        ${initials}
                    </div>
                    <div>
                        <div class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${this.escapeHtml(reporter.user.first_name)} ${this.escapeHtml(reporter.user.last_name)}</div>
                    </div>
                </div>
            </td>
            <td class="py-2 px-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">${this.escapeHtml(reporter.user.email)}</td>
            <td class="py-2 px-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">${this.escapeHtml(roleName)}</td>
            <td class="py-2 px-3">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClass}">
                    ${statusText}
                </span>
            </td>
            <td class="py-2 px-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">${createdDate}</td>
            <td class="py-2 px-3">
                <div class="flex items-center justify-center gap-2">
                    <button onclick="viewReporter(${reporter.id})" class="btn-view" title="View">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <button onclick="openEditModal(${reporter.id})" class="btn-edit" title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </button>
                    <button onclick="deleteReporter(${reporter.id})" class="btn-delete" title="Delete">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </td>
        `;

        return row;
    }

    /**
     * Show empty state when no reporters exist
     */
    showEmptyState() {
        const emptyRow = document.createElement('tr');
        emptyRow.innerHTML = `
            <td colspan="6" class="py-8 px-4 text-center text-[#706f6c] dark:text-[#A1A09A]">
                No reporters found.
            </td>
        `;
        this.tableBody.appendChild(emptyRow);
    }

    /**
     * Show browser/system notification
     */
    showBrowserNotification(title, message, reporterId = null, action = null) {
        // Request permission if not granted
        if (Notification.permission === 'default') {
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    this.displayNotification(title, message, reporterId, action);
                }
            });
        } else if (Notification.permission === 'granted') {
            this.displayNotification(title, message, reporterId, action);
        }
    }

    /**
     * Display the browser notification
     */
    displayNotification(title, message, reporterId = null, action = null) {
        const notification = new Notification(title, {
            body: message,
            icon: '/favicon.ico',
            badge: '/favicon.ico',
            tag: 'reporter-update',
            requireInteraction: false,
        });

        // Auto close after 5 seconds
        setTimeout(() => notification.close(), 5000);

        // Handle click event - navigate to page
        notification.onclick = function() {
            window.focus();
            notification.close();
            
            // Navigate to reporters page
            if (reporterId && action === 'updated') {
                window.location.href = `/users/reporters?view=${reporterId}`;
            } else {
                window.location.href = '/users/reporters';
            }
        };
    }

    /**
     * Generate consistent avatar color based on name
     */
    getAvatarColor(name) {
        const colors = [
            '#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#6366F1', 
            '#8B5CF6', '#EC4899', '#14B8A6', '#F97316', '#06B6D4'
        ];
        
        // Use only the first letter for consistent color
        const firstLetter = name.charAt(0).toUpperCase();
        const hash = firstLetter.charCodeAt(0);
        
        return colors[hash % colors.length];
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
     * Disconnect from the channel
     */
    disconnect() {
        if (this.channel) {
            window.Echo.leave('reporters');
            this.channel = null;
            this.updateConnectionStatus('disconnected');
            console.log('Disconnected from reporters channel');
        }
    }
}

// Export for use in blade templates
window.ReportersRealtime = ReportersRealtime;
