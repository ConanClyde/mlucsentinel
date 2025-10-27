/**
 * Real-time Vehicle Updates Module
 * 
 * This module handles real-time updates for the vehicles table
 * using Laravel Echo and Reverb WebSocket server.
 */

class VehiclesRealtime {
    constructor() {
        this.connectionStatus = null;
        this.tableBody = null;
        this.vehicles = [];
        this.channel = null;
        this.retryCount = 0;
        this.maxRetries = 5;
    }

    /**
     * Initialize the real-time connection
     */
    init(vehicles = []) {
        this.vehicles = vehicles;
        this.connectionStatus = document.getElementById('connectionStatus');
        this.tableBody = document.getElementById('vehiclesTableBody');

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

        console.log('Setting up Echo connection for vehicles...');
        this.updateConnectionStatus('connecting');

        try {
            // Subscribe to the vehicles channel
            this.channel = window.Echo.channel('vehicles');

            // Listen for vehicle.updated events
            this.channel.listen('.vehicle.updated', (event) => {
                console.log('Received vehicle broadcast event:', event);
                this.handleVehicleUpdate(event);
            });

            // Handle connection success
            this.channel.subscribed(() => {
                console.log('Successfully subscribed to vehicles channel');
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
     * Handle vehicle update events
     */
    handleVehicleUpdate(event) {
        const { vehicle, action, editor } = event;

        switch (action) {
            case 'created':
                this.addVehicle(vehicle);
                this.showBrowserNotification(
                    'Vehicle Added',
                    `${editor} added a vehicle for ${vehicle.user.first_name} ${vehicle.user.last_name}`,
                    vehicle.id,
                    'created'
                );
                break;
            case 'updated':
                this.updateVehicle(vehicle);
                if (editor && !this.isCurrentUserAction(vehicle.id)) {
                    this.showBrowserNotification(
                        'Vehicle Updated',
                        `${editor} updated vehicle ${vehicle.plate_no || vehicle.color + '-' + vehicle.number}`,
                        vehicle.id,
                        'updated'
                    );
                }
                break;
            case 'deleted':
                this.removeVehicle(vehicle);
                if (editor && !this.isCurrentUserAction(vehicle.id)) {
                    this.showBrowserNotification(
                        'Vehicle Deleted',
                        `${editor} deleted vehicle ${vehicle.plate_no || vehicle.color + '-' + vehicle.number}`,
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
    isCurrentUserAction(vehicleId) {
        if (!window._recentActions) {
            window._recentActions = new Map();
        }
        
        const now = Date.now();
        const recent = window._recentActions.get(vehicleId);
        
        if (recent && (now - recent) < 2000) {
            window._recentActions.delete(vehicleId);
            return true;
        }
        
        return false;
    }

    /**
     * Mark an action as performed by current user
     */
    markUserAction(vehicleId) {
        if (!window._recentActions) {
            window._recentActions = new Map();
        }
        window._recentActions.set(vehicleId, Date.now());
    }

    /**
     * Add a new vehicle row to the table
     */
    addVehicle(vehicle) {
        // Check if vehicle already exists
        const existingRow = this.tableBody.querySelector(`tr[data-id="${vehicle.id}"]`);
        
        if (existingRow) {
            this.updateVehicle(vehicle);
            return;
        }

        // Add to local array
        this.vehicles.unshift(vehicle);

        // Update global vehicles array
        if (window.vehicles) {
            window.vehicles.unshift(vehicle);
        }

        // Create and insert the new row
        const newRow = this.createVehicleRow(vehicle);
        
        // Add animation class
        newRow.classList.add('animate-fade-in');
        
        // Remove "No vehicles found" row if it exists
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

        // Reapply pagination
        if (typeof window.applyPagination === 'function') {
            window.applyPagination();
        }
    }

    /**
     * Update an existing vehicle row
     */
    updateVehicle(vehicle) {
        const existingRow = this.tableBody.querySelector(`tr[data-id="${vehicle.id}"]`);
        
        if (!existingRow) {
            console.log('Vehicle not found in table, adding instead');
            this.addVehicle(vehicle);
            return;
        }

        // Update local array
        const index = this.vehicles.findIndex(v => v.id === vehicle.id);
        if (index !== -1) {
            this.vehicles[index] = vehicle;
        }

        // Update global vehicles array
        if (window.vehicles) {
            const globalIndex = window.vehicles.findIndex(v => v.id === vehicle.id);
            if (globalIndex !== -1) {
                window.vehicles[globalIndex] = vehicle;
            }
        }

        // Create new row and replace the old one
        const newRow = this.createVehicleRow(vehicle);
        
        // Add highlight animation
        newRow.classList.add('animate-highlight');
        
        existingRow.parentNode.replaceChild(newRow, existingRow);

        // Remove animation class after animation completes
        setTimeout(() => {
            newRow.classList.remove('animate-highlight');
        }, 1000);

        // Reapply pagination
        if (typeof window.applyPagination === 'function') {
            window.applyPagination();
        }
    }

    /**
     * Remove a vehicle row from the table
     */
    removeVehicle(vehicle) {
        const rowToRemove = this.tableBody.querySelector(`tr[data-id="${vehicle.id}"]`);
        
        if (!rowToRemove) {
            console.log('Vehicle not found in table');
            return;
        }

        // Update local array
        this.vehicles = this.vehicles.filter(v => v.id !== vehicle.id);

        // Update global vehicles array
        if (window.vehicles) {
            window.vehicles = window.vehicles.filter(v => v.id !== vehicle.id);
        }

        // Add fade-out animation
        rowToRemove.classList.add('animate-fade-out');

        // Remove after animation
        setTimeout(() => {
            rowToRemove.remove();

            // Check if table is empty
            if (this.tableBody.children.length === 0 || 
                this.tableBody.querySelectorAll('tr:not([style*="display: none"])').length === 0) {
                this.showEmptyState();
            }

            // Reapply pagination
            if (typeof window.applyPagination === 'function') {
                window.applyPagination();
            }
        }, 300);
    }

    /**
     * Create a vehicle table row
     */
    createVehicleRow(vehicle) {
        const row = document.createElement('tr');
        row.className = 'border-b border-[#e3e3e0] dark:border-[#3E3E3A] hover:bg-gray-50 dark:hover:bg-[#161615] transition-colors';
        row.setAttribute('data-id', vehicle.id);

        const statusClass = vehicle.is_active
            ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200'
            : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200';

        const statusText = vehicle.is_active ? 'Active' : 'Inactive';
        const typeName = vehicle.type ? vehicle.type.name : 'N/A';
        const plateNo = vehicle.plate_no || 'No Plate';
        const createdDate = new Date(vehicle.created_at).toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });

        const initials = vehicle.user.first_name.charAt(0).toUpperCase();
        const avatarColor = this.getAvatarColor(vehicle.user.first_name + vehicle.user.last_name);

        const colorMap = {
            'blue': '#007BFF',
            'green': '#28A745',
            'yellow': '#FFC107',
            'pink': '#E83E8C',
            'orange': '#FD7E14',
            'maroon': '#800000',
            'white': '#FFFFFF',
            'black': '#000000',
        };
        const bgColor = colorMap[vehicle.color] || '#000000';

        row.innerHTML = `
            <td class="py-2 px-3">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3 text-white font-semibold text-xs" style="background-color: ${avatarColor}">
                        ${initials}
                    </div>
                    <div>
                        <div class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${this.escapeHtml(vehicle.user.first_name)} ${this.escapeHtml(vehicle.user.last_name)}</div>
                        <div class="text-xs text-[#706f6c] dark:text-[#A1A09A]">${this.escapeHtml(vehicle.user.user_type.charAt(0).toUpperCase() + vehicle.user.user_type.slice(1))}</div>
                    </div>
                </div>
            </td>
            <td class="py-2 px-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">${this.escapeHtml(typeName)}</td>
            <td class="py-2 px-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">${this.escapeHtml(plateNo)}</td>
            <td class="py-2 px-3">
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded-full border border-gray-300 dark:border-gray-600" style="background-color: ${bgColor}"></div>
                    <span class="text-sm text-[#1b1b18] dark:text-[#EDEDEC]">${this.escapeHtml(vehicle.number)}</span>
                </div>
            </td>
            <td class="py-2 px-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">${createdDate}</td>
            <td class="py-2 px-3">
                <div class="flex items-center justify-center gap-2">
                    <button onclick="viewVehicle(${vehicle.id})" class="btn-view" title="View Sticker">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                    <button onclick="deleteVehicle(${vehicle.id})" class="btn-delete" title="Delete">
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
     * Show empty state when no vehicles exist
     */
    showEmptyState() {
        const emptyRow = document.createElement('tr');
        emptyRow.innerHTML = `
            <td colspan="6" class="py-8 px-4 text-center text-[#706f6c] dark:text-[#A1A09A]">
                No vehicles found.
            </td>
        `;
        this.tableBody.appendChild(emptyRow);
    }

    /**
     * Show browser/system notification
     */
    showBrowserNotification(title, message, vehicleId = null, action = null) {
        // Request permission if not granted
        if (Notification.permission === 'default') {
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    this.displayNotification(title, message, vehicleId, action);
                }
            });
        } else if (Notification.permission === 'granted') {
            this.displayNotification(title, message, vehicleId, action);
        }
    }

    /**
     * Display the browser notification
     */
    displayNotification(title, message, vehicleId = null, action = null) {
        const notification = new Notification(title, {
            body: message,
            icon: '/favicon.ico',
            badge: '/favicon.ico',
            tag: 'vehicle-update',
            requireInteraction: false,
        });

        // Auto close after 5 seconds
        setTimeout(() => notification.close(), 5000);

        // Handle click event - navigate to page
        notification.onclick = function() {
            window.focus();
            notification.close();
            window.location.href = '/vehicles';
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
            window.Echo.leave('vehicles');
            this.channel = null;
            this.updateConnectionStatus('disconnected');
            console.log('Disconnected from vehicles channel');
        }
    }
}

// Export for use in blade templates
window.VehiclesRealtime = VehiclesRealtime;

