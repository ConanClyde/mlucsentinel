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
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.829-2.828z"></path>
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

export function openEditFeeModal(id, displayName, description, currentAmount) {
    document.getElementById('edit-fee-id').value = id;
    document.getElementById('edit-fee-display-name').value = displayName;
    document.getElementById('edit-fee-description').value = description;
    document.getElementById('edit-fee-amount').value = parseFloat(currentAmount).toFixed(2);
    document.getElementById('edit-fee-modal').classList.remove('hidden');
}

export function closeEditFeeModal() {
    document.getElementById('edit-fee-modal').classList.add('hidden');
    document.getElementById('edit-fee-id').value = '';
    document.getElementById('edit-fee-display-name').value = '';
    document.getElementById('edit-fee-description').value = '';
    document.getElementById('edit-fee-amount').value = '';
}

export function updateFee() {
    const id = document.getElementById('edit-fee-id').value;
    const amount = document.getElementById('edit-fee-amount').value;
    
    if (!amount || parseFloat(amount) < 0) {
        window.showErrorModal('Please enter a valid fee amount');
        return;
    }
    
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
            window.showErrorModal(data.message || 'Failed to update fee');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.showErrorModal('An error occurred while updating the fee');
    });
}

// Make functions globally available
window.openEditFeeModal = openEditFeeModal;
window.closeEditFeeModal = closeEditFeeModal;
window.updateFee = updateFee;

