@extends('layouts.guest')

@section('title', 'Stakeholder Registration - MLUC Sentinel')

@section('content')
<div class="min-h-screen py-4 md:py-8 flex items-center justify-center">
    <div class="max-w-5xl w-full mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6 md:mb-8 text-center">
            <h1 class="text-xl md:text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Stakeholder Registration</h1>
            <p class="text-sm md:text-base text-[#706f6c] dark:text-[#A1A09A]">Register as stakeholder with vehicle information and account credentials</p>
        </div>

        <!-- Registration Form -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A]">
            <form id="stakeholderRegistrationForm" method="POST" action="{{ route('register.post') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="user_type" value="stakeholder">
                
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
                        <label for="stakeholder_type_id" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                            Stakeholder Type <span class="text-red-500">*</span>
                        </label>
                        <select id="stakeholder_type_id" name="stakeholder_type_id" class="form-input" required>
                            <option value="">Select Stakeholder Type</option>
                            @foreach($stakeholderTypes as $type)
                                <option value="{{ $type->id }}" data-evidence-required="{{ $type->evidence_required ? 'true' : 'false' }}" {{ old('stakeholder_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                            @endforeach
                        </select>
                        <div id="stakeholder_type_id_error" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <div class="mt-4 md:mt-6">
                        <label for="email" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input type="email" id="email" name="email" class="form-input" placeholder="john.doe@gmail.com or john.doe@dmmmsu.edu.ph" value="{{ old('email') }}" required>
                        <div class="text-sm text-[#706f6c] dark:text-[#A1A09A] mt-1">Used for violation notifications</div>
                        <div id="email_error" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                    
                    <!-- Guardian Evidence Upload (Conditional) -->
                    <div id="guardian_evidence_section" class="mt-4 md:mt-6 hidden">
                        <div>
                            <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                                Guardian Evidence <span class="text-red-500">*</span>
                            </label>
                            <div class="flex gap-2">
                                <button type="button" class="btn btn-secondary" onclick="openGuardianCameraModal()">
                                    <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    Take Photo
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="document.getElementById('guardian_evidence').click()">
                                    <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    Upload File
                                </button>
                            </div>

                            <!-- Hidden File Input -->
                            <input type="file" id="guardian_evidence" name="guardian_evidence" accept="image/*" class="hidden" onchange="handleGuardianFileUpload(event)">

                            <!-- Image Preview -->
                            <div id="guardianEvidencePreview" class="hidden mt-4">
                                <div class="relative inline-block">
                                    <div id="guardianPreviewContent">
                                        <!-- Content will be populated dynamically -->
                                    </div>
                                    <button type="button" onclick="removeGuardianPreview()" class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                            </div>
                            
                            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-2">Upload a clear photo of your children's university ID or enrollment documents (max 10MB)</p>
                            <div id="guardian_evidence_error" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>
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
                        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Add vehicle information for the Stakeholder</p>
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
                        Maximum of 3 vehicles allowed per Stakeholder
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
                    <p class="text-xs md:text-sm text-[#706f6c] dark:text-[#A1A09A] mb-4 md:mb-6">Create login credentials for the stakeholder</p>
                    
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

<!-- Guardian Camera Modal -->
<div id="guardianCameraModal" class="modal-backdrop hidden">
    <div class="camera-container max-w-4xl">
        <div class="modal-header flex justify-between items-center">
            <h2 class="modal-title">Camera - Guardian Evidence</h2>
            <button onclick="closeGuardianCameraModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <div class="modal-body p-0">
            <video id="guardianCameraVideo" autoplay playsinline class="w-full h-auto bg-black max-h-[70vh] sm:max-h-[80vh] object-cover"></video>
            <canvas id="guardianCameraCanvas" class="hidden"></canvas>
        </div>
        <div class="modal-footer">
            <button class="btn-camera" onclick="captureGuardianPhoto()">
                <svg class="w-6 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            </button>
        </div>
    </div>
</div>

<script>
// Global variables (matching admin form exactly)
let currentStep = 1;
const totalSteps = 4;
let vehicleCount = 1;

// Track validation errors (global scope like admin form)
const availabilityErrors = {
    email: false,
    plateNos: {}
};

// Timeout variables for debounced validation
let firstNameTimeout, lastNameTimeout, typeIdTimeout, emailTimeout;

// Function to show step (global scope like admin form)
function showStep(step) {
    // Hide all steps
    for (let i = 1; i <= totalSteps; i++) {
        document.getElementById(`step-${i}`).classList.add('hidden');
    }
    
    // Show current step
    document.getElementById(`step-${step}`).classList.remove('hidden');
    
    currentStep = step;
    
    // Update button states after step change (matching admin form)
    updateButtonStates();
}

// Function to update button states (global scope like admin form)
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

// Email validation function (global scope like admin form) - Stakeholder email validation
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@(gmail\.com|dmmmsu\.edu\.ph)$/;
    return emailRegex.test(email);
}

// Function to check if all step 1 fields are valid (global scope like admin form)
function isStep1Valid() {
    const firstName = document.getElementById('first_name').value.trim();
    const lastName = document.getElementById('last_name').value.trim();
    const typeId = document.getElementById('stakeholder_type_id').value;
    const email = document.getElementById('email').value.trim();
    
    const hasNoErrors = !availabilityErrors.email;
    
    return firstName && lastName && typeId && email && isValidEmail(email) && hasNoErrors;
}

// Function to check if all step 2 fields are valid (global scope like admin form)
function isStep2Valid() {
    // License information is optional
    return true;
}

// Function to check if all step 3 fields are valid (global scope like admin form)
function isStep3Valid() {
    const vehicles = document.querySelectorAll('.vehicle-item');
    let isValid = true;
    
    vehicles.forEach((vehicle, index) => {
        const typeSelect = vehicle.querySelector('select[name*="[type_id]"]');
        const plateInput = vehicle.querySelector('input[name*="[plate_no]"]');
        
        if (!typeSelect.value) {
            isValid = false;
        }
        
        // Only require plate number if vehicle type requires it
        const requiresPlate = typeSelect.options[typeSelect.selectedIndex]?.getAttribute('data-requires-plate') === '1';
        if (requiresPlate && !plateInput.value.trim()) {
            isValid = false;
        }
        
        // Validate plate number format if it has a value
        if (plateInput.value.trim() && !validatePlateNumber(plateInput.value.trim())) {
            isValid = false;
        }
        
        // Check for plate number availability errors
        if (availabilityErrors.plateNos[index]) {
            isValid = false;
        }
    });
    
    return isValid && vehicles.length > 0;
}

// Function to check if all step 4 fields are valid (global scope like admin form)
function isStep4Valid() {
    const password = document.getElementById('password').value.trim();
    const passwordConfirmation = document.getElementById('password_confirmation').value.trim();
    
    // Password must be at least 8 characters
    if (password.length < 8) {
        return false;
    }
    
    // Passwords must match
    if (password !== passwordConfirmation) {
        return false;
    }
    
    return true;
}

// Validate plate number format (global scope like admin form)
function validatePlateNumber(plateNumber) {
    const plateRegex = /^[A-Z]{2,3}-[0-9]{3,4}$/;
    return plateRegex.test(plateNumber);
}

document.addEventListener('DOMContentLoaded', function() {
    
    // Stakeholder type change handler for guardian evidence
    document.getElementById('stakeholder_type_id').addEventListener('change', function() {
        updateGuardianEvidenceVisibility();
    });
    
    // Guardian evidence file upload handler
    document.getElementById('guardian_evidence').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            handleGuardianFileUpload(e);
        }
    });
    
    // Step 1 Navigation
    document.getElementById('step-1-next').addEventListener('click', function() {
        if (isStep1Valid()) {
            showStep(2);
        }
    });
    
    // Step 2 Navigation
    document.getElementById('step-2-prev').addEventListener('click', function() {
        showStep(1);
    });
    
    document.getElementById('step-2-next').addEventListener('click', function() {
        if (isStep2Valid()) {
            showStep(3);
        }
    });
    
    // Step 3 Navigation
    document.getElementById('step-3-prev').addEventListener('click', function() {
        showStep(2);
    });
    
    document.getElementById('step-3-next').addEventListener('click', function() {
        if (isStep3Valid()) {
            showStep(4);
        }
    });
    
    // Step 4 Navigation
    document.getElementById('step-4-prev').addEventListener('click', function() {
        showStep(3);
    });
    
    // Vehicle Management
    document.getElementById('addVehicleBtn').addEventListener('click', function() {
        if (vehicleCount >= 3) {
            alert('Maximum of 3 vehicles allowed per stakeholder');
            return;
        }
        addVehicle();
    });
    
    // Initialize vehicle type change handlers for existing vehicles
    document.querySelectorAll('select[name*="[type_id]"]').forEach(select => {
        select.addEventListener('change', handleVehicleTypeChange);
    });
    
    function addVehicle() {
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
        
        // Update add button state (matching admin form exactly)
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
    
    // Helper functions for error display
    function showFieldError(fieldName, message) {
        const errorElement = document.getElementById(`${fieldName}_error`);
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.classList.remove('hidden');
        }
    }
    
    function clearFieldError(fieldName) {
        const errorElement = document.getElementById(`${fieldName}_error`);
        if (errorElement) {
            errorElement.textContent = '';
            errorElement.classList.add('hidden');
        }
    }
    
    // Individual validation functions (matching admin form exactly)
    function validateFirstName() {
        const firstName = firstNameInput.value.trim();
        
        if (!firstName) {
            showFieldError('first_name', 'First name is required');
            return false;
        } else {
            clearFieldError('first_name');
            return true;
        }
    }

    function validateLastName() {
        const lastName = lastNameInput.value.trim();
        
        if (!lastName) {
            showFieldError('last_name', 'Last name is required');
            return false;
        } else {
            clearFieldError('last_name');
            return true;
        }
    }

    function validateTypeId() {
        const typeId = typeIdInput.value;
        
        if (!typeId) {
            showFieldError('stakeholder_type_id', 'Stakeholder Type is required');
            return false;
        } else {
            clearFieldError('stakeholder_type_id');
            return true;
        }
    }

    function validateEmail() {
        const email = emailInput.value.trim();
        
        if (!email) {
            showFieldError('email', 'Email is required');
            return false;
        } else if (!isValidEmail(email)) {
            showFieldError('email', 'Please enter a valid email from Gmail (@gmail.com) or Stakeholder DMMMSU (@dmmmsu.edu.ph)');
            return false;
        } else {
            clearFieldError('email');
            // Check email availability
            checkEmailAvailability(email);
            return true;
        }
    }
    
    // Availability checking functions
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
    
    // Get input elements (matching admin form)
    const firstNameInput = document.getElementById('first_name');
    const lastNameInput = document.getElementById('last_name');
    const typeIdInput = document.getElementById('stakeholder_type_id');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const passwordConfirmationInput = document.getElementById('password_confirmation');
    
    if (firstNameInput) {
        firstNameInput.addEventListener('input', function() {
            clearTimeout(firstNameTimeout);
            firstNameTimeout = setTimeout(validateFirstName, 1000);
            updateButtonStates();
        });
    }

    if (lastNameInput) {
        lastNameInput.addEventListener('input', function() {
            clearTimeout(lastNameTimeout);
            lastNameTimeout = setTimeout(validateLastName, 1000);
            updateButtonStates();
        });
    }

    if (typeIdInput) {
        typeIdInput.addEventListener('change', function() {
            validateTypeId();
            updateButtonStates();
        });
    }

    if (emailInput) {
        emailInput.addEventListener('input', function() {
            clearTimeout(emailTimeout);
            emailTimeout = setTimeout(validateEmail, 1000);
            updateButtonStates();
        });
        
        emailInput.addEventListener('blur', function() {
            validateEmail();
        });
    }
    
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            updateButtonStates();
        });
    }
    
    if (passwordConfirmationInput) {
        passwordConfirmationInput.addEventListener('input', function() {
            updateButtonStates();
        });
    }
    
    // Initialize
    showStep(1);
    updateButtonStates();
    updateVehicleRemoveButtons();
    updateVehicleNumbers(); // Ensure all vehicles have proper event listeners
    
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

function removeVehicle(button) {
    const vehicleItem = button.closest('.vehicle-item');
    // Get the vehicle index before removing
    const vehicleIndex = Array.from(document.querySelectorAll('.vehicle-item')).indexOf(vehicleItem);
    
    // Clear the plate number error for this vehicle
    delete availabilityErrors.plateNos[vehicleIndex];
    
    vehicleItem.remove();
    vehicleCount--;
    
    // Update vehicle numbers and re-index any remaining plate number errors
    const newPlateNos = {};
    Object.keys(availabilityErrors.plateNos).forEach(oldIndex => {
        const numOldIndex = parseInt(oldIndex);
        if (numOldIndex < vehicleIndex) {
            // Vehicle before removed one, keep same index
            newPlateNos[oldIndex] = availabilityErrors.plateNos[oldIndex];
        } else if (numOldIndex > vehicleIndex) {
            // Vehicle after removed one, shift index down
            newPlateNos[numOldIndex - 1] = availabilityErrors.plateNos[oldIndex];
        }
        // vehicleIndex is removed, so we don't copy it
    });
    availabilityErrors.plateNos = newPlateNos;
    
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

function checkPlateNumberAvailability(plateNo, vehicleIndex) {
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
        const errorElement = document.querySelector(`.plate_no_${vehicleIndex}_error`);
        if (data.available) {
            // Clear error for this vehicle
            if (errorElement) {
                errorElement.classList.add('hidden');
                errorElement.textContent = '';
            }
            delete availabilityErrors.plateNos[vehicleIndex];
        } else {
            // Show error for this vehicle
            if (errorElement) {
                errorElement.classList.remove('hidden');
                errorElement.textContent = data.message;
            }
            // Set error for this vehicle
            availabilityErrors.plateNos[vehicleIndex] = true;
        }
        updateButtonStates();
    })
    .catch(error => {
        console.error('Plate No check error:', error);
        // Clear error on failure
        delete availabilityErrors.plateNos[vehicleIndex];
        updateButtonStates();
    });
}

function handleVehicleTypeChange(e) {
    const vehicleItem = e.target.closest('.vehicle-item');
    const plateNumberGroup = vehicleItem.querySelector('.plate-number-group');
    const plateNumberInput = vehicleItem.querySelector('input[name*="[plate_no]"]');
    const plateAsterisk = vehicleItem.querySelector('.plate-required-asterisk');
    
    if (!plateNumberGroup || !plateNumberInput) {
        return; // Safety check
    }
    
    const selectedOption = e.target.options[e.target.selectedIndex];
    const requiresPlate = selectedOption && selectedOption.getAttribute('data-requires-plate') === '1';
    
    if (!requiresPlate) {
        plateNumberGroup.style.display = 'none';
        plateNumberInput.removeAttribute('required');
        plateNumberInput.value = ''; // Clear the value
        if (plateAsterisk) {
            plateAsterisk.style.display = 'none';
        }
    } else {
        plateNumberGroup.style.display = 'block';
        plateNumberInput.setAttribute('required', 'required');
        // Ensure it's visible (remove any hidden classes)
        plateNumberGroup.classList.remove('hidden');
        plateNumberGroup.style.visibility = 'visible';
        if (plateAsterisk) {
            plateAsterisk.style.display = 'inline';
        }
    }
    
    updateButtonStates();
}

// Update vehicle numbers and form names (matching admin form)
function updateVehicleNumbers() {
    const vehiclesContainer = document.getElementById('vehiclesContainer');
    if (!vehiclesContainer) return;
    
    const vehicleItems = vehiclesContainer.querySelectorAll('.vehicle-item');
    vehicleItems.forEach((item, index) => {
        // Update title
        const title = item.querySelector('h4');
        if (title) title.textContent = 'Vehicle ' + (index + 1);
        
        // Update form names
        const selects = item.querySelectorAll('select');
        const inputs = item.querySelectorAll('input');
        
        selects.forEach(select => {
            select.name = 'vehicles[' + index + '][type_id]';
        });
        
        inputs.forEach(input => {
            if (input.name && input.name.includes('plate_no')) {
                input.name = 'vehicles[' + index + '][plate_no]';
            }
        });
        
        // Update error element class if it exists
        const errorElement = item.querySelector('[class*="plate_no_"]');
        if (errorElement) {
            // Remove old class and add new one
            const oldClass = errorElement.className.match(/plate_no_\d+_error/);
            if (oldClass) {
                errorElement.className = errorElement.className.replace(oldClass[0], `plate_no_${index}_error`);
            } else {
                // Add error element if it doesn't exist
                const plateInput = item.querySelector('input[name*="[plate_no]"]');
                if (plateInput && plateInput.parentNode) {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = `plate_no_${index}_error text-red-500 text-sm mt-1 hidden`;
                    plateInput.parentNode.appendChild(errorDiv);
                }
            }
        } else {
            // Add error element if it doesn't exist
            const plateInput = item.querySelector('input[name*="[plate_no]"]');
            if (plateInput && plateInput.parentNode) {
                const errorDiv = document.createElement('div');
                errorDiv.className = `plate_no_${index}_error text-red-500 text-sm mt-1 hidden`;
                plateInput.parentNode.appendChild(errorDiv);
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
                    this.classList.add('border-red-500');
                    this.classList.remove('border-gray-300', 'dark:border-gray-600');
                } else {
                    this.classList.remove('border-red-500');
                    this.classList.add('border-gray-300', 'dark:border-gray-600');
                }
                
                // Check plate number availability if format is valid
                if (plateNumber && validatePlateNumber(plateNumber)) {
                    checkPlateNumberAvailability(plateNumber, index);
                }
            });
        }
    });
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

// Handle form submission with AJAX
document.getElementById('stakeholderRegistrationForm').addEventListener('submit', function(e) {
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

// Guardian Evidence Functions
function updateGuardianEvidenceVisibility() {
    const stakeholderTypeSelect = document.getElementById('stakeholder_type_id');
    const guardianSection = document.getElementById('guardian_evidence_section');
    
    if (stakeholderTypeSelect && stakeholderTypeSelect.value) {
        const selectedOption = stakeholderTypeSelect.options[stakeholderTypeSelect.selectedIndex];
        const evidenceRequired = selectedOption.dataset.evidenceRequired === 'true';
        
        if (evidenceRequired) {
            guardianSection.classList.remove('hidden');
            // Make field required
            const guardianInput = document.getElementById('guardian_evidence');
            if (guardianInput) guardianInput.required = true;
        } else {
            guardianSection.classList.add('hidden');
            removeGuardianPreview();
            // Make field optional
            const guardianInput = document.getElementById('guardian_evidence');
            if (guardianInput) guardianInput.required = false;
        }
    } else {
        // No stakeholder type selected yet, hide for now
        guardianSection.classList.add('hidden');
        removeGuardianPreview();
    }
}

// Guardian Evidence File Upload Functions
function handleGuardianFileUpload(event) {
    const file = event.target.files[0];
    if (file) {
        // Check file size (10MB limit)
        const maxSize = 10 * 1024 * 1024; // 10MB
        if (file.size > maxSize) {
            alert('File size must be less than 10MB');
            event.target.value = '';
            return;
        }

        // Check file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!allowedTypes.includes(file.type)) {
            alert('Only JPG and PNG files are allowed');
            event.target.value = '';
            return;
        }

        const previewDiv = document.getElementById('guardianEvidencePreview');
        const contentDiv = document.getElementById('guardianPreviewContent');
        
        const reader = new FileReader();
        reader.onload = function(e) {
            contentDiv.innerHTML = `<img src="${e.target.result}" alt="Guardian Evidence Preview" class="w-full max-w-md rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A]">`;
        };
        reader.readAsDataURL(file);
        
        previewDiv.classList.remove('hidden');
    }
}

function removeGuardianPreview() {
    document.getElementById('guardianEvidencePreview').classList.add('hidden');
    document.getElementById('guardian_evidence').value = '';
}

// Guardian Camera Modal Functions
let guardianCameraStream = null;

window.openGuardianCameraModal = async function() {
    try {
        // Get camera with high resolution first
        guardianCameraStream = await navigator.mediaDevices.getUserMedia({ 
            video: { 
                facingMode: 'user',
                width: { ideal: 1920 },
                height: { ideal: 1080 }
            } 
        });
        const video = document.getElementById('guardianCameraVideo');
        video.srcObject = guardianCameraStream;
        
        // Wait for video to load and get actual dimensions before showing modal
        video.onloadedmetadata = function() {
            // Set video dimensions to match camera resolution
            video.style.width = '100%';
            video.style.height = 'auto';
            video.style.maxHeight = '80vh'; // Limit max height to prevent overflow
            
            // Only show modal when camera is ready
            document.getElementById('guardianCameraModal').classList.remove('hidden');
        };
    } catch (error) {
        alert('Error accessing camera: ' + error.message);
    }
}

window.closeGuardianCameraModal = function() {
    if (guardianCameraStream) {
        guardianCameraStream.getTracks().forEach(track => track.stop());
        guardianCameraStream = null;
    }
    document.getElementById('guardianCameraModal').classList.add('hidden');
}

window.captureGuardianPhoto = function() {
    const video = document.getElementById('guardianCameraVideo');
    const canvas = document.getElementById('guardianCameraCanvas');
    const context = canvas.getContext('2d');
    
    // Set canvas dimensions to match video
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    
    // Draw the video frame to canvas
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    
    // Convert canvas to blob and create file
    canvas.toBlob(function(blob) {
        // Create a file from the blob
        const file = new File([blob], 'guardian_evidence_photo.jpg', { type: 'image/jpeg' });
        
        // Create a FileList-like object
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        
        // Set the file input value
        const fileInput = document.getElementById('guardian_evidence');
        fileInput.files = dataTransfer.files;
        
        // Trigger the change event to show preview
        const event = new Event('change', { bubbles: true });
        fileInput.dispatchEvent(event);
        
        // Close the camera modal
        closeGuardianCameraModal();
    }, 'image/jpeg', 0.8);
}
</script>

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
            <p>Your stakeholder registration has been submitted successfully! An administrator will review your application and notify you via email once it has been approved.</p>
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
@endsection
