/**
 * Location Type Settings Management
 */

let locationTypesRealtimeManager = null;
let locationTypesLoaded = false;

export function initializeLocationType() {
    loadLocationTypes();
}

export function loadLocationTypes() {
    if (locationTypesLoaded) return;
    
    const tableBody = document.getElementById('location-type-table-body');
    if (!tableBody) return;
    
    // Check if data is already server-rendered
    const existingRows = tableBody.querySelectorAll('tr[data-location-type-id]');
    if (existingRows.length > 0) {
        locationTypesLoaded = true;
        // Initialize realtime manager with server-rendered data
        if (window.LocationTypesRealtime && !locationTypesRealtimeManager) {
            const locationTypes = Array.from(existingRows).map(row => {
                const id = row.getAttribute('data-location-type-id');
                const name = row.querySelector('td:nth-child(1) span').textContent;
                const color = row.querySelector('td:nth-child(2) span').textContent;
                const createdAt = row.querySelector('td:nth-child(3) span').textContent;
                return { id: parseInt(id), name, default_color: color, created_at: createdAt };
            });
            locationTypesRealtimeManager = new window.LocationTypesRealtime();
            locationTypesRealtimeManager.init(locationTypes);
        }
        return;
    }
    
    locationTypesLoaded = true;
    tableBody.innerHTML = '<tr><td colspan="4" class="px-4 py-8 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">Loading location types...</td></tr>';
    
    fetch('/api/map-location-types', {
        headers: {
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const locationTypes = data.data;
            
            if (locationTypes.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="4" class="px-4 py-8 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">No location types found. Click Add button to create one.</td></tr>';
            } else {
                tableBody.innerHTML = locationTypes.map(type => {
                    return `
                    <tr class="hover:bg-gray-50 dark:hover:bg-[#161615]" data-id="${type.id}">
                        <td class="px-4 py-3 text-sm text-[#1b1b18] dark:text-[#EDEDEC]">${type.name}</td>
                        <td class="px-4 py-3 text-sm">
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded" style="background-color: ${type.default_color || '#3B82F6'}"></div>
                                <span class="text-[#706f6c] dark:text-[#A1A09A]">${type.default_color || '#3B82F6'}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">${new Date(type.created_at).toLocaleDateString()}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="editLocationType(${type.id}, '${type.name.replace(/'/g, "\\'")}', '${(type.default_color || '#3B82F6').replace(/'/g, "\\'")}', '${(type.description || '').replace(/'/g, "\\'").replace(/\n/g, '\\n')}')" class="btn-edit" title="Edit">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.829-2.828z"></path>
                                    </svg>
                                </button>
                                <button onclick="deleteLocationType(${type.id})" class="btn-delete" title="Delete">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
                }).join('');
            }
            
            // Initialize realtime manager
            if (window.LocationTypesRealtime && !locationTypesRealtimeManager) {
                locationTypesRealtimeManager = new window.LocationTypesRealtime();
                locationTypesRealtimeManager.init(locationTypes);
            }
        }
    })
    .catch(error => {
        console.error('Error loading location types:', error);
                tableBody.innerHTML = '<tr><td colspan="4" class="px-4 py-8 text-center text-sm text-red-600 dark:text-red-400">Error loading location types. Please try again.</td></tr>';
    });
}

export function openAddLocationTypeModal() {
    // Sync color picker and hex input
    const colorInput = document.getElementById('modal-location-type-color');
    const hexInput = document.getElementById('modal-location-type-color-hex');
    
    if (colorInput && hexInput) {
        colorInput.addEventListener('input', function() {
            hexInput.value = this.value;
        });
        hexInput.addEventListener('input', function() {
            if (/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(this.value)) {
                colorInput.value = this.value;
            }
        });
    }
    
    document.getElementById('add-location-type-modal').classList.remove('hidden');
}

export function closeAddLocationTypeModal() {
    document.getElementById('add-location-type-modal').classList.add('hidden');
    document.getElementById('modal-location-type-name').value = '';
    document.getElementById('modal-location-type-color').value = '#3B82F6';
    document.getElementById('modal-location-type-color-hex').value = '#3B82F6';
    document.getElementById('modal-location-type-description').value = '';
}

export function addLocationType() {
    const name = document.getElementById('modal-location-type-name').value.trim();
    const color = document.getElementById('modal-location-type-color-hex').value.trim() || document.getElementById('modal-location-type-color').value;
    const description = document.getElementById('modal-location-type-description').value.trim();
    
    if (!name) {
        window.showErrorModal('Please enter a location type name');
        return;
    }
    
    if (!color || !/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(color)) {
        window.showErrorModal('Please enter a valid hex color (e.g., #3B82F6)');
        return;
    }
    
    fetch('/api/map-location-types', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ 
            name: name,
            default_color: color,
            requires_polygon: true, // Always require polygon
            description: description || null,
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.showSuccessModal('Success!', 'Location type added successfully!');
            closeAddLocationTypeModal();
            locationTypesLoaded = false; // Reset to allow reload
            loadLocationTypes();
            if (locationTypesRealtimeManager && data.data) {
                locationTypesRealtimeManager.addLocationType(data.data);
            }
        } else {
            window.showErrorModal(data.message || 'Failed to add location type');
        }
    })
    .catch(error => {
        console.error('Error adding location type:', error);
        window.showErrorModal('Error adding location type. Please try again.');
    });
}

export function editLocationType(id, currentName, currentColor, currentDescription = '') {
    document.getElementById('edit-location-type-id').value = id;
    document.getElementById('edit-location-type-name').value = currentName;
    document.getElementById('edit-location-type-color').value = currentColor || '#3B82F6';
    document.getElementById('edit-location-type-color-hex').value = currentColor || '#3B82F6';
    document.getElementById('edit-location-type-description').value = currentDescription || '';
    
    // Sync color picker and hex input
    const colorInput = document.getElementById('edit-location-type-color');
    const hexInput = document.getElementById('edit-location-type-color-hex');
    
    if (colorInput && hexInput) {
        colorInput.addEventListener('input', function() {
            hexInput.value = this.value;
        });
        hexInput.addEventListener('input', function() {
            if (/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(this.value)) {
                colorInput.value = this.value;
            }
        });
    }
    
    document.getElementById('edit-location-type-modal').classList.remove('hidden');
}

export function closeEditLocationTypeModal() {
    document.getElementById('edit-location-type-modal').classList.add('hidden');
    document.getElementById('edit-location-type-id').value = '';
    document.getElementById('edit-location-type-name').value = '';
    document.getElementById('edit-location-type-color').value = '#3B82F6';
    document.getElementById('edit-location-type-color-hex').value = '#3B82F6';
    document.getElementById('edit-location-type-description').value = '';
}

export function updateLocationType() {
    const id = document.getElementById('edit-location-type-id').value;
    const name = document.getElementById('edit-location-type-name').value.trim();
    const color = document.getElementById('edit-location-type-color-hex').value.trim() || document.getElementById('edit-location-type-color').value;
    const description = document.getElementById('edit-location-type-description').value.trim();
    
    if (!name) {
        window.showErrorModal('Please enter a location type name');
        return;
    }
    
    if (!color || !/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(color)) {
        window.showErrorModal('Please enter a valid hex color (e.g., #3B82F6)');
        return;
    }
    
    fetch(`/api/map-location-types/${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ 
            name: name,
            default_color: color,
            requires_polygon: true, // Always require polygon
            description: description || null,
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.showSuccessModal('Success!', 'Location type updated successfully!');
            closeEditLocationTypeModal();
            locationTypesLoaded = false; // Reset to allow reload
            loadLocationTypes();
            if (locationTypesRealtimeManager && data.data) {
                locationTypesRealtimeManager.updateLocationType(data.data);
            }
        } else {
            window.showErrorModal(data.message || 'Failed to update location type');
        }
    })
    .catch(error => {
        console.error('Error updating location type:', error);
        window.showErrorModal('Error updating location type. Please try again.');
    });
}

let locationTypeToDelete = null;

export function deleteLocationType(id) {
    locationTypeToDelete = id;
    document.getElementById('delete-location-type-modal').classList.remove('hidden');
}

export function closeDeleteLocationTypeModal() {
    document.getElementById('delete-location-type-modal').classList.add('hidden');
    locationTypeToDelete = null;
}

export function confirmDeleteLocationType() {
    if (!locationTypeToDelete) return;
    
    const locationTypeId = locationTypeToDelete;
    
    fetch(`/api/map-location-types/${locationTypeId}`, {
        method: 'DELETE',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.showSuccessModal('Success!', 'Location type deleted successfully!');
            closeDeleteLocationTypeModal();
            locationTypesLoaded = false; // Reset to allow reload
            loadLocationTypes();
            if (locationTypesRealtimeManager) {
                locationTypesRealtimeManager.removeLocationType({ id: locationTypeId });
            }
        } else {
            window.showErrorModal(data.message || 'Failed to delete location type');
        }
    })
    .catch(error => {
        console.error('Error deleting location type:', error);
        window.showErrorModal('Error deleting location type. Please try again.');
    });
}

// Make functions globally available
window.openAddLocationTypeModal = openAddLocationTypeModal;
window.closeAddLocationTypeModal = closeAddLocationTypeModal;
window.addLocationType = addLocationType;
window.editLocationType = editLocationType;
window.closeEditLocationTypeModal = closeEditLocationTypeModal;
window.updateLocationType = updateLocationType;
window.deleteLocationType = deleteLocationType;
window.closeDeleteLocationTypeModal = closeDeleteLocationTypeModal;
window.confirmDeleteLocationType = confirmDeleteLocationType;

