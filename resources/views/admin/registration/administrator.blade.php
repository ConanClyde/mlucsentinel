@extends('layouts.app')

@section('title', 'Administrator Registration')
@section('page-title', 'Administrator Registration')

@section('content')
<div class="min-h-screen py-4 md:py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6 md:mb-8 text-center">
            <h1 class="text-xl md:text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Administrator Registration</h1>
            <p class="text-sm md:text-base text-[#706f6c] dark:text-[#A1A09A]">Create a new administrator account</p>
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
                        <span id="step-2-label" class="ml-1 md:ml-2 text-xs md:text-sm font-medium text-gray-500 dark:text-gray-400 hidden sm:inline">Account Information</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Registration Form -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A]">
            <form id="administratorRegistrationForm" method="POST" action="{{ route('admin.registration.administrator.store') }}">
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
                    
                    <div class="mt-4 md:mt-6">
                        <label for="role_id" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                            Admin Role <span class="text-red-500">*</span>
                        </label>
                        <select id="role_id" name="role_id" class="form-input" required>
                            <option value="">Select an admin role</option>
                            @foreach($adminRoles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                        <div id="role_id_error" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                </div>

                <!-- Step 2: Account Information -->
                <div id="step-2" class="step-content p-6 hidden">
                    <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-6">Account Information</h3>
                    
                    <div class="space-y-4 md:space-y-6">
                        <div>
                            <label for="email" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                                Email Address <span class="text-red-500">*</span>
                            </label>
                            <input type="email" id="email" name="email" class="form-input" placeholder="example@gmail.com or user@dmmmsu.edu.ph" required>
                            <div id="email_error" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>
                        
                        <div>
                            <label for="password" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                                Password <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" id="password" name="password" class="form-input pr-10" placeholder="••••••••" required minlength="8">
                                <button type="button" class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-[#706f6c] dark:text-[#A1A09A] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC] transition-colors" data-target="password">
                                    <x-heroicon-c-eye class="w-5 h-5 eye-icon" />
                                    <x-heroicon-c-eye-slash class="w-5 h-5 eye-slash-icon hidden" />
                                </button>
                            </div>
                            <div class="text-sm text-[#706f6c] dark:text-[#A1A09A] mt-1">Minimum 8 characters</div>
                            <div id="password_error" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>
                        
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                                Confirm Password <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-input pr-10" placeholder="••••••••" required>
                                <button type="button" class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-[#706f6c] dark:text-[#A1A09A] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC] transition-colors" data-target="password_confirmation">
                                    <x-heroicon-c-eye class="w-5 h-5 eye-icon" />
                                    <x-heroicon-c-eye-slash class="w-5 h-5 eye-slash-icon hidden" />
                                </button>
                            </div>
                            <div id="password_confirmation_error" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>
                    </div>
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
                        <button type="submit" id="submit-form" class="btn btn-primary w-full sm:w-auto" disabled>
                            Register
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="modal-backdrop hidden">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title text-green-600 flex items-center gap-2">
                <x-heroicon-o-check-circle class="modal-icon-success" />
                Administrator Created Successfully!
            </h2>
        </div>
        <div class="modal-body">
            <p>The administrator has been registered successfully and can now access the system.</p>
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
                <x-heroicon-o-x-circle class="modal-icon-error" />
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


// Wait for DOM to load
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, setting up event listeners');
    
    // Step navigation
    const nextButton = document.getElementById('next-step');
    const prevButton = document.getElementById('prev-step');
    
    if (nextButton) {
        nextButton.addEventListener('click', function() {
            console.log('Next button clicked');
            console.log('Validation result:', validateCurrentStep());
            if (validateCurrentStep()) {
                console.log('Moving to step 2');
                showStep(2);
            } else {
                console.log('Validation failed');
            }
        });
    } else {
        console.error('Next button not found');
    }
    
    if (prevButton) {
        prevButton.addEventListener('click', function() {
            showStep(1);
        });
    } else {
        console.error('Previous button not found');
    }
    
    // Form submission
    const form = document.getElementById('administratorRegistrationForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!validateCurrentStep()) {
                return;
            }
            
            const formData = new FormData(this);
            
            fetch('{{ route("admin.registration.administrator.store") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(Object.fromEntries(formData))
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
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
    } else {
        console.error('Form not found');
    }
    
    // Password toggle functionality is handled by global password-toggle.js
    
    // Delayed validation (1 second after user stops typing)
    let firstNameTimeout;
    let lastNameTimeout;
    let roleTimeout;
    let emailTimeout;
    let passwordTimeout;
    let passwordConfirmationTimeout;

    // Step 1 validation
    const firstNameInput = document.getElementById('first_name');
    const lastNameInput = document.getElementById('last_name');
    const roleInput = document.getElementById('role_id');

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

    if (roleInput) {
        roleInput.addEventListener('change', function() {
            clearTimeout(roleTimeout);
            roleTimeout = setTimeout(validateRole, 1000);
            updateButtonStates();
        });
    }

    // Step 2 validation
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const passwordConfirmationInput = document.getElementById('password_confirmation');

    if (emailInput) {
        emailInput.addEventListener('input', function() {
            clearTimeout(emailTimeout);
            emailTimeout = setTimeout(validateEmail, 1000);
            updateButtonStates();
        });
    }

    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            clearTimeout(passwordTimeout);
            passwordTimeout = setTimeout(function() {
                validatePassword();
            }, 1000);
            updateButtonStates();
        });
    }

    if (passwordConfirmationInput) {
        passwordConfirmationInput.addEventListener('input', function() {
            clearTimeout(passwordConfirmationTimeout);
            passwordConfirmationTimeout = setTimeout(validatePasswordConfirmation, 1000);
            updateButtonStates();
        });
    }

    // Individual validation functions
    function validateFirstName() {
        const firstName = firstNameInput.value.trim();
        const firstNameError = document.getElementById('first_name_error');
        
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

    function validateRole() {
        const roleId = roleInput.value;
        
        if (!roleId) {
            showFieldError('role_id', 'Please select an admin role');
            return false;
        } else {
            clearFieldError('role_id');
            return true;
        }
    }


    function validateEmail() {
        const email = emailInput.value.trim();
        
        if (!email) {
            showFieldError('email', 'Email is required');
            return false;
        } else if (!isValidEmail(email)) {
            showFieldError('email', 'Please enter a valid email from Gmail (@gmail.com) or DMMMSU (@dmmmsu.edu.ph)');
            return false;
        } else {
            // Check email availability
            checkEmailAvailability(email);
            return true;
        }
    }

    function checkEmailAvailability(email) {
        fetch('{{ route("admin.registration.administrator.check-email") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email: email })
        })
        .then(response => response.json())
        .then(data => {
            if (data.available) {
                clearFieldError('email');
            } else {
                showFieldError('email', data.message);
            }
        })
        .catch(error => {
            console.error('Email check error:', error);
        });
    }

    function validatePassword() {
        const password = passwordInput.value;
        
        if (!password) {
            showFieldError('password', 'Password is required');
            return false;
        } else if (password.length < 8) {
            showFieldError('password', 'Password must be at least 8 characters');
            return false;
        } else {
            clearFieldError('password');
            return true;
        }
    }

    function validatePasswordConfirmation() {
        const password = passwordInput.value;
        const passwordConfirmation = passwordConfirmationInput.value;
        
        if (!passwordConfirmation) {
            showFieldError('password_confirmation', 'Please confirm your password');
            return false;
        } else if (password !== passwordConfirmation) {
            showFieldError('password_confirmation', 'Passwords do not match');
            return false;
        } else {
            clearFieldError('password_confirmation');
            return true;
        }
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
    }
});

// Email validation function
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@(gmail\.com|dmmmsu\.edu\.ph)$/;
    return emailRegex.test(email);
}

// Function to check if all step 1 fields are valid
function isStep1Valid() {
    const firstName = document.getElementById('first_name').value.trim();
    const lastName = document.getElementById('last_name').value.trim();
    const roleId = document.getElementById('role_id').value;
    
    return firstName && lastName && roleId;
}

// Function to check if all step 2 fields are valid
function isStep2Valid() {
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const passwordConfirmation = document.getElementById('password_confirmation').value;
    
    return email && password && passwordConfirmation && 
           password.length >= 8 && 
           password === passwordConfirmation &&
           isValidEmail(email);
}

// Function to update button states
function updateButtonStates() {
    const nextButton = document.getElementById('next-step');
    const submitButton = document.getElementById('submit-form');
    
    if (currentStep === 1) {
        nextButton.disabled = !isStep1Valid();
    } else if (currentStep === 2) {
        submitButton.disabled = !isStep2Valid();
    }
}

// Function to show step with corrected button logic
function showStep(step) {
    console.log('showStep called with step:', step);
    currentStep = step;
    
    // Hide all steps
    document.querySelectorAll('.step-content').forEach(stepElement => {
        stepElement.classList.add('hidden');
    });
    
    // Show current step
    const currentStepElement = document.getElementById(`step-${currentStep}`);
    console.log('Current step element:', currentStepElement);
    if (currentStepElement) {
        currentStepElement.classList.remove('hidden');
        console.log('Step', currentStep, 'should now be visible');
    } else {
        console.error('Step element not found for step:', currentStep);
    }
    
    // Update step indicators
    for (let i = 1; i <= 2; i++) {
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
    
    if (currentStep === 1) {
        // Step 1: Show step 1 buttons, hide step 2 buttons
        step1Buttons.classList.remove('hidden');
        step2Buttons.classList.add('hidden');
    } else if (currentStep === 2) {
        // Step 2: Hide step 1 buttons, show step 2 buttons
        step1Buttons.classList.add('hidden');
        step2Buttons.classList.remove('hidden');
    }
    
    // Update button states after step change
    updateButtonStates();
}

// Simple validation
function validateCurrentStep() {
    console.log('Validating current step:', currentStep);
    if (currentStep === 1) {
        const firstName = document.getElementById('first_name').value.trim();
        const lastName = document.getElementById('last_name').value.trim();
        const roleId = document.getElementById('role_id').value;
        
        console.log('Step 1 values - First name:', firstName, 'Last name:', lastName, 'Role:', roleId);
        
        if (!firstName || !lastName || !roleId) {
            showErrorModal('Please fill in all required fields');
            return false;
        }
        return true;
    } else if (currentStep === 2) {
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        const passwordConfirmation = document.getElementById('password_confirmation').value;
        
        if (!email || !password || !passwordConfirmation) {
            showErrorModal('Please fill in all required fields');
            return false;
        }
        
        if (!isValidEmail(email)) {
            showErrorModal('Please enter a valid email from Gmail (@gmail.com) or DMMMSU (@dmmmsu.edu.ph)');
            return false;
        }
        
        if (password !== passwordConfirmation) {
            showErrorModal('Passwords do not match');
            return false;
        }
        
        if (password.length < 8) {
            showErrorModal('Password must be at least 8 characters');
            return false;
        }
        
        return true;
    }
    return false;
}


// Reset form to initial state
function resetForm() {
    const form = document.getElementById('administratorRegistrationForm');
    
    // Re-enable all form inputs (in case they were disabled during submission)
    const allInputs = form.querySelectorAll('input, select, textarea, button');
    allInputs.forEach(input => {
        // Don't enable buttons that should be disabled based on form state
        if (input.type === 'submit' || input.id === 'next-step' || input.id === 'submit-form') {
            // Will be handled by updateButtonStates()
            return;
        }
        input.disabled = false;
        // Clear any loading state data attributes
        delete input.dataset.originalDisabled;
    });
    
    // Reset form fields
    document.getElementById('first_name').value = '';
    document.getElementById('last_name').value = '';
    document.getElementById('role_id').value = '';
    document.getElementById('email').value = '';
    document.getElementById('password').value = '';
    document.getElementById('password_confirmation').value = '';
    
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
    
    // Remove loading state from form if FormLoader was used
    if (window.FormLoader && form) {
        form.classList.remove('form-loading');
        delete form.dataset.loading;
    }
    
    // Update button states to reflect empty form
    updateButtonStates();
}

// Password Toggle Functionality (same as profile page)
document.addEventListener('click', function(e) {
    const button = e.target.closest('.toggle-password');
    if (!button) return;
    
    e.preventDefault();
    e.stopPropagation();
    
    const targetId = button.getAttribute('data-target');
    const passwordInput = document.getElementById(targetId);
    
    if (!passwordInput) {
        console.error('Password input not found for target:', targetId);
        return;
    }
    
    // Get all SVG elements (Blade components render as SVG)
    const svgs = button.querySelectorAll('svg');
    const eyeIcon = svgs[0]; // First SVG is the eye icon
    const eyeSlashIcon = svgs[1]; // Second SVG is the eye-slash icon
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        if (eyeIcon) eyeIcon.classList.add('hidden');
        if (eyeSlashIcon) eyeSlashIcon.classList.remove('hidden');
    } else {
        passwordInput.type = 'password';
        if (eyeIcon) eyeIcon.classList.remove('hidden');
        if (eyeSlashIcon) eyeSlashIcon.classList.add('hidden');
    }
});
</script>