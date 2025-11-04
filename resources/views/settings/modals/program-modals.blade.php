<!-- Add Program Modal -->
<div id="add-program-modal" class="modal-backdrop hidden" onclick="if(event.target === this) closeAddProgramModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title">Add New Program</h2>
        </div>
        <form id="addProgramForm">
            <div class="modal-body">
                <div class="form-group mb-4">
                    <label class="form-label">College <span class="text-red-500">*</span></label>
                    <select id="modal-program-college-id" class="form-input" required>
                        <option value="">Select College</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Program Name <span class="text-red-500">*</span></label>
                    <input type="text" id="modal-program-name" class="form-input" placeholder="e.g. Bachelor of Science in Electrical Engineering" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeAddProgramModal()" class="btn btn-secondary">Cancel</button>
                <button type="button" onclick="addProgram()" class="btn btn-primary">Add Program</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Program Modal -->
<div id="edit-program-modal" class="modal-backdrop hidden" onclick="if(event.target === this) closeEditProgramModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title">Edit Program</h2>
        </div>
        <form id="editProgramForm">
            <div class="modal-body">
                <input type="hidden" id="edit-program-id">
                <div class="form-group mb-4">
                    <label class="form-label">College <span class="text-red-500">*</span></label>
                    <select id="edit-program-college-id" class="form-input" required>
                        <option value="">Select College</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Program Name <span class="text-red-500">*</span></label>
                    <input type="text" id="edit-program-name" class="form-input" placeholder="e.g. Bachelor of Science in Electrical Engineering" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeEditProgramModal()" class="btn btn-secondary">Cancel</button>
                <button type="button" onclick="updateProgram()" class="btn btn-primary">Update Program</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Program Confirmation Modal -->
<div id="delete-program-modal" class="modal-backdrop hidden" onclick="if(event.target === this) closeDeleteProgramModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title text-red-500 flex items-center gap-2">
                <x-heroicon-o-exclamation-triangle class="modal-icon-error" />
                Delete Program
            </h2>
        </div>
        <div class="modal-body">
            <p id="deleteProgramMessage">Are you sure you want to delete this program? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button onclick="closeDeleteProgramModal()" class="btn btn-secondary">Cancel</button>
            <button onclick="confirmDeleteProgram()" class="btn btn-danger">Delete</button>
        </div>
    </div>
</div>

