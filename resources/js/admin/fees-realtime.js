/**
 * Real-time Fee Updates Module
 * 
 * This module handles real-time updates for the fees table
 * using Laravel Echo and Reverb WebSocket server.
 */

class FeesRealtime {
    constructor() {
        this.connectionStatus = null;
        this.tableBody = null;
        this.fees = [];
        this.channel = null;
        this.retryCount = 0;
        this.maxRetries = 5;
    }

    /**
     * Initialize the real-time connection
     */
    init(fees = []) {
        this.fees = fees;
        this.connectionStatus = document.getElementById('feesConnectionStatus');
        this.tableBody = document.getElementById('fees-table-body');

        if (!this.connectionStatus || !this.tableBody) {
            console.error('Required DOM elements not found for fees real-time');
            return;
        }

        this.setupEcho();
    }

    /**
     * Setup Laravel Echo connection
     */
    setupEcho() {
        if (!window.Echo) {
            if (this.retryCount < this.maxRetries) {
                this.retryCount++;
                console.log(`Echo not ready for fees, retrying... (${this.retryCount}/${this.maxRetries})`);
                setTimeout(() => this.setupEcho(), 500);
            } else {
                console.error('Echo failed to initialize after maximum retries');
                this.updateConnectionStatus('error');
            }
            return;
        }

        console.log('Setting up Echo connection for fees...');
        this.updateConnectionStatus('connecting');

        try {
            // Subscribe to the fees channel
            this.channel = window.Echo.channel('fees');

            // Listen for fee.updated events
            this.channel.listen('.fee.updated', (event) => {
                console.log('Received fee broadcast event:', event);
                this.handleFeeUpdate(event);
            });

            // Handle connection success
            this.channel.subscribed(() => {
                console.log('Successfully subscribed to fees channel');
                this.updateConnectionStatus('connected');
            });

            // Handle connection errors
            this.channel.error((error) => {
                console.error('Channel subscription error:', error);
                this.updateConnectionStatus('error');
            });

        } catch (error) {
            console.error('Error setting up Echo:', error);
            this.updateConnectionStatus('error');
        }
    }

    /**
     * Update connection status indicator
     */
    updateConnectionStatus(status) {
        if (!this.connectionStatus) return;

        const statusClasses = {
            connecting: 'bg-yellow-500',
            connected: 'bg-green-500',
            error: 'bg-red-500',
            disconnected: 'bg-gray-500'
        };

        // Remove all status classes
        Object.values(statusClasses).forEach(cls => {
            this.connectionStatus.classList.remove(cls);
        });

        // Add the current status class
        this.connectionStatus.classList.add(statusClasses[status] || statusClasses.disconnected);

        // Update title attribute for tooltip
        const statusTexts = {
            connecting: 'Connecting to live updates...',
            connected: 'Connected - Live updates active',
            error: 'Connection error - Updates may be delayed',
            disconnected: 'Disconnected from live updates'
        };

        this.connectionStatus.title = statusTexts[status] || 'Unknown status';
    }

    /**
     * Handle fee update events
     */
    handleFeeUpdate(event) {
        const { fee, action, editor } = event;

        switch (action) {
            case 'updated':
                this.updateFee(fee);
                this.showBrowserNotification(
                    'Fee Updated',
                    `${editor} updated ${fee.display_name || 'a fee'} to ₱${parseFloat(fee.amount || 0).toFixed(2)}`,
                    fee.id,
                    'updated'
                );
                break;
            default:
                console.warn('Unknown action:', action);
        }
    }

    /**
     * Update a fee in the table
     */
    updateFee(fee) {
        const row = this.tableBody.querySelector(`tr[data-fee-id="${fee.id}"]`);

        if (row) {
            // Update the amount
            const amountCell = row.querySelector('.fee-amount');
            if (amountCell) {
                amountCell.textContent = `₱${parseFloat(fee.amount).toFixed(2)}`;
            }

            // Add highlight animation
            row.classList.add('animate-highlight');

            // Remove animation class after animation completes
            setTimeout(() => {
                row.classList.remove('animate-highlight');
            }, 1000);
        }
    }

    /**
     * Show browser notification
     */
    showBrowserNotification(title, message, feeId, action) {
        // Check if user has enabled browser notifications
        const notificationsEnabled = localStorage.getItem('browserNotificationsEnabled') === 'true';
        
        if (!notificationsEnabled || Notification.permission !== 'granted') {
            // Fallback to in-page notification
            this.displayNotification(title, message, action);
            return;
        }

        // Create browser notification
        const notification = new Notification(title, {
            body: message,
            icon: '/images/logo.png',
            badge: '/images/logo.png',
            tag: `fee-${feeId}-${Date.now()}`,
        });

        // Auto-close notification after 5 seconds
        setTimeout(() => notification.close(), 5000);

        // Also show in-page notification
        this.displayNotification(title, message, action);
    }

    /**
     * Display in-page notification
     */
    displayNotification(title, message, action) {
        // No toast notification - only browser notifications
        // This matches the pattern used by other realtime managers
    }

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
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

    /**
     * Disconnect from the channel
     */
    disconnect() {
        if (this.channel) {
            window.Echo.leave('fees');
            this.channel = null;
            this.updateConnectionStatus('disconnected');
            console.log('Disconnected from fees channel');
        }
    }
}

// Initialize and make available globally
if (typeof window !== 'undefined') {
    window.FeesRealtime = FeesRealtime;
}

