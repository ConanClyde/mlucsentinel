<!-- Add College Modal -->
<div id="add-college-modal" class="modal-backdrop hidden" onclick="if(event.target === this) closeAddCollegeModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title">Add New College</h2>
        </div>
        <form id="addCollegeForm">
            <div class="modal-body">
                <div class="form-group mb-4">
                    <label class="form-label">College Name <span class="text-red-500">*</span></label>
                    <input type="text" id="modal-college-name" class="form-input" placeholder="Enter college name" required>
                    <p id="modal-college-name-error" class="text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div class="form-group mb-4">
                    <label class="form-label">College Code <span class="text-red-500">*</span></label>
                    <input type="text" id="modal-college-code" class="form-input uppercase" placeholder="e.g. CGS" maxlength="20" required>
                    <p id="modal-college-code-error" class="text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div class="form-group mb-4">
                    <label class="form-label">College Type</label>
                    <input type="text" id="modal-college-type" class="form-input" placeholder="e.g. college" value="college">
                    <p id="modal-college-type-error" class="text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea id="modal-college-description" class="form-input" rows="3" placeholder="Brief description about the college"></textarea>
                    <p id="modal-college-description-error" class="text-red-500 text-xs mt-1 hidden"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeAddCollegeModal()" class="btn btn-secondary">Cancel</button>
                <button type="button" id="add-college-btn" onclick="addCollege()" class="btn btn-primary" disabled>Add College</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit College Modal -->
<div id="edit-college-modal" class="modal-backdrop hidden" onclick="if(event.target === this) closeEditCollegeModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title">Edit College</h2>
        </div>
        <form id="editCollegeForm">
            <div class="modal-body">
                <input type="hidden" id="edit-college-id">
                <div class="form-group mb-4">
                    <label class="form-label">College Name <span class="text-red-500">*</span></label>
                    <input type="text" id="edit-college-name" class="form-input" placeholder="Enter college name" required>
                    <p id="edit-college-name-error" class="text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div class="form-group mb-4">
                    <label class="form-label">College Code <span class="text-red-500">*</span></label>
                    <input type="text" id="edit-college-code" class="form-input uppercase" placeholder="e.g. CGS" maxlength="20" required>
                    <p id="edit-college-code-error" class="text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div class="form-group mb-4">
                    <label class="form-label">College Type</label>
                    <input type="text" id="edit-college-type" class="form-input" placeholder="e.g. college">
                    <p id="edit-college-type-error" class="text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea id="edit-college-description" class="form-input" rows="3" placeholder="Brief description about the college"></textarea>
                    <p id="edit-college-description-error" class="text-red-500 text-xs mt-1 hidden"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeEditCollegeModal()" class="btn btn-secondary">Cancel</button>
                <button type="button" id="update-college-btn" onclick="updateCollege()" class="btn btn-primary" disabled>Update College</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete College Confirmation Modal -->
<div id="delete-college-modal" class="modal-backdrop hidden" onclick="if(event.target === this) closeDeleteCollegeModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title text-red-500 flex items-center gap-2">
                <x-heroicon-o-exclamation-triangle class="modal-icon-error" />
                Delete College
            </h2>
        </div>
        <div class="modal-body">
            <p id="deleteCollegeMessage">Are you sure you want to delete this college? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button onclick="closeDeleteCollegeModal()" class="btn btn-secondary">Cancel</button>
            <button onclick="confirmDeleteCollege()" class="btn btn-danger">Delete</button>
        </div>
    </div>
</div>

