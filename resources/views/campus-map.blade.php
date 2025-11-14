@extends('layouts.app')

@section('page-title', 'Campus Map')

@push('styles')
@vite('resources/css/admin/campus-map.css')
@endpush

@section('content')
<div class="space-y-4 md:space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 md:gap-4">
        <div>
            <h1 class="text-xl md:text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Campus Map</h1>
            <p class="text-xs md:text-sm text-[#706f6c] dark:text-[#A1A09A]">View campus locations and parking areas</p>
        </div>
    </div>

    <!-- Map Container -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] overflow-hidden">
        <div class="p-4 md:p-6">
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
                         onload="hideMapSkeleton()">

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
                </div>

                <!-- Zoom Controls -->
                <div class="absolute top-4 right-4 flex flex-col space-y-2 z-10">
                    <button id="zoom-in-btn" class="bg-white dark:bg-[#1a1a1a] border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-lg p-2 shadow-sm hover:bg-gray-50 dark:hover:bg-[#2a2a2a] transition-colors">
                        <svg class="w-5 h-5 text-[#1b1b18] dark:text-[#EDEDEC]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </button>
                    <button id="zoom-out-btn" class="bg-white dark:bg-[#1a1a1a] border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-lg p-2 shadow-sm hover:bg-gray-50 dark:hover:bg-[#2a2a2a] transition-colors">
                        <svg class="w-5 h-5 text-[#1b1b18] dark:text-[#EDEDEC]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6"></path>
                        </svg>
                    </button>
                    <button id="reset-zoom-btn" class="bg-white dark:bg-[#1a1a1a] border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-lg p-2 shadow-sm hover:bg-gray-50 dark:hover:bg-[#2a2a2a] transition-colors">
                        <svg class="w-5 h-5 text-[#1b1b18] dark:text-[#EDEDEC]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6">
        <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Map Legend</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4" id="legend-items">
            @foreach($locationTypes as $type)
            <div class="flex items-center space-x-2">
                <div class="w-6 h-6 rounded" style="background-color: {{ $type->default_color }};"></div>
                <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">{{ $type->name }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Pass data to JavaScript
window.campusMapData = {
    locationTypes: @json($locationTypes),
    locations: @json($locations)
};

// Set view-only mode before the main script loads
window.campusMapConfig = {
    viewOnly: true,
    showLegend: true,
    enableLocationDetails: true,
    disableEditing: true
};

// Add missing functions that the admin script expects
function hideMapSkeleton() {
    const skeleton = document.getElementById('map-loading-skeleton');
    if (skeleton) {
        skeleton.style.display = 'none';
    }
}

function handleMapError(img) {
    console.error('Failed to load campus map image');
    const skeleton = document.getElementById('map-loading-skeleton');
    if (skeleton) {
        skeleton.innerHTML = '<div class="text-center"><p class="text-red-500">Failed to load campus map</p></div>';
    }
}


// Stub out admin-specific elements that might be referenced
document.addEventListener('DOMContentLoaded', function() {
    // Create stub elements for admin-specific functionality
    const stubElements = [
        'add-location-btn',
        'cancel-drawing-btn',
        'save-location-btn',
        'undo-point-btn',
        'drawing-notice',
        'point-counter',
        'location-form',
        'location-name',
        'location-type',
        'location-description',
        'viewLocationModal',
        'viewLocationContent'
    ];
    
    stubElements.forEach(id => {
        if (!document.getElementById(id)) {
            const stub = document.createElement('div');
            stub.id = id;
            stub.style.display = 'none';
            document.body.appendChild(stub);
        }
    });
});
</script>
@vite('resources/js/admin/campus-map.js')
@endpush
