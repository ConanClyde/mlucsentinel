// Reset Password Code Validation
document.addEventListener('DOMContentLoaded', function() {
    const codeInput = document.getElementById('code');
    const passwordFields = document.getElementById('passwordFields');
    const validationMessage = document.getElementById('codeValidationMessage');
    const submitButtonContainer = document.getElementById('submitButtonContainer');
    const submitButton = document.querySelector('button[type="submit"]');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('password_confirmation');
    const resendCodeBtn = document.getElementById('resendCodeBtn');
    
    let isValidCode = false;
    let validationTimeout = null;

    if (codeInput) {
        codeInput.addEventListener('input', function() {
            const code = this.value.trim();
            
            // Clear previous timeout
            if (validationTimeout) {
                clearTimeout(validationTimeout);
            }
            
            // Hide validation message, password fields, and submit button while typing
            validationMessage.classList.add('hidden');
            passwordFields.classList.add('hidden', 'opacity-0', 'max-h-0', 'overflow-hidden', 'm-0', 'p-0');
            passwordFields.classList.remove('opacity-100', 'max-h-96');
            submitButtonContainer.classList.add('hidden');
            isValidCode = false;
            
            // Only validate if code is 6 digits
            if (code.length === 6) {
                validationTimeout = setTimeout(() => {
                    validateResetCode(code);
                }, 500); // Wait 500ms after user stops typing
            }
        });
    }

    function validateResetCode(code) {
        const email = document.getElementById('email').value;
        
        if (!email) {
            showValidationMessage('Please enter your email address first.', 'error');
            return;
        }

        // Show loading state
        showValidationMessage('Validating code...', 'loading');
        
        // Make AJAX request to validate code
        fetch('/validate-reset-code', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                               document.querySelector('input[name="_token"]')?.value
            },
            body: JSON.stringify({
                email: email,
                code: code
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.valid) {
                showValidationMessage('✓ Code is valid! You can now set your new password.', 'success');
                passwordFields.classList.remove('hidden', 'opacity-0', 'max-h-0', 'overflow-hidden', 'm-0', 'p-0');
                passwordFields.classList.add('opacity-100', 'max-h-96');
                submitButtonContainer.classList.remove('hidden');
                isValidCode = true;
                
                // Focus on password field
                setTimeout(() => {
                    passwordInput.focus();
                }, 100);
            } else {
                showValidationMessage(data.message || 'Invalid or expired reset code.', 'error');
                passwordFields.classList.add('hidden', 'opacity-0', 'max-h-0', 'overflow-hidden', 'm-0', 'p-0');
                passwordFields.classList.remove('opacity-100', 'max-h-96');
                submitButtonContainer.classList.add('hidden');
                isValidCode = false;
            }
        })
        .catch(error => {
            console.error('Validation error:', error);
            showValidationMessage('Error validating code. Please try again.', 'error');
            passwordFields.classList.add('hidden', 'opacity-0', 'max-h-0', 'overflow-hidden', 'm-0', 'p-0');
            passwordFields.classList.remove('opacity-100', 'max-h-96');
            submitButtonContainer.classList.add('hidden');
            isValidCode = false;
        });
    }

    // Resend Code functionality
    if (resendCodeBtn) {
        resendCodeBtn.addEventListener('click', function() {
            const email = document.getElementById('email').value;
            
            if (!email) {
                showValidationMessage('Please enter your email address first.', 'error');
                return;
            }

            // Show loading state
            resendCodeBtn.textContent = 'Sending...';
            resendCodeBtn.disabled = true;

            // Make AJAX request to resend code
            fetch('/forgot-password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                                   document.querySelector('input[name="_token"]')?.value
                },
                body: `email=${encodeURIComponent(email)}`
            })
            .then(response => {
                if (response.ok) {
                    showValidationMessage('✓ New reset code has been sent to your email!', 'success');
                    // Clear the current code input
                    codeInput.value = '';
                    // Hide password fields and submit button
                    passwordFields.classList.add('hidden', 'opacity-0', 'max-h-0', 'overflow-hidden', 'm-0', 'p-0');
                    passwordFields.classList.remove('opacity-100', 'max-h-96');
                    submitButtonContainer.classList.add('hidden');
                    isValidCode = false;
                } else {
                    showValidationMessage('Failed to send reset code. Please try again.', 'error');
                }
            })
            .catch(error => {
                console.error('Resend error:', error);
                showValidationMessage('Error sending reset code. Please try again.', 'error');
            })
            .finally(() => {
                // Reset button state
                resendCodeBtn.textContent = 'Resend Code';
                resendCodeBtn.disabled = false;
            });
        });
    }

    function showValidationMessage(message, type) {
        validationMessage.textContent = message;
        validationMessage.classList.remove('hidden', 'text-green-600', 'text-red-600', 'text-blue-600');
        
        switch(type) {
            case 'success':
                validationMessage.classList.add('text-green-600', 'dark:text-green-400');
                break;
            case 'error':
                validationMessage.classList.add('text-red-600', 'dark:text-red-400');
                break;
            case 'loading':
                validationMessage.classList.add('text-blue-600', 'dark:text-blue-400');
                break;
        }
    }


    // Form submission validation
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!isValidCode) {
                e.preventDefault();
                showValidationMessage('Please enter a valid reset code first.', 'error');
                return false;
            }
            
            // Additional client-side validation
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            if (password.length < 8) {
                e.preventDefault();
                showValidationMessage('Password must be at least 8 characters long.', 'error');
                passwordInput.focus();
                return false;
            }
            
            if (password !== confirmPassword) {
                e.preventDefault();
                showValidationMessage('Passwords do not match.', 'error');
                confirmPasswordInput.focus();
                return false;
            }
        });
    }

});
