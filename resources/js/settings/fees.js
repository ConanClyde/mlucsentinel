/**
 * Fees Settings Management
 */

let feesRealtimeManager = null;
let feesLoaded = false;

export function initializeFees() {
    loadFees();
}

// Helper function to escape HTML to prevent XSS
function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

export function loadFees() {
    if (feesLoaded) return;
    
    const tbody = document.getElementById('fees-table-body');
    if (!tbody) return;
    
    // Check if fees are already server-rendered (has rows with data-fee-id)
    const existingFees = tbody.querySelectorAll('tr[data-fee-id]');
    if (existingFees.length > 0) {
        feesLoaded = true;
        // Initialize realtime manager with server-rendered fees
        if (window.FeesRealtime && !feesRealtimeManager) {
            // Extract fee data from server-rendered rows
            const fees = Array.from(existingFees).map(row => {
                const id = row.getAttribute('data-fee-id');
                const displayName = row.querySelector('td:nth-child(1) span').textContent;
                const description = row.querySelector('td:nth-child(2) span').textContent;
                const amount = parseFloat(row.querySelector('td:nth-child(3) span').textContent.replace(/,/g, ''));
                return { id: parseInt(id), display_name: displayName, description, amount };
            });
            feesRealtimeManager = new window.FeesRealtime();
            feesRealtimeManager.init(fees);
        }
        return;
    }
    
    // Fallback: fetch fees via AJAX if not server-rendered
    fetch('/api/fees')
        .then(response => response.json())
        .then(fees => {
            if (fees.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">
                            No fees found
                        </td>
                    </tr>
                `;
                return;
            }
            
            tbody.innerHTML = '';
            fees.forEach(fee => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50 dark:hover:bg-[#2a2a2a] transition-colors';
                row.setAttribute('data-fee-id', fee.id);
                row.innerHTML = `
                    <td class="px-4 py-3">
                        <span class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${escapeHtml(fee.display_name)}</span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">${escapeHtml(fee.description || '')}</span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <span class="text-sm font-semibold text-[#1b1b18] dark:text-[#EDEDEC] fee-amount">₱${parseFloat(fee.amount).toFixed(2)}</span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-2">
                            <button onclick="openEditFeeModal(${fee.id}, '${escapeHtml(fee.display_name)}', '${escapeHtml(fee.description || '')}', ${fee.amount})" class="btn-edit" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(row);
            });
            
            feesLoaded = true;
            
            // Initialize realtime manager
            if (window.FeesRealtime && !feesRealtimeManager) {
                feesRealtimeManager = new window.FeesRealtime();
                feesRealtimeManager.init(fees);
            }
        })
        .catch(err => {
            console.error('Error loading fees:', err);
            const tbody = document.getElementById('fees-table-body');
            if (tbody) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-sm text-red-500">
                            Error loading fees
                        </td>
                    </tr>
                `;
            }
        });
}

// Helper functions
function showError(inputId, errorId, message) {
    const errorEl = document.getElementById(errorId);
    if (errorEl) {
        errorEl.textContent = message;
        errorEl.classList.remove('hidden');
    }
    const inputEl = document.getElementById(inputId);
    if (inputEl) {
        inputEl.classList.add('border-red-500');
    }
}

function hideError(inputId, errorId) {
    const errorEl = document.getElementById(errorId);
    if (errorEl) {
        errorEl.classList.add('hidden');
        errorEl.textContent = '';
    }
    const inputEl = document.getElementById(inputId);
    if (inputEl) {
        inputEl.classList.remove('border-red-500');
    }
}

function validateEditFeeForm() {
    const amount = document.getElementById('edit-fee-amount').value;
    const updateBtn = document.getElementById('update-fee-btn');
    
    if (!amount || parseFloat(amount) < 0 || isNaN(parseFloat(amount))) {
        updateBtn.disabled = true;
        return false;
    }
    
    hideError('edit-fee-amount', 'edit-fee-amount-error');
    updateBtn.disabled = false;
    return true;
}

export function openEditFeeModal(id, displayName, description, currentAmount) {
    // If only ID is provided, extract data from the table row
    if (displayName === undefined || displayName === null) {
        const row = document.querySelector(`tr[data-fee-id="${id}"]`);
        if (row) {
            // Try to get from data attributes first (more reliable)
            displayName = row.dataset.feeDisplayName || '';
            description = row.dataset.feeDescription || '';
            currentAmount = row.dataset.feeAmount || 0;
            
            // Fallback to extracting from cells if data attributes not available
            if (!displayName) {
                const nameCell = row.querySelector('td:nth-child(1) span');
                displayName = nameCell ? nameCell.textContent.trim() : '';
            }
            if (!description) {
                const descCell = row.querySelector('td:nth-child(2) span');
                description = descCell ? descCell.textContent.trim() : '';
            }
            if (!currentAmount || currentAmount === 0) {
                const amountCell = row.querySelector('td:nth-child(3) span');
                if (amountCell) {
                    const amountText = amountCell.textContent.trim().replace(/[₱,]/g, '');
                    currentAmount = parseFloat(amountText) || 0;
                }
            }
        } else if (feesRealtimeManager) {
            // Try to get from realtime manager
            const fee = feesRealtimeManager.fees?.find(f => f.id === Number(id));
            if (fee) {
                displayName = fee.display_name || '';
                description = fee.description || '';
                currentAmount = fee.amount || 0;
            }
        }
    }
    
    document.getElementById('edit-fee-id').value = id;
    document.getElementById('edit-fee-display-name').value = displayName || '';
    document.getElementById('edit-fee-description').value = description || '';
    document.getElementById('edit-fee-amount').value = parseFloat(currentAmount || 0).toFixed(2);
    document.getElementById('edit-fee-modal').classList.remove('hidden');
    const amountInput = document.getElementById('edit-fee-amount');
    if (amountInput) amountInput.addEventListener('input', validateEditFeeForm);
    validateEditFeeForm();
}

export function closeEditFeeModal() {
    document.getElementById('edit-fee-modal').classList.add('hidden');
    document.getElementById('edit-fee-id').value = '';
    document.getElementById('edit-fee-display-name').value = '';
    document.getElementById('edit-fee-description').value = '';
    document.getElementById('edit-fee-amount').value = '';
    hideError('edit-fee-amount', 'edit-fee-amount-error');
}

export function updateFee() {
    const id = document.getElementById('edit-fee-id').value;
    const amount = document.getElementById('edit-fee-amount').value;
    
    if (!amount || parseFloat(amount) < 0 || isNaN(parseFloat(amount))) {
        showError('edit-fee-amount', 'edit-fee-amount-error', 'Please enter a valid fee amount');
        return;
    }
    
    hideError('edit-fee-amount', 'edit-fee-amount-error');
    
    fetch(`/api/fees/${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ amount: parseFloat(amount) })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.showSuccessModal('Success!', 'Fee updated successfully!');
            closeEditFeeModal();
            if (feesRealtimeManager && data.data) {
                feesRealtimeManager.updateFee(data.data);
            }
            feesLoaded = false;
            loadFees();
        } else {
            showError('edit-fee-amount', 'edit-fee-amount-error', data.message || 'Failed to update fee');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('edit-fee-amount', 'edit-fee-amount-error', 'An error occurred while updating the fee');
    });
}

// Make functions globally available
window.openEditFeeModal = openEditFeeModal;
window.closeEditFeeModal = closeEditFeeModal;
window.updateFee = updateFee;

