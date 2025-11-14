/**
 * College Settings Management
 */

let collegesRealtimeManager = null;
let collegesLoaded = false;

export function initializeCollege() {
    // Load colleges when the tab is shown
    loadColleges();
}

export function loadColleges() {
    if (collegesLoaded) return;
    
    const tableBody = document.getElementById('college-table-body');
    if (!tableBody) return;
    
    // Check if data is already server-rendered
    const existingRows = tableBody.querySelectorAll('tr[data-college-id]');
    if (existingRows.length > 0) {
        collegesLoaded = true;
        // Initialize realtime manager with server-rendered data
        if (window.CollegesRealtime && !collegesRealtimeManager) {
            const colleges = Array.from(existingRows).map(row => {
                const id = parseInt(row.getAttribute('data-college-id'), 10);
                return {
                    id,
                    code: row.dataset.collegeCode || '',
                    name: row.dataset.collegeName || '',
                    type: row.dataset.collegeType || 'college',
                    description: row.dataset.collegeDescription || '',
                    created_at: row.dataset.collegeCreatedAt,
                };
            });
            collegesRealtimeManager = new window.CollegesRealtime();
            collegesRealtimeManager.init(colleges);
        }
        return;
    }
    
    collegesLoaded = true;
    tableBody.innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">Loading colleges...</td></tr>';
    
    fetch('/api/colleges', {
        headers: {
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const colleges = data.data;
            
            if (colleges.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">No colleges found. Click Add button to create one.</td></tr>';
            } else {
                tableBody.innerHTML = colleges.map(renderCollegeRow).join('');
            }
            
            // Initialize realtime manager
            if (window.CollegesRealtime && !collegesRealtimeManager) {
                collegesRealtimeManager = new window.CollegesRealtime();
                collegesRealtimeManager.init(colleges);
            }
        }
    })
    .catch(error => {
        console.error('Error loading colleges:', error);
        tableBody.innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-sm text-red-600 dark:text-red-400">Error loading colleges. Please try again.</td></tr>';
    });
}

function escapeHtml(value) {
    if (value === null || value === undefined) {
        return '';
    }

    return value
        .toString()
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function escapeAttr(value) {
    return escapeHtml(value);
}

function formatType(type) {
    if (!type) return '';

    return type
        .toString()
        .toLowerCase()
        .replace(/\b\w/g, char => char.toUpperCase());
}

function renderCollegeRow(college) {
    const createdDate = college.created_at ? new Date(college.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '';
    const description = college.description ? escapeHtml(college.description) : '—';
    const typeLabel = formatType(college.type) || 'College';

    return `
        <tr class="hover:bg-gray-50 dark:hover:bg-[#161615]" data-id="${college.id}" data-college-id="${college.id}"
            data-college-code="${escapeAttr(college.code || '')}"
            data-college-name="${escapeAttr(college.name || '')}"
            data-college-type="${escapeAttr(college.type || 'college')}"
            data-college-description="${escapeAttr(college.description || '')}"
            data-college-created-at="${escapeAttr(college.created_at || '')}">
            <td class="px-4 py-3 text-sm text-[#1b1b18] dark:text-[#EDEDEC]">${escapeHtml(college.code || '')}</td>
            <td class="px-4 py-3 text-sm text-[#1b1b18] dark:text-[#EDEDEC]">${escapeHtml(college.name || '')}</td>
            <td class="px-4 py-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">${typeLabel}</td>
            <td class="px-4 py-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">${description}</td>
            <td class="px-4 py-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">${createdDate}</td>
            <td class="px-4 py-3">
                <div class="flex items-center justify-center gap-2">
                    <button onclick="editCollege(${college.id})" class="btn-edit" title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </button>
                    <button onclick="deleteCollege(${college.id})" class="btn-delete" title="Delete">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            </td>
        </tr>
    `;
}

// Helper function to show error message
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

// Helper function to hide error message
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

// Validate add college form
function validateAddCollegeForm() {
    const name = document.getElementById('modal-college-name').value.trim();
    const code = document.getElementById('modal-college-code').value.trim();
    const addBtn = document.getElementById('add-college-btn');
    
    if (!name || !code) {
        addBtn.disabled = true;
        return false;
    }
    
    hideError('modal-college-name', 'modal-college-name-error');
    hideError('modal-college-code', 'modal-college-code-error');
    addBtn.disabled = false;
    return true;
}

// Validate edit college form
function validateEditCollegeForm() {
    const name = document.getElementById('edit-college-name').value.trim();
    const code = document.getElementById('edit-college-code').value.trim();
    const updateBtn = document.getElementById('update-college-btn');
    
    if (!name || !code) {
        updateBtn.disabled = true;
        return false;
    }
    
    hideError('edit-college-name', 'edit-college-name-error');
    hideError('edit-college-code', 'edit-college-code-error');
    updateBtn.disabled = false;
    return true;
}

// Modal functions
export function openAddCollegeModal() {
    document.getElementById('add-college-modal').classList.remove('hidden');
    const nameInput = document.getElementById('modal-college-name');
    const codeInput = document.getElementById('modal-college-code');
    const typeInput = document.getElementById('modal-college-type');
    document.getElementById('modal-college-type').value = 'college';
    if (nameInput) {
        nameInput.addEventListener('input', validateAddCollegeForm);
    }
    if (codeInput) {
        codeInput.addEventListener('input', validateAddCollegeForm);
    }
    if (typeInput) {
        typeInput.addEventListener('input', validateAddCollegeForm);
    }
    validateAddCollegeForm();
}

export function closeAddCollegeModal() {
    document.getElementById('add-college-modal').classList.add('hidden');
    document.getElementById('modal-college-name').value = '';
    document.getElementById('modal-college-code').value = '';
    document.getElementById('modal-college-type').value = 'college';
    document.getElementById('modal-college-description').value = '';
    hideError('modal-college-name', 'modal-college-name-error');
    hideError('modal-college-code', 'modal-college-code-error');
}

export function addCollege() {
    const collegeName = document.getElementById('modal-college-name').value.trim();
    const collegeCode = document.getElementById('modal-college-code').value.trim();
    const collegeType = document.getElementById('modal-college-type').value.trim() || 'college';
    const collegeDescription = document.getElementById('modal-college-description').value.trim();
    
    if (!collegeName) {
        showError('modal-college-name', 'modal-college-name-error', 'Please enter a college name');
        return;
    }
    if (!collegeCode) {
        showError('modal-college-code', 'modal-college-code-error', 'Please enter a college code');
        return;
    }
    
    hideError('modal-college-name', 'modal-college-name-error');
    hideError('modal-college-code', 'modal-college-code-error');
    
    fetch('/api/colleges', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            name: collegeName,
            code: collegeCode.toUpperCase(),
            type: collegeType || 'college',
            description: collegeDescription || null,
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.showSuccessModal('Success!', 'College added successfully!');
            closeAddCollegeModal();
            if (collegesRealtimeManager && data.data) {
                collegesRealtimeManager.addCollege(data.data);
            }
        } else {
            showError('modal-college-name', 'modal-college-name-error', data.message || 'Failed to add college');
        }
    })
    .catch(error => {
        console.error('Error adding college:', error);
        showError('modal-college-name', 'modal-college-name-error', 'Error adding college. Please try again.');
    });
}

export function editCollege(id) {
    const row = document.querySelector(`tr[data-college-id="${id}"]`);

    let name = '';
    let code = '';
    let type = 'college';
    let description = '';

    if (row) {
        name = row.dataset.collegeName || '';
        code = row.dataset.collegeCode || '';
        type = row.dataset.collegeType || 'college';
        description = row.dataset.collegeDescription || '';
    } else if (collegesRealtimeManager) {
        const fallback = collegesRealtimeManager.colleges.find(c => c.id === Number(id));
        if (fallback) {
            name = fallback.name || '';
            code = fallback.code || '';
            type = fallback.type || 'college';
            description = fallback.description || '';
        }
    }

    document.getElementById('edit-college-id').value = id;
    document.getElementById('edit-college-name').value = name;
    document.getElementById('edit-college-code').value = code;
    document.getElementById('edit-college-type').value = type;
    document.getElementById('edit-college-description').value = description;
    document.getElementById('edit-college-modal').classList.remove('hidden');
    const nameInput = document.getElementById('edit-college-name');
    const codeInput = document.getElementById('edit-college-code');
    const typeInput = document.getElementById('edit-college-type');
    const descriptionInput = document.getElementById('edit-college-description');
    if (nameInput) {
        nameInput.addEventListener('input', validateEditCollegeForm);
    }
    if (codeInput) {
        codeInput.addEventListener('input', validateEditCollegeForm);
    }
    if (typeInput) {
        typeInput.addEventListener('input', validateEditCollegeForm);
    }
    if (descriptionInput) {
        descriptionInput.addEventListener('input', () => hideError('edit-college-description', 'edit-college-description-error'));
    }
    validateEditCollegeForm();
}

export function closeEditCollegeModal() {
    document.getElementById('edit-college-modal').classList.add('hidden');
    document.getElementById('edit-college-id').value = '';
    document.getElementById('edit-college-name').value = '';
    document.getElementById('edit-college-code').value = '';
    document.getElementById('edit-college-type').value = 'college';
    document.getElementById('edit-college-description').value = '';
    hideError('edit-college-name', 'edit-college-name-error');
    hideError('edit-college-code', 'edit-college-code-error');
}

export function updateCollege() {
    const id = document.getElementById('edit-college-id').value;
    const name = document.getElementById('edit-college-name').value.trim();
    const code = document.getElementById('edit-college-code').value.trim();
    const type = document.getElementById('edit-college-type').value.trim() || 'college';
    const description = document.getElementById('edit-college-description').value.trim();
    
    if (!name) {
        showError('edit-college-name', 'edit-college-name-error', 'Please enter a college name');
        return;
    }
    if (!code) {
        showError('edit-college-code', 'edit-college-code-error', 'Please enter a college code');
        return;
    }
    
    hideError('edit-college-name', 'edit-college-name-error');
    hideError('edit-college-code', 'edit-college-code-error');
    
    fetch(`/api/colleges/${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            name: name,
            code: code.toUpperCase(),
            type: type || 'college',
            description: description || null,
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.showSuccessModal('Success!', 'College updated successfully!');
            closeEditCollegeModal();
            if (collegesRealtimeManager && data.data) {
                collegesRealtimeManager.updateCollege(data.data);
            }
        } else {
            showError('edit-college-name', 'edit-college-name-error', data.message || 'Failed to update college');
        }
    })
    .catch(error => {
        console.error('Error updating college:', error);
        showError('edit-college-name', 'edit-college-name-error', 'Error updating college. Please try again.');
    });
}

let collegeToDelete = null;

export function deleteCollege(id) {
    collegeToDelete = id;
    document.getElementById('delete-college-modal').classList.remove('hidden');
}

export function closeDeleteCollegeModal() {
    document.getElementById('delete-college-modal').classList.add('hidden');
    collegeToDelete = null;
}

export function confirmDeleteCollege() {
    if (!collegeToDelete) return;
    
    const collegeId = collegeToDelete;
    
    fetch(`/api/colleges/${collegeId}`, {
        method: 'DELETE',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.showSuccessModal('Success!', 'College deleted successfully!');
            closeDeleteCollegeModal();
            if (collegesRealtimeManager) {
                collegesRealtimeManager.removeCollege({ id: collegeId });
            }
        } else {
            window.showErrorModal(data.message || 'Failed to delete college');
        }
    })
    .catch(error => {
        console.error('Error deleting college:', error);
        window.showErrorModal('Error deleting college. Please try again.');
    });
}

// Make functions globally available
window.openAddCollegeModal = openAddCollegeModal;
window.closeAddCollegeModal = closeAddCollegeModal;
window.addCollege = addCollege;
window.editCollege = editCollege;
window.closeEditCollegeModal = closeEditCollegeModal;
window.updateCollege = updateCollege;
window.deleteCollege = deleteCollege;
window.closeDeleteCollegeModal = closeDeleteCollegeModal;
window.confirmDeleteCollege = confirmDeleteCollege;

