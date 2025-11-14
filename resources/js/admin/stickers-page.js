/**
 * Stickers Page Management
 * 
 * Note: The following values are passed from Blade:
 * - window.stickerFee (set in blade file)
 * - window.currentUserId (set in blade file)
 * - window.currentUserName (set in blade file)
 */

// Set current tab globally for real-time updates
window.currentTab = 'stickers';
let currentTab = 'stickers';
let selectedUser = null;
let selectedVehicles = [];
let currentPaymentId = null;
let confirmationCallback = null;
let stickersData = [];
let stickersCurrentPage = 1;
let stickersItemsPerPage = 24; // 3 rows x 8 columns
let stickersMeta = { total: 0, per_page: stickersItemsPerPage, current_page: 1, last_page: 1 };

// Payments pagination
let allPayments = [];
let paymentCurrentPage = 1;
let paymentItemsPerPage = 10;
let paymentMeta = { total: 0, per_page: paymentItemsPerPage, current_page: 1, last_page: 1 };

// Transactions pagination
let transactionsCurrentPage = 1;
let transactionsItemsPerPage = 10;
let allTransactions = [];
let transactionsMeta = { total: 0, per_page: transactionsItemsPerPage, current_page: 1, last_page: 1 };

// Idempotency guard for create request
let requestInFlight = false;

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
        iconElement.innerHTML = '<svg class="w-6 h-6 modal-icon-warning" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>';
        closeBtn.className = 'btn btn-danger';
    } else if (type === 'success' || title === 'Success') {
        titleElement.classList.add('text-green-600');
        iconElement.innerHTML = '<svg class="w-6 h-6 modal-icon-success" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
        closeBtn.className = 'btn btn-success';
    } else if (type === 'warning' || title === 'Warning') {
        titleElement.classList.add('text-yellow-500');
        iconElement.innerHTML = '<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>';
        closeBtn.className = 'btn btn-secondary';
    } else {
        titleElement.classList.add('text-blue-500');
        iconElement.innerHTML = '<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg>';
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
    
    // Restore active tab from URL hash
    restoreActiveTab();
    
    // Load payments with pagination on initial load if payment tab is active
    // Initial payment count will be set by loadPayments()
    if (currentTab === 'payment') {
        loadPayments();
    }

    // Payment pagination limit change handler
    const paymentPaginationLimit = document.getElementById('paymentPaginationLimit');
    if (paymentPaginationLimit) {
        paymentPaginationLimit.addEventListener('change', function() {
            paymentItemsPerPage = parseInt(this.value);
            paymentCurrentPage = 1; // Reset to first page
            loadPayments();
        });
    }

    // Stickers pagination limit change handler
    const stickersPaginationLimit = document.getElementById('stickersPaginationLimit');
    if (stickersPaginationLimit) {
        stickersPaginationLimit.addEventListener('change', function() {
            stickersItemsPerPage = parseInt(this.value);
            stickersCurrentPage = 1; // Reset to first page
            loadStickers();
        });
    }

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

    // Transactions server-side pagination wiring
    const paginationLimit = document.getElementById('transactionsPaginationLimit');
    let tLimit = 10;
    if (paginationLimit) {
        paginationLimit.addEventListener('change', function() {
            transactionsItemsPerPage = parseInt(this.value);
            transactionsCurrentPage = 1;
            loadTransactions();
        });
    }
    // Initial load
    loadTransactions();
    // Keep existing HTML onclicks working
    window.changePage = function(direction) { changeTransactionsPage(direction); };
    window.goToPage = function(page) { goToTransactionsPage(page); };
});

function switchTab(tab) {
    currentTab = tab;
    window.currentTab = tab; // Update global variable for real-time updates
    
    // Hide all content
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.sticker-tab').forEach(el => el.classList.remove('active'));
    
    // Show selected content
    document.getElementById(tab + 'Content').classList.remove('hidden');
    document.getElementById(tab + 'Tab').classList.add('active');
    
    // Update URL hash without triggering scroll
    history.replaceState(null, null, `#${tab}`);
    
    // Load data if needed
    if (tab === 'stickers') {
        loadStickers();
    } else if (tab === 'payment') {
        loadPayments();
    } else if (tab === 'transactions') {
        loadTransactions();
    }
}

// Restore active tab from URL hash on page load
function restoreActiveTab() {
    const hash = window.location.hash.substring(1); // Remove the # character
    const validTabs = ['stickers', 'request', 'payment', 'transactions'];
    
    if (hash && validTabs.includes(hash)) {
        switchTab(hash);
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
                    <svg class="checkbox-icon" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
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
    if (requestInFlight) return;
    requestInFlight = true;
    const submitBtn = document.querySelector('#requestForm button[type="submit"]');
    const prevText = submitBtn ? submitBtn.textContent : '';
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Creating...';
    }
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
    })
    .finally(() => {
        requestInFlight = false;
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = prevText || 'Create Request';
        }
    });
}

function loadPayments() {
    const searchInput = document.getElementById('paymentSearch');
    const searchTerm = searchInput ? searchInput.value : '';

    const params = new URLSearchParams();
    params.append('tab', 'payment');
    if (searchTerm.trim()) params.append('search', searchTerm.trim());
    params.append('page', paymentCurrentPage.toString());
    params.append('per_page', paymentItemsPerPage.toString());

    return fetch(`/stickers/data?${params.toString()}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(resp => {
        allPayments = resp.data || [];
        paymentMeta = resp.meta || paymentMeta;
        paymentCurrentPage = paymentMeta.current_page || 1;
        paymentItemsPerPage = paymentMeta.per_page || paymentItemsPerPage;
        displayPayments(allPayments);
        updatePaymentPaginationControls();
        updatePaymentCount(paymentMeta.total || 0);
    })
    .catch(err => console.error('Error loading payments:', err));
}

function applyPaymentPagination() {
    // For server-side pagination, simply render current page and update controls
    displayPayments(allPayments);
    updatePaymentPaginationControls();
}

function updatePaymentPaginationControls() {
    const meta = paymentMeta;
    const total = meta.total || 0;
    const perPage = meta.per_page || paymentItemsPerPage;
    const current = meta.current_page || paymentCurrentPage;
    const totalPages = meta.last_page || 1;
    const start = total === 0 ? 0 : (current - 1) * perPage + 1;
    const end = Math.min(current * perPage, total);
    const showingStart = document.getElementById('paymentShowingStart');
    const showingEnd = document.getElementById('paymentShowingEnd');
    const totalCount = document.getElementById('paymentTotalCount');
    
    if (showingStart) showingStart.textContent = start;
    if (showingEnd) showingEnd.textContent = end;
    if (totalCount) totalCount.textContent = total;

    const prevButton = document.getElementById('paymentPrevPage');
    const nextButton = document.getElementById('paymentNextPage');
    const pageNumbersDiv = document.getElementById('paymentPageNumbers');
    
    if (!prevButton || !nextButton || !pageNumbersDiv) return;

    prevButton.disabled = current === 1;
    prevButton.className = current === 1 ? 'btn-pagination btn-paginationDisable' : 'btn-pagination btn-paginationArrow';

    nextButton.disabled = current >= totalPages;
    nextButton.className = current >= totalPages ? 'btn-pagination btn-paginationDisable' : 'btn-pagination btn-paginationArrow';

    pageNumbersDiv.innerHTML = '';
    if (totalPages > 0) {
        const maxPagesToShow = 3;
        let startPage = Math.max(1, current - Math.floor(maxPagesToShow / 2));
        let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);

        if (endPage - startPage + 1 < maxPagesToShow) {
            startPage = Math.max(1, endPage - maxPagesToShow + 1);
        }

        for (let i = startPage; i <= endPage; i++) {
            const pageButton = document.createElement('button');
            pageButton.textContent = i;
            pageButton.className = i === current 
                ? 'btn-pagination btn-paginationActive' 
                : 'btn-pagination btn-paginationNumber';
            pageButton.onclick = () => goToPaymentPage(i);
            pageNumbersDiv.appendChild(pageButton);
        }
    }
}

function changePaymentPage(direction) {
    const totalPages = paymentMeta.last_page || 1;
    const newPage = paymentCurrentPage + direction;
    if (newPage >= 1 && newPage <= totalPages) {
        paymentCurrentPage = newPage;
        loadPayments();
    }
}

function goToPaymentPage(page) {
    paymentCurrentPage = page;
    loadPayments();
}

function displayPayments(payments) {
    const tbody = document.getElementById('paymentTableBody');
    
    if (payments.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="py-8 px-4 text-center text-[#706f6c] dark:text-[#A1A09A]">No pending payments</td></tr>';
        return;
    }

    tbody.innerHTML = payments.map(payment => `
        <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A] hover:bg-gray-50 dark:hover:bg-[#161615] table-row-enter" data-payment-id="${payment.id}">
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
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                    <button onclick="deletePaymentRequest(${payment.id})" class="btn-delete" title="Delete Request">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function showReceipt(paymentId) {
    currentPaymentId = paymentId;
    // Prefer local page cache first
    let payment = Array.isArray(allPayments) ? allPayments.find(p => p.id === paymentId) : null;
    if (payment) {
        showBatchVehicles(payment);
        return;
    }

    // Fallback: fetch current page data
    const params = new URLSearchParams();
    params.append('tab', 'payment');
    params.append('page', paymentCurrentPage.toString());
    params.append('per_page', paymentItemsPerPage.toString());
    fetch(`/stickers/data?${params.toString()}`, { headers: { 'Accept': 'application/json' }})
        .then(r => r.json())
        .then(resp => {
            const list = resp.data || [];
            const found = list.find(p => p.id === paymentId);
            if (found) {
                showBatchVehicles(found);
            } else {
                showNotification('Error', 'Payment not found', 'error');
            }
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
            loadTransactions();
            
            // Show receipt modal directly
            showTransactionReceipt(paymentIdToShow);
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

function confirmDeletePayment() {
    console.log('confirmDeletePayment called, currentDeletePaymentId:', currentDeletePaymentId);
    
    if (!currentDeletePaymentId) {
        console.error('No payment ID set');
        showNotification('Error', 'No payment selected for deletion', 'error');
        return;
    }

    console.log('Sending DELETE request for payment:', currentDeletePaymentId);

    // Mark this action before making the request
    if (!window._recentActions) {
        window._recentActions = new Map();
    }
    window._recentActions.set(currentDeletePaymentId, Date.now());
    
    fetch(`/stickers/${currentDeletePaymentId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            loadPayments();
            loadTransactions();
        } else {
            showNotification('Error', data.message || 'Failed to delete payment request', 'error');
        }
    })
    .catch(error => {
        console.error('Error deleting payment:', error);
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
    const statusFilter = document.getElementById('transactionsStatusFilter');
    const searchTerm = searchInput ? searchInput.value : '';
    const status = statusFilter ? statusFilter.value : 'paid';
    const params = new URLSearchParams();
    params.append('tab', 'transactions');
    params.append('status', status);
    if (searchTerm.trim()) params.append('search', searchTerm.trim());
    params.append('page', transactionsCurrentPage.toString());
    params.append('per_page', transactionsItemsPerPage.toString());

    fetch(`/stickers/data?${params.toString()}`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
        }
    })
    .then(r => r.json())
    .then(resp => {
        allTransactions = resp.data || [];
        transactionsMeta = resp.meta || transactionsMeta;
        transactionsCurrentPage = transactionsMeta.current_page || 1;
        transactionsItemsPerPage = transactionsMeta.per_page || transactionsItemsPerPage;
        applyTransactionsPagination();
    })
    .catch(error => {
        console.error('Error loading transactions:', error);
    });
}

function applyTransactionsPagination() {
    displayTransactions(allTransactions);
    updateTransactionsPaginationControls();
}

function displayTransactions(transactions) {
    const tbody = document.getElementById('transactionsTableBody');
    
    if (transactions.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="py-8 px-4 text-center text-[#706f6c] dark:text-[#A1A09A]">No transactions found</td></tr>';
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
            <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A] hover:bg-gray-50 dark:hover:bg-[#161615] table-row-enter">
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
                        <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Batch payment</p>
                    ` : `
                        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">${payment.vehicle?.type?.name || 'N/A'}</p>
                        <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">${payment.vehicle?.plate_no || payment.vehicle?.color + '-' + payment.vehicle?.number}</p>
                    `}
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
                <td class="py-2 px-3">
                    <div class="flex items-center justify-center gap-2">
                        ${payment.status !== 'cancelled' ? `
                            <button onclick="viewTransactionReceipt(${payment.id})" class="btn-view" title="View Receipt">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        ` : ''}
                        <button onclick="deleteTransaction(${payment.id})" class="btn-delete" title="Delete Transaction">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

function updateTransactionsPaginationControls() {
    const meta = transactionsMeta;
    const total = meta.total || 0;
    const perPage = meta.per_page || transactionsItemsPerPage;
    const current = meta.current_page || transactionsCurrentPage;
    const totalPages = meta.last_page || 1;
    const start = total === 0 ? 0 : (current - 1) * perPage + 1;
    const end = Math.min(current * perPage, total);

    const showingStart = document.getElementById('showing-start');
    const showingEnd = document.getElementById('showing-end');
    const totalCount = document.getElementById('total-count');
    if (showingStart) showingStart.textContent = start;
    if (showingEnd) showingEnd.textContent = end;
    if (totalCount) totalCount.textContent = total;

    const prevBtn = document.getElementById('prev-page');
    const nextBtn = document.getElementById('next-page');
    const pageNumbers = document.getElementById('page-numbers');
    if (!prevBtn || !nextBtn || !pageNumbers) return;

    prevBtn.disabled = current === 1;
    nextBtn.disabled = current >= totalPages || totalPages === 0;
    prevBtn.className = current === 1 ? 'btn-pagination btn-paginationDisable' : 'btn-pagination btn-paginationArrow';
    nextBtn.className = (current >= totalPages || totalPages === 0) ? 'btn-pagination btn-paginationDisable' : 'btn-pagination btn-paginationArrow';

    // Generate page numbers (3 at a time)
    pageNumbers.innerHTML = '';
    let startPage = Math.max(1, current - 1);
    let endPage = Math.min(totalPages, startPage + 2);
    if (endPage - startPage < 2) startPage = Math.max(1, endPage - 2);
    for (let i = startPage; i <= endPage; i++) {
        const btn = document.createElement('button');
        btn.textContent = i;
        btn.className = i === current ? 'btn-pagination btn-paginationActive' : 'btn-pagination btn-paginationNumber';
        btn.onclick = () => goToTransactionsPage(i);
        pageNumbers.appendChild(btn);
    }
}

function changeTransactionsPage(direction) {
    const totalPages = transactionsMeta.last_page || 1;
    const newPage = transactionsCurrentPage + direction;
    if (newPage >= 1 && newPage <= totalPages) {
        transactionsCurrentPage = newPage;
        loadTransactions();
    }
}

function goToTransactionsPage(page) {
    transactionsCurrentPage = page;
    loadTransactions();
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
    
    // Update modal footer to include action dropdown
    const modalFooter = document.querySelector('#batchViewModal .modal-footer');
    modalFooter.innerHTML = `
        <button onclick="closeBatchViewModal()" class="btn btn-secondary">Close</button>
        <div class="relative inline-block">
            <button id="batchActionDropdownBtn" onclick="toggleBatchActionDropdown()" class="btn btn-primary">
                <span>Actions</span>
            </button>
            <div id="batchActionDropdown" class="hidden absolute bottom-full right-0 mb-2 w-48 bg-white dark:bg-[#1a1a1a] rounded-lg shadow-lg border border-[#e3e3e0] dark:border-[#3E3E3A] z-50">
                <button onclick="confirmPayment()" class="w-full text-left px-4 py-2 text-sm text-[#1b1b18] dark:text-[#EDEDEC] hover:bg-gray-50 dark:hover:bg-[#161615] rounded-t-lg inline-flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>Confirm Payment</span>
                </button>
                <button onclick="cancelRequest()" class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-50 dark:hover:bg-[#161615] rounded-b-lg inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span>Cancel Request</span>
                </button>
            </div>
        </div>
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
    .then(resp => {
        const list = resp && Array.isArray(resp.data) ? resp.data : [];
        const payment = list.find(p => p.id === paymentId);
        if (!payment) {
            showNotification('Error', 'Payment not found', 'error');
            return;
        }

        const receiptHTML = `
            <div class="space-y-4">
                <div class="p-6 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl border-2 border-green-200 dark:border-green-800">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-green-100 dark:bg-green-800 flex items-center justify-center">
                            <svg class="w-7 h-7 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <h3 class="font-bold text-2xl text-green-600 dark:text-green-400">Payment Successful</h3>
                    </div>
                </div>

                <div class="p-6 bg-white dark:bg-[#1a1a1a] rounded-xl border border-[#e3e3e0] dark:border-[#3E3E3A] shadow-sm">
                    <h4 class="font-bold text-lg text-[#1b1b18] dark:text-[#EDEDEC] mb-6 pb-3 border-b border-[#e3e3e0] dark:border-[#3E3E3A]">Receipt Details</h4>
                    <div class="grid grid-cols-2 gap-6 mb-6">
                        <div class="space-y-1">
                            <p class="text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wide">Reference Number</p>
                            <p class="font-semibold text-base text-[#1b1b18] dark:text-[#EDEDEC]">${payment.reference}</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wide">Payment Date</p>
                            <p class="font-semibold text-base text-[#1b1b18] dark:text-[#EDEDEC]">${payment.paid_at ? new Date(payment.paid_at).toLocaleDateString() : '-'}</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wide">Customer Name</p>
                            <p class="font-semibold text-base text-[#1b1b18] dark:text-[#EDEDEC]">${payment.user?.first_name} ${payment.user?.last_name}</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wide">User Type</p>
                            <p class="font-semibold text-base text-[#1b1b18] dark:text-[#EDEDEC]">${payment.user?.user_type ? payment.user.user_type.charAt(0).toUpperCase() + payment.user.user_type.slice(1) : 'N/A'}</p>
                        </div>
                    </div>
                    
                    <!-- Vehicles List -->
                    <div class="border-t border-[#e3e3e0] dark:border-[#3E3E3A] pt-4">
                        <p class="text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wide mb-3">Vehicle Stickers</p>
                        <div class="space-y-2">
                            ${payment.vehicle_count > 1 && Array.isArray(payment.batch_vehicles) && payment.batch_vehicles.length > 0 ? 
                                payment.batch_vehicles.map(vehicle => `
                                    <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-[#161615] rounded-lg">
                                        <div>
                                            <p class="font-medium text-sm text-[#1b1b18] dark:text-[#EDEDEC]">${(vehicle.type && vehicle.type.name) || vehicle.type_name || 'Vehicle Sticker'}</p>
                                            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">${vehicle.plate_no || vehicle.color + '-' + vehicle.number || 'N/A'}</p>
                                        </div>
                                        <p class="font-bold text-base text-[#1b1b18] dark:text-[#EDEDEC]">₱${parseFloat(payment.amount / payment.vehicle_count).toFixed(2)}</p>
                                    </div>
                                `).join('') 
                                : 
                                `<div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-[#161615] rounded-lg">
                                    <div>
                                        <p class="font-medium text-sm text-[#1b1b18] dark:text-[#EDEDEC]">${payment.vehicle?.type?.name || 'Vehicle Sticker'}</p>
                                        <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">${payment.vehicle?.plate_no || (payment.vehicle ? (payment.vehicle.color + '-' + payment.vehicle.number) : 'N/A')}</p>
                                    </div>
                                    <p class="font-bold text-base text-[#1b1b18] dark:text-[#EDEDEC]">₱${parseFloat(payment.amount).toFixed(2)}</p>
                                </div>`
                            }
                        </div>
                    </div>
                    
                    <!-- Total -->
                    <div class="border-t border-[#e3e3e0] dark:border-[#3E3E3A] mt-4 pt-4">
                        <div class="flex justify-between items-center">
                            <p class="text-lg font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Total Amount</p>
                            <p class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">₱${parseFloat(payment.amount).toFixed(2)}</p>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.getElementById('transactionReceiptContent').innerHTML = receiptHTML;
        
        // Store payment ID for printing
        currentTransactionReceiptId = payment.id;
        
        document.getElementById('transactionReceiptModal').classList.remove('hidden');
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error', 'Failed to load receipt', 'error');
    });
}

// Alias for action button
function viewTransactionReceipt(paymentId) {
    showTransactionReceipt(paymentId);
}

function closeTransactionReceipt() {
    document.getElementById('transactionReceiptModal').classList.add('hidden');
}

// Delete transaction
let currentDeleteTransactionId = null;

function deleteTransaction(paymentId) {
    currentDeleteTransactionId = paymentId;
    document.getElementById('deleteTransactionModal').classList.remove('hidden');
}

function closeDeleteTransactionModal() {
    document.getElementById('deleteTransactionModal').classList.add('hidden');
    currentDeleteTransactionId = null;
}

function confirmDeleteTransaction() {
    if (!currentDeleteTransactionId) return;
    
    fetch(`/stickers/${currentDeleteTransactionId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadTransactions();
            closeDeleteTransactionModal();
        } else {
            showNotification('Error', data.message || 'Failed to delete transaction', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error', 'An error occurred while deleting transaction', 'error');
    });
}

function printReceipt() {
    const payment = allTransactions.find(p => p.id === currentTransactionReceiptId);
    if (!payment) return;
    
    const printWindow = window.open('', '', 'width=800,height=600');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
            <head>
                <title>Payment Receipt</title>
                <style>
                    * { margin: 0; padding: 0; box-sizing: border-box; }
                    body { 
                        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
                        padding: 40px 20px;
                        background: #fff;
                    }
                    .receipt-container { 
                        max-width: 600px; 
                        margin: 0 auto;
                        border: 1px solid #e5e7eb;
                        padding: 30px;
                    }
                    .receipt-header {
                        text-align: center;
                        padding-bottom: 20px;
                        border-bottom: 2px solid #e5e7eb;
                        margin-bottom: 30px;
                    }
                    .receipt-title {
                        font-size: 24px;
                        font-weight: 700;
                        color: #111827;
                        margin-bottom: 8px;
                    }
                    .receipt-meta {
                        font-size: 13px;
                        color: #6b7280;
                    }
                    .receipt-section {
                        margin-bottom: 25px;
                    }
                    .section-title {
                        font-size: 12px;
                        font-weight: 600;
                        color: #6b7280;
                        text-transform: uppercase;
                        letter-spacing: 0.05em;
                        margin-bottom: 12px;
                    }
                    .info-row {
                        display: flex;
                        justify-content: space-between;
                        padding: 10px 0;
                        border-bottom: 1px solid #f3f4f6;
                    }
                    .info-row:last-child {
                        border-bottom: none;
                    }
                    .info-label {
                        font-size: 14px;
                        color: #111827;
                        font-weight: 500;
                    }
                    .info-detail {
                        font-size: 12px;
                        color: #6b7280;
                        margin-top: 2px;
                    }
                    .info-value {
                        font-size: 14px;
                        font-weight: 600;
                        color: #111827;
                        text-align: right;
                    }
                    .total-row {
                        display: flex;
                        justify-content: space-between;
                        padding: 15px 0;
                        margin-top: 15px;
                        border-top: 2px solid #e5e7eb;
                        font-size: 16px;
                        font-weight: 700;
                    }
                    .total-label { color: #111827; }
                    .total-value { color: #16a34a; }
                    .print-buttons {
                        text-align: center;
                        margin-top: 30px;
                        padding-top: 20px;
                        border-top: 1px solid #e5e7eb;
                    }
                    .btn {
                        padding: 10px 24px;
                        margin: 0 8px;
                        border: 1px solid #d1d5db;
                        border-radius: 6px;
                        background: #fff;
                        color: #374151;
                        font-size: 14px;
                        font-weight: 500;
                        cursor: pointer;
                        transition: all 0.2s;
                    }
                    .btn:hover {
                        background: #f9fafb;
                        border-color: #9ca3af;
                    }
                    .btn-primary {
                        background: #3b82f6;
                        color: #fff;
                        border-color: #3b82f6;
                    }
                    .btn-primary:hover {
                        background: #2563eb;
                        border-color: #2563eb;
                    }
                    @media print {
                        body { padding: 0; }
                        .receipt-container { border: none; padding: 0; }
                        .print-buttons { display: none; }
                    }
                </style>
            </head>
            <body>
                <div class="receipt-container">
                    <div class="receipt-header">
                        <div class="receipt-title">Payment Receipt</div>
                        <div class="receipt-meta">
                            Date: ${payment.paid_at ? new Date(payment.paid_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}<br>
                            Reference: ${payment.reference}<br>
                            Customer Number: ${payment.user?.id || 'N/A'}
                        </div>
                    </div>
                    
                    <div class="receipt-section">
                        <div class="section-title">Billed to:</div>
                        <div class="info-row">
                            <div class="info-label">Name</div>
                            <div class="info-value">${payment.user?.first_name} ${payment.user?.last_name}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">User Type</div>
                            <div class="info-value">${payment.user?.user_type ? payment.user.user_type.charAt(0).toUpperCase() + payment.user.user_type.slice(1) : 'N/A'}</div>
                        </div>
                    </div>
                    
                    <div class="receipt-section">
                        <div class="section-title">Vehicle Stickers</div>
                        ${payment.vehicle_count > 1 && Array.isArray(payment.batch_vehicles) && payment.batch_vehicles.length > 0 ? 
                            payment.batch_vehicles.map(vehicle => `
                                <div class="info-row">
                                    <div>
                                        <div class="info-label">${(vehicle.type && vehicle.type.name) || vehicle.type_name || 'Vehicle Sticker'}</div>
                                        <div class="info-detail">${vehicle.plate_no || vehicle.color + '-' + vehicle.number || 'N/A'}</div>
                                    </div>
                                    <div class="info-value">₱${parseFloat(payment.amount / payment.vehicle_count).toFixed(2)}</div>
                                </div>
                            `).join('')
                            :
                            `<div class="info-row">
                                <div>
                                    <div class="info-label">${payment.vehicle?.type?.name || 'Vehicle Sticker'}</div>
                                    <div class="info-detail">${payment.vehicle?.plate_no || (payment.vehicle ? (payment.vehicle.color + '-' + payment.vehicle.number) : 'N/A')}</div>
                                </div>
                                <div class="info-value">₱${parseFloat(payment.amount).toFixed(2)}</div>
                            </div>`
                        }
                    </div>
                    
                    <div class="total-row">
                        <div class="total-label">Total</div>
                        <div class="total-value">₱${parseFloat(payment.amount).toFixed(2)}</div>
                    </div>
                    
                    <div class="print-buttons">
                        <button class="btn btn-primary" onclick="window.print()">Print Receipt</button>
                        <button class="btn" onclick="window.close()">Close</button>
                    </div>
                </div>
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.focus();
}

// Store current receipt ID for printing
let currentTransactionReceiptId = null;

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

// Toggle action dropdown
function toggleActionDropdown() {
    const dropdown = document.getElementById('actionDropdown');
    dropdown.classList.toggle('hidden');
}

function toggleBatchActionDropdown() {
    const dropdown = document.getElementById('batchActionDropdown');
    dropdown.classList.toggle('hidden');
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    const actionBtn = document.getElementById('actionDropdownBtn');
    const actionDropdown = document.getElementById('actionDropdown');
    const batchActionBtn = document.getElementById('batchActionDropdownBtn');
    const batchActionDropdown = document.getElementById('batchActionDropdown');
    
    if (actionBtn && actionDropdown && !actionBtn.contains(event.target) && !actionDropdown.contains(event.target)) {
        actionDropdown.classList.add('hidden');
    }
    
    if (batchActionBtn && batchActionDropdown && !batchActionBtn.contains(event.target) && !batchActionDropdown.contains(event.target)) {
        batchActionDropdown.classList.add('hidden');
    }
});

// Cancel payment request
function cancelRequest() {
    if (!currentPaymentId) {
        console.error('No payment ID set for cancellation');
        return;
    }
    
    // Store the payment ID before closing modals
    const paymentIdToCancel = currentPaymentId;
    
    // Close any open dropdowns
    const actionDropdown = document.getElementById('actionDropdown');
    const batchActionDropdown = document.getElementById('batchActionDropdown');
    if (actionDropdown) actionDropdown.classList.add('hidden');
    if (batchActionDropdown) batchActionDropdown.classList.add('hidden');
    
    // Close modals immediately
    closeReceiptModal();
    closeBatchViewModal();
    
    fetch(`/stickers/${paymentIdToCancel}/cancel`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadPayments();
            loadTransactions();
            showNotification('Success', 'Payment request cancelled successfully', 'success');
        } else {
            showNotification('Error', data.message || 'Failed to cancel payment request', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error', 'An error occurred while cancelling the request', 'error');
    });
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
window.confirmDeletePayment = confirmDeletePayment;
window.closeDeleteConfirmModal = closeDeleteConfirmModal;
window.loadPayments = loadPayments;
window.loadTransactions = loadTransactions;
window.changeTransactionsPage = changeTransactionsPage;
window.goToTransactionsPage = goToTransactionsPage;
window.changePaymentPage = changePaymentPage;
window.goToPaymentPage = goToPaymentPage;
window.changeStickersPage = changeStickersPage;
window.goToStickersPage = goToStickersPage;
window.exportTransactionsToCSV = exportTransactionsToCSV;
window.showBatchVehicles = showBatchVehicles;
window.closeBatchViewModal = closeBatchViewModal;
window.showTransactionReceipt = showTransactionReceipt;
window.viewTransactionReceipt = viewTransactionReceipt;
window.closeTransactionReceipt = closeTransactionReceipt;
window.deleteTransaction = deleteTransaction;
window.closeDeleteTransactionModal = closeDeleteTransactionModal;
window.confirmDeleteTransaction = confirmDeleteTransaction;
window.printReceipt = printReceipt;
window.clearPaymentSearch = clearPaymentSearch;
window.clearTransactionsSearch = clearTransactionsSearch;
window.toggleActionDropdown = toggleActionDropdown;
window.toggleBatchActionDropdown = toggleBatchActionDropdown;
window.cancelRequest = cancelRequest;

// ===== STICKERS TAB FUNCTIONS =====

// Load stickers with filters
function loadStickers() {
    const fromDate = document.getElementById('stickersFromDate').value;
    const toDate = document.getElementById('stickersToDate').value;
    const search = document.getElementById('stickersSearch').value;

    // Show skeletons while loading
    showStickersSkeleton();

    const params = new URLSearchParams();
    if (fromDate) params.append('from_date', fromDate);
    if (toDate) params.append('to_date', toDate);
    if (search) params.append('search', search);
    params.append('page', stickersCurrentPage.toString());
    params.append('per_page', stickersItemsPerPage.toString());

    fetch(`/stickers/issued?${params.toString()}`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(r => r.json())
    .then(resp => {
        stickersData = resp.data || [];
        stickersMeta = resp.meta || stickersMeta;
        stickersCurrentPage = stickersMeta.current_page || 1;
        stickersItemsPerPage = stickersMeta.per_page || stickersItemsPerPage;
        applyStickersPagination();
    })
    .catch(error => {
        console.error('Error loading stickers:', error);
        showNotification('Error', 'Failed to load stickers', 'error');
    });
}

// Apply pagination to stickers
function applyStickersPagination() {
    renderStickers(stickersData);
    updateStickersPaginationControls();
}

function updateStickersPaginationControls() {
    const total = stickersMeta.total || 0;
    const perPage = stickersMeta.per_page || stickersItemsPerPage;
    const current = stickersMeta.current_page || stickersCurrentPage;
    const totalPages = stickersMeta.last_page || 1;
    const start = total === 0 ? 0 : (current - 1) * perPage + 1;
    const end = Math.min(current * perPage, total);
    const showingStart = document.getElementById('stickersShowingStart');
    const showingEnd = document.getElementById('stickersShowingEnd');
    const totalCount = document.getElementById('stickersTotalCount');
    
    if (showingStart) showingStart.textContent = start;
    if (showingEnd) showingEnd.textContent = end;
    if (totalCount) totalCount.textContent = total;

    const prevButton = document.getElementById('stickersPrevPage');
    const nextButton = document.getElementById('stickersNextPage');
    const pageNumbersDiv = document.getElementById('stickersPageNumbers');
    
    if (!prevButton || !nextButton || !pageNumbersDiv) return;

    prevButton.disabled = current === 1;
    prevButton.className = current === 1 ? 'btn-pagination btn-paginationDisable' : 'btn-pagination btn-paginationArrow';

    nextButton.disabled = current >= totalPages;
    nextButton.className = current >= totalPages ? 'btn-pagination btn-paginationDisable' : 'btn-pagination btn-paginationArrow';

    pageNumbersDiv.innerHTML = '';
    if (totalPages > 0) {
        const maxPagesToShow = 3;
        let startPage = Math.max(1, current - Math.floor(maxPagesToShow / 2));
        let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);

        if (endPage - startPage + 1 < maxPagesToShow) {
            startPage = Math.max(1, endPage - maxPagesToShow + 1);
        }

        for (let i = startPage; i <= endPage; i++) {
            const pageButton = document.createElement('button');
            pageButton.textContent = i;
            pageButton.className = i === current 
                ? 'btn-pagination btn-paginationActive' 
                : 'btn-pagination btn-paginationNumber';
            pageButton.onclick = () => goToStickersPage(i);
            pageNumbersDiv.appendChild(pageButton);
        }
    }
}

function changeStickersPage(direction) {
    const totalPages = stickersMeta.last_page || 1;
    const newPage = stickersCurrentPage + direction;
    if (newPage >= 1 && newPage <= totalPages) {
        stickersCurrentPage = newPage;
        loadStickers();
    }
}

function goToStickersPage(page) {
    stickersCurrentPage = page;
    loadStickers();
}

// Render stickers grid
function renderStickers(stickers) {
    const grid = document.getElementById('stickersGrid');
    const emptyState = document.getElementById('stickersEmptyState');
    const countSpan = document.getElementById('stickersCount');
    
    countSpan.textContent = stickersMeta.total || stickersData.length; // Show total count
    
    if (stickersData.length === 0) {
        grid.classList.add('hidden');
        emptyState.classList.remove('hidden');
        return;
    }
    
    grid.classList.remove('hidden');
    emptyState.classList.add('hidden');
    
    grid.innerHTML = stickers.map(sticker => `
        <div class="bg-white dark:bg-[#2a2a2a] rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] overflow-hidden hover:shadow-md transition-shadow">
            <!-- Sticker Image -->
            <div class="bg-gray-50 dark:bg-[#1a1a1a] flex items-center justify-center border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
                ${sticker.sticker ? `
                    <div class="skeleton w-full h-32"></div>
                    <img src="${sticker.sticker}"
                         alt="Sticker"
                         class="w-full h-auto object-contain"
                         decoding="async"
                         style="display:none;"
                         onload="this.style.display='block'; this.previousElementSibling.remove();"
                         onerror="this.previousElementSibling.remove(); this.nextElementSibling.style.display='flex'; this.remove();">
                    <div class="w-full h-32 bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-500 text-xs" style="display: none;">
                        No Image
                    </div>
                ` : `
                    <div class="w-full h-32 bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-500 text-xs">
                        No Sticker
                    </div>
                `}
            </div>
            
            <!-- Vehicle Details -->
            <div class="p-2 border-t border-[#e3e3e0] dark:border-[#3E3E3A]">
                <div class="space-y-1 text-center">
                    <!-- Owner Name -->
                    <div class="overflow-hidden">
                        <span class="text-xs font-semibold text-[#1b1b18] dark:text-[#EDEDEC] block truncate px-1" title="${sticker.owner_name}">${sticker.owner_name}</span>
                    </div>
                    
                    <!-- Vehicle Type -->
                    <div class="text-[#706f6c] dark:text-[#A1A09A] overflow-hidden">
                        <span class="text-xs block truncate px-1" title="${sticker.vehicle_type}">${sticker.vehicle_type}</span>
                    </div>
                    
                    <!-- Download Button -->
                    <div class="pt-1">
                        <button onclick="downloadSingleSticker(${sticker.id})" class="w-full py-1 px-2 text-xs bg-blue-600 hover:bg-blue-700 text-white rounded flex items-center justify-center gap-1 transition-colors">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                            Download
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

// Render skeleton cards while fetching sticker data
function showStickersSkeleton() {
    const grid = document.getElementById('stickersGrid');
    const emptyState = document.getElementById('stickersEmptyState');
    if (!grid) return;
    grid.classList.remove('hidden');
    if (emptyState) emptyState.classList.add('hidden');
    const count = stickersItemsPerPage || 24;
    const skeletonCard = () => `
        <div class="bg-white dark:bg-[#2a2a2a] rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] overflow-hidden">
            <div class="skeleton w-full h-32"></div>
            <div class="p-2 border-t border-[#e3e3e0] dark:border-[#3E3E3A] space-y-2">
                <div class="skeleton h-3 w-2/3 mx-auto"></div>
                <div class="skeleton h-3 w-1/2 mx-auto"></div>
                <div class="skeleton h-6 w-full"></div>
            </div>
        </div>
    `;
    grid.innerHTML = new Array(count).fill(0).map(() => skeletonCard()).join('');
}

// Download single sticker
function downloadSingleSticker(vehicleId) {
    window.location.href = `/stickers/vehicle/${vehicleId}/download`;
}

// Download all filtered stickers
function downloadAllStickers() {
    const fromDate = document.getElementById('stickersFromDate').value;
    const toDate = document.getElementById('stickersToDate').value;
    const search = document.getElementById('stickersSearch').value;
    
    const params = new URLSearchParams();
    if (fromDate) params.append('from_date', fromDate);
    if (toDate) params.append('to_date', toDate);
    if (search) params.append('search', search);
    
    window.location.href = `/stickers/download-filtered?${params.toString()}`;
}

// Reset stickers filters
function resetStickersFilters() {
    document.getElementById('stickersFromDate').value = '';
    document.getElementById('stickersToDate').value = '';
    document.getElementById('stickersSearch').value = '';
    loadStickers();
}

// Add event listeners for stickers filters
document.addEventListener('DOMContentLoaded', function() {
    const fromDate = document.getElementById('stickersFromDate');
    const toDate = document.getElementById('stickersToDate');
    const search = document.getElementById('stickersSearch');
    
    if (fromDate) {
        fromDate.addEventListener('change', loadStickers);
    }
    
    if (toDate) {
        toDate.addEventListener('change', loadStickers);
    }
    
    if (search) {
        search.addEventListener('input', debounce(loadStickers, 500));
    }
    
    // Load stickers on page load
    if (window.currentTab === 'stickers') {
        loadStickers();
    }
});

// Debounce helper function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Export functions
window.loadStickers = loadStickers;
window.downloadSingleSticker = downloadSingleSticker;
window.downloadAllStickers = downloadAllStickers;
window.resetStickersFilters = resetStickersFilters;

// Real-time updates are handled by stickers-realtime.js module
