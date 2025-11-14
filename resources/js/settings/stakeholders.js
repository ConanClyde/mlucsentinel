/**
 * Stakeholder Types Settings Management
 */

const qs = (id) => document.getElementById(id);

let stakeholderTypes = [];
let stakeholdersRealtimeBound = false;

export function initializeStakeholders() {
    loadStakeholderTypes();
    bindStakeholdersRealtime();
}

function loadStakeholderTypes() {
    fetch('/api/stakeholder-types', {
        headers: { 'Accept': 'application/json' }
    })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                stakeholderTypes = data.data || [];
                renderStakeholderTypesTable();
            }
        })
        .catch(() => window.showErrorModal('Failed to load stakeholder types'));
}

function renderStakeholderTypesTable() {
    const tbody = qs('stakeholder-types-table-body');
    if (!tbody) return;

    if (stakeholderTypes.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="px-4 py-8 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    No stakeholder types found.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = stakeholderTypes.map(type => `
        <tr class="hover:bg-gray-50 dark:hover:bg-[#2a2a2a] transition-colors" data-stakeholder-type-id="${type.id}">
            <td class="px-4 py-3">
                <span class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${type.name}</span>
            </td>
            <td class="px-4 py-3">
                <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">${type.description || '-'}</span>
            </td>
            <td class="px-4 py-3 text-center">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${type.evidence_required ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200'}">
                    ${type.evidence_required ? 'Required' : 'Optional'}
                </span>
            </td>
            <td class="px-4 py-3">
                <div class="flex items-center justify-center gap-2">
                    <button onclick="openEditStakeholderTypeModal(${type.id}, '${escapeHtml(type.name)}', '${escapeHtml(type.description || '')}', ${type.evidence_required})" class="btn-edit" title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </button>
                    <button onclick="openDeleteStakeholderTypeModal(${type.id}, '${escapeHtml(type.name)}')" class="btn-delete" title="Delete">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function escapeHtml(text) {
    return text.replace(/'/g, "\\'").replace(/"/g, '&quot;').replace(/\n/g, '\\n');
}

// Add Stakeholder Type Modal
window.openAddStakeholderTypeModal = function() {
    qs('add-stakeholder-name').value = '';
    qs('add-stakeholder-description').value = '';
    hideErrors('add-stakeholder');
    qs('addStakeholderTypeModal').classList.remove('hidden');
};

window.closeAddStakeholderTypeModal = function() {
    qs('addStakeholderTypeModal').classList.add('hidden');
};

window.submitAddStakeholderType = function() {
    const name = qs('add-stakeholder-name').value.trim();
    const description = qs('add-stakeholder-description').value.trim();
    const evidenceRequired = qs('add-stakeholder-evidence-required').checked;

    hideErrors('add-stakeholder');

    if (!name) {
        showError('add-stakeholder-name-error', 'Name is required');
        return;
    }

    fetch('/api/stakeholder-types', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ name, description, evidence_required: evidenceRequired })
    })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.showSuccessModal('Added', 'Stakeholder type added successfully');
                closeAddStakeholderTypeModal();
                loadStakeholderTypes();
            } else {
                if (data.errors) {
                    Object.keys(data.errors).forEach(key => {
                        showError(`add-stakeholder-${key}-error`, data.errors[key][0]);
                    });
                } else {
                    window.showErrorModal(data.message || 'Failed to add stakeholder type');
                }
            }
        })
        .catch(() => window.showErrorModal('Failed to add stakeholder type'));
};

// Edit Stakeholder Type Modal
window.openEditStakeholderTypeModal = function(id, name, description, evidenceRequired) {
    qs('edit-stakeholder-id').value = id;
    qs('edit-stakeholder-name').value = name;
    qs('edit-stakeholder-description').value = description;
    qs('edit-stakeholder-evidence-required').checked = evidenceRequired;
    hideErrors('edit-stakeholder');
    qs('editStakeholderTypeModal').classList.remove('hidden');
};

window.closeEditStakeholderTypeModal = function() {
    qs('editStakeholderTypeModal').classList.add('hidden');
};

window.submitEditStakeholderType = function() {
    const id = qs('edit-stakeholder-id').value;
    const name = qs('edit-stakeholder-name').value.trim();
    const description = qs('edit-stakeholder-description').value.trim();
    const evidenceRequired = qs('edit-stakeholder-evidence-required').checked;

    hideErrors('edit-stakeholder');

    if (!name) {
        showError('edit-stakeholder-name-error', 'Name is required');
        return;
    }

    fetch(`/api/stakeholder-types/${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ name, description, evidence_required: evidenceRequired })
    })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.showSuccessModal('Updated', 'Stakeholder type updated successfully');
                closeEditStakeholderTypeModal();
                loadStakeholderTypes();
            } else {
                if (data.errors) {
                    Object.keys(data.errors).forEach(key => {
                        showError(`edit-stakeholder-${key}-error`, data.errors[key][0]);
                    });
                } else {
                    window.showErrorModal(data.message || 'Failed to update stakeholder type');
                }
            }
        })
        .catch(() => window.showErrorModal('Failed to update stakeholder type'));
};

// Delete Stakeholder Type Modal
window.openDeleteStakeholderTypeModal = function(id, name) {
    qs('delete-stakeholder-id').value = id;
    qs('deleteStakeholderMessage').textContent = `Are you sure you want to delete the stakeholder type "${name}"? This action cannot be undone.`;
    qs('deleteStakeholderTypeModal').classList.remove('hidden');
};

window.closeDeleteStakeholderTypeModal = function() {
    qs('deleteStakeholderTypeModal').classList.add('hidden');
};

window.submitDeleteStakeholderType = function() {
    const id = qs('delete-stakeholder-id').value;

    fetch(`/api/stakeholder-types/${id}`, {
        method: 'DELETE',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.showSuccessModal('Deleted', 'Stakeholder type deleted successfully');
                closeDeleteStakeholderTypeModal();
                loadStakeholderTypes();
            } else {
                window.showErrorModal(data.message || 'Failed to delete stakeholder type');
            }
        })
        .catch(() => window.showErrorModal('Failed to delete stakeholder type'));
};

function showError(id, msg) {
    const el = qs(id);
    if (el) {
        el.textContent = msg;
        el.classList.remove('hidden');
    }
}

function hideErrors(prefix) {
    ['name', 'description'].forEach(field => {
        const el = qs(`${prefix}-${field}-error`);
        if (el) el.classList.add('hidden');
    });
}

function bindStakeholdersRealtime() {
    if (stakeholdersRealtimeBound) return;
    if (!window.Echo) return;
    try {
        window.Echo.channel('stakeholder-types')
            .listen('.stakeholder-type.updated', (event) => {
                // Reload stakeholder types table
                loadStakeholderTypes();
            });
        stakeholdersRealtimeBound = true;
    } catch (e) {
        console.warn('Realtime not available for stakeholder-types:', e);
    }
}
