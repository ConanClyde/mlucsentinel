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

        // Listen to payments channel
        window.Echo.channel('payments')
            .listen('.payment.updated', (event) => {
                console.log('Received payment update:', event);
                this.handlePaymentUpdate(event);
            });

        console.log('Stickers real-time listening on payments channel');
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
            console.log('Skipping notification for own action');
            return;
        }

        switch (action) {
            case 'created':
                this.showBrowserNotification(
                    'New Payment Request',
                    `${editor} created a payment request for ${payment.user?.first_name} ${payment.user?.last_name}`,
                    payment.id,
                    'created'
                );
                // Refresh current tab
                if (typeof window.loadPayments === 'function' && window.currentTab === 'payment') {
                    window.loadPayments();
                }
                break;

            case 'updated':
                // Check if status changed
                if (payment.status === 'paid' && editor && !this.isCurrentUserAction(payment.id)) {
                    this.showBrowserNotification(
                        'Payment Confirmed',
                        `${editor} confirmed payment for ${payment.reference}`,
                        payment.id,
                        'updated'
                    );
                } else if (payment.status === 'cancelled' && editor && !this.isCurrentUserAction(payment.id)) {
                    this.showBrowserNotification(
                        'Payment Cancelled',
                        `${editor} cancelled payment for ${payment.reference}`,
                        payment.id,
                        'cancelled'
                    );
                }

                // Refresh both tabs
                if (typeof window.loadPayments === 'function') {
                    window.loadPayments();
                }
                if (typeof window.loadTransactions === 'function') {
                    window.loadTransactions();
                }
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

