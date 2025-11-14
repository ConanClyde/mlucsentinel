/**
 * Vehicle Type Settings Management
 */

let vehicleTypesRealtimeManager = null;
let vehicleTypesLoaded = false;

export function initializeVehicleType() {
    loadVehicleTypes();
}

export function loadVehicleTypes() {
    if (vehicleTypesLoaded) return;
    
    const tableBody = document.getElementById('vehicle-type-table-body');
    if (!tableBody) return;
    
    // Check if data is already server-rendered
    const existingRows = tableBody.querySelectorAll('tr[data-vehicle-type-id]');
    if (existingRows.length > 0) {
        vehicleTypesLoaded = true;
        // Initialize realtime manager with server-rendered data
        if (window.VehicleTypesRealtime && !vehicleTypesRealtimeManager) {
            const vehicleTypes = Array.from(existingRows).map(row => {
                const id = row.getAttribute('data-vehicle-type-id');
                const name = row.querySelector('td:nth-child(1) span').textContent;
                const requiresPlateText = row.querySelector('td:nth-child(2) span').textContent.trim();
                const createdAt = row.querySelector('td:nth-child(3) span').textContent;
                return { 
                    id: parseInt(id), 
                    name, 
                    requires_plate: requiresPlateText === 'Yes',
                    created_at: createdAt 
                };
            });
            vehicleTypesRealtimeManager = new window.VehicleTypesRealtime();
            vehicleTypesRealtimeManager.init(vehicleTypes);
        }
        return;
    }
    
    vehicleTypesLoaded = true;
    tableBody.innerHTML = '<tr><td colspan="4" class="px-4 py-8 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">Loading vehicle types...</td></tr>';
    
    fetch('/api/vehicle-types', {
        headers: {
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const vehicleTypes = data.data;
            
            if (vehicleTypes.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="4" class="px-4 py-8 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">No vehicle types found. Click Add button to create one.</td></tr>';
            } else {
                tableBody.innerHTML = vehicleTypes.map(type => `
                    <tr class="hover:bg-gray-50 dark:hover:bg-[#161615]" data-id="${type.id}" data-vehicle-type-id="${type.id}">
                        <td class="px-4 py-3 text-sm text-[#1b1b18] dark:text-[#EDEDEC]">${type.name}</td>
                        <td class="px-4 py-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                            ${type.requires_plate ? '<span class="px-2 py-1 text-xs font-medium text-green-700 bg-green-100 dark:bg-green-900/30 dark:text-green-400 rounded-full">Yes</span>' : '<span class="px-2 py-1 text-xs font-medium text-gray-700 bg-gray-100 dark:bg-gray-800 dark:text-gray-400 rounded-full">No</span>'}
                        </td>
                        <td class="px-4 py-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">${type.created_at ? new Date(type.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : ''}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="editVehicleType(${type.id}, '${type.name.replace(/'/g, "\\'")}', ${type.requires_plate ? 'true' : 'false'})" class="btn-edit" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <button onclick="deleteVehicleType(${type.id})" class="btn-delete" title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            }
            
            // Initialize realtime manager
            if (window.VehicleTypesRealtime && !vehicleTypesRealtimeManager) {
                vehicleTypesRealtimeManager = new window.VehicleTypesRealtime();
                vehicleTypesRealtimeManager.init(vehicleTypes);
            }
        }
    })
    .catch(error => {
        console.error('Error loading vehicle types:', error);
        tableBody.innerHTML = '<tr><td colspan="4" class="px-4 py-8 text-center text-sm text-red-600 dark:text-red-400">Error loading vehicle types. Please try again.</td></tr>';
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

function validateAddVehicleTypeForm() {
    const name = document.getElementById('modal-vehicle-type-name').value.trim();
    const addBtn = document.getElementById('add-vehicle-type-btn');
    
    if (!name) {
        addBtn.disabled = true;
        return false;
    }
    
    hideError('modal-vehicle-type-name', 'modal-vehicle-type-name-error');
    addBtn.disabled = false;
    return true;
}

function validateEditVehicleTypeForm() {
    const name = document.getElementById('edit-vehicle-type-name').value.trim();
    const updateBtn = document.getElementById('update-vehicle-type-btn');
    
    if (!name) {
        updateBtn.disabled = true;
        return false;
    }
    
    hideError('edit-vehicle-type-name', 'edit-vehicle-type-name-error');
    updateBtn.disabled = false;
    return true;
}

export function openAddVehicleTypeModal() {
    document.getElementById('add-vehicle-type-modal').classList.remove('hidden');
    const nameInput = document.getElementById('modal-vehicle-type-name');
    if (nameInput) nameInput.addEventListener('input', validateAddVehicleTypeForm);
    validateAddVehicleTypeForm();
}

export function closeAddVehicleTypeModal() {
    document.getElementById('add-vehicle-type-modal').classList.add('hidden');
    document.getElementById('modal-vehicle-type-name').value = '';
    document.getElementById('modal-vehicle-type-requires-plate').checked = true;
    hideError('modal-vehicle-type-name', 'modal-vehicle-type-name-error');
}

export function addVehicleType() {
    const vehicleTypeName = document.getElementById('modal-vehicle-type-name').value.trim();
    const requiresPlate = document.getElementById('modal-vehicle-type-requires-plate').checked;
    
    if (!vehicleTypeName) {
        showError('modal-vehicle-type-name', 'modal-vehicle-type-name-error', 'Please enter a vehicle type name');
        return;
    }
    
    hideError('modal-vehicle-type-name', 'modal-vehicle-type-name-error');
    
    fetch('/api/vehicle-types', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ 
            name: vehicleTypeName,
            requires_plate: requiresPlate 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.showSuccessModal('Success!', 'Vehicle type added successfully!');
            closeAddVehicleTypeModal();
            if (vehicleTypesRealtimeManager && data.data) {
                vehicleTypesRealtimeManager.addVehicleType(data.data);
            }
        } else {
            showError('modal-vehicle-type-name', 'modal-vehicle-type-name-error', data.message || 'Failed to add vehicle type');
        }
    })
    .catch(error => {
        console.error('Error adding vehicle type:', error);
        showError('modal-vehicle-type-name', 'modal-vehicle-type-name-error', 'Error adding vehicle type. Please try again.');
    });
}

export function editVehicleType(id, currentName, currentRequiresPlate = true) {
    // If only ID is provided, extract data from the table row
    if (currentName === undefined || currentName === null) {
        const row = document.querySelector(`tr[data-vehicle-type-id="${id}"]`);
        if (row) {
            // Try to get from data attributes first (more reliable)
            currentName = row.dataset.vehicleTypeName || '';
            currentRequiresPlate = row.dataset.vehicleTypeRequiresPlate === '1';
            
            // Fallback to extracting from cells if data attributes not available
            if (!currentName) {
                const nameCell = row.querySelector('td:nth-child(1) span');
                currentName = nameCell ? nameCell.textContent.trim() : '';
            }
            if (currentRequiresPlate === undefined) {
                const requiresPlateCell = row.querySelector('td:nth-child(2) span');
                currentRequiresPlate = requiresPlateCell ? requiresPlateCell.textContent.trim() === 'Yes' : true;
            }
        } else if (vehicleTypesRealtimeManager) {
            // Try to get from realtime manager
            const vehicleType = vehicleTypesRealtimeManager.vehicleTypes?.find(vt => vt.id === Number(id));
            if (vehicleType) {
                currentName = vehicleType.name || '';
                currentRequiresPlate = vehicleType.requires_plate !== undefined ? vehicleType.requires_plate : true;
            }
        }
    }
    
    document.getElementById('edit-vehicle-type-id').value = id;
    document.getElementById('edit-vehicle-type-name').value = currentName || '';
    document.getElementById('edit-vehicle-type-requires-plate').checked = currentRequiresPlate;
    document.getElementById('edit-vehicle-type-modal').classList.remove('hidden');
    const nameInput = document.getElementById('edit-vehicle-type-name');
    if (nameInput) nameInput.addEventListener('input', validateEditVehicleTypeForm);
    validateEditVehicleTypeForm();
}

export function closeEditVehicleTypeModal() {
    document.getElementById('edit-vehicle-type-modal').classList.add('hidden');
    document.getElementById('edit-vehicle-type-id').value = '';
    document.getElementById('edit-vehicle-type-name').value = '';
    document.getElementById('edit-vehicle-type-requires-plate').checked = true;
    hideError('edit-vehicle-type-name', 'edit-vehicle-type-name-error');
}

export function updateVehicleType() {
    const id = document.getElementById('edit-vehicle-type-id').value;
    const name = document.getElementById('edit-vehicle-type-name').value.trim();
    const requiresPlate = document.getElementById('edit-vehicle-type-requires-plate').checked;
    
    if (!name) {
        showError('edit-vehicle-type-name', 'edit-vehicle-type-name-error', 'Please enter a vehicle type name');
        return;
    }
    
    hideError('edit-vehicle-type-name', 'edit-vehicle-type-name-error');
    
    fetch(`/api/vehicle-types/${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ 
            name: name,
            requires_plate: requiresPlate 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.showSuccessModal('Success!', 'Vehicle type updated successfully!');
            closeEditVehicleTypeModal();
            if (vehicleTypesRealtimeManager && data.data) {
                vehicleTypesRealtimeManager.updateVehicleType(data.data);
            }
        } else {
            showError('edit-vehicle-type-name', 'edit-vehicle-type-name-error', data.message || 'Failed to update vehicle type');
        }
    })
    .catch(error => {
        console.error('Error updating vehicle type:', error);
        showError('edit-vehicle-type-name', 'edit-vehicle-type-name-error', 'Error updating vehicle type. Please try again.');
    });
}

let vehicleTypeToDelete = null;

export function deleteVehicleType(id) {
    vehicleTypeToDelete = id;
    document.getElementById('delete-vehicle-type-modal').classList.remove('hidden');
}

export function closeDeleteVehicleTypeModal() {
    document.getElementById('delete-vehicle-type-modal').classList.add('hidden');
    vehicleTypeToDelete = null;
}

export function confirmDeleteVehicleType() {
    if (!vehicleTypeToDelete) return;
    
    const vehicleTypeId = vehicleTypeToDelete;
    
    fetch(`/api/vehicle-types/${vehicleTypeId}`, {
        method: 'DELETE',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.showSuccessModal('Success!', 'Vehicle type deleted successfully!');
            closeDeleteVehicleTypeModal();
            if (vehicleTypesRealtimeManager) {
                vehicleTypesRealtimeManager.removeVehicleType({ id: vehicleTypeId });
            }
        } else {
            window.showErrorModal(data.message || 'Failed to delete vehicle type');
        }
    })
    .catch(error => {
        console.error('Error deleting vehicle type:', error);
        window.showErrorModal('Error deleting vehicle type. Please try again.');
    });
}

// Make functions globally available
window.openAddVehicleTypeModal = openAddVehicleTypeModal;
window.closeAddVehicleTypeModal = closeAddVehicleTypeModal;
window.addVehicleType = addVehicleType;
window.editVehicleType = editVehicleType;
window.closeEditVehicleTypeModal = closeEditVehicleTypeModal;
window.updateVehicleType = updateVehicleType;
window.deleteVehicleType = deleteVehicleType;
window.closeDeleteVehicleTypeModal = closeDeleteVehicleTypeModal;
window.confirmDeleteVehicleType = confirmDeleteVehicleType;

