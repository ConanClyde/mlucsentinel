// Vehicles Management JavaScript
// Use global vehicles array set by blade template
window.vehicles = window.vehicles || [];
let currentPage = 1;
let itemsPerPage = 10;
let deleteVehicleId = null;

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize if we're on the vehicles page
    const vehiclesTableBody = document.getElementById('vehiclesTableBody');
    if (!vehiclesTableBody) {
        return; // Not on vehicles page, skip initialization
    }
    
    initializeVehiclesTable();
    initializeFilters();
    initializePagination();
    initializeModals();
});

function initializeVehiclesTable() {
    // Apply initial pagination
    applyPagination();
}

function initializeFilters() {
    const searchInput = document.getElementById('search-input');
    const typeFilter = document.getElementById('type-filter');
    const colorFilter = document.getElementById('color-filter');
    const userTypeFilter = document.getElementById('user-type-filter');
    const collegeFilter = document.getElementById('college-filter');
    const collegeFilterWrapper = document.getElementById('college-filter-wrapper');
    const resetButton = document.getElementById('reset-filters');

    // Add event listeners
    searchInput.addEventListener('input', function() {
        currentPage = 1;
        applyPagination();
    });
    
    typeFilter.addEventListener('change', function() {
        currentPage = 1;
        applyPagination();
    });
    
    colorFilter.addEventListener('change', function() {
        currentPage = 1;
        applyPagination();
    });
    
    if (userTypeFilter) {
        userTypeFilter.addEventListener('change', function() {
            const selectedUserType = this.value;
            
            // Show/hide college filter based on user type
            if (selectedUserType === 'student') {
                collegeFilterWrapper.classList.remove('hidden');
            } else {
                collegeFilterWrapper.classList.add('hidden');
                // Reset college filter when hiding
                if (collegeFilter) collegeFilter.value = '';
            }
            
            currentPage = 1;
            applyPagination();
        });
    }
    
    if (collegeFilter) {
        collegeFilter.addEventListener('change', function() {
            currentPage = 1;
            applyPagination();
        });
    }

    resetButton.addEventListener('click', function() {
        searchInput.value = '';
        typeFilter.value = '';
        colorFilter.value = '';
        if (userTypeFilter) userTypeFilter.value = '';
        if (collegeFilter) collegeFilter.value = '';
        if (collegeFilterWrapper) collegeFilterWrapper.classList.add('hidden');
        currentPage = 1;
        applyPagination();
    });
}

function initializePagination() {
    const paginationLimit = document.getElementById('pagination-limit');
    itemsPerPage = parseInt(paginationLimit.value);

    paginationLimit.addEventListener('change', function() {
        itemsPerPage = parseInt(this.value);
        currentPage = 1;
        applyPagination();
    });
}

function applyPagination() {
    const rows = document.querySelectorAll('#vehiclesTableBody tr');
    const searchInput = document.getElementById('search-input');
    const typeFilter = document.getElementById('type-filter');
    const colorFilter = document.getElementById('color-filter');
    const userTypeFilter = document.getElementById('user-type-filter');
    const collegeFilter = document.getElementById('college-filter');
    
    let visibleCount = 0;
    let totalFiltered = 0;
    
    // First pass: count total filtered rows
    rows.forEach((row) => {
        if (row.querySelector('td[colspan]')) return; // Skip empty row
        
        const ownerName = row.querySelector('td:nth-child(1)')?.textContent.toLowerCase() || '';
        const plateNo = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
        
        const searchTerm = searchInput.value.toLowerCase();
        const typeValue = typeFilter.value;
        const colorValue = colorFilter.value;
        const userTypeValue = userTypeFilter ? userTypeFilter.value : '';
        const collegeValue = collegeFilter ? collegeFilter.value : '';
        
        const vehicleId = row.getAttribute('data-id');
        const vehicle = window.vehicles.find(v => v.id == vehicleId);
        
        const matchesSearch = ownerName.includes(searchTerm) || plateNo.includes(searchTerm);
        const matchesType = typeValue === '' || (vehicle && vehicle.type_id == typeValue);
        const matchesColor = colorValue === '' || (vehicle && vehicle.color === colorValue);
        const matchesUserType = userTypeValue === '' || (vehicle && vehicle.user && vehicle.user.user_type === userTypeValue);
        const matchesCollege = collegeValue === '' || (vehicle && vehicle.user && vehicle.user.student && vehicle.user.student.college_id == collegeValue);
        
        if (matchesSearch && matchesType && matchesColor && matchesUserType && matchesCollege) {
            totalFiltered++;
        }
    });
    
    // Second pass: apply pagination
    rows.forEach((row) => {
        // Handle empty state row
        if (row.querySelector('td[colspan]')) {
            if (totalFiltered === 0) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
            return;
        }
        
        const ownerName = row.querySelector('td:nth-child(1)')?.textContent.toLowerCase() || '';
        const plateNo = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
        
        const searchTerm = searchInput.value.toLowerCase();
        const typeValue = typeFilter.value;
        const colorValue = colorFilter.value;
        const userTypeValue = userTypeFilter ? userTypeFilter.value : '';
        const collegeValue = collegeFilter ? collegeFilter.value : '';
        
        const vehicleId = row.getAttribute('data-id');
        const vehicle = window.vehicles.find(v => v.id == vehicleId);
        
        const matchesSearch = ownerName.includes(searchTerm) || plateNo.includes(searchTerm);
        const matchesType = typeValue === '' || (vehicle && vehicle.type_id == typeValue);
        const matchesColor = colorValue === '' || (vehicle && vehicle.color === colorValue);
        const matchesUserType = userTypeValue === '' || (vehicle && vehicle.user && vehicle.user.user_type === userTypeValue);
        const matchesCollege = collegeValue === '' || (vehicle && vehicle.user && vehicle.user.student && vehicle.user.student.college_id == collegeValue);
        
        if (matchesSearch && matchesType && matchesColor && matchesUserType && matchesCollege) {
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;
            
            if (visibleCount >= startIndex && visibleCount < endIndex) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update pagination info
    updatePaginationInfo(totalFiltered);
}

function updatePaginationInfo(totalCount) {
    const startIndex = totalCount > 0 ? (currentPage - 1) * itemsPerPage + 1 : 0;
    const endIndex = Math.min(currentPage * itemsPerPage, totalCount);
    const totalPages = Math.ceil(totalCount / itemsPerPage);
    
    document.getElementById('showing-start').textContent = startIndex;
    document.getElementById('showing-end').textContent = endIndex;
    document.getElementById('total-count').textContent = totalCount;
    
    // Update prev/next buttons
    const prevButton = document.getElementById('prev-page');
    const nextButton = document.getElementById('next-page');
    
    prevButton.disabled = currentPage === 1;
    prevButton.className = currentPage === 1 ? 'btn-pagination btn-paginationDisable' : 'btn-pagination btn-paginationArrow';
    
    nextButton.disabled = currentPage === totalPages || totalPages === 0;
    nextButton.className = currentPage === totalPages || totalPages === 0 ? 'btn-pagination btn-paginationDisable' : 'btn-pagination btn-paginationArrow';
    
    // Update page buttons (show only 3 pages at a time)
    const pageNumbersContainer = document.getElementById('page-numbers');
    pageNumbersContainer.innerHTML = '';
    
    // Calculate which 3 pages to show
    let startPage = Math.max(1, currentPage - 1);
    let endPage = Math.min(totalPages, startPage + 2);
    
    // Adjust if we're near the end
    if (endPage - startPage < 2) {
        startPage = Math.max(1, endPage - 2);
    }
    
    for (let i = startPage; i <= endPage; i++) {
        const button = document.createElement('button');
        button.textContent = i;
        button.className = i === currentPage ? 'btn-pagination btn-paginationActive' : 'btn-pagination btn-paginationNumber';
        button.onclick = () => goToPage(i);
        pageNumbersContainer.appendChild(button);
    }
}

function changePage(direction) {
    // First, we need to calculate the total filtered count
    const rows = document.querySelectorAll('#vehiclesTableBody tr');
    const searchInput = document.getElementById('search-input');
    const typeFilter = document.getElementById('type-filter');
    const colorFilter = document.getElementById('color-filter');
    const userTypeFilter = document.getElementById('user-type-filter');
    const collegeFilter = document.getElementById('college-filter');
    
    let totalFiltered = 0;
    
    rows.forEach((row) => {
        if (row.querySelector('td[colspan]')) return; // Skip empty row
        
        const ownerName = row.querySelector('td:nth-child(1)')?.textContent.toLowerCase() || '';
        const plateNo = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
        
        const searchTerm = searchInput.value.toLowerCase();
        const typeValue = typeFilter.value;
        const colorValue = colorFilter.value;
        const userTypeValue = userTypeFilter ? userTypeFilter.value : '';
        const collegeValue = collegeFilter ? collegeFilter.value : '';
        
        const vehicleId = row.getAttribute('data-id');
        const vehicle = window.vehicles.find(v => v.id == vehicleId);
        
        const matchesSearch = ownerName.includes(searchTerm) || plateNo.includes(searchTerm);
        const matchesType = typeValue === '' || (vehicle && vehicle.type_id == typeValue);
        const matchesColor = colorValue === '' || (vehicle && vehicle.color === colorValue);
        const matchesUserType = userTypeValue === '' || (vehicle && vehicle.user && vehicle.user.user_type === userTypeValue);
        const matchesCollege = collegeValue === '' || (vehicle && vehicle.user && vehicle.user.student && vehicle.user.student.college_id == collegeValue);
        
        if (matchesSearch && matchesType && matchesColor && matchesUserType && matchesCollege) {
            totalFiltered++;
        }
    });
    
    const totalPages = Math.ceil(totalFiltered / itemsPerPage);
    
    currentPage += direction;
    if (currentPage < 1) currentPage = 1;
    if (currentPage > totalPages && totalPages > 0) currentPage = totalPages;
    
    applyPagination();
}

function goToPage(page) {
    currentPage = page;
    applyPagination();
}

function initializeModals() {
    // No edit functionality - only view and delete
}

// View Vehicle
function viewVehicle(id) {
    const vehicle = window.vehicles.find(v => v.id === id);
    if (!vehicle) return;
    
    const modal = document.getElementById('viewModal');
    const detailsDiv = document.getElementById('view-vehicle-details');
    
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
    
    detailsDiv.innerHTML = `
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div>
                    <p class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Owner</p>
                    <p class="text-base text-[#1b1b18] dark:text-[#EDEDEC]">${vehicle.user?.first_name} ${vehicle.user?.last_name}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">User Type</p>
                    <p class="text-base text-[#1b1b18] dark:text-[#EDEDEC]">${vehicle.user?.user_type ? vehicle.user.user_type.charAt(0).toUpperCase() + vehicle.user.user_type.slice(1) : 'N/A'}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Vehicle Type</p>
                    <p class="text-base text-[#1b1b18] dark:text-[#EDEDEC]">${vehicle.type?.name || 'N/A'}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Plate Number</p>
                    <p class="text-base text-[#1b1b18] dark:text-[#EDEDEC]">${vehicle.plate_no || 'No Plate'}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Registration Date</p>
                    <p class="text-base text-[#1b1b18] dark:text-[#EDEDEC]">${new Date(vehicle.created_at).toLocaleDateString()}</p>
                </div>
            </div>
            <div class="space-y-4">
                ${vehicle.sticker ? `
                <div>
                    <p class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] mb-2">Sticker Preview</p>
                    <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A]">
                        <img src="${vehicle.sticker}" alt="Sticker Preview" class="w-full h-auto max-w-sm mx-auto rounded">
                    </div>
                </div>
                ` : ''}
            </div>
        </div>
    `;
    
    modal.classList.remove('hidden');
}

function closeViewModal() {
    const modal = document.getElementById('viewModal');
    modal.classList.add('hidden');
}

// Delete Vehicle
function deleteVehicle(id) {
    const vehicle = window.vehicles.find(v => v.id === id);
    if (!vehicle) {
        console.error('Vehicle not found:', id);
        return;
    }
    
    const ownerName = `${vehicle.user?.first_name} ${vehicle.user?.last_name}`;
    const plateInfo = vehicle.plate_no || `${vehicle.color}-${vehicle.number}`;
    
    document.getElementById('deleteModalMessage').textContent = 
        `Are you sure you want to delete the vehicle ${plateInfo} owned by ${ownerName}? This action cannot be undone.`;
    
    // Store the ID for confirmation
    deleteVehicleId = id;
    
    const modal = document.getElementById('deleteModal');
    modal.classList.remove('hidden');
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    modal.classList.add('hidden');
    deleteVehicleId = null;
}

function confirmDelete() {
    if (!deleteVehicleId) return;
    
    // Mark this action BEFORE sending request - critical for notification filtering
    if (window.realtimeManager && window.realtimeManager.markUserAction) {
        window.realtimeManager.markUserAction(deleteVehicleId);
    }
    
    fetch(`/vehicles/${deleteVehicleId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeDeleteModal();
            // Table will update via real-time broadcast
        } else {
            alert(data.message || 'Failed to delete vehicle');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the vehicle');
    });
}

// Export to CSV
function exportToCSV() {
    const rows = [];
    const headers = ['Owner', 'User Type', 'Vehicle Type', 'Plate No.', 'Sticker', 'Status', 'Created'];
    rows.push(headers);
    
    window.vehicles.forEach(vehicle => {
        const row = [
            `${vehicle.user?.first_name} ${vehicle.user?.last_name}`,
            vehicle.user?.user_type || '',
            vehicle.type?.name || '',
            vehicle.plate_no || 'No Plate',
            `${vehicle.color}-${vehicle.number}`,
            vehicle.is_active ? 'Active' : 'Inactive',
            new Date(vehicle.created_at).toLocaleDateString()
        ];
        rows.push(row);
    });
    
    const csvContent = rows.map(e => e.join(",")).join("\n");
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `vehicles_${new Date().toISOString().split('T')[0]}.csv`;
    a.click();
    window.URL.revokeObjectURL(url);
}

// Helper Functions
function showNotification(type, message) {
    // You can implement a toast notification system here
    alert(message);
}

// Export functions to window
window.viewVehicle = viewVehicle;
window.closeViewModal = closeViewModal;
window.deleteVehicle = deleteVehicle;
window.closeDeleteModal = closeDeleteModal;
window.confirmDelete = confirmDelete;
window.exportToCSV = exportToCSV;
window.changePage = changePage;
window.goToPage = goToPage;

