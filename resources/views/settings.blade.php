@extends('layouts.app')

@section('page-title', 'Settings')

@section('content')
<div class="min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Settings</h1>
            <p class="mt-2 text-sm text-[#706f6c] dark:text-[#A1A09A]">Manage your application settings and preferences</p>
        </div>

        <!-- Settings Sections -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Sidebar Navigation -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4">
                <nav class="space-y-1">
                    <button onclick="showSettingsTab('appearance')" id="tab-appearance" class="settings-tab active w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400">
                        <x-heroicon-o-paint-brush class="w-5 h-5 mr-3" />
                        Appearance
                    </button>
                    <button onclick="showSettingsTab('notifications')" id="tab-notifications" class="settings-tab w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]">
                        <x-heroicon-o-bell class="w-5 h-5 mr-3" />
                        Notifications
                    </button>
                    <button onclick="showSettingsTab('college')" id="tab-college" class="settings-tab w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]">
                        <x-heroicon-o-academic-cap class="w-5 h-5 mr-3" />
                        College
                    </button>
                    <button onclick="showSettingsTab('vehicle-type')" id="tab-vehicle-type" class="settings-tab w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]">
                        <x-heroicon-o-truck class="w-5 h-5 mr-3" />
                        Vehicle Type
                    </button>
                    <button onclick="showSettingsTab('security')" id="tab-security" class="settings-tab w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]">
                        <x-heroicon-o-shield-check class="w-5 h-5 mr-3" />
                        Security
                    </button>
                </nav>
            </div>
        </div>

        <!-- Content Area -->
        <div class="lg:col-span-2">
            <!-- Appearance Settings -->
            <div id="content-appearance" class="settings-content bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
                <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Appearance Settings</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Theme</label>
                        <div class="flex items-center space-x-4">
                            <button id="theme-light" onclick="setThemePreference('light')" class="theme-option flex items-center justify-center w-24 h-24 border-2 border-[#e3e3e0] dark:border-[#3E3E3A] rounded-lg hover:border-blue-500 transition-colors">
                                <div class="text-center">
                                    <x-heroicon-o-sun class="w-8 h-8 mx-auto mb-2 text-yellow-500" />
                                    <span class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Light</span>
                                </div>
                            </button>
                            <button id="theme-dark" onclick="setThemePreference('dark')" class="theme-option flex items-center justify-center w-24 h-24 border-2 border-[#e3e3e0] dark:border-[#3E3E3A] rounded-lg hover:border-blue-500 transition-colors">
                                <div class="text-center">
                                    <x-heroicon-o-moon class="w-8 h-8 mx-auto mb-2 text-indigo-500" />
                                    <span class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Dark</span>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notification Settings -->
            <div id="content-notifications" class="settings-content hidden bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
                <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Notification Settings</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Browser Notifications</p>
                            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Show notifications in browser</p>
                            <p id="notification-status" class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-1"></p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="browser-notifications-toggle" class="sr-only peer" onchange="toggleBrowserNotifications(this)">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- College Settings -->
            <div id="content-college" class="settings-content hidden bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
                <!-- Header with Add Button -->
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">College Management</h3>
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Live Updates:</span>
                            <div id="connectionStatus" class="w-3 h-3 rounded-full bg-red-500"></div>
                        </div>
                        <button onclick="openAddCollegeModal()" class="btn btn-primary text-sm">
                            Add
                        </button>
                    </div>
                </div>

                <!-- Colleges Table -->
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-[#161615] border-y border-[#e3e3e0] dark:border-[#3E3E3A]">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">College Name</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Created At</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="college-table-body" class="divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                            <!-- Colleges will be loaded here -->
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                    Loading colleges...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Vehicle Type Settings -->
            <div id="content-vehicle-type" class="settings-content hidden bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
                <!-- Header with Add Button -->
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Vehicle Type Management</h3>
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Live Updates:</span>
                            <div id="vehicle-types-connection-status" class="w-3 h-3 rounded-full bg-red-500"></div>
                        </div>
                        <button onclick="openAddVehicleTypeModal()" class="btn btn-primary text-sm">
                            Add
                        </button>
                    </div>
                </div>

                <!-- Vehicle Types Table -->
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-[#161615] border-y border-[#e3e3e0] dark:border-[#3E3E3A]">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Vehicle Type Name</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Created At</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="vehicle-type-table-body" class="divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                            <!-- Vehicle types will be loaded here -->
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                    Loading vehicle types...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Security Settings -->
            <div id="content-security" class="settings-content hidden bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
                <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Security Settings</h3>
                <div class="space-y-4">
                    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <div class="flex items-start">
                            <x-heroicon-o-information-circle class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-3 mt-0.5" />
                            <div>
                                <p class="text-sm font-medium text-blue-900 dark:text-blue-300">Password Management</p>
                                <p class="text-xs text-blue-700 dark:text-blue-400 mt-1">To change your password, please visit your <a href="{{ route('profile') }}" class="underline">Profile</a> page.</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Two-Factor Authentication</p>
                            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Add an extra layer of security</p>
                        </div>
                        <button class="btn btn-secondary text-xs">Coming Soon</button>
                    </div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Login Activity</p>
                            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">View recent login history</p>
                        </div>
                        <button class="btn btn-secondary text-xs">Coming Soon</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Add College Modal -->
<div id="add-college-modal" class="modal-backdrop hidden" onclick="if(event.target === this) closeAddCollegeModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title">Add New College</h2>
        </div>
        <form id="addCollegeForm">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">College Name <span class="text-red-500">*</span></label>
                    <input type="text" id="modal-college-name" class="form-input" placeholder="Enter college name" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeAddCollegeModal()" class="btn btn-secondary">Cancel</button>
                <button type="button" onclick="addCollege()" class="btn btn-primary">Add College</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Vehicle Type Modal -->
<div id="add-vehicle-type-modal" class="modal-backdrop hidden" onclick="if(event.target === this) closeAddVehicleTypeModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title">Add New Vehicle Type</h2>
        </div>
        <form id="addVehicleTypeForm">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Vehicle Type Name <span class="text-red-500">*</span></label>
                    <input type="text" id="modal-vehicle-type-name" class="form-input" placeholder="Enter vehicle type name" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeAddVehicleTypeModal()" class="btn btn-secondary">Cancel</button>
                <button type="button" onclick="addVehicleType()" class="btn btn-primary">Add Vehicle Type</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit College Modal -->
<div id="edit-college-modal" class="modal-backdrop hidden" onclick="if(event.target === this) closeEditCollegeModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title">Edit College</h2>
        </div>
        <form id="editCollegeForm">
            <div class="modal-body">
                <input type="hidden" id="edit-college-id">
                <div class="form-group">
                    <label class="form-label">College Name <span class="text-red-500">*</span></label>
                    <input type="text" id="edit-college-name" class="form-input" placeholder="Enter college name" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeEditCollegeModal()" class="btn btn-secondary">Cancel</button>
                <button type="button" onclick="updateCollege()" class="btn btn-primary">Update College</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Vehicle Type Modal -->
<div id="edit-vehicle-type-modal" class="modal-backdrop hidden" onclick="if(event.target === this) closeEditVehicleTypeModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title">Edit Vehicle Type</h2>
        </div>
        <form id="editVehicleTypeForm">
            <div class="modal-body">
                <input type="hidden" id="edit-vehicle-type-id">
                <div class="form-group">
                    <label class="form-label">Vehicle Type Name <span class="text-red-500">*</span></label>
                    <input type="text" id="edit-vehicle-type-name" class="form-input" placeholder="Enter vehicle type name" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeEditVehicleTypeModal()" class="btn btn-secondary">Cancel</button>
                <button type="button" onclick="updateVehicleType()" class="btn btn-primary">Update Vehicle Type</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete College Confirmation Modal -->
<div id="delete-college-modal" class="modal-backdrop hidden" onclick="if(event.target === this) closeDeleteCollegeModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title text-red-500 flex items-center gap-2">
                <x-heroicon-o-exclamation-triangle class="modal-icon-error" />
                Delete College
            </h2>
        </div>
        <div class="modal-body">
            <p id="deleteCollegeMessage">Are you sure you want to delete this college? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button onclick="closeDeleteCollegeModal()" class="btn btn-secondary">Cancel</button>
            <button onclick="confirmDeleteCollege()" class="btn btn-danger">Delete</button>
        </div>
    </div>
</div>

<!-- Delete Vehicle Type Confirmation Modal -->
<div id="delete-vehicle-type-modal" class="modal-backdrop hidden" onclick="if(event.target === this) closeDeleteVehicleTypeModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title text-red-500 flex items-center gap-2">
                <x-heroicon-o-exclamation-triangle class="modal-icon-error" />
                Delete Vehicle Type
            </h2>
        </div>
        <div class="modal-body">
            <p id="deleteVehicleTypeMessage">Are you sure you want to delete this vehicle type? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button onclick="closeDeleteVehicleTypeModal()" class="btn btn-secondary">Cancel</button>
            <button onclick="confirmDeleteVehicleType()" class="btn btn-danger">Delete</button>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="modal-backdrop hidden">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title text-green-600 flex items-center gap-2">
                <svg class="modal-icon-success w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span id="successTitle">Success!</span>
            </h2>
        </div>
        <div class="modal-body">
            <p id="successMessage">Operation completed successfully.</p>
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
            <button class="btn btn-secondary" onclick="closeErrorModal()">Close</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set current theme on appearance tab
    const currentTheme = localStorage.getItem('theme') || 'light';
    updateThemeSelection(currentTheme);
    
    // Initialize browser notifications and request permission if not set
    initializeNotifications();
    
    // Load colleges and vehicle types
    loadColleges();
    loadVehicleTypes();
});

// Initialize notifications - request permission on first visit
function initializeNotifications() {
    if (!('Notification' in window)) {
        checkNotificationPermission();
        return;
    }
    
    const preference = localStorage.getItem('browserNotifications');
    
    // If user hasn't made a choice yet, automatically request permission
    if (!preference && Notification.permission === 'default') {
        Notification.requestPermission().then(function(permission) {
            if (permission === 'granted') {
                localStorage.setItem('browserNotifications', 'enabled');
                showNotification('Browser notifications enabled! You can disable them anytime in settings.', 'success');
                checkNotificationPermission();
            } else {
                // User denied, don't ask again
                localStorage.setItem('browserNotifications', 'disabled');
                checkNotificationPermission();
            }
        });
    } else {
        // User has already made a choice, just update the UI
        checkNotificationPermission();
    }
}

// Check and update notification permission status
function checkNotificationPermission() {
    const toggle = document.getElementById('browser-notifications-toggle');
    const statusText = document.getElementById('notification-status');
    
    // Clear previous status classes
    statusText.className = 'text-xs mt-1';
    
    if (!('Notification' in window)) {
        statusText.textContent = 'Browser notifications not supported';
        statusText.classList.add('text-red-600', 'dark:text-red-400');
        toggle.disabled = true;
        return;
    }
    
    const preference = localStorage.getItem('browserNotifications') || 'disabled';
    
    if (Notification.permission === 'granted' && preference === 'enabled') {
        toggle.checked = true;
        statusText.textContent = 'Notifications enabled';
        statusText.classList.add('text-green-600', 'dark:text-green-400');
    } else if (Notification.permission === 'denied') {
        toggle.checked = false;
        statusText.textContent = 'Notifications blocked by browser. Please enable in browser settings.';
        statusText.classList.add('text-red-600', 'dark:text-red-400');
        toggle.disabled = true;
    } else if (Notification.permission === 'default' || preference === 'disabled') {
        toggle.checked = false;
        statusText.textContent = preference === 'disabled' && Notification.permission === 'granted' 
            ? 'Notifications disabled' 
            : 'Click the toggle to enable notifications';
        statusText.classList.add('text-gray-600', 'dark:text-gray-400');
    }
}

// Show notification function
function showNotification(message, type = 'success') {
    // Create notification element
    const notification = document.createElement('div');
    const bgColor = type === 'error' ? 'bg-red-500' : type === 'warning' ? 'bg-yellow-500' : 'bg-green-500';
    notification.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-opacity duration-300`;
    notification.textContent = message;
    
    // Add to body
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Toggle browser notifications
function toggleBrowserNotifications(checkbox) {
    if (!('Notification' in window)) {
        showNotification('Your browser does not support notifications', 'error');
        checkbox.checked = false;
        return;
    }
    
    if (checkbox.checked) {
        // User wants to enable notifications
        if (Notification.permission === 'granted') {
            localStorage.setItem('browserNotifications', 'enabled');
            showNotification('Browser notifications enabled!', 'success');
            checkNotificationPermission();
        } else if (Notification.permission === 'denied') {
            showNotification('Notifications are blocked. Please enable them in your browser settings.', 'error');
            checkbox.checked = false;
        } else {
            // Request permission
            Notification.requestPermission().then(function(permission) {
                if (permission === 'granted') {
                    localStorage.setItem('browserNotifications', 'enabled');
                    showNotification('Browser notifications enabled!', 'success');
                    checkNotificationPermission();
                    
                    // Show a welcome notification
                    new Notification('MLUC Sentinel', {
                        body: 'You will now receive browser notifications!',
                        icon: '/favicon.ico'
                    });
                } else {
                    showNotification('Permission denied. Please enable notifications in your browser.', 'error');
                    checkbox.checked = false;
                    checkNotificationPermission();
                }
            });
        }
    } else {
        // User wants to disable notifications
        localStorage.setItem('browserNotifications', 'disabled');
        showNotification('Browser notifications disabled', 'success');
        checkNotificationPermission();
    }
}

// Modal functions
function openAddCollegeModal() {
    document.getElementById('add-college-modal').classList.remove('hidden');
}

function closeAddCollegeModal() {
    document.getElementById('add-college-modal').classList.add('hidden');
    document.getElementById('modal-college-name').value = '';
}

function openAddVehicleTypeModal() {
    document.getElementById('add-vehicle-type-modal').classList.remove('hidden');
}

function closeAddVehicleTypeModal() {
    document.getElementById('add-vehicle-type-modal').classList.add('hidden');
    document.getElementById('modal-vehicle-type-name').value = '';
}

// Initialize realtime managers
let collegesRealtimeManager = null;
let vehicleTypesRealtimeManager = null;
let collegesLoaded = false;
let vehicleTypesLoaded = false;

// Load colleges and initialize realtime
function loadColleges() {
    if (collegesLoaded) return; // Already loaded
    collegesLoaded = true;
    
    const tableBody = document.getElementById('college-table-body');
    tableBody.innerHTML = '<tr><td colspan="4" class="px-4 py-8 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">Loading colleges...</td></tr>';
    
    fetch('/api/colleges', {
        headers: {
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const colleges = data.data;
            
            // Display colleges in table
            if (colleges.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="3" class="px-4 py-8 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">No colleges found. Click Add button to create one.</td></tr>';
            } else {
                tableBody.innerHTML = colleges.map(college => `
                    <tr class="hover:bg-gray-50 dark:hover:bg-[#161615]" data-id="${college.id}">
                        <td class="px-4 py-3 text-sm text-[#1b1b18] dark:text-[#EDEDEC]">${college.name}</td>
                        <td class="px-4 py-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">${new Date(college.created_at).toLocaleDateString()}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="editCollege(${college.id}, '${college.name.replace(/'/g, "\\'")}')" class="btn-edit" title="Edit">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.829-2.828z"></path>
                                    </svg>
                                </button>
                                <button onclick="deleteCollege(${college.id})" class="btn-delete" title="Delete">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            }
            
            // Initialize realtime manager
            if (window.CollegesRealtime && !collegesRealtimeManager) {
                collegesRealtimeManager = new window.CollegesRealtime();
                collegesRealtimeManager.init(colleges);
            }
        }
    })
    .catch(error => {
        console.error('Error loading colleges:', error);
        tableBody.innerHTML = '<tr><td colspan="3" class="px-4 py-8 text-center text-sm text-red-600 dark:text-red-400">Error loading colleges. Please try again.</td></tr>';
    });
}

// Add new college
function addCollege() {
    const collegeName = document.getElementById('modal-college-name').value.trim();
    
    if (!collegeName) {
        showErrorModal('Please enter a college name');
        return;
    }
    
    fetch('/api/colleges', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ name: collegeName })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccessModal('Success!', 'College added successfully!');
            closeAddCollegeModal();
            // Manually add to table (since broadcast uses toOthers())
            if (collegesRealtimeManager && data.data) {
                collegesRealtimeManager.addCollege(data.data);
            }
        } else {
            showErrorModal(data.message || 'Failed to add college');
        }
    })
    .catch(error => {
        console.error('Error adding college:', error);
        showErrorModal('Error adding college. Please try again.');
    });
}

// Edit college
function editCollege(id, currentName) {
    document.getElementById('edit-college-id').value = id;
    document.getElementById('edit-college-name').value = currentName;
    document.getElementById('edit-college-modal').classList.remove('hidden');
}

function closeEditCollegeModal() {
    document.getElementById('edit-college-modal').classList.add('hidden');
    document.getElementById('edit-college-id').value = '';
    document.getElementById('edit-college-name').value = '';
}

function updateCollege() {
    const id = document.getElementById('edit-college-id').value;
    const name = document.getElementById('edit-college-name').value.trim();
    
    if (!name) {
        showErrorModal('Please enter a college name');
        return;
    }
    
    fetch(`/api/colleges/${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ name: name })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccessModal('Success!', 'College updated successfully!');
            closeEditCollegeModal();
            // Manually update table (since broadcast uses toOthers())
            if (collegesRealtimeManager && data.data) {
                collegesRealtimeManager.updateCollege(data.data);
            }
        } else {
            showErrorModal(data.message || 'Failed to update college');
        }
    })
    .catch(error => {
        console.error('Error updating college:', error);
        showErrorModal('Error updating college. Please try again.');
    });
}

// Delete college
let collegeToDelete = null;

function deleteCollege(id) {
    collegeToDelete = id;
    document.getElementById('delete-college-modal').classList.remove('hidden');
}

function closeDeleteCollegeModal() {
    document.getElementById('delete-college-modal').classList.add('hidden');
    collegeToDelete = null;
}

function confirmDeleteCollege() {
    if (!collegeToDelete) return;
    
    const collegeId = collegeToDelete;
    
    fetch(`/api/colleges/${collegeId}`, {
        method: 'DELETE',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccessModal('Success!', 'College deleted successfully!');
            closeDeleteCollegeModal();
            // Manually remove from table (since broadcast uses toOthers())
            if (collegesRealtimeManager) {
                collegesRealtimeManager.removeCollege({ id: collegeId });
            }
        } else {
            showErrorModal(data.message || 'Failed to delete college');
        }
    })
    .catch(error => {
        console.error('Error deleting college:', error);
        showErrorModal('Error deleting college. Please try again.');
    });
}

// Load vehicle types and initialize realtime
function loadVehicleTypes() {
    if (vehicleTypesLoaded) return; // Already loaded
    vehicleTypesLoaded = true;
    
    const tableBody = document.getElementById('vehicle-type-table-body');
    tableBody.innerHTML = '<tr><td colspan="4" class="px-4 py-8 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">Loading vehicle types...</td></tr>';
    
    fetch('/api/vehicle-types', {
        headers: {
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const vehicleTypes = data.data;
            
            // Display vehicle types in table
            if (vehicleTypes.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="3" class="px-4 py-8 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">No vehicle types found. Click Add button to create one.</td></tr>';
            } else {
                tableBody.innerHTML = vehicleTypes.map(type => `
                    <tr class="hover:bg-gray-50 dark:hover:bg-[#161615]" data-id="${type.id}">
                        <td class="px-4 py-3 text-sm text-[#1b1b18] dark:text-[#EDEDEC]">${type.name}</td>
                        <td class="px-4 py-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">${new Date(type.created_at).toLocaleDateString()}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="editVehicleType(${type.id}, '${type.name.replace(/'/g, "\\'")}')" class="btn-edit" title="Edit">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.829-2.828z"></path>
                                    </svg>
                                </button>
                                <button onclick="deleteVehicleType(${type.id})" class="btn-delete" title="Delete">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            }
            
            // Initialize realtime manager
            if (window.VehicleTypesRealtime && !vehicleTypesRealtimeManager) {
                vehicleTypesRealtimeManager = new window.VehicleTypesRealtime();
                vehicleTypesRealtimeManager.init(vehicleTypes);
            }
        }
    })
    .catch(error => {
        console.error('Error loading vehicle types:', error);
        tableBody.innerHTML = '<tr><td colspan="3" class="px-4 py-8 text-center text-sm text-red-600 dark:text-red-400">Error loading vehicle types. Please try again.</td></tr>';
    });
}

// Add new vehicle type
function addVehicleType() {
    const vehicleTypeName = document.getElementById('modal-vehicle-type-name').value.trim();
    
    if (!vehicleTypeName) {
        showErrorModal('Please enter a vehicle type name');
        return;
    }
    
    fetch('/api/vehicle-types', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ name: vehicleTypeName })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccessModal('Success!', 'Vehicle type added successfully!');
            closeAddVehicleTypeModal();
            // Manually add to table (since broadcast uses toOthers())
            if (vehicleTypesRealtimeManager && data.data) {
                vehicleTypesRealtimeManager.addVehicleType(data.data);
            }
        } else {
            showErrorModal(data.message || 'Failed to add vehicle type');
        }
    })
    .catch(error => {
        console.error('Error adding vehicle type:', error);
        showErrorModal('Error adding vehicle type. Please try again.');
    });
}

// Edit vehicle type
function editVehicleType(id, currentName) {
    document.getElementById('edit-vehicle-type-id').value = id;
    document.getElementById('edit-vehicle-type-name').value = currentName;
    document.getElementById('edit-vehicle-type-modal').classList.remove('hidden');
}

function closeEditVehicleTypeModal() {
    document.getElementById('edit-vehicle-type-modal').classList.add('hidden');
    document.getElementById('edit-vehicle-type-id').value = '';
    document.getElementById('edit-vehicle-type-name').value = '';
}

function updateVehicleType() {
    const id = document.getElementById('edit-vehicle-type-id').value;
    const name = document.getElementById('edit-vehicle-type-name').value.trim();
    
    if (!name) {
        showErrorModal('Please enter a vehicle type name');
        return;
    }
    
    fetch(`/api/vehicle-types/${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ name: name })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccessModal('Success!', 'Vehicle type updated successfully!');
            closeEditVehicleTypeModal();
            // Manually update table (since broadcast uses toOthers())
            if (vehicleTypesRealtimeManager && data.data) {
                vehicleTypesRealtimeManager.updateVehicleType(data.data);
            }
        } else {
            showErrorModal(data.message || 'Failed to update vehicle type');
        }
    })
    .catch(error => {
        console.error('Error updating vehicle type:', error);
        showErrorModal('Error updating vehicle type. Please try again.');
    });
}

// Delete vehicle type
let vehicleTypeToDelete = null;

function deleteVehicleType(id) {
    vehicleTypeToDelete = id;
    document.getElementById('delete-vehicle-type-modal').classList.remove('hidden');
}

function closeDeleteVehicleTypeModal() {
    document.getElementById('delete-vehicle-type-modal').classList.add('hidden');
    vehicleTypeToDelete = null;
}

function confirmDeleteVehicleType() {
    if (!vehicleTypeToDelete) return;
    
    const vehicleTypeId = vehicleTypeToDelete;
    
    fetch(`/api/vehicle-types/${vehicleTypeId}`, {
        method: 'DELETE',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccessModal('Success!', 'Vehicle type deleted successfully!');
            closeDeleteVehicleTypeModal();
            // Manually remove from table (since broadcast uses toOthers())
            if (vehicleTypesRealtimeManager) {
                vehicleTypesRealtimeManager.removeVehicleType({ id: vehicleTypeId });
            }
        } else {
            showErrorModal(data.message || 'Failed to delete vehicle type');
        }
    })
    .catch(error => {
        console.error('Error deleting vehicle type:', error);
        showErrorModal('Error deleting vehicle type. Please try again.');
    });
}

// Show success modal
function showSuccessModal(title, message) {
    document.getElementById('successTitle').textContent = title;
    document.getElementById('successMessage').textContent = message;
    document.getElementById('successModal').classList.remove('hidden');
}

function closeSuccessModal() {
    document.getElementById('successModal').classList.add('hidden');
}

// Show error modal
function showErrorModal(message) {
    document.getElementById('errorMessage').textContent = message;
    document.getElementById('errorModal').classList.remove('hidden');
}

function closeErrorModal() {
    document.getElementById('errorModal').classList.add('hidden');
}

function showSettingsTab(tabName) {
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
    
    // Load data for specific tabs
    if (tabName === 'college') {
        loadColleges();
    } else if (tabName === 'vehicle-type') {
        loadVehicleTypes();
    }
}

function setThemePreference(theme) {
    const html = document.documentElement;
    
    if (theme === 'dark') {
        html.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        html.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
    
    updateThemeSelection(theme);
    
    // Update charts if they exist
    if (typeof updateChartsForTheme === 'function') {
        setTimeout(updateChartsForTheme, 100);
    }
}

function updateThemeSelection(theme) {
    // Remove selection from all theme options
    document.querySelectorAll('.theme-option').forEach(option => {
        option.classList.remove('border-blue-500', 'border-2');
        option.classList.add('border-[#e3e3e0]', 'dark:border-[#3E3E3A]');
    });
    
    // Add selection to current theme
    const selectedTheme = document.getElementById(`theme-${theme}`);
    if (selectedTheme) {
        selectedTheme.classList.add('border-blue-500', 'border-2');
        selectedTheme.classList.remove('border-[#e3e3e0]', 'dark:border-[#3E3E3A]');
    }
}
</script>

<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes fadeOut {
    from { opacity: 1; transform: scale(1); }
    to { opacity: 0; transform: scale(0.95); }
}
@keyframes highlight {
    0%, 100% { background-color: transparent; }
    50% { background-color: rgba(99, 102, 241, 0.1); }
}

.animate-fade-in { animation: fadeIn 0.5s ease-out; }
.animate-fade-out { animation: fadeOut 0.3s ease-out; }
.animate-highlight { animation: highlight 1s ease-out; }
</style>
@endsection

