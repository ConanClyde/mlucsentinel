/**
 * Real-time College Updates Module
 * 
 * This module handles real-time updates for the colleges table
 * using Laravel Echo and Reverb WebSocket server.
 */

class CollegesRealtime {
    constructor() {
        this.connectionStatus = null;
        this.tableBody = null;
        this.colleges = [];
        this.channel = null;
        this.retryCount = 0;
        this.maxRetries = 5;
    }

    /**
     * Initialize the real-time connection
     */
    init(colleges = []) {
        this.colleges = colleges;
        this.connectionStatus = document.getElementById('connectionStatus');
        this.tableBody = document.getElementById('college-table-body');

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

        console.log('Setting up Echo connection for colleges...');
        this.updateConnectionStatus('connecting');

        try {
            // Subscribe to the colleges channel
            this.channel = window.Echo.channel('colleges');

            // Listen for college.updated events
            this.channel.listen('.college.updated', (event) => {
                console.log('Received college broadcast event:', event);
                this.handleCollegeUpdate(event);
            });

            // Handle connection success
            this.channel.subscribed(() => {
                console.log('Successfully subscribed to colleges channel');
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
     * Handle college update events
     */
    handleCollegeUpdate(event) {
        const { college, action, editor } = event;

        switch (action) {
            case 'created':
                this.addCollege(college);
                this.showBrowserNotification(
                    'College Created',
                    `${editor} added ${college.name || 'a college'}`,
                    college.id,
                    'created'
                );
                break;
            case 'updated':
                this.updateCollege(college);
                this.showBrowserNotification(
                    'College Updated',
                    `${editor} updated ${college.name || 'a college'}`,
                    college.id,
                    'updated'
                );
                break;
            case 'deleted':
                this.removeCollege(college);
                this.showBrowserNotification(
                    'College Removed',
                    `${editor} removed ${college.name || 'a college'}`,
                    null,
                    'deleted'
                );
                break;
            default:
                console.warn('Unknown action:', action);
        }
    }

    /**
     * Add a new college row to the table
     */
    addCollege(college) {
        // Check if college already exists
        const existingRow = this.tableBody.querySelector(`tr[data-id="${college.id}"], tr[data-college-id="${college.id}"]`);
        
        if (existingRow) {
            this.updateCollege(college);
            return;
        }

        // Add to local array
        this.colleges.unshift(college);

        // Create and insert the new row
        const newRow = this.createCollegeRow(college);
        
        // Add animation class
        newRow.classList.add('animate-fade-in');
        
        // Remove "No colleges found" row if it exists
        const emptyRow = this.tableBody.querySelector('tr td[colspan]');
        if (emptyRow) {
            emptyRow.closest('tr').remove();
        }
        
        // Insert at the beginning
        this.tableBody.insertBefore(newRow, this.tableBody.firstChild);

        // Remove animation class after animation completes
        setTimeout(() => {
            newRow.classList.remove('animate-fade-in');
        }, 500);
    }

    /**
     * Update an existing college row
     */
    updateCollege(college) {
        const existingRow = this.tableBody.querySelector(`tr[data-id="${college.id}"], tr[data-college-id="${college.id}"]`);
        
        if (!existingRow) {
            console.log('College not found in table, adding instead');
            this.addCollege(college);
            return;
        }

        // Update local array
        const index = this.colleges.findIndex(c => c.id === college.id);
        if (index !== -1) {
            this.colleges[index] = college;
        }

        // Create new row and replace the old one
        const newRow = this.createCollegeRow(college);
        
        // Add highlight animation
        newRow.classList.add('animate-highlight');
        
        existingRow.parentNode.replaceChild(newRow, existingRow);

        // Remove animation class after animation completes
        setTimeout(() => {
            newRow.classList.remove('animate-highlight');
        }, 1000);
    }

    /**
     * Remove a college row from the table
     */
    removeCollege(college) {
        const rowToRemove = this.tableBody.querySelector(`tr[data-id="${college.id}"], tr[data-college-id="${college.id}"]`);
        
        if (!rowToRemove) {
            console.log('College not found in table');
            return;
        }

        // Update local array
        this.colleges = this.colleges.filter(c => c.id !== college.id);

        // Add fade-out animation
        rowToRemove.classList.add('animate-fade-out');

        // Remove after animation
        setTimeout(() => {
            rowToRemove.remove();

            // Check if table is empty
            if (this.tableBody.children.length === 0) {
                this.showEmptyState();
            }
        }, 300);
    }

    /**
     * Create a college table row
     */
    createCollegeRow(college) {
        const row = document.createElement('tr');
        row.className = 'hover:bg-gray-50 dark:hover:bg-[#161615]';
        row.setAttribute('data-id', college.id);
        row.setAttribute('data-college-id', college.id);
        row.dataset.collegeCode = college.code || '';
        row.dataset.collegeName = college.name || '';
        row.dataset.collegeType = college.type || 'college';
        row.dataset.collegeDescription = college.description || '';
        row.dataset.collegeCreatedAt = college.created_at || '';

        const createdDate = college.created_at ? new Date(college.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '';
        const description = college.description ? this.escapeHtml(college.description) : '—';
        const typeLabel = this.formatType(college.type) || 'College';

        row.innerHTML = `
            <td class="px-4 py-3 text-sm text-[#1b1b18] dark:text-[#EDEDEC]">${this.escapeHtml(college.code || '')}</td>
            <td class="px-4 py-3 text-sm text-[#1b1b18] dark:text-[#EDEDEC]">${this.escapeHtml(college.name || '')}</td>
            <td class="px-4 py-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">${typeLabel}</td>
            <td class="px-4 py-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">${description}</td>
            <td class="px-4 py-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">${createdDate}</td>
            <td class="px-4 py-3">
                <div class="flex items-center justify-center gap-2">
                    <button onclick="editCollege(${college.id})" class="btn-edit" title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </button>
                    <button onclick="deleteCollege(${college.id})" class="btn-delete" title="Delete">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            </td>
        `;

        return row;
    }

    /**
     * Show empty state when no colleges exist
     */
    showEmptyState() {
        const emptyRow = document.createElement('tr');
        emptyRow.innerHTML = `
            <td colspan="6" class="px-4 py-8 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">
                No colleges found. Click Add button to create one.
            </td>
        `;
        this.tableBody.appendChild(emptyRow);
    }

    /**
     * Show browser/system notification
     */
    showBrowserNotification(title, message, collegeId = null, action = null) {
        // Check if browser notifications are enabled
        const notificationsEnabled = localStorage.getItem('browserNotifications') === 'true';
        if (!notificationsEnabled) {
            return;
        }

        // Request permission if not granted
        if (Notification.permission === 'default') {
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    this.displayNotification(title, message, collegeId, action);
                }
            });
        } else if (Notification.permission === 'granted') {
            this.displayNotification(title, message, collegeId, action);
        }
    }

    /**
     * Display the browser notification
     */
    displayNotification(title, message, collegeId = null, action = null) {
        const notification = new Notification(title, {
            body: message,
            icon: '/favicon.ico',
            badge: '/favicon.ico',
            tag: 'college-update',
            requireInteraction: false,
        });

        // Auto close after 5 seconds
        setTimeout(() => notification.close(), 5000);

        // Handle click event
        notification.onclick = function() {
            window.focus();
            notification.close();
            // Navigate to settings page college tab
            window.location.href = '/settings#college';
        };
    }

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text ?? '';
        return div.innerHTML;
    }

    formatType(type) {
        if (!type) return '';

        return type
            .toString()
            .toLowerCase()
            .replace(/\b\w/g, char => char.toUpperCase());
    }

    /**
     * Disconnect from the channel
     */
    disconnect() {
        if (this.channel) {
            window.Echo.leave('colleges');
            this.channel = null;
            this.updateConnectionStatus('disconnected');
            console.log('Disconnected from colleges channel');
        }
    }
}

// Export for use in blade templates
window.CollegesRealtime = CollegesRealtime;

