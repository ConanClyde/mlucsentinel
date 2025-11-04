<!-- Add Vehicle Type Modal -->
<div id="add-vehicle-type-modal" class="modal-backdrop hidden" onclick="if(event.target === this) closeAddVehicleTypeModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title">Add New Vehicle Type</h2>
        </div>
        <form id="addVehicleTypeForm">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Vehicle Type Name <span class="text-red-500">*</span></label>
                    <input type="text" id="modal-vehicle-type-name" class="form-input" placeholder="Enter vehicle type name" required>
                </div>
                <div class="form-group">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" id="modal-vehicle-type-requires-plate" class="form-checkbox" checked>
                        <span class="form-label mb-0">Requires Plate Number</span>
                    </label>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-1">If unchecked, vehicles of this type will not require a plate number (e.g., Electric Vehicles)</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeAddVehicleTypeModal()" class="btn btn-secondary">Cancel</button>
                <button type="button" onclick="addVehicleType()" class="btn btn-primary">Add Vehicle Type</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Vehicle Type Modal -->
<div id="edit-vehicle-type-modal" class="modal-backdrop hidden" onclick="if(event.target === this) closeEditVehicleTypeModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title">Edit Vehicle Type</h2>
        </div>
        <form id="editVehicleTypeForm">
            <div class="modal-body">
                <input type="hidden" id="edit-vehicle-type-id">
                <div class="form-group">
                    <label class="form-label">Vehicle Type Name <span class="text-red-500">*</span></label>
                    <input type="text" id="edit-vehicle-type-name" class="form-input" placeholder="Enter vehicle type name" required>
                </div>
                <div class="form-group">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" id="edit-vehicle-type-requires-plate" class="form-checkbox">
                        <span class="form-label mb-0">Requires Plate Number</span>
                    </label>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-1">If unchecked, vehicles of this type will not require a plate number (e.g., Electric Vehicles)</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeEditVehicleTypeModal()" class="btn btn-secondary">Cancel</button>
                <button type="button" onclick="updateVehicleType()" class="btn btn-primary">Update Vehicle Type</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Vehicle Type Confirmation Modal -->
<div id="delete-vehicle-type-modal" class="modal-backdrop hidden" onclick="if(event.target === this) closeDeleteVehicleTypeModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title text-red-500 flex items-center gap-2">
                <x-heroicon-o-exclamation-triangle class="modal-icon-error" />
                Delete Vehicle Type
            </h2>
        </div>
        <div class="modal-body">
            <p id="deleteVehicleTypeMessage">Are you sure you want to delete this vehicle type? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button onclick="closeDeleteVehicleTypeModal()" class="btn btn-secondary">Cancel</button>
            <button onclick="confirmDeleteVehicleType()" class="btn btn-danger">Delete</button>
        </div>
    </div>
</div>

