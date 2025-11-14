<!-- Add Sticker Color Modal -->
<div id="add-sticker-color-modal" class="modal-backdrop hidden" onclick="if(event.target === this) closeAddStickerColorModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title">Add New Sticker Color</h2>
        </div>
        <form id="addStickerColorForm">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Color Name <span class="text-red-500">*</span></label>
                    <input type="text" id="modal-sticker-color-name" class="form-input" placeholder="e.g. Emerald Green" required>
                    <p id="modal-sticker-color-name-error" class="text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div class="form-group">
                    <label class="form-label">Color <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-2">
                        <input type="color" id="modal-sticker-color" class="h-10 w-20 rounded border border-[#e3e3e0] dark:border-[#3E3E3A]" value="#3B82F6">
                        <input type="text" id="modal-sticker-color-hex" class="form-input flex-1" placeholder="#3B82F6" pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$" required>
                    </div>
                    <p id="modal-sticker-color-error" class="text-red-500 text-xs mt-1 hidden"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeAddStickerColorModal()" class="btn btn-secondary">Cancel</button>
                <button type="button" id="add-sticker-color-btn" onclick="addStickerColor()" class="btn btn-primary" disabled>Add Color</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Sticker Color Modal -->
<div id="edit-sticker-color-modal" class="modal-backdrop hidden" onclick="if(event.target === this) closeEditStickerColorModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title">Edit Sticker Color</h2>
        </div>
        <form id="editStickerColorForm">
            <div class="modal-body">
                <input type="hidden" id="edit-sticker-color-key">
                <div class="form-group">
                    <label class="form-label">Color Name <span class="text-red-500">*</span></label>
                    <input type="text" id="edit-sticker-color-name" class="form-input" placeholder="Enter color name" required>
                    <p id="edit-sticker-color-name-error" class="text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div class="form-group">
                    <label class="form-label">Color <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-2">
                        <input type="color" id="edit-sticker-color" class="h-10 w-20 rounded border border-[#e3e3e0] dark:border-[#3E3E3A]" value="#3B82F6">
                        <input type="text" id="edit-sticker-color-hex" class="form-input flex-1" placeholder="#3B82F6" pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$" required>
                    </div>
                    <p id="edit-sticker-color-error" class="text-red-500 text-xs mt-1 hidden"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeEditStickerColorModal()" class="btn btn-secondary">Cancel</button>
                <button type="button" id="update-sticker-color-btn" onclick="updateStickerColor()" class="btn btn-primary" disabled>Update Color</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Sticker Color Confirmation Modal -->
<div id="delete-sticker-color-modal" class="modal-backdrop hidden" onclick="if(event.target === this) closeDeleteStickerColorModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title text-red-500 flex items-center gap-2">
                <x-heroicon-o-exclamation-triangle class="modal-icon-error" />
                Delete Sticker Color
            </h2>
        </div>
        <div class="modal-body">
            <p id="deleteStickerColorMessage">Are you sure you want to delete this color? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button onclick="closeDeleteStickerColorModal()" class="btn btn-secondary">Cancel</button>
            <button onclick="confirmDeleteStickerColor()" class="btn btn-danger">Delete</button>
        </div>
    </div>
</div>
