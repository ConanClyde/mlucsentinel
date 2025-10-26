// Student Registration Form Handler
document.addEventListener('DOMContentLoaded', function() {
    const addVehicleBtn = document.getElementById('addVehicleBtn');
    const vehiclesContainer = document.getElementById('vehiclesContainer');
    let vehicleCount = 1; // Start with 1 since we have the default vehicle

    // Add Vehicle functionality
    if (addVehicleBtn) {
        addVehicleBtn.addEventListener('click', function() {
            if (vehicleCount >= 3) {
                alert('Maximum of 3 vehicles allowed per user');
                return;
            }

            vehicleCount++;
            
            const vehicleHtml = '<div class="vehicle-item bg-gray-50 dark:bg-[#161615] p-4 rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A]">' +
                '<div class="flex items-center justify-between mb-3">' +
                    '<h4 class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Vehicle ' + vehicleCount + '</h4>' +
                    '<button type="button" class="remove-vehicle-btn text-red-600 hover:text-red-700">' +
                        '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>' +
                    '</button>' +
                '</div>' +
                '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">' +
                    '<div class="form-group">' +
                        '<label class="form-label">Vehicle Type</label>' +
                        '<select name="vehicles[' + (vehicleCount - 1) + '][type]" class="form-input" required>' +
                            '<option value="">Select Vehicle Type</option>' +
                            '<option value="1">Motorcycle</option>' +
                            '<option value="2">Car</option>' +
                            '<option value="3">Electric Vehicle</option>' +
                        '</select>' +
                    '</div>' +
                    '<div class="form-group">' +
                        '<label class="form-label">Plate Number</label>' +
                        '<input name="vehicles[' + (vehicleCount - 1) + '][plate_no]" type="text" required class="form-input" placeholder="ABC-1234">' +
                    '</div>' +
                '</div>' +
            '</div>';
            
            vehiclesContainer.insertAdjacentHTML('beforeend', vehicleHtml);
            
            // Add event listener for the new vehicle
            addVehicleTypeListeners();
            
            // Update add button state
            if (vehicleCount >= 3) {
                addVehicleBtn.disabled = true;
                addVehicleBtn.textContent = 'Maximum Reached';
                addVehicleBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
            
            // Show remove buttons for all vehicles when there's more than 1
            if (vehicleCount > 1) {
                document.querySelectorAll('.remove-vehicle-btn').forEach(btn => {
                    btn.classList.remove('hidden');
                });
            }
        });
    }

    // Remove Vehicle functionality
    if (vehiclesContainer) {
        vehiclesContainer.addEventListener('click', function(e) {
            if (e.target.closest('.remove-vehicle-btn')) {
                const vehicleItem = e.target.closest('.vehicle-item');
                vehicleItem.remove();
                vehicleCount--;
                
                // Update vehicle numbers
                updateVehicleNumbers();
                
                // Update add button state
                if (vehicleCount < 3) {
                    addVehicleBtn.disabled = false;
                    addVehicleBtn.innerHTML = '<svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" /></svg>Add Vehicle';
                    addVehicleBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    addVehicleBtn.classList.add('flex', 'items-center');
                }
                
                // Hide remove buttons if only 1 vehicle left
                if (vehicleCount === 1) {
                    document.querySelectorAll('.remove-vehicle-btn').forEach(btn => {
                        btn.classList.add('hidden');
                    });
                }
            }
        });
    }

    // Initialize event listeners for existing vehicles
    addVehicleTypeListeners();
});

// Update vehicle numbers and form names
function updateVehicleNumbers() {
    const vehiclesContainer = document.getElementById('vehiclesContainer');
    if (!vehiclesContainer) return;
    
    const vehicleItems = vehiclesContainer.querySelectorAll('.vehicle-item');
    vehicleItems.forEach((item, index) => {
        // Update title
        const title = item.querySelector('h4');
        title.textContent = 'Vehicle ' + (index + 1);
        
        // Update form names
        const selects = item.querySelectorAll('select');
        const inputs = item.querySelectorAll('input');
        
        selects.forEach(select => {
            select.name = 'vehicles[' + index + '][type]';
        });
        
        inputs.forEach(input => {
            if (input.name.includes('plate_no')) {
                input.name = 'vehicles[' + index + '][plate_no]';
            }
        });
    });
    
    // Re-add event listeners after updating names
    addVehicleTypeListeners();
}

// Function to toggle plate number visibility based on vehicle type
function togglePlateNumberVisibility(vehicleItem) {
    const vehicleTypeSelect = vehicleItem.querySelector('select[name*="[type]"]');
    const plateNumberInput = vehicleItem.querySelector('input[name*="[plate_no]"]');
    const plateNumberGroup = plateNumberInput.closest('.form-group');
    
    if (vehicleTypeSelect.value === '3') { // Electric Vehicle
        plateNumberGroup.style.display = 'none';
        plateNumberInput.removeAttribute('required');
        plateNumberInput.value = ''; // Clear the value
    } else {
        plateNumberGroup.style.display = 'block';
        plateNumberInput.setAttribute('required', 'required');
    }
}

// Add event listeners for vehicle type changes
function addVehicleTypeListeners() {
    const vehiclesContainer = document.getElementById('vehiclesContainer');
    if (!vehiclesContainer) return;
    
    const vehicleItems = vehiclesContainer.querySelectorAll('.vehicle-item');
    vehicleItems.forEach(item => {
        const vehicleTypeSelect = item.querySelector('select[name*="[type]"]');
        if (vehicleTypeSelect) {
            // Remove existing listener to avoid duplicates
            vehicleTypeSelect.removeEventListener('change', handleVehicleTypeChange);
            // Add new listener
            vehicleTypeSelect.addEventListener('change', handleVehicleTypeChange);
        }
    });
}

// Handle vehicle type change
function handleVehicleTypeChange(e) {
    const vehicleItem = e.target.closest('.vehicle-item');
    togglePlateNumberVisibility(vehicleItem);
}

// License Image Functions
let licenseCameraStream = null;

// License file upload handling
window.handleLicenseFileUpload = function(event) {
    const file = event.target.files[0];
    if (file) {
        // Check file size (5MB limit)
        const maxSize = 5 * 1024 * 1024; // 5MB in bytes
        if (file.size > maxSize) {
            alert('File size must be less than 5MB. Your file is ' + (file.size / 1024 / 1024).toFixed(2) + 'MB');
            event.target.value = ''; // Clear the input
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewImage = document.getElementById('licensePreviewImage');
            const imagePreview = document.getElementById('licenseImagePreview');
            const uploadOptions = document.querySelector('.flex.gap-4.mb-6');
            
            previewImage.src = e.target.result;
            imagePreview.classList.remove('hidden');
            
            // Hide upload options when image is previewed
            if (uploadOptions) {
                uploadOptions.classList.add('hidden');
            }
        };
        reader.readAsDataURL(file);
    }
}

// Remove license preview
window.removeLicensePreview = function() {
    document.getElementById('licenseImagePreview').classList.add('hidden');
    document.getElementById('licenseFileInput').value = '';
    
    // Show upload options again
    const uploadOptions = document.querySelector('.flex.gap-4.mb-6');
    if (uploadOptions) {
        uploadOptions.classList.remove('hidden');
    }
}

// License Camera Modal Functions
window.openLicenseCameraModal = async function() {
    try {
        // Get camera with high resolution first
        licenseCameraStream = await navigator.mediaDevices.getUserMedia({ 
            video: { 
                facingMode: 'user',
                width: { ideal: 1920 },
                height: { ideal: 1080 }
            } 
        });
        const video = document.getElementById('licenseCameraVideo');
        video.srcObject = licenseCameraStream;
        
        // Wait for video to load and get actual dimensions before showing modal
        video.onloadedmetadata = function() {
            // Set video dimensions to match camera resolution
            video.style.width = '100%';
            video.style.height = 'auto';
            video.style.maxHeight = '80vh'; // Limit max height to prevent overflow
            
            // Only show modal when camera is ready
            document.getElementById('licenseCameraModal').classList.remove('hidden');
        };
    } catch (error) {
        alert('Error accessing camera: ' + error.message);
    }
}

window.closeLicenseCameraModal = function() {
    if (licenseCameraStream) {
        licenseCameraStream.getTracks().forEach(track => track.stop());
        licenseCameraStream = null;
    }
    document.getElementById('licenseCameraModal').classList.add('hidden');
}

window.captureLicensePhoto = function() {
    const video = document.getElementById('licenseCameraVideo');
    const canvas = document.getElementById('licenseCameraCanvas');
    const context = canvas.getContext('2d');
    
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    context.drawImage(video, 0, 0);
    
    // Convert canvas to data URL and show in preview
    const dataURL = canvas.toDataURL('image/jpeg', 0.8);
    
    const previewImage = document.getElementById('licensePreviewImage');
    const imagePreview = document.getElementById('licenseImagePreview');
    const uploadOptions = document.querySelector('.flex.gap-4.mb-6');
    
    previewImage.src = dataURL;
    imagePreview.classList.remove('hidden');
    
    // Hide upload options when image is previewed
    if (uploadOptions) {
        uploadOptions.classList.add('hidden');
    }
    
    closeLicenseCameraModal();
}
