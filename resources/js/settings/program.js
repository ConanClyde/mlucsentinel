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
                const id = row.getAttribute('data-program-id');
                const name = row.querySelector('td:nth-child(1) span').textContent;
                const collegeName = row.querySelector('td:nth-child(2) span').textContent;
                const createdAt = row.querySelector('td:nth-child(3) span').textContent;
                return { 
                    id: parseInt(id), 
                    name, 
                    college: { name: collegeName },
                    created_at: createdAt 
                };
            });
            programsRealtimeManager = new window.ProgramsRealtime();
            programsRealtimeManager.init(programs);
        }
        return;
    }
    
    programsLoaded = true;
    tableBody.innerHTML = '<tr><td colspan="4" class="px-4 py-8 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">Loading programs...</td></tr>';
    
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
                tableBody.innerHTML = '<tr><td colspan="4" class="px-4 py-8 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">No programs found. Click Add button to create one.</td></tr>';
            } else {
                tableBody.innerHTML = programs.map(program => `
                    <tr class="hover:bg-gray-50 dark:hover:bg-[#161615]" data-id="${program.id}">
                        <td class="px-4 py-3 text-sm text-[#1b1b18] dark:text-[#EDEDEC]">${program.name}</td>
                        <td class="px-4 py-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">${program.college ? program.college.name : 'N/A'}</td>
                        <td class="px-4 py-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">${new Date(program.created_at).toLocaleDateString()}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="editProgram(${program.id}, ${program.college_id}, '${program.name.replace(/'/g, "\\'")}')" class="btn-edit" title="Edit">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.829-2.828z"></path>
                                    </svg>
                                </button>
                                <button onclick="deleteProgram(${program.id})" class="btn-delete" title="Delete">
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
            if (window.ProgramsRealtime && !programsRealtimeManager) {
                programsRealtimeManager = new window.ProgramsRealtime();
                programsRealtimeManager.init(programs);
            }
        }
    })
    .catch(error => {
        console.error('Error loading programs:', error);
        tableBody.innerHTML = '<tr><td colspan="4" class="px-4 py-8 text-center text-sm text-red-600 dark:text-red-400">Error loading programs. Please try again.</td></tr>';
    });
}

export function openAddProgramModal() {
    updateProgramCollegeDropdowns();
    document.getElementById('add-program-modal').classList.remove('hidden');
}

export function closeAddProgramModal() {
    document.getElementById('add-program-modal').classList.add('hidden');
    document.getElementById('modal-program-college-id').value = '';
    document.getElementById('modal-program-name').value = '';
}

export function addProgram() {
    const collegeId = document.getElementById('modal-program-college-id').value;
    const programName = document.getElementById('modal-program-name').value.trim();
    
    if (!collegeId) {
        window.showErrorModal('Please select a college');
        return;
    }
    
    if (!programName) {
        window.showErrorModal('Please enter a program name');
        return;
    }
    
    fetch('/api/programs', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ 
            college_id: collegeId,
            name: programName 
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
            window.showErrorModal(data.message || 'Failed to add program');
        }
    })
    .catch(error => {
        console.error('Error adding program:', error);
        window.showErrorModal('Error adding program. Please try again.');
    });
}

export function editProgram(id, collegeId, currentName) {
    updateProgramCollegeDropdowns();
    document.getElementById('edit-program-id').value = id;
    document.getElementById('edit-program-college-id').value = collegeId;
    document.getElementById('edit-program-name').value = currentName;
    document.getElementById('edit-program-modal').classList.remove('hidden');
}

export function closeEditProgramModal() {
    document.getElementById('edit-program-modal').classList.add('hidden');
    document.getElementById('edit-program-id').value = '';
    document.getElementById('edit-program-college-id').value = '';
    document.getElementById('edit-program-name').value = '';
}

export function updateProgram() {
    const id = document.getElementById('edit-program-id').value;
    const collegeId = document.getElementById('edit-program-college-id').value;
    const name = document.getElementById('edit-program-name').value.trim();
    
    if (!collegeId) {
        window.showErrorModal('Please select a college');
        return;
    }
    
    if (!name) {
        window.showErrorModal('Please enter a program name');
        return;
    }
    
    fetch(`/api/programs/${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ 
            college_id: collegeId,
            name: name 
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
            window.showErrorModal(data.message || 'Failed to update program');
        }
    })
    .catch(error => {
        console.error('Error updating program:', error);
        window.showErrorModal('Error updating program. Please try again.');
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

