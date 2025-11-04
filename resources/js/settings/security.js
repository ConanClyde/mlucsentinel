/**
 * Security Settings - 2FA and Activity Logs
 */

// ===== TWO-FACTOR AUTHENTICATION FUNCTIONS =====

export function enable2FA() {
    fetch(window.Laravel.routes['settings.2fa.enable'], {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Show QR code modal with SVG image
            document.getElementById('qr-code-container').innerHTML = `<img src="${data.qrCodeUrl}" alt="QR Code" class="w-64 h-64 mx-auto">`;
            document.getElementById('2fa-secret').textContent = data.secret;
            document.getElementById('2fa-verification-code').value = '';
            document.getElementById('enable2FAModal').classList.remove('hidden');
        } else {
            window.showErrorModal(data.message || 'Failed to generate 2FA setup');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.showErrorModal('An error occurred while setting up 2FA');
    });
}

export function closeEnable2FAModal() {
    document.getElementById('enable2FAModal').classList.add('hidden');
    document.getElementById('2fa-verification-code').value = '';
}

export function confirm2FA() {
    const code = document.getElementById('2fa-verification-code').value;
    
    if (!code || code.length !== 6) {
        window.showErrorModal('Please enter a valid 6-digit code');
        return;
    }
    
    fetch(window.Laravel.routes['settings.2fa.confirm'], {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ code })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            closeEnable2FAModal();
            // Show recovery codes
            displayRecoveryCodes(data.recoveryCodes);
            window.showSuccessModal('2FA Enabled', '2FA has been enabled successfully. Please save your recovery codes.');
            // Reload page to update UI
            setTimeout(() => location.reload(), 2000);
        } else {
            window.showErrorModal(data.message || 'Invalid verification code');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.showErrorModal('An error occurred while verifying the code');
    });
}

export function showDisable2FAModal() {
    document.getElementById('disable2FAModal').classList.remove('hidden');
}

export function closeDisable2FAModal() {
    document.getElementById('disable2FAModal').classList.add('hidden');
    const passwordInput = document.getElementById('disable-2fa-password');
    const errorElement = document.getElementById('disable-2fa-password-error');
    passwordInput.value = '';
    clearError(passwordInput, errorElement);
}

export function confirmDisable2FA() {
    const passwordInput = document.getElementById('disable-2fa-password');
    const password = passwordInput.value;
    const errorElement = document.getElementById('disable-2fa-password-error');
    
    // Clear any previous errors
    clearError(passwordInput, errorElement);
    
    if (!password) {
        showError(passwordInput, errorElement, 'Please enter your password');
        return;
    }
    
    fetch(window.Laravel.routes['settings.2fa.disable'], {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ password })
    })
    .then(r => {
        // Check if response is ok, but still parse JSON even for 400 errors
        return r.json().then(data => ({ status: r.status, data }));
    })
    .then(({ status, data }) => {
        if (data.success) {
            closeDisable2FAModal();
            window.showSuccessModal('2FA Disabled', '2FA has been disabled successfully.');
            setTimeout(() => location.reload(), 2000);
        } else {
            showError(passwordInput, errorElement, data.message || 'Failed to disable 2FA. Please check your password.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError(passwordInput, errorElement, 'An error occurred while disabling 2FA');
    });
}

export function show2FARecoveryCodes() {
    document.getElementById('viewRecoveryCodesModal').classList.remove('hidden');
}

export function closeViewRecoveryCodesModal() {
    document.getElementById('viewRecoveryCodesModal').classList.add('hidden');
    const passwordInput = document.getElementById('view-recovery-password');
    const errorElement = document.getElementById('view-recovery-password-error');
    passwordInput.value = '';
    clearError(passwordInput, errorElement);
}

export function confirmViewRecoveryCodes() {
    const passwordInput = document.getElementById('view-recovery-password');
    const password = passwordInput.value;
    const errorElement = document.getElementById('view-recovery-password-error');
    
    // Clear any previous errors
    clearError(passwordInput, errorElement);
    
    if (!password) {
        showError(passwordInput, errorElement, 'Please enter your password');
        return;
    }
    
    fetch(window.Laravel.routes['settings.2fa.recovery-codes'], {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ password })
    })
    .then(r => {
        // Check if response is ok, but still parse JSON even for 400 errors
        return r.json().then(data => ({ status: r.status, data }));
    })
    .then(({ status, data }) => {
        if (data.success) {
            closeViewRecoveryCodesModal();
            displayRecoveryCodes(data.recoveryCodes);
        } else {
            showError(passwordInput, errorElement, data.message || 'Failed to retrieve recovery codes. Please check your password.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError(passwordInput, errorElement, 'An error occurred while retrieving recovery codes');
    });
}

export function displayRecoveryCodes(codes) {
    const container = document.getElementById('recovery-codes-list');
    container.innerHTML = codes.map(code => `
        <div class="p-2 bg-gray-100 dark:bg-gray-800 rounded text-center font-mono text-sm">
            ${code}
        </div>
    `).join('');
    
    // Store codes for copying
    window.currentRecoveryCodes = codes;
    
    document.getElementById('recoveryCodesModal').classList.remove('hidden');
}

export function closeRecoveryCodesModal() {
    document.getElementById('recoveryCodesModal').classList.add('hidden');
}

export function copyRecoveryCodes() {
    if (!window.currentRecoveryCodes) return;
    
    const text = window.currentRecoveryCodes.join('\n');
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    
    navigator.clipboard.writeText(text).then(() => {
        // Change button text temporarily
        button.innerHTML = `
            <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            Copied!
        `;
        
        // Reset after 2 seconds
        setTimeout(() => {
            button.innerHTML = originalText;
        }, 2000);
    }).catch(err => {
        console.error('Failed to copy:', err);
        window.showErrorModal('Failed to copy recovery codes to clipboard');
    });
}

// ===== ACTIVITY LOG FUNCTIONS =====

export function loadActivityLogs() {
    const container = document.getElementById('activity-logs-container');
    if (!container) return;
    
    container.innerHTML = '<div class="text-center py-8 text-sm text-[#706f6c] dark:text-[#A1A09A]">Loading activity logs...</div>';
    
    fetch(window.Laravel.routes['settings.activity-logs'], {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && data.activities.length > 0) {
            container.innerHTML = data.activities.map(activity => `
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-[#161615] rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A]">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                            ${getActivityIcon(activity.action)}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${formatActivityAction(activity.action)}</p>
                            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">
                                ${activity.device || 'Unknown Device'} • ${activity.browser || 'Unknown Browser'} • ${activity.platform || 'Unknown OS'}
                            </p>
                            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">
                                IP: ${activity.ip_address || 'Unknown'} • ${formatDate(activity.created_at)}
                            </p>
                        </div>
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = '<div class="text-center py-8 text-sm text-[#706f6c] dark:text-[#A1A09A]">No activity logs found</div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        container.innerHTML = '<div class="text-center py-8 text-sm text-red-500">Failed to load activity logs</div>';
    });
}

function getActivityIcon(action) {
    const icons = {
        'login': '<svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 3a1 1 0 011 1v12a1 1 0 11-2 0V4a1 1 0 011-1zm7.707 3.293a1 1 0 010 1.414L9.414 9H17a1 1 0 110 2H9.414l1.293 1.293a1 1 0 01-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>',
        'logout': '<svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/></svg>',
        '2fa_enabled': '<svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>',
        '2fa_disabled': '<svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944zM11 14a1 1 0 11-2 0 1 1 0 012 0zm0-7a1 1 0 10-2 0v3a1 1 0 102 0V7z" clip-rule="evenodd"/></svg>',
        'password_change': '<svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>',
    };
    return icons[action] || '<svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>';
}

function formatActivityAction(action) {
    const actions = {
        'login': 'Logged In',
        'logout': 'Logged Out',
        '2fa_enabled': '2FA Enabled',
        '2fa_disabled': '2FA Disabled',
        '2fa_recovery_codes_regenerated': 'Recovery Codes Regenerated',
        'password_change': 'Password Changed',
        'profile_update': 'Profile Updated',
    };
    return actions[action] || action.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    const seconds = Math.floor(diff / 1000);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);
    
    if (seconds < 60) return 'Just now';
    if (minutes < 60) return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
    if (hours < 24) return `${hours} hour${hours > 1 ? 's' : ''} ago`;
    if (days < 7) return `${days} day${days > 1 ? 's' : ''} ago`;
    
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
}

// Initialize security tab event listener
export function initializeSecurity() {
    const securityTab = document.getElementById('tab-security');
    if (securityTab) {
        securityTab.addEventListener('click', function() {
            setTimeout(loadActivityLogs, 100);
        });
    }
    
    // Password Toggle Functionality for settings page (similar to profile page)
    document.addEventListener('click', function(e) {
        // Only handle password toggles within settings modals
        const toggleButton = e.target.closest('.toggle-password');
        if (!toggleButton) return;
        
        // Check if we're in a settings-related container
        const isInSettings = toggleButton.closest('#disable2FAModal, #viewRecoveryCodesModal, #settings');
        if (!isInSettings) return;
        
        e.preventDefault();
        e.stopPropagation();
        
        const targetId = toggleButton.getAttribute('data-target');
        if (!targetId) {
            console.error('Toggle button missing data-target attribute');
            return;
        }
        
        const passwordInput = document.getElementById(targetId);
        if (!passwordInput) {
            console.error('Password input not found for target:', targetId);
            return;
        }
        
        // Find icons - search within the button
        const eyeIcon = toggleButton.querySelector('.eye-icon');
        const eyeSlashIcon = toggleButton.querySelector('.eye-slash-icon');
        
        // Toggle password visibility
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
}

// Helper functions for inline error display (similar to profile page)
function showError(input, errorElement, message) {
    if (!errorElement) return;
    errorElement.textContent = message;
    errorElement.classList.remove('hidden');
    input.classList.remove('border-gray-300', 'dark:border-gray-600', 'dark:border-[#3E3E3A]');
    input.classList.add('border-red-500');
}

function clearError(input, errorElement) {
    if (!errorElement) return;
    errorElement.classList.add('hidden');
    errorElement.textContent = '';
    input.classList.remove('border-red-500');
    input.classList.add('border-gray-300', 'dark:border-gray-600', 'dark:border-[#3E3E3A]');
}

// Make functions globally available
window.enable2FA = enable2FA;
window.closeEnable2FAModal = closeEnable2FAModal;
window.confirm2FA = confirm2FA;
window.showDisable2FAModal = showDisable2FAModal;
window.closeDisable2FAModal = closeDisable2FAModal;
window.confirmDisable2FA = confirmDisable2FA;
window.show2FARecoveryCodes = show2FARecoveryCodes;
window.closeViewRecoveryCodesModal = closeViewRecoveryCodesModal;
window.confirmViewRecoveryCodes = confirmViewRecoveryCodes;
window.displayRecoveryCodes = displayRecoveryCodes;
window.closeRecoveryCodesModal = closeRecoveryCodesModal;
window.copyRecoveryCodes = copyRecoveryCodes;
window.loadActivityLogs = loadActivityLogs;

