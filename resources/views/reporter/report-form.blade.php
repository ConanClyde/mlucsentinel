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
                <label for="location" class="form-label">Location <span class="text-red-500">*</span></label>
                <input type="text" id="location" name="location" class="form-input" placeholder="e.g., Building A, Parking Lot 1" required>
            </div>

            <!-- Description -->
            <div class="form-group">
                <label for="description" class="form-label">Description <span class="text-red-500">*</span></label>
                <textarea id="description" name="description" rows="4" class="form-input" placeholder="Describe the violation in detail..." required></textarea>
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
                    <input type="file" id="evidence_image" name="evidence_image" accept="image/*" class="hidden" onchange="handleEvidenceFileUpload(event)">

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
            <h2 class="modal-title text-green-600 dark:text-green-400 flex items-center gap-2">
                <x-heroicon-o-check-circle class="w-6 h-6" />
                Report Submitted Successfully!
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

// Expose modal functions to global scope
window.showSuccessModal = showSuccessModal;
window.closeSuccessModal = closeSuccessModal;
</script>
@endpush
