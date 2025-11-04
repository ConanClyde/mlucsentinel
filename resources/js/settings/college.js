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
                const id = row.getAttribute('data-college-id');
                const name = row.querySelector('td:nth-child(1) span').textContent;
                const createdAt = row.querySelector('td:nth-child(2) span').textContent;
                return { id: parseInt(id), name, created_at: createdAt };
            });
            collegesRealtimeManager = new window.CollegesRealtime();
            collegesRealtimeManager.init(colleges);
        }
        return;
    }
    
    collegesLoaded = true;
    tableBody.innerHTML = '<tr><td colspan="4" class="px-4 py-8 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">Loading colleges...</td></tr>';
    
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
                tableBody.innerHTML = '<tr><td colspan="3" class="px-4 py-8 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">No colleges found. Click Add button to create one.</td></tr>';
            } else {
                tableBody.innerHTML = colleges.map(college => `
                    <tr class="hover:bg-gray-50 dark:hover:bg-[#161615]" data-id="${college.id}">
                        <td class="px-4 py-3 text-sm text-[#1b1b18] dark:text-[#EDEDEC]">${college.name}</td>
                        <td class="px-4 py-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">${new Date(college.created_at).toLocaleDateString()}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="editCollege(${college.id}, '${college.name.replace(/'/g, "\\'")}')" class="btn-edit" title="Edit">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.829-2.828z"></path>
                                    </svg>
                                </button>
                                <button onclick="deleteCollege(${college.id})" class="btn-delete" title="Delete">
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
            if (window.CollegesRealtime && !collegesRealtimeManager) {
                collegesRealtimeManager = new window.CollegesRealtime();
                collegesRealtimeManager.init(colleges);
            }
        }
    })
    .catch(error => {
        console.error('Error loading colleges:', error);
        tableBody.innerHTML = '<tr><td colspan="3" class="px-4 py-8 text-center text-sm text-red-600 dark:text-red-400">Error loading colleges. Please try again.</td></tr>';
    });
}

// Modal functions
export function openAddCollegeModal() {
    document.getElementById('add-college-modal').classList.remove('hidden');
}

export function closeAddCollegeModal() {
    document.getElementById('add-college-modal').classList.add('hidden');
    document.getElementById('modal-college-name').value = '';
}

export function addCollege() {
    const collegeName = document.getElementById('modal-college-name').value.trim();
    
    if (!collegeName) {
        window.showErrorModal('Please enter a college name');
        return;
    }
    
    fetch('/api/colleges', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ name: collegeName })
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
            window.showErrorModal(data.message || 'Failed to add college');
        }
    })
    .catch(error => {
        console.error('Error adding college:', error);
        window.showErrorModal('Error adding college. Please try again.');
    });
}

export function editCollege(id, currentName) {
    document.getElementById('edit-college-id').value = id;
    document.getElementById('edit-college-name').value = currentName;
    document.getElementById('edit-college-modal').classList.remove('hidden');
}

export function closeEditCollegeModal() {
    document.getElementById('edit-college-modal').classList.add('hidden');
    document.getElementById('edit-college-id').value = '';
    document.getElementById('edit-college-name').value = '';
}

export function updateCollege() {
    const id = document.getElementById('edit-college-id').value;
    const name = document.getElementById('edit-college-name').value.trim();
    
    if (!name) {
        window.showErrorModal('Please enter a college name');
        return;
    }
    
    fetch(`/api/colleges/${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ name: name })
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
            window.showErrorModal(data.message || 'Failed to update college');
        }
    })
    .catch(error => {
        console.error('Error updating college:', error);
        window.showErrorModal('Error updating college. Please try again.');
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

