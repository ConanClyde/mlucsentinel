/**
 * Real-time Location Type Updates Module
 * 
 * This module handles real-time updates for the location types table
 * using Laravel Echo and Reverb WebSocket server.
 */

class LocationTypesRealtime {
    constructor() {
        this.connectionStatus = null;
        this.tableBody = null;
        this.locationTypes = [];
        this.channel = null;
        this.retryCount = 0;
        this.maxRetries = 5;
    }

    /**
     * Initialize the real-time connection
     */
    init(locationTypes = []) {
        this.locationTypes = locationTypes;
        this.connectionStatus = document.getElementById('location-types-connection-status');
        this.tableBody = document.getElementById('location-type-table-body');

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

        console.log('Setting up Echo connection for location types...');
        this.updateConnectionStatus('connecting');

        try {
            // Subscribe to the map-location-types channel
            this.channel = window.Echo.channel('map-location-types');

            // Listen for map-location-type.updated events
            this.channel.listen('.map-location-type.updated', (event) => {
                console.log('Received location type broadcast event:', event);
                this.handleLocationTypeUpdate(event);
            });

            // Handle connection success
            this.channel.subscribed(() => {
                console.log('Successfully subscribed to map-location-types channel');
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
     * Handle location type update events
     */
    handleLocationTypeUpdate(event) {
        const { locationType, action, editor } = event;

        switch (action) {
            case 'created':
                this.addLocationType(locationType);
                this.showBrowserNotification(
                    'Location Type Created',
                    `${editor} added ${locationType.name || 'a location type'}`,
                    locationType.id,
                    'created'
                );
                break;
            case 'updated':
                this.updateLocationType(locationType);
                this.showBrowserNotification(
                    'Location Type Updated',
                    `${editor} updated ${locationType.name || 'a location type'}`,
                    locationType.id,
                    'updated'
                );
                break;
            case 'deleted':
                this.removeLocationType(locationType);
                this.showBrowserNotification(
                    'Location Type Removed',
                    `${editor} removed ${locationType.name || 'a location type'}`,
                    null,
                    'deleted'
                );
                break;
            default:
                console.warn('Unknown action:', action);
        }
    }

    /**
     * Add a new location type row to the table
     */
    addLocationType(locationType) {
        // Check if location type already exists
        const existingRow = this.tableBody.querySelector(`tr[data-id="${locationType.id}"], tr[data-location-type-id="${locationType.id}"]`);
        
        if (existingRow) {
            this.updateLocationType(locationType);
            return;
        }

        // Add to local array
        this.locationTypes.unshift(locationType);

        // Create and insert the new row
        const newRow = this.createLocationTypeRow(locationType);
        
        // Add animation class
        newRow.classList.add('animate-fade-in');
        
        // Remove "No location types found" row if it exists
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
     * Update an existing location type row
     */
    updateLocationType(locationType) {
        const existingRow = this.tableBody.querySelector(`tr[data-id="${locationType.id}"], tr[data-location-type-id="${locationType.id}"]`);
        
        if (!existingRow) {
            console.log('Location type not found in table, adding instead');
            this.addLocationType(locationType);
            return;
        }

        // Update local array
        const index = this.locationTypes.findIndex(lt => lt.id === locationType.id);
        if (index !== -1) {
            this.locationTypes[index] = locationType;
        }

        // Create new row and replace the old one
        const newRow = this.createLocationTypeRow(locationType);
        
        // Add highlight animation
        newRow.classList.add('animate-highlight');
        
        existingRow.parentNode.replaceChild(newRow, existingRow);

        // Remove animation class after animation completes
        setTimeout(() => {
            newRow.classList.remove('animate-highlight');
        }, 1000);
    }

    /**
     * Remove a location type row from the table
     */
    removeLocationType(locationType) {
        const rowToRemove = this.tableBody.querySelector(`tr[data-id="${locationType.id}"], tr[data-location-type-id="${locationType.id}"]`);
        
        if (!rowToRemove) {
            console.log('Location type not found in table');
            return;
        }

        // Update local array
        this.locationTypes = this.locationTypes.filter(lt => lt.id !== locationType.id);

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
     * Create a location type table row
     */
    createLocationTypeRow(locationType) {
        const row = document.createElement('tr');
        row.className = 'hover:bg-gray-50 dark:hover:bg-[#161615]';
        row.setAttribute('data-id', locationType.id);
        row.setAttribute('data-location-type-id', locationType.id);

        const createdDate = locationType.created_at ? new Date(locationType.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '';

        const locationTypeName = locationType.name || '';
        row.innerHTML = `
            <td class="px-4 py-3 text-sm text-[#1b1b18] dark:text-[#EDEDEC]">${this.escapeHtml(locationTypeName)}</td>
            <td class="px-4 py-3 text-sm">
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded" style="background-color: ${locationType.default_color || '#3B82F6'}"></div>
                    <span class="text-[#706f6c] dark:text-[#A1A09A]">${locationType.default_color || '#3B82F6'}</span>
                </div>
            </td>
            <td class="px-4 py-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">${createdDate}</td>
            <td class="px-4 py-3">
                <div class="flex items-center justify-center gap-2">
                    <button onclick="editLocationType(${locationType.id}, '${locationTypeName.replace(/'/g, "\\'")}', '${(locationType.default_color || '#3B82F6').replace(/'/g, "\\'")}', '${(locationType.description || '').replace(/'/g, "\\'").replace(/\n/g, '\\n')}')" class="btn-edit" title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </button>
                    <button onclick="deleteLocationType(${locationType.id})" class="btn-delete" title="Delete">
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
     * Show empty state when no location types exist
     */
    showEmptyState() {
        const emptyRow = document.createElement('tr');
        emptyRow.innerHTML = `
            <td colspan="4" class="px-4 py-8 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">
                No location types found. Click Add button to create one.
            </td>
        `;
        this.tableBody.appendChild(emptyRow);
    }

    /**
     * Show browser/system notification
     */
    showBrowserNotification(title, message, locationTypeId = null, action = null) {
        // Check if browser notifications are enabled
        const notificationsEnabled = localStorage.getItem('browserNotifications') === 'true';
        if (!notificationsEnabled) {
            return;
        }

        // Request permission if not granted
        if (Notification.permission === 'default') {
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    this.displayNotification(title, message, locationTypeId, action);
                }
            });
        } else if (Notification.permission === 'granted') {
            this.displayNotification(title, message, locationTypeId, action);
        }
    }

    /**
     * Display the browser notification
     */
    displayNotification(title, message, locationTypeId = null, action = null) {
        const notification = new Notification(title, {
            body: message,
            icon: '/favicon.ico',
            badge: '/favicon.ico',
            tag: 'location-type-update',
            requireInteraction: false,
        });

        // Auto close after 5 seconds
        setTimeout(() => notification.close(), 5000);

        // Handle click event
        notification.onclick = function() {
            window.focus();
            notification.close();
            // Navigate to settings page location type tab
            window.location.href = '/settings#location-type';
        };
    }

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        if (text === null || text === undefined) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Disconnect from the channel
     */
    disconnect() {
        if (this.channel) {
            window.Echo.leave('map-location-types');
            this.channel = null;
            this.updateConnectionStatus('disconnected');
            console.log('Disconnected from map-location-types channel');
        }
    }
}

// Export for use in blade templates
window.LocationTypesRealtime = LocationTypesRealtime;

