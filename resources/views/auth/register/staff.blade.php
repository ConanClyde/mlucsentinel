@extends('layouts.guest')

@section('title', 'Staff Registration - MLUC Sentinel')

@section('content')
<div class="min-h-screen py-4 md:py-8 flex items-center justify-center">
    <div class="max-w-5xl w-full mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6 md:mb-8 text-center">
            <h1 class="text-xl md:text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Staff Registration</h1>
            <p class="text-sm md:text-base text-[#706f6c] dark:text-[#A1A09A]">Register as staff with vehicle information and account credentials</p>
        </div>

        <!-- Registration Form -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A]">
            <form id="staffRegistrationForm" method="POST" action="{{ route('register.post') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="user_type" value="staff">
                
                <!-- Step 1: Basic Information -->
                <div id="step-1" class="step-content p-4 md:p-6">
                    <h3 class="text-base md:text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4 md:mb-6">Basic Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                                First Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="first_name" name="first_name" class="form-input" placeholder="John" value="{{ old('first_name') }}" required>
                            <div id="first_name_error" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>
                        
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                                Last Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="last_name" name="last_name" class="form-input" placeholder="Doe" value="{{ old('last_name') }}" required>
                            <div id="last_name_error" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>
                    </div>
                    
                    <div class="mt-4 md:mt-6">
                        <label for="staff_id" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                            Staff ID <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="staff_id" name="staff_id" class="form-input" placeholder="123456" value="{{ old('staff_id') }}" required>
                        <div id="staff_id_error" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <div class="mt-4 md:mt-6">
                        <label for="email" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input type="email" id="email" name="email" class="form-input" placeholder="john.doe@gmail.com or john.doe@dmmmsu.edu.ph" value="{{ old('email') }}" required>
                        <div class="text-sm text-[#706f6c] dark:text-[#A1A09A] mt-1">Used for violation notifications</div>
                        <div id="email_error" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                    
                    <!-- Navigation Buttons -->
                    <div class="flex justify-end mt-6 md:mt-8">
                        <button type="button" id="step-1-next" class="btn btn-primary" disabled>
                            Next
                        </button>
                    </div>
                </div>

                <!-- Step 2: License Information -->
                <div id="step-2" class="step-content p-4 md:p-6 hidden">
                    <h3 class="text-base md:text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4 md:mb-6">License Information</h3>
                    
                    <div class="space-y-4 md:space-y-6">
                        <div>
                            <label for="license_no" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                                License Number <span class="text-gray-400 text-xs">(Optional)</span>
                            </label>
                            <input type="text" id="license_no" name="license_no" class="form-input" placeholder="A12-34-567890" value="{{ old('license_no') }}">
                            <div id="license_no_error" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                                License Image <span class="text-gray-400 text-xs">(Optional)</span>
                            </label>
                            <div class="flex gap-2">
                                <button type="button" class="btn btn-secondary" onclick="openLicenseCameraModal()">
                                    <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    Take Photo
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="document.getElementById('license_image').click()">
                                    <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    Upload File
                                </button>
                            </div>

                            <!-- Hidden File Input -->
                            <input type="file" id="license_image" name="license_image" accept="image/*" class="hidden" onchange="handleLicenseFileUpload(event)">

                            <!-- Image Preview -->
                            <div id="licenseImagePreview" class="hidden mt-4">
                                <div class="relative inline-block">
                                    <img id="licensePreviewImage" src="" alt="License Preview" class="w-full max-w-md rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A]">
                                    <button type="button" onclick="removeLicensePreview()" class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                            </div>
                            
                            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-2">Upload a clear photo of your driver's license (max 2MB)</p>
                            <div id="license_image_error" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>
                    </div>
                    
                    <!-- Navigation Buttons -->
                    <div class="flex justify-between mt-6 md:mt-8">
                        <button type="button" id="step-2-prev" class="btn btn-secondary">
                            Previous
                        </button>
                        <button type="button" id="step-2-next" class="btn btn-primary" disabled>
                            Next
                        </button>
                    </div>
                </div>

                <!-- Step 3: Vehicle Information -->
                <div id="step-3" class="step-content p-4 md:p-6 hidden">
                    <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-6">Vehicle Information</h3>
                    
                    <div class="flex items-center justify-between mb-4">
                        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Add vehicle information for the Staff</p>
                        <button type="button" id="addVehicleBtn" class="btn btn-secondary">
                            <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                            Add Vehicle
                        </button>
                    </div>
                    
                    <!-- Vehicle Container -->
                    <div id="vehiclesContainer" class="space-y-4">
                        <!-- Default Vehicle -->
                        <div class="vehicle-item bg-gray-50 dark:bg-[#161615] p-4 rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A]">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Vehicle 1</h4>
                                <button type="button" class="remove-vehicle-btn text-red-600 hover:text-red-700 hidden" onclick="removeVehicle(this)">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="form-group">
                                    <label class="form-label">Vehicle Type <span class="text-red-500">*</span></label>
                                    <select name="vehicles[0][type_id]" class="form-input" required>
                                        <option value="">Select Vehicle Type</option>
                                        @foreach($vehicleTypes as $type)
                                            <option value="{{ $type->id }}" data-requires-plate="{{ $type->requires_plate ? '1' : '0' }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="type_id_0_error text-red-500 text-sm mt-1 hidden"></div>
                                </div>
                                <div class="form-group plate-number-group">
                                    <label class="form-label">Plate Number <span class="text-red-500 plate-required-asterisk">*</span></label>
                                    <input 
                                        name="vehicles[0][plate_no]" 
                                        type="text" 
                                        required 
                                        class="form-input" 
                                        placeholder="ABC-1234"
                                    >
                                    <div class="plate_no_0_error text-red-500 text-sm mt-1 hidden"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-2">
                        Maximum of 3 vehicles allowed per Staff
                    </p>
                    
                    <!-- Navigation Buttons -->
                    <div class="flex justify-between mt-6 md:mt-8">
                        <button type="button" id="step-3-prev" class="btn btn-secondary">
                            Previous
                        </button>
                        <button type="button" id="step-3-next" class="btn btn-primary" disabled>
                            Next
                        </button>
                    </div>
                </div>
                
                <!-- Step 4: Account Information -->
                <div id="step-4" class="step-content p-4 md:p-6 hidden">
                    <h3 class="text-base md:text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4 md:mb-6">Account Information</h3>
                    <p class="text-xs md:text-sm text-[#706f6c] dark:text-[#A1A09A] mb-4 md:mb-6">Create login credentials for the staff</p>
                    
                    <div class="space-y-4 md:space-y-6">
                        <div>
                            <label for="password" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                                Password <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" id="password" name="password" class="form-input pr-10" placeholder="Enter password (minimum 8 characters)" required>
                                <button type="button" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200" onclick="togglePasswordVisibility('password')">
                                    <x-heroicon-c-eye id="password-eye-icon" class="w-5 h-5" />
                                    <x-heroicon-c-eye-slash id="password-eye-off-icon" class="w-5 h-5 hidden" />
                                </button>
                            </div>
                            <div class="text-sm text-[#706f6c] dark:text-[#A1A09A] mt-1">Minimum 8 characters</div>
                            <div id="password_error" class="text-red-500 text-sm mt-1" style="display: none;"></div>
                        </div>
                        
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                                Confirm Password <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-input pr-10" placeholder="Re-enter password" required>
                                <button type="button" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200" onclick="togglePasswordVisibility('password_confirmation')">
                                    <x-heroicon-c-eye id="password_confirmation-eye-icon" class="w-5 h-5" />
                                    <x-heroicon-c-eye-slash id="password_confirmation-eye-off-icon" class="w-5 h-5 hidden" />
                                </button>
                            </div>
                            <div id="password_confirmation_error" class="text-red-500 text-sm mt-1" style="display: none;"></div>
                        </div>
                    </div>
                    
                    <!-- Navigation Buttons -->
                    <div class="flex justify-between mt-6 md:mt-8">
                        <button type="button" id="step-4-prev" class="btn btn-secondary">
                            Previous
                        </button>
                        <button type="submit" id="submit-form" class="btn btn-primary" disabled>
                            Register
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Login Link -->
        <div class="mt-6 text-center">
            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                Already have an account? 
                <a href="{{ route('login') }}" class="text-[#1b1b18] dark:text-[#EDEDEC] font-medium hover:underline">
                    Sign in here
                </a>
            </p>
        </div>
        
        <!-- Back to Role Selection -->
        <div class="mt-4 text-center">
            <a href="{{ route('register') }}" class="text-sm text-[#706f6c] dark:text-[#A1A09A] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]">
                ← Back to role selection
            </a>
        </div>
    </div>
</div>

<!-- Registration Success Modal -->
<div id="registrationSuccessModal" class="modal-backdrop hidden">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title text-green-600 dark:text-green-400 flex items-center gap-3">
                <x-heroicon-o-check-circle class="modal-icon-success" />
                Registration Submitted
            </h2>
        </div>
        <div class="modal-body">
            <p>Your staff registration has been submitted successfully! An administrator will review your application and notify you via email once it has been approved.</p>
            <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                <p class="text-sm text-blue-700 dark:text-blue-300">
                    <strong>What happens next?</strong><br>
                    • Admin will verify your information<br>
                    • You'll receive an email notification<br>
                    • Once approved, you can log in to your account
                </p>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-success" onclick="closeRegistrationSuccessModal()">Okay</button>
        </div>
    </div>
</div>

<script>
// Global variables
let currentStep = 1;
let vehicleCount = 1;
let availabilityErrors = {
    email: false,
    staffId: false,
    plateNos: {}
};

// Global functions
function showStep(step) {
    // Hide all steps
    for (let i = 1; i <= 4; i++) {
        document.getElementById(`step-${i}`).classList.add('hidden');
    }
    
    // Show current step
    document.getElementById(`step-${step}`).classList.remove('hidden');
    
    currentStep = step;
    
    // Update button states after step change (matching student form)
    updateButtonStates();
}

function updateButtonStates() {
    const nextButton = document.getElementById('step-1-next');
    const nextButton2 = document.getElementById('step-2-next');
    const nextButton3 = document.getElementById('step-3-next');
    const submitButton = document.getElementById('submit-form');
    
    if (currentStep === 1) {
        if (nextButton) nextButton.disabled = !isStep1Valid();
    } else if (currentStep === 2) {
        if (nextButton2) nextButton2.disabled = !isStep2Valid();
    } else if (currentStep === 3) {
        if (nextButton3) nextButton3.disabled = !isStep3Valid();
    } else if (currentStep === 4) {
        if (submitButton) submitButton.disabled = !isStep4Valid();
    }
}

// Validation functions
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@(gmail\.com|dmmmsu\.edu\.ph)$/;
    return emailRegex.test(email);
}

function isValidStaffId(staffId) {
    return staffId && staffId.trim().length >= 6;
}

function validatePlateNumber(plateNumber) {
    const plateRegex = /^[A-Z]{2,3}-[0-9]{3,4}$/;
    return plateRegex.test(plateNumber);
}

function isStep1Valid() {
    const firstName = document.getElementById('first_name').value.trim();
    const lastName = document.getElementById('last_name').value.trim();
    const staffId = document.getElementById('staff_id').value.trim();
    const email = document.getElementById('email').value.trim();
    
    const hasNoErrors = !availabilityErrors.email && !availabilityErrors.staffId;
    
    return firstName && lastName && staffId && email && isValidEmail(email) && isValidStaffId(staffId) && hasNoErrors;
}

function isStep2Valid() {
    return true; // License information is optional
}

function isStep3Valid() {
    const vehicleItems = document.querySelectorAll('.vehicle-item');
    
    for (let i = 0; i < vehicleItems.length; i++) {
        const typeSelect = vehicleItems[i].querySelector('select[name*="[type_id]"]');
        const plateInput = vehicleItems[i].querySelector('input[name*="[plate_no]"]');
        
        if (!typeSelect || !typeSelect.value) {
            return false;
        }
        
        const requiresPlate = typeSelect.options[typeSelect.selectedIndex]?.getAttribute('data-requires-plate') === '1';
        
        if (requiresPlate) {
            const plateNumber = plateInput ? plateInput.value.trim() : '';
            if (!plateNumber || !validatePlateNumber(plateNumber)) {
                return false;
            }
            
            if (availabilityErrors.plateNos[i]) {
                return false;
            }
        }
    }
    
    return true;
}

function isStep4Valid() {
    const password = document.getElementById('password').value;
    const passwordConfirmation = document.getElementById('password_confirmation').value;
    
    return password && password.length >= 8 && password === passwordConfirmation;
}

// AJAX functions
function checkEmailAvailability(email) {
    if (!email || !isValidEmail(email)) return;
    
    fetch('/check-email-availability', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ email: email })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.available) {
            showFieldError('email', data.message);
            availabilityErrors.email = true;
        } else {
            clearFieldError('email');
            availabilityErrors.email = false;
        }
        updateButtonStates();
    })
    .catch(error => {
        console.error('Email check error:', error);
        clearFieldError('email');
        availabilityErrors.email = false;
        updateButtonStates();
    });
}

function checkStaffIdAvailability(staffId) {
    if (!staffId || !isValidStaffId(staffId)) return;
    
    fetch('/check-staff-id-availability', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ staff_id: staffId })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.available) {
            showFieldError('staff_id', data.message);
            availabilityErrors.staffId = true;
        } else {
            clearFieldError('staff_id');
            availabilityErrors.staffId = false;
        }
        updateButtonStates();
    })
    .catch(error => {
        console.error('Staff ID check error:', error);
        clearFieldError('staff_id');
        availabilityErrors.staffId = false;
        updateButtonStates();
    });
}

function checkPlateNumberAvailability(plateNo, vehicleIndex) {
    if (!plateNo || !validatePlateNumber(plateNo)) return;
    
    fetch('/check-plate-no-availability', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ plate_no: plateNo })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.available) {
            showFieldError(`plate_no_${vehicleIndex}`, data.message);
            availabilityErrors.plateNos[vehicleIndex] = true;
        } else {
            clearFieldError(`plate_no_${vehicleIndex}`);
            availabilityErrors.plateNos[vehicleIndex] = false;
        }
        updateButtonStates();
    })
    .catch(error => {
        console.error('Plate number check error:', error);
        clearFieldError(`plate_no_${vehicleIndex}`);
        availabilityErrors.plateNos[vehicleIndex] = false;
        updateButtonStates();
    });
}

// Vehicle management functions
function addVehicle() {
    if (vehicleCount >= 3) {
        alert('Maximum of 3 vehicles allowed per staff member.');
        return;
    }
    
    const vehiclesContainer = document.getElementById('vehiclesContainer');
    const vehicleHtml = `
        <div class="vehicle-item bg-gray-50 dark:bg-[#161615] p-4 rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A]">
            <div class="flex items-center justify-between mb-3">
                <h4 class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Vehicle ${vehicleCount + 1}</h4>
                <button type="button" class="remove-vehicle-btn text-red-600 hover:text-red-700" onclick="removeVehicle(this)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Vehicle Type <span class="text-red-500">*</span></label>
                    <select name="vehicles[${vehicleCount}][type_id]" class="form-input" required>
                        <option value="">Select Vehicle Type</option>
                        @foreach($vehicleTypes as $type)
                            <option value="{{ $type->id }}" data-requires-plate="{{ $type->requires_plate ? '1' : '0' }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                    <div class="type_id_${vehicleCount}_error text-red-500 text-sm mt-1 hidden"></div>
                </div>
                <div class="form-group plate-number-group">
                    <label class="form-label">Plate Number <span class="text-red-500 plate-required-asterisk">*</span></label>
                    <input 
                        name="vehicles[${vehicleCount}][plate_no]" 
                        type="text" 
                        required 
                        class="form-input" 
                        placeholder="ABC-1234"
                    >
                    <div class="plate_no_${vehicleCount}_error text-red-500 text-sm mt-1 hidden"></div>
                </div>
            </div>
        </div>
    `;
    
    vehiclesContainer.insertAdjacentHTML('beforeend', vehicleHtml);
    vehicleCount++;
    
    // Update add button state
    const addVehicleBtn = document.getElementById('addVehicleBtn');
    if (vehicleCount >= 3) {
        addVehicleBtn.disabled = true;
        addVehicleBtn.textContent = 'Maximum Reached';
        addVehicleBtn.classList.add('opacity-50', 'cursor-not-allowed');
    }
    
    // Update vehicle numbers and event listeners for all vehicles
    updateVehicleNumbers();
    
    // Update remove button visibility
    updateVehicleRemoveButtons();
}

function updateVehicleRemoveButtons() {
    const removeButtons = document.querySelectorAll('.remove-vehicle-btn');
    removeButtons.forEach(btn => {
        btn.classList.toggle('hidden', vehicleCount <= 1);
    });
}

function removeVehicle(button) {
    const vehicleItem = button.closest('.vehicle-item');
    const vehicleIndex = Array.from(document.querySelectorAll('.vehicle-item')).indexOf(vehicleItem);
    
    if (vehicleCount <= 1) {
        alert('At least one vehicle is required.');
        return;
    }
    
    // Remove availability error for this vehicle
    delete availabilityErrors.plateNos[vehicleIndex];
    
    vehicleItem.remove();
    vehicleCount--;
    
    // Update vehicle numbers
    updateVehicleNumbers();
    
    // Update add button state
    const addVehicleBtn = document.getElementById('addVehicleBtn');
    if (vehicleCount < 3) {
        addVehicleBtn.disabled = false;
        addVehicleBtn.innerHTML = '<svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" /></svg>Add Vehicle';
        addVehicleBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    }
    
    // Hide remove buttons if only 1 vehicle left
    if (vehicleCount === 1) {
        document.querySelectorAll('.remove-vehicle-btn').forEach(btn => {
            btn.classList.add('hidden');
        });
    }
        
    updateButtonStates();
}

function updateVehicleNumbers() {
    const vehicleItems = document.querySelectorAll('.vehicle-item');
    
    vehicleItems.forEach((item, index) => {
        // Update vehicle title
        const title = item.querySelector('h4');
        if (title) {
            title.textContent = `Vehicle ${index + 1}`;
        }
        
        // Update form field names
        const typeSelect = item.querySelector('select[name*="[type_id]"]');
        if (typeSelect) {
            typeSelect.name = `vehicles[${index}][type_id]`;
        }
        
        const plateInput = item.querySelector('input[name*="[plate_no]"]');
        if (plateInput) {
            plateInput.name = `vehicles[${index}][plate_no]`;
        }
        
        // Update error element classes
        const errorElement = item.querySelector('[class*="_error"]');
        if (errorElement) {
            const oldClass = errorElement.className.match(/\w+_\d+_error/);
            if (oldClass) {
                errorElement.className = errorElement.className.replace(oldClass[0], `plate_no_${index}_error`);
            }
        }
    });
    
    // Re-add event listeners after updating names
    vehicleItems.forEach((item, index) => {
        const vehicleTypeSelect = item.querySelector('select[name*="[type_id]"]');
        if (vehicleTypeSelect) {
            vehicleTypeSelect.removeEventListener('change', handleVehicleTypeChange);
            vehicleTypeSelect.addEventListener('change', handleVehicleTypeChange);
        }
        
        const plateNumberInput = item.querySelector('input[name*="[plate_no]"]');
        if (plateNumberInput) {
            // Remove old listener and add new one with correct index
            plateNumberInput.replaceWith(plateNumberInput.cloneNode(true));
            const newPlateNumberInput = item.querySelector('input[name*="[plate_no]"]');
            
            newPlateNumberInput.addEventListener('input', function() {
                const plateNumber = this.value.trim();
                
                // Clear the error when user types
                delete availabilityErrors.plateNos[index];
                const errorElement = document.querySelector(`.plate_no_${index}_error`);
                if (errorElement) {
                    errorElement.classList.add('hidden');
                    errorElement.textContent = '';
                }
                updateButtonStates();
                
                if (plateNumber && !validatePlateNumber(plateNumber)) {
                    showFieldError(`plate_no_${index}`, 'Invalid plate number format. Use format: ABC-1234');
                    return;
                }
                
                if (plateNumber && validatePlateNumber(plateNumber)) {
                    clearFieldError(`plate_no_${index}`);
                    checkPlateNumberAvailability(plateNumber, index);
                }
            });
        }
    });
}

function handleVehicleTypeChange(event) {
    const select = event.target;
    const vehicleItem = select.closest('.vehicle-item');
    const plateNumberGroup = vehicleItem.querySelector('.plate-number-group');
    const plateNumberInput = plateNumberGroup.querySelector('input[name*="[plate_no]"]');
    const plateRequiredAsterisk = plateNumberGroup.querySelector('.plate-required-asterisk');
    
    const selectedOption = select.options[select.selectedIndex];
    const requiresPlate = selectedOption && selectedOption.getAttribute('data-requires-plate') === '1';
    
    if (requiresPlate) {
        // Show plate number input and make it required
        plateNumberGroup.style.display = 'block';
        plateNumberInput.required = true;
        if (plateRequiredAsterisk) {
            plateRequiredAsterisk.style.display = 'inline';
        }
    } else {
        // Hide plate number input and make it optional
        plateNumberGroup.style.display = 'none';
        plateNumberInput.required = false;
        plateNumberInput.value = '';
        if (plateRequiredAsterisk) {
            plateRequiredAsterisk.style.display = 'none';
        }
        
        // Clear any plate number errors for this vehicle
        const vehicleIndex = Array.from(document.querySelectorAll('.vehicle-item')).indexOf(vehicleItem);
        delete availabilityErrors.plateNos[vehicleIndex];
        clearFieldError(`plate_no_${vehicleIndex}`);
    }
    
    updateButtonStates();
}

// Helper functions
function showFieldError(fieldName, message) {
    const errorElement = document.querySelector(`.${fieldName}_error`) || document.getElementById(`${fieldName}_error`);
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.classList.remove('hidden');
    }
}

function clearFieldError(fieldName) {
    const errorElement = document.querySelector(`.${fieldName}_error`) || document.getElementById(`${fieldName}_error`);
    if (errorElement) {
        errorElement.textContent = '';
        errorElement.classList.add('hidden');
    }
}

// License image functions
function handleLicenseFileUpload(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('licensePreviewImage').src = e.target.result;
            document.getElementById('licenseImagePreview').classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }
}

function removeLicensePreview() {
    document.getElementById('license_image').value = '';
    document.getElementById('licenseImagePreview').classList.add('hidden');
}

// Registration Success Modal Functions
function showRegistrationSuccessModal() {
    document.getElementById('registrationSuccessModal').classList.remove('hidden');
}

function closeRegistrationSuccessModal() {
    document.getElementById('registrationSuccessModal').classList.add('hidden');
    // Redirect to landing page
    window.location.href = '{{ route('landing') }}';
}

// DOM Content Loaded
document.addEventListener('DOMContentLoaded', function() {
    const vehiclesContainer = document.getElementById('vehiclesContainer');
    
    // Step Navigation
    document.getElementById('step-1-next').addEventListener('click', function() {
        showStep(2);
    });
    
    document.getElementById('step-2-prev').addEventListener('click', function() {
        showStep(1);
    });
    
    document.getElementById('step-2-next').addEventListener('click', function() {
        showStep(3);
    });
    
    document.getElementById('step-3-prev').addEventListener('click', function() {
        showStep(2);
    });
    
    document.getElementById('step-3-next').addEventListener('click', function() {
        showStep(4);
    });
    
    document.getElementById('step-4-prev').addEventListener('click', function() {
        showStep(3);
    });
    
    // Add Vehicle Button
    document.getElementById('addVehicleBtn').addEventListener('click', function() {
        if (vehicleCount >= 3) {
            alert('Maximum of 3 vehicles allowed per staff member.');
            return;
        }
        addVehicle();
    });
    
    // Attach event listeners for existing vehicle type selects
    document.querySelectorAll('select[name*="[type_id]"]').forEach(select => {
        select.addEventListener('change', handleVehicleTypeChange);
    });
    
    // Individual validation functions
    function validateFirstName() {
        const firstName = document.getElementById('first_name').value.trim();
        
        if (!firstName) {
            showFieldError('first_name', 'First name is required');
            return false;
        } else {
            clearFieldError('first_name');
            return true;
        }
    }

    function validateLastName() {
        const lastName = document.getElementById('last_name').value.trim();
        
        if (!lastName) {
            showFieldError('last_name', 'Last name is required');
            return false;
        } else {
            clearFieldError('last_name');
            return true;
        }
    }

    function validateStaffId() {
        const staffId = document.getElementById('staff_id').value.trim();
        
        if (!staffId) {
            showFieldError('staff_id', 'Staff ID is required');
            return false;
        } else if (staffId.length < 6) {
            showFieldError('staff_id', 'Staff ID must contain at least 6 digits');
            return false;
        } else {
            // Check Staff ID availability
            checkStaffIdAvailability(staffId);
            clearFieldError('staff_id');
            return true;
        }
    }

    function validateEmail() {
        const email = document.getElementById('email').value.trim();
        
        if (!email) {
            showFieldError('email', 'Email is required');
            return false;
        } else if (!isValidEmail(email)) {
            showFieldError('email', 'Please enter a valid email from Gmail (@gmail.com) or Staff DMMMSU (@dmmmsu.edu.ph)');
            return false;
        } else {
            // Check email availability
            checkEmailAvailability(email);
            clearFieldError('email');
            return true;
        }
    }

    function validateLicenseNo() {
        const licenseNo = document.getElementById('license_no').value.trim();
        
        if (licenseNo && licenseNo.length < 8) {
            showFieldError('license_no', 'License number must be at least 8 characters');
            return false;
        } else {
            clearFieldError('license_no');
            return true;
        }
    }

    // Form validation event listeners with real-time validation
    const firstNameInput = document.getElementById('first_name');
    if (firstNameInput) {
        let firstNameTimeout;
        firstNameInput.addEventListener('input', function() {
            clearTimeout(firstNameTimeout);
            firstNameTimeout = setTimeout(validateFirstName, 500);
            updateButtonStates();
        });
    }
    
    const lastNameInput = document.getElementById('last_name');
    if (lastNameInput) {
        let lastNameTimeout;
        lastNameInput.addEventListener('input', function() {
            clearTimeout(lastNameTimeout);
            lastNameTimeout = setTimeout(validateLastName, 500);
            updateButtonStates();
        });
    }
    
    const staffIdInput = document.getElementById('staff_id');
    if (staffIdInput) {
        let staffIdTimeout;
        staffIdInput.addEventListener('input', function() {
            clearTimeout(staffIdTimeout);
            staffIdTimeout = setTimeout(validateStaffId, 500);
            updateButtonStates();
        });
    }
    
    const emailInput = document.getElementById('email');
    if (emailInput) {
        let emailTimeout;
        emailInput.addEventListener('input', function() {
            clearTimeout(emailTimeout);
            emailTimeout = setTimeout(validateEmail, 500);
            updateButtonStates();
        });
    }
    
    const licenseNoInput = document.getElementById('license_no');
    if (licenseNoInput) {
        let licenseNoTimeout;
        licenseNoInput.addEventListener('input', function() {
            clearTimeout(licenseNoTimeout);
            licenseNoTimeout = setTimeout(validateLicenseNo, 500);
            updateButtonStates();
        });
    }
    
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            updateButtonStates();
        });
    }
    
    const passwordConfirmationInput = document.getElementById('password_confirmation');
    if (passwordConfirmationInput) {
        passwordConfirmationInput.addEventListener('input', function() {
            updateButtonStates();
        });
    }
    
    // License image upload
    const licenseImageInput = document.getElementById('license_image');
    if (licenseImageInput) {
        licenseImageInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('licensePreviewImage').src = e.target.result;
                    document.getElementById('licensePreview').classList.remove('hidden');
                    document.getElementById('licenseUploadText').classList.add('hidden');
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Form submission
    document.getElementById('staffRegistrationForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitButton = this.querySelector('button[type="submit"]');
        const originalText = submitButton.textContent;
        
        // Disable submit button and show loading
        submitButton.disabled = true;
        submitButton.textContent = 'Submitting...';
        
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success modal
                showRegistrationSuccessModal();
            } else {
                // Handle validation errors
                if (data.errors) {
                    alert('Please check your form for errors.');
                } else {
                    alert('An error occurred. Please try again.');
                }
                // Re-enable submit button
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
            // Re-enable submit button
            submitButton.disabled = false;
            submitButton.textContent = originalText;
        });
    });
    
    // Initialize
    showStep(1);
    updateButtonStates();
    updateVehicleRemoveButtons();
    updateVehicleNumbers();
    
    // Camera modal event listener
    const cameraModal = document.getElementById('licenseCameraModal');
    if (cameraModal) {
        cameraModal.addEventListener('click', function(e) {
            if (e.target === this) closeLicenseCameraModal();
        });
    }
});

// Global functions for camera and file handling
function togglePasswordVisibility(fieldId) {
    const field = document.getElementById(fieldId);
    const eyeIcon = document.getElementById(fieldId + '-eye-icon');
    const eyeOffIcon = document.getElementById(fieldId + '-eye-off-icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        eyeIcon.classList.add('hidden');
        eyeOffIcon.classList.remove('hidden');
    } else {
        field.type = 'password';
        eyeIcon.classList.remove('hidden');
        eyeOffIcon.classList.add('hidden');
    }
}

// License Camera Modal Functions
let licenseCameraStream = null;

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
    
    // Convert canvas to blob
    canvas.toBlob(function(blob) {
        if (!blob) return;
        
        // Convert blob to File object
        const file = new File([blob], 'license-photo.jpg', { type: 'image/jpeg' });
        
        // Create a new FileList with the captured image
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        
        // Assign the FileList to the input
        const licenseInput = document.getElementById('license_image');
        licenseInput.files = dataTransfer.files;
        
        // Convert canvas to data URL and show in preview
        const dataURL = canvas.toDataURL('image/jpeg', 0.8);
        
        const previewImage = document.getElementById('licensePreviewImage');
        const imagePreview = document.getElementById('licenseImagePreview');
        
        previewImage.src = dataURL;
        imagePreview.classList.remove('hidden');
        
    }, 'image/jpeg', 0.8);
    
    closeLicenseCameraModal();
}
</script>

<!-- Camera Modal -->
<div id="licenseCameraModal" class="modal-backdrop hidden">
    <div class="camera-container max-w-4xl">
        <div class="modal-header flex justify-between items-center">
            <h2 class="modal-title">Camera</h2>
            <button onclick="closeLicenseCameraModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <div class="modal-body p-0">
            <video id="licenseCameraVideo" autoplay playsinline class="w-full h-auto bg-black max-h-[70vh] sm:max-h-[80vh] object-cover"></video>
            <canvas id="licenseCameraCanvas" class="hidden"></canvas>
        </div>
        <div class="modal-footer">
            <button class="btn-camera" onclick="captureLicensePhoto()">
                <svg class="w-6 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            </button>
        </div>
    </div>
</div>
@endsection

