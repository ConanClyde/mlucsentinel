@extends('layouts.app')

@section('title', 'Student Registration')
@section('page-title', 'Student Registration')

@section('content')
<div class="min-h-screen py-4 md:py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6 md:mb-8 text-center">
            <h1 class="text-xl md:text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Student Registration</h1>
            <p class="text-sm md:text-base text-[#706f6c] dark:text-[#A1A09A]">Register new student users with vehicle information</p>
            </div>

        <!-- Progress Steps -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6 mb-4 md:mb-6 overflow-x-auto">
            <div class="flex items-center justify-center min-w-max">
                <div class="flex items-center space-x-2 md:space-x-4">
                    <!-- Step 1 -->
                    <div class="flex items-center">
                        <div id="step-1-indicator" class="w-7 h-7 md:w-8 md:h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs md:text-sm font-medium">
                            1
            </div>
                        <span id="step-1-label" class="ml-1 md:ml-2 text-xs md:text-sm font-medium text-blue-600 dark:text-blue-400 hidden sm:inline">Basic Information</span>
    </div>

                    <div class="w-8 md:w-16 h-0.5 bg-gray-300 dark:bg-gray-600"></div>
                    
                    <!-- Step 2 -->
                    <div class="flex items-center">
                        <div id="step-2-indicator" class="w-7 h-7 md:w-8 md:h-8 rounded-full bg-gray-300 dark:bg-gray-600 text-gray-600 dark:text-gray-400 flex items-center justify-center text-xs md:text-sm font-medium">
                            2
                        </div>
                        <span id="step-2-label" class="ml-1 md:ml-2 text-xs md:text-sm font-medium text-gray-500 dark:text-gray-400 hidden sm:inline">License Information</span>
                    </div>

                    <div class="w-8 md:w-16 h-0.5 bg-gray-300 dark:bg-gray-600"></div>
                    
                    <!-- Step 3 -->
                    <div class="flex items-center">
                        <div id="step-3-indicator" class="w-7 h-7 md:w-8 md:h-8 rounded-full bg-gray-300 dark:bg-gray-600 text-gray-600 dark:text-gray-400 flex items-center justify-center text-xs md:text-sm font-medium">
                            3
                    </div>
                        <span id="step-3-label" class="ml-1 md:ml-2 text-xs md:text-sm font-medium text-gray-500 dark:text-gray-400 hidden sm:inline">Vehicle Information</span>
                </div>
                    </div>
                </div>
            </div>

        <!-- Registration Form -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A]">
            <form id="studentRegistrationForm" enctype="multipart/form-data">
                @csrf
                
                <!-- Step 1: Basic Information -->
                <div id="step-1" class="step-content p-4 md:p-6">
                    <h3 class="text-base md:text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4 md:mb-6">Basic Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                                First Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="first_name" name="first_name" class="form-input" placeholder="John" required>
                            <div id="first_name_error" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>
                        
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                                Last Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="last_name" name="last_name" class="form-input" placeholder="Doe" required>
                            <div id="last_name_error" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>
                    </div>
                    
                    <div class="mt-4 md:mt-6 grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                    <div>
                        <label for="program_id" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                            Program <span class="text-red-500">*</span>
                        </label>
                        <select id="program_id" name="program_id" class="form-input" required>
                            <option value="">Select Program</option>
                            @foreach($colleges as $college)
                                <optgroup label="{{ $college->name }}">
                                    @foreach($college->programs as $program)
                                        <option value="{{ $program->id }}">{{ $program->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        <div id="program_id_error" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                        <div>
                            <label for="student_id" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                                Student ID <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="student_id" name="student_id" class="form-input" placeholder="221-0238-2" required>
                            <div id="student_id_error" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                </div>

                    <div class="mt-4 md:mt-6">
                        <label for="email" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input type="email" id="email" name="email" class="form-input" placeholder="john.doe@gmail.com or john.doe@student.dmmmsu.edu.ph" required>
                        <div class="text-sm text-[#706f6c] dark:text-[#A1A09A] mt-1">Used for violation notifications</div>
                        <div id="email_error" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                </div>

                <!-- Step 2: License Information -->
                <div id="step-2" class="step-content p-6 hidden">
                    <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-6">License Information</h3>
                    
                    <div class="space-y-4 md:space-y-6">
                        <div>
                            <label for="license_no" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                                License Number <span class="text-gray-400 text-xs">(Optional)</span>
                            </label>
                            <input type="text" id="license_no" name="license_no" class="form-input" placeholder="A12-34-567890">
                            <div id="license_no_error" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                                License Image <span class="text-gray-400 text-xs">(Optional)</span>
                            </label>
                    <div class="bg-gray-50 dark:bg-[#161615] p-4 rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A]">
                        <!-- Upload Options -->
                        <div class="flex gap-4 mb-4">
                                    <button type="button" class="btn btn-info" onclick="openLicenseCameraModal()">
                                        <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                Take Photo
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="document.getElementById('license_image').click()">
                                        <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                Upload File
                            </button>
                        </div>

                        <!-- Hidden File Input -->
                                <input type="file" id="license_image" name="license_image" accept="image/jpeg,image/jpg,image/png,image/heic,image/heif" class="hidden" onchange="handleLicenseFileUpload(event)">

                        <!-- Image Preview -->
                                <div id="licenseImagePreview" class="hidden">
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
                </div>
            </div>

                <!-- Step 3: Vehicle Information -->
                <div id="step-3" class="step-content p-6 hidden">
                    <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-6">Vehicle Information</h3>
                    
                <div class="flex items-center justify-between mb-4">
                        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Add vehicle information for the student</p>
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
                            <button type="button" class="remove-vehicle-btn text-red-600 hover:text-red-700 hidden">
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
                            </div>
                                <div class="form-group plate-number-group">
                                    <label class="form-label">Plate Number <span class="text-red-500">*</span></label>
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
                    Maximum of 3 vehicles allowed per student
                </p>
            </div>

                <!-- Form Actions -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-2 sm:gap-3 pt-4 md:pt-6 border-t border-[#e3e3e0] dark:border-[#3E3E3A] px-4 md:px-6 pb-4 md:pb-6">
                    <!-- Step 1 Buttons -->
                    <div id="step-1-buttons" class="w-full sm:w-auto">
                        <button type="button" id="next-step" class="btn btn-primary w-full sm:w-auto" disabled>
                            Next
                </button>
                    </div>
                    
                    <!-- Step 2 Buttons -->
                    <div id="step-2-buttons" class="hidden flex-col sm:flex-row gap-2 sm:gap-3 w-full sm:w-auto">
                        <button type="button" id="prev-step" class="btn btn-secondary w-full sm:w-auto">
                            Previous
                        </button>
                        <button type="button" id="next-step-2" class="btn btn-primary w-full sm:w-auto" disabled>
                            Next
                        </button>
                    </div>
                    
                    <!-- Step 3 Buttons -->
                    <div id="step-3-buttons" class="hidden flex-col sm:flex-row gap-2 sm:gap-3 w-full sm:w-auto">
                        <button type="button" id="prev-step-2" class="btn btn-secondary w-full sm:w-auto">
                            Previous
                        </button>
                        <button type="submit" id="submit-form" class="btn btn-primary w-full sm:w-auto" disabled>
                            Register
                        </button>
                    </div>
            </div>
        </form>
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

<!-- Success Modal -->
<div id="successModal" class="modal-backdrop hidden">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title text-green-600 flex items-center gap-2">
                <svg class="modal-icon-success w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Student Registered Successfully!
            </h2>
        </div>
        <div class="modal-body">
            <p>The student has been registered successfully and can now be assigned vehicles.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-success" onclick="closeSuccessModal()">Okay</button>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div id="errorModal" class="modal-backdrop hidden">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title text-red-500 flex items-center gap-2">
                <svg class="modal-icon-error w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Error
            </h2>
        </div>
        <div class="modal-body">
            <p id="errorMessage">An error occurred while processing your request. Please try again.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-danger" onclick="closeErrorModal()">Close</button>
        </div>
    </div>
</div>
@endsection

<script>
// Override global modal functions from main.js immediately (before DOMContentLoaded)
// The global showSuccessModal from main.js expects successTitle and successMessage elements
// which don't exist in this page's modal, so we override it before any calls
(function() {
    'use strict';
    window.showSuccessModal = function(title, message) {
        // This page's modal doesn't have successTitle/successMessage, just show the modal
        const modal = document.getElementById('successModal');
        if (modal) {
            modal.classList.remove('hidden');
        }
    };
    
    window.closeSuccessModal = function() {
        const modal = document.getElementById('successModal');
        if (modal) {
            modal.classList.add('hidden');
        }
    };
    
    window.showErrorModal = function(message) {
        const modal = document.getElementById('errorModal');
        const errorMessage = document.getElementById('errorMessage');
        if (modal && errorMessage) {
            errorMessage.textContent = message || 'An error occurred while processing your request';
            modal.classList.remove('hidden');
        }
    };
    
    window.closeErrorModal = function() {
        const modal = document.getElementById('errorModal');
        if (modal) {
            modal.classList.add('hidden');
        }
    };
})();

let currentStep = 1;

// Track validation errors
const availabilityErrors = {
    email: false,
    studentId: false,
    licenseNo: false,
    plateNos: {}
};

// Wait for DOM to load
document.addEventListener('DOMContentLoaded', function() {
    
    // Step navigation
    const nextButton = document.getElementById('next-step');
    const nextButton2 = document.getElementById('next-step-2');
    const prevButton = document.getElementById('prev-step');
    const prevButton2 = document.getElementById('prev-step-2');
    
    if (nextButton) {
        nextButton.addEventListener('click', function() {
            if (validateCurrentStep()) {
                showStep(2);
            }
        });
    }
    
    if (nextButton2) {
        nextButton2.addEventListener('click', function() {
            if (validateCurrentStep()) {
                showStep(3);
            }
        });
    }
    
    if (prevButton) {
        prevButton.addEventListener('click', function() {
            showStep(1);
        });
    }
    
    if (prevButton2) {
        prevButton2.addEventListener('click', function() {
            showStep(2);
        });
    }
    
    // Form submission
    const form = document.getElementById('studentRegistrationForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!validateCurrentStep()) {
                return;
            }
            
            const formData = new FormData(this);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            
            fetch('{{ route("admin.registration.student.store") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                return response.json().then(data => {
                    return { status: response.status, data };
                });
            })
            .then(({ status, data }) => {
                if (status === 422 && data.errors) {
                    // Validation errors
                    let errorMessages = [];
                    for (let field in data.errors) {
                        if (data.errors[field]) {
                            errorMessages.push(data.errors[field][0]);
                        }
                    }
                    // Hide loading state on validation error
                    if (window.FormLoader) {
                        FormLoader.hideLoading(form);
                    }
                    showErrorModal(errorMessages.join('<br>'));
                } else if (data.success) {
                    // Hide loading state first to re-enable inputs
                    if (window.FormLoader) {
                        FormLoader.hideLoading(form);
                    }
                    showSuccessModal();
                    resetForm();
                } else {
                    // Hide loading state on error too
                    if (window.FormLoader) {
                        FormLoader.hideLoading(form);
                    }
                    showErrorModal(data.message || 'An error occurred');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Hide loading state on error
                if (window.FormLoader) {
                    FormLoader.hideLoading(form);
                }
                showErrorModal('An error occurred while processing your request');
            });
        });
    }
    
    // Delayed validation (1 second after user stops typing)
    let firstNameTimeout;
    let lastNameTimeout;
    let studentIdTimeout;
    let emailTimeout;
    let licenseNoTimeout;

    // Step 1 validation
    const firstNameInput = document.getElementById('first_name');
    const lastNameInput = document.getElementById('last_name');
    const programInput = document.getElementById('program_id');
    const studentIdInput = document.getElementById('student_id');
    const emailInput = document.getElementById('email');

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

    if (programInput) {
        programInput.addEventListener('change', function() {
            updateButtonStates();
        });
    }

    if (studentIdInput) {
        studentIdInput.addEventListener('input', function() {
            clearTimeout(studentIdTimeout);
            studentIdTimeout = setTimeout(validateStudentId, 1000);
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

    // Step 2 validation
    const licenseNoInput = document.getElementById('license_no');

    if (licenseNoInput) {
        licenseNoInput.addEventListener('input', function() {
            clearTimeout(licenseNoTimeout);
            licenseNoTimeout = setTimeout(validateLicenseNo, 1000);
            updateButtonStates();
        });
    }

    // Individual validation functions
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

    function validateStudentId() {
        const studentId = studentIdInput.value.trim();
        
        if (!studentId) {
            showFieldError('student_id', 'Student ID is required');
            return false;
        } else if (!isValidStudentId(studentId)) {
            showFieldError('student_id', 'Student ID must be in format 2XX-XXXX-2 (e.g., 221-0238-2)');
            return false;
        } else {
            // Check student ID availability
            checkStudentIdAvailability(studentId);
            return true;
        }
    }

    function validateEmail() {
        const email = emailInput.value.trim();
        
        if (!email) {
            showFieldError('email', 'Email is required');
            return false;
        } else if (!isValidEmail(email)) {
            showFieldError('email', 'Please enter a valid email from Gmail (@gmail.com) or Student DMMMSU (@student.dmmmsu.edu.ph)');
            return false;
        } else {
            // Check email availability
            checkEmailAvailability(email);
            return true;
        }
    }

    function checkEmailAvailability(email) {
                fetch('{{ route("admin.registration.student.check-email") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ email: email })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.available) {
                clearFieldError('email');
                availabilityErrors.email = false;
                    } else {
                showFieldError('email', data.message);
                availabilityErrors.email = true;
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

    function checkStudentIdAvailability(studentId) {
        fetch('{{ route("admin.registration.student.check-student-id") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ student_id: studentId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.available) {
                clearFieldError('student_id');
                availabilityErrors.studentId = false;
            } else {
                showFieldError('student_id', data.message);
                availabilityErrors.studentId = true;
            }
            updateButtonStates();
        })
        .catch(error => {
            console.error('Student ID check error:', error);
            clearFieldError('student_id');
            availabilityErrors.studentId = false;
            updateButtonStates();
        });
    }

    function checkLicenseNoAvailability(licenseNo) {
        fetch('{{ route("admin.registration.student.check-license-no") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ license_no: licenseNo })
        })
        .then(response => response.json())
        .then(data => {
            if (data.available) {
                clearFieldError('license_no');
                availabilityErrors.licenseNo = false;
            } else {
                showFieldError('license_no', data.message);
                availabilityErrors.licenseNo = true;
            }
            updateButtonStates();
        })
        .catch(error => {
            console.error('License No check error:', error);
            clearFieldError('license_no');
            availabilityErrors.licenseNo = false;
            updateButtonStates();
        });
    }

    function validateLicenseNo() {
        const licenseNo = licenseNoInput.value.trim();
        
        // License is now optional
        if (licenseNo) {
            // Check license number availability only if provided
            checkLicenseNoAvailability(licenseNo);
        }
        return true;
    }

    function showFieldError(fieldName, message) {
        const errorElement = document.getElementById(fieldName + '_error');
        const inputElement = document.getElementById(fieldName);
        
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.classList.remove('hidden');
        }
        
        if (inputElement) {
            inputElement.classList.remove('border-gray-300', 'dark:border-gray-600');
            inputElement.classList.add('border-red-500');
        }
    }

    function clearFieldError(fieldName) {
        const errorElement = document.getElementById(fieldName + '_error');
        const inputElement = document.getElementById(fieldName);
        
        if (errorElement) {
            errorElement.classList.add('hidden');
            errorElement.textContent = '';
        }
        
        if (inputElement) {
            inputElement.classList.remove('border-red-500');
            inputElement.classList.add('border-gray-300', 'dark:border-gray-600');
        }
        
        // Reset availability error tracking
        if (fieldName === 'email') {
            availabilityErrors.email = false;
        } else if (fieldName === 'student_id') {
            availabilityErrors.studentId = false;
        } else if (fieldName === 'license_no') {
            availabilityErrors.licenseNo = false;
        }
        
        updateButtonStates();
    }
});

// Plate number availability checker (moved outside DOMContentLoaded to be globally accessible)
function checkPlateNumberAvailability(plateNo, vehicleIndex) {
    fetch('{{ route("admin.registration.student.check-plate-no") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ plate_no: plateNo })
    })
    .then(response => response.json())
    .then(data => {
        const plateInput = document.querySelector(`input[name="vehicles[${vehicleIndex}][plate_no]"]`);
        const errorElement = document.querySelector(`.plate_no_${vehicleIndex}_error`);
        
        if (data.available) {
            if (plateInput) {
                plateInput.classList.remove('border-red-500');
                plateInput.classList.add('border-gray-300', 'dark:border-gray-600');
            }
            if (errorElement) {
                errorElement.classList.add('hidden');
                errorElement.textContent = '';
            }
            // Clear error for this vehicle
            delete availabilityErrors.plateNos[vehicleIndex];
        } else {
            if (plateInput) {
                plateInput.classList.add('border-red-500');
                plateInput.classList.remove('border-gray-300', 'dark:border-gray-600');
            }
            if (errorElement) {
                errorElement.textContent = data.message || 'Plate number is already registered';
                errorElement.classList.remove('hidden');
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

// Email validation function
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@(gmail\.com|student\.dmmmsu\.edu\.ph)$/;
    return emailRegex.test(email);
}

// Function to check if all step 1 fields are valid
function isStep1Valid() {
    const firstName = document.getElementById('first_name').value.trim();
    const lastName = document.getElementById('last_name').value.trim();
    const programId = document.getElementById('program_id').value;
    const studentId = document.getElementById('student_id').value.trim();
    const email = document.getElementById('email').value.trim();
    
    const hasNoErrors = !availabilityErrors.email && !availabilityErrors.studentId;
    
    return firstName && lastName && programId && studentId && email && isValidEmail(email) && isValidStudentId(studentId) && hasNoErrors;
}

// Function to check if all step 2 fields are valid
function isStep2Valid() {
    // License information is now optional (for users with only electric vehicles)
    // Always return true to enable the Next button
    return true;
}

// Function to check if all step 3 fields are valid
function isStep3Valid() {
    const vehicles = document.querySelectorAll('.vehicle-item');
    let isValid = true;
    
    vehicles.forEach((vehicle, index) => {
        const typeSelect = vehicle.querySelector('select[name*="[type_id]"]');
        const plateInput = vehicle.querySelector('input[name*="[plate_no]"]');
        
        if (!typeSelect.value) {
            isValid = false;
        }
        
        // Only require plate number if it's not an electric vehicle
        if (typeSelect.value !== '3' && !plateInput.value.trim()) {
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

// Function to update button states
function updateButtonStates() {
    const nextButton = document.getElementById('next-step');
    const nextButton2 = document.getElementById('next-step-2');
    const submitButton = document.getElementById('submit-form');
    
    if (currentStep === 1) {
        nextButton.disabled = !isStep1Valid();
    } else if (currentStep === 2) {
        nextButton2.disabled = !isStep2Valid();
    } else if (currentStep === 3) {
        submitButton.disabled = !isStep3Valid();
    }
}

// Function to show step with corrected button logic
function showStep(step) {
    currentStep = step;
    
    // Hide all steps
    document.querySelectorAll('.step-content').forEach(stepElement => {
        stepElement.classList.add('hidden');
    });
    
    // Show current step
    const currentStepElement = document.getElementById(`step-${currentStep}`);
    if (currentStepElement) {
        currentStepElement.classList.remove('hidden');
                    } else {
        console.error('Step element not found for step:', currentStep);
    }
    
    // Update step indicators
    for (let i = 1; i <= 3; i++) {
        const indicator = document.getElementById(`step-${i}-indicator`);
        const label = document.getElementById(`step-${i}-label`);
        
        if (i <= currentStep) {
            indicator.className = 'w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-medium';
            label.className = 'ml-2 text-sm font-medium text-blue-600 dark:text-blue-400';
        } else {
            indicator.className = 'w-8 h-8 rounded-full bg-gray-300 dark:bg-gray-600 text-gray-600 dark:text-gray-400 flex items-center justify-center text-sm font-medium';
            label.className = 'ml-2 text-sm font-medium text-gray-500 dark:text-gray-400';
        }
    }
    
    // Update buttons - HTML STRUCTURE BASED
    const step1Buttons = document.getElementById('step-1-buttons');
    const step2Buttons = document.getElementById('step-2-buttons');
    const step3Buttons = document.getElementById('step-3-buttons');
    
    if (currentStep === 1) {
        step1Buttons.classList.remove('hidden');
        step2Buttons.classList.add('hidden');
        step3Buttons.classList.add('hidden');
    } else if (currentStep === 2) {
        step1Buttons.classList.add('hidden');
        step2Buttons.classList.remove('hidden');
        step3Buttons.classList.add('hidden');
    } else if (currentStep === 3) {
        step1Buttons.classList.add('hidden');
        step2Buttons.classList.add('hidden');
        step3Buttons.classList.remove('hidden');
    }
    
    // Update button states after step change
    updateButtonStates();
}

// Simple validation
function validateCurrentStep() {
    if (currentStep === 1) {
        const firstName = document.getElementById('first_name').value.trim();
        const lastName = document.getElementById('last_name').value.trim();
        const programId = document.getElementById('program_id').value;
        const studentId = document.getElementById('student_id').value.trim();
        const email = document.getElementById('email').value.trim();
        
        if (!firstName || !lastName || !programId || !studentId || !email) {
            showErrorModal('Please fill in all required fields');
            return false;
        }
        
        if (!isValidEmail(email)) {
            showErrorModal('Please enter a valid email from Gmail (@gmail.com) or Student DMMMSU (@student.dmmmsu.edu.ph)');
            return false;
        }
        
        if (!isValidStudentId(studentId)) {
            showErrorModal('Student ID must be in format 2XX-XXXX-2 (e.g., 221-0238-2)');
            return false;
        }
        
        return true;
    } else if (currentStep === 2) {
        // License information is optional (for users with only electric vehicles)
        return true;
    } else if (currentStep === 3) {
        const vehicles = document.querySelectorAll('.vehicle-item');
        
        if (vehicles.length === 0) {
            showErrorModal('Please add at least one vehicle');
            return false;
        }
        
        for (let vehicle of vehicles) {
            const typeSelect = vehicle.querySelector('select[name*="[type_id]"]');
            const plateInput = vehicle.querySelector('input[name*="[plate_no]"]');
            const plateNumberGroup = vehicle.querySelector('.plate-number-group');
            
            if (!typeSelect.value) {
                showErrorModal('Please select vehicle type for all vehicles');
                return false;
            }
            
            // Only require plate number if it's not an electric vehicle
            if (typeSelect.value !== '3' && !plateInput.value.trim()) {
                showErrorModal('Please fill in plate number for all vehicles');
                return false;
            }
            
            // Validate plate number format if it has a value
            if (plateInput.value.trim() && !validatePlateNumber(plateInput.value.trim())) {
                showErrorModal('Plate number must be in format ABC-1234 (2-3 letters, dash, 3-4 numbers)');
                return false;
            }
        }
        
        return true;
    }
    return false;
}

// Show success modal - using window function
function showSuccessModal() {
    window.showSuccessModal();
}

// Close success modal - using window function
function closeSuccessModal() {
    window.closeSuccessModal();
}

// Show error modal - using window function
function showErrorModal(message) {
    window.showErrorModal(message);
}

// Close error modal - using window function
function closeErrorModal() {
    window.closeErrorModal();
}

// Reset form to initial state
function resetForm() {
    const form = document.getElementById('studentRegistrationForm');
    
    // Re-enable all form inputs (in case they were disabled during submission)
    const allInputs = form.querySelectorAll('input, select, textarea, button');
    allInputs.forEach(input => {
        // Don't enable buttons that should be disabled based on form state
        if (input.type === 'submit' || input.id === 'next-step' || input.id === 'submit-form') {
            // Will be handled by updateButtonStates() or other logic
            return;
        }
        input.disabled = false;
        // Clear any loading state data attributes
        delete input.dataset.originalDisabled;
    });
    
    // Remove loading state from form if FormLoader was used
    if (window.FormLoader && form) {
        form.classList.remove('form-loading');
        delete form.dataset.loading;
    }
    
    // Reset form fields
    document.getElementById('first_name').value = '';
    document.getElementById('last_name').value = '';
    document.getElementById('program_id').value = '';
    document.getElementById('student_id').value = '';
    document.getElementById('email').value = '';
    document.getElementById('license_no').value = '';
    document.getElementById('license_image').value = '';
    
    // Reset license image preview
    document.getElementById('licenseImagePreview').classList.add('hidden');
    document.querySelector('.flex.gap-4.mb-4').classList.remove('hidden');
    
    // Reset vehicle container
    const vehiclesContainer = document.getElementById('vehiclesContainer');
    const addVehicleBtn = document.getElementById('addVehicleBtn');
    
    // Reset vehicle count to 1
    vehicleCount = 1;
    
    // Clear plate number errors
    availabilityErrors.plateNos = {};
    
    const existingSelect = document.querySelector('select[name="vehicles[0][type_id]"]');
    let vehicleTypeOptions = '<option value="">Select Vehicle Type</option>';
    if (existingSelect) {
        existingSelect.querySelectorAll('option').forEach(option => {
            if (option.value !== '') {
                const requiresPlate = option.getAttribute('data-requires-plate');
                vehicleTypeOptions += '<option value="' + option.value + '" data-requires-plate="' + (requiresPlate || '1') + '">' + option.textContent + '</option>';
            }
        });
    }
    
    vehiclesContainer.innerHTML = '<div class="vehicle-item bg-gray-50 dark:bg-[#161615] p-4 rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A]">' +
        '<div class="flex items-center justify-between mb-3">' +
            '<h4 class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Vehicle 1</h4>' +
            '<button type="button" class="remove-vehicle-btn text-red-600 hover:text-red-700 hidden">' +
                '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>' +
            '</button>' +
        '</div>' +
        '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">' +
            '<div class="form-group">' +
                '<label class="form-label">Vehicle Type <span class="text-red-500">*</span></label>' +
                '<select name="vehicles[0][type_id]" class="form-input" required>' +
                    vehicleTypeOptions +
                '</select>' +
            '</div>' +
            '<div class="form-group plate-number-group">' +
                '<label class="form-label">Plate Number <span class="text-red-500">*</span></label>' +
                '<input name="vehicles[0][plate_no]" type="text" required class="form-input" placeholder="ABC-1234">' +
                '<div class="plate_no_0_error text-red-500 text-sm mt-1 hidden"></div>' +
            '</div>' +
        '</div>' +
    '</div>';
    
    // Reset add vehicle button
    if (addVehicleBtn) {
        addVehicleBtn.disabled = false;
        addVehicleBtn.textContent = 'Add Vehicle';
        addVehicleBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        addVehicleBtn.innerHTML = '<svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" /></svg>Add Vehicle';
    }
    
    // Reset to step 1
    showStep(1);
    
    // Clear any error messages
    const errorElements = document.querySelectorAll('[id$="_error"]');
    errorElements.forEach(element => {
        element.classList.add('hidden');
        element.textContent = '';
    });
    
    // Remove error styling from inputs
    const inputs = document.querySelectorAll('.form-input');
    inputs.forEach(input => {
        input.classList.remove('border-red-500');
        input.classList.add('border-gray-300', 'dark:border-gray-600');
    });
    
    // Re-attach event listeners for the reset vehicle
    setTimeout(() => {
        const plateInput = document.querySelector('input[name="vehicles[0][plate_no]"]');
        if (plateInput) {
            // Remove old listeners and add new one
            plateInput.replaceWith(plateInput.cloneNode(true));
            const newPlateInput = document.querySelector('input[name="vehicles[0][plate_no]"]');
            
            newPlateInput.addEventListener('input', function() {
                const plateNumber = this.value.trim();
                
                // Clear the error when user types
                delete availabilityErrors.plateNos[0];
                const errorElement = document.querySelector('.plate_no_0_error');
                if (errorElement) {
                    errorElement.classList.add('hidden');
                    errorElement.textContent = '';
                }
                
                if (plateNumber && !validatePlateNumber(plateNumber)) {
                    this.classList.add('border-red-500');
                    this.classList.remove('border-gray-300', 'dark:border-gray-600');
                } else {
                    this.classList.remove('border-red-500');
                    this.classList.add('border-gray-300', 'dark:border-gray-600');
                }
                
                // Check plate number availability if format is valid
                if (plateNumber && validatePlateNumber(plateNumber)) {
                    checkPlateNumberAvailability(plateNumber, 0);
                }
                
                updateButtonStates();
            });
        }
        
        const vehicleTypeSelect = document.querySelector('select[name="vehicles[0][type_id]"]');
        if (vehicleTypeSelect) {
            vehicleTypeSelect.addEventListener('change', handleVehicleTypeChange);
        }
        
        updateButtonStates();
    }, 100);
}

// Vehicle Management Functions
let vehicleCount = 1;

// Add Vehicle functionality
document.addEventListener('DOMContentLoaded', function() {
    const addVehicleBtn = document.getElementById('addVehicleBtn');
    const vehiclesContainer = document.getElementById('vehiclesContainer');

    if (addVehicleBtn) {
    addVehicleBtn.addEventListener('click', function() {
        if (vehicleCount >= 3) {
            alert('Maximum of 3 vehicles allowed per student');
            return;
        }

        vehicleCount++;
            
            // Get vehicle types from the existing select
            const existingSelect = document.querySelector('select[name="vehicles[0][type_id]"]');
            let vehicleTypeOptions = '<option value="">Select Vehicle Type</option>';
            if (existingSelect) {
                existingSelect.querySelectorAll('option').forEach(option => {
                    if (option.value !== '') {
                        const requiresPlate = option.getAttribute('data-requires-plate');
                        vehicleTypeOptions += '<option value="' + option.value + '" data-requires-plate="' + (requiresPlate || '1') + '">' + option.textContent + '</option>';
                    }
                });
            }
        
        const vehicleHtml = '<div class="vehicle-item bg-gray-50 dark:bg-[#161615] p-4 rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A]">' +
            '<div class="flex items-center justify-between mb-3">' +
                '<h4 class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Vehicle ' + vehicleCount + '</h4>' +
                '<button type="button" class="remove-vehicle-btn text-red-600 hover:text-red-700">' +
                        '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>' +
                '</button>' +
            '</div>' +
            '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">' +
                '<div class="form-group">' +
                        '<label class="form-label">Vehicle Type <span class="text-red-500">*</span></label>' +
                    '<select name="vehicles[' + (vehicleCount - 1) + '][type_id]" class="form-input" required>' +
                            vehicleTypeOptions +
                    '</select>' +
                '</div>' +
                    '<div class="form-group plate-number-group">' +
                        '<label class="form-label">Plate Number <span class="text-red-500">*</span></label>' +
                    '<input name="vehicles[' + (vehicleCount - 1) + '][plate_no]" type="text" required class="form-input" placeholder="ABC-1234">' +
                    '<div class="plate_no_' + (vehicleCount - 1) + '_error text-red-500 text-sm mt-1 hidden"></div>' +
                '</div>' +
            '</div>' +
        '</div>';
        
        vehiclesContainer.insertAdjacentHTML('beforeend', vehicleHtml);
        
            // Add event listener for the new vehicle type select
            const newVehicleSelect = vehiclesContainer.querySelector(`select[name="vehicles[${vehicleCount - 1}][type_id]"]`);
            if (newVehicleSelect) {
                newVehicleSelect.addEventListener('change', handleVehicleTypeChange);
                // Ensure plate number group is visible by default for new vehicles
                const newVehicleItem = newVehicleSelect.closest('.vehicle-item');
                const newPlateNumberGroup = newVehicleItem.querySelector('.plate-number-group');
                if (newPlateNumberGroup) {
                    newPlateNumberGroup.style.display = 'block';
                    newPlateNumberGroup.classList.remove('hidden');
                    newPlateNumberGroup.style.visibility = 'visible';
                }
            }
            
            // Add event listener for the new plate number input
            const vehicleIndex = vehicleCount - 1; // Capture index here, outside the closure
            const newPlateInput = vehiclesContainer.querySelector(`input[name="vehicles[${vehicleIndex}][plate_no]"]`);
            if (newPlateInput) {
                newPlateInput.addEventListener('input', function() {
                    const plateNumber = this.value.trim();
                    
                    // Clear the error when user types
                    delete availabilityErrors.plateNos[vehicleIndex];
                    const errorElement = document.querySelector(`.plate_no_${vehicleIndex}_error`);
                    if (errorElement) {
                        errorElement.classList.add('hidden');
                        errorElement.textContent = '';
                    }
                    
                    if (plateNumber && !validatePlateNumber(plateNumber)) {
                        this.classList.add('border-red-500');
                        this.classList.remove('border-gray-300', 'dark:border-gray-600');
                    } else {
                        this.classList.remove('border-red-500');
                        this.classList.add('border-gray-300', 'dark:border-gray-600');
                    }
                    
                    // Check plate number availability if format is valid
                    if (plateNumber && validatePlateNumber(plateNumber)) {
                        checkPlateNumberAvailability(plateNumber, vehicleIndex);
                    }
                    
                    updateButtonStates();
                });
            }
        
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
            
            updateButtonStates();
    });
    }

    // Remove Vehicle functionality
    if (vehiclesContainer) {
    vehiclesContainer.addEventListener('click', function(e) {
        if (e.target.closest('.remove-vehicle-btn')) {
            const vehicleItem = e.target.closest('.vehicle-item');
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
        });
    }
});

// Handle vehicle type change
function handleVehicleTypeChange(e) {
    const vehicleItem = e.target.closest('.vehicle-item');
    const plateNumberGroup = vehicleItem.querySelector('.plate-number-group');
    const plateNumberInput = vehicleItem.querySelector('input[name*="[plate_no]"]');
    
    if (!plateNumberGroup || !plateNumberInput) {
        return; // Safety check
    }
    
    const selectedOption = e.target.options[e.target.selectedIndex];
    const requiresPlate = selectedOption && selectedOption.getAttribute('data-requires-plate') === '1';
    
    if (!requiresPlate) {
        plateNumberGroup.style.display = 'none';
        plateNumberInput.removeAttribute('required');
        plateNumberInput.value = ''; // Clear the value
    } else {
        plateNumberGroup.style.display = 'block';
        plateNumberInput.setAttribute('required', 'required');
        // Ensure it's visible (remove any hidden classes)
        plateNumberGroup.classList.remove('hidden');
        plateNumberGroup.style.visibility = 'visible';
    }
    
    updateButtonStates();
}

// Validate Student ID format
function isValidStudentId(studentId) {
    const studentIdRegex = /^2[0-9]{2}-[0-9]{4}-2$/;
    return studentIdRegex.test(studentId);
}

// Validate plate number format
function validatePlateNumber(plateNumber) {
    const plateRegex = /^[A-Z]{2,3}-[0-9]{3,4}$/;
    return plateRegex.test(plateNumber);
}

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
            select.name = 'vehicles[' + index + '][type_id]';
        });
        
        inputs.forEach(input => {
            if (input.name.includes('plate_no')) {
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

// License Image Functions
let licenseCameraStream = null;

// License file upload handling
window.handleLicenseFileUpload = function(event) {
    const file = event.target.files[0];
    if (file) {
        // Check file size (2MB limit)
        const maxSize = 2 * 1024 * 1024; // 2MB in bytes
        if (file.size > maxSize) {
            alert('File size must be less than 2MB. Your file is ' + (file.size / 1024 / 1024).toFixed(2) + 'MB');
            event.target.value = ''; // Clear the input
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewImage = document.getElementById('licensePreviewImage');
            const imagePreview = document.getElementById('licenseImagePreview');
            const uploadOptions = document.querySelector('.flex.gap-4.mb-4');
            
            previewImage.src = e.target.result;
            imagePreview.classList.remove('hidden');
            
            // Hide upload options when image is previewed
            if (uploadOptions) {
                uploadOptions.classList.add('hidden');
            }
            
            updateButtonStates();
        };
        reader.readAsDataURL(file);
    }
}

// Remove license preview
window.removeLicensePreview = function() {
    document.getElementById('licenseImagePreview').classList.add('hidden');
    document.getElementById('license_image').value = '';
    
    // Show upload options again
    const uploadOptions = document.querySelector('.flex.gap-4.mb-4');
    if (uploadOptions) {
        uploadOptions.classList.remove('hidden');
    }
    
    updateButtonStates();
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
        const uploadOptions = document.querySelector('.flex.gap-4.mb-4');
        
        previewImage.src = dataURL;
        imagePreview.classList.remove('hidden');
        
        // Hide upload options when image is previewed
        if (uploadOptions) {
            uploadOptions.classList.add('hidden');
        }
        
        updateButtonStates();
    }, 'image/jpeg', 0.8);
    
    closeLicenseCameraModal();
}

// Camera modal event listener
document.addEventListener('DOMContentLoaded', function() {
    const cameraModal = document.getElementById('licenseCameraModal');
    if (cameraModal) {
        cameraModal.addEventListener('click', function(e) {
            if (e.target === this) closeLicenseCameraModal();
        });
    }
    
    // Add event listeners for existing vehicle type selects
    const vehicleTypeSelects = document.querySelectorAll('select[name*="[type_id]"]');
    vehicleTypeSelects.forEach(select => {
        select.addEventListener('change', handleVehicleTypeChange);
    });
    
    // Add event listeners for existing plate number inputs
    const plateNumberInputs = document.querySelectorAll('input[name*="[plate_no]"]');
    plateNumberInputs.forEach(input => {
        const vehicleIndex = input.name.match(/vehicles\[(\d+)\]/)[1];
        input.addEventListener('input', function() {
            const plateNumber = this.value.trim();
            
            // Clear the error when user types
            delete availabilityErrors.plateNos[vehicleIndex];
            const errorElement = document.querySelector(`.plate_no_${vehicleIndex}_error`);
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
                checkPlateNumberAvailability(plateNumber, vehicleIndex);
            }
        });
    });
    
    // Initial button state
    updateButtonStates();
});
</script>