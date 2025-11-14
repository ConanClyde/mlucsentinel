<!-- Add Admin Role Modal -->
<div id="add-role-modal" class="modal-backdrop hidden" onclick="if(event.target === this) closeAddRoleModal()">
    <div class="modal-container-wide">
        <div class="modal-header">
            <h2 class="modal-title">Add New Admin Role</h2>
        </div>
        <form id="addRoleForm">
            <div class="modal-body max-h-[70vh] overflow-y-auto">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group md:col-span-2">
                        <label class="form-label">Role Name <span class="text-red-500">*</span></label>
                        <input type="text" id="modal-role-name" class="form-input" placeholder="Enter role name" required>
                    </div>
                    <div class="form-group md:col-span-2">
                        <label class="form-label">Description</label>
                        <textarea id="modal-role-description" class="form-input" rows="2" placeholder="Enter role description"></textarea>
                    </div>
                    <div class="form-group md:col-span-2">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" id="modal-role-is-active" class="form-checkbox" checked>
                            <span class="form-label mb-0">Active</span>
                        </label>
                        <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-1">If unchecked, users with this role will not have any access</p>
                    </div>
                </div>

                <div class="mt-6 border-t border-[#e3e3e0] dark:border-[#3E3E3A] pt-4">
                    <h3 class="text-md font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Assign Privileges</h3>
                    <div id="modal-privileges-list" class="space-y-4">
                        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Loading privileges...</p>
                    </div>
                </div>

                <div class="mt-6 border-t border-[#e3e3e0] dark:border-[#3E3E3A] pt-4">
                    <h3 class="text-md font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Report Notification Targets</h3>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mb-3">Select violator user types this role should receive notifications for when a new report is created.</p>
                    <div id="modal-report-targets" class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        <!-- Rendered by JS -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeAddRoleModal()" class="btn btn-secondary">Cancel</button>
                <button type="button" onclick="addRole()" class="btn btn-primary">Add Role</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Admin Role Modal -->
<div id="edit-role-modal" class="modal-backdrop hidden" onclick="if(event.target === this) closeEditRoleModal()">
    <div class="modal-container-wide">
        <div class="modal-header">
            <h2 class="modal-title">Edit Admin Role</h2>
        </div>
        <form id="editRoleForm">
            <div class="modal-body max-h-[70vh] overflow-y-auto">
                <input type="hidden" id="edit-role-id">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group md:col-span-2">
                        <label class="form-label">Role Name <span class="text-red-500">*</span></label>
                        <input type="text" id="edit-role-name" class="form-input" placeholder="Enter role name" required>
                    </div>
                    <div class="form-group md:col-span-2">
                        <label class="form-label">Description</label>
                        <textarea id="edit-role-description" class="form-input" rows="2" placeholder="Enter role description"></textarea>
                    </div>
                    <div class="form-group md:col-span-2">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" id="edit-role-is-active" class="form-checkbox">
                            <span class="form-label mb-0">Active</span>
                        </label>
                        <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-1">If unchecked, users with this role will not have any access</p>
                    </div>
                </div>

                <div class="mt-6 border-t border-[#e3e3e0] dark:border-[#3E3E3A] pt-4">
                    <h3 class="text-md font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Assign Privileges</h3>
                    <div id="edit-privileges-list" class="space-y-4">
                        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Loading privileges...</p>
                    </div>
                </div>

                <div class="mt-6 border-t border-[#e3e3e0] dark:border-[#3E3E3A] pt-4">
                    <h3 class="text-md font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Report Notification Targets</h3>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mb-3">Select violator user types this role should receive notifications for when a new report is created.</p>
                    <div id="edit-report-targets" class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        <!-- Rendered by JS -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeEditRoleModal()" class="btn btn-secondary">Cancel</button>
                <button type="button" onclick="updateRole()" class="btn btn-primary">Update Role</button>
            </div>
        </form>
    </div>
</div>

<!-- View Admin Role Modal -->
<div id="view-role-modal" class="modal-backdrop hidden" onclick="if(event.target === this) closeViewRoleModal()">
    <div class="modal-container-wide">
        <div class="modal-header">
            <h2 class="modal-title">View Admin Role Details</h2>
        </div>
        <div class="modal-body max-h-[70vh] overflow-y-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="form-group md:col-span-2">
                    <label class="form-label">Role Name</label>
                    <p id="view-role-name" class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]"></p>
                </div>
                <div class="form-group md:col-span-2">
                    <label class="form-label">Description</label>
                    <p id="view-role-description" class="text-sm text-[#706f6c] dark:text-[#A1A09A]"></p>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <p><span id="view-role-status" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"></span></p>
                </div>
            </div>

            <div class="mt-6 border-t border-[#e3e3e0] dark:border-[#3E3E3A] pt-4">
                <h3 class="text-md font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Assigned Privileges</h3>
                <div id="view-privileges-list" class="space-y-4">
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Loading privileges...</p>
                </div>
            </div>

            <div class="mt-6 border-t border-[#e3e3e0] dark:border-[#3E3E3A] pt-4">
                <h3 class="text-md font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Report Notification Targets</h3>
                <div id="view-report-targets-list" class="flex flex-wrap gap-2">
                    <!-- Rendered by JS -->
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" onclick="closeViewRoleModal()" class="btn btn-secondary">Close</button>
        </div>
    </div>
</div>

<!-- Delete Role Confirmation Modal -->
<div id="delete-role-modal" class="modal-backdrop hidden" onclick="if(event.target === this) closeDeleteRoleModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title text-red-500 flex items-center gap-2">
                <x-heroicon-o-exclamation-triangle class="modal-icon-error" />
                Delete Admin Role
            </h2>
        </div>
        <div class="modal-body">
            <p id="deleteRoleMessage">Are you sure you want to delete this admin role? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button onclick="closeDeleteRoleModal()" class="btn btn-secondary">Cancel</button>
            <button onclick="confirmDeleteRole()" class="btn btn-danger">Delete</button>
        </div>
    </div>
</div>

