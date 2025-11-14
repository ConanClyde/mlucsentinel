<!-- Add Stakeholder Type Modal -->
<div id="addStakeholderTypeModal" class="modal-backdrop hidden" onclick="if(event.target === this) closeAddStakeholderTypeModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title">Add New Stakeholder Type</h2>
        </div>
        <form id="addStakeholderTypeForm">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Name <span class="text-red-500">*</span></label>
                    <input type="text" id="add-stakeholder-name" class="form-input" placeholder="e.g., Contractor, Vendor" required>
                    <p id="add-stakeholder-name-error" class="text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div class="form-group">
                    <label class="form-label">Description (Optional)</label>
                    <textarea id="add-stakeholder-description" class="form-input" rows="3" placeholder="Brief description of this stakeholder type"></textarea>
                    <p id="add-stakeholder-description-error" class="text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div class="form-group">
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="form-label">Require Guardian Evidence</label>
                            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Require stakeholders of this type to upload proof of children's university ID</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="add-stakeholder-evidence-required" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeAddStakeholderTypeModal()" class="btn btn-secondary">Cancel</button>
                <button type="button" onclick="submitAddStakeholderType()" class="btn btn-primary">Add Stakeholder Type</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Stakeholder Type Modal -->
<div id="editStakeholderTypeModal" class="modal-backdrop hidden" onclick="if(event.target === this) closeEditStakeholderTypeModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title">Edit Stakeholder Type</h2>
        </div>
        <form id="editStakeholderTypeForm">
            <div class="modal-body">
                <input type="hidden" id="edit-stakeholder-id">
                <div class="form-group">
                    <label class="form-label">Name <span class="text-red-500">*</span></label>
                    <input type="text" id="edit-stakeholder-name" class="form-input" required>
                    <p id="edit-stakeholder-name-error" class="text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div class="form-group">
                    <label class="form-label">Description (Optional)</label>
                    <textarea id="edit-stakeholder-description" class="form-input" rows="3"></textarea>
                    <p id="edit-stakeholder-description-error" class="text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div class="form-group">
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="form-label">Require Guardian Evidence</label>
                            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Require stakeholders of this type to upload proof of children's university ID</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="edit-stakeholder-evidence-required" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeEditStakeholderTypeModal()" class="btn btn-secondary">Cancel</button>
                <button type="button" onclick="submitEditStakeholderType()" class="btn btn-primary">Update Stakeholder Type</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Stakeholder Type Confirmation Modal -->
<div id="deleteStakeholderTypeModal" class="modal-backdrop hidden" onclick="if(event.target === this) closeDeleteStakeholderTypeModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title text-red-500 flex items-center gap-2">
                <x-heroicon-o-exclamation-triangle class="modal-icon-error" />
                Delete Stakeholder Type
            </h2>
        </div>
        <div class="modal-body">
            <input type="hidden" id="delete-stakeholder-id">
            <p id="deleteStakeholderMessage">Are you sure you want to delete this stakeholder type? This action cannot be undone.</p>
            <p class="text-sm text-red-600 dark:text-red-400 mt-2">Warning: This type cannot be deleted if it is currently in use.</p>
        </div>
        <div class="modal-footer">
            <button onclick="closeDeleteStakeholderTypeModal()" class="btn btn-secondary">Cancel</button>
            <button onclick="submitDeleteStakeholderType()" class="btn btn-danger">Delete</button>
        </div>
    </div>
</div>
