/**
 * Real-time Program Updates Module
 * 
 * This module handles real-time updates for the programs table
 * using Laravel Echo and Reverb WebSocket server.
 */

class ProgramsRealtime {
    constructor() {
        this.connectionStatus = null;
        this.tableBody = null;
        this.programs = [];
        this.channel = null;
        this.retryCount = 0;
        this.maxRetries = 5;
    }

    /**
     * Initialize the real-time connection
     */
    init(programs = []) {
        this.programs = programs;
        this.connectionStatus = document.getElementById('program-connection-status');
        this.tableBody = document.getElementById('program-table-body');

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

        console.log('Setting up Echo connection for programs...');
        this.updateConnectionStatus('connecting');

        try {
            // Subscribe to the programs channel
            this.channel = window.Echo.channel('programs');

            // Listen for program.updated events
            this.channel.listen('.program.updated', (event) => {
                console.log('Received program broadcast event:', event);
                this.handleProgramUpdate(event);
            });

            // Handle connection success
            this.channel.subscribed(() => {
                console.log('Successfully subscribed to programs channel');
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
     * Handle program update events
     */
    handleProgramUpdate(event) {
        const { program, action, editor } = event;

        switch (action) {
            case 'created':
                this.addProgram(program);
                this.showBrowserNotification(
                    'Program Created',
                    `${editor} added ${program.name || 'a program'}`,
                    program.id,
                    'created'
                );
                break;
            case 'updated':
                this.updateProgram(program);
                this.showBrowserNotification(
                    'Program Updated',
                    `${editor} updated ${program.name || 'a program'}`,
                    program.id,
                    'updated'
                );
                break;
            case 'deleted':
                this.removeProgram(program);
                this.showBrowserNotification(
                    'Program Removed',
                    `${editor} removed ${program.name || 'a program'}`,
                    null,
                    'deleted'
                );
                break;
            default:
                console.warn('Unknown action:', action);
        }
    }

    /**
     * Add a new program row to the table
     */
    addProgram(program) {
        // Check if program already exists
        const existingRow = this.tableBody.querySelector(`tr[data-id="${program.id}"]`);
        
        if (existingRow) {
            this.updateProgram(program);
            return;
        }

        // Add to local array
        this.programs.unshift(program);

        // Create and insert the new row
        const newRow = this.createProgramRow(program);
        
        // Add animation class
        newRow.classList.add('animate-fade-in');
        
        // Remove "No programs found" row if it exists
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
     * Update an existing program row
     */
    updateProgram(program) {
        const existingRow = this.tableBody.querySelector(`tr[data-id="${program.id}"], tr[data-program-id="${program.id}"]`);
        
        if (!existingRow) {
            console.log('Program not found in table, adding instead');
            this.addProgram(program);
            return;
        }

        // Update local array
        const index = this.programs.findIndex(p => p.id === program.id);
        if (index !== -1) {
            this.programs[index] = program;
        }

        // Create new row and replace the old one
        const newRow = this.createProgramRow(program);
        
        // Add highlight animation
        newRow.classList.add('animate-highlight');
        
        existingRow.parentNode.replaceChild(newRow, existingRow);

        // Remove animation class after animation completes
        setTimeout(() => {
            newRow.classList.remove('animate-highlight');
        }, 1000);
    }

    /**
     * Remove a program row from the table
     */
    removeProgram(program) {
        const rowToRemove = this.tableBody.querySelector(`tr[data-id="${program.id}"], tr[data-program-id="${program.id}"]`);
        
        if (!rowToRemove) {
            console.log('Program not found in table');
            return;
        }

        // Update local array
        this.programs = this.programs.filter(p => p.id !== program.id);

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
     * Create a program table row
     */
    createProgramRow(program) {
        const row = document.createElement('tr');
        row.className = 'hover:bg-gray-50 dark:hover:bg-[#161615]';
        row.setAttribute('data-id', program.id);
        row.setAttribute('data-program-id', program.id);
        row.dataset.programCode = program.code || '';
        row.dataset.programName = program.name || '';
        row.dataset.programDescription = program.description || '';
        row.dataset.programCollegeId = program.college_id ?? (program.college ? program.college.id : '');
        row.dataset.programCollegeName = program.college ? program.college.name : '';
        row.dataset.programCreatedAt = program.created_at || '';

        const createdDate = program.created_at ? new Date(program.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '';
        const collegeName = program.college ? this.escapeHtml(program.college.name) : 'N/A';
        const description = program.description ? this.escapeHtml(program.description) : '—';

        row.innerHTML = `
            <td class="px-4 py-3 text-sm text-[#1b1b18] dark:text-[#EDEDEC]">${this.escapeHtml(program.code || '')}</td>
            <td class="px-4 py-3 text-sm text-[#1b1b18] dark:text-[#EDEDEC]">${this.escapeHtml(program.name || '')}</td>
            <td class="px-4 py-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">${collegeName}</td>
            <td class="px-4 py-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">${description}</td>
            <td class="px-4 py-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">${createdDate}</td>
            <td class="px-4 py-3">
                <div class="flex items-center justify-center gap-2">
                    <button onclick="editProgram(${program.id})" class="btn-edit" title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </button>
                    <button onclick="deleteProgram(${program.id})" class="btn-delete" title="Delete">
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
     * Show empty state when no programs exist
     */
    showEmptyState() {
        const emptyRow = document.createElement('tr');
        emptyRow.innerHTML = `
            <td colspan="6" class="px-4 py-8 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">
                No programs found. Click Add button to create one.
            </td>
        `;
        this.tableBody.appendChild(emptyRow);
    }

    /**
     * Show browser/system notification
     */
    showBrowserNotification(title, message, programId = null, action = null) {
        // Check if browser notifications are enabled
        const notificationsEnabled = localStorage.getItem('browserNotifications') === 'true';
        if (!notificationsEnabled) {
            return;
        }

        // Request permission if not granted
        if (Notification.permission === 'default') {
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    this.displayNotification(title, message, programId, action);
                }
            });
        } else if (Notification.permission === 'granted') {
            this.displayNotification(title, message, programId, action);
        }
    }

    /**
     * Display the browser notification
     */
    displayNotification(title, message, programId = null, action = null) {
        const notification = new Notification(title, {
            body: message,
            icon: '/favicon.ico',
            badge: '/favicon.ico',
            tag: 'program-update',
            requireInteraction: false,
        });

        // Auto close after 5 seconds
        setTimeout(() => notification.close(), 5000);

        // Handle click event
        notification.onclick = function() {
            window.focus();
            notification.close();
            // Navigate to settings page program tab
            window.location.href = '/settings#program';
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

    /**
     * Disconnect from the channel
     */
    disconnect() {
        if (this.channel) {
            window.Echo.leave('programs');
            this.channel = null;
            this.updateConnectionStatus('disconnected');
            console.log('Disconnected from programs channel');
        }
    }
}

// Export for use in blade templates
window.ProgramsRealtime = ProgramsRealtime;

