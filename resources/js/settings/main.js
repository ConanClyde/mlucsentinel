/**
 * Main Settings Initializer
 */

import { initializeAppearance, setupThemeSync } from './appearance.js';
import { initializeNotifications, checkNotificationPermission } from './notifications.js';
import { initializeCollege } from './college.js';
import { initializeProgram } from './program.js';
import { initializeVehicleType } from './vehicle-type.js';
import { initializeLocationType } from './location-type.js';
import { initializeFees } from './fees.js';
import { initializeAdminRoles } from './admin-roles.js';
import { initializeStickerColors } from './sticker-colors.js';
import { initializeStickerRules } from './sticker-rules.js';
import { initializeStakeholders } from './stakeholders.js';
import { initializeSecurity } from './security.js';
import { initReporterRoles } from './reporter-roles.js';

// Real-time instances - will be initialized per tab when data loads
// No need for pre-initialization as each tab handles its own realtime setup

// Show settings tab
export function showSettingsTab(tabName) {
    // Hide all content sections
    document.querySelectorAll('.settings-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active class from all tabs
    document.querySelectorAll('.settings-tab').forEach(tab => {
        tab.classList.remove('active', 'bg-blue-100', 'dark:bg-blue-900', 'text-blue-600', 'dark:text-blue-400');
        tab.classList.add('text-[#706f6c]', 'dark:text-[#A1A09A]', 'hover:bg-gray-100', 'dark:hover:bg-[#2a2a2a]', 'hover:text-[#1b1b18]', 'dark:hover:text-[#EDEDEC]');
    });
    
    // Show selected content
    document.getElementById(`content-${tabName}`).classList.remove('hidden');
    
    // Add active class to selected tab
    const activeTab = document.getElementById(`tab-${tabName}`);
    activeTab.classList.add('active', 'bg-blue-100', 'dark:bg-blue-900', 'text-blue-600', 'dark:text-blue-400');
    activeTab.classList.remove('text-[#706f6c]', 'dark:text-[#A1A09A]', 'hover:bg-gray-100', 'dark:hover:bg-[#2a2a2a]', 'hover:text-[#1b1b18]', 'dark:hover:text-[#EDEDEC]');
    
    // Update URL hash without triggering scroll
    history.replaceState(null, null, `#${tabName}`);
    
    // Load data for specific tabs
    // Each tab initializes its own realtime manager when data loads
    if (tabName === 'college') {
        initializeCollege();
    } else if (tabName === 'program') {
        initializeProgram();
    } else if (tabName === 'vehicle-type') {
        initializeVehicleType();
    } else if (tabName === 'location-type') {
        initializeLocationType();
    } else if (tabName === 'fees') {
        initializeFees();
    } else if (tabName === 'admin-roles') {
        initializeAdminRoles();
    } else if (tabName === 'sticker-colors') {
        initializeStickerColors();
    } else if (tabName === 'sticker-rules') {
        initializeStickerRules();
    } else if (tabName === 'stakeholders') {
        initializeStakeholders();
    } else if (tabName === 'reporters') {
        initReporterRoles();
    }
}

// Restore active tab from URL hash on page load
function restoreActiveTab() {
    const hash = window.location.hash.substring(1); // Remove the # character
    const validTabs = ['appearance', 'notifications', 'college', 'program', 'vehicle-type', 'location-type', 'fees', 'admin-roles', 'sticker-colors', 'sticker-rules', 'stakeholders', 'reporters', 'security'];
    
    if (hash && validTabs.includes(hash)) {
        showSettingsTab(hash);
    }
}

// Modal functions
export function showSuccessModal(title, message) {
    const successTitle = document.getElementById('successTitle');
    const successMessage = document.getElementById('successMessage');
    const successModal = document.getElementById('successModal');
    
    // Only set textContent if elements exist (for pages with different modal structures)
    if (successTitle) {
        successTitle.textContent = title;
    }
    if (successMessage) {
        successMessage.textContent = message;
    }
    if (successModal) {
        successModal.classList.remove('hidden');
    }
}

export function closeSuccessModal() {
    const successModal = document.getElementById('successModal');
    if (successModal) {
        successModal.classList.add('hidden');
    }
}

export function showErrorModal(message) {
    const errorMessage = document.getElementById('errorMessage');
    const errorModal = document.getElementById('errorModal');
    if (errorMessage) {
        errorMessage.textContent = message;
    }
    if (errorModal) {
        errorModal.classList.remove('hidden');
    }
}

export function closeErrorModal() {
    const errorModal = document.getElementById('errorModal');
    if (errorModal) {
        errorModal.classList.add('hidden');
    }
}

// Password toggle functionality
function setupPasswordToggles() {
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const eyeIcon = this.querySelector('.eye-icon');
            const eyeSlashIcon = this.querySelector('.eye-slash-icon');
            
            if (input.type === 'password') {
                input.type = 'text';
                eyeIcon.classList.add('hidden');
                eyeSlashIcon.classList.remove('hidden');
            } else {
                input.type = 'password';
                eyeIcon.classList.remove('hidden');
                eyeSlashIcon.classList.add('hidden');
            }
        });
    });
}

// Initialize everything when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeAppearance();
    setupThemeSync();
    initializeNotifications();
    initializeSecurity();
    setupPasswordToggles();
    restoreActiveTab();
});

// Make functions globally available
// Only set if not already overridden by page-specific scripts
window.showSettingsTab = showSettingsTab;
if (!window.showSuccessModal) {
    window.showSuccessModal = showSuccessModal;
}
if (!window.closeSuccessModal) {
    window.closeSuccessModal = closeSuccessModal;
}
if (!window.showErrorModal) {
    window.showErrorModal = showErrorModal;
}
if (!window.closeErrorModal) {
    window.closeErrorModal = closeErrorModal;
}

