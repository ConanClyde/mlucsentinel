<!-- Add College Modal -->
<div id="add-college-modal" class="modal-backdrop hidden" onclick="if(event.target === this) closeAddCollegeModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title">Add New College</h2>
        </div>
        <form id="addCollegeForm">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">College Name <span class="text-red-500">*</span></label>
                    <input type="text" id="modal-college-name" class="form-input" placeholder="Enter college name" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeAddCollegeModal()" class="btn btn-secondary">Cancel</button>
                <button type="button" onclick="addCollege()" class="btn btn-primary">Add College</button>
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
                <div class="form-group">
                    <label class="form-label">College Name <span class="text-red-500">*</span></label>
                    <input type="text" id="edit-college-name" class="form-input" placeholder="Enter college name" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeEditCollegeModal()" class="btn btn-secondary">Cancel</button>
                <button type="button" onclick="updateCollege()" class="btn btn-primary">Update College</button>
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

