<!-- Add Reporter Role Modal -->
<div id="add-reporter-role-modal" class="modal-backdrop hidden">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title">Add Reporter Role</h2>
        </div>
        <form id="add-reporter-role-form" onsubmit="addReporterRole(event)">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Role Name <span class="text-red-500">*</span></label>
                    <input type="text" id="reporter-add-role-name" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea id="reporter-add-role-description" class="form-input" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Can Report <span class="text-red-500">*</span></label>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mb-2">Select which user types this reporter role can report</p>
                    <div class="space-y-2" id="reporter-add-user-types-list">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Default Expiration (Years) <span class="text-xs text-[#706f6c] dark:text-[#A1A09A]">(Optional)</span></label>
                    <input type="number" id="reporter-add-expiration-years" class="form-input" min="1" placeholder="e.g., 1, 2, 3...">
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-1">Reporters with this role will expire after this many years. Leave empty for no expiration.</p>
                </div>

                <div class="form-group">
                    <label class="flex items-center">
                        <input type="checkbox" id="reporter-add-role-is-active" class="form-checkbox" checked>
                        <span class="ml-2 text-sm text-[#1b1b18] dark:text-[#EDEDEC]">Active</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeAddReporterRoleModal()" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Role</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Reporter Role Modal -->
<div id="edit-reporter-role-modal" class="modal-backdrop hidden">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title">Edit Reporter Role</h2>
        </div>
        <form id="edit-reporter-role-form" onsubmit="updateReporterRole(event)">
            <input type="hidden" id="reporter-edit-role-id">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Role Name <span class="text-red-500">*</span></label>
                    <input type="text" id="reporter-edit-role-name" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea id="reporter-edit-role-description" class="form-input" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Can Report <span class="text-red-500">*</span></label>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mb-2">Select which user types this reporter role can report</p>
                    <div class="space-y-2" id="reporter-edit-user-types-list">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Default Expiration (Years) <span class="text-xs text-[#706f6c] dark:text-[#A1A09A]">(Optional)</span></label>
                    <input type="number" id="reporter-edit-expiration-years" class="form-input" min="1" placeholder="e.g., 1, 2, 3...">
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-1">Reporters with this role will expire after this many years. Leave empty for no expiration.</p>
                </div>

                <div class="form-group">
                    <label class="flex items-center">
                        <input type="checkbox" id="reporter-edit-role-is-active" class="form-checkbox">
                        <span class="ml-2 text-sm text-[#1b1b18] dark:text-[#EDEDEC]">Active</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeEditReporterRoleModal()" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- View Reporter Role Modal -->
<div id="view-reporter-role-modal" class="modal-backdrop hidden">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title">Reporter Role Details</h2>
        </div>
        <div class="modal-body">
            <div class="space-y-4">
                <div>
                    <label class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Role Name</label>
                    <p id="reporter-view-role-name" class="text-[#1b1b18] dark:text-[#EDEDEC] font-medium"></p>
                </div>

                <div>
                    <label class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Description</label>
                    <p id="reporter-view-role-description" class="text-[#1b1b18] dark:text-[#EDEDEC]"></p>
                </div>

                <div>
                    <label class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Can Report</label>
                    <div id="reporter-view-user-types" class="flex flex-wrap gap-2 mt-2">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>

                <div>
                    <label class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Status</label>
                    <p id="reporter-view-role-status"></p>
                </div>

                <div>
                    <label class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Reporters Using This Role</label>
                    <p id="reporter-view-reporters-count" class="text-[#1b1b18] dark:text-[#EDEDEC]"></p>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button onclick="closeViewReporterRoleModal()" class="btn btn-secondary">Close</button>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="delete-reporter-role-modal" class="modal-backdrop hidden">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title text-red-500 flex items-center gap-2">
                <x-heroicon-o-exclamation-triangle class="modal-icon-error" />
                Delete Reporter Role
            </h2>
        </div>
        <div class="modal-body">
            <p id="reporter-delete-role-message"></p>
        </div>
        <div class="modal-footer">
            <button onclick="closeDeleteReporterRoleModal()" class="btn btn-secondary">Cancel</button>
            <button onclick="confirmDeleteReporterRole()" class="btn btn-danger">Delete</button>
        </div>
    </div>
</div>
