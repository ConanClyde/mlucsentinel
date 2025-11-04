@extends('layouts.app')

@section('page-title', 'Report User')

@section('content')
<div class="space-y-4 md:space-y-6">
    <!-- Page Header - Simple -->
    <div class="mb-4 md:mb-6">
        <h2 class="text-xl sm:text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-1 sm:mb-2">
            Report Violation
        </h2>
        <p class="text-sm sm:text-base text-[#706f6c] dark:text-[#A1A09A]">
            Choose identification method
        </p>
        @php
            $isSBO = Auth::user()->user_type === App\Enums\UserType::Reporter && 
                     Auth::user()->reporter && 
                     (Auth::user()->reporter->reporterType->name ?? '') === 'SBO';
        @endphp
        @if($isSBO)
            <div class="mt-3 p-2 sm:p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                <p class="text-xs sm:text-sm text-yellow-800 dark:text-yellow-200 flex items-start sm:items-center">
                    <svg class="w-4 h-4 mr-2 flex-shrink-0 mt-0.5 sm:mt-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <span><strong>Note:</strong> As an SBO member, you can only report student vehicles.</span>
                </p>
            </div>
        @endif
    </div>

    <!-- Options Grid - Mobile Optimized with Large Touch Targets -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
        <!-- QR Scan Option - Primary Action -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-xl shadow-lg border-2 border-[#e3e3e0] dark:border-[#3E3E3A] p-6 sm:p-8 hover:border-blue-500 dark:hover:border-blue-500 active:scale-[0.98] transition-all cursor-pointer" onclick="openQRScanner()">
            <div class="flex flex-col items-center text-center space-y-4">
                <div class="w-16 h-16 sm:w-20 sm:h-20 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 sm:w-10 sm:h-10 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg sm:text-xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">QR Scan</h3>
                    <p class="text-sm sm:text-base text-[#706f6c] dark:text-[#A1A09A]">Scan sticker QR code</p>
                </div>
                <button type="button" class="btn btn-primary w-full">
                    <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Scan Now
                </button>
            </div>
        </div>

        <!-- Manual Entry Option -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-xl shadow-lg border-2 border-[#e3e3e0] dark:border-[#3E3E3A] p-6 sm:p-8 hover:border-green-500 dark:hover:border-green-500 active:scale-[0.98] transition-all cursor-pointer" onclick="openManualEntry()">
            <div class="flex flex-col items-center text-center space-y-4">
                <div class="w-16 h-16 sm:w-20 sm:h-20 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 sm:w-10 sm:h-10 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg sm:text-xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Manual Entry</h3>
                    <p class="text-sm sm:text-base text-[#706f6c] dark:text-[#A1A09A]">Enter details manually</p>
                </div>
                <button type="button" class="btn btn-success w-full">
                    <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                    </svg>
                    Enter Manually
                </button>
            </div>
        </div>
    </div>
</div>

<!-- QR Scanner Modal -->
<div id="qrScannerModal" class="modal-backdrop hidden" onclick="if(event.target === this) closeQRScanner()">
    <div class="modal-container max-w-2xl">
        <div class="modal-header flex justify-between items-center">
            <h2 class="modal-title">Scan Vehicle QR Code</h2>
            <button onclick="closeQRScanner()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <div class="modal-body p-0 relative">
            <video id="qrVideo" class="w-full aspect-square object-cover bg-black" autoplay playsinline></video>
            <canvas id="qrCanvas" class="hidden"></canvas>
            <!-- Flip Camera Button -->
            <button onclick="flipQRCamera()" class="absolute top-4 right-4 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 rounded-full p-3 shadow-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" title="Flip Camera">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </button>
        </div>
    </div>
</div>

<!-- Manual Entry Modal -->
<div id="manualEntryModal" class="modal-backdrop hidden" onclick="if(event.target === this) closeManualEntry()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title">Enter Vehicle Information</h2>
        </div>
        <div class="modal-body">
            <form id="manualEntryForm" class="space-y-4">
                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mb-4">Enter either the sticker information OR the plate number</p>
                
                <!-- Sticker Information -->
                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <h3 class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-3">Sticker Information</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label">Sticker Number</label>
                            <input type="text" id="sticker_number" class="form-input" placeholder="e.g., 12345">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Sticker Color</label>
                            <select id="sticker_color" class="form-input">
                                <option value="">Select Color</option>
                                <option value="blue">Blue</option>
                                <option value="green">Green</option>
                                <option value="yellow">Yellow</option>
                                <option value="pink">Pink</option>
                                <option value="orange">Orange</option>
                                <option value="maroon">Maroon</option>
                                <option value="white">White</option>
                                <option value="black">Black</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="text-center text-[#706f6c] dark:text-[#A1A09A] font-semibold">OR</div>

                <!-- Plate Number -->
                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <h3 class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-3">Plate Number</h3>
                    <div class="form-group">
                        <label class="form-label">Plate Number</label>
                        <input type="text" id="plate_no" class="form-input" placeholder="e.g., ABC-1234">
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button onclick="closeManualEntry()" class="btn btn-secondary">Cancel</button>
            <button onclick="searchVehicle()" class="btn btn-primary">Search Vehicle</button>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div id="errorModal" class="modal-backdrop hidden">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title flex items-center gap-3">
                <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center flex-shrink-0">
                    <x-heroicon-o-exclamation-circle class="w-6 h-6 text-red-600 dark:text-red-400" />
                </div>
                <span class="text-[#1b1b18] dark:text-[#EDEDEC]">Vehicle Not Found</span>
            </h2>
        </div>
        <div class="modal-body">
            <p id="errorMessage" class="text-[#706f6c] dark:text-[#A1A09A]"></p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeErrorModal()">Close</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://unpkg.com/@zxing/library@0.20.0/umd/index.min.js"></script>
<script>
// Check for session error on page load
document.addEventListener('DOMContentLoaded', function() {
    @if(session('error'))
        showErrorModal('{{ session('error') }}');
    @endif
});

let qrCodeReader = null;
let qrStream = null;
let qrFacingMode = 'environment'; // 'environment' for back camera, 'user' for front camera

function openQRScanner() {
    document.getElementById('qrScannerModal').classList.remove('hidden');
    startQRScanner();
}

function closeQRScanner() {
    if (qrStream) {
        qrStream.getTracks().forEach(track => track.stop());
        qrStream = null;
    }
    if (qrCodeReader) {
        qrCodeReader.reset();
    }
    document.getElementById('qrScannerModal').classList.add('hidden');
}

async function startQRScanner() {
    try {
        // Check if camera API is available
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            alert('Camera access is not supported in your browser. Please use a modern browser or enable camera permissions.');
            closeQRScanner();
            return;
        }

        // Check if ZXing library is loaded
        if (typeof ZXing === 'undefined') {
            alert('QR Scanner library is not loaded. Please refresh the page and try again.');
            closeQRScanner();
            return;
        }

        const video = document.getElementById('qrVideo');
        
        // Request camera permission with specific constraints
        qrStream = await navigator.mediaDevices.getUserMedia({ 
            video: { 
                facingMode: qrFacingMode,
                width: { ideal: 1920 },
                height: { ideal: 1080 }
            } 
        });
        video.srcObject = qrStream;

        qrCodeReader = new ZXing.BrowserQRCodeReader();
        
        qrCodeReader.decodeFromVideoDevice(null, 'qrVideo', (result, err) => {
            if (result) {
                const qrValue = result.text;
                // Assuming QR contains vehicle ID or redirect URL
                if (qrValue.includes('/report-user/')) {
                    window.location.href = qrValue;
                } else {
                    // Assume it's a vehicle ID
                    window.location.href = `/report-user/${qrValue}`;
                }
                closeQRScanner();
            }
        });
    } catch (error) {
        console.error('Error starting QR scanner:', error);
        
        let errorMessage = 'Unable to access camera. ';
        
        if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') {
            errorMessage += 'Camera permission was denied. Please enable camera access in your browser settings and try again.';
        } else if (error.name === 'NotFoundError' || error.name === 'DevicesNotFoundError') {
            errorMessage += 'No camera found on your device.';
        } else if (error.name === 'NotReadableError' || error.name === 'TrackStartError') {
            errorMessage += 'Camera is already in use by another application.';
        } else if (error.name === 'OverconstrainedError' || error.name === 'ConstraintNotSatisfiedError') {
            errorMessage += 'Camera does not support the required settings.';
        } else if (error.name === 'NotSupportedError') {
            errorMessage += 'Camera access requires HTTPS. Please use a secure connection.';
        } else {
            errorMessage += error.message || 'Please check your browser settings and permissions.';
        }
        
        alert(errorMessage);
        closeQRScanner();
    }
}

async function flipQRCamera() {
    // Stop current stream
    if (qrStream) {
        qrStream.getTracks().forEach(track => track.stop());
    }
    if (qrCodeReader) {
        qrCodeReader.reset();
    }
    
    // Toggle facing mode
    qrFacingMode = qrFacingMode === 'environment' ? 'user' : 'environment';
    
    // Restart scanner with new facing mode
    await startQRScanner();
}

function openManualEntry() {
    document.getElementById('manualEntryModal').classList.remove('hidden');
}

function closeManualEntry() {
    document.getElementById('manualEntryModal').classList.add('hidden');
    document.getElementById('manualEntryForm').reset();
}

function searchVehicle() {
    const stickerNumber = document.getElementById('sticker_number').value;
    const stickerColor = document.getElementById('sticker_color').value;
    const plateNo = document.getElementById('plate_no').value;

    if (!plateNo && (!stickerNumber || !stickerColor)) {
        alert('Please enter either plate number OR both sticker number and color');
        return;
    }

    const formData = new FormData();
    if (plateNo) {
        formData.append('plate_no', plateNo);
    } else {
        formData.append('sticker_number', stickerNumber);
        formData.append('sticker_color', stickerColor);
    }

    fetch('{{ route('reporter.search-vehicle') }}', {
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
            window.location.href = data.redirect_url;
        } else {
            showErrorModal(data.message || 'Vehicle not found');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorModal('An error occurred while searching for the vehicle');
    });
}

function showErrorModal(message) {
    document.getElementById('errorMessage').textContent = message;
    document.getElementById('errorModal').classList.remove('hidden');
}

function closeErrorModal() {
    document.getElementById('errorModal').classList.add('hidden');
}

// Expose functions to global scope
window.openQRScanner = openQRScanner;
window.closeQRScanner = closeQRScanner;
window.flipQRCamera = flipQRCamera;
window.openManualEntry = openManualEntry;
window.closeManualEntry = closeManualEntry;
window.searchVehicle = searchVehicle;
window.showErrorModal = showErrorModal;
window.closeErrorModal = closeErrorModal;
</script>
@endpush
