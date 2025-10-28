@extends('layouts.app')

@section('content')
<div class="min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">{{ $pageTitle }}</h1>
            <p class="mt-2 text-sm text-[#706f6c] dark:text-[#A1A09A]">Manage your account settings and personal information.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Profile Information Card -->
            <div class="lg:col-span-2">
                <div class="table-container">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-6">Personal Information</h2>
                        
                        <div class="space-y-6">
                            <!-- Avatar and Basic Info -->
                            <div class="flex items-center space-x-6">
                                <div class="w-20 h-20 rounded-full flex items-center justify-center text-white font-bold text-2xl" id="profile-avatar" style="background-color: {{ $avatarColor }}">
                                    {{ strtoupper(substr($user->first_name ?? 'U', 0, 1)) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC] truncate" title="{{ $user->first_name }} {{ $user->last_name }}">
                                        {{ $user->first_name }} {{ $user->last_name }}
                                    </h3>
                                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] truncate" title="{{ $user->email }}">{{ $user->email }}</p>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mt-2
                                        {{ $user->is_active ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200' }}">
                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>

                            <!-- User Details -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="form-label">First Name</label>
                                    <div class="p-3 bg-gray-50 dark:bg-[#1f1f1d] rounded-lg text-[#1b1b18] dark:text-[#EDEDEC]">
                                        {{ $user->first_name }}
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="form-label">Last Name</label>
                                    <div class="p-3 bg-gray-50 dark:bg-[#1f1f1d] rounded-lg text-[#1b1b18] dark:text-[#EDEDEC]">
                                        {{ $user->last_name }}
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="form-label">Email Address</label>
                                    <div class="p-3 bg-gray-50 dark:bg-[#1f1f1d] rounded-lg text-[#1b1b18] dark:text-[#EDEDEC] break-all" title="{{ $user->email }}">
                                        {{ $user->email }}
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="form-label">User Type</label>
                                    <div class="p-3 bg-gray-50 dark:bg-[#1f1f1d] rounded-lg text-[#1b1b18] dark:text-[#EDEDEC]">
                                        {{ ucfirst(str_replace('_', ' ', $user->user_type)) }}
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="form-label">Account Created</label>
                                    <div class="p-3 bg-gray-50 dark:bg-[#1f1f1d] rounded-lg text-[#1b1b18] dark:text-[#EDEDEC]">
                                        {{ $user->created_at->format('M d, Y') }}
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="form-label">Last Updated</label>
                                    <div class="p-3 bg-gray-50 dark:bg-[#1f1f1d] rounded-lg text-[#1b1b18] dark:text-[#EDEDEC]">
                                        {{ $user->updated_at->format('M d, Y') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Account Settings -->
                <div class="table-container">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Account Settings</h3>
                        <div class="space-y-3">
                            <button onclick="openEditProfileModal()" class="w-full btn btn-secondary">
                                Edit Profile
                            </button>
                            <button onclick="openChangePasswordModal()" class="w-full btn btn-secondary">
                                Change Password
                            </button>
                            <button onclick="openDeleteAccountModal()" class="w-full btn btn-danger">
                                Delete Account
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<div id="editProfileModal" class="modal-backdrop hidden">
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="modal-title">Edit Profile</h3>
        </div>
        <div class="modal-body">
            <form id="editProfileForm">
                @csrf
                <div class="form-group">
                    <label class="form-label">First Name</label>
                    <input type="text" name="first_name" class="form-input" value="{{ $user->first_name }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="last_name" class="form-input" value="{{ $user->last_name }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-input" value="{{ $user->email }}" required>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" onclick="closeEditProfileModal()" class="btn btn-secondary">Cancel</button>
            <button type="button" onclick="updateProfile()" class="btn btn-primary">Save Changes</button>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div id="changePasswordModal" class="modal-backdrop hidden">
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="modal-title">Change Password</h3>
        </div>
        <div class="modal-body">
            <form id="changePasswordForm">
                @csrf
                <div class="form-group">
                    <label class="form-label">Current Password</label>
                    <div class="relative">
                        <input type="password" name="current_password" id="current_password" class="form-input pr-10" required placeholder="Enter your current password">
                        <button type="button" class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-[#706f6c] dark:text-[#A1A09A] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC] transition-colors" data-target="current_password">
                            <x-heroicon-c-eye class="w-5 h-5 eye-icon" />
                            <x-heroicon-c-eye-slash class="w-5 h-5 eye-slash-icon hidden" />
                        </button>
                    </div>
                    <div id="current_password_error" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <div class="relative">
                        <input type="password" name="password" id="new_password" class="form-input pr-10" required minlength="8" placeholder="Enter your new password (min. 8 characters)">
                        <button type="button" class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-[#706f6c] dark:text-[#A1A09A] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC] transition-colors" data-target="new_password">
                            <x-heroicon-c-eye class="w-5 h-5 eye-icon" />
                            <x-heroicon-c-eye-slash class="w-5 h-5 eye-slash-icon hidden" />
                        </button>
                    </div>
                    <div id="new_password_error" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm New Password</label>
                    <div class="relative">
                        <input type="password" name="password_confirmation" id="confirm_password" class="form-input pr-10" required minlength="8" placeholder="Confirm your new password">
                        <button type="button" class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-[#706f6c] dark:text-[#A1A09A] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC] transition-colors" data-target="confirm_password">
                            <x-heroicon-c-eye class="w-5 h-5 eye-icon" />
                            <x-heroicon-c-eye-slash class="w-5 h-5 eye-slash-icon hidden" />
                        </button>
                    </div>
                    <div id="confirm_password_error" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" onclick="closeChangePasswordModal()" class="btn btn-secondary">Cancel</button>
            <button type="button" onclick="updatePassword()" class="btn btn-primary" id="changePasswordSubmitBtn" disabled>Change Password</button>
        </div>
    </div>
</div>

<!-- Change Password Success Modal -->
<div id="changePasswordSuccessModal" class="modal-backdrop hidden">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title text-green-600 dark:text-green-400 flex items-center gap-2">
                <x-heroicon-o-check-circle class="modal-icon-success" />
                Password Changed Successfully!
            </h2>
        </div>
        <div class="modal-body">
            <p class="text-[#706f6c] dark:text-[#A1A09A]">Your password has been updated successfully. Please use your new password the next time you log in.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-success" onclick="closeChangePasswordSuccessModal()">Okay</button>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div id="deleteAccountModal" class="modal-backdrop hidden">
    <div class="modal-container">
        <div class="modal-header">
            <div class="flex items-center">
                <div class="modal-icon-error mr-3">
                    <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                </div>
                <h3 class="modal-title">Delete Account</h3>
            </div>
        </div>
        <div class="modal-body">
            <div class="mb-4">
                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mb-4">
                    <strong class="text-red-600 dark:text-red-400">Warning:</strong> This action cannot be undone. 
                    Deleting your account will permanently remove all your data, including:
                </p>
                <ul class="text-sm text-[#706f6c] dark:text-[#A1A09A] list-disc list-inside space-y-1 mb-4">
                    <li>Your profile information</li>
                    <li>All reports you've submitted</li>
                    <li>Your notification history</li>
                    <li>All associated data</li>
                </ul>
                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    If you're sure you want to proceed, please enter your password below to confirm.
                </p>
            </div>
            <form id="deleteAccountForm">
                @csrf
                <div class="form-group">
                    <label class="form-label">Enter your password to confirm</label>
                    <div class="relative">
                        <input type="password" name="password" id="delete_password" class="form-input pr-10" required placeholder="Enter your current password to confirm deletion">
                        <button type="button" class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-[#706f6c] dark:text-[#A1A09A] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC] transition-colors" data-target="delete_password">
                            <x-heroicon-c-eye class="w-5 h-5 eye-icon" />
                            <x-heroicon-c-eye-slash class="w-5 h-5 eye-slash-icon hidden" />
                        </button>
                    </div>
                    <div id="delete_password_error" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" onclick="closeDeleteAccountModal()" class="btn btn-secondary">Cancel</button>
            <button type="button" onclick="deleteAccount()" class="btn btn-danger" id="deleteAccountSubmitBtn" disabled>Delete Account</button>
        </div>
    </div>
</div>

<script>
// Modal Functions
function openEditProfileModal() {
    document.getElementById('editProfileModal').classList.remove('hidden');
}

function closeEditProfileModal() {
    document.getElementById('editProfileModal').classList.add('hidden');
}

function openChangePasswordModal() {
    document.getElementById('changePasswordModal').classList.remove('hidden');
    setupPasswordFormValidation();
}

function closeChangePasswordModal() {
    document.getElementById('changePasswordModal').classList.add('hidden');
    // Reset the form
    document.getElementById('changePasswordForm').reset();
    // Disable the button
    document.getElementById('changePasswordSubmitBtn').disabled = true;
}

function openChangePasswordSuccessModal() {
    document.getElementById('changePasswordSuccessModal').classList.remove('hidden');
}

function closeChangePasswordSuccessModal() {
    document.getElementById('changePasswordSuccessModal').classList.add('hidden');
}

function openDeleteAccountModal() {
    document.getElementById('deleteAccountModal').classList.remove('hidden');
    setupDeleteAccountFormValidation();
}

function closeDeleteAccountModal() {
    document.getElementById('deleteAccountModal').classList.add('hidden');
    // Reset the form
    document.getElementById('deleteAccountForm').reset();
    // Disable the button and reset text
    const submitBtn = document.getElementById('deleteAccountSubmitBtn');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Delete Account';
}

// Form Validation Functions
function setupPasswordFormValidation() {
    const form = document.getElementById('changePasswordForm');
    const submitBtn = document.getElementById('changePasswordSubmitBtn');

    function checkFormValidity() {
        const isCurrentValid = validateCurrentPassword();
        const isNewValid = validateNewPassword();
        const isConfirmValid = validateConfirmPassword();

        if (isCurrentValid && isNewValid && isConfirmValid) {
            submitBtn.disabled = false;
        } else {
            submitBtn.disabled = true;
        }
    }

    // Add validation on input with debounce
    let validationTimeout;
    form.querySelectorAll('input').forEach(input => {
        input.addEventListener('input', function() {
            clearTimeout(validationTimeout);
            validationTimeout = setTimeout(() => {
                // Show errors only after user interaction
                if (input.id === 'current_password' && input.value) {
                    verifyCurrentPassword();
                } else if (input.id === 'new_password' && input.value) {
                    if (input.value.length < 8) {
                        showError(input, document.getElementById('new_password_error'), 'Password must be at least 8 characters long');
                    } else {
                        clearError(input, document.getElementById('new_password_error'));
                    }
                    
                    // Also validate confirm password when new password changes
                    const confirmPassword = document.getElementById('confirm_password').value;
                    if (confirmPassword) {
                        if (input.value !== confirmPassword) {
                            showError(document.getElementById('confirm_password'), document.getElementById('confirm_password_error'), 'Passwords do not match');
                        } else {
                            clearError(document.getElementById('confirm_password'), document.getElementById('confirm_password_error'));
                        }
                    }
                } else if (input.id === 'confirm_password' && input.value) {
                    const newPassword = document.getElementById('new_password').value;
                    if (input.value !== newPassword) {
                        showError(input, document.getElementById('confirm_password_error'), 'Passwords do not match');
                    } else {
                        clearError(input, document.getElementById('confirm_password_error'));
                    }
                }
                checkFormValidity();
            }, 1000);
        });
        input.addEventListener('change', checkFormValidity);
    });

    checkFormValidity();
}

function setupDeleteAccountFormValidation() {
    const form = document.getElementById('deleteAccountForm');
    const submitBtn = document.getElementById('deleteAccountSubmitBtn');
    let debounceTimer;
    let isSubmitting = false;
    
    // Enable/disable button based on password validity
    function checkPasswordValidity() {
        // Don't run verification if we're submitting
        if (isSubmitting) return;
        
        const password = form.querySelector('input[name="password"]').value;
        const errorElement = document.getElementById('delete_password_error');
        
        if (!password || password.length === 0) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Delete Account';
            return;
        }
        
        // Show verifying state
        errorElement.textContent = 'Verifying password...';
        errorElement.classList.remove('hidden');
        
        submitBtn.disabled = true;
        submitBtn.textContent = 'Verifying...';
        
        // Clear previous timer
        if (debounceTimer) {
            clearTimeout(debounceTimer);
        }
        
        // Debounce the API call
        debounceTimer = setTimeout(() => {
            // Check password with server
            fetch('/profile/verify-password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ password: password })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    clearError(document.getElementById('delete_password'), errorElement);
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Delete Account';
                } else {
                    showError(document.getElementById('delete_password'), errorElement, 'Incorrect password');
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Delete Account';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError(document.getElementById('delete_password'), errorElement, 'Error verifying password');
                submitBtn.disabled = true;
                submitBtn.textContent = 'Delete Account';
            });
        }, 500); // Wait 500ms after user stops typing
    }
    
    // Add event listener to password input
    const passwordInput = form.querySelector('input[name="password"]');
    passwordInput.addEventListener('input', checkPasswordValidity);
    passwordInput.addEventListener('change', checkPasswordValidity);
    
    // Add click listener to prevent verification during submit
    submitBtn.addEventListener('click', function() {
        isSubmitting = true;
    });
    
    // Initial check
    checkPasswordValidity();
}

// Update Functions
function updateProfile() {
    const form = document.getElementById('editProfileForm');
    const formData = new FormData(form);
    
    fetch('/profile/update', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeEditProfileModal();
            location.reload(); // Reload to show updated data
        } else {
            alert('Error updating profile: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating profile');
    });
}

function updatePassword() {
    const form = document.getElementById('changePasswordForm');
    const formData = new FormData(form);
    
    // Validate password confirmation
    const password = formData.get('password');
    const passwordConfirmation = formData.get('password_confirmation');
    
    if (password !== passwordConfirmation) {
        alert('New passwords do not match');
        return;
    }
    
    fetch('/profile/change-password', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeChangePasswordModal();
            form.reset();
            openChangePasswordSuccessModal();
        } else {
            alert('Error changing password: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error changing password');
    });
}

function deleteAccount() {
    const form = document.getElementById('deleteAccountForm');
    const passwordInput = form.querySelector('input[name="password"]');
    const password = passwordInput.value;
    
    // Ensure password is provided
    if (!password) {
        alert('Please enter your password to confirm deletion.');
        return;
    }
    
    // Disable the verification to prevent interference
    const submitBtn = document.getElementById('deleteAccountSubmitBtn');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Deleting...';
    
    // Log for debugging
    console.log('Password value:', password);
    
    // Send password directly in JSON body instead of FormData
    fetch('/profile/delete', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
        },
        body: JSON.stringify({ 
            password: password,
            _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        })
    })
    .then(response => {
        return response.json().then(data => {
            return { status: response.status, data: data };
        });
    })
    .then(result => {
        if (result.data.success) {
            // Redirect to landing page (account has been deleted and user logged out)
            window.location.href = '/';
        } else {
            alert('Error deleting account: ' + (result.data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error deleting account');
    });
}

// Form validation functions (global scope)
function validateCurrentPassword() {
    const password = document.getElementById('current_password').value;
    
    if (!password) {
        return false;
    } else {
        return true;
    }
}

function verifyCurrentPassword() {
    const password = document.getElementById('current_password').value;
    const errorElement = document.getElementById('current_password_error');
    
    if (!password) {
        showError(document.getElementById('current_password'), errorElement, 'Current password is required');
        return false;
    }
    
    // Show verifying state
    errorElement.textContent = 'Verifying password...';
    errorElement.classList.remove('hidden');
    
    return fetch('/profile/verify-password', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
        },
        body: JSON.stringify({ password: password })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            clearError(document.getElementById('current_password'), errorElement);
            return true;
        } else {
            showError(document.getElementById('current_password'), errorElement, 'Incorrect password');
            return false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError(document.getElementById('current_password'), errorElement, 'Error verifying password');
        return false;
    });
}

function validateNewPassword() {
    const password = document.getElementById('new_password').value;
    
    if (!password) {
        return false;
    } else if (password.length < 8) {
        return false;
    } else {
        return true;
    }
}

function validateConfirmPassword() {
    const password = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (!confirmPassword) {
        return false;
    } else if (password !== confirmPassword) {
        return false;
    } else {
        return true;
    }
}

function validateDeletePassword() {
    const password = document.getElementById('delete_password').value;
    
    if (!password) {
        return false;
    } else {
        return true;
    }
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

// Close modals when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-backdrop')) {
        e.target.classList.add('hidden');
    }
});

// Real-time updates for profile page
document.addEventListener('DOMContentLoaded', function() {
    // Password Toggle Functionality using event delegation
    document.addEventListener('click', function(e) {
        if (e.target.closest('.toggle-password')) {
            e.preventDefault();
            e.stopPropagation();
            
            const button = e.target.closest('.toggle-password');
            const targetId = button.getAttribute('data-target');
            const passwordInput = document.getElementById(targetId);
            const eyeIcon = button.querySelector('.eye-icon');
            const eyeSlashIcon = button.querySelector('.eye-slash-icon');
            
            if (!passwordInput) {
                console.error('Password input not found for target:', targetId);
                return;
            }
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                if (eyeIcon) eyeIcon.classList.add('hidden');
                if (eyeSlashIcon) eyeSlashIcon.classList.remove('hidden');
            } else {
                passwordInput.type = 'password';
                if (eyeIcon) eyeIcon.classList.remove('hidden');
                if (eyeSlashIcon) eyeSlashIcon.classList.add('hidden');
            }
        }
    });

    if (window.Echo) {
        // Listen for administrator updates
        window.Echo.channel('administrators')
            .listen('.administrator.updated', (event) => {
                if (event.administrator && event.administrator.user_id === {{ auth()->id() }}) {
                    // Update profile page with new data
                    updateProfileDisplay(event.administrator.user);
                }
            });

        // Listen for reporter updates
        window.Echo.channel('reporters')
            .listen('.reporter.updated', (event) => {
                if (event.reporter && event.reporter.user_id === {{ auth()->id() }}) {
                    // Update profile page with new data
                    updateProfileDisplay(event.reporter.user);
                }
            });
    }
});

// Function to update profile display with new data
function updateProfileDisplay(userData) {
    // Update the displayed name
    const nameElement = document.querySelector('h3');
    if (nameElement) {
        nameElement.textContent = `${userData.first_name} ${userData.last_name}`;
    }

    // Update the displayed email
    const emailElement = document.querySelector('p.text-sm.text-\\[\\#706f6c\\]');
    if (emailElement) {
        emailElement.textContent = userData.email;
        emailElement.title = userData.email;
    }

    // Update the profile avatar
    const profileAvatar = document.querySelector('#profile-avatar');
    if (profileAvatar) {
        const newColor = getAvatarColor(userData.first_name + userData.last_name);
        profileAvatar.style.backgroundColor = newColor;
        profileAvatar.textContent = userData.first_name.charAt(0).toUpperCase();
    }

    // Update the form fields in modals
    const firstNameInput = document.querySelector('input[name="first_name"]');
    if (firstNameInput) {
        firstNameInput.value = userData.first_name;
    }

    const lastNameInput = document.querySelector('input[name="last_name"]');
    if (lastNameInput) {
        lastNameInput.value = userData.last_name;
    }

    const emailInput = document.querySelector('input[name="email"]');
    if (emailInput) {
        emailInput.value = userData.email;
    }

    // Update the sidebar user info
    updateSidebarUserInfo(userData);
}

// Function to update sidebar user info
function updateSidebarUserInfo(userData) {
    const sidebarName = document.querySelector('#user-avatar').nextElementSibling.querySelector('p');
    if (sidebarName) {
        sidebarName.textContent = `${userData.first_name} ${userData.last_name}`;
    }

    const sidebarEmail = document.querySelector('#user-avatar').nextElementSibling.querySelector('p:last-child');
    if (sidebarEmail) {
        sidebarEmail.textContent = userData.email;
    }

    // Update avatar initial and color
    const avatar = document.querySelector('#user-avatar');
    if (avatar) {
        const newColor = getAvatarColor(userData.first_name + userData.last_name);
        avatar.style.backgroundColor = newColor;
        avatar.textContent = userData.first_name.charAt(0).toUpperCase();
    }
}

// Function to get avatar color based on first letter of name
function getAvatarColor(name) {
    const colors = [
        '#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#6366F1', 
        '#8B5CF6', '#EC4899', '#14B8A6', '#F97316', '#06B6D4'
    ];
    
    // Use only the first letter for consistent color
    const firstLetter = name.charAt(0).toUpperCase();
    const hash = firstLetter.charCodeAt(0);
    
    return colors[hash % colors.length];
}
</script>
@endsection
