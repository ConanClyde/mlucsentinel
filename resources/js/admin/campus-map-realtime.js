/**
 * Real-time Campus Map Updates Module
 * 
 * This module handles real-time updates for the campus map locations
 * using Laravel Echo and Reverb WebSocket server.
 */

class CampusMapRealtime {
    constructor() {
        this.channel = null;
        this.retryCount = 0;
        this.maxRetries = 5;
        this.locations = [];
    }

    /**
     * Initialize the real-time connection
     */
    init(locations = []) {
        this.locations = locations;
        // Sync with global locations array
        if (typeof window.campusMapData !== 'undefined' && window.campusMapData.locations) {
            this.locations = window.campusMapData.locations;
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
            }
            return;
        }

        console.log('Setting up Echo connection for map locations...');

        try {
            // Subscribe to the map-locations channel
            this.channel = window.Echo.channel('map-locations');

            // Listen for location.updated events
            this.channel.listen('.location.updated', (event) => {
                console.log('Received map location broadcast event:', event);
                this.handleLocationUpdate(event);
            });

            // Handle connection success
            this.channel.subscribed(() => {
                console.log('Successfully subscribed to map-locations channel');
            });

            // Handle connection errors
            this.channel.error((error) => {
                console.error('Channel subscription error:', error);
            });

        } catch (error) {
            console.error('Error setting up Echo:', error);
        }
    }

    /**
     * Handle location update events
     */
    handleLocationUpdate(event) {
        const { location, action, editor } = event;

        // Process sticker path if it exists
        if (location.sticker_path && !location.sticker_path.startsWith('/storage/') && !location.sticker_path.startsWith('http')) {
            location.sticker_path = '/storage/' + location.sticker_path;
        }

        switch (action) {
            case 'created':
                this.addLocation(location);
                break;
            case 'updated':
                this.updateLocation(location);
                // Only show notification if this update wasn't made by current user
                if (editor && !this.isCurrentUserAction(location.id)) {
                    console.log(`Location "${location.name}" was updated by ${editor.name || editor}`);
                }
                break;
            case 'deleted':
                this.removeLocation(location);
                if (editor && !this.isCurrentUserAction(location.id)) {
                    console.log(`Location "${location.name}" was deleted by ${editor.name || editor}`);
                }
                break;
            default:
                console.warn('Unknown action:', action);
        }
    }

    /**
     * Add a new location to the map
     */
    addLocation(location) {
        // Update global locations array if it exists
        if (typeof locations !== 'undefined' && Array.isArray(locations)) {
            const index = locations.findIndex(l => l.id === location.id);
            if (index === -1) {
                locations.push(location);
            } else {
                locations[index] = location;
            }
        }

        // Add to local array
        const localIndex = this.locations.findIndex(l => l.id === location.id);
        if (localIndex === -1) {
            this.locations.push(location);
        } else {
            this.locations[localIndex] = location;
        }

        // Re-render locations on map
        if (typeof renderLocations === 'function') {
            renderLocations();
        }

        // Add to locations list card if visible
        this.updateLocationsListCard(location, 'created');
    }

    /**
     * Update an existing location
     */
    updateLocation(location) {
        // Update global locations array if it exists
        if (typeof locations !== 'undefined' && Array.isArray(locations)) {
            const index = locations.findIndex(l => l.id === location.id);
            if (index !== -1) {
                locations[index] = location;
            } else {
                locations.push(location);
            }
        }

        // Update in local array
        const localIndex = this.locations.findIndex(l => l.id === location.id);
        if (localIndex !== -1) {
            this.locations[localIndex] = location;
        } else {
            this.locations.push(location);
        }

        // If currently editing this location, update the editing state
        if (typeof editingLocationId !== 'undefined' && editingLocationId === location.id) {
            // Refresh the location data but keep edit mode
            console.log('Updating location in edit mode');
        }

        // Re-render locations on map
        if (typeof renderLocations === 'function') {
            renderLocations();
        }

        // Update in locations list card
        this.updateLocationsListCard(location, 'updated');
    }

    /**
     * Remove a location from the map
     */
    removeLocation(location) {
        // Remove from global locations array if it exists
        if (typeof locations !== 'undefined' && Array.isArray(locations)) {
            const index = locations.findIndex(l => l.id === location.id);
            if (index !== -1) {
                locations.splice(index, 1);
            }
        }

        // Remove from local array
        this.locations = this.locations.filter(l => l.id !== location.id);

        // If currently editing this location, cancel edit mode
        if (typeof editingLocationId !== 'undefined' && editingLocationId === location.id) {
            if (typeof cancelDrawingMode === 'function') {
                cancelDrawingMode();
            }
        }

        // Re-render locations on map
        if (typeof renderLocations === 'function') {
            renderLocations();
        }

        // Remove from locations list card
        this.removeFromLocationsListCard(location.id);
    }

    /**
     * Update locations list card in the UI
     */
    updateLocationsListCard(location, action) {
        const listCard = document.getElementById('locations-list-card');
        if (!listCard) return;

        const locationElement = listCard.querySelector(`[data-location-id="${location.id}"]`);
        
        if (action === 'created') {
            // Add new location to the list (insert at appropriate position)
            if (!locationElement && location.is_active) {
                this.insertLocationInList(location, listCard);
            }
        } else if (action === 'updated') {
            // Update existing location in the list
            if (locationElement) {
                if (location.is_active) {
                    // Update the existing element
                    this.updateLocationInList(locationElement, location);
                } else {
                    // Remove if deactivated
                    locationElement.remove();
                }
            } else if (location.is_active) {
                // Add if it doesn't exist but is now active
                this.insertLocationInList(location, listCard);
            }
        }
    }

    /**
     * Insert location into the list (maintaining sort order)
     */
    insertLocationInList(location, listCard) {
        const locationItems = Array.from(listCard.querySelectorAll('[data-location-id]'));
        
        // Find insertion point based on display_order or name
        let insertBefore = null;
        for (const item of locationItems) {
            const itemId = parseInt(item.getAttribute('data-location-id'));
            const itemLocation = this.locations.find(l => l.id === itemId);
            if (itemLocation) {
                if (location.display_order < itemLocation.display_order ||
                    (location.display_order === itemLocation.display_order && 
                     location.name < itemLocation.name)) {
                    insertBefore = item;
                    break;
                }
            }
        }

        // Create location HTML element
        const locationHtml = this.createLocationElement(location);
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = locationHtml.trim();
        const newElement = tempDiv.firstChild;

        if (insertBefore) {
            listCard.querySelector('.space-y-2').insertBefore(newElement, insertBefore);
        } else {
            listCard.querySelector('.space-y-2').appendChild(newElement);
        }
    }

    /**
     * Update location element in the list
     */
    updateLocationInList(element, location) {
        const typeElement = element.querySelector('p.text-xs:last-child');
        if (typeElement) {
            typeElement.textContent = location.type?.name || 'Unknown';
        }

        const nameElement = element.querySelector('p.text-xs.text-\\[\\#706f6c\\]');
        if (nameElement && nameElement.classList.contains('truncate')) {
            nameElement.textContent = location.name;
        }

        const codeElement = element.querySelector('span.font-semibold');
        if (codeElement) {
            codeElement.textContent = location.short_code || location.name;
        }

        const colorElement = element.querySelector('.w-3.h-3.rounded');
        if (colorElement) {
            colorElement.style.backgroundColor = location.color;
        }
    }

    /**
     * Remove location from list card
     */
    removeFromLocationsListCard(locationId) {
        const listCard = document.getElementById('locations-list-card');
        if (!listCard) return;

        const locationElement = listCard.querySelector(`[data-location-id="${locationId}"]`);
        if (locationElement) {
            locationElement.remove();
        }
    }

    /**
     * Create location HTML element (matches Blade template structure)
     */
    createLocationElement(location) {
        return `
            <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-lg p-3 hover:bg-gray-50 dark:hover:bg-[#161615] transition-colors" data-location-id="${location.id}">
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center space-x-2 mb-1">
                            <div class="w-3 h-3 rounded" style="background-color: ${location.color};"></div>
                            <span class="text-sm font-semibold text-[#1b1b18] dark:text-[#EDEDEC] truncate">
                                ${location.short_code || location.name}
                            </span>
                        </div>
                        <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] truncate">${location.name}</p>
                        <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-1">${location.type?.name || 'Unknown'}</p>
                    </div>
                    <div class="flex items-center space-x-1 ml-2">
                        <button onclick="viewLocationDetails(${location.id})" class="btn-view" title="View">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                        <button onclick="editLocation(${location.id})" class="btn-edit" title="Edit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        <button onclick="deleteLocation(${location.id})" class="btn-delete" title="Delete">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Check if the current user just performed this action
     */
    isCurrentUserAction(locationId) {
        if (!window._recentMapActions) {
            window._recentMapActions = new Map();
        }
        
        const now = Date.now();
        const recent = window._recentMapActions.get(locationId);
        
        if (recent && (now - recent) < 2000) {
            window._recentMapActions.delete(locationId);
            return true;
        }
        
        return false;
    }

    /**
     * Mark an action as performed by current user
     */
    markUserAction(locationId) {
        if (!window._recentMapActions) {
            window._recentMapActions = new Map();
        }
        window._recentMapActions.set(locationId, Date.now());
    }
}

// Export for use in other files
window.CampusMapRealtime = CampusMapRealtime;

