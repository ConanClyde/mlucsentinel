<!-- Location Form Modal -->
<div id="location-modal" class="modal-backdrop hidden" onclick="if(event.target === this) closeLocationModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title" id="modal-title">Add New Location</h2>
            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mt-1" id="vertices-info">0 points added</p>
        </div>
        <form id="location-form">
            @csrf
            <input type="hidden" id="location-id" name="location_id">
            <input type="hidden" id="location-vertices" name="vertices">

            <div class="modal-body max-h-[70vh] overflow-y-auto">
                <div class="space-y-4">
                    <!-- Type -->
                    <div class="form-group">
                        <label for="location-type" class="form-label">
                            Location Type <span class="text-red-500">*</span>
                        </label>
                        <select id="location-type" name="type_id" required class="form-input">
                            <option value="">Select a type...</option>
                            @foreach($locationTypes as $type)
                            <option value="{{ $type->id }}" data-color="{{ $type->default_color }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-red-500 mt-1 hidden" id="error-type_id"></p>
                    </div>

                    <!-- Name -->
                    <div class="form-group">
                        <label for="location-name" class="form-label">
                            Location Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="location-name" name="name" required maxlength="255"
                               class="form-input"
                               placeholder="e.g., Main Parking Area">
                        <p class="text-xs text-red-500 mt-1 hidden" id="error-name"></p>
                    </div>

                    <!-- Short Code -->
                    <div class="form-group">
                        <label for="location-code" class="form-label">
                            Short Code
                        </label>
                        <input type="text" id="location-code" name="short_code" maxlength="6"
                               class="form-input"
                               placeholder="e.g., P1, MB, E1">
                        <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-1">Optional short identifier displayed on the map</p>
                        <p class="text-xs text-red-500 mt-1 hidden" id="error-short_code"></p>
                    </div>

                    <!-- Description -->
                    <div class="form-group">
                        <label for="location-description" class="form-label">
                            Description
                        </label>
                        <textarea id="location-description" name="description" rows="3" maxlength="1000"
                                  class="form-input"
                                  placeholder="Additional details about this location..."></textarea>
                        <p class="text-xs text-red-500 mt-1 hidden" id="error-description"></p>
                    </div>

                    <!-- Color -->
                    <div class="form-group">
                        <label for="location-color" class="form-label">
                            Color <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center space-x-2">
                            <input type="color" id="location-color" name="color" value="#3B82F6" required
                                   class="w-12 h-10 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded cursor-pointer">
                            <input type="text" id="location-color-text" value="#3B82F6" maxlength="7" pattern="^#[0-9A-Fa-f]{6}$"
                                   class="form-input flex-1"
                                   placeholder="#3B82F6">
                        </div>
                        <p class="text-xs text-red-500 mt-1 hidden" id="error-color"></p>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" onclick="closeLocationModal()" class="btn btn-secondary">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    Save Location
                </button>
            </div>
        </form>
    </div>
</div>

<!-- View Location Modal -->
<div id="viewLocationModal" class="modal-backdrop hidden" onclick="if(event.target === this) closeViewLocationModal()">
    <div class="bg-white dark:bg-[#161615] rounded-lg shadow-xl max-w-4xl w-full mx-4 transform transition-all">
        <div class="modal-header">
            <h2 class="modal-title">Location Details</h2>
        </div>
        <div class="modal-body">
            <div id="viewLocationContent">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
        <div class="modal-footer">
            <button onclick="closeViewLocationModal()" class="btn btn-secondary">Close</button>
        </div>
    </div>
</div>

<!-- Delete Location Modal -->
<div id="deleteLocationModal" class="modal-backdrop hidden" onclick="if(event.target === this) closeDeleteLocationModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title text-red-500 flex items-center gap-2">
                <x-heroicon-o-exclamation-triangle class="modal-icon-error" />
                Delete Location
            </h2>
        </div>
        <div class="modal-body">
            <p id="deleteLocationMessage"></p>
        </div>
        <div class="modal-footer">
            <button onclick="closeDeleteLocationModal()" class="btn btn-secondary">Cancel</button>
            <button onclick="confirmDeleteLocation()" class="btn btn-danger">Delete</button>
        </div>
    </div>
</div>
