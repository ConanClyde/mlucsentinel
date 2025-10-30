/**
 * Stickers Real-time Management
 * Handles real-time updates for payment requests and transactions
 */

class StickersRealtime {
    constructor() {
        this.isInitialized = false;
    }

    init() {
        // Guard: Only run on stickers page
        if (!window.location.pathname.includes('/stickers')) {
            return;
        }

        console.log('Initializing stickers real-time updates...');
        this.setupEcho();
        this.isInitialized = true;
    }

    setupEcho() {
        if (!window.Echo) {
            console.error('Laravel Echo is not available');
            return;
        }

        // Listen to private payments channel
        window.Echo.private('payments')
            .listen('.payment.updated', (event) => {
                console.log('Received payment update:', event);
                this.handlePaymentUpdate(event);
            });

        console.log('Stickers real-time listening on private payments channel');
        this.updateConnectionStatus(true);
    }

    updateConnectionStatus(connected) {
        const paymentStatus = document.getElementById('paymentConnectionStatus');
        const transactionsStatus = document.getElementById('transactionsConnectionStatus');
        
        if (paymentStatus) {
            paymentStatus.className = connected 
                ? 'w-3 h-3 rounded-full bg-green-500' 
                : 'w-3 h-3 rounded-full bg-red-500';
        }
        
        if (transactionsStatus) {
            transactionsStatus.className = connected 
                ? 'w-3 h-3 rounded-full bg-green-500' 
                : 'w-3 h-3 rounded-full bg-red-500';
        }
    }

    handlePaymentUpdate(event) {
        const { payment, action, editor } = event;

        console.log(`Payment ${action}:`, payment);

        // Check if this action was performed by current user
        if (editor && this.isCurrentUserAction(payment.id)) {
            console.log('Skipping reload for own action');
            return;
        }

        // Show notification
        switch (action) {
            case 'created':
                this.showBrowserNotification(
                    'New Payment Request',
                    `${editor} created a payment request for ${payment.user?.first_name} ${payment.user?.last_name}`,
                    payment.id,
                    'created'
                );
                // Add to payments table if on payment tab
                if (window.currentTab === 'payment' && payment.status === 'pending') {
                    this.addPaymentToTable(payment);
                    this.updatePaymentBadge(1);
                }
                break;

            case 'updated':
                if (payment.status === 'paid') {
                    this.showBrowserNotification(
                        'Payment Confirmed',
                        `${editor} confirmed payment for ${payment.reference}`,
                        payment.id,
                        'updated'
                    );
                    // Remove from payments table and update transactions
                    this.removePaymentFromTable(payment.id);
                    this.updatePaymentBadge(-1);
                    if (window.currentTab === 'transactions') {
                        this.addTransactionToTable(payment);
                    }
                } else if (payment.status === 'cancelled') {
                    this.showBrowserNotification(
                        'Payment Cancelled',
                        `${editor} cancelled payment for ${payment.reference}`,
                        payment.id,
                        'cancelled'
                    );
                    // Remove from payments table
                    this.removePaymentFromTable(payment.id);
                    this.updatePaymentBadge(-1);
                }
                break;
                
            case 'deleted':
                this.showBrowserNotification(
                    'Payment Deleted',
                    `${editor} deleted payment for ${payment.reference}`,
                    payment.id,
                    'deleted'
                );
                // Remove from payments table
                this.removePaymentFromTable(payment.id);
                this.updatePaymentBadge(-1);
                break;
        }
    }

    showBrowserNotification(title, body, paymentId, type) {
        // Check if browser notifications are supported and allowed
        if (!('Notification' in window)) {
            console.log('Browser notifications not supported');
            return;
        }

        if (Notification.permission === 'granted') {
            const notification = new Notification(title, {
                body: body,
                icon: '/favicon.ico',
                tag: `payment-${paymentId}`,
            });

            notification.onclick = function() {
                window.focus();
                // Switch to appropriate tab
                if (type === 'created' && typeof window.switchTab === 'function') {
                    window.switchTab('payment');
                } else if (type === 'updated' && typeof window.switchTab === 'function') {
                    window.switchTab('transactions');
                }
                notification.close();
            };
        } else if (Notification.permission !== 'denied') {
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    this.showBrowserNotification(title, body, paymentId, type);
                }
            });
        }
    }

    isCurrentUserAction(paymentId) {
        // Check if this payment was just modified by the current user
        if (!window._recentActions) {
            return false;
        }

        const actionTime = window._recentActions.get(paymentId);
        if (!actionTime) {
            return false;
        }

        // Consider it a recent action if it happened in the last 3 seconds
        const isRecent = (Date.now() - actionTime) < 3000;
        
        // Clean up old actions
        if (!isRecent) {
            window._recentActions.delete(paymentId);
        }

        return isRecent;
    }

    markUserAction(paymentId) {
        if (!window._recentActions) {
            window._recentActions = new Map();
        }
        window._recentActions.set(paymentId, Date.now());
    }

    updatePaymentBadge(change) {
        const badge = document.getElementById('paymentCount');
        if (!badge) return;
        
        let currentCount = parseInt(badge.textContent) || 0;
        currentCount = Math.max(0, currentCount + change);
        badge.textContent = currentCount;
        
        // Add pulse animation
        badge.classList.add('animate-pulse');
        setTimeout(() => badge.classList.remove('animate-pulse'), 1000);
    }

    addPaymentToTable(payment) {
        const tbody = document.getElementById('paymentTableBody');
        if (!tbody) {
            console.log('Payment table body not found');
            return;
        }
        
        // Check if payment already exists
        const existingRow = tbody.querySelector(`tr[data-payment-id="${payment.id}"]`);
        if (existingRow) {
            console.log('Payment already exists in table');
            return;
        }
        
        // Remove "no payments" message if exists
        const emptyRow = tbody.querySelector('td[colspan]');
        if (emptyRow) {
            emptyRow.closest('tr').remove();
        }
        
        const vehicleInfo = payment.vehicle_count > 1 
            ? `<p class="text-sm font-medium text-blue-600 dark:text-blue-400">${payment.vehicle_count} Vehicles</p>
               <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Click view to see all</p>`
            : `<p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">${payment.vehicle?.type?.name || 'N/A'}</p>
               <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">${payment.vehicle?.plate_no || ''}</p>`;
        
        const row = document.createElement('tr');
        row.className = 'border-b border-[#e3e3e0] dark:border-[#3E3E3A] hover:bg-gray-50 dark:hover:bg-[#161615] animate-fade-in';
        row.dataset.paymentId = payment.id;
        row.innerHTML = `
            <td class="py-2 px-3"><span class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${payment.reference}</span></td>
            <td class="py-2 px-3">
                <div>
                    <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${payment.user?.first_name} ${payment.user?.last_name}</p>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">${payment.user?.user_type || 'N/A'}</p>
                </div>
            </td>
            <td class="py-2 px-3">${vehicleInfo}</td>
            <td class="py-2 px-3"><span class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">₱${parseFloat(payment.amount).toFixed(2)}</span></td>
            <td class="py-2 px-3"><span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">${new Date(payment.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</span></td>
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
        `;
        
        tbody.insertBefore(row, tbody.firstChild);
        console.log('Payment row added to table with animation');
        
        // Remove animation class after it completes
        setTimeout(() => {
            row.classList.remove('animate-fade-in');
        }, 500);
    }

    removePaymentFromTable(paymentId) {
        const tbody = document.getElementById('paymentTableBody');
        if (!tbody) return;
        
        const row = tbody.querySelector(`tr[data-payment-id="${paymentId}"]`);
        if (row) {
            row.remove();
        }
        
        // Show empty message if no rows left
        if (tbody.querySelectorAll('tr').length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="py-8 px-4 text-center text-[#706f6c] dark:text-[#A1A09A]">
                        No pending payments
                    </td>
                </tr>
            `;
        }
    }

    addTransactionToTable(payment) {
        const tbody = document.getElementById('transactionsTableBody');
        if (!tbody) return;
        
        // Remove "no transactions" message if exists
        const emptyRow = tbody.querySelector('td[colspan]');
        if (emptyRow) {
            emptyRow.parentElement.remove();
        }
        
        const vehicleInfo = payment.vehicle_count > 1 
            ? `<p class="text-sm font-medium text-blue-600 dark:text-blue-400">${payment.vehicle_count} Vehicles</p>
               <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Click view to see all</p>`
            : `<p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">${payment.vehicle?.type?.name || 'N/A'}</p>
               <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">${payment.vehicle?.plate_no || ''}</p>`;
        
        const statusBadge = payment.status === 'paid' 
            ? '<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Paid</span>'
            : '<span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Cancelled</span>';
        
        const row = document.createElement('tr');
        row.className = 'border-b border-[#e3e3e0] dark:border-[#3E3E3A] hover:bg-gray-50 dark:hover:bg-[#161615]';
        row.dataset.transactionId = payment.id;
        row.innerHTML = `
            <td class="py-2 px-3"><span class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${payment.reference}</span></td>
            <td class="py-2 px-3">
                <div>
                    <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${payment.user?.first_name} ${payment.user?.last_name}</p>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">${payment.user?.user_type || 'N/A'}</p>
                </div>
            </td>
            <td class="py-2 px-3">${vehicleInfo}</td>
            <td class="py-2 px-3"><span class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">₱${parseFloat(payment.amount).toFixed(2)}</span></td>
            <td class="py-2 px-3">${statusBadge}</td>
            <td class="py-2 px-3"><span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">${new Date(payment.updated_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</span></td>
            <td class="py-2 px-3">
                <button onclick="printReceipt(${payment.id})" class="btn-view" title="Print Receipt">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </td>
        `;
        
        tbody.insertBefore(row, tbody.firstChild);
        
        // Add highlight animation
        row.classList.add('bg-green-50', 'dark:bg-green-900/20');
        setTimeout(() => {
            row.classList.remove('bg-green-50', 'dark:bg-green-900/20');
        }, 2000);
    }

    disconnect() {
        if (window.Echo) {
            window.Echo.leaveChannel('payments');
        }
        this.isInitialized = false;
    }
}

// Export for use
window.StickersRealtime = StickersRealtime;

// Auto-initialize if on stickers page
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        const manager = new StickersRealtime();
        manager.init();
        window.stickersRealtimeManager = manager;
    });
} else {
    const manager = new StickersRealtime();
    manager.init();
    window.stickersRealtimeManager = manager;
}

