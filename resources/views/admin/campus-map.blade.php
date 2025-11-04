@extends('layouts.app')

@section('page-title', 'Campus Map')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/campus-map.css') }}">
@endpush

@section('content')
<div class="space-y-4 md:space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 md:gap-4">
        <div>
            <h1 class="text-xl md:text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Campus Map Manager</h1>
            <p class="text-xs md:text-sm text-[#706f6c] dark:text-[#A1A09A]">Click points on the map to create polygon locations</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button id="add-location-btn" class="btn btn-primary text-xs md:text-sm !px-3 md:!px-4">
                Add Location
            </button>
            <a href="{{ route('admin.campus-map.download-stickers') }}" class="btn btn-secondary text-xs md:text-sm !px-3 md:!px-4">
                Download
            </a>
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
                </div>
            </div>
        </div>
    </div>

    <!-- Map and Table Layout -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 md:gap-6">
        <!-- Map Container (2/3 width on desktop) -->
        <div class="xl:col-span-2 order-2 xl:order-1">
            <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-3 md:p-4">
                <!-- Actual Map Container -->
                <div class="relative overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-900 w-full" id="campus-map-container">
                    
                    <!-- Loading Skeleton -->
                    <div id="map-loading-skeleton" class="absolute inset-0 flex items-center justify-center bg-gray-100 dark:bg-gray-900 z-50">
                        <div class="text-center">
                            <svg class="animate-spin h-12 w-12 mx-auto text-blue-600 dark:text-blue-400 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] font-medium">Loading campus map...</p>
                        </div>
                    </div>
                    <div id="map-wrapper" class="relative w-full" style="transform-origin: center center; transition: transform 0.3s ease-out;">
                        <!-- Campus Map Image -->
                        <img id="campus-map" src="{{ asset('images/campus-map.svg') }}" alt="Campus Map" 
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
                    <div class="absolute top-2 right-2 bg-white dark:bg-[#1a1a1a] rounded-lg shadow-lg border border-[#e3e3e0] dark:border-[#3E3E3A] p-2 space-y-2" style="z-index: 20; pointer-events: auto;">
                        <button id="zoom-in-btn" class="flex items-center justify-center w-8 h-8 bg-blue-600 dark:bg-blue-600 text-white rounded hover:bg-blue-700 dark:hover:bg-blue-700 transition-colors touch-manipulation">
                            <span class="text-lg font-bold leading-none">+</span>
                        </button>
                        <button id="zoom-out-btn" class="flex items-center justify-center w-8 h-8 bg-blue-600 dark:bg-blue-600 text-white rounded hover:bg-blue-700 dark:hover:bg-blue-700 transition-colors touch-manipulation">
                            <span class="text-lg font-bold leading-none">−</span>
                        </button>
                <button id="reset-zoom-btn" class="flex items-center justify-center w-8 h-8 border border-[#19140035] dark:border-[#3E3E3A] text-[#1b1b18] dark:text-[#EDEDEC] rounded hover:border-[#1915014a] dark:hover:border-[#62605b] transition-colors touch-manipulation">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </button>
            </div>
        </div>
            </div>
        </div>

        <!-- Locations Table / Form Card (1/3 width on desktop, full width on tablet/mobile) -->
        <div class="xl:col-span-1 order-1 xl:order-2">
            <!-- Locations List Card -->
            <div id="locations-list-card" class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-3 md:p-4 transition-all duration-300">
                <h3 class="text-base md:text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-3 md:mb-4">Locations</h3>
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
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                                <button onclick="editLocation({{ $location->id }})" class="btn-edit" title="Edit">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.829-2.828z"></path>
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

            <!-- Location Form Card (Hidden by default) -->
            <div id="location-form-card" class="hidden bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-3 md:p-4 transition-all duration-300">
                <div class="flex items-center justify-between mb-3 md:mb-4">
                    <div>
                        <h3 class="text-base md:text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]" id="form-card-title">Add New Location</h3>
                        <p class="text-xs md:text-sm text-[#706f6c] dark:text-[#A1A09A] mt-1" id="form-vertices-info">0 points added</p>
                    </div>
                    <button onclick="closeLocationForm()" class="text-[#706f6c] dark:text-[#A1A09A] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Edit Mode Instructions -->
                <div id="edit-mode-instructions" class="hidden bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3 mb-4 text-sm">
                    <p class="text-blue-800 dark:text-blue-300 font-medium mb-1">Edit Mode Controls:</p>
                    <ul class="text-blue-700 dark:text-blue-400 space-y-1 ml-4 list-disc">
                        <li><strong>Add points:</strong> Click on the map</li>
                        <li><strong>Move points:</strong> Left-click and drag points</li>
                        <li><strong>Remove points:</strong> Right-click on a point</li>
                        <li><strong>Move label:</strong> Right-click and drag the text label</li>
                        <li><strong>Pan map:</strong> Left-click and drag the map</li>
                    </ul>
                </div>
                
                <form id="location-form">
                    @csrf
                    <input type="hidden" id="location-id" name="location_id">
                    <input type="hidden" id="location-vertices" name="vertices">

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
                    </div>
                    
                    <!-- Hidden color input - automatically set based on location type -->
                    <input type="hidden" id="location-color" name="color" value="">
                    
                    <div class="flex space-x-2 mt-6 pt-4 border-t border-[#e3e3e0] dark:border-[#3E3E3A]">
                        <button type="button" onclick="closeLocationForm()" class="btn btn-secondary flex-1">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary flex-1">
                            Save Location
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div id="map-legend" class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4">
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

@include('admin.campus-map.modals')

@endsection

@push('scripts')
<script>
// Pass data to JavaScript
window.campusMapData = {
    locationTypes: @json($locationTypes),
    locations: @json($locations)
};

// Expose functions globally for inline event handlers
window.handleMapError = function(img) {
    img.onerror = null;
    img.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAwIiBoZWlnaHQ9IjYwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iODAwIiBoZWlnaHQ9IjYwMCIgZmlsbD0iI2Y3ZjlmNyIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjQiIGZpbGw9IiM5Yzk5YzkiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5VcGxvYWQgQ2FtcHVzIE1hcDwvdGV4dD48dGV4dCB4PSI1MCUiIHk9IjU1JSIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjE0IiBmaWxsPSIjYWJhYmFiIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSI+UGxhY2UgY2FtcHVzIG1hcCBhdCBwdWJsaWMvaW1hZ2VzL2NhbXB1cy1tYXAucG5nPC90ZXh0Pjwvc3ZnPg==';
    img.alt = 'Upload Campus Map';
};

window.initializeMapDimensions = function() {
    const img = document.getElementById('campus-map');
    const container = document.getElementById('campus-map-container');
    const skeleton = document.getElementById('map-loading-skeleton');
    
    if (img && img.complete && img.naturalHeight > 0) {
        // Hide skeleton and show map
        if (skeleton) skeleton.classList.add('hidden');
        if (container) container.classList.remove('hidden');
        
        // Trigger the main initialization if the module is loaded
        if (window.campusMapModule && window.campusMapModule.initializeMapDimensions) {
            window.campusMapModule.initializeMapDimensions();
        }
    }
};

// Download campus map with locations
</script>
@vite('resources/js/admin/campus-map.js')
@endpush

