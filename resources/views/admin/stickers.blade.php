@extends('layouts.app')

@section('page-title', 'Stickers Management')

@section('content')
<div class="space-y-6">
    <!-- Tabs Navigation -->
    <div class="border-b border-[#e3e3e0] dark:border-[#3E3E3A] mb-6">
        <nav class="flex space-x-8">
            <button id="requestTab" class="sticker-tab active" onclick="switchTab('request')">
                <x-heroicon-o-document-plus class="w-5 h-5" />
                Request Sticker
            </button>
            <button id="paymentTab" class="sticker-tab" onclick="switchTab('payment')">
                <x-heroicon-o-credit-card class="w-5 h-5" />
                Payment
                <span id="paymentCount" class="ml-2 px-2 py-0.5 rounded-full text-xs bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200">0</span>
            </button>
            <button id="transactionsTab" class="sticker-tab" onclick="switchTab('transactions')">
                <x-heroicon-o-clipboard-document-list class="w-5 h-5" />
                Transactions
            </button>
        </nav>
    </div>

    <!-- Request Tab Content -->
    <div id="requestContent" class="tab-content">
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
            <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-6">Request New Sticker</h3>
            
            <form id="requestForm" class="space-y-6">
            <div>
                    <label class="form-label">Search User <span class="text-red-500">*</span></label>
                    <input type="text" id="userSearch" class="form-input" placeholder="Search by name or email..." autocomplete="off">
                    <div id="userSearchResults" class="mt-2 hidden bg-white dark:bg-[#1a1a1a] border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-lg shadow-lg max-h-64 overflow-y-auto"></div>
                </div>

                <div id="selectedUserInfo" class="hidden">
                    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                        <h4 class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Selected User</h4>
                        <p id="selectedUserName" class="text-sm text-[#706f6c] dark:text-[#A1A09A]"></p>
                    </div>
                </div>

                <div id="vehicleSelection" class="hidden">
                    <label class="form-label">Select Vehicle(s) <span class="text-red-500">*</span></label>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mb-4">Click on vehicles to select. You can select multiple vehicles.</p>
                    <div id="vehicleCards" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"></div>
                </div>

                <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                    <div class="flex items-start">
                        <x-heroicon-o-information-circle class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-3 mt-0.5 flex-shrink-0" />
                        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                            <strong>Note:</strong> Sticker fee is ₱15.00 per request. Payment record will be created automatically.
                        </p>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" onclick="resetRequestForm()" class="btn btn-secondary">Reset</button>
                    <button type="submit" class="btn btn-primary">Create Request</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Payment Tab Content -->
    <div id="paymentContent" class="tab-content hidden">
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Pending Payments</h3>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <input type="text" id="paymentSearch" class="form-input !h-[38px] !py-1 !px-3 text-sm !w-64" placeholder="Search by user, reference, or vehicle..." autocomplete="off">
                        <button onclick="clearPaymentSearch()" class="btn btn-secondary !h-[38px] !py-1 !px-3 text-sm" title="Clear search">
                            Clear
                        </button>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Live Updates:</span>
                        <div id="paymentConnectionStatus" class="w-3 h-3 rounded-full bg-red-500"></div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
                            <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Reference</th>
                            <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">User</th>
                            <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Vehicles</th>
                            <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Amount</th>
                            <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Request Date</th>
                            <th class="text-center py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="paymentTableBody">
                        <tr>
                            <td colspan="6" class="py-8 px-4 text-center text-[#706f6c] dark:text-[#A1A09A]">
                                Loading payments...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Transactions Tab Content -->
    <div id="transactionsContent" class="tab-content hidden">
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Payment History</h3>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <input type="text" id="transactionsSearch" class="form-input !h-[38px] !py-1 !px-3 text-sm !w-64" placeholder="Search by user, reference, or vehicle..." autocomplete="off">
                        <button onclick="clearTransactionsSearch()" class="btn btn-secondary !h-[38px] !py-1 !px-3 text-sm" title="Clear search">
                            Clear
                        </button>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Show:</span>
                        <select id="transactionsPaginationLimit" class="form-input !h-[38px] !py-1 !px-3 text-sm">
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Live Updates:</span>
                        <div id="transactionsConnectionStatus" class="w-3 h-3 rounded-full bg-red-500"></div>
                    </div>
                    <button onclick="exportTransactionsToCSV()" class="btn btn-csv">CSV</button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
                            <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Reference</th>
                            <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">User</th>
                            <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Vehicle</th>
                            <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Amount</th>
                            <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Status</th>
                            <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Paid Date</th>
                            <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Request Date</th>
                        </tr>
                    </thead>
                    <tbody id="transactionsTableBody">
                        <tr>
                            <td colspan="7" class="py-8 px-4 text-center text-[#706f6c] dark:text-[#A1A09A]">
                                Loading transactions...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Controls -->
            <div id="transactionsPaginationControls" class="flex items-center justify-between mt-6">
                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    Showing <span id="transactionsShowingStart">1</span>-<span id="transactionsShowingEnd">10</span> of <span id="transactionsTotalCount">0</span> transactions
                </p>
                <div class="flex space-x-2">
                    <button id="transactionsPrevPage" class="btn-pagination btn-paginationDisable" onclick="changeTransactionsPage(-1)">
                        <x-heroicon-o-chevron-left class="w-4 h-4" />
                    </button>
                    <div id="transactionsPageNumbers" class="flex space-x-2"></div>
                    <button id="transactionsNextPage" class="btn-pagination btn-paginationArrow" onclick="changeTransactionsPage(1)">
                        <x-heroicon-o-chevron-right class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Receipt Modal -->
<div id="receiptModal" class="modal-backdrop hidden" onclick="if(event.target === this) closeReceiptModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title">Payment Receipt</h2>
        </div>
        <div class="modal-body" id="receiptContent">
            <!-- Receipt content will be loaded here -->
        </div>
        <div class="modal-footer">
            <button onclick="closeReceiptModal()" class="btn btn-secondary">Close</button>
            <button onclick="confirmPayment()" class="btn btn-primary">Confirm Payment</button>
        </div>
    </div>
</div>

<!-- Notification Modal -->
<div id="notificationModal" class="modal-backdrop hidden" onclick="if(event.target === this) closeNotificationModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 id="notificationTitle" class="modal-title flex items-center gap-2">
                <span id="notificationIcon"></span>
                <span id="notificationTitleText"></span>
            </h2>
        </div>
        <div class="modal-body">
            <p id="notificationMessage" class="text-[#706f6c] dark:text-[#A1A09A]"></p>
        </div>
        <div class="modal-footer">
            <button id="notificationCloseBtn" onclick="closeNotificationModal()" class="btn btn-primary">Okay</button>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmationModal" class="modal-backdrop hidden" onclick="if(event.target === this) closeConfirmationModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 id="confirmationTitle" class="modal-title text-yellow-500 flex items-center gap-2">
                <x-heroicon-o-exclamation-triangle class="modal-icon-warning" />
                <span id="confirmationTitleText"></span>
            </h2>
        </div>
        <div class="modal-body">
            <p id="confirmationMessage" class="text-[#706f6c] dark:text-[#A1A09A]"></p>
        </div>
        <div class="modal-footer">
            <button onclick="closeConfirmationModal()" class="btn btn-secondary">Cancel</button>
            <button id="confirmationButton" class="btn btn-primary">Confirm</button>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="modal-backdrop hidden" onclick="if(event.target === this) closeDeleteConfirmModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title text-red-500 flex items-center gap-2">
                <x-heroicon-o-exclamation-triangle class="modal-icon-error" />
                Delete Payment Request
            </h2>
        </div>
        <div class="modal-body">
            <p class="text-[#706f6c] dark:text-[#A1A09A]">Are you sure you want to delete this payment request? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button onclick="closeDeleteConfirmModal()" class="btn btn-secondary">Cancel</button>
            <button onclick="confirmDelete()" class="btn btn-danger">Delete</button>
        </div>
    </div>
</div>

<!-- Batch Vehicles View Modal -->
<div id="batchViewModal" class="modal-backdrop hidden" onclick="if(event.target === this) closeBatchViewModal()">
    <div class="modal-container-wide">
        <div class="modal-header">
            <h2 class="modal-title">Sticker Request Details</h2>
        </div>
        <div class="modal-body">
            <div id="batchViewContent" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Vehicle cards will be loaded here -->
            </div>
        </div>
        <div class="modal-footer">
            <!-- Footer buttons will be dynamically updated by JavaScript -->
            <button onclick="closeBatchViewModal()" class="btn btn-secondary">Close</button>
        </div>
    </div>
</div>

<!-- Transaction Receipt Modal -->
<div id="transactionReceiptModal" class="modal-backdrop hidden" onclick="if(event.target === this) closeTransactionReceipt()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title">Payment Receipt</h2>
        </div>
        <div class="modal-body" id="transactionReceiptContent">
            <!-- Receipt content will be loaded here -->
        </div>
        <div class="modal-footer">
            <button onclick="closeTransactionReceipt()" class="btn btn-secondary">Close</button>
            <button onclick="printReceipt()" class="btn btn-primary">Print Receipt</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Set current user ID for action tracking
window.currentUserId = {{ auth()->id() }};

let currentTab = 'request';
let selectedUser = null;
let selectedVehicles = [];
let currentPaymentId = null;
let confirmationCallback = null;

// Transactions pagination
let transactionsCurrentPage = 1;
let transactionsItemsPerPage = 10;
let allTransactions = [];

// Notification and Confirmation Helpers
function showNotification(title, message, type = 'info') {
    const titleElement = document.getElementById('notificationTitle');
    const titleTextElement = document.getElementById('notificationTitleText');
    const iconElement = document.getElementById('notificationIcon');
    const messageElement = document.getElementById('notificationMessage');
    const closeBtn = document.getElementById('notificationCloseBtn');
    
    titleTextElement.textContent = title;
    messageElement.textContent = message;
    
    // Set icon and color based on type
    titleElement.className = 'modal-title flex items-center gap-2';
    
    if (type === 'error' || title === 'Error' || title === 'Validation Error') {
        titleElement.classList.add('text-red-500');
        iconElement.innerHTML = '<svg class="w-6 h-6 modal-icon-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>';
        closeBtn.className = 'btn btn-danger';
    } else if (type === 'success' || title === 'Success') {
        titleElement.classList.add('text-green-600');
        iconElement.innerHTML = '<svg class="w-6 h-6 modal-icon-success" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
        closeBtn.className = 'btn btn-success';
    } else if (type === 'warning' || title === 'Warning') {
        titleElement.classList.add('text-yellow-500');
        iconElement.innerHTML = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>';
        closeBtn.className = 'btn btn-secondary';
    } else {
        titleElement.classList.add('text-blue-500');
        iconElement.innerHTML = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
        closeBtn.className = 'btn btn-primary';
    }
    
    document.getElementById('notificationModal').classList.remove('hidden');
}

function closeNotificationModal() {
    document.getElementById('notificationModal').classList.add('hidden');
}

function showConfirmation(title, message, callback) {
    document.getElementById('confirmationTitleText').textContent = title;
    document.getElementById('confirmationMessage').textContent = message;
    confirmationCallback = callback;
    document.getElementById('confirmationModal').classList.remove('hidden');
    
    // Set up the confirmation button
    const confirmBtn = document.getElementById('confirmationButton');
    confirmBtn.onclick = function() {
        closeConfirmationModal();
        if (confirmationCallback) {
            confirmationCallback();
        }
    };
}

function closeConfirmationModal() {
    document.getElementById('confirmationModal').classList.add('hidden');
    confirmationCallback = null;
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize search functionality
    initializeSearch();
    
    // Load initial data
    loadPayments();
    loadTransactions();

    // Setup user search with debounce
    let searchTimeout;
    document.getElementById('userSearch').addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        const search = e.target.value.trim();
        
        if (search.length < 2) {
            document.getElementById('userSearchResults').classList.add('hidden');
            return;
        }

        searchTimeout = setTimeout(() => {
            searchUsers(search);
        }, 300);
    });

    // Handle request form submission
    document.getElementById('requestForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitRequest();
    });

    // Transactions pagination limit
    document.getElementById('transactionsPaginationLimit').addEventListener('change', function() {
        transactionsItemsPerPage = parseInt(this.value);
        transactionsCurrentPage = 1;
        applyTransactionsPagination();
    });
});

function switchTab(tab) {
    currentTab = tab;
    
    // Hide all content
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.sticker-tab').forEach(el => el.classList.remove('active'));
    
    // Show selected content
    document.getElementById(tab + 'Content').classList.remove('hidden');
    document.getElementById(tab + 'Tab').classList.add('active');
    
    // Load data if needed
    if (tab === 'payment') {
        loadPayments();
    } else if (tab === 'transactions') {
        loadTransactions();
    }
}

function searchUsers(search) {
    fetch(`/stickers/search-users?search=${encodeURIComponent(search)}`, {
        headers: {
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(users => {
        displaySearchResults(users);
    })
    .catch(error => {
        console.error('Error searching users:', error);
    });
}

function displaySearchResults(users) {
    const resultsDiv = document.getElementById('userSearchResults');
    
    if (users.length === 0) {
        resultsDiv.innerHTML = '<p class="text-sm text-[#706f6c] dark:text-[#A1A09A] p-4">No users found</p>';
        resultsDiv.classList.remove('hidden');
        return;
    }

    resultsDiv.innerHTML = users.map(user => `
        <div class="p-3 hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer border-b border-[#e3e3e0] dark:border-[#3E3E3A] last:border-b-0" onclick='selectUser(${JSON.stringify(user)})'>
            <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${user.first_name} ${user.last_name}</p>
            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">${user.email} · ${user.user_type}</p>
            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-1">${user.vehicles.length} vehicle(s)</p>
        </div>
    `).join('');
    
    resultsDiv.classList.remove('hidden');
}

function selectUser(user) {
    selectedUser = user;
    selectedVehicles = []; // Reset selected vehicles
    
    // Hide search results
    document.getElementById('userSearchResults').classList.add('hidden');
    document.getElementById('userSearch').value = `${user.first_name} ${user.last_name}`;
    
    // Show selected user info
    document.getElementById('selectedUserName').textContent = `${user.first_name} ${user.last_name} (${user.email})`;
    document.getElementById('selectedUserInfo').classList.remove('hidden');
    
    // Populate vehicle cards
    const vehicleCardsDiv = document.getElementById('vehicleCards');
    
    if (user.vehicles.length === 0) {
        vehicleCardsDiv.innerHTML = '<p class="col-span-full text-center text-[#706f6c] dark:text-[#A1A09A] py-8">No vehicles found for this user</p>';
    } else {
        vehicleCardsDiv.innerHTML = user.vehicles.map(vehicle => `
            <div class="vehicle-sticker-card" data-vehicle-id="${vehicle.id}" onclick="toggleVehicleSelection(${vehicle.id})">
                <div class="checkbox-indicator">
                    <svg class="checkbox-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div class="sticker-image-container">
                    ${vehicle.sticker_image ? `<img src="${vehicle.sticker_image}" alt="Sticker">` : '<div class="text-center text-[#706f6c] dark:text-[#A1A09A] py-8">No Sticker</div>'}
                </div>
                <div class="vehicle-info">
                    <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${vehicle.type_name || 'Unknown Type'}</p>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">${vehicle.plate_no || 'No Plate'}</p>
                </div>
            </div>
        `).join('');
    }
    
    document.getElementById('vehicleSelection').classList.remove('hidden');
}

function toggleVehicleSelection(vehicleId) {
    const card = document.querySelector(`.vehicle-sticker-card[data-vehicle-id="${vehicleId}"]`);
    const index = selectedVehicles.indexOf(vehicleId);
    
    if (index > -1) {
        // Deselect
        selectedVehicles.splice(index, 1);
        card.classList.remove('selected');
    } else {
        // Select
        selectedVehicles.push(vehicleId);
        card.classList.add('selected');
    }
}

function resetRequestForm() {
    selectedUser = null;
    selectedVehicles = [];
    document.getElementById('userSearch').value = '';
    document.getElementById('userSearchResults').classList.add('hidden');
    document.getElementById('selectedUserInfo').classList.add('hidden');
    document.getElementById('vehicleSelection').classList.add('hidden');
}

function submitRequest() {
    if (!selectedUser) {
        showNotification('Validation Error', 'Please select a user', 'error');
        return;
    }
    
    if (selectedVehicles.length === 0) {
        showNotification('Validation Error', 'Please select at least one vehicle', 'error');
        return;
    }

    fetch('/stickers/request', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            user_id: selectedUser.id,
            vehicle_ids: selectedVehicles,
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Success', 'Sticker request created successfully!');
            resetRequestForm();
            switchTab('payment');
        } else {
            showNotification('Error', data.message || 'Failed to create request');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error', 'An error occurred while creating the request');
    });
}

function loadPayments() {
    const searchInput = document.getElementById('paymentSearch');
    const searchTerm = searchInput ? searchInput.value : '';
    
    let url = '/stickers/data?tab=payment';
    if (searchTerm.trim()) {
        url += `&search=${encodeURIComponent(searchTerm.trim())}`;
    }
    
    fetch(url, {
        headers: {
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(payments => {
        displayPayments(payments);
        updatePaymentCount(payments.length);
    })
    .catch(error => {
        console.error('Error loading payments:', error);
    });
}

function displayPayments(payments) {
    const tbody = document.getElementById('paymentTableBody');
    
    if (payments.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="py-8 px-4 text-center text-[#706f6c] dark:text-[#A1A09A]">No pending payments</td></tr>';
        return;
    }

    tbody.innerHTML = payments.map(payment => `
        <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A] hover:bg-gray-50 dark:hover:bg-[#161615]">
            <td class="py-2 px-3">
                <span class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${payment.reference || 'N/A'}</span>
            </td>
            <td class="py-2 px-3">
                <div>
                    <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${payment.user?.first_name} ${payment.user?.last_name}</p>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">${payment.user?.user_type || 'N/A'}</p>
                </div>
            </td>
            <td class="py-2 px-3">
                ${payment.vehicle_count > 1 ? `
                    <p class="text-sm font-medium text-blue-600 dark:text-blue-400">${payment.vehicle_count} Vehicles</p>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Click view to see all</p>
                ` : `
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">${payment.vehicle?.type?.name || 'N/A'}</p>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">${payment.vehicle?.plate_no || payment.vehicle?.color + '-' + payment.vehicle?.number}</p>
                `}
            </td>
            <td class="py-2 px-3">
                <span class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">₱${parseFloat(payment.amount).toFixed(2)}</span>
            </td>
            <td class="py-2 px-3">
                <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">${new Date(payment.created_at).toLocaleDateString()}</span>
            </td>
            <td class="py-2 px-3">
                <div class="flex items-center justify-center gap-2">
                    <button onclick="showReceipt(${payment.id})" class="btn-view" title="View Receipt">
                        <x-heroicon-s-eye class="w-4 h-4" />
                    </button>
                    <button onclick="deletePaymentRequest(${payment.id})" class="btn-delete" title="Delete Request">
                        <x-heroicon-s-trash class="w-4 h-4" />
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function showReceipt(paymentId) {
    currentPaymentId = paymentId;
    
    fetch(`/stickers/data?tab=payment`, {
        headers: {
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(payments => {
        const payment = payments.find(p => p.id === paymentId);
        if (!payment) {
            showNotification('Error', 'Payment not found', 'error');
            return;
        }
        
        // Always use batch view modal for consistency
        showBatchVehicles(payment);
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error', 'Failed to load payment details');
    });
}

function closeReceiptModal() {
    document.getElementById('receiptModal').classList.add('hidden');
    currentPaymentId = null;
}

function confirmPayment() {
    if (!currentPaymentId) return;

    const paymentIdToShow = currentPaymentId;

    // Mark this action before making the request
    if (!window._recentActions) {
        window._recentActions = new Map();
    }
    window._recentActions.set(currentPaymentId, Date.now());

    fetch(`/stickers/${currentPaymentId}/pay`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeReceiptModal();
            closeBatchViewModal();
            loadPayments();
            
            // Load transactions and wait for it to complete before showing receipt
            fetch('/stickers/data?tab=transactions', {
                headers: {
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(transactions => {
                allTransactions = transactions;
                applyTransactionsPagination();
                
                // Switch to transactions tab and show receipt
                switchTab('transactions');
                setTimeout(() => {
                    showTransactionReceipt(paymentIdToShow);
                    showNotification('Success', 'Payment confirmed successfully!', 'success');
                }, 100);
            });
        } else {
            showNotification('Error', data.message || 'Failed to confirm payment', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error', 'An error occurred while confirming payment', 'error');
    });
}

let currentDeletePaymentId = null;

function deletePaymentRequest(paymentId) {
    currentDeletePaymentId = paymentId;
    document.getElementById('deleteConfirmModal').classList.remove('hidden');
}

function confirmDelete() {
    if (!currentDeletePaymentId) {
        showNotification('Error', 'No payment selected for deletion', 'error');
        return;
    }

    // Mark this action before making the request
    if (!window._recentActions) {
        window._recentActions = new Map();
    }
    window._recentActions.set(currentDeletePaymentId, Date.now());
    
    fetch(`/stickers/${currentDeletePaymentId}/cancel`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Success', 'Payment request deleted successfully', 'success');
            loadPayments();
            loadTransactions();
        } else {
            showNotification('Error', data.message || 'Failed to delete payment request', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error', 'An error occurred while deleting payment request', 'error');
    })
    .finally(() => {
        closeDeleteConfirmModal();
    });
}

function closeDeleteConfirmModal() {
    document.getElementById('deleteConfirmModal').classList.add('hidden');
    currentDeletePaymentId = null;
}

function loadTransactions() {
    const searchInput = document.getElementById('transactionsSearch');
    const searchTerm = searchInput ? searchInput.value : '';
    
    let url = '/stickers/data?tab=transactions';
    if (searchTerm.trim()) {
        url += `&search=${encodeURIComponent(searchTerm.trim())}`;
    }
    
    fetch(url, {
        headers: {
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(transactions => {
        allTransactions = transactions;
        transactionsCurrentPage = 1; // Reset to first page when searching
        applyTransactionsPagination();
    })
    .catch(error => {
        console.error('Error loading transactions:', error);
    });
}

function applyTransactionsPagination() {
    const totalCount = allTransactions.length;
    const totalPages = Math.ceil(totalCount / transactionsItemsPerPage);
    const startIndex = (transactionsCurrentPage - 1) * transactionsItemsPerPage;
    const endIndex = Math.min(startIndex + transactionsItemsPerPage, totalCount);
    
    const paginatedTransactions = allTransactions.slice(startIndex, endIndex);
    
    displayTransactions(paginatedTransactions);
    updateTransactionsPaginationControls(startIndex + 1, endIndex, totalCount, totalPages);
}

function displayTransactions(transactions) {
    const tbody = document.getElementById('transactionsTableBody');
    
    if (transactions.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="py-8 px-4 text-center text-[#706f6c] dark:text-[#A1A09A]">No transactions found</td></tr>';
        return;
    }

    tbody.innerHTML = transactions.map(payment => {
        let statusClass = '';
        let statusText = '';
        
        switch(payment.status) {
            case 'paid':
                statusClass = 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200';
                statusText = 'Paid';
                break;
            case 'cancelled':
                statusClass = 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200';
                statusText = 'Cancelled';
                break;
            case 'failed':
                statusClass = 'bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200';
                statusText = 'Failed';
                break;
        }

        return `
            <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A] hover:bg-gray-50 dark:hover:bg-[#161615]">
                <td class="py-2 px-3">
                    <span class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${payment.reference || 'N/A'}</span>
                </td>
                <td class="py-2 px-3">
                    <div>
                        <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${payment.user?.first_name} ${payment.user?.last_name}</p>
                        <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">${payment.user?.user_type || 'N/A'}</p>
                    </div>
                </td>
                <td class="py-2 px-3">
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">${payment.vehicle?.type?.name || 'N/A'}</p>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">${payment.vehicle?.plate_no || payment.vehicle?.color + '-' + payment.vehicle?.number}</p>
                </td>
                <td class="py-2 px-3">
                    <span class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">₱${parseFloat(payment.amount).toFixed(2)}</span>
                </td>
                <td class="py-2 px-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClass}">
                        ${statusText}
                    </span>
                </td>
                <td class="py-2 px-3">
                    <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">${payment.paid_at ? new Date(payment.paid_at).toLocaleDateString() : '-'}</span>
                </td>
                <td class="py-2 px-3">
                    <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">${new Date(payment.created_at).toLocaleDateString()}</span>
                </td>
            </tr>
        `;
    }).join('');
}

function updateTransactionsPaginationControls(start, end, total, totalPages) {
    document.getElementById('transactionsShowingStart').textContent = total === 0 ? 0 : start;
    document.getElementById('transactionsShowingEnd').textContent = end;
    document.getElementById('transactionsTotalCount').textContent = total;

    const prevButton = document.getElementById('transactionsPrevPage');
    const nextButton = document.getElementById('transactionsNextPage');
    const pageNumbersDiv = document.getElementById('transactionsPageNumbers');

    prevButton.disabled = transactionsCurrentPage === 1;
    prevButton.className = transactionsCurrentPage === 1 ? 'btn-pagination btn-paginationDisable' : 'btn-pagination btn-paginationArrow';

    nextButton.disabled = transactionsCurrentPage >= totalPages;
    nextButton.className = transactionsCurrentPage >= totalPages ? 'btn-pagination btn-paginationDisable' : 'btn-pagination btn-paginationArrow';

    pageNumbersDiv.innerHTML = '';
    if (totalPages > 0) {
        const maxPagesToShow = 3;
        let startPage = Math.max(1, transactionsCurrentPage - Math.floor(maxPagesToShow / 2));
        let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);

        if (endPage - startPage + 1 < maxPagesToShow) {
            startPage = Math.max(1, endPage - maxPagesToShow + 1);
        }

        for (let i = startPage; i <= endPage; i++) {
            const pageButton = document.createElement('button');
            pageButton.textContent = i;
            pageButton.className = i === transactionsCurrentPage 
                ? 'btn-pagination btn-paginationActive btn-paginationNumber' 
                : 'btn-pagination btn-paginationNumber';
            pageButton.onclick = () => goToTransactionsPage(i);
            pageNumbersDiv.appendChild(pageButton);
        }
    }
}

function changeTransactionsPage(direction) {
    const totalPages = Math.ceil(allTransactions.length / transactionsItemsPerPage);
    const newPage = transactionsCurrentPage + direction;
    
    if (newPage >= 1 && newPage <= totalPages) {
        transactionsCurrentPage = newPage;
        applyTransactionsPagination();
    }
}

function goToTransactionsPage(page) {
    transactionsCurrentPage = page;
    applyTransactionsPagination();
}

function exportTransactionsToCSV() {
    const csvData = [];
    csvData.push(['Reference', 'User Name', 'Email', 'User Type', 'Vehicle Type', 'Plate/Sticker', 'Amount', 'Status', 'Paid Date', 'Request Date']);

    allTransactions.forEach(payment => {
        csvData.push([
            payment.reference || 'N/A',
            `${payment.user?.first_name} ${payment.user?.last_name}`,
            payment.user?.email || 'N/A',
            payment.user?.user_type || 'N/A',
            payment.vehicle?.type?.name || 'N/A',
            payment.vehicle?.plate_no || `${payment.vehicle?.color}-${payment.vehicle?.number}`,
            `₱${parseFloat(payment.amount).toFixed(2)}`,
            payment.status,
            payment.paid_at ? new Date(payment.paid_at).toLocaleDateString() : '-',
            new Date(payment.created_at).toLocaleDateString()
        ]);
    });

    const csvContent = csvData.map(row => row.map(cell => `"${cell}"`).join(',')).join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `transactions_${new Date().toISOString().split('T')[0]}.csv`;
    link.click();
}

function updatePaymentCount(count) {
    document.getElementById('paymentCount').textContent = count;
}

function showBatchVehicles(payment) {
    const content = document.getElementById('batchViewContent');
    
    // Handle both single and multiple vehicles
    let vehicles = [];
    if (payment.batch_vehicles && payment.batch_vehicles.length > 0) {
        vehicles = payment.batch_vehicles;
    } else if (payment.vehicle) {
        // Single vehicle - create array with single vehicle
        vehicles = [{
            id: payment.vehicle.id,
            plate_no: payment.vehicle.plate_no,
            color: payment.vehicle.color,
            number: payment.vehicle.number,
            sticker: payment.vehicle.sticker,
            type_name: payment.vehicle.type?.name || 'Unknown Type'
        }];
    }
    
    content.innerHTML = vehicles.map(vehicle => `
        <div class="vehicle-sticker-card" style="cursor: default; pointer-events: none;">
            <div class="sticker-image-container">
                ${vehicle.sticker ? `<img src="${vehicle.sticker}" alt="Sticker">` : '<div class="text-center text-[#706f6c] dark:text-[#A1A09A] py-8">No Sticker</div>'}
            </div>
            <div class="vehicle-info">
                <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${vehicle.type_name || 'Unknown Type'}</p>
                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">${vehicle.plate_no || 'No Plate'}</p>
            </div>
        </div>
    `).join('');
    
    // Store current payment ID and show the modal with action buttons
    currentPaymentId = payment.id;
    document.getElementById('batchViewModal').classList.remove('hidden');
    
    // Update modal footer to include action buttons (removed Cancel button)
    const modalFooter = document.querySelector('#batchViewModal .modal-footer');
    modalFooter.innerHTML = `
        <button onclick="closeBatchViewModal()" class="btn btn-secondary">Close</button>
        <button onclick="confirmPayment()" class="btn btn-primary">Confirm Payment</button>
    `;
}

function closeBatchViewModal() {
    document.getElementById('batchViewModal').classList.add('hidden');
    currentPaymentId = null;
}

function showTransactionReceipt(paymentId) {
    fetch(`/stickers/data?tab=transactions`, {
        headers: {
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(payments => {
        const payment = payments.find(p => p.id === paymentId);
        if (!payment) {
            showNotification('Error', 'Payment not found', 'error');
            return;
        }

        const receiptHTML = `
            <div class="space-y-4">
                <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                    <div class="flex items-center gap-2 mb-4">
                        <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="font-semibold text-lg text-green-600 dark:text-green-400">Payment Successful</h3>
                    </div>
                </div>

                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <h4 class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Receipt Details</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Reference Number</p>
                            <p class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${payment.reference}</p>
                        </div>
                        <div>
                            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Amount Paid</p>
                            <p class="font-medium text-green-600 dark:text-green-400">₱${parseFloat(payment.amount).toFixed(2)}</p>
                        </div>
                        <div>
                            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Customer Name</p>
                            <p class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${payment.user?.first_name} ${payment.user?.last_name}</p>
                        </div>
                        <div>
                            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">User Type</p>
                            <p class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${payment.user?.user_type || 'N/A'}</p>
                        </div>
                        <div>
                            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">${payment.vehicle_count > 1 ? 'Vehicles' : 'Vehicle'}</p>
                            <p class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                                ${payment.vehicle_count > 1 ? payment.vehicle_count + ' Vehicles' : (payment.vehicle?.type?.name || 'N/A')}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Payment Date</p>
                            <p class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${payment.paid_at ? new Date(payment.paid_at).toLocaleDateString() : '-'}</p>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.getElementById('transactionReceiptContent').innerHTML = receiptHTML;
        document.getElementById('transactionReceiptModal').classList.remove('hidden');
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error', 'Failed to load receipt', 'error');
    });
}

function closeTransactionReceipt() {
    document.getElementById('transactionReceiptModal').classList.add('hidden');
}

function printReceipt() {
    const content = document.getElementById('transactionReceiptContent').innerHTML;
    const printWindow = window.open('', '', 'width=800,height=600');
    printWindow.document.write(`
        <html>
            <head>
                <title>Payment Receipt</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; }
                    .receipt { max-width: 600px; margin: 0 auto; }
                    h3 { color: #16a34a; margin-bottom: 20px; }
                    .details { margin-top: 20px; }
                    .row { display: flex; justify-content: space-between; margin-bottom: 10px; padding: 10px; background: #f9f9f9; }
                    .label { font-weight: bold; color: #666; }
                    .value { color: #333; }
                    @media print {
                        button { display: none; }
                    }
                </style>
            </head>
            <body>
                <div class="receipt">
                    ${content}
                </div>
                <div style="text-align: center; margin-top: 30px;">
                    <button onclick="window.print()">Print</button>
                    <button onclick="window.close()">Close</button>
                </div>
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.focus();
}

// Search functionality
let paymentSearchTimeout;
let transactionsSearchTimeout;

function initializeSearch() {
    // Payment search
    const paymentSearchInput = document.getElementById('paymentSearch');
    if (paymentSearchInput) {
        paymentSearchInput.addEventListener('input', function() {
            clearTimeout(paymentSearchTimeout);
            paymentSearchTimeout = setTimeout(() => {
                loadPayments();
            }, 300);
        });
    }

    // Transactions search
    const transactionsSearchInput = document.getElementById('transactionsSearch');
    if (transactionsSearchInput) {
        transactionsSearchInput.addEventListener('input', function() {
            clearTimeout(transactionsSearchTimeout);
            transactionsSearchTimeout = setTimeout(() => {
                loadTransactions();
            }, 300);
        });
    }
}

function clearPaymentSearch() {
    const searchInput = document.getElementById('paymentSearch');
    if (searchInput) {
        searchInput.value = '';
        loadPayments();
    }
}

function clearTransactionsSearch() {
    const searchInput = document.getElementById('transactionsSearch');
    if (searchInput) {
        searchInput.value = '';
        loadTransactions();
    }
}

// Make functions globally accessible
window.switchTab = switchTab;
window.selectUser = selectUser;
window.toggleVehicleSelection = toggleVehicleSelection;
window.resetRequestForm = resetRequestForm;
window.submitRequest = submitRequest;
window.showReceipt = showReceipt;
window.closeReceiptModal = closeReceiptModal;
window.closeNotificationModal = closeNotificationModal;
window.closeConfirmationModal = closeConfirmationModal;
window.confirmPayment = confirmPayment;
window.deletePaymentRequest = deletePaymentRequest;
window.confirmDelete = confirmDelete;
window.closeDeleteConfirmModal = closeDeleteConfirmModal;
window.loadPayments = loadPayments;
window.loadTransactions = loadTransactions;
window.changeTransactionsPage = changeTransactionsPage;
window.goToTransactionsPage = goToTransactionsPage;
window.exportTransactionsToCSV = exportTransactionsToCSV;
window.showBatchVehicles = showBatchVehicles;
window.closeBatchViewModal = closeBatchViewModal;
window.showTransactionReceipt = showTransactionReceipt;
window.closeTransactionReceipt = closeTransactionReceipt;
window.printReceipt = printReceipt;
window.clearPaymentSearch = clearPaymentSearch;
window.clearTransactionsSearch = clearTransactionsSearch;
</script>
@endpush
