// Reporter Roles Management
let reporterRoles = [];
let availableUserTypes = [];
let currentDeleteRoleId = null;
let reporterRolesRealtimeManager = null;
let reporterRolesLoaded = false;

// Initialize reporter roles when tab is shown
export function initReporterRoles() {
    if (!reporterRolesLoaded) {
        loadAvailableUserTypes();
        loadReporterRoles();
    }
}

// Load available user types
async function loadAvailableUserTypes() {
    try {
        const response = await fetch('/api/reporter-roles/user-types', {
            headers: {
                'Accept': 'application/json',
            }
        });
        
        if (!response.ok) throw new Error('Failed to load user types');
        
        availableUserTypes = await response.json();
    } catch (error) {
        console.error('Error loading user types:', error);
        window.showErrorModal('Failed to load user types');
    }
}

// Load all reporter roles
async function loadReporterRoles() {
    try {
        const response = await fetch('/api/reporter-roles', {
            headers: {
                'Accept': 'application/json',
            }
        });
        
        if (!response.ok) throw new Error('Failed to load reporter roles');
        
        reporterRoles = await response.json();
        reporterRolesLoaded = true;
        renderReporterRolesTable();
        
        // Initialize real-time manager if not already initialized
        if (window.ReporterRolesRealtime && !reporterRolesRealtimeManager) {
            reporterRolesRealtimeManager = new window.ReporterRolesRealtime();
            reporterRolesRealtimeManager.init(reporterRoles);
        }
    } catch (error) {
        console.error('Error loading reporter roles:', error);
        const tbody = document.getElementById('reporter-roles-table-body');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-sm text-red-500">
                        Failed to load reporter roles. Please refresh the page.
                    </td>
                </tr>
            `;
        }
    }
}

// Expose loadReporterRoles globally for real-time updates
window.loadReporterRoles = loadReporterRoles;

// Render reporter roles table
function renderReporterRolesTable() {
    const tbody = document.getElementById('reporter-roles-table-body');
    if (!tbody) return;
    
    if (reporterRoles.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="px-4 py-8 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    No reporter roles found. Click "Add Reporter Role" to create one.
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = reporterRoles.map(role => {
        const userTypeLabels = role.allowed_user_types.map(type => {
            const userType = availableUserTypes.find(ut => ut.value === type);
            return userType ? userType.label : type;
        }).join(', ');
        
        return `
            <tr class="hover:bg-gray-50 dark:hover:bg-[#161615]">
                <td class="px-4 py-3">
                    <div>
                        <p class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${escapeHtml(role.name)}</p>
                        ${role.description ? `<p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-1">${escapeHtml(role.description)}</p>` : ''}
                    </div>
                </td>
                <td class="px-4 py-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    ${userTypeLabels || 'None'}
                </td>
                <td class="px-4 py-3 text-center">
                    <button onclick="toggleReporterRoleStatus(${role.id})" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${role.is_active ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200'}">
                        ${role.is_active ? 'Active' : 'Inactive'}
                    </button>
                </td>
                <td class="px-4 py-3">
                    <div class="flex items-center justify-center gap-2">
                        <button onclick="viewReporterRole(${role.id})" class="btn-view" title="View">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                        <button onclick="openEditReporterRoleModal(${role.id})" class="btn-edit" title="Edit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <button onclick="openDeleteReporterRoleModal(${role.id})" class="btn-delete" title="Delete">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

// Open add modal
window.openAddReporterRoleModal = function() {
    document.getElementById('reporter-add-role-name').value = '';
    document.getElementById('reporter-add-role-description').value = '';
    document.getElementById('reporter-add-expiration-years').value = '';
    document.getElementById('reporter-add-role-is-active').checked = true;
    
    renderUserTypeCheckboxes('reporter-add-user-types-list', []);
    document.getElementById('add-reporter-role-modal').classList.remove('hidden');
};

// Close add modal
window.closeAddReporterRoleModal = function() {
    document.getElementById('add-reporter-role-modal').classList.add('hidden');
};

// Add reporter role
window.addReporterRole = async function(event) {
    event.preventDefault();
    
    const name = document.getElementById('reporter-add-role-name').value.trim();
    const description = document.getElementById('reporter-add-role-description').value.trim();
    const expirationYears = document.getElementById('reporter-add-expiration-years').value;
    const isActive = document.getElementById('reporter-add-role-is-active').checked;
    
    const selectedUserTypes = Array.from(document.querySelectorAll('#reporter-add-user-types-list input[type="checkbox"]:checked'))
        .map(cb => cb.value);
    
    if (selectedUserTypes.length === 0) {
        window.showErrorModal('Please select at least one user type that this role can report');
        return;
    }
    
    try {
        const response = await fetch('/api/reporter-roles', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                name,
                description,
                default_expiration_years: expirationYears || null,
                is_active: isActive,
                allowed_user_types: selectedUserTypes
            })
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            const errors = data.errors ? Object.values(data.errors).flat().join(', ') : data.message;
            throw new Error(errors || 'Failed to create reporter role');
        }
        
        window.showSuccessModal('Success!', 'Reporter role created successfully!');
        closeAddReporterRoleModal();
        loadReporterRoles();
    } catch (error) {
        console.error('Error creating reporter role:', error);
        window.showErrorModal(error.message);
    }
};

// Open edit modal
window.openEditReporterRoleModal = function(roleId) {
    const role = reporterRoles.find(r => r.id === roleId);
    if (!role) return;
    
    document.getElementById('reporter-edit-role-id').value = role.id;
    document.getElementById('reporter-edit-role-name').value = role.name;
    document.getElementById('reporter-edit-role-description').value = role.description || '';
    document.getElementById('reporter-edit-expiration-years').value = role.default_expiration_years || '';
    document.getElementById('reporter-edit-role-is-active').checked = role.is_active;
    
    renderUserTypeCheckboxes('reporter-edit-user-types-list', role.allowed_user_types);
    document.getElementById('edit-reporter-role-modal').classList.remove('hidden');
};

// Close edit modal
window.closeEditReporterRoleModal = function() {
    document.getElementById('edit-reporter-role-modal').classList.add('hidden');
};

// Update reporter role
window.updateReporterRole = async function(event) {
    event.preventDefault();
    
    const roleId = document.getElementById('reporter-edit-role-id').value;
    const name = document.getElementById('reporter-edit-role-name').value.trim();
    const description = document.getElementById('reporter-edit-role-description').value.trim();
    const expirationYears = document.getElementById('reporter-edit-expiration-years').value;
    const isActive = document.getElementById('reporter-edit-role-is-active').checked;
    
    const selectedUserTypes = Array.from(document.querySelectorAll('#reporter-edit-user-types-list input[type="checkbox"]:checked'))
        .map(cb => cb.value);
    
    if (selectedUserTypes.length === 0) {
        window.showErrorModal('Please select at least one user type that this role can report');
        return;
    }
    
    try {
        const response = await fetch(`/api/reporter-roles/${roleId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                name,
                description,
                default_expiration_years: expirationYears || null,
                is_active: isActive,
                allowed_user_types: selectedUserTypes
            })
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            const errors = data.errors ? Object.values(data.errors).flat().join(', ') : data.message;
            throw new Error(errors || 'Failed to update reporter role');
        }
        
        window.showSuccessModal('Success!', 'Reporter role updated successfully!');
        closeEditReporterRoleModal();
        loadReporterRoles();
    } catch (error) {
        console.error('Error updating reporter role:', error);
        window.showErrorModal(error.message);
    }
};

// View reporter role
window.viewReporterRole = function(roleId) {
    const role = reporterRoles.find(r => r.id === roleId);
    if (!role) return;
    
    document.getElementById('reporter-view-role-name').textContent = role.name;
    document.getElementById('reporter-view-role-description').textContent = role.description || 'No description provided';
    document.getElementById('reporter-view-reporters-count').textContent = `${role.reporters_count} reporter(s)`;
    
    const statusEl = document.getElementById('reporter-view-role-status');
    statusEl.innerHTML = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${role.is_active ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200'}">${role.is_active ? 'Active' : 'Inactive'}</span>`;
    
    const userTypesEl = document.getElementById('reporter-view-user-types');
    userTypesEl.innerHTML = role.allowed_user_types.map(type => {
        const userType = availableUserTypes.find(ut => ut.value === type);
        const label = userType ? userType.label : type;
        return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">${escapeHtml(label)}</span>`;
    }).join('');
    
    document.getElementById('view-reporter-role-modal').classList.remove('hidden');
};

// Close view modal
window.closeViewReporterRoleModal = function() {
    document.getElementById('view-reporter-role-modal').classList.add('hidden');
};

// Toggle role status
window.toggleReporterRoleStatus = async function(roleId) {
    try {
        const response = await fetch(`/api/reporter-roles/${roleId}/toggle-active`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || 'Failed to update status');
        }
        
        loadReporterRoles();
    } catch (error) {
        console.error('Error toggling status:', error);
        window.showErrorModal(error.message);
    }
};

// Open delete modal
window.openDeleteReporterRoleModal = function(roleId) {
    const role = reporterRoles.find(r => r.id === roleId);
    if (!role) return;
    
    currentDeleteRoleId = roleId;
    
    const message = role.reporters_count > 0
        ? `Cannot delete "${role.name}" because ${role.reporters_count} reporter(s) are currently assigned to this role. Please reassign them first.`
        : `Are you sure you want to delete the reporter role "${role.name}"? This action cannot be undone.`;
    
    document.getElementById('reporter-delete-role-message').textContent = message;
    
    // Hide delete button if role has reporters
    const deleteBtn = document.querySelector('#delete-reporter-role-modal .btn-danger');
    if (deleteBtn) {
        deleteBtn.style.display = role.reporters_count > 0 ? 'none' : 'inline-flex';
    }
    
    document.getElementById('delete-reporter-role-modal').classList.remove('hidden');
};

// Close delete modal
window.closeDeleteReporterRoleModal = function() {
    document.getElementById('delete-reporter-role-modal').classList.add('hidden');
    currentDeleteRoleId = null;
};

// Confirm delete
window.confirmDeleteReporterRole = async function() {
    if (!currentDeleteRoleId) return;
    
    try {
        const response = await fetch(`/api/reporter-roles/${currentDeleteRoleId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || 'Failed to delete reporter role');
        }
        
        window.showSuccessModal('Success!', 'Reporter role deleted successfully!');
        closeDeleteReporterRoleModal();
        loadReporterRoles();
    } catch (error) {
        console.error('Error deleting reporter role:', error);
        window.showErrorModal(error.message);
    }
};

// Render user type checkboxes
function renderUserTypeCheckboxes(containerId, selectedTypes = []) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    container.innerHTML = availableUserTypes.map(userType => {
        const isChecked = selectedTypes.includes(userType.value);
        return `
            <label class="flex items-center p-2 rounded hover:bg-gray-50 dark:hover:bg-[#161615] cursor-pointer">
                <input type="checkbox" value="${userType.value}" ${isChecked ? 'checked' : ''} class="form-checkbox">
                <span class="ml-2 text-sm text-[#1b1b18] dark:text-[#EDEDEC]">${escapeHtml(userType.label)}</span>
            </label>
        `;
    }).join('');
}

// Utility function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
