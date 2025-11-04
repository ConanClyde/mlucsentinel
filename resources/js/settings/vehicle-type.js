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
                    <tr class="hover:bg-gray-50 dark:hover:bg-[#161615]" data-id="${type.id}">
                        <td class="px-4 py-3 text-sm text-[#1b1b18] dark:text-[#EDEDEC]">${type.name}</td>
                        <td class="px-4 py-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                            ${type.requires_plate ? '<span class="px-2 py-1 text-xs font-medium text-green-700 bg-green-100 dark:bg-green-900/30 dark:text-green-400 rounded-full">Yes</span>' : '<span class="px-2 py-1 text-xs font-medium text-gray-700 bg-gray-100 dark:bg-gray-800 dark:text-gray-400 rounded-full">No</span>'}
                        </td>
                        <td class="px-4 py-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">${new Date(type.created_at).toLocaleDateString()}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="editVehicleType(${type.id}, '${type.name.replace(/'/g, "\\'")}', ${type.requires_plate ? 'true' : 'false'})" class="btn-edit" title="Edit">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.829-2.828z"></path>
                                    </svg>
                                </button>
                                <button onclick="deleteVehicleType(${type.id})" class="btn-delete" title="Delete">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
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

export function openAddVehicleTypeModal() {
    document.getElementById('add-vehicle-type-modal').classList.remove('hidden');
}

export function closeAddVehicleTypeModal() {
    document.getElementById('add-vehicle-type-modal').classList.add('hidden');
    document.getElementById('modal-vehicle-type-name').value = '';
    document.getElementById('modal-vehicle-type-requires-plate').checked = true;
}

export function addVehicleType() {
    const vehicleTypeName = document.getElementById('modal-vehicle-type-name').value.trim();
    const requiresPlate = document.getElementById('modal-vehicle-type-requires-plate').checked;
    
    if (!vehicleTypeName) {
        window.showErrorModal('Please enter a vehicle type name');
        return;
    }
    
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
            window.showErrorModal(data.message || 'Failed to add vehicle type');
        }
    })
    .catch(error => {
        console.error('Error adding vehicle type:', error);
        window.showErrorModal('Error adding vehicle type. Please try again.');
    });
}

export function editVehicleType(id, currentName, currentRequiresPlate = true) {
    document.getElementById('edit-vehicle-type-id').value = id;
    document.getElementById('edit-vehicle-type-name').value = currentName;
    document.getElementById('edit-vehicle-type-requires-plate').checked = currentRequiresPlate;
    document.getElementById('edit-vehicle-type-modal').classList.remove('hidden');
}

export function closeEditVehicleTypeModal() {
    document.getElementById('edit-vehicle-type-modal').classList.add('hidden');
    document.getElementById('edit-vehicle-type-id').value = '';
    document.getElementById('edit-vehicle-type-name').value = '';
    document.getElementById('edit-vehicle-type-requires-plate').checked = true;
}

export function updateVehicleType() {
    const id = document.getElementById('edit-vehicle-type-id').value;
    const name = document.getElementById('edit-vehicle-type-name').value.trim();
    const requiresPlate = document.getElementById('edit-vehicle-type-requires-plate').checked;
    
    if (!name) {
        window.showErrorModal('Please enter a vehicle type name');
        return;
    }
    
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
            window.showErrorModal(data.message || 'Failed to update vehicle type');
        }
    })
    .catch(error => {
        console.error('Error updating vehicle type:', error);
        window.showErrorModal('Error updating vehicle type. Please try again.');
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

