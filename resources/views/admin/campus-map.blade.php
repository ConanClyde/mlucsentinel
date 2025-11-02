@extends('layouts.app')

@section('page-title', 'Campus Map')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Campus Map Manager</h1>
            <p class="text-[#706f6c] dark:text-[#A1A09A]">Manage patrol locations and map polygons</p>
        </div>
        <div class="flex space-x-2">
            <button id="add-location-btn" class="btn btn-primary">
                Add Location
            </button>
            <button id="toggle-legend-btn" class="btn btn-secondary">
                Legend
            </button>
            <button id="toggle-locations-btn" class="btn btn-secondary">
                Hide Locations
            </button>
        </div>
    </div>

    <!-- Drawing Mode Notice -->
    <div id="drawing-notice" class="hidden bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
            </svg>
            <div class="ml-3 flex-1">
                <h3 class="text-sm font-semibold text-blue-800 dark:text-blue-300">Drawing Mode Active</h3>
                <p class="text-sm text-blue-700 dark:text-blue-400 mt-1">
                    Click on the map to add points. Need at least 3 points. 
                    <span class="font-semibold" id="point-counter">Points: 0</span>
                </p>
                <div class="flex space-x-2 mt-2">
                    <button id="undo-point-btn" class="btn btn-warning" disabled>
                        Undo Last Point
                    </button>
                    <button id="complete-polygon-btn" class="btn btn-success" disabled>
                        Complete Polygon
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Editing Vertices Mode Notice -->
    <div id="editing-notice" class="hidden bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-green-600 dark:text-green-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
            </svg>
            <div class="ml-3 flex-1">
                <h3 class="text-sm font-semibold text-green-800 dark:text-green-300">Editing Vertices Mode</h3>
                <p class="text-sm text-green-700 dark:text-green-400 mt-1">
                    Drag to move • Click polygon to add • Right-click to delete • Click "Save Location" when done
                    <span class="font-semibold" id="edit-point-counter">Points: 0</span>
                </p>
                <div class="flex space-x-2 mt-2">
                    <button id="undo-edit-point-btn" class="btn btn-warning" disabled>
                        Remove Last Point
                    </button>
                    <button id="undo-vertex-change-btn" class="btn btn-secondary" disabled>
                        Undo
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Map and Table Layout -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- Map Container (2/3 width on desktop) -->
        <div class="xl:col-span-2">
            <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4">
                <div class="relative overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-900" id="campus-map-container">
            <div id="map-wrapper" class="relative w-full origin-center transition-transform duration-300 ease-out">
                <!-- Campus Map Image -->
                <img id="campus-map" src="{{ asset('images/campus-map.svg') }}" alt="Campus Map" 
                     class="block w-full h-auto select-none" 
                     draggable="false"
                     onerror="handleMapError(this)"
                     onload="initializeMapDimensions()">

                <!-- Locations Overlay Canvas -->
                <div id="locations-overlay" class="absolute inset-0 pointer-events-none z-10">
                    <!-- Saved locations will be rendered here as SVG polygons -->
                    <svg class="w-full h-full" id="locations-svg" viewBox="0 0 100 100" preserveAspectRatio="none">
                        <defs>
                            <filter id="glow">
                                <feGaussianBlur stdDeviation="0.5" result="coloredBlur"/>
                                <feMerge>
                                    <feMergeNode in="coloredBlur"/>
                                    <feMergeNode in="SourceGraphic"/>
                                </feMerge>
                            </filter>
                        </defs>
                    </svg>
                </div>

                <!-- Drawing Canvas (INSIDE wrapper so it DOES transform with the map) -->
                <svg id="drawing-canvas" class="absolute inset-0 w-full h-full pointer-events-none z-20" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <!-- Preview polygon will be drawn here -->
                    <polygon id="preview-polygon" points="" fill="rgba(37, 99, 235, 0.2)" stroke="#2563eb" stroke-width="0.3" class="hidden"/>
                    <!-- Preview points -->
                    <g id="preview-points"></g>
                </svg>
            </div>

            <!-- Zoom Controls (inside container, outside wrapper so they don't zoom) -->
            <div class="absolute top-2 right-2 bg-white dark:bg-[#1a1a1a] rounded-lg shadow-lg border border-[#e3e3e0] dark:border-[#3E3E3A] p-2 space-y-2 z-30 pointer-events-auto">
                <button id="zoom-in-btn" class="flex items-center justify-center w-8 h-8 bg-blue-600 dark:bg-blue-600 text-white rounded hover:bg-blue-700 dark:hover:bg-blue-700 transition-colors">
                    <span class="text-lg font-bold leading-none">+</span>
                </button>
                <button id="zoom-out-btn" class="flex items-center justify-center w-8 h-8 bg-blue-600 dark:bg-blue-600 text-white rounded hover:bg-blue-700 dark:hover:bg-blue-700 transition-colors">
                    <span class="text-lg font-bold leading-none">−</span>
                </button>
                <button id="reset-zoom-btn" class="flex items-center justify-center w-8 h-8 border border-[#19140035] dark:border-[#3E3E3A] text-[#1b1b18] dark:text-[#EDEDEC] rounded hover:border-[#1915014a] dark:hover:border-[#62605b] transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </button>
            </div>
        </div>
            </div>
        </div>

        <!-- Locations Table (1/3 width on desktop, full width on tablet/mobile) -->
        <div class="xl:col-span-1">
            <!-- Locations List -->
            <div id="locations-list-container" class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 max-h-[600px] overflow-y-auto">
                <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4 sticky top-0 bg-white dark:bg-[#1a1a1a] pb-2">Locations</h3>
                <div class="space-y-2">
                    @forelse($locations as $location)
                    <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-lg p-3 hover:bg-gray-50 dark:hover:bg-[#161615] transition-colors" data-location-id="{{ $location->id }}">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-2 mb-1">
                                    <div class="w-3 h-3 rounded" style="background-color: {{ $location->color }};"></div>
                                    <span class="text-sm font-semibold text-[#1b1b18] dark:text-[#EDEDEC] truncate">
                                        {{ $location->short_code ?? $location->name }}
                                    </span>
                                </div>
                                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] truncate">{{ $location->name }}</p>
                                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-1">{{ $location->type->name }}</p>
                            </div>
                            <div class="flex items-center space-x-1 ml-2">
                                <button onclick="viewLocationDetails({{ $location->id }})" class="btn-view" title="View">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                                <button onclick="editLocation({{ $location->id }})" class="btn-edit" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <button onclick="deleteLocation({{ $location->id }})" class="btn-delete" title="Delete">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                        No locations yet.<br>Click "Add Location" to start.
                    </div>
                    @endforelse
        </div>
    </div>

            <!-- Inline Location Form (Hidden by default) -->
            <div id="inline-location-form" class="hidden bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A]">
                <!-- Header -->
                <div class="p-4 pb-3 border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
                    <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]" id="form-title">Add New Location</h3>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mt-1" id="form-vertices-info">0 points added (optional for patrol-only locations)</p>
                    <div id="edit-vertices-help" class="hidden mt-2 p-2 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded text-xs text-blue-800 dark:text-blue-200">
                        <p class="font-semibold mb-1">Edit Vertices:</p>
                        <ul class="space-y-0.5 ml-4 list-disc">
                            <li><strong>Drag</strong> vertices to move them</li>
                            <li><strong>Click</strong> on polygon to add new vertex</li>
                            <li><strong>Right-click</strong> vertex to delete (min. 3 required)</li>
                        </ul>
        </div>
    </div>

        <form id="location-form">
            @csrf
            <input type="hidden" id="location-id" name="location_id">
            <input type="hidden" id="location-vertices" name="vertices">
            <input type="hidden" id="location-center-x" name="center_x">
            <input type="hidden" id="location-center-y" name="center_y">

                    <div class="p-4 space-y-4">
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
                        <input type="text" id="location-code" name="short_code" maxlength="10"
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
                        <input type="hidden" id="location-color" name="color" value="#3B82F6" required>
                        <!-- Color palette removed: color is controlled by selected Location Type -->
                        <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-1">Color is controlled by the selected Location Type.</p>
                        <p class="text-xs text-red-500 mt-1 hidden" id="error-color"></p>
                    </div>


                        <!-- Action Buttons -->
                        <div class="flex space-x-2 pt-2">
                            <button type="button" onclick="closeInlineForm()" class="btn btn-secondary flex-1">
                    Cancel
                </button>
                            <button type="submit" class="btn btn-primary flex-1">
                    Save Location
                </button>
                        </div>
            </div>
        </form>
            </div>
    </div>
    </div>

    <!-- Legend -->
    <div id="map-legend" class="hidden bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4">
        <h3 class="text-md font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-3">Map Legend</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4" id="legend-items">
            @foreach($locationTypes as $type)
            <div class="flex items-center space-x-2">
                <div class="w-6 h-6 rounded" style="background-color: {{ $type->default_color }};"></div>
                <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">{{ $type->name }}</span>
            </div>
            @endforeach
        </div>
    </div>

</div>

<!-- View Location Modal -->
<div id="viewLocationModal" class="modal-backdrop hidden" onclick="if(event.target === this) closeViewLocationModal()">
    <div class="modal-container-wide">
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

<script>
// Global state
let locationTypes = @json($locationTypes);
let locations = @json($locations);
let isDrawing = false;
let currentPoints = [];
let currentScale = 1;
let panX = 0;
let panY = 0;
let editingLocationId = null;
let locationsVisible = true;
let hasDragged = false;
let isDraggingPoint = false;
let draggingPointIndex = -1;
let deletingLocationId = null;
let aspectRatio = 1; // Default aspect ratio
let editingVertices = [];
let editVertexColor = '#3B82F6';
let draggingVertexIndex = null;
let vertexHistory = []; // Stack for undo functionality

// Helper function to check if we're in drawing or editing mode
function isInAddOrEditMode() {
    return isDrawing || (editingVertices.length > 0);
}

// Helper function to update map cursor based on mode
function updateMapCursor() {
    const container = document.getElementById('campus-map-container');
    if (container) {
        container.style.cursor = isInAddOrEditMode() ? 'crosshair' : 'grab';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    initializeMap();
    renderLocations();
});

function initializeMapDimensions() {
    const img = document.getElementById('campus-map');
    const container = document.getElementById('campus-map-container');
    
    // Set container height based on image aspect ratio
    if (img && img.complete && img.naturalHeight > 0) {
        aspectRatio = img.naturalHeight / img.naturalWidth; // Update global aspectRatio
        const containerWidth = container.offsetWidth;
        const newHeight = containerWidth * aspectRatio;
        container.style.height = newHeight + 'px';
        container.style.minHeight = newHeight + 'px';
        
        console.log('🖼️ Image dimensions:', img.naturalWidth, 'x', img.naturalHeight);
        
        // DON'T update viewBox - keep it at 0 0 100 100 for percentage-based coordinates
        // The drawing canvas already uses viewBox="0 0 100 100" which works with our percentage system
        console.log('✅ Map dimensions initialized');
    }
}

function initializeMap() {
    const container = document.getElementById('campus-map-container');
    const wrapper = document.getElementById('map-wrapper');
    const img = document.getElementById('campus-map');
    
    // Initialize dimensions when image loads
    if (img.complete) {
        initializeMapDimensions();
    } else {
        img.addEventListener('load', initializeMapDimensions);
    }
    
    // Zoom controls with long press support
    setupLongPressZoom('zoom-in-btn', 0.2);
    setupLongPressZoom('zoom-out-btn', -0.2);
    document.getElementById('reset-zoom-btn').addEventListener('click', resetZoom);

    // Drawing mode
    document.getElementById('add-location-btn').addEventListener('click', enableDrawingMode);
    document.getElementById('undo-point-btn').addEventListener('click', undoLastPoint);
    document.getElementById('complete-polygon-btn').addEventListener('click', completePolygon);
    
    // Editing mode
    document.getElementById('undo-edit-point-btn').addEventListener('click', undoLastEditPoint);
    document.getElementById('undo-vertex-change-btn').addEventListener('click', undoVertexChange);

    // Legend toggle
    document.getElementById('toggle-legend-btn').addEventListener('click', () => {
        document.getElementById('map-legend').classList.toggle('hidden');
    });

    // Locations visibility toggle
    document.getElementById('toggle-locations-btn').addEventListener('click', toggleLocationsVisibility);

    // Map click for adding points
    container.addEventListener('click', handleMapClick);

    // Mouse drag to pan (works even while drawing) - LEFT CLICK ONLY
    let isDraggingMap = false;
    let dragStartX, dragStartY;
    let startPanX, startPanY;
    
    container.addEventListener('mousedown', (e) => {
        // Only work on LEFT click (button === 0 or not right button)
        // Right click (button === 2) is for label dragging
        if (e.button === 2) return; // Ignore right click for map panning
        
        isDraggingMap = true;
        hasDragged = false;
        dragStartX = e.clientX;
        dragStartY = e.clientY;
        startPanX = panX;
        startPanY = panY;
        container.style.cursor = 'grabbing';
        
        // Disable transition while dragging for smooth movement
        wrapper.style.transition = 'none';
    });
    
    container.addEventListener('mousemove', (e) => {
        // Handle point dragging
        if (isDraggingPoint && draggingPointIndex >= 0) {
            const wrapper = document.getElementById('map-wrapper');
            const rect = wrapper.getBoundingClientRect();
            
            const x = ((e.clientX - rect.left) / rect.width) * 100;
            const y = ((e.clientY - rect.top) / rect.height) * 100;
            
            // Update the point position
            currentPoints[draggingPointIndex] = {
                x: parseFloat(x.toFixed(4)),
                y: parseFloat(y.toFixed(4))
            };
            
            drawPreviewPolygon();
            return;
        }
        
        // Handle map dragging
        if (!isDraggingMap) return;
        
        const deltaX = e.clientX - dragStartX;
        const deltaY = e.clientY - dragStartY;
        
        // If moved more than 10 pixels, consider it a drag (increased from 5)
        if (Math.abs(deltaX) > 10 || Math.abs(deltaY) > 10) {
            hasDragged = true;
            
            panX = startPanX + deltaX;
            panY = startPanY + deltaY;
            
            applyMapTransform();
        }
    });
    
    container.addEventListener('mouseup', (e) => {
        // Reset point dragging (only on left button)
        if (isDraggingPoint && e.button === 0) {
            isDraggingPoint = false;
            draggingPointIndex = -1;
            // Reset cursor on all point groups
            document.querySelectorAll('#preview-points g').forEach(g => {
                g.style.cursor = 'pointer';
            });
        }
        
        // Only stop map dragging on left button release (not right button)
        if (isDraggingMap && e.button === 0) {
            isDraggingMap = false;
            container.style.cursor = isInAddOrEditMode() ? 'crosshair' : 'grab';
            
            // Re-enable transition
            wrapper.style.transition = 'transform 0.3s ease-out';
        }
    });
    
    container.addEventListener('mouseleave', () => {
        if (isDraggingMap) {
            isDraggingMap = false;
            container.style.cursor = isInAddOrEditMode() ? 'crosshair' : 'default';
            wrapper.style.transition = 'transform 0.3s ease-out';
        }
    });
    
    // Set initial cursor - update dynamically based on mode
    updateMapCursor();
    
    // Mouse wheel zoom (zoom toward cursor like Google Maps)
    container.addEventListener('wheel', (e) => {
        e.preventDefault();
        
        const delta = e.deltaY > 0 ? -0.1 : 0.1; // Scroll down = zoom out, scroll up = zoom in
        const oldScale = currentScale;
        const newScale = Math.min(Math.max(1, currentScale + delta), 3);
        
        if (newScale !== currentScale) {
            // Get mouse position relative to container center
            const rect = container.getBoundingClientRect();
            const mouseX = e.clientX - rect.left - rect.width / 2;
            const mouseY = e.clientY - rect.top - rect.height / 2;
            
            // Calculate the zoom point offset from center
            // When zooming with center origin, we need to adjust pan to keep cursor point stationary
            const scaleRatio = newScale / oldScale;
            
            // Adjust pan: move by the difference in how far the point is from center at different scales
            panX = panX - (mouseX - panX) * (scaleRatio - 1);
            panY = panY - (mouseY - panY) * (scaleRatio - 1);
            
            // Update scale
            currentScale = newScale;
            
            applyMapTransform();
        }
    }, { passive: false });

    // ESC to cancel drawing
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && isDrawing) {
            cancelDrawingMode();
        }
    });

    // Type change updates color
    document.getElementById('location-type').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const defaultColor = selectedOption.dataset.color;
        if (defaultColor) {
            selectColor(defaultColor);
        }
    });

    // Form submission
    document.getElementById('location-form').addEventListener('submit', handleFormSubmit);
    
    // Handle window resize to maintain aspect ratio
    window.addEventListener('resize', function() {
        initializeMapDimensions();
    });
}

function enableDrawingMode() {
    isDrawing = true;
    currentPoints = [];
    // Disable the Add Location button
    const addLocationBtn = document.getElementById('add-location-btn');
    addLocationBtn.disabled = true;
    addLocationBtn.classList.add('opacity-50', 'cursor-not-allowed');
    // Show the inline form immediately, replacing the locations card
    openLocationModal([]);
    document.getElementById('drawing-notice').classList.remove('hidden');
    updateMapCursor();
    // Initialize form state
    document.getElementById('location-vertices').value = JSON.stringify(currentPoints);
    updateVerticesInfo(currentPoints.length);
    updatePointCounter();
    updateDrawingButtons();
}

function updateVerticesInfo(count) {
    const suffix = count === 0 ? ' (optional for patrol-only locations)' : '';
    document.getElementById('form-vertices-info').textContent = `${count} points added${suffix}`;
}

function cancelDrawingMode() {
    isDrawing = false;
    currentPoints = [];
    // Re-enable the Add Location button
    const addLocationBtn = document.getElementById('add-location-btn');
    addLocationBtn.disabled = false;
    addLocationBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    document.getElementById('drawing-notice').classList.add('hidden');
    updateMapCursor();
    document.getElementById('preview-polygon').classList.add('hidden');
    document.getElementById('preview-points').innerHTML = '';
}

function handleMapClick(e) {
    console.log('🔵 handleMapClick called. isDrawing:', isDrawing, 'hasDragged:', hasDragged);
    
    if (!isDrawing) {
        console.log('❌ Not in drawing mode');
        return;
    }
    
    // Don't add point if user was dragging
    if (hasDragged) {
        console.log('❌ User was dragging, not adding point');
        return;
    }
    
    // Get the wrapper (drawing canvas is inside it, so it transforms together)
    const wrapper = document.getElementById('map-wrapper');
    const rect = wrapper.getBoundingClientRect();
    
    const x = ((e.clientX - rect.left) / rect.width) * 100;
    const y = ((e.clientY - rect.top) / rect.height) * 100;
    
    console.log('🖱️ Click at:', { clientX: e.clientX, clientY: e.clientY });
    console.log('📏 Wrapper rect:', rect);
    console.log('📍 Calculated point:', { x, y });
    
    // Add point
    currentPoints.push({ x: parseFloat(x.toFixed(4)), y: parseFloat(y.toFixed(4)) });
    console.log('✅ Point added! Total points:', currentPoints.length);
    
    // Live-update the form while drawing
    const verticesInput = document.getElementById('location-vertices');
    const info = document.getElementById('form-vertices-info');
    if (verticesInput) verticesInput.value = JSON.stringify(currentPoints);
    if (info) info.textContent = `${currentPoints.length} points added`;
    updatePointCounter();
    updateDrawingButtons();
    drawPreviewPolygon();
}

function undoLastPoint() {
    if (currentPoints.length > 0) {
        currentPoints.pop();
        // Live-update the form while drawing
        const verticesInput = document.getElementById('location-vertices');
        const info = document.getElementById('form-vertices-info');
        if (verticesInput) verticesInput.value = JSON.stringify(currentPoints);
        if (info) info.textContent = `${currentPoints.length} points added`;
        updatePointCounter();
        updateDrawingButtons();
        drawPreviewPolygon();
    }
}

function completePolygon() {
    if (currentPoints.length < 3) {
        alert('You need at least 3 points to create a polygon.');
        return;
    }
    // Form is already open and synced; just exit drawing mode
    cancelDrawingMode();
}

function updatePointCounter() {
    document.getElementById('point-counter').textContent = `Points: ${currentPoints.length}`;
}

function updateDrawingButtons() {
    document.getElementById('undo-point-btn').disabled = currentPoints.length === 0;
    document.getElementById('complete-polygon-btn').disabled = currentPoints.length < 3;
}

function drawPreviewPolygon() {
    const pointsGroup = document.getElementById('preview-points');
    const polygon = document.getElementById('preview-polygon');
    
    console.log('🎨 Drawing preview polygon. Points:', currentPoints.length, currentPoints);
    console.log('📍 Points group element:', pointsGroup);
    console.log('🔷 Polygon element:', polygon);
    
    // Clear previous points
    pointsGroup.innerHTML = '';
    
    if (currentPoints.length === 0) {
        polygon.classList.add('hidden');
        return;
    }
    
    // Determine current selected color (fallback to blue)
    const colorInput = document.getElementById('location-color');
    const currentColor = colorInput && colorInput.value ? colorInput.value : '#2563eb';
    
    // Draw points with fixed size (compensate for zoom scale)
    const fixedRadius = 1.5 / currentScale; // Inverse of current zoom scale
    const fixedFontSize = 1.2 / currentScale;
    
    currentPoints.forEach((point, index) => {
        console.log(`➕ Adding point ${index + 1} at (${point.x}, ${point.y})`);
        
        // Create a group for each point (so we can make it draggable)
        const pointGroup = document.createElementNS('http://www.w3.org/2000/svg', 'g');
        pointGroup.setAttribute('data-point-index', index);
        pointGroup.style.cursor = 'pointer';
        pointGroup.style.pointerEvents = 'auto'; // Enable mouse events
        
        // Use ellipse to compensate for aspect ratio, making it appear as a perfect circle
        const ellipse = document.createElementNS('http://www.w3.org/2000/svg', 'ellipse');
        ellipse.setAttribute('cx', point.x);
        ellipse.setAttribute('cy', point.y);
        ellipse.setAttribute('rx', fixedRadius); // Horizontal radius
        ellipse.setAttribute('ry', fixedRadius / aspectRatio); // Vertical radius - DIVIDE to compensate
        ellipse.setAttribute('fill', currentColor);
        ellipse.setAttribute('stroke', 'none');
        pointGroup.appendChild(ellipse);
        
        // Add point number
        const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        text.setAttribute('x', point.x);
        text.setAttribute('y', point.y); // No offset, perfectly centered
        text.setAttribute('fill', '#ffffff'); // White text
        text.setAttribute('font-size', fixedFontSize);
        text.setAttribute('font-weight', 'bold');
        text.setAttribute('text-anchor', 'middle');
        text.setAttribute('dominant-baseline', 'central'); // Use 'central' for perfect vertical centering
        text.style.pointerEvents = 'none'; // Text doesn't block mouse events
        text.textContent = index + 1;
        pointGroup.appendChild(text);
        
        // Add drag handlers
        pointGroup.addEventListener('mousedown', (e) => {
            e.stopPropagation();
            isDraggingPoint = true;
            draggingPointIndex = index;
            pointGroup.style.cursor = 'grabbing';
        });
        
        pointsGroup.appendChild(pointGroup);
        console.log('✅ Point group appended:', pointGroup);
    });
    
    console.log('📊 Points group children count:', pointsGroup.children.length);
    
    // Draw polygon if we have at least 3 points
    if (currentPoints.length >= 3) {
        const pointsString = currentPoints.map(p => `${p.x},${p.y}`).join(' ');
        const fixedStrokeWidth = 0.3 / currentScale; // Fixed stroke width regardless of zoom
        
        polygon.setAttribute('points', pointsString);
        polygon.setAttribute('stroke-width', fixedStrokeWidth);
        polygon.setAttribute('fill', currentColor + '20');
        polygon.setAttribute('stroke', currentColor);
        polygon.classList.remove('hidden');
        console.log('🔷 Polygon created with points:', pointsString);
    } else {
        polygon.classList.add('hidden');
    }
}

function selectColor(color) {
    // Update hidden input
    document.getElementById('location-color').value = color;
    
    // Update visual selection (border)
    document.querySelectorAll('.color-option').forEach(btn => {
        if (btn.onclick.toString().includes(color)) {
            btn.classList.remove('border-transparent');
            btn.classList.add('border-gray-600');
        } else {
            btn.classList.remove('border-gray-600');
            btn.classList.add('border-transparent');
        }
    });
    
    // If currently editing vertices, update color in real-time
    if (typeof editingVertices !== 'undefined' && editingVertices.length > 0) {
        editVertexColor = color;
        redrawEditVertices();
    }
    // If currently drawing, redraw preview with new color
    if (typeof isDrawing !== 'undefined' && isDrawing) {
        drawPreviewPolygon();
    }
}

function openLocationModal(vertices, locationData = null) {
    const locationsList = document.getElementById('locations-list-container');
    const inlineForm = document.getElementById('inline-location-form');
    const form = document.getElementById('location-form');
    
    // Hide locations list and show inline form
    locationsList.classList.add('hidden');
    inlineForm.classList.remove('hidden');
    
    // Reset form
    form.reset();
    editingLocationId = null;
    document.querySelectorAll('[id^="error-"]').forEach(el => el.classList.add('hidden'));
    
    // Clear center coordinates
    document.getElementById('location-center-x').value = '';
    document.getElementById('location-center-y').value = '';
    
    // Set vertices
    document.getElementById('location-vertices').value = JSON.stringify(vertices);
    updateVerticesInfo(vertices.length);
    
    // If editing existing location
    if (locationData) {
        editingLocationId = locationData.id;
        document.getElementById('form-title').textContent = 'Edit Location';
        document.getElementById('location-id').value = locationData.id;
        document.getElementById('location-type').value = locationData.type_id;
        document.getElementById('location-name').value = locationData.name;
        document.getElementById('location-code').value = locationData.short_code || '';
        document.getElementById('location-description').value = locationData.description || '';
        
        // Set center coordinates for label dragging
        if (locationData.center_x && locationData.center_y) {
            document.getElementById('location-center-x').value = locationData.center_x;
            document.getElementById('location-center-y').value = locationData.center_y;
        }
        
        // Enforce color from type
        enforceColorFromType();
        
        // Re-render locations so labels become draggable
        renderLocations();
        
        // Automatically enable vertex editing mode if location has vertices
        if (vertices && vertices.length >= 3) {
            // Small delay to ensure form is fully rendered
            setTimeout(() => {
                enableVertexEditing();
            }, 100);
        }
    } else {
        document.getElementById('form-title').textContent = 'Add New Location';
        
        // Enforce color from type for new locations as well
        enforceColorFromType();
    }
}

function enableVertexEditing() {
    // Get current vertices from form
    const verticesJson = document.getElementById('location-vertices').value;
    const vertices = verticesJson ? JSON.parse(verticesJson) : [];
    
    if (!vertices || vertices.length < 3) {
        alert('This location has no polygon to edit. You can only edit vertices for locations with map polygons.');
        return;
    }
    
    // Get current color
    const color = document.getElementById('location-color').value;
    
    // Enter vertex editing mode
    displayVerticesOnMap(vertices, color);
}

function displayVerticesOnMap(vertices, color) {
    // Clear any existing edit vertices
    clearEditVertices();
    
    // Store original vertices for cancel functionality
    const verticesInput = document.getElementById('location-vertices');
    verticesInput.setAttribute('data-original-vertices', JSON.stringify(vertices));
    
    // Store vertices for editing
    editingVertices = JSON.parse(JSON.stringify(vertices)); // Deep copy
    editVertexColor = color;
    
    // Initialize history with initial state
    vertexHistory = [];
    saveVertexState();
    
    // Show edit help and notice
    document.getElementById('edit-vertices-help').classList.remove('hidden');
    document.getElementById('editing-notice').classList.remove('hidden');
    document.getElementById('edit-point-counter').textContent = `Points: ${editingVertices.length}`;
    
    // Update button state
    updateEditingButtons();
    
    redrawEditVertices();
    updateMapCursor();
    document.getElementById('add-location-btn').disabled = true;
    document.getElementById('add-location-btn').classList.add('opacity-40', 'cursor-not-allowed');
}

function saveVertexState() {
    // Save current vertex state to history
    vertexHistory.push(JSON.parse(JSON.stringify(editingVertices)));
    // Limit history to 50 states to prevent memory issues
    if (vertexHistory.length > 50) {
        vertexHistory.shift();
    }
    updateEditingButtons();
}

function updateEditingButtons() {
    document.getElementById('undo-edit-point-btn').disabled = editingVertices.length <= 3;
    
    // Enable/disable undo button based on history
    const undoVertexBtn = document.getElementById('undo-vertex-change-btn');
    if (undoVertexBtn) {
        undoVertexBtn.disabled = vertexHistory.length <= 1;
    }
}

function undoLastEditPoint() {
    if (editingVertices.length <= 3) {
        alert('Cannot remove point. A polygon must have at least 3 points.');
        return;
    }
    
    // Save state before removing
    saveVertexState();
    
    // Remove last point
    editingVertices.pop();
    
    // Update UI
    redrawEditVertices();
    
    // Update form and counters
    document.getElementById('location-vertices').value = JSON.stringify(editingVertices);
    updateVerticesInfo(editingVertices.length);
    document.getElementById('edit-point-counter').textContent = `Points: ${editingVertices.length}`;
    
    // Update button state
    updateEditingButtons();
}

function undoVertexChange() {
    if (vertexHistory.length <= 1) return;
    
    // Remove current state (last item)
    vertexHistory.pop();
    
    // Restore previous state
    const previousState = vertexHistory[vertexHistory.length - 1];
    if (previousState) {
        editingVertices = JSON.parse(JSON.stringify(previousState)); // Deep copy
        
        // Update UI
        redrawEditVertices();
        
        // Update form and counters
        document.getElementById('location-vertices').value = JSON.stringify(editingVertices);
        updateVerticesInfo(editingVertices.length);
        document.getElementById('edit-point-counter').textContent = `Points: ${editingVertices.length}`;
        
        // Update button state
        updateEditingButtons();
    }
}

function cancelVertexEditing() {
    // Revert to original vertices before editing started
    const originalVerticesJson = document.getElementById('location-vertices').getAttribute('data-original-vertices');
    if (originalVerticesJson) {
        document.getElementById('location-vertices').value = originalVerticesJson;
        const vertices = JSON.parse(originalVerticesJson);
        updateVerticesInfo(vertices.length);
    }
    
    // Close editing mode
    clearEditVertices();
}

function redrawEditVertices() {
    const pointsGroup = document.getElementById('preview-points');
    const polygon = document.getElementById('preview-polygon');
    
    // Clear existing vertices
    pointsGroup.innerHTML = '';
    
    // COPY EXACTLY from drawPreviewPolygon
    const fixedRadius = 1.5 / currentScale;
    const fixedFontSize = 1.2 / currentScale;
    
    // Draw points for each vertex - EXACTLY like drawing mode
    editingVertices.forEach((point, index) => {
        // Create a group for each point (EXACTLY like drawing mode)
        const pointGroup = document.createElementNS('http://www.w3.org/2000/svg', 'g');
        pointGroup.setAttribute('data-point-index', index);
        pointGroup.style.cursor = 'pointer';
        pointGroup.style.pointerEvents = 'auto';
        
        // Use ellipse to compensate for aspect ratio, making it appear as a perfect circle
        const ellipse = document.createElementNS('http://www.w3.org/2000/svg', 'ellipse');
        ellipse.setAttribute('cx', point.x);
        ellipse.setAttribute('cy', point.y);
        ellipse.setAttribute('rx', fixedRadius);
        ellipse.setAttribute('ry', fixedRadius / aspectRatio);
        ellipse.setAttribute('fill', editVertexColor);
        ellipse.setAttribute('stroke', 'none');
        pointGroup.appendChild(ellipse);
        
        // Add point number
        const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        text.setAttribute('x', point.x);
        text.setAttribute('y', point.y);
        text.setAttribute('fill', '#ffffff');
        text.setAttribute('font-size', fixedFontSize);
        text.setAttribute('font-weight', 'bold');
        text.setAttribute('text-anchor', 'middle');
        text.setAttribute('dominant-baseline', 'central');
        text.style.pointerEvents = 'none';
        text.textContent = index + 1;
        pointGroup.appendChild(text);
        
        // Drag to move
        pointGroup.addEventListener('mousedown', startDragVertex);
        
        // Right-click to delete
        pointGroup.addEventListener('contextmenu', deleteVertex);
        
        pointsGroup.appendChild(pointGroup);
    });
    
    // Draw the polygon shape
    updateEditPolygon();
}

function startDragVertex(e) {
    e.stopPropagation();
    e.preventDefault();
    
    // Get index from data-point-index attribute
    let target = e.currentTarget;
    draggingVertexIndex = parseInt(target.getAttribute('data-point-index'));
    
    // Save initial position for comparison
    const initialVertex = {...editingVertices[draggingVertexIndex]};
    
    // Save state before dragging starts
    saveVertexState();
    
    // Add global listeners
    document.addEventListener('mousemove', dragVertex);
    document.addEventListener('mouseup', function stopHandler() {
        stopDragVertex(initialVertex);
        document.removeEventListener('mouseup', stopHandler);
    });
    
    // Change cursor
    document.body.style.cursor = 'grabbing';
}

function dragVertex(e) {
    if (draggingVertexIndex === null || draggingVertexIndex === undefined) return;
    
    e.preventDefault();
    
    const wrapper = document.getElementById('map-wrapper');
    const rect = wrapper.getBoundingClientRect();
    
    // Calculate position relative to wrapper
    const x = ((e.clientX - rect.left) / rect.width) * 100;
    const y = ((e.clientY - rect.top) / rect.height) * 100;
    
    // Update vertex position
    editingVertices[draggingVertexIndex] = {
        x: parseFloat(x.toFixed(4)),
        y: parseFloat(y.toFixed(4))
    };
    
    // Update visual - find the group and update ellipse and text
    const pointsGroup = document.getElementById('preview-points');
    const group = pointsGroup.querySelector(`[data-point-index="${draggingVertexIndex}"]`);
    if (group) {
        const ellipse = group.querySelector('ellipse');
        const text = group.querySelector('text');
        
        if (ellipse) {
            ellipse.setAttribute('cx', editingVertices[draggingVertexIndex].x);
            ellipse.setAttribute('cy', editingVertices[draggingVertexIndex].y);
        }
        if (text) {
            text.setAttribute('x', editingVertices[draggingVertexIndex].x);
            text.setAttribute('y', editingVertices[draggingVertexIndex].y);
        }
    }
    
    // Update polygon
    const polygon = document.getElementById('preview-polygon');
    const pointsString = editingVertices.map(p => `${p.x},${p.y}`).join(' ');
    polygon.setAttribute('points', pointsString);
}

function stopDragVertex(initialVertex) {
    if (draggingVertexIndex !== null) {
        // Check if vertex actually moved (if initialVertex was provided)
        let hasChanged = false;
        if (initialVertex) {
            const current = editingVertices[draggingVertexIndex];
            hasChanged = current.x !== initialVertex.x || current.y !== initialVertex.y;
        }
        
        // If vertex didn't move, remove the state we saved (undo the save)
        if (initialVertex && !hasChanged && vertexHistory.length > 1) {
            vertexHistory.pop();
            updateEditingButtons();
        }
        
        // Update hidden input with new vertices
        document.getElementById('location-vertices').value = JSON.stringify(editingVertices);
        updateVerticesInfo(editingVertices.length);
    }
    
    draggingVertexIndex = null;
    document.removeEventListener('mousemove', dragVertex);
    
    // Restore cursor
    document.body.style.cursor = '';
}

function updateEditPolygon() {
    const polygon = document.getElementById('preview-polygon');
    
    if (editingVertices.length >= 3) {
        const pointsString = editingVertices.map(p => `${p.x},${p.y}`).join(' ');
        const fixedStrokeWidth = 0.3 / currentScale;
        
        polygon.setAttribute('points', pointsString);
        polygon.setAttribute('fill', editVertexColor + '20');
        polygon.setAttribute('stroke', editVertexColor);
        polygon.setAttribute('stroke-width', fixedStrokeWidth);
        polygon.classList.remove('hidden');

        // Accept clicks for adding points in edit mode
        polygon.style.pointerEvents = 'auto';
        polygon.style.cursor = 'crosshair';
        polygon.onclick = addEditVertexOnPolygon;
    } else {
        polygon.classList.add('hidden');
        polygon.onclick = null;
    }
}

function addEditVertexOnPolygon(e) {
    if (draggingVertexIndex !== null) return;
    e.stopPropagation();
    
    // Save state before adding
    saveVertexState();
    
    const wrapper = document.getElementById('map-wrapper');
    const rect = wrapper.getBoundingClientRect();
    const x = ((e.clientX - rect.left) / rect.width) * 100;
    const y = ((e.clientY - rect.top) / rect.height) * 100;
    const newPoint = {
        x: parseFloat(x.toFixed(4)),
        y: parseFloat(y.toFixed(4))
    };
    // Find closest edge and insert point there
    let minDistance = Infinity;
    let insertIndex = editingVertices.length;
    for (let i = 0; i < editingVertices.length; i++) {
        const p1 = editingVertices[i];
        const p2 = editingVertices[(i + 1) % editingVertices.length];
        const distance = distanceToLineSegment(newPoint, p1, p2);
        if (distance < minDistance) {
            minDistance = distance;
            insertIndex = i + 1;
        }
    }
    editingVertices.splice(insertIndex, 0, newPoint);
    // Update everything
    redrawEditVertices();
    document.getElementById('location-vertices').value = JSON.stringify(editingVertices);
    updateVerticesInfo(editingVertices.length);
    document.getElementById('edit-point-counter').textContent = `Points: ${editingVertices.length}`;
    updateEditingButtons();
}


function deleteVertex(e) {
    e.preventDefault();
    e.stopPropagation();
    
    // Get index from data-point-index attribute
    let target = e.currentTarget;
    const index = parseInt(target.getAttribute('data-point-index'));
    
    // Need at least 3 vertices to maintain a polygon
    if (editingVertices.length <= 3) {
        alert('Cannot delete vertex. A polygon must have at least 3 points.');
        return;
    }
    
    // Save state before deleting
    saveVertexState();
    
    // Remove the vertex
    editingVertices.splice(index, 1);
    
    // Update UI
    redrawEditVertices();
    
    // Update form and counter
    document.getElementById('location-vertices').value = JSON.stringify(editingVertices);
    updateVerticesInfo(editingVertices.length);
    document.getElementById('edit-point-counter').textContent = `Points: ${editingVertices.length}`;
    
    // Update button state
    updateEditingButtons();
}

function distanceToLineSegment(point, lineStart, lineEnd) {
    const dx = lineEnd.x - lineStart.x;
    const dy = lineEnd.y - lineStart.y;
    
    if (dx === 0 && dy === 0) {
        // Line segment is just a point
        return Math.sqrt(Math.pow(point.x - lineStart.x, 2) + Math.pow(point.y - lineStart.y, 2));
    }
    
    // Calculate projection of point onto line
    const t = Math.max(0, Math.min(1, ((point.x - lineStart.x) * dx + (point.y - lineStart.y) * dy) / (dx * dx + dy * dy)));
    
    // Find closest point on line segment
    const closestX = lineStart.x + t * dx;
    const closestY = lineStart.y + t * dy;
    
    // Return distance
    return Math.sqrt(Math.pow(point.x - closestX, 2) + Math.pow(point.y - closestY, 2));
}

function clearEditVertices() {
    // Clear edit vertices
    const pointsGroup = document.getElementById('preview-points');
    pointsGroup.innerHTML = '';
    
    // Hide preview polygon
    const polygon = document.getElementById('preview-polygon');
    polygon.classList.add('hidden');
    
    // Hide edit help and notice
    document.getElementById('edit-vertices-help').classList.add('hidden');
    document.getElementById('editing-notice').classList.add('hidden');
    
    // Reset editing state
    editingVertices = [];
    vertexHistory = [];
    draggingVertexIndex = null;
    document.getElementById('add-location-btn').disabled = false;
    document.getElementById('add-location-btn').classList.remove('opacity-40', 'cursor-not-allowed');
    updateMapCursor();
    
    // Disable undo buttons
    const undoVertexBtn = document.getElementById('undo-vertex-change-btn');
    if (undoVertexBtn) {
        undoVertexBtn.disabled = true;
    }
}

function closeLocationModal() {
    const locationsList = document.getElementById('locations-list-container');
    const inlineForm = document.getElementById('inline-location-form');
    
    // Hide inline form and show locations list
    inlineForm.classList.add('hidden');
    locationsList.classList.remove('hidden');
    editingLocationId = null;
    
    // Clear center coordinates
    document.getElementById('location-center-x').value = '';
    document.getElementById('location-center-y').value = '';
    
    // Clear vertices from map
    clearEditVertices();
    
    // Re-render locations to remove draggable labels
    renderLocations();
}

function closeInlineForm() {
    // Cancel drawing mode if active
    if (isDrawing) {
        cancelDrawingMode();
    }
    
    // Clear vertex editing mode if active
    if (editingVertices && editingVertices.length > 0) {
        cancelVertexEditing();
    }
    closeLocationModal();
}

async function handleFormSubmit(e) {
    e.preventDefault();
    
    // If in vertex editing mode, update vertices input with current editingVertices
    if (editingVertices && editingVertices.length > 0) {
        document.getElementById('location-vertices').value = JSON.stringify(editingVertices);
        // Clear editing mode after capturing vertices
        clearEditVertices();
    }
    
    const formData = new FormData(e.target);
    const data = {};
    
    // Convert form data to object
    formData.forEach((value, key) => {
        if (key === 'vertices') {
            data[key] = JSON.parse(value);
        } else if (key === 'center_x' || key === 'center_y') {
            // Include center coordinates if they exist and are not empty
            if (value && value.trim() !== '') {
                data[key] = parseFloat(value);
            }
        } else {
            data[key] = value;
        }
    });
    
    // Clear previous errors
    document.querySelectorAll('[id^="error-"]').forEach(el => {
        el.classList.add('hidden');
        el.textContent = '';
    });
    
    try {
        const url = editingLocationId 
            ? `/api/map-locations/${editingLocationId}`
            : '/api/map-locations';
        
        const method = editingLocationId ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (response.ok) {
            // Success
            closeLocationModal();
            
            // Update locations array
            if (editingLocationId) {
                const index = locations.findIndex(l => l.id === editingLocationId);
                if (index !== -1) {
                    locations[index] = result.data;
                }
            } else {
                locations.push(result.data);
            }
            
            renderLocations();
            
            // Reload page to update table
            location.reload();
        } else {
            // Validation errors
            if (result.errors) {
                Object.keys(result.errors).forEach(key => {
                    const errorElement = document.getElementById(`error-${key}`);
                    if (errorElement) {
                        errorElement.textContent = result.errors[key][0];
                        errorElement.classList.remove('hidden');
                    }
                });
            } else {
                alert(result.message || 'An error occurred');
            }
        }
    } catch (error) {
        console.error('Error saving location:', error);
        alert('Failed to save location. Please try again.');
    }
}

function renderLocations() {
    if (!locationsVisible) return;
    
    const svg = document.getElementById('locations-svg');
    
    // Clear existing locations (except defs)
    while (svg.children.length > 1) {
        svg.removeChild(svg.lastChild);
    }
    
    // Store labels to append at the end (so they're on top)
    const labelsToAppend = [];
    
    locations.forEach(location => {
        if (!location.is_active || !location.vertices || location.vertices.length < 3) return;
        
        // Create polygon group
        const g = document.createElementNS('http://www.w3.org/2000/svg', 'g');
        g.setAttribute('class', 'location-polygon');
        g.style.cursor = 'pointer';
        g.style.pointerEvents = 'auto'; // Always keep group enabled
        
        // Disable polygon pointer events when editing this location (so label can be dragged)
        const isEditingThisLocation = editingLocationId === location.id;
        
        // Draw polygon (initially invisible, only shows on hover)
        const polygon = document.createElementNS('http://www.w3.org/2000/svg', 'polygon');
        const pointsString = location.vertices.map(v => `${v.x},${v.y}`).join(' ');
        polygon.setAttribute('points', pointsString);
        polygon.setAttribute('fill', 'transparent'); // Transparent by default
        polygon.setAttribute('stroke', 'none'); // No border
        polygon.setAttribute('class', 'transition-all duration-200');
        
        // When editing, hide the polygon completely so label is clearly on top
        if (isEditingThisLocation) {
            polygon.style.pointerEvents = 'none'; // Polygon won't capture events
            polygon.style.display = 'none'; // Hide polygon visually
            g.style.pointerEvents = 'none'; // Group won't capture events either - label is separate
            g.style.display = 'none'; // Hide group visually too
        } else {
            // If ANY location is being edited, disable pointer events on all OTHER polygons
            // This prevents them from intercepting mouse events intended for the label
            if (editingLocationId !== null) {
                polygon.style.pointerEvents = 'none'; // Disable so label can be dragged
                g.style.pointerEvents = 'none'; // Disable group too
            } else {
                polygon.style.pointerEvents = 'auto';
                g.style.pointerEvents = 'auto';
            }
            polygon.style.display = ''; // Show polygon
            g.style.display = ''; // Show group
        }
        
        // Add label if there's a center point and it's not a parking type
        let label = null;
        const isParking = location.type && location.type.name && location.type.name.toLowerCase().includes('parking');
        
        if (location.center_x && location.center_y && !isParking) {
            // Use consistent font size - don't scale with zoom to maintain readability
            const fixedFontSize = 1; // Fixed size at 1 unit in viewBox coordinates
            
            label = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            label.setAttribute('x', location.center_x);
            label.setAttribute('y', location.center_y);
            label.setAttribute('text-anchor', 'middle');
            label.setAttribute('dominant-baseline', 'central');
            label.setAttribute('fill', '#4b5563'); // Dark gray (gray-600)
            label.setAttribute('stroke', '#ffffff'); // White outline
            label.setAttribute('stroke-width', 0.15); // Fixed stroke width
            label.setAttribute('paint-order', 'stroke fill'); // Draw stroke behind fill
            label.setAttribute('font-weight', '700'); // Bold for better visibility
            label.setAttribute('font-size', fixedFontSize);
            label.setAttribute('font-family', 'Satoshi, ui-sans-serif, system-ui, sans-serif'); // Same as app.blade
            // Apply transform to counter the aspect ratio stretch
            label.setAttribute('transform', `scale(1, ${1 / aspectRatio})`);
            label.setAttribute('transform-origin', `${location.center_x} ${location.center_y}`);
            label.setAttribute('data-location-id', location.id);
            label.setAttribute('data-center-x', location.center_x);
            label.setAttribute('data-center-y', location.center_y);
            
            // Make label draggable when editing this location
            if (isEditingThisLocation) {
                // Create a group for the draggable label with a hit area
                const labelGroup = document.createElementNS('http://www.w3.org/2000/svg', 'g');
                labelGroup.setAttribute('data-location-id', location.id);
                labelGroup.style.cursor = 'move';
                labelGroup.style.pointerEvents = 'auto';
                
                // Make entire SVG draggable when editing this location
                // We'll attach a global drag handler to the SVG itself
                
                // Keep original label styling - don't change appearance
                label.style.pointerEvents = 'auto';
                label.style.cursor = 'move';
                // Keep original fill and stroke colors - don't change them
                
                // Store original position
                labelGroup.setAttribute('data-original-x', location.center_x);
                labelGroup.setAttribute('data-original-y', location.center_y);
                labelGroup.setAttribute('data-center-x', location.center_x);
                labelGroup.setAttribute('data-center-y', location.center_y);
                
                // Set text content before appending
                label.textContent = location.short_code || location.name.substring(0, 3).toUpperCase();
                
                // Save reference to text element before we lose it
                const textElement = label;
                
                // Append text to group
                labelGroup.appendChild(textElement);
                
                // Make the entire SVG draggable when editing - attach handler to SVG
                // This allows dragging from anywhere, not just on the label
                makeLabelDraggableGlobal(location.id, labelGroup, textElement);
                
                // Also make the label group itself draggable for direct clicks
                makeLabelDraggable(labelGroup, location.id);
                makeLabelDraggable(textElement, location.id);
                
                // Replace label with labelGroup (for later reference)
                label = labelGroup;
            } else {
                label.style.pointerEvents = 'none';
                label.textContent = location.short_code || location.name.substring(0, 3).toUpperCase();
            }
        }
        
        // Hover effects - show shape and tooltip immediately (only if not editing this location)
        let tooltip = null;
        
        if (!isEditingThisLocation) {
            g.addEventListener('mouseenter', function(e) {
                // Disable hover when in edit mode
                if (editingVertices.length > 0 || isDrawing) return;
                
                polygon.setAttribute('fill', location.color + '20'); // Show with 12% opacity
                polygon.setAttribute('stroke', location.color + '60'); // Stroke with 38% opacity
                const fixedStrokeWidth = 0.3 / currentScale;
                polygon.setAttribute('stroke-width', fixedStrokeWidth);
                polygon.setAttribute('filter', 'url(#glow)');
                
                // Show tooltip immediately
                tooltip = document.createElement('div');
                tooltip.className = 'absolute bg-[#1b1b18] dark:bg-[#EDEDEC] text-white dark:text-[#1b1b18] px-3 py-2 rounded-lg text-sm font-medium shadow-lg z-50 pointer-events-none';
                tooltip.style.left = e.clientX + 10 + 'px';
                tooltip.style.top = e.clientY + 10 + 'px';
                tooltip.textContent = location.name;
                document.body.appendChild(tooltip);
            });
            
            g.addEventListener('mouseleave', function() {
                polygon.setAttribute('fill', 'transparent'); // Hide again
                polygon.setAttribute('stroke', 'none');
                polygon.removeAttribute('filter');
                
                // Remove tooltip
                if (tooltip) {
                    tooltip.remove();
                    tooltip = null;
                }
            });
            
            // Update tooltip position on mouse move
            g.addEventListener('mousemove', function(e) {
                if (tooltip) {
                    tooltip.style.left = e.clientX + 10 + 'px';
                    tooltip.style.top = e.clientY + 10 + 'px';
                }
            });
            
            // Click to view details
            g.addEventListener('click', () => {
                // Disable click when in edit mode
                if (editingVertices.length > 0 || isDrawing) return;
                viewLocationDetails(location.id);
            });
        }
        
        // When editing this location, DON'T append the polygon group at all
        // This ensures the label (which will be appended later) is definitely on top
        if (!isEditingThisLocation) {
            g.appendChild(polygon);
            svg.appendChild(g);
        }
        
        // ALWAYS append labels directly to SVG (not inside group) so they're on top of ALL polygons
        if (label) {
            labelsToAppend.push(label);
        }
    });
    
    // Append ALL labels at the VERY END (after ALL polygon groups)
    // This ensures they render on top with highest z-index (SVG renders last = top)
    // Labels are appended directly to SVG, never inside polygon groups, so they're always on top
    labelsToAppend.forEach((label) => {
        svg.appendChild(label);
    });
    
    // If editing a location, ensure its label is the ABSOLUTE LAST element
    // Move it to the end if anything else got appended
    if (editingLocationId !== null) {
        const editingLabel = Array.from(svg.children).find(child => {
            const locationId = child.getAttribute('data-location-id');
            return locationId && parseInt(locationId) === editingLocationId;
        });
        
        if (editingLabel && svg.lastChild !== editingLabel) {
            // Move editing label to absolute end
            svg.removeChild(editingLabel);
            svg.appendChild(editingLabel);
        }
    }
}

// Global drag state for label dragging from anywhere
let globalDragState = null;
let globalDragSetupDone = false;

// Setup global drag handlers once
function setupGlobalLabelDrag() {
    if (globalDragSetupDone) return; // Already set up
    globalDragSetupDone = true;
    
    const mapContainer = document.getElementById('campus-map-container');
    if (!mapContainer) return;
    
    // Attach handler to map container - works from anywhere on the map
    mapContainer.addEventListener('mousedown', function(e) {
        // Only work if a location is being edited AND it's a right-click (button === 2)
        if (!editingLocationId || e.button !== 2) return;
        
        const svg = document.getElementById('locations-svg');
        if (!svg) return;
        
        // Find the editing location's label
        const editingLabelGroup = Array.from(svg.children).find(child => {
            const locationId = child.getAttribute('data-location-id');
            return locationId && parseInt(locationId) === editingLocationId;
        });
        
        if (!editingLabelGroup) return;
        
        const textElement = editingLabelGroup.querySelector('text');
        if (!textElement) return;
        
        // Don't interfere if clicking directly on label (it has its own handler)
        if (editingLabelGroup.contains(e.target)) return;
        
        // FORCE start drag from anywhere - block everything else
        e.preventDefault();
        e.stopPropagation();
        
        // Get SVG bounding rect to calculate coordinates
        const svgRect = svg.getBoundingClientRect();
        const svgViewBox = svg.viewBox.baseVal;
        
        // Calculate initial label position in viewBox coordinates
        const currentX = parseFloat(editingLabelGroup.getAttribute('data-center-x') || textElement.getAttribute('x') || '0');
        const currentY = parseFloat(editingLabelGroup.getAttribute('data-center-y') || textElement.getAttribute('y') || '0');
        
        // Convert to screen coordinates for initial calculation
        const startSvgX = (currentX / svgViewBox.width) * svgRect.width + svgRect.left;
        const startSvgY = (currentY / svgViewBox.height) * svgRect.height + svgRect.top;
        
        // Start drag from anywhere on map
        globalDragState = {
            isDragging: true,
            labelGroup: editingLabelGroup,
            textElement: textElement,
            startX: e.clientX,
            startY: e.clientY,
            startLabelX: currentX,
            startLabelY: currentY,
            svgRect: svgRect,
            svgViewBox: svgViewBox,
        };
        
        document.body.style.cursor = 'grabbing';
    });
    
    // Handle drag movement
    document.addEventListener('mousemove', function(e) {
        if (!globalDragState || !globalDragState.isDragging) return;
        
        const svg = document.getElementById('locations-svg');
        if (!svg) return;
        
        // Use cached rect or get fresh one
        const svgRect = svg.getBoundingClientRect();
        const svgViewBox = svg.viewBox.baseVal;
        
        // Calculate mouse position in viewBox coordinates
        const mouseX = ((e.clientX - svgRect.left) / svgRect.width) * svgViewBox.width;
        const mouseY = ((e.clientY - svgRect.top) / svgRect.height) * svgViewBox.height;
        
        // Calculate start position in viewBox coordinates
        const startSvgX = ((globalDragState.startX - svgRect.left) / svgRect.width) * svgViewBox.width;
        const startSvgY = ((globalDragState.startY - svgRect.top) / svgRect.height) * svgViewBox.height;
        
        // Calculate delta
        const deltaX = mouseX - startSvgX;
        const deltaY = (mouseY - startSvgY) * aspectRatio;
        
        // Calculate new position
        const newX = Math.max(0, Math.min(100, globalDragState.startLabelX + deltaX));
        const newY = Math.max(0, Math.min(100, globalDragState.startLabelY + deltaY));
        
        // Update label position
        if (globalDragState.textElement) {
            globalDragState.textElement.setAttribute('x', newX);
            globalDragState.textElement.setAttribute('y', newY);
            globalDragState.textElement.setAttribute('transform-origin', `${newX} ${newY}`);
        }
        
        // Update group data
        if (globalDragState.labelGroup) {
            globalDragState.labelGroup.setAttribute('data-center-x', newX);
            globalDragState.labelGroup.setAttribute('data-center-y', newY);
        }
        
        // Update hidden inputs
        const centerXInput = document.getElementById('location-center-x');
        const centerYInput = document.getElementById('location-center-y');
        if (centerXInput) centerXInput.value = newX.toFixed(4);
        if (centerYInput) centerYInput.value = newY.toFixed(4);
    });
    
    // Handle drag end (only on right button release)
    document.addEventListener('mouseup', function(e) {
        if (globalDragState && globalDragState.isDragging && e.button === 2) {
            globalDragState.isDragging = false;
            globalDragState = null;
            document.body.style.cursor = '';
        }
    });
}

function makeLabelDraggableGlobal(locationId, labelGroup, textElement) {
    // Setup once - will work for any editing location
    setupGlobalLabelDrag();
}

// Make a label draggable in edit mode
function makeLabelDraggable(labelElement, locationId) {
    let isDragging = false;
    let startX, startY;
    let startLabelX, startLabelY;
    
    labelElement.addEventListener('mousedown', function(e) {
        // Only activate on right-click (button === 2)
        if (e.button !== 2) return;
        
        // Prevent polygon click and group handlers when dragging label
        e.stopPropagation();
        e.preventDefault(); // Also prevent default to stop context menu
        
        isDragging = true;
        startX = e.clientX;
        startY = e.clientY;
        // Get position from data attributes or element attributes
        // If it's a text element, get from parent group's data attributes first, then from text's x/y
        if (labelElement.tagName === 'text' && labelElement.parentElement) {
            const parentGroup = labelElement.parentElement;
            startLabelX = parseFloat(parentGroup.getAttribute('data-center-x') || labelElement.getAttribute('x') || '0');
            startLabelY = parseFloat(parentGroup.getAttribute('data-center-y') || labelElement.getAttribute('y') || '0');
        } else {
            startLabelX = parseFloat(labelElement.getAttribute('data-center-x') || labelElement.getAttribute('x') || '0');
            startLabelY = parseFloat(labelElement.getAttribute('data-center-y') || labelElement.getAttribute('y') || '0');
        }
        
        // Change cursor
        document.body.style.cursor = 'grabbing';
    });
    
    document.addEventListener('mousemove', function(e) {
        if (!isDragging) return;
        
        // Calculate SVG coordinates
        const svg = document.getElementById('locations-svg');
        const svgRect = svg.getBoundingClientRect();
        const svgViewBox = svg.viewBox.baseVal; // viewBox is 0 0 100 100
        
        // Calculate mouse position relative to SVG in viewBox coordinates (0-100)
        const mouseX = ((e.clientX - svgRect.left) / svgRect.width) * svgViewBox.width;
        const mouseY = ((e.clientY - svgRect.top) / svgRect.height) * svgViewBox.height;
        
        // Calculate start position in viewBox coordinates
        const startSvgX = ((startX - svgRect.left) / svgRect.width) * svgViewBox.width;
        const startSvgY = ((startY - svgRect.top) / svgRect.height) * svgViewBox.height;
        
        // Calculate delta in viewBox coordinates
        const deltaX = mouseX - startSvgX;
        const deltaY = (mouseY - startSvgY) * aspectRatio; // Adjust for aspect ratio stretch
        
        // Calculate new position
        const newX = Math.max(0, Math.min(100, startLabelX + deltaX));
        const newY = Math.max(0, Math.min(100, startLabelY + deltaY));
        
        // Update label position - check if it's a group or text element
        const textElement = labelElement.tagName === 'text' ? labelElement : labelElement.querySelector('text');
        const parentGroup = labelElement.tagName === 'text' ? labelElement.parentElement : labelElement;
        
        if (textElement) {
            textElement.setAttribute('x', newX);
            textElement.setAttribute('y', newY);
            textElement.setAttribute('transform-origin', `${newX} ${newY}`);
        }
        
        // Update group's hitArea if parent is a group
        if (parentGroup && parentGroup.tagName === 'g') {
            const hitArea = parentGroup.querySelector('rect');
            if (hitArea) {
                hitArea.setAttribute('x', (newX - 8).toString()); // Match the larger hitArea size
                hitArea.setAttribute('y', (newY - 3).toString());
            }
            // Update parent group's data attributes
            parentGroup.setAttribute('data-center-x', newX);
            parentGroup.setAttribute('data-center-y', newY);
        }
        
        // Update data attributes on the element that's being dragged
        if (labelElement.tagName === 'text') {
            // Text element - update its own attributes
            labelElement.setAttribute('data-center-x', newX);
            labelElement.setAttribute('data-center-y', newY);
        } else {
            // Group element
            labelElement.setAttribute('data-center-x', newX);
            labelElement.setAttribute('data-center-y', newY);
        }
        
        // Update hidden inputs
        document.getElementById('location-center-x').value = newX.toFixed(4);
        document.getElementById('location-center-y').value = newY.toFixed(4);
    });
    
    document.addEventListener('mouseup', function(e) {
        if (isDragging && e.button === 2) {
            isDragging = false;
            document.body.style.cursor = '';
        }
    });
}

function viewLocationDetails(id) {
    const location = locations.find(l => l.id === id);
    if (!location) return;
    
    const modal = document.getElementById('viewLocationModal');
    const content = document.getElementById('viewLocationContent');
    
    // Process sticker path - encode # character for proper URL handling
    let stickerImageUrl = null;
    if (location.sticker_path) {
        // Replace # with %23 to properly encode hex color codes in filename
        stickerImageUrl = location.sticker_path.replace(/#/g, '%23');
    }
    
    content.innerHTML = `
        <div class="grid grid-cols-1 ${stickerImageUrl ? 'md:grid-cols-2' : ''} gap-6">
            <!-- Location Information -->
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Name</label>
                        <p class="text-base text-[#1b1b18] dark:text-[#EDEDEC] font-medium">${location.name}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Short Code</label>
                        <p class="text-base text-[#1b1b18] dark:text-[#EDEDEC]">${location.short_code || 'N/A'}</p>
                    </div>
                </div>
                <div>
                    <label class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Type</label>
                    <p class="text-base text-[#1b1b18] dark:text-[#EDEDEC]">${location.type.name}</p>
                </div>
                ${location.description ? `
                <div>
                    <label class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Description</label>
                    <p class="text-base text-[#1b1b18] dark:text-[#EDEDEC]">${location.description}</p>
                </div>
                ` : ''}
            </div>
            
            <!-- Location Sticker -->
            ${stickerImageUrl ? `
            <div class="space-y-2">
                <label class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Location Sticker</label>
                <div class="bg-gray-50 dark:bg-[#161615] p-4 rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A]">
                    <img src="${stickerImageUrl}" alt="${location.name} Sticker" class="vehicle-sticker-image">
                </div>
            </div>
            ` : ''}
        </div>
    `;
    
    modal.classList.remove('hidden');
}

function closeViewLocationModal() {
    document.getElementById('viewLocationModal').classList.add('hidden');
}

function closeDeleteLocationModal() {
    document.getElementById('deleteLocationModal').classList.add('hidden');
    deletingLocationId = null;
}

function toggleLocationsVisibility() {
    locationsVisible = !locationsVisible;
    const btn = document.getElementById('toggle-locations-btn');
    const overlay = document.getElementById('locations-overlay');
    
    if (locationsVisible) {
        btn.textContent = 'Hide Locations';
        overlay.classList.remove('hidden');
        renderLocations();
    } else {
        btn.textContent = 'Show Locations';
        overlay.classList.add('hidden');
    }
}

async function editLocation(id) {
    try {
        const response = await fetch(`/api/map-locations/${id}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const result = await response.json();
        
        if (response.ok) {
            const location = result.data;
            openLocationModal(location.vertices, location);
        } else {
            alert('Failed to load location data');
        }
    } catch (error) {
        console.error('Error loading location:', error);
        alert('Failed to load location');
    }
}

function deleteLocation(id) {
    const location = locations.find(l => l.id === id);
    if (!location) return;
    
    deletingLocationId = id;
    const modal = document.getElementById('deleteLocationModal');
    const message = document.getElementById('deleteLocationMessage');
    
    message.innerHTML = `
        <p class="text-[#1b1b18] dark:text-[#EDEDEC]">
            Are you sure you want to delete the location <strong class="font-semibold">${location.name}</strong>?
        </p>
        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mt-2">
            This action cannot be undone.
        </p>
    `;
    
    modal.classList.remove('hidden');
}

async function confirmDeleteLocation() {
    if (!deletingLocationId) return;
    
    try {
        const response = await fetch(`/api/map-locations/${deletingLocationId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        
        const result = await response.json();
        
        if (response.ok) {
            locations = locations.filter(l => l.id !== deletingLocationId);
            renderLocations();
            closeDeleteLocationModal();
            location.reload();
        } else {
            alert(result.message || 'Failed to delete location');
        }
    } catch (error) {
        console.error('Error deleting location:', error);
        alert('Failed to delete location');
    }
}

function zoomMap(delta) {
    currentScale = Math.min(Math.max(1, currentScale + delta), 3); // Min zoom is 1 (100%), max is 3 (300%)
    applyMapTransform();
}

function resetZoom() {
    currentScale = 1;
    panX = 0;
    panY = 0;
    applyMapTransform();
}

function setupLongPressZoom(buttonId, zoomDelta) {
    const button = document.getElementById(buttonId);
    let zoomInterval = null;
    let longPressTimeout = null;
    let isLongPress = false;
    
    const startZoom = () => {
        isLongPress = false;
        
        // Start long press timer (250ms delay before continuous zoom)
        longPressTimeout = setTimeout(() => {
            isLongPress = true;
            // Start continuous zooming (every 30ms for smooth animation)
            zoomInterval = setInterval(() => {
                zoomMap(zoomDelta * 0.15); // Smaller, frequent increments for smooth zoom
            }, 30);
        }, 250);
    };
    
    const stopZoom = () => {
        // If not long press, treat as single click
        if (!isLongPress && longPressTimeout) {
            zoomMap(zoomDelta); // Single click zoom
        }
        
        // Clear timers
        if (longPressTimeout) {
            clearTimeout(longPressTimeout);
            longPressTimeout = null;
        }
        if (zoomInterval) {
            clearInterval(zoomInterval);
            zoomInterval = null;
        }
        
        isLongPress = false;
    };
    
    // Mouse events
    button.addEventListener('mousedown', startZoom);
    button.addEventListener('mouseup', stopZoom);
    button.addEventListener('mouseleave', () => {
        // On mouse leave, just stop without zoom
        if (longPressTimeout) {
            clearTimeout(longPressTimeout);
            longPressTimeout = null;
        }
        if (zoomInterval) {
            clearInterval(zoomInterval);
            zoomInterval = null;
        }
        isLongPress = false;
    });
    
    // Touch events for mobile
    button.addEventListener('touchstart', (e) => {
        e.preventDefault(); // Prevent default touch behavior
        startZoom();
    });
    button.addEventListener('touchend', stopZoom);
    button.addEventListener('touchcancel', () => {
        // On touch cancel, just stop without zoom
        if (longPressTimeout) {
            clearTimeout(longPressTimeout);
            longPressTimeout = null;
        }
        if (zoomInterval) {
            clearInterval(zoomInterval);
            zoomInterval = null;
        }
        isLongPress = false;
    });
}

function applyMapTransform() {
    const wrapper = document.getElementById('map-wrapper');
    const container = document.getElementById('campus-map-container');
    
    // Get actual dimensions
    const containerWidth = container.offsetWidth;
    const containerHeight = container.offsetHeight;
    
    // Calculate scaled dimensions
    const scaledWidth = containerWidth * currentScale;
    const scaledHeight = containerHeight * currentScale;
    
    // Calculate how much extra space we have when scaled (due to center origin)
    const extraWidth = (scaledWidth - containerWidth) / 2;
    const extraHeight = (scaledHeight - containerHeight) / 2;
    
    // Constrain pan values
    if (currentScale > 1) {
        // With center origin, the image expands equally in all directions
        // We can pan in both directions within the extra space
        const maxPanX = extraWidth;
        const minPanX = -extraWidth;
        const maxPanY = extraHeight;
        const minPanY = -extraHeight;
        
        panX = Math.max(minPanX, Math.min(maxPanX, panX));
        panY = Math.max(minPanY, Math.min(maxPanY, panY));
    } else {
        // At 100% zoom, reset pan to center
        panX = 0;
        panY = 0;
    }
    
    wrapper.style.transform = `translate(${panX}px, ${panY}px) scale(${currentScale})`;
    
    // Redraw points to update sizes based on new scale
    if (isDrawing && currentPoints.length > 0) {
        drawPreviewPolygon();
    }
    
    // Redraw edit vertices to update sizes based on new scale
    if (editingVertices.length > 0) {
        redrawEditVertices();
    }
}

function handleMapError(img) {
    img.onerror = null;
    img.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAwIiBoZWlnaHQ9IjYwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iODAwIiBoZWlnaHQ9IjYwMCIgZmlsbD0iI2Y3ZjlmNyIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjQiIGZpbGw9IiM5Yzk5YzkiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5VcGxvYWQgQ2FtcHVzIE1hcDwvdGV4dD48dGV4dCB4PSI1MCUiIHk9IjU1JSIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjE0IiBmaWxsPSIjYWJhYmFiIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSI+UGxhY2UgY2FtcHVzIG1hcCBpbWFnZSBhdCBwdWJsaWMvaW1hZ2VzL2NhbXB1cy1tYXAucG5nPC90ZXh0Pjwvc3ZnPg==';
    img.alt = 'Upload Campus Map';
}

// Expose functions to global scope for onclick handlers
window.viewLocationDetails = viewLocationDetails;
window.editLocation = editLocation;
window.deleteLocation = deleteLocation;
window.closeViewLocationModal = closeViewLocationModal;
window.closeDeleteLocationModal = closeDeleteLocationModal;
window.confirmDeleteLocation = confirmDeleteLocation;
window.closeInlineForm = closeInlineForm;
window.selectColor = selectColor;

// Helper to enforce color from selected type and disable manual picking
function enforceColorFromType() {
    const typeSelect = document.getElementById('location-type');
    if (!typeSelect) return;
    const selectedOption = typeSelect.options[typeSelect.selectedIndex];
    const defaultColor = selectedOption ? selectedOption.dataset.color : null;
    if (defaultColor) {
        selectColor(defaultColor);
    }
    // Disable manual color buttons
    document.querySelectorAll('.color-option').forEach(btn => {
        btn.disabled = true;
        btn.classList.add('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
    });
}

// Update type change handler to always enforce type color
(function hookTypeColorEnforcement() {
    const typeEl = document.getElementById('location-type');
    if (!typeEl) return;
    typeEl.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const defaultColor = selectedOption ? selectedOption.dataset.color : null;
        if (defaultColor) {
            selectColor(defaultColor);
        }
    });
})();
</script>

<style>
#campus-map-container {
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
}

.location-polygon:hover {
    opacity: 1;
}
</style>
@endsection
