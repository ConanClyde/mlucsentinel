<!-- Add Color Modal -->
<div id="addColorModal" class="modal-backdrop hidden" onclick="if(event.target === this) closeAddColorModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title">Add New Sticker Color</h2>
        </div>
        <form id="addColorForm">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Color Name <span class="text-red-500">*</span></label>
                    <input type="text" id="add-color-name" class="form-input" placeholder="e.g., red, purple" required>
                    <p id="add-color-name-error" class="text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div class="form-group">
                    <label class="form-label">Color <span class="text-red-500">*</span></label>
                    <div class="flex gap-2">
                        <input type="color" id="add-color-picker" class="h-10 w-16 rounded border border-[#e3e3e0] dark:border-[#3E3E3A]" value="#000000">
                        <input type="text" id="add-color-hex" class="form-input flex-1" placeholder="#000000" pattern="^#[0-9A-Fa-f]{6}$" required>
                    </div>
                    <p id="add-color-hex-error" class="text-red-500 text-xs mt-1 hidden"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeAddColorModal()" class="btn btn-secondary">Cancel</button>
                <button type="button" onclick="submitAddColor()" class="btn btn-primary">Add Color</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Color Modal -->
<div id="editColorModal" class="modal-backdrop hidden" onclick="if(event.target === this) closeEditColorModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title">Edit Sticker Color</h2>
        </div>
        <form id="editColorForm">
            <div class="modal-body">
                <input type="hidden" id="edit-color-old-key">
                <div class="form-group">
                    <label class="form-label">Color Name <span class="text-red-500">*</span></label>
                    <input type="text" id="edit-color-name" class="form-input" placeholder="e.g., red, purple" required>
                    <p id="edit-color-name-error" class="text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div class="form-group">
                    <label class="form-label">Color <span class="text-red-500">*</span></label>
                    <div class="flex gap-2">
                        <input type="color" id="edit-color-picker" class="h-10 w-16 rounded border border-[#e3e3e0] dark:border-[#3E3E3A]">
                        <input type="text" id="edit-color-hex" class="form-input flex-1" placeholder="#000000" pattern="^#[0-9A-Fa-f]{6}$" required>
                    </div>
                    <p id="edit-color-hex-error" class="text-red-500 text-xs mt-1 hidden"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeEditColorModal()" class="btn btn-secondary">Cancel</button>
                <button type="button" onclick="submitEditColor()" class="btn btn-primary">Update Color</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Color Confirmation Modal -->
<div id="deleteColorModal" class="modal-backdrop hidden" onclick="if(event.target === this) closeDeleteColorModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title text-red-500 flex items-center gap-2">
                <x-heroicon-o-exclamation-triangle class="modal-icon-error" />
                Delete Sticker Color
            </h2>
        </div>
        <div class="modal-body">
            <input type="hidden" id="delete-color-key">
            <p id="deleteColorMessage">Are you sure you want to delete this sticker color? This action cannot be undone.</p>
            <p class="text-sm text-red-600 dark:text-red-400 mt-2">Warning: This will affect any sticker rules using this color.</p>
        </div>
        <div class="modal-footer">
            <button onclick="closeDeleteColorModal()" class="btn btn-secondary">Cancel</button>
            <button onclick="submitDeleteColor()" class="btn btn-danger">Delete</button>
        </div>
    </div>
</div>
