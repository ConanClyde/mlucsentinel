/**
 * Real-time Vehicle Type Updates Module
 * 
 * This module handles real-time updates for the vehicle types table
 * using Laravel Echo and Reverb WebSocket server.
 */

class VehicleTypesRealtime {
    constructor() {
        this.connectionStatus = null;
        this.tableBody = null;
        this.vehicleTypes = [];
        this.channel = null;
        this.retryCount = 0;
        this.maxRetries = 5;
    }

    /**
     * Initialize the real-time connection
     */
    init(vehicleTypes = []) {
        this.vehicleTypes = vehicleTypes;
        this.connectionStatus = document.getElementById('vehicle-types-connection-status');
        this.tableBody = document.getElementById('vehicle-type-table-body');

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

        console.log('Setting up Echo connection for vehicle types...');
        this.updateConnectionStatus('connecting');

        try {
            // Subscribe to the vehicle-types channel
            this.channel = window.Echo.channel('vehicle-types');

            // Listen for vehicle-type.updated events
            this.channel.listen('.vehicle-type.updated', (event) => {
                console.log('Received vehicle type broadcast event:', event);
                this.handleVehicleTypeUpdate(event);
            });

            // Handle connection success
            this.channel.subscribed(() => {
                console.log('Successfully subscribed to vehicle-types channel');
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
     * Handle vehicle type update events
     */
    handleVehicleTypeUpdate(event) {
        const { vehicleType, action, editor } = event;

        switch (action) {
            case 'created':
                this.addVehicleType(vehicleType);
                this.showBrowserNotification(
                    'Vehicle Type Created',
                    `${editor} added ${vehicleType.name}`,
                    vehicleType.id,
                    'created'
                );
                break;
            case 'updated':
                this.updateVehicleType(vehicleType);
                this.showBrowserNotification(
                    'Vehicle Type Updated',
                    `${editor} updated ${vehicleType.name}`,
                    vehicleType.id,
                    'updated'
                );
                break;
            case 'deleted':
                this.removeVehicleType(vehicleType);
                this.showBrowserNotification(
                    'Vehicle Type Removed',
                    `${editor} removed ${vehicleType.name}`,
                    null,
                    'deleted'
                );
                break;
            default:
                console.warn('Unknown action:', action);
        }
    }

    /**
     * Add a new vehicle type row to the table
     */
    addVehicleType(vehicleType) {
        // Check if vehicle type already exists
        const existingRow = this.tableBody.querySelector(`tr[data-id="${vehicleType.id}"]`);
        
        if (existingRow) {
            this.updateVehicleType(vehicleType);
            return;
        }

        // Add to local array
        this.vehicleTypes.unshift(vehicleType);

        // Create and insert the new row
        const newRow = this.createVehicleTypeRow(vehicleType);
        
        // Add animation class
        newRow.classList.add('animate-fade-in');
        
        // Remove "No vehicle types found" row if it exists
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
     * Update an existing vehicle type row
     */
    updateVehicleType(vehicleType) {
        const existingRow = this.tableBody.querySelector(`tr[data-id="${vehicleType.id}"]`);
        
        if (!existingRow) {
            console.log('Vehicle type not found in table, adding instead');
            this.addVehicleType(vehicleType);
            return;
        }

        // Update local array
        const index = this.vehicleTypes.findIndex(vt => vt.id === vehicleType.id);
        if (index !== -1) {
            this.vehicleTypes[index] = vehicleType;
        }

        // Create new row and replace the old one
        const newRow = this.createVehicleTypeRow(vehicleType);
        
        // Add highlight animation
        newRow.classList.add('animate-highlight');
        
        existingRow.parentNode.replaceChild(newRow, existingRow);

        // Remove animation class after animation completes
        setTimeout(() => {
            newRow.classList.remove('animate-highlight');
        }, 1000);
    }

    /**
     * Remove a vehicle type row from the table
     */
    removeVehicleType(vehicleType) {
        const rowToRemove = this.tableBody.querySelector(`tr[data-id="${vehicleType.id}"]`);
        
        if (!rowToRemove) {
            console.log('Vehicle type not found in table');
            return;
        }

        // Update local array
        this.vehicleTypes = this.vehicleTypes.filter(vt => vt.id !== vehicleType.id);

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
     * Create a vehicle type table row
     */
    createVehicleTypeRow(vehicleType) {
        const row = document.createElement('tr');
        row.className = 'hover:bg-gray-50 dark:hover:bg-[#161615]';
        row.setAttribute('data-id', vehicleType.id);

        const createdDate = new Date(vehicleType.created_at).toLocaleDateString();

        row.innerHTML = `
            <td class="px-4 py-3 text-sm text-[#1b1b18] dark:text-[#EDEDEC]">${this.escapeHtml(vehicleType.name)}</td>
            <td class="px-4 py-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">${createdDate}</td>
            <td class="px-4 py-3">
                <div class="flex items-center justify-center gap-2">
                    <button onclick="editVehicleType(${vehicleType.id}, '${vehicleType.name.replace(/'/g, "\\'")}')" class="btn-edit" title="Edit">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.829-2.828z"></path>
                        </svg>
                    </button>
                    <button onclick="deleteVehicleType(${vehicleType.id})" class="btn-delete" title="Delete">
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
     * Show empty state when no vehicle types exist
     */
    showEmptyState() {
        const emptyRow = document.createElement('tr');
        emptyRow.innerHTML = `
            <td colspan="3" class="px-4 py-8 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">
                No vehicle types found. Click Add button to create one.
            </td>
        `;
        this.tableBody.appendChild(emptyRow);
    }

    /**
     * Show browser/system notification
     */
    showBrowserNotification(title, message, vehicleTypeId = null, action = null) {
        // Check if browser notifications are enabled
        const notificationsEnabled = localStorage.getItem('browserNotifications') === 'true';
        if (!notificationsEnabled) {
            return;
        }

        // Request permission if not granted
        if (Notification.permission === 'default') {
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    this.displayNotification(title, message, vehicleTypeId, action);
                }
            });
        } else if (Notification.permission === 'granted') {
            this.displayNotification(title, message, vehicleTypeId, action);
        }
    }

    /**
     * Display the browser notification
     */
    displayNotification(title, message, vehicleTypeId = null, action = null) {
        const notification = new Notification(title, {
            body: message,
            icon: '/favicon.ico',
            badge: '/favicon.ico',
            tag: 'vehicle-type-update',
            requireInteraction: false,
        });

        // Auto close after 5 seconds
        setTimeout(() => notification.close(), 5000);

        // Handle click event
        notification.onclick = function() {
            window.focus();
            notification.close();
            // Navigate to settings page vehicle type tab
            window.location.href = '/settings#vehicle-type';
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
            window.Echo.leave('vehicle-types');
            this.channel = null;
            this.updateConnectionStatus('disconnected');
            console.log('Disconnected from vehicle-types channel');
        }
    }
}

// Export for use in blade templates
window.VehicleTypesRealtime = VehicleTypesRealtime;

