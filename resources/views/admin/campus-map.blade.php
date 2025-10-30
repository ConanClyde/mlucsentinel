@extends('layouts.app')

@section('page-title', 'Campus Map')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Campus Map Manager</h1>
            <p class="text-[#706f6c] dark:text-[#A1A09A]">Click points on the map to create polygon locations</p>
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
                    <button id="cancel-drawing-btn" class="btn btn-danger">
                        Cancel
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
            <div id="map-wrapper" class="relative w-full" style="transform-origin: center center; transition: transform 0.3s ease-out;">
                <!-- Campus Map Image -->
                <img id="campus-map" src="{{ asset('images/campus-map.png') }}" alt="Campus Map" 
                     class="block w-full h-auto select-none" 
                     draggable="false"
                     onerror="handleMapError(this)"
                     onload="initializeMapDimensions()"
                     style="display: block;">

                <!-- Locations Overlay Canvas -->
                <div id="locations-overlay" class="absolute inset-0 pointer-events-none" style="z-index: 10;">
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
                <svg id="drawing-canvas" class="absolute inset-0 w-full h-full pointer-events-none" viewBox="0 0 100 100" preserveAspectRatio="none" style="z-index: 20;">
                    <!-- Preview polygon will be drawn here -->
                    <polygon id="preview-polygon" points="" fill="rgba(37, 99, 235, 0.2)" stroke="#2563eb" stroke-width="0.3" class="hidden"/>
                    <!-- Preview points -->
                    <g id="preview-points"></g>
                </svg>
            </div>

            <!-- Zoom Controls (inside container, outside wrapper so they don't zoom) -->
            <div class="absolute top-2 right-2 bg-white dark:bg-[#1a1a1a] rounded-lg shadow-lg border border-[#e3e3e0] dark:border-[#3E3E3A] p-2 space-y-2" style="z-index: 30; pointer-events: auto;">
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
            <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4" style="max-height: 600px; overflow-y-auto;">
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
    <div class="modal-container">
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
    
    // Zoom controls
    document.getElementById('zoom-in-btn').addEventListener('click', () => zoomMap(0.2));
    document.getElementById('zoom-out-btn').addEventListener('click', () => zoomMap(-0.2));
    document.getElementById('reset-zoom-btn').addEventListener('click', resetZoom);

    // Drawing mode
    document.getElementById('add-location-btn').addEventListener('click', enableDrawingMode);
    document.getElementById('cancel-drawing-btn').addEventListener('click', cancelDrawingMode);
    document.getElementById('undo-point-btn').addEventListener('click', undoLastPoint);
    document.getElementById('complete-polygon-btn').addEventListener('click', completePolygon);

    // Legend toggle
    document.getElementById('toggle-legend-btn').addEventListener('click', () => {
        document.getElementById('map-legend').classList.toggle('hidden');
    });

    // Locations visibility toggle
    document.getElementById('toggle-locations-btn').addEventListener('click', toggleLocationsVisibility);

    // Map click for adding points
    container.addEventListener('click', handleMapClick);

    // Mouse drag to pan (works even while drawing)
    let isDraggingMap = false;
    let dragStartX, dragStartY;
    let startPanX, startPanY;
    
    container.addEventListener('mousedown', (e) => {
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
    
    container.addEventListener('mouseup', () => {
        // Reset point dragging
        if (isDraggingPoint) {
            isDraggingPoint = false;
            draggingPointIndex = -1;
            // Reset cursor on all point groups
            document.querySelectorAll('#preview-points g').forEach(g => {
                g.style.cursor = 'grab';
            });
        }
        
        if (isDraggingMap) {
            isDraggingMap = false;
            container.style.cursor = isDrawing ? 'crosshair' : 'grab';
            
            // Re-enable transition
            wrapper.style.transition = 'transform 0.3s ease-out';
        }
    });
    
    container.addEventListener('mouseleave', () => {
        if (isDraggingMap) {
            isDraggingMap = false;
            container.style.cursor = isDrawing ? 'crosshair' : 'default';
            wrapper.style.transition = 'transform 0.3s ease-out';
        }
    });
    
    // Set initial cursor
    container.style.cursor = 'grab';
    
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

    // Color picker sync
    const colorPicker = document.getElementById('location-color');
    const colorText = document.getElementById('location-color-text');
    
    colorPicker.addEventListener('input', (e) => {
        colorText.value = e.target.value.toUpperCase();
    });
    
    colorText.addEventListener('input', (e) => {
        if (/^#[0-9A-Fa-f]{6}$/.test(e.target.value)) {
            colorPicker.value = e.target.value;
        }
    });

    // Type change updates color
    document.getElementById('location-type').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const defaultColor = selectedOption.dataset.color;
        if (defaultColor) {
            colorPicker.value = defaultColor;
            colorText.value = defaultColor.toUpperCase();
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
    document.getElementById('drawing-notice').classList.remove('hidden');
    document.getElementById('campus-map-container').style.cursor = 'crosshair';
    updatePointCounter();
    updateDrawingButtons();
}

function cancelDrawingMode() {
    isDrawing = false;
    currentPoints = [];
    document.getElementById('drawing-notice').classList.add('hidden');
    document.getElementById('campus-map-container').style.cursor = 'default';
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
    
    updatePointCounter();
    updateDrawingButtons();
    drawPreviewPolygon();
}

function undoLastPoint() {
    if (currentPoints.length > 0) {
        currentPoints.pop();
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
    
    openLocationModal(currentPoints);
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
    
    // Draw points with fixed size (compensate for zoom scale)
    const fixedRadius = 1.5 / currentScale; // Inverse of current zoom scale
    const fixedFontSize = 1.2 / currentScale;
    
    currentPoints.forEach((point, index) => {
        console.log(`➕ Adding point ${index + 1} at (${point.x}, ${point.y})`);
        
        // Create a group for each point (so we can make it draggable)
        const pointGroup = document.createElementNS('http://www.w3.org/2000/svg', 'g');
        pointGroup.setAttribute('data-point-index', index);
        pointGroup.style.cursor = 'grab';
        pointGroup.style.pointerEvents = 'auto'; // Enable mouse events
        
        // Use ellipse to compensate for aspect ratio, making it appear as a perfect circle
        const ellipse = document.createElementNS('http://www.w3.org/2000/svg', 'ellipse');
        ellipse.setAttribute('cx', point.x);
        ellipse.setAttribute('cy', point.y);
        ellipse.setAttribute('rx', fixedRadius); // Horizontal radius
        ellipse.setAttribute('ry', fixedRadius / aspectRatio); // Vertical radius - DIVIDE to compensate
        ellipse.setAttribute('fill', '#2563eb'); // Blue-600 - same as primary button
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
        polygon.classList.remove('hidden');
        console.log('🔷 Polygon created with points:', pointsString);
    } else {
        polygon.classList.add('hidden');
    }
}

function openLocationModal(vertices, locationData = null) {
    const modal = document.getElementById('location-modal');
    const form = document.getElementById('location-form');
    
    // Reset form
    form.reset();
    editingLocationId = null;
    document.querySelectorAll('[id^="error-"]').forEach(el => el.classList.add('hidden'));
    
    // Set vertices
    document.getElementById('location-vertices').value = JSON.stringify(vertices);
    document.getElementById('vertices-info').textContent = `${vertices.length} points added`;
    
    // If editing existing location
    if (locationData) {
        editingLocationId = locationData.id;
        document.getElementById('modal-title').textContent = 'Edit Location';
        document.getElementById('location-id').value = locationData.id;
        document.getElementById('location-type').value = locationData.type_id;
        document.getElementById('location-name').value = locationData.name;
        document.getElementById('location-code').value = locationData.short_code || '';
        document.getElementById('location-description').value = locationData.description || '';
        document.getElementById('location-color').value = locationData.color;
        document.getElementById('location-color-text').value = locationData.color;
    } else {
        document.getElementById('modal-title').textContent = 'Add New Location';
    }
    
    modal.classList.remove('hidden');
}

function closeLocationModal() {
    document.getElementById('location-modal').classList.add('hidden');
    editingLocationId = null;
}

async function handleFormSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = {};
    
    // Convert form data to object
    formData.forEach((value, key) => {
        if (key === 'vertices') {
            data[key] = JSON.parse(value);
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
    
    locations.forEach(location => {
        if (!location.is_active || !location.vertices || location.vertices.length < 3) return;
        
        // Create polygon group
        const g = document.createElementNS('http://www.w3.org/2000/svg', 'g');
        g.setAttribute('class', 'location-polygon');
        g.style.cursor = 'pointer';
        g.style.pointerEvents = 'auto';
        
        // Draw polygon (initially invisible, only shows on hover)
        const polygon = document.createElementNS('http://www.w3.org/2000/svg', 'polygon');
        const pointsString = location.vertices.map(v => `${v.x},${v.y}`).join(' ');
        polygon.setAttribute('points', pointsString);
        polygon.setAttribute('fill', 'transparent'); // Transparent by default
        polygon.setAttribute('stroke', 'none'); // No border
        polygon.setAttribute('class', 'transition-all duration-200');
        
        // Add label if there's a center point
        let label = null;
        if (location.center_x && location.center_y) {
            const fixedFontSize = 1 / currentScale; // Fixed size regardless of zoom
            
            label = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            label.setAttribute('x', location.center_x);
            label.setAttribute('y', location.center_y);
            label.setAttribute('text-anchor', 'middle');
            label.setAttribute('dominant-baseline', 'central');
            label.setAttribute('fill', '#4b5563'); // Dark gray (gray-600)
            label.setAttribute('stroke', '#ffffff'); // White outline
            label.setAttribute('stroke-width', 0.15 / currentScale); // Fixed stroke width
            label.setAttribute('paint-order', 'stroke fill'); // Draw stroke behind fill
            label.setAttribute('font-weight', '700'); // Bold for better visibility
            label.setAttribute('font-size', fixedFontSize);
            label.setAttribute('font-family', 'Inter, ui-sans-serif, system-ui, sans-serif'); // Same as app.blade
            // Apply transform to counter the aspect ratio stretch
            label.setAttribute('transform', `scale(1, ${1 / aspectRatio})`);
            label.setAttribute('transform-origin', `${location.center_x} ${location.center_y}`);
            label.setAttribute('style', 'pointer-events: none;');
            label.textContent = location.short_code || location.name.substring(0, 3).toUpperCase();
        }
        
        // Hover effects - show shape and tooltip immediately
        let tooltip = null;
        
        g.addEventListener('mouseenter', function(e) {
            polygon.setAttribute('fill', location.color + '40'); // Show with 25% opacity
            polygon.setAttribute('stroke', location.color);
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
        g.addEventListener('click', () => viewLocationDetails(location.id));
        
        g.appendChild(polygon);
        if (label) g.appendChild(label);
        svg.appendChild(g);
    });
}

function viewLocationDetails(id) {
    const location = locations.find(l => l.id === id);
    if (!location) return;
    
    const modal = document.getElementById('viewLocationModal');
    const content = document.getElementById('viewLocationContent');
    
    content.innerHTML = `
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
