@extends('layouts.app')

@section('page-title', 'Submit Report')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                    Submit Violation Report
                </h2>
                <p class="text-[#706f6c] dark:text-[#A1A09A]">
                    Report a violation for the selected vehicle
                </p>
            </div>
            <div class="w-16 h-16 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                <x-heroicon-o-exclamation-triangle class="w-8 h-8 text-red-600 dark:text-red-400" />
            </div>
        </div>
    </div>

    <!-- Vehicle Information Card -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
        <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Vehicle Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Owner</p>
                <p class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]">{{ $vehicle->user->first_name ?? '' }} {{ $vehicle->user->last_name ?? '' }}</p>
            </div>
            <div>
                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Vehicle Type</p>
                <p class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]">{{ $vehicle->type->name ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Plate Number / Sticker</p>
                <p class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                    @if($vehicle->plate_no)
                        {{ $vehicle->plate_no }}
                    @else
                        {{ ucfirst($vehicle->color) }}-{{ $vehicle->number }}
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Report Form -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
        <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Report Details</h3>
        
        <form id="reportForm" class="space-y-6">
            @csrf
            <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">

            <!-- Violation Type -->
            <div class="form-group">
                <label for="violation_type_id" class="form-label">Violation Type <span class="text-red-500">*</span></label>
                <select id="violation_type_id" name="violation_type_id" class="form-input" required>
                    <option value="">Select violation type</option>
                    @foreach($violationTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Location -->
            <div class="form-group">
                <label class="form-label">Location <span class="text-red-500">*</span></label>
                <input type="hidden" id="location_id" name="location_id">
                <input type="hidden" id="location" name="location">
                <input type="hidden" id="pin_x" name="pin_x">
                <input type="hidden" id="pin_y" name="pin_y">
                
                <!-- Interactive Map Display -->
                <div class="bg-gray-50 dark:bg-[#161615] p-4 rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A]">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Pin Location on Map</h4>
                        <div class="flex gap-2">
                            <button type="button" onclick="resetMapView()" class="btn btn-sm btn-secondary">
                                <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Reset View
                            </button>
                        </div>
                    </div>
                    
                    <!-- Map Container -->
                    <div id="report-map-container" class="relative w-full bg-gray-100 dark:bg-gray-800 rounded-lg overflow-hidden" style="min-height: 400px; max-height: 500px;">
                        <!-- Zoom Controls (inside container, outside wrapper so they don't zoom) -->
                        <div class="absolute top-2 right-2 bg-white dark:bg-[#1a1a1a] rounded-lg shadow-lg border border-[#e3e3e0] dark:border-[#3E3E3A] p-2 space-y-2" style="z-index: 10; pointer-events: auto;">
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
                        
                        <!-- Map Wrapper -->
                        <div id="report-map-wrapper" class="w-full h-full cursor-grab active:cursor-grabbing" style="transform-origin: center center; transition: transform 0.1s ease-out;">
                            <img id="report-map-image" 
                                 src="{{ asset('images/campus-map.png') }}" 
                                 alt="Campus Map" 
                                 class="block w-full h-auto select-none" 
                                 draggable="false"
                                 onload="initializeReportMapDimensions()"
                                 onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAwIiBoZWlnaHQ9IjYwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iODAwIiBoZWlnaHQ9IjYwMCIgZmlsbD0iI2Y3ZjlmNyIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjQiIGZpbGw9IiM5Yzk5YzkiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5NYXAgTm90IEF2YWlsYWJsZTwvdGV4dD48L3N2Zz4=';"
                                 style="display: block;">
                            
                            <svg id="report-locations-svg" class="absolute inset-0 w-full h-full pointer-events-none" viewBox="0 0 100 100" preserveAspectRatio="none">
                                <defs>
                                    <filter id="report-glow">
                                        <feGaussianBlur stdDeviation="0.5" result="coloredBlur"/>
                                        <feMerge>
                                            <feMergeNode in="coloredBlur"/>
                                            <feMergeNode in="SourceGraphic"/>
                                        </feMerge>
                                    </filter>
                                </defs>
                            </svg>
                            
                            <!-- Pin (inside wrapper so it moves with map) -->
                            <div id="violation-pin" class="absolute pointer-events-none hidden" style="transform-origin: 50% 100%; z-index: 20;">
                                <svg width="32" height="40" viewBox="0 0 32 40" fill="none" xmlns="http://www.w3.org/2000/svg" style="display: block; margin-left: -16px; margin-top: -40px;">
                                    <!-- Shadow -->
                                    <ellipse cx="16" cy="38" rx="8" ry="2" fill="black" opacity="0.3"/>
                                    <!-- Pin body -->
                                    <path d="M16 0C9.373 0 4 5.373 4 12c0 8.4 12 28 12 28s12-19.6 12-28c0-6.627-5.373-12-12-12z" fill="#EA4335"/>
                                    <!-- Inner circle -->
                                    <circle cx="16" cy="12" r="5" fill="white"/>
                                    <!-- Inner dot -->
                                    <circle cx="16" cy="12" r="2.5" fill="#C5221F"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Selected Location Info -->
                    <div id="selected-location-info" class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg hidden">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-blue-800 dark:text-blue-200" id="selected-location-name">Location Selected</p>
                                <p class="text-xs text-blue-600 dark:text-blue-400" id="selected-location-coords">Coordinates: 0, 0</p>
                            </div>
                        </div>
                    </div>
                </div>
                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-1">Click anywhere on the map to pin the violation location</p>
            </div>

            <!-- Description -->
            <div class="form-group">
                <label for="description" class="form-label">Description <span class="text-gray-400">(Optional)</span></label>
                <textarea id="description" name="description" rows="4" class="form-input" placeholder="Describe the violation in detail..."></textarea>
            </div>

            <!-- Evidence Image Upload -->
            <div class="form-group">
                <label class="form-label">Evidence Image <span class="text-red-500">*</span></label>
                <div class="bg-gray-50 dark:bg-[#161615] p-4 rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A]">
                    <!-- Upload Options -->
                    <div id="evidenceUploadOptions" class="flex gap-4 mb-4">
                        <button type="button" class="btn btn-info" onclick="openEvidenceCameraModal()">
                            <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            Take Photo
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('evidence_image').click()">
                            <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            Upload File
                        </button>
                    </div>

                    <!-- Hidden File Input -->
                    <input type="file" id="evidence_image" name="evidence_image" accept="image/jpeg,image/jpg,image/png,image/heic,image/heif" class="hidden" onchange="handleEvidenceFileUpload(event)">

                    <!-- Image Preview -->
                    <div id="evidenceImagePreview" class="hidden">
                        <h4 class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] mb-2">Evidence Image</h4>
                        <div class="relative inline-block">
                            <img id="evidencePreviewImage" src="" alt="Evidence Preview" class="w-full max-w-md rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A]">
                            <button type="button" onclick="removeEvidencePreview()" class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    </div>
                    
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-2">Upload a clear photo as evidence of the violation (max 2MB)</p>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end gap-3">
                <a href="{{ route('reporter.report-user') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-danger">Submit Report</button>
            </div>
        </form>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="modal-backdrop hidden">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title flex items-center gap-3">
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center flex-shrink-0">
                    <x-heroicon-o-check-circle class="w-6 h-6 text-green-600 dark:text-green-400" />
                </div>
                <span class="text-[#1b1b18] dark:text-[#EDEDEC]">Report Submitted Successfully!</span>
            </h2>
        </div>
        <div class="modal-body">
            <p id="successMessage">Your report has been submitted and assigned to the appropriate administrator.</p>
            <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <p class="text-sm text-blue-800 dark:text-blue-200">
                    <strong>Assigned to:</strong> <span id="assignedToRole"></span>
                </p>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-success" onclick="closeSuccessModal()">View My Reports</button>
        </div>
    </div>
</div>

<!-- Evidence Camera Modal -->
<div id="evidenceCameraModal" class="modal-backdrop hidden">
    <div class="camera-container max-w-4xl">
        <div class="modal-header flex justify-between items-center">
            <h2 class="modal-title">Camera</h2>
            <button onclick="closeEvidenceCameraModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <div class="modal-body p-0 relative">
            <video id="evidenceCameraVideo" autoplay playsinline class="w-full h-auto bg-black max-h-[70vh] sm:max-h-[80vh] object-cover"></video>
            <canvas id="evidenceCameraCanvas" class="hidden"></canvas>
            <!-- Flip Camera Button -->
            <button onclick="flipEvidenceCamera()" class="absolute top-4 right-4 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 rounded-full p-3 shadow-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" title="Flip Camera">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </button>
        </div>
        <div class="modal-footer">
            <button class="btn-camera" onclick="captureEvidencePhoto()">
                <svg class="w-6 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let evidenceCameraStream = null;
let evidenceImageFile = null;
let evidenceFacingMode = 'environment'; // 'environment' for back camera, 'user' for front camera

// Camera functions
window.openEvidenceCameraModal = async function() {
    try {
        evidenceCameraStream = await navigator.mediaDevices.getUserMedia({ 
            video: { 
                facingMode: evidenceFacingMode,
                width: { ideal: 1920 },
                height: { ideal: 1080 }
            } 
        });
        
        const video = document.getElementById('evidenceCameraVideo');
        video.srcObject = evidenceCameraStream;
        document.getElementById('evidenceCameraModal').classList.remove('hidden');
    } catch (error) {
        console.error('Error accessing camera:', error);
        alert('Unable to access camera. Please check permissions or use file upload instead.');
    }
}

window.closeEvidenceCameraModal = function() {
    if (evidenceCameraStream) {
        evidenceCameraStream.getTracks().forEach(track => track.stop());
        evidenceCameraStream = null;
    }
    document.getElementById('evidenceCameraModal').classList.add('hidden');
}

window.flipEvidenceCamera = async function() {
    // Stop current stream
    if (evidenceCameraStream) {
        evidenceCameraStream.getTracks().forEach(track => track.stop());
        evidenceCameraStream = null;
    }
    
    // Toggle facing mode
    evidenceFacingMode = evidenceFacingMode === 'environment' ? 'user' : 'environment';
    
    // Restart camera with new facing mode
    await window.openEvidenceCameraModal();
}

window.captureEvidencePhoto = function() {
    const video = document.getElementById('evidenceCameraVideo');
    const canvas = document.getElementById('evidenceCameraCanvas');
    const context = canvas.getContext('2d');
    
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    
    canvas.toBlob(function(blob) {
        evidenceImageFile = new File([blob], 'evidence.jpg', { type: 'image/jpeg' });
        
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('evidencePreviewImage').src = e.target.result;
            document.getElementById('evidenceImagePreview').classList.remove('hidden');
            document.getElementById('evidenceUploadOptions').classList.add('hidden');
        };
        reader.readAsDataURL(blob);
        
        closeEvidenceCameraModal();
    }, 'image/jpeg', 0.8);
}

window.handleEvidenceFileUpload = function(event) {
    const file = event.target.files[0];
    if (file) {
        const maxSize = 2 * 1024 * 1024; // 2MB
        if (file.size > maxSize) {
            alert('File size exceeds 2MB. Please choose a smaller file.');
            event.target.value = '';
            return;
        }
        
        evidenceImageFile = file;
        
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('evidencePreviewImage').src = e.target.result;
            document.getElementById('evidenceImagePreview').classList.remove('hidden');
            document.getElementById('evidenceUploadOptions').classList.add('hidden');
        };
        reader.readAsDataURL(file);
    }
};

window.removeEvidencePreview = function() {
    const imagePreview = document.getElementById('evidenceImagePreview');
    const uploadOptions = document.getElementById('evidenceUploadOptions');
    
    imagePreview.classList.add('hidden');
    uploadOptions.classList.remove('hidden');
    document.getElementById('evidence_image').value = '';
    evidenceImageFile = null;
}

// Form submission
document.getElementById('reportForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!evidenceImageFile) {
        alert('Please upload or capture an evidence image');
        return;
    }
    
    const formData = new FormData(this);
    formData.append('evidence_image', evidenceImageFile);
    
    // Disable submit button to prevent double submission
    const submitButton = this.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    submitButton.disabled = true;
    submitButton.innerHTML = '<svg class="animate-spin h-5 w-5 inline-block mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Submitting...';
    
    fetch('{{ route("reporter.report-submit") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success modal with assigned role
            document.getElementById('assignedToRole').textContent = data.assigned_to_role;
            showSuccessModal();
        } else {
            alert(data.message || 'Failed to submit report');
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while submitting the report');
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
    });
});

// Success modal functions
function showSuccessModal() {
    const modal = document.getElementById('successModal');
    if (modal) {
        modal.classList.remove('hidden');
    }
}

function closeSuccessModal() {
    const modal = document.getElementById('successModal');
    if (modal) {
        modal.classList.add('hidden');
    }
    // Redirect to my reports page
    window.location.href = '{{ route("reporter.my-reports") }}';
}

// Interactive Map with Pin Functionality
let mapLocations = @json($mapLocations);
let reportMapScale = 1;
let reportMapPanX = 0;
let reportMapPanY = 0;
let reportMapAspectRatio = 1;
let isDragging = false;
let lastMouseX = 0;
let lastMouseY = 0;
let selectedPinX = null;
let selectedPinY = null;
let nearestLocation = null;
let hasDragged = false;

// Initialize map when page loads
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        const img = document.getElementById('report-map-image');
        if (img && img.complete && img.naturalHeight > 0) {
            initReportMap();
        } else if (img) {
            img.onload = function() {
                initReportMap();
            };
        }
    }, 500); // Small delay to ensure DOM is ready
});

function resetMapView() {
    resetZoom();
}

// Remove old modal functions - map is now inline

function initReportMap() {
    const img = document.getElementById('report-map-image');
    const container = document.getElementById('report-map-container');
    const wrapper = document.getElementById('report-map-wrapper');
    
    // Initialize dimensions when image loads
    if (img.complete) {
        initializeReportMapDimensions();
    } else {
        img.addEventListener('load', initializeReportMapDimensions);
    }
    
    // Add event listeners
    addReportMapEventListeners();
    
    // Zoom controls
    document.getElementById('zoom-in-btn').addEventListener('click', () => zoomMap(0.2));
    document.getElementById('zoom-out-btn').addEventListener('click', () => zoomMap(-0.2));
    document.getElementById('reset-zoom-btn').addEventListener('click', resetZoom);
    
    // Handle window resize to maintain aspect ratio
    window.addEventListener('resize', function() {
        initializeReportMapDimensions();
    });
}

function initializeReportMapDimensions() {
    const img = document.getElementById('report-map-image');
    const container = document.getElementById('report-map-container');
    
    // Set container height based on image aspect ratio
    if (img && img.complete && img.naturalHeight > 0) {
        reportMapAspectRatio = img.naturalHeight / img.naturalWidth;
        const containerWidth = container.offsetWidth;
        const newHeight = containerWidth * reportMapAspectRatio;
        container.style.height = newHeight + 'px';
        container.style.minHeight = newHeight + 'px';
        
        // Apply initial transform
        applyReportMapTransform();
        
        // Render existing locations
        renderReportMapLocations();
        
        console.log('✅ Report map dimensions initialized');
    }
}

function addReportMapEventListeners() {
    const wrapper = document.getElementById('report-map-wrapper');
    const container = document.getElementById('report-map-container');
    
    // Mouse events for dragging
    container.addEventListener('mousedown', handleReportMapMouseDown);
    container.addEventListener('mousemove', handleReportMapMouseMove);
    container.addEventListener('mouseup', handleReportMapMouseUp);
    container.addEventListener('mouseleave', handleReportMapMouseUp);
    
    // Click to pin
    container.addEventListener('click', handleReportMapClick);
    
    // Wheel for zoom
    container.addEventListener('wheel', handleReportMapWheel);
}

let dragStartX = 0;
let dragStartY = 0;
let startPanX = 0;
let startPanY = 0;

function handleReportMapMouseDown(e) {
    if (e.target.tagName === 'IMG') {
        isDragging = true;
        hasDragged = false;
        dragStartX = e.clientX;
        dragStartY = e.clientY;
        startPanX = reportMapPanX;
        startPanY = reportMapPanY;
        e.preventDefault();
    }
}

function handleReportMapMouseMove(e) {
    if (!isDragging) return;
    
    const deltaX = e.clientX - dragStartX;
    const deltaY = e.clientY - dragStartY;
    
    // If moved more than 10 pixels, consider it a drag
    if (Math.abs(deltaX) > 10 || Math.abs(deltaY) > 10) {
        hasDragged = true;
        
        reportMapPanX = startPanX + deltaX;
        reportMapPanY = startPanY + deltaY;
        
        applyReportMapTransform();
    }
}

function handleReportMapMouseUp() {
    isDragging = false;
}

function handleReportMapClick(e) {
    if (e.target.tagName === 'IMG' && !hasDragged) {
        const rect = e.target.getBoundingClientRect();
        const x = ((e.clientX - rect.left) / rect.width) * 100;
        const y = ((e.clientY - rect.top) / rect.height) * 100;
        
        pinLocation(x, y);
    }
}

function handleReportMapWheel(e) {
    e.preventDefault();
    
    const delta = e.deltaY > 0 ? -0.1 : 0.1;
    const oldScale = reportMapScale;
    const newScale = Math.min(Math.max(1, reportMapScale + delta), 3);
    
    if (newScale !== oldScale) {
        const container = document.getElementById('report-map-container');
        const rect = container.getBoundingClientRect();
        const mouseX = e.clientX - rect.left - rect.width / 2;
        const mouseY = e.clientY - rect.top - rect.height / 2;
        
        // Calculate the zoom point offset from center
        // When zooming with center origin, we need to adjust pan to keep cursor point stationary
        const scaleRatio = newScale / oldScale;
        
        // Adjust pan: move by the difference in how far the point is from center at different scales
        reportMapPanX = reportMapPanX - (mouseX - reportMapPanX) * (scaleRatio - 1);
        reportMapPanY = reportMapPanY - (mouseY - reportMapPanY) * (scaleRatio - 1);
        
        // Update scale
        reportMapScale = newScale;
        
        applyReportMapTransform();
    }
}

function applyReportMapTransform() {
    const wrapper = document.getElementById('report-map-wrapper');
    const container = document.getElementById('report-map-container');
    
    // Get actual dimensions
    const containerWidth = container.offsetWidth;
    const containerHeight = container.offsetHeight;
    
    // Calculate scaled dimensions
    const scaledWidth = containerWidth * reportMapScale;
    const scaledHeight = containerHeight * reportMapScale;
    
    // Calculate how much extra space we have when scaled (due to center origin)
    const extraWidth = (scaledWidth - containerWidth) / 2;
    const extraHeight = (scaledHeight - containerHeight) / 2;
    
    // Constrain pan values
    if (reportMapScale > 1) {
        // With center origin, the image expands equally in all directions
        // We can pan in both directions within the extra space
        const maxPanX = extraWidth;
        const minPanX = -extraWidth;
        const maxPanY = extraHeight;
        const minPanY = -extraHeight;
        
        reportMapPanX = Math.max(minPanX, Math.min(maxPanX, reportMapPanX));
        reportMapPanY = Math.max(minPanY, Math.min(maxPanY, reportMapPanY));
    } else {
        // At 100% zoom, reset pan to center
        reportMapPanX = 0;
        reportMapPanY = 0;
    }
    
    wrapper.style.transform = `translate(${reportMapPanX}px, ${reportMapPanY}px) scale(${reportMapScale})`;
    
    // Counter-scale the pin so it stays the same size
    const pin = document.getElementById('violation-pin');
    if (pin && !pin.classList.contains('hidden')) {
        const inverseScale = 1 / reportMapScale;
        pin.style.transform = `scale(${inverseScale})`;
    }
}

function zoomMap(delta) {
    const newScale = Math.min(Math.max(1, reportMapScale + delta), 3);
    reportMapScale = newScale;
    applyReportMapTransform();
}

function resetZoom() {
    reportMapScale = 1;
    reportMapPanX = 0;
    reportMapPanY = 0;
    applyReportMapTransform();
}

function pinLocation(x, y) {
    selectedPinX = x;
    selectedPinY = y;
    
    // Show pin
    const pin = document.getElementById('violation-pin');
    pin.style.left = x + '%';
    pin.style.top = y + '%';
    pin.classList.remove('hidden');
    
    // Find nearest location
    nearestLocation = findNearestLocation(x, y);
    
    // Update info
    const info = document.getElementById('selected-location-info');
    const nameEl = document.getElementById('selected-location-name');
    const coordsEl = document.getElementById('selected-location-coords');
    
    if (nearestLocation) {
        nameEl.textContent = `Near: ${nearestLocation.name}`;
        coordsEl.textContent = `Coordinates: ${x.toFixed(1)}%, ${y.toFixed(1)}%`;
    } else {
        nameEl.textContent = 'Custom Location';
        coordsEl.textContent = `Coordinates: ${x.toFixed(1)}%, ${y.toFixed(1)}%`;
    }
    
    info.classList.remove('hidden');
    
    // Update form preview
    updateLocationPreview();
}

function findNearestLocation(x, y) {
    let nearest = null;
    let minDistance = Infinity;
    
    mapLocations.forEach(location => {
        if (location.center_x && location.center_y) {
            const distance = Math.sqrt(
                Math.pow(x - location.center_x, 2) + 
                Math.pow(y - location.center_y, 2)
            );
            
            if (distance < minDistance && distance < 10) { // Within 10% of map
                minDistance = distance;
                nearest = location;
            }
        }
    });
    
    return nearest;
}

function updateLocationPreview() {
    if (selectedPinX !== null && selectedPinY !== null) {
        // Set form values
        document.getElementById('pin_x').value = selectedPinX;
        document.getElementById('pin_y').value = selectedPinY;
        
        if (nearestLocation) {
            document.getElementById('location_id').value = nearestLocation.id;
            document.getElementById('location').value = nearestLocation.name;
        } else {
            document.getElementById('location_id').value = '';
            document.getElementById('location').value = `Custom Location (${selectedPinX.toFixed(1)}%, ${selectedPinY.toFixed(1)}%)`;
        }
        
        // Update preview (if element exists)
        const preview = document.getElementById('location-preview');
        if (preview) {
            preview.innerHTML = `
                <div class="flex items-center">
                    <svg class="w-4 h-4 text-green-600 dark:text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-sm font-medium text-green-800 dark:text-green-200">${document.getElementById('location').value}</span>
                </div>
            `;
        }
    }
}

function renderReportMapLocations() {
    const svg = document.getElementById('report-locations-svg');
    if (!svg) return;
    
    // Clear existing
    while (svg.children.length > 1) {
        svg.removeChild(svg.lastChild);
    }
    
    mapLocations.forEach(location => {
        if (!location.vertices || location.vertices.length < 3) return;
        
        const g = document.createElementNS('http://www.w3.org/2000/svg', 'g');
        g.setAttribute('class', 'location-item');
        g.style.pointerEvents = 'none'; // Disable interaction for existing locations
        
        // Polygon
        const polygon = document.createElementNS('http://www.w3.org/2000/svg', 'polygon');
        const points = location.vertices.map(v => `${v.x},${v.y}`).join(' ');
        polygon.setAttribute('points', points);
        polygon.setAttribute('fill', location.color + '20'); // More transparent
        polygon.setAttribute('stroke', location.color);
        polygon.setAttribute('stroke-width', '0.2');
        
        // Label
        let label = null;
        if (location.center_x && location.center_y) {
            label = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            label.setAttribute('x', location.center_x);
            label.setAttribute('y', location.center_y);
            label.setAttribute('text-anchor', 'middle');
            label.setAttribute('dominant-baseline', 'central');
            label.setAttribute('fill', '#6b7280');
            label.setAttribute('stroke', '#ffffff');
            label.setAttribute('stroke-width', '0.1');
            label.setAttribute('paint-order', 'stroke fill');
            label.setAttribute('font-weight', '600');
            label.setAttribute('font-size', '0.8');
            label.setAttribute('font-family', 'Satoshi, ui-sans-serif, system-ui, sans-serif');
            label.setAttribute('transform', `scale(1, ${1 / reportMapAspectRatio})`);
            label.setAttribute('transform-origin', `${location.center_x} ${location.center_y}`);
            label.textContent = location.short_code || location.name.substring(0, 3).toUpperCase();
        }
        
        g.appendChild(polygon);
        if (label) g.appendChild(label);
        svg.appendChild(g);
    });
}

// Expose functions to global scope
window.showSuccessModal = showSuccessModal;
window.closeSuccessModal = closeSuccessModal;
window.zoomMap = zoomMap;
window.resetZoom = resetZoom;
window.resetMapView = resetMapView;
window.updateLocationPreview = updateLocationPreview;
</script>
@endpush

