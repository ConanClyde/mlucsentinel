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
                    `${editor} added ${locationType.name}`,
                    locationType.id,
                    'created'
                );
                break;
            case 'updated':
                this.updateLocationType(locationType);
                this.showBrowserNotification(
                    'Location Type Updated',
                    `${editor} updated ${locationType.name}`,
                    locationType.id,
                    'updated'
                );
                break;
            case 'deleted':
                this.removeLocationType(locationType);
                this.showBrowserNotification(
                    'Location Type Removed',
                    `${editor} removed ${locationType.name}`,
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
        const existingRow = this.tableBody.querySelector(`tr[data-id="${locationType.id}"]`);
        
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
        const existingRow = this.tableBody.querySelector(`tr[data-id="${locationType.id}"]`);
        
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
        const rowToRemove = this.tableBody.querySelector(`tr[data-id="${locationType.id}"]`);
        
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

        const createdDate = new Date(locationType.created_at).toLocaleDateString();

        row.innerHTML = `
            <td class="px-4 py-3 text-sm text-[#1b1b18] dark:text-[#EDEDEC]">${this.escapeHtml(locationType.name)}</td>
            <td class="px-4 py-3 text-sm">
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded" style="background-color: ${locationType.default_color || '#3B82F6'}"></div>
                    <span class="text-[#706f6c] dark:text-[#A1A09A]">${locationType.default_color || '#3B82F6'}</span>
                </div>
            </td>
            <td class="px-4 py-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">${createdDate}</td>
            <td class="px-4 py-3">
                <div class="flex items-center justify-center gap-2">
                    <button onclick="editLocationType(${locationType.id}, '${locationType.name.replace(/'/g, "\\'")}', '${(locationType.default_color || '#3B82F6').replace(/'/g, "\\'")}', '${(locationType.description || '').replace(/'/g, "\\'").replace(/\n/g, '\\n')}')" class="btn-edit" title="Edit">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.829-2.828z"></path>
                        </svg>
                    </button>
                    <button onclick="deleteLocationType(${locationType.id})" class="btn-delete" title="Delete">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
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

