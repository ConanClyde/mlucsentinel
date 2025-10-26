let userIdCounter = 4;

// Add Modal Functions
window.openAddModal = function() {
    document.getElementById('addModal').classList.remove('hidden');
}

window.closeAddModal = function() {
    document.getElementById('addModal').classList.add('hidden');
    document.getElementById('addUserForm').reset();
}

// Helper function to get badge class
function getBadgeClass(role) {
    return role === 'Admin' ? 'badge badge-admin' : 'badge badge-user';
}

function addUser() {
    const name = document.getElementById('addName').value;
    const email = document.getElementById('addEmail').value;
    const role = document.getElementById('addRole').value;

    if (!name || !email) {
        alert('Please fill in all fields');
        return;
    }

    const tbody = document.getElementById('userTableBody');
    const newRow = tbody.insertRow();
    newRow.innerHTML = `
        <td>${userIdCounter}</td>
        <td>
            <div class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${name}</div>
            <div class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-0.5">${role}</div>
        </td>
        <td>${email}</td>
        <td><span class="badge badge-active">Active</span></td>
        <td class="flex items-center gap-2">
            <button class="btn-view" onclick="openViewModal(${userIdCounter}, '${name}', '${email}', '${role}')" title="View">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
            </button>
            <button class="btn-edit" onclick="openEditModal(${userIdCounter}, '${name}', '${email}', '${role}')" title="Edit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                </svg>
            </button>
            <button class="btn-delete" onclick="openDeleteModal(${userIdCounter}, '${name}')" title="Delete">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                </svg>
            </button>
        </td>
    `;

    userIdCounter++;
    closeAddModal();
}

// View Modal Functions
window.openViewModal = function(id, name, email, role) {
    document.getElementById('viewUserId').textContent = id;
    document.getElementById('viewName').textContent = name;
    document.getElementById('viewEmail').textContent = email;
    document.getElementById('viewRole').textContent = role;
    document.getElementById('viewModal').classList.remove('hidden');
}

window.closeViewModal = function() {
    document.getElementById('viewModal').classList.add('hidden');
}

// Edit Modal Functions
window.openEditModal = function(id, name, email, role) {
    document.getElementById('editUserId').value = id;
    document.getElementById('editName').value = name;
    document.getElementById('editEmail').value = email;
    document.getElementById('editRole').value = role;
    document.getElementById('editModal').classList.remove('hidden');
}

window.closeEditModal = function() {
    document.getElementById('editModal').classList.add('hidden');
}

function updateUser() {
    const id = document.getElementById('editUserId').value;
    const name = document.getElementById('editName').value;
    const email = document.getElementById('editEmail').value;
    const role = document.getElementById('editRole').value;

    const rows = document.getElementById('userTableBody').rows;
    for (let i = 0; i < rows.length; i++) {
        if (rows[i].cells[0].textContent == id) {
            rows[i].cells[1].innerHTML = `
                <div class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${name}</div>
                <div class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-0.5">${role}</div>
            `;
            rows[i].cells[2].textContent = email;
            rows[i].cells[3].innerHTML = `<span class="badge badge-active">Active</span>`;
            rows[i].cells[4].innerHTML = `
                <button class="btn-view" onclick="openViewModal(${id}, '${name}', '${email}', '${role}')" title="View">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </button>
                <button class="btn-edit" onclick="openEditModal(${id}, '${name}', '${email}', '${role}')" title="Edit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                    </svg>
                </button>
                <button class="btn-delete" onclick="openDeleteModal(${id}, '${name}')" title="Delete">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                    </svg>
                </button>
            `;
            break;
        }
    }

    closeEditModal();
}

// Delete Modal Functions
window.openDeleteModal = function(id, name) {
    document.getElementById('deleteUserId').value = id;
    document.getElementById('deleteUserName').textContent = name;
    document.getElementById('deleteModal').classList.remove('hidden');
}

window.closeDeleteModal = function() {
    document.getElementById('deleteModal').classList.add('hidden');
}

function deleteUser() {
    const id = document.getElementById('deleteUserId').value;
    const rows = document.getElementById('userTableBody').rows;
    
    for (let i = 0; i < rows.length; i++) {
        if (rows[i].cells[0].textContent == id) {
            rows[i].remove();
            break;
        }
    }

    closeDeleteModal();
}

// Close modals when clicking outside
const addModal = document.getElementById('addModal');
if (addModal) {
    addModal.addEventListener('click', function(e) {
        if (e.target === this) closeAddModal();
    });
}

const viewModal = document.getElementById('viewModal');
if (viewModal) {
    viewModal.addEventListener('click', function(e) {
        if (e.target === this) closeViewModal();
    });
}

const editModal = document.getElementById('editModal');
if (editModal) {
    editModal.addEventListener('click', function(e) {
        if (e.target === this) closeEditModal();
    });
}

const deleteModal = document.getElementById('deleteModal');
if (deleteModal) {
    deleteModal.addEventListener('click', function(e) {
        if (e.target === this) closeDeleteModal();
    });
}
