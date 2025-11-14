@extends('layouts.guest')

@section('title', 'Reporter Registration - MLUC Sentinel')

@section('content')
<div class="min-h-screen py-4 md:py-8 flex items-center justify-center">
    <div class="max-w-3xl w-full mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6 md:mb-8 text-center">
            <h1 class="text-xl md:text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Reporter Registration</h1>
            <p class="text-sm md:text-base text-[#706f6c] dark:text-[#A1A09A]">Create your reporter account to submit parking violation reports</p>
        </div>

        <!-- Registration Form -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6 md:p-8">
            <form method="POST" action="{{ route('register.post') }}">
                @csrf
                <input type="hidden" name="user_type" value="reporter">
                <input type="hidden" name="has_vehicle" value="0">
                
                <!-- Personal Information -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Personal Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                                First Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="first_name" name="first_name" class="form-input" placeholder="John" value="{{ old('first_name') }}" required>
                            @error('first_name')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                                Last Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="last_name" name="last_name" class="form-input" placeholder="Doe" value="{{ old('last_name') }}" required>
                            @error('last_name')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <label for="email" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input type="email" id="email" name="email" class="form-input" placeholder="reporter@example.com" value="{{ old('email') }}" required>
                        <div class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-1">Use a valid email address for notifications</div>
                        @error('email')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mt-4">
                        <label for="reporter_role_id" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                            Reporter Role <span class="text-red-500">*</span>
                        </label>
                        <select id="reporter_role_id" name="reporter_role_id" class="form-input" required>
                            <option value="">Select Reporter Role</option>
                            @foreach($reporterRoles as $role)
                                <option value="{{ $role->id }}" {{ old('reporter_role_id') == $role->id ? 'selected' : '' }}>
                                    {{ $role->name }}
                                    
                                </option>
                            @endforeach
                        </select>
                        @error('reporter_role_id')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Account Security -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Account Security</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
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
                            @error('password')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
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
                        </div>
                    </div>
                </div>

                <!-- Reporter Information -->
                <div class="mb-8">
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md p-4">
                        <div class="flex">
                            <x-heroicon-s-information-circle class="w-5 h-5 text-blue-500 mr-2" />
                            <div class="text-sm text-blue-700 dark:text-blue-400">
                                <p class="font-medium mb-1">Reporter Account Benefits:</p>
                                <ul class="list-disc list-inside space-y-1 text-xs">
                                    <li>Submit parking violation reports</li>
                                    <li>Access to violation reporting tools</li>
                                    <li>Track your submitted reports</li>
                                    <li>No vehicle registration required</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Registration Info -->
                <div class="mb-6 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-md p-4">
                    <div class="flex">
                        <x-heroicon-s-exclamation-triangle class="w-5 h-5 text-amber-500 mr-2" />
                        <p class="text-sm text-amber-700 dark:text-amber-400">
                            <strong>Approval Required:</strong> Your registration will be reviewed by an administrator. You'll be notified once your account is approved and activated.
                        </p>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-between items-center">
                    <a href="{{ route('register') }}" class="text-sm text-[#706f6c] dark:text-[#A1A09A] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]">
                        ← Back to role selection
                    </a>
                    <button type="submit" class="btn btn-primary">
                        Submit Registration
                    </button>
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
            <p>Your registration has been submitted successfully! An administrator will review your application and notify you via email once it has been approved.</p>
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
// Handle form submission with AJAX
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        const submitButton = form.querySelector('button[type="submit"]');
        const originalText = submitButton.textContent;
        
        // Disable submit button and show loading
        submitButton.disabled = true;
        submitButton.textContent = 'Submitting...';
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Show success modal
                document.getElementById('registrationSuccessModal').classList.remove('hidden');
            } else {
                // Handle validation errors
                if (data.errors) {
                    let errorMessage = 'Please fix the following errors:\n';
                    for (const field in data.errors) {
                        errorMessage += `• ${data.errors[field].join(', ')}\n`;
                    }
                    alert(errorMessage);
                } else {
                    alert(data.message || 'An error occurred. Please try again.');
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
});

function closeRegistrationSuccessModal() {
    document.getElementById('registrationSuccessModal').classList.add('hidden');
    // Redirect to landing page
    window.location.href = '{{ route('landing') }}';
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    const modal = document.getElementById('registrationSuccessModal');
    if (e.target === modal) {
        closeRegistrationSuccessModal();
    }
});
</script>
@endsection
