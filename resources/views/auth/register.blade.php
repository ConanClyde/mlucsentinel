<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - MLUC Sentinel</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://api.fontshare.com/v2/css?f[]=satoshi@400,500,700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Dark Mode Script - Run before page renders to prevent flash -->
    <script>
        // Check for saved theme preference or default to light mode
        const savedTheme = localStorage.getItem('theme') || 'light';
        if (savedTheme === 'dark') {
            document.documentElement.classList.add('dark');
        }
    </script>
</head>
<body class="bg-[#FDFDFC] dark:bg-[#161615]">

@section('title', 'Register - MLUC Sentinel')

@section('content')
<div class="min-h-screen py-4 md:py-8 flex items-center justify-center">
    <div class="max-w-5xl w-full mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6 md:mb-8 text-center">
            <h1 class="text-xl md:text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Create Account</h1>
            <p class="text-sm md:text-base text-[#706f6c] dark:text-[#A1A09A]">Join MLUC Sentinel and manage your campus parking</p>
        </div>

        <!-- Progress Steps -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6 mb-4 md:mb-6">
            <div class="flex items-center justify-center overflow-x-auto">
                <div class="flex items-center space-x-2 md:space-x-4">
                    <!-- Step 1 -->
                    <div class="flex items-center">
                        <div id="step-1-indicator" class="w-7 h-7 md:w-8 md:h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs md:text-sm font-medium">
                            1
                        </div>
                        <span id="step-1-label" class="ml-1 md:ml-2 text-xs md:text-sm font-medium text-blue-600 dark:text-blue-400 hidden sm:inline">Basic Information</span>
                    </div>

                    <div class="w-6 md:w-16 h-0.5 bg-gray-300 dark:bg-gray-600"></div>
                    
                    <!-- Step 2 -->
                    <div class="flex items-center">
                        <div id="step-2-indicator" class="w-7 h-7 md:w-8 md:h-8 rounded-full bg-gray-300 dark:bg-gray-600 text-gray-600 dark:text-gray-400 flex items-center justify-center text-xs md:text-sm font-medium">
                            2
                        </div>
                        <span id="step-2-label" class="ml-1 md:ml-2 text-xs md:text-sm font-medium text-gray-500 dark:text-gray-400 hidden sm:inline">Account Security</span>
                    </div>

                    <div class="w-6 md:w-16 h-0.5 bg-gray-300 dark:bg-gray-600"></div>
                    
                    <!-- Step 3 -->
                    <div class="flex items-center">
                        <div id="step-3-indicator" class="w-7 h-7 md:w-8 md:h-8 rounded-full bg-gray-300 dark:bg-gray-600 text-gray-600 dark:text-gray-400 flex items-center justify-center text-xs md:text-sm font-medium">
                            3
                        </div>
                        <span id="step-3-label" class="ml-1 md:ml-2 text-xs md:text-sm font-medium text-gray-500 dark:text-gray-400 hidden sm:inline">Vehicle Information</span>
                    </div>

                    <div class="w-6 md:w-16 h-0.5 bg-gray-300 dark:bg-gray-600"></div>
                    
                    <!-- Step 4 -->
                    <div class="flex items-center">
                        <div id="step-4-indicator" class="w-7 h-7 md:w-8 md:h-8 rounded-full bg-gray-300 dark:bg-gray-600 text-gray-600 dark:text-gray-400 flex items-center justify-center text-xs md:text-sm font-medium">
                            4
                        </div>
                        <span id="step-4-label" class="ml-1 md:ml-2 text-xs md:text-sm font-medium text-gray-500 dark:text-gray-400 hidden sm:inline">Review & Submit</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Registration Form -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A]">
            <form id="registrationForm" method="POST" action="{{ route('register.post') }}" enctype="multipart/form-data">
                @csrf
                
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
                    
                    <div class="mt-4 md:mt-6 grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                        <div>
                            <label for="user_type" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                                User Type <span class="text-red-500">*</span>
                            </label>
                            <select id="user_type" name="user_type" class="form-input" required>
                                <option value="">Select your role</option>
                                <option value="student" {{ old('user_type') == 'student' ? 'selected' : '' }}>Student</option>
                                <option value="staff" {{ old('user_type') == 'staff' ? 'selected' : '' }}>Staff</option>
                                <option value="stakeholder" {{ old('user_type') == 'stakeholder' ? 'selected' : '' }}>Stakeholder</option>
                                <option value="security" {{ old('user_type') == 'security' ? 'selected' : '' }}>Security</option>
                                <option value="reporter" {{ old('user_type') == 'reporter' ? 'selected' : '' }}>Reporter</option>
                            </select>
                            <div id="user_type_error" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>
                        
                        <div>
                            <label for="email" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                                Email Address <span class="text-red-500">*</span>
                            </label>
                            <input type="email" id="email" name="email" class="form-input" placeholder="john.doe@example.com" value="{{ old('email') }}" required>
                            <div id="email_error" class="text-red-500 text-sm mt-1 hidden"></div>
                            <div id="email_help" class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-1 hidden"></div>
                        </div>
                    </div>
                    
                    <!-- Guardian Evidence Upload (Stakeholder Only) -->
                    <div id="guardian_evidence_section" class="mt-4 md:mt-6 hidden">
                        <div>
                            <label for="guardian_evidence" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                                Guardian Evidence <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-md hover:border-gray-400 dark:hover:border-gray-500 transition-colors">
                                <div class="space-y-1 text-center">
                                    <div id="guardian_evidence_preview" class="hidden">
                                        <img id="guardian_evidence_image" src="" alt="Guardian Evidence Preview" class="mx-auto h-32 w-auto rounded-lg shadow-sm">
                                        <div class="mt-2">
                                            <button type="button" id="remove_guardian_evidence" class="text-sm text-red-600 hover:text-red-500">Remove</button>
                                        </div>
                                    </div>
                                    <div id="guardian_evidence_upload" class="text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-sm text-gray-600 dark:text-gray-400">
                                            <label for="guardian_evidence" class="relative cursor-pointer bg-white dark:bg-[#1a1a1a] rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                                <span>Upload a file</span>
                                                <input id="guardian_evidence" name="guardian_evidence" type="file" class="sr-only" accept="image/*,.pdf">
                                            </label>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG, PDF up to 10MB</p>
                                    </div>
                                </div>
                            </div>
                            <div id="guardian_evidence_error" class="text-red-500 text-sm mt-1 hidden"></div>
                            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-1">
                                Upload proof of your children's university ID or enrollment documents
                            </p>
                        </div>
                    </div>
                    
                    <!-- Navigation Buttons -->
                    <div class="flex justify-end mt-6 md:mt-8">
                        <button type="button" id="step-1-next" class="btn btn-primary">
                            Next Step
                            <x-heroicon-o-arrow-right class="w-4 h-4 ml-2" />
                        </button>
                    </div>
                </div>
                
                <!-- Step 2: Account Security -->
                <div id="step-2" class="step-content p-4 md:p-6 hidden">
                    <h3 class="text-base md:text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4 md:mb-6">Account Security</h3>
                    
                    <div class="grid grid-cols-1 gap-4 md:gap-6">
                        <div>
                            <label for="password" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                                Password <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" id="password" name="password" class="form-input pr-10" placeholder="Enter your password" required>
                                <button type="button" class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-[#706f6c] dark:text-[#A1A09A] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC] transition-colors" data-target="password">
                                    <x-heroicon-c-eye class="w-5 h-5 eye-icon" />
                                    <x-heroicon-c-eye-slash class="w-5 h-5 eye-slash-icon hidden" />
                                </button>
                            </div>
                            <div id="password_error" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>
                        
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                                Confirm Password <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-input pr-10" placeholder="Confirm your password" required>
                                <button type="button" class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-[#706f6c] dark:text-[#A1A09A] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC] transition-colors" data-target="password_confirmation">
                                    <x-heroicon-c-eye class="w-5 h-5 eye-icon" />
                                    <x-heroicon-c-eye-slash class="w-5 h-5 eye-slash-icon hidden" />
                                </button>
                            </div>
                            <div id="password_confirmation_error" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>
                    </div>
                    
                    <!-- Navigation Buttons -->
                    <div class="flex justify-between mt-6 md:mt-8">
                        <button type="button" id="step-2-prev" class="btn btn-secondary">
                            <x-heroicon-o-arrow-left class="w-4 h-4 mr-2" />
                            Previous
                        </button>
                        <button type="button" id="step-2-next" class="btn btn-primary">
                            Next Step
                            <x-heroicon-o-arrow-right class="w-4 h-4 ml-2" />
                        </button>
                    </div>
                </div>
                
                <!-- Step 3: Vehicle Information -->
                <div id="step-3" class="step-content p-4 md:p-6 hidden">
                    <h3 class="text-base md:text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4 md:mb-6">Vehicle Information</h3>
                    
                    <div id="vehicle-section">
                        <div class="mb-6">
                            <div class="flex items-center">
                                <input type="checkbox" id="has_vehicle" name="has_vehicle" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="has_vehicle" class="ml-2 text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                                    I have a vehicle to register
                                </label>
                            </div>
                        </div>
                        
                        <div id="vehicle_fields" class="space-y-4 hidden">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                                <div>
                                    <label for="license_plate" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                                        License Plate <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="license_plate" name="license_plate" class="form-input" placeholder="ABC-1234">
                                    <div id="license_plate_error" class="text-red-500 text-sm mt-1 hidden"></div>
                                </div>
                                
                                <div>
                                    <label for="vehicle_make" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                                        Vehicle Make <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="vehicle_make" name="vehicle_make" class="form-input" placeholder="Toyota">
                                    <div id="vehicle_make_error" class="text-red-500 text-sm mt-1 hidden"></div>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                                <div>
                                    <label for="vehicle_model" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                                        Vehicle Model <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="vehicle_model" name="vehicle_model" class="form-input" placeholder="Camry">
                                    <div id="vehicle_model_error" class="text-red-500 text-sm mt-1 hidden"></div>
                                </div>
                                
                                <div>
                                    <label for="vehicle_color" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                                        Vehicle Color <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="vehicle_color" name="vehicle_color" class="form-input" placeholder="White">
                                    <div id="vehicle_color_error" class="text-red-500 text-sm mt-1 hidden"></div>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                                <div>
                                    <label for="vehicle_year" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                                        Vehicle Year <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" id="vehicle_year" name="vehicle_year" class="form-input" placeholder="2020" min="1900" max="{{ date('Y') + 1 }}">
                                    <div id="vehicle_year_error" class="text-red-500 text-sm mt-1 hidden"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Navigation Buttons -->
                    <div class="flex justify-between mt-6 md:mt-8">
                        <button type="button" id="step-3-prev" class="btn btn-secondary">
                            <x-heroicon-o-arrow-left class="w-4 h-4 mr-2" />
                            Previous
                        </button>
                        <button type="button" id="step-3-next" class="btn btn-primary">
                            Next Step
                            <x-heroicon-o-arrow-right class="w-4 h-4 ml-2" />
                        </button>
                    </div>
                </div>
                
                <!-- Step 4: Review & Submit -->
                <div id="step-4" class="step-content p-4 md:p-6 hidden">
                    <h3 class="text-base md:text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4 md:mb-6">Review & Submit</h3>
                    
                    <div class="space-y-6">
                        <!-- Review Summary -->
                        <div class="bg-gray-50 dark:bg-[#2a2a2a] rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-3">Registration Summary</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-[#706f6c] dark:text-[#A1A09A]">Name:</span>
                                    <span id="review-name" class="text-[#1b1b18] dark:text-[#EDEDEC] font-medium"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-[#706f6c] dark:text-[#A1A09A]">Email:</span>
                                    <span id="review-email" class="text-[#1b1b18] dark:text-[#EDEDEC] font-medium"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-[#706f6c] dark:text-[#A1A09A]">User Type:</span>
                                    <span id="review-user-type" class="text-[#1b1b18] dark:text-[#EDEDEC] font-medium"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-[#706f6c] dark:text-[#A1A09A]">Vehicle:</span>
                                    <span id="review-vehicle" class="text-[#1b1b18] dark:text-[#EDEDEC] font-medium"></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Registration Info -->
                        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-md p-4">
                            <div class="flex">
                                <x-heroicon-s-exclamation-triangle class="w-5 h-5 text-amber-500 mr-2" />
                                <p class="text-sm text-amber-700 dark:text-amber-400">
                                    <strong>Approval Required:</strong> Your registration will be reviewed by an administrator. You'll be notified once your account is approved and activated.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Navigation Buttons -->
                    <div class="flex justify-between mt-6 md:mt-8">
                        <button type="button" id="step-4-prev" class="btn btn-secondary">
                            <x-heroicon-o-arrow-left class="w-4 h-4 mr-2" />
                            Previous
                        </button>
                        <button type="submit" id="submit-registration" class="btn btn-primary">
                            Submit Registration
                            <x-heroicon-o-check class="w-4 h-4 ml-2" />
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

        
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentStep = 1;
    const totalSteps = 4;
    
    // Initialize form
    showStep(currentStep);
    
    // Step navigation
    document.getElementById('step-1-next').addEventListener('click', () => validateAndNext(1));
    document.getElementById('step-2-prev').addEventListener('click', () => goToStep(1));
    document.getElementById('step-2-next').addEventListener('click', () => validateAndNext(2));
    document.getElementById('step-3-prev').addEventListener('click', () => goToStep(2));
    document.getElementById('step-3-next').addEventListener('click', () => validateAndNext(3));
    document.getElementById('step-4-prev').addEventListener('click', () => goToStep(3));
    
    // User type change handler
    document.getElementById('user_type').addEventListener('change', function() {
        updateEmailValidation();
        updateVehicleVisibility();
        updateGuardianEvidenceVisibility();
    });
    
    // Stakeholder type change handler for guardian evidence
    const stakeholderTypeSelect = document.querySelector('select[name="stakeholder_type_id"]');
    if (stakeholderTypeSelect) {
        stakeholderTypeSelect.addEventListener('change', function() {
            updateGuardianEvidenceVisibility();
        });
    }
    
    // Vehicle checkbox handler
    document.getElementById('has_vehicle').addEventListener('change', function() {
        const vehicleFields = document.getElementById('vehicle_fields');
        if (this.checked) {
            vehicleFields.classList.remove('hidden');
        } else {
            vehicleFields.classList.add('hidden');
            clearVehicleFields();
        }
    });
    
    // Password toggle functionality
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const target = this.getAttribute('data-target');
            const passwordInput = document.getElementById(target);
            const eyeIcon = this.querySelector('.eye-icon');
            const eyeSlashIcon = this.querySelector('.eye-slash-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon?.classList.add('hidden');
                eyeSlashIcon?.classList.remove('hidden');
            } else {
                passwordInput.type = 'password';
                eyeIcon?.classList.remove('hidden');
                eyeSlashIcon?.classList.add('hidden');
            }
        });
    });
    
    // Guardian evidence file upload handler
    document.getElementById('guardian_evidence').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            handleGuardianEvidenceUpload(file);
        }
    });
    
    // Remove guardian evidence handler
    document.getElementById('remove_guardian_evidence').addEventListener('click', function() {
        clearGuardianEvidence();
    });
    
    function showStep(step) {
        // Hide all steps
        for (let i = 1; i <= totalSteps; i++) {
            document.getElementById(`step-${i}`).classList.add('hidden');
            document.getElementById(`step-${i}-indicator`).classList.remove('bg-blue-600', 'text-white');
            document.getElementById(`step-${i}-indicator`).classList.add('bg-gray-300', 'dark:bg-gray-600', 'text-gray-600', 'dark:text-gray-400');
            document.getElementById(`step-${i}-label`).classList.remove('text-blue-600', 'dark:text-blue-400');
            document.getElementById(`step-${i}-label`).classList.add('text-gray-500', 'dark:text-gray-400');
        }
        
        // Show current step
        document.getElementById(`step-${step}`).classList.remove('hidden');
        document.getElementById(`step-${step}-indicator`).classList.add('bg-blue-600', 'text-white');
        document.getElementById(`step-${step}-indicator`).classList.remove('bg-gray-300', 'dark:bg-gray-600', 'text-gray-600', 'dark:text-gray-400');
        document.getElementById(`step-${step}-label`).classList.add('text-blue-600', 'dark:text-blue-400');
        document.getElementById(`step-${step}-label`).classList.remove('text-gray-500', 'dark:text-gray-400');
        
        if (step === 4) {
            updateReviewSummary();
        }
    }
    
    function goToStep(step) {
        currentStep = step;
        showStep(currentStep);
    }
    
    function validateAndNext(step) {
        if (validateStep(step)) {
            currentStep = step + 1;
            showStep(currentStep);
        }
    }
    
    function validateStep(step) {
        switch (step) {
            case 1:
                return validateBasicInfo();
            case 2:
                return validateSecurity();
            case 3:
                return validateVehicle();
            default:
                return true;
        }
    }
    
    function validateBasicInfo() {
        const firstName = document.getElementById('first_name').value.trim();
        const lastName = document.getElementById('last_name').value.trim();
        const userType = document.getElementById('user_type').value;
        const email = document.getElementById('email').value.trim();
        
        let isValid = true;
        
        if (!firstName) {
            showError('first_name_error', 'First name is required');
            isValid = false;
        } else {
            clearError('first_name_error');
        }
        
        if (!lastName) {
            showError('last_name_error', 'Last name is required');
            isValid = false;
        } else {
            clearError('last_name_error');
        }
        
        if (!userType) {
            showError('user_type_error', 'Please select your user type');
            isValid = false;
        } else {
            clearError('user_type_error');
        }
        
        if (!email) {
            showError('email_error', 'Email is required');
            isValid = false;
        } else if (!isValidEmail(email)) {
            showError('email_error', 'Please enter a valid email address');
            isValid = false;
        } else if (!isValidEmailForUserType(email, userType)) {
            showError('email_error', getEmailValidationMessage(userType));
            isValid = false;
        } else {
            clearError('email_error');
        }
        
        // Validate guardian evidence for stakeholders
        if (userType === 'stakeholder') {
            const guardianEvidence = document.getElementById('guardian_evidence').files[0];
            if (!guardianEvidence) {
                showError('guardian_evidence_error', 'Guardian evidence is required for stakeholders');
                isValid = false;
            } else {
                clearError('guardian_evidence_error');
            }
        }
        
        return isValid;
    }
    
    function validateSecurity() {
        const password = document.getElementById('password').value;
        const passwordConfirmation = document.getElementById('password_confirmation').value;
        
        let isValid = true;
        
        if (!password) {
            showError('password_error', 'Password is required');
            isValid = false;
        } else if (password.length < 8) {
            showError('password_error', 'Password must be at least 8 characters');
            isValid = false;
        } else {
            clearError('password_error');
        }
        
        if (!passwordConfirmation) {
            showError('password_confirmation_error', 'Please confirm your password');
            isValid = false;
        } else if (password !== passwordConfirmation) {
            showError('password_confirmation_error', 'Passwords do not match');
            isValid = false;
        } else {
            clearError('password_confirmation_error');
        }
        
        return isValid;
    }
    
    function validateVehicle() {
        const userType = document.getElementById('user_type').value;
        const hasVehicle = document.getElementById('has_vehicle').checked;
        
        // Reporters don't need vehicle info
        if (userType === 'reporter') {
            return true;
        }
        
        if (!hasVehicle) {
            return true; // Vehicle is optional
        }
        
        const licensePlate = document.getElementById('license_plate').value.trim();
        const vehicleMake = document.getElementById('vehicle_make').value.trim();
        const vehicleModel = document.getElementById('vehicle_model').value.trim();
        const vehicleColor = document.getElementById('vehicle_color').value.trim();
        const vehicleYear = document.getElementById('vehicle_year').value;
        
        let isValid = true;
        
        if (!licensePlate) {
            showError('license_plate_error', 'License plate is required');
            isValid = false;
        } else {
            clearError('license_plate_error');
        }
        
        if (!vehicleMake) {
            showError('vehicle_make_error', 'Vehicle make is required');
            isValid = false;
        } else {
            clearError('vehicle_make_error');
        }
        
        if (!vehicleModel) {
            showError('vehicle_model_error', 'Vehicle model is required');
            isValid = false;
        } else {
            clearError('vehicle_model_error');
        }
        
        if (!vehicleColor) {
            showError('vehicle_color_error', 'Vehicle color is required');
            isValid = false;
        } else {
            clearError('vehicle_color_error');
        }
        
        if (!vehicleYear) {
            showError('vehicle_year_error', 'Vehicle year is required');
            isValid = false;
        } else {
            clearError('vehicle_year_error');
        }
        
        return isValid;
    }
    
    function updateEmailValidation() {
        const userType = document.getElementById('user_type').value;
        const emailHelp = document.getElementById('email_help');
        
        if (userType) {
            emailHelp.textContent = getEmailValidationMessage(userType);
            emailHelp.classList.remove('hidden');
        } else {
            emailHelp.classList.add('hidden');
        }
    }
    
    function updateVehicleVisibility() {
        const userType = document.getElementById('user_type').value;
        const vehicleSection = document.getElementById('vehicle-section');
        
        if (userType === 'reporter') {
            vehicleSection.classList.add('hidden');
        } else {
            vehicleSection.classList.remove('hidden');
        }
    }
    
    function updateGuardianEvidenceVisibility() {
        const userType = document.getElementById('user_type').value;
        const guardianSection = document.getElementById('guardian_evidence_section');
        
        if (userType === 'stakeholder') {
            // Check if stakeholder type requires evidence
            const stakeholderTypeSelect = document.querySelector('select[name="stakeholder_type_id"]');
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
                    clearGuardianEvidence();
                    // Make field optional
                    const guardianInput = document.getElementById('guardian_evidence');
                    if (guardianInput) guardianInput.required = false;
                }
            } else {
                // No stakeholder type selected yet, hide for now
                guardianSection.classList.add('hidden');
                clearGuardianEvidence();
            }
        } else {
            guardianSection.classList.add('hidden');
            clearGuardianEvidence();
        }
    }
    
    function handleGuardianEvidenceUpload(file) {
        // Validate file size (10MB max)
        if (file.size > 10 * 1024 * 1024) {
            showError('guardian_evidence_error', 'File size must be less than 10MB');
            return;
        }
        
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
        if (!allowedTypes.includes(file.type)) {
            showError('guardian_evidence_error', 'Only JPG, PNG, and PDF files are allowed');
            return;
        }
        
        clearError('guardian_evidence_error');
        
        // Show preview for images
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('guardian_evidence_image').src = e.target.result;
                document.getElementById('guardian_evidence_preview').classList.remove('hidden');
                document.getElementById('guardian_evidence_upload').classList.add('hidden');
            };
            reader.readAsDataURL(file);
        } else {
            // For PDFs, show a generic preview
            document.getElementById('guardian_evidence_image').src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTI4IiBoZWlnaHQ9IjEyOCIgdmlld0JveD0iMCAwIDEyOCAxMjgiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxMjgiIGhlaWdodD0iMTI4IiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik0zMiA0MEgzNlY0NEgzMlY0MFoiIGZpbGw9IiM2QjczODAiLz4KPHA+UERGIEZpbGU8L3A+Cjwvc3ZnPgo=';
            document.getElementById('guardian_evidence_preview').classList.remove('hidden');
            document.getElementById('guardian_evidence_upload').classList.add('hidden');
        }
    }
    
    function clearGuardianEvidence() {
        document.getElementById('guardian_evidence').value = '';
        document.getElementById('guardian_evidence_preview').classList.add('hidden');
        document.getElementById('guardian_evidence_upload').classList.remove('hidden');
        clearError('guardian_evidence_error');
    }
    
    function getEmailValidationMessage(userType) {
        switch (userType) {
            case 'student':
                return 'Students should use their institutional email address';
            case 'staff':
                return 'Staff should use their official work email address';
            case 'security':
                return 'Security personnel should use their official work email';
            case 'stakeholder':
                return 'Please use your primary business email address';
            case 'reporter':
                return 'Please use a valid email address for notifications';
            default:
                return '';
        }
    }
    
    function isValidEmailForUserType(email, userType) {
        // Basic validation - can be enhanced based on requirements
        return isValidEmail(email);
    }
    
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    function updateReviewSummary() {
        const firstName = document.getElementById('first_name').value;
        const lastName = document.getElementById('last_name').value;
        const email = document.getElementById('email').value;
        const userType = document.getElementById('user_type').value;
        const hasVehicle = document.getElementById('has_vehicle').checked;
        
        document.getElementById('review-name').textContent = `${firstName} ${lastName}`;
        document.getElementById('review-email').textContent = email;
        document.getElementById('review-user-type').textContent = userType.charAt(0).toUpperCase() + userType.slice(1);
        
        if (userType === 'reporter' || !hasVehicle) {
            document.getElementById('review-vehicle').textContent = 'No vehicle';
        } else {
            const licensePlate = document.getElementById('license_plate').value;
            const vehicleMake = document.getElementById('vehicle_make').value;
            const vehicleModel = document.getElementById('vehicle_model').value;
            document.getElementById('review-vehicle').textContent = `${licensePlate} - ${vehicleMake} ${vehicleModel}`;
        }
    }
    
    function clearVehicleFields() {
        document.getElementById('license_plate').value = '';
        document.getElementById('vehicle_make').value = '';
        document.getElementById('vehicle_model').value = '';
        document.getElementById('vehicle_color').value = '';
        document.getElementById('vehicle_year').value = '';
    }
    
    function showError(errorId, message) {
        const errorElement = document.getElementById(errorId);
        errorElement.textContent = message;
        errorElement.classList.remove('hidden');
    }
    
    function clearError(errorId) {
        const errorElement = document.getElementById(errorId);
        errorElement.textContent = '';
        errorElement.classList.add('hidden');
    }
});
</script>
@endsection
                validateUserType();
            });

            passwordInput.addEventListener('input', function() {
                clearTimeout(passwordTimeout);
                passwordTimeout = setTimeout(validatePassword, 1000);
            });

            passwordConfirmationInput.addEventListener('input', function() {
                clearTimeout(confirmTimeout);
                confirmTimeout = setTimeout(validatePasswordConfirmation, 1000);
            });

            // Form submission validation
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const isFirstNameValid = validateFirstName();
                const isLastNameValid = validateLastName();
                const isEmailValid = validateEmail();
                const isUserTypeValid = validateUserType();
                const isPasswordValid = validatePassword();
                const isPasswordConfirmationValid = validatePasswordConfirmation();
                const isVehicleValid = validateVehicleInfo();
                
                if (isFirstNameValid && isLastNameValid && isEmailValid && isUserTypeValid && isPasswordValid && isPasswordConfirmationValid && isVehicleValid) {
                    form.submit();
                }
            });

            function validateFirstName() {
                const firstName = firstNameInput.value.trim();
                const firstNameError = document.getElementById('first_name_error');
                
                if (!firstName) {
                    showError(firstNameInput, firstNameError, 'First name is required');
                    return false;
                } else if (firstName.length < 2) {
                    showError(firstNameInput, firstNameError, 'First name must be at least 2 characters');
                    return false;
                } else {
                    clearError(firstNameInput, firstNameError);
                    return true;
                }
            }

            function validateLastName() {
                const lastName = lastNameInput.value.trim();
                const lastNameError = document.getElementById('last_name_error');
                
                if (!lastName) {
                    showError(lastNameInput, lastNameError, 'Last name is required');
                    return false;
                } else if (lastName.length < 2) {
                    showError(lastNameInput, lastNameError, 'Last name must be at least 2 characters');
                    return false;
                } else {
                    clearError(lastNameInput, lastNameError);
                    return true;
                }
            }

            function validateEmail() {
                const email = emailInput.value.trim();
                const emailError = document.getElementById('email_error');
                
                if (!email) {
                    showError(emailInput, emailError, 'Email is required');
                    return false;
                } else if (!isValidEmail(email)) {
                    showError(emailInput, emailError, 'Please enter a valid email address');
                    return false;
                } else {
                    clearError(emailInput, emailError);
                    return true;
                }
            }

            function validateUserType() {
                const userType = userTypeInput.value;
                const userTypeError = document.getElementById('user_type_error');
                
                if (!userType) {
                    showError(userTypeInput, userTypeError, 'Please select your user type');
                    return false;
                } else {
                    clearError(userTypeInput, userTypeError);
                    return true;
                }
            }

            function validatePassword() {
                const password = passwordInput.value;
                const passwordError = document.getElementById('password_error');
                
                if (!password) {
                    showError(passwordInput, passwordError, 'Password is required');
                    return false;
                } else if (password.length < 8) {
                    showError(passwordInput, passwordError, 'Password must be at least 8 characters');
                    return false;
                } else {
                    clearError(passwordInput, passwordError);
                    return true;
                }
            }

            function validatePasswordConfirmation() {
                const password = passwordInput.value;
                const passwordConfirmation = passwordConfirmationInput.value;
                const passwordConfirmationError = document.getElementById('password_confirmation_error');
                
                if (!passwordConfirmation) {
                    showError(passwordConfirmationInput, passwordConfirmationError, 'Please confirm your password');
                    return false;
                } else if (password !== passwordConfirmation) {
                    showError(passwordConfirmationInput, passwordConfirmationError, 'Passwords do not match');
                    return false;
                } else {
                    clearError(passwordConfirmationInput, passwordConfirmationError);
                    return true;
                }
            }

            function validateVehicleInfo() {
                if (!hasVehicleCheckbox.checked) {
                    return true; // No vehicle info required
                }

                let isValid = true;

                // Validate license plate
                const licensePlate = licensePlateInput.value.trim();
                const licensePlateError = document.getElementById('license_plate_error');
                if (!licensePlate) {
                    showError(licensePlateInput, licensePlateError, 'License plate is required');
                    isValid = false;
                } else {
                    clearError(licensePlateInput, licensePlateError);
                }

                // Validate vehicle make
                const vehicleMake = vehicleMakeInput.value.trim();
                const vehicleMakeError = document.getElementById('vehicle_make_error');
                if (!vehicleMake) {
                    showError(vehicleMakeInput, vehicleMakeError, 'Vehicle make is required');
                    isValid = false;
                } else {
                    clearError(vehicleMakeInput, vehicleMakeError);
                }

                // Validate vehicle model
                const vehicleModel = vehicleModelInput.value.trim();
                const vehicleModelError = document.getElementById('vehicle_model_error');
                if (!vehicleModel) {
                    showError(vehicleModelInput, vehicleModelError, 'Vehicle model is required');
                    isValid = false;
                } else {
                    clearError(vehicleModelInput, vehicleModelError);
                }

                // Validate vehicle color
                const vehicleColor = vehicleColorInput.value.trim();
                const vehicleColorError = document.getElementById('vehicle_color_error');
                if (!vehicleColor) {
                    showError(vehicleColorInput, vehicleColorError, 'Vehicle color is required');
                    isValid = false;
                } else {
                    clearError(vehicleColorInput, vehicleColorError);
                }

                // Validate vehicle year
                const vehicleYear = vehicleYearInput.value;
                const vehicleYearError = document.getElementById('vehicle_year_error');
                const currentYear = new Date().getFullYear();
                if (!vehicleYear) {
                    showError(vehicleYearInput, vehicleYearError, 'Vehicle year is required');
                    isValid = false;
                } else if (vehicleYear < 1900 || vehicleYear > currentYear + 1) {
                    showError(vehicleYearInput, vehicleYearError, 'Please enter a valid year');
                    isValid = false;
                } else {
                    clearError(vehicleYearInput, vehicleYearError);
                }

                return isValid;
            }

            function isValidEmail(email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }

            function showError(input, errorElement, message) {
                errorElement.textContent = message;
                errorElement.classList.remove('hidden');
                input.classList.remove('border-gray-300', 'dark:border-gray-600');
                input.classList.add('border-red-500');
            }

            function clearError(input, errorElement) {
                errorElement.classList.add('hidden');
                errorElement.textContent = '';
                input.classList.remove('border-red-500');
                input.classList.add('border-gray-300', 'dark:border-gray-600');
            }
        });
    </script>
</body>
</html>
