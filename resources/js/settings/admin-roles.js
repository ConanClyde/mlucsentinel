/**
 * Admin Roles & Privileges Settings Management
 */

let rolesLoaded = false;
let privilegesData = null;
let roleToDelete = null;
let adminRolesRealtimeBound = false;

export function initializeAdminRoles() {
    loadRoles();
    loadPrivileges();
    bindAdminRolesRealtime();
}

export function loadRoles() {
    if (rolesLoaded) return;
    
    const tableBody = document.getElementById('admin-roles-table-body');
    if (!tableBody) return;
    
    rolesLoaded = true;
    tableBody.innerHTML = '<tr><td colspan="5" class="px-4 py-8 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">Loading roles...</td></tr>';
    
    fetch('/api/admin-roles', {
        headers: {
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const roles = data.roles;
            
            if (roles.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="5" class="px-4 py-8 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">No roles found. Click Add Role button to create one.</td></tr>';
            } else {
                tableBody.innerHTML = roles.map(role => {
                    const privilegeCount = role.privileges ? role.privileges.length : 0;
                    const privilegesList = role.privileges ? role.privileges.map(p => p.display_name).slice(0, 3).join(', ') : '';
                    const moreText = privilegeCount > 3 ? ` +${privilegeCount - 3} more` : '';
                    
                    return `
                        <tr class="hover:bg-gray-50 dark:hover:bg-[#2a2a2a] transition-colors" data-role-id="${role.id}">
                            <td class="px-4 py-3">
                                <span class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${role.name}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">${role.description || 'No description'}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]" title="${role.privileges ? role.privileges.map(p => p.display_name).join(', ') : 'None'}">
                                    ${privilegeCount > 0 ? `${privilegesList}${moreText}` : 'No privileges'}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${role.is_active ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200'}">
                                    ${role.is_active ? 'Active' : 'Inactive'}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick="viewRole(${role.id})" class="btn-view" title="View Details">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                    <button onclick="editRole(${role.id})" class="btn-edit" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button onclick="deleteRole(${role.id}, '${role.name.replace(/'/g, "\\'")}')}" class="btn-delete" title="Delete">
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
        }
    })
    .catch(error => {
        console.error('Error loading roles:', error);
        tableBody.innerHTML = '<tr><td colspan="5" class="px-4 py-8 text-center text-sm text-red-600 dark:text-red-400">Error loading roles. Please try again.</td></tr>';
    });
}

export function loadPrivileges() {
    if (privilegesData) return;
    
    fetch('/api/admin-roles/privileges', {
        headers: {
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            privilegesData = data.privileges;
        }
    })
    .catch(error => {
        console.error('Error loading privileges:', error);
    });
}

function renderPrivileges(containerId, selectedPrivilegeIds = []) {
    const container = document.getElementById(containerId);
    if (!container || !privilegesData) return;
    
    let html = '';

    // Helper: determine if any privilege in a set is selected
    const anySelected = (names = []) => {
        const all = names.map(name => findPrivilegeByName(name)).filter(Boolean);
        return all.some(p => selectedPrivilegeIds.includes(p.id));
    };

    // Helper: find privilege object by name across all categories
    const findPrivilegeByName = (name) => {
        for (const [, list] of Object.entries(privilegesData)) {
            const p = list.find(x => x.name === name);
            if (p) return p;
        }
        return null;
    };

    // DASHBOARD group
    if (privilegesData.dashboard) {
        const dashNames = privilegesData.dashboard.map(p => p.name);
        const dashChecked = anySelected(dashNames);
        html += `
            <div class="mb-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" class="form-checkbox group-toggle" data-group="dashboard" ${dashChecked ? 'checked' : ''}>
                    <span class="text-sm font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Dashboard</span>
                </label>
                <div class="mt-2 ml-5" data-group-body="dashboard" style="${dashChecked ? '' : 'display:none'}">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        ${privilegesData.dashboard.map(priv => {
                            const checked = selectedPrivilegeIds.includes(priv.id);
                            return `
                                <label class="flex items-start space-x-2">
                                    <input type="checkbox" name="privileges[]" value="${priv.id}" ${checked ? 'checked' : ''} class="form-checkbox mt-1" data-in-group="dashboard">
                                    <div class="flex flex-col">
                                        <span class="text-sm text-[#1b1b18] dark:text-[#EDEDEC]">${priv.display_name}</span>
                                        ${priv.description ? `<span class="text-xs text-[#706f6c] dark:text-[#A1A09A]">${priv.description}</span>` : ''}
                                    </div>
                                </label>`;
                        }).join('')}
                    </div>
                </div>
            </div>
        `;
    }
    
    // USER MANAGEMENT group (table)
    if (privilegesData.users) {
        const userPrivileges = privilegesData.users;
        const userTypes = ['students', 'staff', 'security', 'stakeholders', 'reporters', 'administrators'];
        // detect if any users privilege is selected
        const usersChecked = (function(){
            return userPrivileges.some(p => selectedPrivilegeIds.includes(p.id));
        })();
        
        html += `
            <div class="mb-6">
                <div class="flex items-center justify-between mb-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" class="form-checkbox group-toggle" data-group="users" ${usersChecked ? 'checked' : ''}>
                        <span class="text-sm font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">User Management</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer text-xs">
                        <input type="checkbox" class="form-checkbox group-toggle" data-group="registration" ${anySelected(userPrivileges.filter(p=>p.name.startsWith('register_')).map(p=>p.name)) ? 'checked' : ''}>
                        <span class="text-[#706f6c] dark:text-[#A1A09A]">Registration</span>
                    </label>
                </div>
                <div class="overflow-x-auto" data-group-body="users" style="${usersChecked ? '' : 'display:none'}">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
                                <th class="px-3 py-2 text-left text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase">User Type</th>
                                <th class="px-3 py-2 text-center text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase">View</th>
                                <th class="px-3 py-2 text-center text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase">Register</th>
                                <th class="px-3 py-2 text-center text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase">Manage</th>
                            </tr>
                        </thead>
                        <tbody>
        `;
        
        userTypes.forEach(userType => {
            const capitalized = userType.charAt(0).toUpperCase() + userType.slice(1);
            const viewPrivilege = userPrivileges.find(p => p.name === `view_${userType}`);
            const registerPrivilege = userPrivileges.find(p => p.name === `register_${userType}`);
            const editPrivilege = userPrivileges.find(p => p.name === `edit_${userType}`);
            const deletePrivilege = userPrivileges.find(p => p.name === `delete_${userType}`);
            
            const viewChecked = viewPrivilege && selectedPrivilegeIds.includes(viewPrivilege.id);
            const registerChecked = registerPrivilege && selectedPrivilegeIds.includes(registerPrivilege.id);
            const editChecked = editPrivilege && selectedPrivilegeIds.includes(editPrivilege.id);
            const deleteChecked = deletePrivilege && selectedPrivilegeIds.includes(deletePrivilege.id);
            const manageChecked = editChecked || deleteChecked; // Show checked if either edit or delete is checked
            
            html += `
                <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A] hover:bg-gray-50 dark:hover:bg-[#2a2a2a]">
                    <td class="px-3 py-2 text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${capitalized}</td>
                    <td class="px-3 py-2 text-center">
                        ${viewPrivilege ? `
                            <input type="checkbox" name="privileges[]" value="${viewPrivilege.id}" ${viewChecked ? 'checked' : ''} class="form-checkbox" title="${viewPrivilege.display_name}" data-in-group="users">
                        ` : ''}
                    </td>
                    <td class="px-3 py-2 text-center">
                        ${registerPrivilege ? `
                            <input type="checkbox" name="privileges[]" value="${registerPrivilege.id}" ${registerChecked ? 'checked' : ''} class="form-checkbox" title="${registerPrivilege.display_name}" data-in-group="users" data-in-group-2="registration">
                        ` : ''}
                    </td>
                    <td class="px-3 py-2 text-center">
                        ${editPrivilege && deletePrivilege ? `
                            <input type="checkbox" class="form-checkbox manage-toggle" data-user-type="${userType}" ${manageChecked ? 'checked' : ''} title="Manage ${capitalized} (Edit & Delete)">
                            <input type="checkbox" name="privileges[]" value="${editPrivilege.id}" ${editChecked ? 'checked' : ''} class="manage-edit-${userType}" style="display:none;">
                            <input type="checkbox" name="privileges[]" value="${deletePrivilege.id}" ${deleteChecked ? 'checked' : ''} class="manage-delete-${userType}" style="display:none;">
                        ` : ''}
                    </td>
                </tr>
            `;
        });
        
        // Add Pending Registrations as a simple checkbox row
        const pendingRegistrationsPrivilege = userPrivileges.find(p => p.name === 'manage_pending_registrations');
        if (pendingRegistrationsPrivilege) {
            const pendingChecked = selectedPrivilegeIds.includes(pendingRegistrationsPrivilege.id);
            html += `
                <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A] hover:bg-gray-50 dark:hover:bg-[#2a2a2a]">
                    <td class="px-3 py-2 text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]" colspan="4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="privileges[]" value="${pendingRegistrationsPrivilege.id}" ${pendingChecked ? 'checked' : ''} class="form-checkbox" title="${pendingRegistrationsPrivilege.display_name}" data-in-group="users">
                            <span class="text-sm text-[#1b1b18] dark:text-[#EDEDEC]">${pendingRegistrationsPrivilege.display_name}</span>
                        </label>
                    </td>
                </tr>
            `;
        }
        
        html += `
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }
    
    // OTHER groups by category (skip 'dashboard' because it's rendered above)
    for (const [category, privileges] of Object.entries(privilegesData)) {
        if (category === 'users' || category === 'dashboard') continue; // Already handled above or rendered separately
        if (!['reports','vehicles','stickers','patrol','campus_map'].includes(category)) continue; // only major groups
        
        // Special handling for campus_map - single checkbox
        if (category === 'campus_map' && privileges.length > 0) {
            const campusMapPrivilege = privileges[0]; // Should only be one privilege now
            const isChecked = selectedPrivilegeIds.includes(campusMapPrivilege.id);
            html += `
                <div class="mb-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="privileges[]" value="${campusMapPrivilege.id}" ${isChecked ? 'checked' : ''} class="form-checkbox">
                        <div class="flex flex-col">
                            <span class="text-sm font-semibold text-[#1b1b18] dark:text-[#EDEDEC] capitalize">${category.replace(/_/g, ' ')}</span>
                            ${campusMapPrivilege.description ? `<span class="text-xs text-[#706f6c] dark:text-[#A1A09A]">${campusMapPrivilege.description}</span>` : ''}
                        </div>
                    </label>
                </div>
            `;
            continue;
        }
        
        const names = privileges.map(p=>p.name);
        const groupChecked = anySelected(names);
        html += `
            <div class="mb-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" class="form-checkbox group-toggle" data-group="${category}" ${groupChecked ? 'checked' : ''}>
                    <span class="text-sm font-semibold text-[#1b1b18] dark:text-[#EDEDEC] capitalize">${category.replace(/_/g, ' ')}</span>
                </label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 ml-5 mt-2" data-group-body="${category}" style="${groupChecked ? '' : 'display:none'}">
        `;
        
        privileges.forEach(privilege => {
            const isChecked = selectedPrivilegeIds.includes(privilege.id);
            html += `
                <label class="flex items-start space-x-2 cursor-pointer">
                    <input type="checkbox" name="privileges[]" value="${privilege.id}" ${isChecked ? 'checked' : ''} class="form-checkbox mt-1" data-in-group="${category}">
                    <div class="flex flex-col">
                        <span class="text-sm text-[#1b1b18] dark:text-[#EDEDEC]">${privilege.display_name}</span>
                        ${privilege.description ? `<span class="text-xs text-[#706f6c] dark:text-[#A1A09A]">${privilege.description}</span>` : ''}
                    </div>
                </label>
            `;
        });
        
        html += `
                </div>
            </div>
        `;
    }
    
    container.innerHTML = html;

    // Attach group handlers
    attachGroupHandlers(containerId);
    
    // Attach manage toggle handlers (combines edit and delete)
    attachManageHandlers(containerId);
}

export function openAddRoleModal() {
    document.getElementById('modal-role-name').value = '';
    document.getElementById('modal-role-description').value = '';
    document.getElementById('modal-role-is-active').checked = true;
    
    renderPrivileges('modal-privileges-list', []);
    renderReportTargets('modal-report-targets', []);
    document.getElementById('add-role-modal').classList.remove('hidden');
}

export function closeAddRoleModal() {
    document.getElementById('add-role-modal').classList.add('hidden');
}

export function addRole() {
    const name = document.getElementById('modal-role-name').value.trim();
    const description = document.getElementById('modal-role-description').value.trim();
    const isActive = document.getElementById('modal-role-is-active').checked;
    
    const privilegeCheckboxes = document.querySelectorAll('#modal-privileges-list input[name="privileges[]"]:checked');
    const privileges = Array.from(privilegeCheckboxes).map(cb => parseInt(cb.value));

    const reportTargets = Array.from(document.querySelectorAll('#modal-report-targets input[name="report_targets[]"]:checked')).map(cb => cb.value);
    
    if (!name) {
        window.showErrorModal('Please enter a role name');
        return;
    }
    
    // Calculate quick flags based on selected privileges
    const selectedPrivilegeNames = Array.from(privilegeCheckboxes).map(cb => {
        const privilege = privilegesData.users?.find(p => p.id === parseInt(cb.value));
        return privilege?.name || '';
    });
    
    const hasRegister = selectedPrivilegeNames.some(name => name.startsWith('register_'));
    const hasEdit = selectedPrivilegeNames.some(name => name.startsWith('edit_'));
    const hasDelete = selectedPrivilegeNames.some(name => name.startsWith('delete_'));
    
    fetch('/api/admin-roles', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ 
            name: name,
            description: description,
            is_active: isActive,
            can_register_users: hasRegister,
            can_edit_users: hasEdit,
            can_delete_users: hasDelete,
            privileges: privileges,
            report_targets: reportTargets
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.showSuccessModal('Success!', 'Admin role created successfully!');
            closeAddRoleModal();
            rolesLoaded = false;
            loadRoles();
        } else {
            window.showErrorModal(data.message || 'Failed to create role');
        }
    })
    .catch(error => {
        console.error('Error creating role:', error);
        window.showErrorModal('Error creating role. Please try again.');
    });
}

export function viewRole(id) {
    // Find the role data
    fetch(`/api/admin-roles`, {
        headers: {
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const role = data.roles.find(r => r.id === id);
            if (role) {
                document.getElementById('view-role-name').textContent = role.name;
                document.getElementById('view-role-description').textContent = role.description || 'No description provided';
                document.getElementById('view-role-status').textContent = role.is_active ? 'Active' : 'Inactive';
                document.getElementById('view-role-status').className = `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${role.is_active ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200'}`;
                
                renderViewPrivileges('view-privileges-list', role.privileges || []);
                renderViewReportTargets('view-report-targets-list', role.report_targets || []);
                
                document.getElementById('view-role-modal').classList.remove('hidden');
            }
        }
    })
    .catch(error => {
        console.error('Error loading role:', error);
        window.showErrorModal('Error loading role details. Please try again.');
    });
}

export function closeViewRoleModal() {
    document.getElementById('view-role-modal').classList.add('hidden');
}

function renderViewPrivileges(containerId, privileges) {
    const container = document.getElementById(containerId);
    if (!container || !privilegesData) return;
    
    if (privileges.length === 0) {
        container.innerHTML = '<p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">No privileges assigned</p>';
        return;
    }
    
    // Separate user privileges from others
    const userPrivileges = privileges.filter(p => p.category === 'users');
    const otherPrivileges = privileges.filter(p => p.category !== 'users');
    
    let html = '';
    
    // User Management Privileges - Table Format
    if (userPrivileges.length > 0) {
        const userTypes = ['students', 'staff', 'security', 'stakeholders', 'reporters', 'administrators'];
        
        html += `
            <div class="mb-6">
                <h4 class="text-sm font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-3">User Management</h4>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
                                <th class="px-3 py-2 text-left text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase">User Type</th>
                                <th class="px-3 py-2 text-center text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase">View</th>
                                <th class="px-3 py-2 text-center text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase">Register</th>
                                <th class="px-3 py-2 text-center text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase">Manage</th>
                            </tr>
                        </thead>
                        <tbody>
        `;
        
        userTypes.forEach(userType => {
            const capitalized = userType.charAt(0).toUpperCase() + userType.slice(1);
            const hasView = userPrivileges.some(p => p.name === `view_${userType}`);
            const hasRegister = userPrivileges.some(p => p.name === `register_${userType}`);
            const hasEdit = userPrivileges.some(p => p.name === `edit_${userType}`);
            const hasDelete = userPrivileges.some(p => p.name === `delete_${userType}`);
            const hasManage = hasEdit || hasDelete; // Show checkmark if either edit or delete is present
            
            html += `
                <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A] hover:bg-gray-50 dark:hover:bg-[#2a2a2a]">
                    <td class="px-3 py-2 text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${capitalized}</td>
                    <td class="px-3 py-2 text-center">
                        ${hasView ? `
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd"></path>
                            </svg>
                        ` : `
                            <svg class="w-5 h-5 text-red-500 dark:text-red-400 mx-auto" fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm-1.72 6.97a.75.75 0 10-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 101.06 1.06L12 13.06l1.72 1.72a.75.75 0 101.06-1.06L13.06 12l1.72-1.72a.75.75 0 10-1.06-1.06L12 10.94l-1.72-1.72z" clip-rule="evenodd"></path>
                            </svg>
                        `}
                    </td>
                    <td class="px-3 py-2 text-center">
                        ${hasRegister ? `
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd"></path>
                            </svg>
                        ` : `
                            <svg class="w-5 h-5 text-red-500 dark:text-red-400 mx-auto" fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm-1.72 6.97a.75.75 0 10-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 101.06 1.06L12 13.06l1.72 1.72a.75.75 0 101.06-1.06L13.06 12l1.72-1.72a.75.75 0 10-1.06-1.06L12 10.94l-1.72-1.72z" clip-rule="evenodd"></path>
                            </svg>
                        `}
                    </td>
                    <td class="px-3 py-2 text-center">
                        ${hasManage ? `
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd"></path>
                            </svg>
                        ` : `
                            <svg class="w-5 h-5 text-red-500 dark:text-red-400 mx-auto" fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm-1.72 6.97a.75.75 0 10-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 101.06 1.06L12 13.06l1.72 1.72a.75.75 0 101.06-1.06L13.06 12l1.72-1.72a.75.75 0 10-1.06-1.06L12 10.94l-1.72-1.72z" clip-rule="evenodd"></path>
                            </svg>
                        `}
                    </td>
                </tr>
            `;
        });
        
        html += `
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }
    
    // Other Privileges - Normal List Format
    if (otherPrivileges.length > 0) {
        const privilegesByCategory = {};
        otherPrivileges.forEach(privilege => {
            if (!privilegesByCategory[privilege.category]) {
                privilegesByCategory[privilege.category] = [];
            }
            privilegesByCategory[privilege.category].push(privilege);
        });
        
        for (const [category, privs] of Object.entries(privilegesByCategory)) {
            html += `
                <div class="mb-4">
                    <h4 class="text-sm font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2 capitalize">${category.replace(/_/g, ' ')}</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 ml-4">
            `;
            
            privs.forEach(privilege => {
                html += `
                    <div class="flex items-start space-x-2">
                        <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd"></path>
                        </svg>
                        <div class="flex flex-col">
                            <span class="text-sm text-[#1b1b18] dark:text-[#EDEDEC]">${privilege.display_name}</span>
                            ${privilege.description ? `<span class="text-xs text-[#706f6c] dark:text-[#A1A09A]">${privilege.description}</span>` : ''}
                        </div>
                    </div>
                `;
            });
            
            html += `
                    </div>
                </div>
            `;
        }
    }
    
    container.innerHTML = html;
}

export function editRole(id) {
    // Find the role data
    fetch(`/api/admin-roles`, {
        headers: {
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const role = data.roles.find(r => r.id === id);
            if (role) {
                document.getElementById('edit-role-id').value = role.id;
                document.getElementById('edit-role-name').value = role.name;
                document.getElementById('edit-role-description').value = role.description || '';
                document.getElementById('edit-role-is-active').checked = role.is_active;
                
                const selectedPrivilegeIds = role.privileges ? role.privileges.map(p => p.id) : [];
                renderPrivileges('edit-privileges-list', selectedPrivilegeIds);
                renderReportTargets('edit-report-targets', role.report_targets || []);
                
                document.getElementById('edit-role-modal').classList.remove('hidden');
            }
        }
    })
    .catch(error => {
        console.error('Error loading role:', error);
        window.showErrorModal('Error loading role details. Please try again.');
    });
}

export function closeEditRoleModal() {
    document.getElementById('edit-role-modal').classList.add('hidden');
}

export function updateRole() {
    const id = document.getElementById('edit-role-id').value;
    const name = document.getElementById('edit-role-name').value.trim();
    const description = document.getElementById('edit-role-description').value.trim();
    const isActive = document.getElementById('edit-role-is-active').checked;
    
    const privilegeCheckboxes = document.querySelectorAll('#edit-privileges-list input[name="privileges[]"]:checked');
    const privileges = Array.from(privilegeCheckboxes).map(cb => parseInt(cb.value));
    const reportTargets = Array.from(document.querySelectorAll('#edit-report-targets input[name="report_targets[]"]:checked')).map(cb => cb.value);
    
    if (!name) {
        window.showErrorModal('Please enter a role name');
        return;
    }
    
    // Calculate quick flags based on selected privileges
    const selectedPrivilegeNames = Array.from(privilegeCheckboxes).map(cb => {
        const privilege = privilegesData.users?.find(p => p.id === parseInt(cb.value));
        return privilege?.name || '';
    });
    
    const hasRegister = selectedPrivilegeNames.some(name => name.startsWith('register_'));
    const hasEdit = selectedPrivilegeNames.some(name => name.startsWith('edit_'));
    const hasDelete = selectedPrivilegeNames.some(name => name.startsWith('delete_'));
    
    fetch(`/api/admin-roles/${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ 
            name: name,
            description: description,
            is_active: isActive,
            can_register_users: hasRegister,
            can_edit_users: hasEdit,
            can_delete_users: hasDelete,
            privileges: privileges
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.showSuccessModal('Success!', 'Admin role updated successfully!');
            closeEditRoleModal();
            rolesLoaded = false;
            loadRoles();
        } else {
            window.showErrorModal(data.message || 'Failed to update role');
        }
    })
    .catch(error => {
        console.error('Error updating role:', error);
        window.showErrorModal('Error updating role. Please try again.');
    });
}

export function deleteRole(id, name) {
    roleToDelete = id;
    document.getElementById('deleteRoleMessage').textContent = `Are you sure you want to delete the "${name}" role? This action cannot be undone.`;
    document.getElementById('delete-role-modal').classList.remove('hidden');
}

export function closeDeleteRoleModal() {
    document.getElementById('delete-role-modal').classList.add('hidden');
    roleToDelete = null;
}

export function confirmDeleteRole() {
    if (!roleToDelete) return;
    
    const roleId = roleToDelete;
    
    fetch(`/api/admin-roles/${roleId}`, {
        method: 'DELETE',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.showSuccessModal('Success!', 'Admin role deleted successfully!');
            closeDeleteRoleModal();
            rolesLoaded = false;
            loadRoles();
        } else {
            window.showErrorModal(data.message || 'Failed to delete role');
        }
    })
    .catch(error => {
        console.error('Error deleting role:', error);
        window.showErrorModal('Error deleting role. Please try again.');
    });
}

// Make functions globally available
window.openAddRoleModal = openAddRoleModal;
window.closeAddRoleModal = closeAddRoleModal;
window.addRole = addRole;
window.viewRole = viewRole;
window.closeViewRoleModal = closeViewRoleModal;
window.editRole = editRole;
window.closeEditRoleModal = closeEditRoleModal;
window.updateRole = updateRole;
window.deleteRole = deleteRole;
window.closeDeleteRoleModal = closeDeleteRoleModal;
window.confirmDeleteRole = confirmDeleteRole;

// Helpers
function attachGroupHandlers(containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    container.querySelectorAll('.group-toggle').forEach(toggle => {
        const group = toggle.getAttribute('data-group');
        const body = container.querySelector(`[data-group-body="${group}"]`);
        toggle.addEventListener('change', () => {
            const checked = toggle.checked;
            if (body) body.style.display = checked ? '' : 'none';
            // toggle child checkboxes tagged with group
            container.querySelectorAll(`input[name="privileges[]"][data-in-group="${group}"]`).forEach(cb => {
                cb.checked = checked;
            });
            // special: registration master toggles only register_* within users table
            if (group === 'registration') {
                container.querySelectorAll('input[name="privileges[]"][data-in-group-2="registration"]').forEach(cb => {
                    cb.checked = checked;
                });
            }
            // special: users group toggle also affects manage toggles (edit & delete)
            if (group === 'users') {
                container.querySelectorAll('.manage-toggle').forEach(manageToggle => {
                    manageToggle.checked = checked;
                    // Trigger change event to sync hidden edit/delete checkboxes
                    manageToggle.dispatchEvent(new Event('change'));
                });
            }
        });
    });
}

function attachManageHandlers(containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    container.querySelectorAll('.manage-toggle').forEach(toggle => {
        const userType = toggle.getAttribute('data-user-type');
        const editCheckbox = container.querySelector(`.manage-edit-${userType}`);
        const deleteCheckbox = container.querySelector(`.manage-delete-${userType}`);
        
        // Set initial state
        if (editCheckbox && deleteCheckbox) {
            const editChecked = editCheckbox.checked;
            const deleteChecked = deleteCheckbox.checked;
            toggle.checked = editChecked || deleteChecked;
        }
        
        // Handle manage toggle change
        toggle.addEventListener('change', () => {
            const checked = toggle.checked;
            if (editCheckbox) editCheckbox.checked = checked;
            if (deleteCheckbox) deleteCheckbox.checked = checked;
        });
        
        // Sync manage toggle when edit/delete checkboxes change (if they're changed elsewhere)
        if (editCheckbox) {
            editCheckbox.addEventListener('change', () => {
                const editChecked = editCheckbox.checked;
                const deleteChecked = deleteCheckbox ? deleteCheckbox.checked : false;
                toggle.checked = editChecked || deleteChecked;
            });
        }
        
        if (deleteCheckbox) {
            deleteCheckbox.addEventListener('change', () => {
                const editChecked = editCheckbox ? editCheckbox.checked : false;
                const deleteChecked = deleteCheckbox.checked;
                toggle.checked = editChecked || deleteChecked;
            });
        }
    });
}

function bindAdminRolesRealtime() {
    if (adminRolesRealtimeBound) return;
    if (!window.Echo) return;
    try {
        window.Echo.channel('admin-roles')
            .listen('.admin-role.updated', (event) => {
                // Reload roles table
                rolesLoaded = false;
                loadRoles();
            });
        adminRolesRealtimeBound = true;
    } catch (e) {
        console.warn('Realtime not available for admin-roles:', e);
    }
}

// Render report notification targets (checkboxes)
function renderReportTargets(containerId, selected = []) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const USER_TYPES = [
        { value: 'student', label: 'Students' },
        { value: 'staff', label: 'Staff' },
        { value: 'security', label: 'Security Personnel' },
        { value: 'stakeholder', label: 'Stakeholders' },
        { value: 'reporter', label: 'Reporters' },
    ];

    container.innerHTML = USER_TYPES.map(ut => {
        const checked = selected.includes(ut.value) ? 'checked' : '';
        return `
            <label class="flex items-center gap-2">
                <input type="checkbox" name="report_targets[]" value="${ut.value}" class="form-checkbox" ${checked}>
                <span class="text-sm text-[#1b1b18] dark:text-[#EDEDEC]">${ut.label}</span>
            </label>
        `;
    }).join('');
}

// Render read-only report targets (chips)
function renderViewReportTargets(containerId, targets = []) {
    const container = document.getElementById(containerId);
    if (!container) return;
    if (!targets || targets.length === 0) {
        container.innerHTML = '<span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">No report targets configured</span>';
        return;
    }

    const LABELS = {
        student: 'Students',
        staff: 'Staff',
        security: 'Security',
        stakeholder: 'Stakeholders',
        reporter: 'Reporters',
    };

    container.innerHTML = targets.map(t => `
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">${LABELS[t] || t}</span>
    `).join('');
}
