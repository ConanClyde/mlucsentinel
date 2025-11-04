<!-- Add Location Type Modal -->
<div id="add-location-type-modal" class="modal-backdrop hidden" onclick="if(event.target === this) closeAddLocationTypeModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title">Add New Location Type</h2>
        </div>
        <form id="addLocationTypeForm">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Location Type Name <span class="text-red-500">*</span></label>
                    <input type="text" id="modal-location-type-name" class="form-input" placeholder="Enter location type name" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Default Color <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-2">
                        <input type="color" id="modal-location-type-color" class="h-10 w-20 rounded border border-[#e3e3e0] dark:border-[#3E3E3A]" value="#3B82F6">
                        <input type="text" id="modal-location-type-color-hex" class="form-input flex-1" placeholder="#3B82F6" pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea id="modal-location-type-description" class="form-input" rows="3" placeholder="Enter description (optional)"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeAddLocationTypeModal()" class="btn btn-secondary">Cancel</button>
                <button type="button" onclick="addLocationType()" class="btn btn-primary">Add Location Type</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Location Type Modal -->
<div id="edit-location-type-modal" class="modal-backdrop hidden" onclick="if(event.target === this) closeEditLocationTypeModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title">Edit Location Type</h2>
        </div>
        <form id="editLocationTypeForm">
            <div class="modal-body">
                <input type="hidden" id="edit-location-type-id">
                <div class="form-group">
                    <label class="form-label">Location Type Name <span class="text-red-500">*</span></label>
                    <input type="text" id="edit-location-type-name" class="form-input" placeholder="Enter location type name" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Default Color <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-2">
                        <input type="color" id="edit-location-type-color" class="h-10 w-20 rounded border border-[#e3e3e0] dark:border-[#3E3E3A]" value="#3B82F6">
                        <input type="text" id="edit-location-type-color-hex" class="form-input flex-1" placeholder="#3B82F6" pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea id="edit-location-type-description" class="form-input" rows="3" placeholder="Enter description (optional)"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeEditLocationTypeModal()" class="btn btn-secondary">Cancel</button>
                <button type="button" onclick="updateLocationType()" class="btn btn-primary">Update Location Type</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Location Type Confirmation Modal -->
<div id="delete-location-type-modal" class="modal-backdrop hidden" onclick="if(event.target === this) closeDeleteLocationTypeModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title text-red-500 flex items-center gap-2">
                <x-heroicon-o-exclamation-triangle class="modal-icon-error" />
                Delete Location Type
            </h2>
        </div>
        <div class="modal-body">
            <p id="deleteLocationTypeMessage">Are you sure you want to delete this location type? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button onclick="closeDeleteLocationTypeModal()" class="btn btn-secondary">Cancel</button>
            <button onclick="confirmDeleteLocationType()" class="btn btn-danger">Delete</button>
        </div>
    </div>
</div>

