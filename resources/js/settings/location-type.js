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
                    <tr class="hover:bg-gray-50 dark:hover:bg-[#161615]" data-id="${type.id}" data-location-type-id="${type.id}">
                        <td class="px-4 py-3 text-sm text-[#1b1b18] dark:text-[#EDEDEC]">${type.name}</td>
                        <td class="px-4 py-3 text-sm">
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded" style="background-color: ${type.default_color || '#3B82F6'}"></div>
                                <span class="text-[#706f6c] dark:text-[#A1A09A]">${type.default_color || '#3B82F6'}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">${type.created_at ? new Date(type.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : ''}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="editLocationType(${type.id}, '${type.name.replace(/'/g, "\\'")}', '${(type.default_color || '#3B82F6').replace(/'/g, "\\'")}', '${(type.description || '').replace(/'/g, "\\'").replace(/\n/g, '\\n')}')" class="btn-edit" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <button onclick="deleteLocationType(${type.id})" class="btn-delete" title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
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

// Helper functions
function showError(inputId, errorId, message) {
    const errorEl = document.getElementById(errorId);
    if (errorEl) {
        errorEl.textContent = message;
        errorEl.classList.remove('hidden');
    }
    const inputEl = document.getElementById(inputId);
    if (inputEl) {
        inputEl.classList.add('border-red-500');
    }
}

function hideError(inputId, errorId) {
    const errorEl = document.getElementById(errorId);
    if (errorEl) {
        errorEl.classList.add('hidden');
        errorEl.textContent = '';
    }
    const inputEl = document.getElementById(inputId);
    if (inputEl) {
        inputEl.classList.remove('border-red-500');
    }
}

function validateAddLocationTypeForm() {
    const name = document.getElementById('modal-location-type-name').value.trim();
    const color = document.getElementById('modal-location-type-color-hex').value.trim() || document.getElementById('modal-location-type-color').value;
    const addBtn = document.getElementById('add-location-type-btn');
    
    if (!name || !color || !/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(color)) {
        addBtn.disabled = true;
        return false;
    }
    
    hideError('modal-location-type-name', 'modal-location-type-name-error');
    hideError('modal-location-type-color-hex', 'modal-location-type-color-error');
    addBtn.disabled = false;
    return true;
}

function validateEditLocationTypeForm() {
    const name = document.getElementById('edit-location-type-name').value.trim();
    const color = document.getElementById('edit-location-type-color-hex').value.trim() || document.getElementById('edit-location-type-color').value;
    const updateBtn = document.getElementById('update-location-type-btn');
    
    if (!name || !color || !/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(color)) {
        updateBtn.disabled = true;
        return false;
    }
    
    hideError('edit-location-type-name', 'edit-location-type-name-error');
    hideError('edit-location-type-color-hex', 'edit-location-type-color-error');
    updateBtn.disabled = false;
    return true;
}

export function openAddLocationTypeModal() {
    // Sync color picker and hex input
    const colorInput = document.getElementById('modal-location-type-color');
    const hexInput = document.getElementById('modal-location-type-color-hex');
    
    if (colorInput && hexInput) {
        colorInput.addEventListener('input', function() {
            hexInput.value = this.value;
            validateAddLocationTypeForm();
        });
        hexInput.addEventListener('input', function() {
            if (/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(this.value)) {
                colorInput.value = this.value;
            }
            validateAddLocationTypeForm();
        });
    }
    
    const nameInput = document.getElementById('modal-location-type-name');
    if (nameInput) nameInput.addEventListener('input', validateAddLocationTypeForm);
    
    document.getElementById('add-location-type-modal').classList.remove('hidden');
    validateAddLocationTypeForm();
}

export function closeAddLocationTypeModal() {
    document.getElementById('add-location-type-modal').classList.add('hidden');
    document.getElementById('modal-location-type-name').value = '';
    document.getElementById('modal-location-type-color').value = '#3B82F6';
    document.getElementById('modal-location-type-color-hex').value = '#3B82F6';
    document.getElementById('modal-location-type-description').value = '';
    hideError('modal-location-type-name', 'modal-location-type-name-error');
    hideError('modal-location-type-color-hex', 'modal-location-type-color-error');
}

export function addLocationType() {
    const name = document.getElementById('modal-location-type-name').value.trim();
    const color = document.getElementById('modal-location-type-color-hex').value.trim() || document.getElementById('modal-location-type-color').value;
    const description = document.getElementById('modal-location-type-description').value.trim();
    
    if (!name) {
        showError('modal-location-type-name', 'modal-location-type-name-error', 'Please enter a location type name');
        return;
    }
    
    if (!color || !/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(color)) {
        showError('modal-location-type-color-hex', 'modal-location-type-color-error', 'Please enter a valid hex color (e.g., #3B82F6)');
        return;
    }
    
    hideError('modal-location-type-name', 'modal-location-type-name-error');
    hideError('modal-location-type-color-hex', 'modal-location-type-color-error');
    
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
            showError('modal-location-type-name', 'modal-location-type-name-error', data.message || 'Failed to add location type');
        }
    })
    .catch(error => {
        console.error('Error adding location type:', error);
        showError('modal-location-type-name', 'modal-location-type-name-error', 'Error adding location type. Please try again.');
    });
}

export function editLocationType(id, currentName, currentColor, currentDescription = '') {
    // If only ID is provided, extract data from the table row
    if (currentName === undefined || currentName === null) {
        const row = document.querySelector(`tr[data-location-type-id="${id}"]`);
        if (row) {
            // Try to get from data attributes first (more reliable)
            currentName = row.dataset.locationTypeName || '';
            currentColor = row.dataset.locationTypeColor || '#3B82F6';
            currentDescription = row.dataset.locationTypeDescription || '';
            
            // Fallback to extracting from cells if data attributes not available
            if (!currentName) {
                const nameCell = row.querySelector('td:nth-child(1) span');
                currentName = nameCell ? nameCell.textContent.trim() : '';
            }
            if (!currentColor || currentColor === '#3B82F6') {
                const colorCell = row.querySelector('td:nth-child(2) span');
                currentColor = colorCell ? colorCell.textContent.trim() : '#3B82F6';
            }
        } else if (locationTypesRealtimeManager) {
            // Try to get from realtime manager
            const locationType = locationTypesRealtimeManager.locationTypes?.find(lt => lt.id === Number(id));
            if (locationType) {
                currentName = locationType.name || '';
                currentColor = locationType.default_color || '#3B82F6';
                currentDescription = locationType.description || '';
            }
        }
    }
    
    document.getElementById('edit-location-type-id').value = id;
    document.getElementById('edit-location-type-name').value = currentName || '';
    document.getElementById('edit-location-type-color').value = currentColor || '#3B82F6';
    document.getElementById('edit-location-type-color-hex').value = currentColor || '#3B82F6';
    document.getElementById('edit-location-type-description').value = currentDescription || '';
    
    // Sync color picker and hex input
    const colorInput = document.getElementById('edit-location-type-color');
    const hexInput = document.getElementById('edit-location-type-color-hex');
    
    if (colorInput && hexInput) {
        colorInput.addEventListener('input', function() {
            hexInput.value = this.value;
            validateEditLocationTypeForm();
        });
        hexInput.addEventListener('input', function() {
            if (/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(this.value)) {
                colorInput.value = this.value;
            }
            validateEditLocationTypeForm();
        });
    }
    
    const nameInput = document.getElementById('edit-location-type-name');
    if (nameInput) nameInput.addEventListener('input', validateEditLocationTypeForm);
    
    document.getElementById('edit-location-type-modal').classList.remove('hidden');
    validateEditLocationTypeForm();
}

export function closeEditLocationTypeModal() {
    document.getElementById('edit-location-type-modal').classList.add('hidden');
    document.getElementById('edit-location-type-id').value = '';
    document.getElementById('edit-location-type-name').value = '';
    document.getElementById('edit-location-type-color').value = '#3B82F6';
    document.getElementById('edit-location-type-color-hex').value = '#3B82F6';
    document.getElementById('edit-location-type-description').value = '';
    hideError('edit-location-type-name', 'edit-location-type-name-error');
    hideError('edit-location-type-color-hex', 'edit-location-type-color-error');
}

export function updateLocationType() {
    const id = document.getElementById('edit-location-type-id').value;
    const name = document.getElementById('edit-location-type-name').value.trim();
    const color = document.getElementById('edit-location-type-color-hex').value.trim() || document.getElementById('edit-location-type-color').value;
    const description = document.getElementById('edit-location-type-description').value.trim();
    
    if (!name) {
        showError('edit-location-type-name', 'edit-location-type-name-error', 'Please enter a location type name');
        return;
    }
    
    if (!color || !/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(color)) {
        showError('edit-location-type-color-hex', 'edit-location-type-color-error', 'Please enter a valid hex color (e.g., #3B82F6)');
        return;
    }
    
    hideError('edit-location-type-name', 'edit-location-type-name-error');
    hideError('edit-location-type-color-hex', 'edit-location-type-color-error');
    
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
            showError('edit-location-type-name', 'edit-location-type-name-error', data.message || 'Failed to update location type');
        }
    })
    .catch(error => {
        console.error('Error updating location type:', error);
        showError('edit-location-type-name', 'edit-location-type-name-error', 'Error updating location type. Please try again.');
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

