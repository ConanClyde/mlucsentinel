/**
 * Sticker Colors Settings
 */

const qs = (id) => document.getElementById(id);

let palette = {};
let paletteData = []; // Store full data array for rendering
let stickerColorsRealtimeBound = false;

export function initializeStickerColors() {
    loadPalette();
    bindStickerColorsRealtime();
}

function loadPalette() {
    const tbody = qs('palette-table-body');
    if (tbody) {
        tbody.innerHTML = '<tr><td colspan="4" class="px-4 py-8 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">Loading colors...</td></tr>';
    }
    
    fetch('/api/settings/sticker-palette', {
        headers: { 'Accept': 'application/json' }
    })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.data) {
                // Store full data array for rendering
                paletteData = data.data;
                // Also convert to object format: { key: hex } for backward compatibility
                palette = {};
                data.data.forEach(item => {
                    palette[item.key] = item.hex;
                });
                renderPaletteTable();
            } else {
                paletteData = [];
                palette = {};
                renderPaletteTable();
            }
        })
        .catch(() => {
            window.showErrorModal('Failed to load sticker palette');
            paletteData = [];
            palette = {};
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="4" class="px-4 py-8 text-center text-sm text-red-600 dark:text-red-400">Failed to load colors</td></tr>';
            }
        });
}

function renderPaletteTable() {
    const tbody = qs('palette-table-body');
    if (!tbody) return;

    if (paletteData.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="px-4 py-8 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    No colors found.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = paletteData.map(item => `
        <tr class="hover:bg-gray-50 dark:hover:bg-[#2a2a2a] transition-colors" data-color-key="${item.key}">
            <td class="px-4 py-3">
                <span class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${item.name}</span>
            </td>
            <td class="px-4 py-3">
                <div class="w-12 h-8 rounded border border-[#e3e3e0] dark:border-[#3E3E3A]" style="background-color: ${item.hex};"></div>
            </td>
            <td class="px-4 py-3">
                <span class="text-sm text-[#706f6c] dark:text-[#A1A09A] font-mono">${item.hex}</span>
            </td>
            <td class="px-4 py-3">
                <div class="flex items-center justify-center gap-2">
                    <button onclick="openEditColorModal('${item.key}', '${item.hex}')" class="btn-edit" title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </button>
                    <button onclick="openDeleteColorModal('${item.key}')" class="btn-delete" title="Delete">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// Add Color Modal
window.openAddColorModal = function() {
    qs('add-color-name').value = '';
    qs('add-color-hex').value = '#000000';
    qs('add-color-picker').value = '#000000';
    hideErrors('add');
    qs('addColorModal').classList.remove('hidden');
};

window.closeAddColorModal = function() {
    qs('addColorModal').classList.add('hidden');
};

window.submitAddColor = function() {
    const name = qs('add-color-name').value.trim().toLowerCase();
    const hex = qs('add-color-hex').value.trim();

    hideErrors('add');

    if (!name) {
        showError('add-color-name-error', 'Color name is required');
        return;
    }
    if (!/^[a-z0-9_-]+$/.test(name)) {
        showError('add-color-name-error', 'Use only lowercase letters, numbers, hyphens, and underscores');
        return;
    }
    if (palette[name]) {
        showError('add-color-name-error', 'Color name already exists');
        return;
    }
    if (!/^#[0-9A-Fa-f]{6}$/.test(hex)) {
        showError('add-color-hex-error', 'Invalid hex color format');
        return;
    }

    fetch('/api/settings/sticker-palette', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ name: name, hex })
    })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.showSuccessModal('Added', 'Color added successfully');
                closeAddColorModal();
                loadPalette();
            } else {
                if (data.errors) {
                    Object.keys(data.errors).forEach(key => {
                        showError(`add-color-${key}-error`, data.errors[key][0]);
                    });
                } else {
                    window.showErrorModal(data.message || 'Failed to add color');
                }
            }
        })
        .catch(() => window.showErrorModal('Failed to add color'));
};

// Edit Color Modal
window.openEditColorModal = function(key, hex) {
    // Find the name from paletteData
    const colorItem = paletteData.find(item => item.key === key);
    const displayName = colorItem ? colorItem.name : key;
    
    qs('edit-color-old-key').value = key;
    qs('edit-color-name').value = displayName;
    qs('edit-color-hex').value = hex;
    qs('edit-color-picker').value = hex;
    hideErrors('edit');
    qs('editColorModal').classList.remove('hidden');
};

window.closeEditColorModal = function() {
    qs('editColorModal').classList.add('hidden');
};

window.submitEditColor = function() {
    const oldKey = qs('edit-color-old-key').value;
    const newKey = qs('edit-color-name').value.trim().toLowerCase();
    const hex = qs('edit-color-hex').value.trim();

    hideErrors('edit');

    if (!newKey) {
        showError('edit-color-name-error', 'Color name is required');
        return;
    }
    if (!/^[a-z0-9_-]+$/.test(newKey)) {
        showError('edit-color-name-error', 'Use only lowercase letters, numbers, hyphens, and underscores');
        return;
    }
    if (newKey !== oldKey && palette[newKey]) {
        showError('edit-color-name-error', 'Color name already exists');
        return;
    }
    if (!/^#[0-9A-Fa-f]{6}$/.test(hex)) {
        showError('edit-color-hex-error', 'Invalid hex color format');
        return;
    }

    fetch(`/api/settings/sticker-palette/${oldKey}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ name: newKey, hex })
    })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.showSuccessModal('Updated', 'Color updated successfully');
                closeEditColorModal();
                loadPalette();
            } else {
                if (data.errors) {
                    Object.keys(data.errors).forEach(key => {
                        showError(`edit-color-${key}-error`, data.errors[key][0]);
                    });
                } else {
                    window.showErrorModal(data.message || 'Failed to update color');
                }
            }
        })
        .catch(() => window.showErrorModal('Failed to update color'));
};

// Delete Color Modal
window.openDeleteColorModal = function(key) {
    // Find the name from paletteData
    const colorItem = paletteData.find(item => item.key === key);
    const displayName = colorItem ? colorItem.name : key;
    
    qs('delete-color-key').value = key;
    qs('deleteColorMessage').textContent = `Are you sure you want to delete the sticker color "${displayName}"? This action cannot be undone.`;
    qs('deleteColorModal').classList.remove('hidden');
};

window.closeDeleteColorModal = function() {
    qs('deleteColorModal').classList.add('hidden');
};

window.submitDeleteColor = function() {
    const key = qs('delete-color-key').value;

    fetch(`/api/settings/sticker-palette/${key}`, {
        method: 'DELETE',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.showSuccessModal('Deleted', 'Color deleted successfully');
                closeDeleteColorModal();
                loadPalette();
            } else {
                window.showErrorModal(data.message || 'Failed to delete color');
            }
        })
        .catch(() => window.showErrorModal('Failed to delete color'));
};

// Color picker sync
document.addEventListener('DOMContentLoaded', () => {
    const addPicker = qs('add-color-picker');
    const addHex = qs('add-color-hex');
    if (addPicker && addHex) {
        addPicker.addEventListener('input', () => addHex.value = addPicker.value.toUpperCase());
        addHex.addEventListener('input', () => {
            if (/^#[0-9A-Fa-f]{6}$/.test(addHex.value)) {
                addPicker.value = addHex.value;
            }
        });
    }

    const editPicker = qs('edit-color-picker');
    const editHex = qs('edit-color-hex');
    if (editPicker && editHex) {
        editPicker.addEventListener('input', () => editHex.value = editPicker.value.toUpperCase());
        editHex.addEventListener('input', () => {
            if (/^#[0-9A-Fa-f]{6}$/.test(editHex.value)) {
                editPicker.value = editHex.value;
            }
        });
    }
});

function showError(id, msg) {
    const el = qs(id);
    if (el) {
        el.textContent = msg;
        el.classList.remove('hidden');
    }
}

function hideErrors(prefix) {
    ['name', 'hex'].forEach(field => {
        const el = qs(`${prefix}-color-${field}-error`);
        if (el) el.classList.add('hidden');
    });
}

function bindStickerColorsRealtime() {
    if (stickerColorsRealtimeBound) return;
    if (!window.Echo) return;
    try {
        window.Echo.channel('sticker-palette')
            .listen('.sticker-palette.updated', (event) => {
                // Reload palette table
                loadPalette();
            });
        stickerColorsRealtimeBound = true;
    } catch (e) {
        console.warn('Realtime not available for sticker-palette:', e);
    }
}
