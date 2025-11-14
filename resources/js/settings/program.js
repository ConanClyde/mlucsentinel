/**
 * Program Settings Management
 */

let programsRealtimeManager = null;
let programsLoaded = false;
let collegesForDropdown = [];

export function initializeProgram() {
    loadCollegesForDropdown();
    loadPrograms();
}

export function loadCollegesForDropdown() {
    fetch('/api/colleges', {
        headers: {
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            collegesForDropdown = data.data;
            updateProgramCollegeDropdowns();
        }
    })
    .catch(error => {
        console.error('Error loading colleges for dropdown:', error);
    });
}

export function updateProgramCollegeDropdowns() {
    const addCollegeSelect = document.getElementById('modal-program-college-id');
    const editCollegeSelect = document.getElementById('edit-program-college-id');
    
    const options = collegesForDropdown.map(college => 
        `<option value="${college.id}">${college.name}</option>`
    ).join('');
    
    if (addCollegeSelect) {
        addCollegeSelect.innerHTML = '<option value="">Select College</option>' + options;
    }
    if (editCollegeSelect) {
        editCollegeSelect.innerHTML = '<option value="">Select College</option>' + options;
    }
}

export function loadPrograms() {
    if (programsLoaded) return;
    
    const tableBody = document.getElementById('program-table-body');
    if (!tableBody) return;
    
    // Check if data is already server-rendered
    const existingRows = tableBody.querySelectorAll('tr[data-program-id]');
    if (existingRows.length > 0) {
        programsLoaded = true;
        // Initialize realtime manager with server-rendered data
        if (window.ProgramsRealtime && !programsRealtimeManager) {
            const programs = Array.from(existingRows).map(row => {
                const id = parseInt(row.getAttribute('data-program-id'), 10);
                return { 
                    id,
                    code: row.dataset.programCode || '',
                    name: row.dataset.programName || '',
                    description: row.dataset.programDescription || '',
                    college_id: row.dataset.programCollegeId ? parseInt(row.dataset.programCollegeId, 10) : null,
                    college: row.dataset.programCollegeName ? { name: row.dataset.programCollegeName } : null,
                    created_at: row.dataset.programCreatedAt,
                };
            });
            programsRealtimeManager = new window.ProgramsRealtime();
            programsRealtimeManager.init(programs);
        }
        return;
    }
    
    programsLoaded = true;
    tableBody.innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">Loading programs...</td></tr>';
    
    fetch('/api/programs', {
        headers: {
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const programs = data.data;
            
            if (programs.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">No programs found. Click Add button to create one.</td></tr>';
            } else {
                tableBody.innerHTML = programs.map(renderProgramRow).join('');
            }
            
            // Initialize realtime manager
            if (window.ProgramsRealtime && !programsRealtimeManager) {
                programsRealtimeManager = new window.ProgramsRealtime();
                programsRealtimeManager.init(programs);
            }
        }
    })
    .catch(error => {
        console.error('Error loading programs:', error);
        tableBody.innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-sm text-red-600 dark:text-red-400">Error loading programs. Please try again.</td></tr>';
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

function renderProgramRow(program) {
    const createdDate = program.created_at ? new Date(program.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '';
    const description = program.description ? escapeHtml(program.description) : '—';
    const collegeName = program.college ? escapeHtml(program.college.name) : 'N/A';
    const collegeId = program.college_id ?? (program.college ? program.college.id : null);
    const datasetCollegeName = program.college ? program.college.name : '';

    return `
        <tr class="hover:bg-gray-50 dark:hover:bg-[#161615]" data-id="${program.id}" data-program-id="${program.id}"
            data-program-code="${escapeAttr(program.code || '')}"
            data-program-name="${escapeAttr(program.name || '')}"
            data-program-description="${escapeAttr(program.description || '')}"
            data-program-college-id="${collegeId ?? ''}"
            data-program-college-name="${escapeAttr(datasetCollegeName)}"
            data-program-created-at="${escapeAttr(program.created_at || '')}">
            <td class="px-4 py-3 text-sm text-[#1b1b18] dark:text-[#EDEDEC]">${escapeHtml(program.code || '')}</td>
            <td class="px-4 py-3 text-sm text-[#1b1b18] dark:text-[#EDEDEC]">${escapeHtml(program.name || '')}</td>
            <td class="px-4 py-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">${collegeName}</td>
            <td class="px-4 py-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">${description}</td>
            <td class="px-4 py-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">${createdDate}</td>
            <td class="px-4 py-3">
                <div class="flex items-center justify-center gap-2">
                    <button onclick="editProgram(${program.id})" class="btn-edit" title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </button>
                    <button onclick="deleteProgram(${program.id})" class="btn-delete" title="Delete">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            </td>
        </tr>
    `;
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

function validateAddProgramForm() {
    const collegeId = document.getElementById('modal-program-college-id').value;
    const programName = document.getElementById('modal-program-name').value.trim();
    const programCode = document.getElementById('modal-program-code').value.trim();
    const addBtn = document.getElementById('add-program-btn');
    
    if (!collegeId || !programName || !programCode) {
        addBtn.disabled = true;
        return false;
    }
    
    hideError('modal-program-college-id', 'modal-program-college-id-error');
    hideError('modal-program-name', 'modal-program-name-error');
    hideError('modal-program-code', 'modal-program-code-error');
    addBtn.disabled = false;
    return true;
}

function validateEditProgramForm() {
    const collegeId = document.getElementById('edit-program-college-id').value;
    const programName = document.getElementById('edit-program-name').value.trim();
    const programCode = document.getElementById('edit-program-code').value.trim();
    const updateBtn = document.getElementById('update-program-btn');
    
    if (!collegeId || !programName || !programCode) {
        updateBtn.disabled = true;
        return false;
    }
    
    hideError('edit-program-college-id', 'edit-program-college-id-error');
    hideError('edit-program-name', 'edit-program-name-error');
    hideError('edit-program-code', 'edit-program-code-error');
    updateBtn.disabled = false;
    return true;
}

export function openAddProgramModal() {
    updateProgramCollegeDropdowns();
    document.getElementById('add-program-modal').classList.remove('hidden');
    const collegeSelect = document.getElementById('modal-program-college-id');
    const nameInput = document.getElementById('modal-program-name');
    const codeInput = document.getElementById('modal-program-code');
    const descriptionInput = document.getElementById('modal-program-description');
    if (collegeSelect) collegeSelect.addEventListener('change', validateAddProgramForm);
    if (nameInput) nameInput.addEventListener('input', validateAddProgramForm);
    if (codeInput) codeInput.addEventListener('input', validateAddProgramForm);
    if (descriptionInput) descriptionInput.addEventListener('input', () => hideError('modal-program-description', 'modal-program-description-error'));
    validateAddProgramForm();
}

export function closeAddProgramModal() {
    document.getElementById('add-program-modal').classList.add('hidden');
    document.getElementById('modal-program-college-id').value = '';
    document.getElementById('modal-program-name').value = '';
    document.getElementById('modal-program-code').value = '';
    document.getElementById('modal-program-description').value = '';
    hideError('modal-program-college-id', 'modal-program-college-id-error');
    hideError('modal-program-name', 'modal-program-name-error');
    hideError('modal-program-code', 'modal-program-code-error');
}

export function addProgram() {
    const collegeId = document.getElementById('modal-program-college-id').value;
    const programName = document.getElementById('modal-program-name').value.trim();
    const programCode = document.getElementById('modal-program-code').value.trim();
    const programDescription = document.getElementById('modal-program-description').value.trim();
    
    if (!collegeId) {
        showError('modal-program-college-id', 'modal-program-college-id-error', 'Please select a college');
        return;
    }
    
    if (!programName) {
        showError('modal-program-name', 'modal-program-name-error', 'Please enter a program name');
        return;
    }
    if (!programCode) {
        showError('modal-program-code', 'modal-program-code-error', 'Please enter a program code');
        return;
    }
    
    hideError('modal-program-college-id', 'modal-program-college-id-error');
    hideError('modal-program-name', 'modal-program-name-error');
    hideError('modal-program-code', 'modal-program-code-error');
    
    fetch('/api/programs', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ 
            college_id: collegeId,
            name: programName,
            code: programCode.toUpperCase(),
            description: programDescription || null,
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.showSuccessModal('Success!', 'Program added successfully!');
            closeAddProgramModal();
            if (programsRealtimeManager && data.data) {
                programsRealtimeManager.addProgram(data.data);
            }
            programsLoaded = false;
            loadPrograms();
        } else {
            showError('modal-program-name', 'modal-program-name-error', data.message || 'Failed to add program');
        }
    })
    .catch(error => {
        console.error('Error adding program:', error);
        showError('modal-program-name', 'modal-program-name-error', 'Error adding program. Please try again.');
    });
}

export function editProgram(id) {
    updateProgramCollegeDropdowns();

    const row = document.querySelector(`tr[data-program-id="${id}"]`);

    let collegeId = '';
    let programName = '';
    let programCode = '';
    let programDescription = '';

    if (row) {
        collegeId = row.dataset.programCollegeId || '';
        programName = row.dataset.programName || '';
        programCode = row.dataset.programCode || '';
        programDescription = row.dataset.programDescription || '';
    } else if (programsRealtimeManager) {
        const fallback = programsRealtimeManager.programs.find(p => p.id === Number(id));
        if (fallback) {
            collegeId = fallback.college_id ?? (fallback.college ? fallback.college.id : '');
            programName = fallback.name || '';
            programCode = fallback.code || '';
            programDescription = fallback.description || '';
        }
    }

    document.getElementById('edit-program-id').value = id;
    document.getElementById('edit-program-college-id').value = collegeId;
    document.getElementById('edit-program-name').value = programName;
    document.getElementById('edit-program-code').value = programCode;
    document.getElementById('edit-program-description').value = programDescription;
    document.getElementById('edit-program-modal').classList.remove('hidden');
    const collegeSelect = document.getElementById('edit-program-college-id');
    const nameInput = document.getElementById('edit-program-name');
    const codeInput = document.getElementById('edit-program-code');
    const descriptionInput = document.getElementById('edit-program-description');
    if (collegeSelect) collegeSelect.addEventListener('change', validateEditProgramForm);
    if (nameInput) nameInput.addEventListener('input', validateEditProgramForm);
    if (codeInput) codeInput.addEventListener('input', validateEditProgramForm);
    if (descriptionInput) descriptionInput.addEventListener('input', () => hideError('edit-program-description', 'edit-program-description-error'));
    validateEditProgramForm();
}

export function closeEditProgramModal() {
    document.getElementById('edit-program-modal').classList.add('hidden');
    document.getElementById('edit-program-id').value = '';
    document.getElementById('edit-program-college-id').value = '';
    document.getElementById('edit-program-name').value = '';
    document.getElementById('edit-program-code').value = '';
    document.getElementById('edit-program-description').value = '';
    hideError('edit-program-college-id', 'edit-program-college-id-error');
    hideError('edit-program-name', 'edit-program-name-error');
    hideError('edit-program-code', 'edit-program-code-error');
}

export function updateProgram() {
    const id = document.getElementById('edit-program-id').value;
    const collegeId = document.getElementById('edit-program-college-id').value;
    const name = document.getElementById('edit-program-name').value.trim();
    const code = document.getElementById('edit-program-code').value.trim();
    const description = document.getElementById('edit-program-description').value.trim();
    
    if (!collegeId) {
        showError('edit-program-college-id', 'edit-program-college-id-error', 'Please select a college');
        return;
    }
    
    if (!name) {
        showError('edit-program-name', 'edit-program-name-error', 'Please enter a program name');
        return;
    }
    if (!code) {
        showError('edit-program-code', 'edit-program-code-error', 'Please enter a program code');
        return;
    }
    
    hideError('edit-program-college-id', 'edit-program-college-id-error');
    hideError('edit-program-name', 'edit-program-name-error');
    hideError('edit-program-code', 'edit-program-code-error');
    
    fetch(`/api/programs/${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ 
            college_id: collegeId,
            name: name,
            code: code.toUpperCase(),
            description: description || null,
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.showSuccessModal('Success!', 'Program updated successfully!');
            closeEditProgramModal();
            if (programsRealtimeManager && data.data) {
                programsRealtimeManager.updateProgram(data.data);
            }
            programsLoaded = false;
            loadPrograms();
        } else {
            showError('edit-program-name', 'edit-program-name-error', data.message || 'Failed to update program');
        }
    })
    .catch(error => {
        console.error('Error updating program:', error);
        showError('edit-program-name', 'edit-program-name-error', 'Error updating program. Please try again.');
    });
}

let programToDelete = null;

export function deleteProgram(id) {
    programToDelete = id;
    document.getElementById('delete-program-modal').classList.remove('hidden');
}

export function closeDeleteProgramModal() {
    document.getElementById('delete-program-modal').classList.add('hidden');
    programToDelete = null;
}

export function confirmDeleteProgram() {
    if (!programToDelete) return;
    
    const programId = programToDelete;
    
    fetch(`/api/programs/${programId}`, {
        method: 'DELETE',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.showSuccessModal('Success!', 'Program deleted successfully!');
            closeDeleteProgramModal();
            if (programsRealtimeManager) {
                programsRealtimeManager.removeProgram({ id: programId });
            }
            programsLoaded = false;
            loadPrograms();
        } else {
            window.showErrorModal(data.message || 'Failed to delete program');
        }
    })
    .catch(error => {
        console.error('Error deleting program:', error);
        window.showErrorModal('Error deleting program. Please try again.');
    });
}

// Make functions globally available
window.openAddProgramModal = openAddProgramModal;
window.closeAddProgramModal = closeAddProgramModal;
window.addProgram = addProgram;
window.editProgram = editProgram;
window.closeEditProgramModal = closeEditProgramModal;
window.updateProgram = updateProgram;
window.deleteProgram = deleteProgram;
window.closeDeleteProgramModal = closeDeleteProgramModal;
window.confirmDeleteProgram = confirmDeleteProgram;

