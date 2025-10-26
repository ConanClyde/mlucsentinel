<!-- Add User Modal -->
<div id="addModal" class="modal-backdrop hidden">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title">Add New User</h2>
        </div>
        <div class="modal-body">
            <form id="addUserForm">
                <div class="form-group">
                    <label class="form-label">Name</label>
                    <input type="text" class="form-input" id="addName" placeholder="Enter name" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-input" id="addEmail" placeholder="Enter email" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select class="form-input" id="addRole">
                        <option value="User">User</option>
                        <option value="Admin">Admin</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeAddModal()">Cancel</button>
            <button class="btn btn-primary" onclick="addUser()">Add User</button>
        </div>
    </div>
</div>

<!-- View User Modal -->
<div id="viewModal" class="modal-backdrop hidden">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title">View User Details</h2>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">ID</label>
                <p id="viewUserId" class="p-3 bg-[#f3f4f6] dark:bg-[#1b1b18] rounded-md"></p>
            </div>
            <div class="form-group">
                <label class="form-label">Name</label>
                <p id="viewName" class="p-3 bg-[#f3f4f6] dark:bg-[#1b1b18] rounded-md"></p>
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <p id="viewEmail" class="p-3 bg-[#f3f4f6] dark:bg-[#1b1b18] rounded-md"></p>
            </div>
            <div class="form-group">
                <label class="form-label">Role</label>
                <p id="viewRole" class="p-3 bg-[#f3f4f6] dark:bg-[#1b1b18] rounded-md"></p>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeViewModal()">Close</button>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editModal" class="modal-backdrop hidden">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title">Edit User</h2>
        </div>
        <div class="modal-body">
            <form id="editUserForm">
                <input type="hidden" id="editUserId">
                <div class="form-group">
                    <label class="form-label">Name</label>
                    <input type="text" class="form-input" id="editName" placeholder="Enter name" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-input" id="editEmail" placeholder="Enter email" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select class="form-input" id="editRole">
                        <option value="User">User</option>
                        <option value="Admin">Admin</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
            <button class="btn btn-primary" onclick="updateUser()">Update User</button>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal-backdrop hidden">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title">Delete User</h2>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete <strong id="deleteUserName"></strong>? This action cannot be undone.</p>
            <input type="hidden" id="deleteUserId">
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
            <button class="btn btn-danger" onclick="deleteUser()">Delete</button>
        </div>
    </div>
</div>

<!-- Message Modal -->
<div id="messageModal" class="modal-backdrop hidden">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title">Confirm Action</h2>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to proceed with this action? This operation cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeMessageModal()">Cancel</button>
            <button class="btn btn-primary" onclick="closeMessageModal()">Confirm</button>
        </div>
    </div>
</div>

<!-- Success Notification Modal -->
<div id="successModal" class="modal-backdrop hidden">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title text-green-600 dark:text-green-400 flex items-center gap-3">
                <x-heroicon-o-check-circle class="modal-icon-success" />
                Success
            </h2>
        </div>
        <div class="modal-body">
            <p>Your operation has been completed successfully!</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-success" onclick="closeSuccessModal()">Okay</button>
        </div>
    </div>
</div>

<!-- Error Notification Modal -->
<div id="errorModal" class="modal-backdrop hidden">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title text-red-500 dark:text-red-400 flex items-center gap-3">
                <x-heroicon-o-x-circle class="modal-icon-error" />
                Error
            </h2>
        </div>
        <div class="modal-body">
            <p>An error occurred while processing your request. Please try again.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-danger" onclick="closeErrorModal()">Close</button>
        </div>
    </div>
</div>

<!-- Warning Notification Modal -->
<div id="warningModal" class="modal-backdrop hidden">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title text-orange-600 dark:text-orange-400 flex items-center gap-3">
                <x-heroicon-o-exclamation-triangle class="modal-icon-warning" />
                Warning
            </h2>
        </div>
        <div class="modal-body">
            <p>Please review your action carefully before proceeding. This may have important consequences.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeWarningModal()">Cancel</button>
            <button class="btn btn-warning" onclick="closeWarningModal()">Proceed</button>
        </div>
    </div>
</div>
