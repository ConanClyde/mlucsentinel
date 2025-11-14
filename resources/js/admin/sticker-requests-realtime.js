// Sticker Requests Real-time Management
class StickerRequestsManager {
    constructor() {
        this.searchTimeout = null;
        this.currentPage = 1;
        this.currentSearch = '';
        this.currentStatus = 'pending';
        this.isLoading = false;
        this.currentRequestId = null;
        
        this.init();
        this.setupEventListeners();
        this.setupBroadcasting();
        this.setupNotifications();
    }

    init() {
        // Set initial status filter in dropdown
        const statusFilter = document.getElementById('statusFilter');
        if (statusFilter) {
            statusFilter.value = this.currentStatus;
        }
        
        // Load initial data
        this.loadRequests();
        
        // Setup auto-refresh every 30 seconds
        setInterval(() => {
            if (!this.isLoading) {
                this.loadRequests(false); // Silent refresh
            }
        }, 30000);
    }

    setupEventListeners() {
        // Search input
        const searchInput = document.getElementById('requestSearch');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                clearTimeout(this.searchTimeout);
                this.searchTimeout = setTimeout(() => {
                    this.currentSearch = e.target.value;
                    this.currentPage = 1;
                    this.loadRequests();
                }, 500);
            });
        }

        // Status filter
        const statusFilter = document.getElementById('statusFilter');
        if (statusFilter) {
            statusFilter.addEventListener('change', (e) => {
                this.currentStatus = e.target.value;
                this.currentPage = 1;
                this.loadRequests();
            });
        }

        // Refresh button
        window.refreshRequests = () => {
            this.loadRequests();
        };

        // Action buttons
        window.viewRequestDetails = (id) => {
            this.viewRequestDetails(id);
        };

        window.approveRequest = (id) => {
            this.approveRequest(id);
        };

        window.rejectRequest = (id) => {
            this.rejectRequest(id);
        };

        // Modal functions
        window.closeApproveRequestModal = () => {
            this.closeApproveRequestModal();
        };

        window.confirmApproveRequest = () => {
            this.confirmApproveRequest();
        };

        window.closeRejectRequestModal = () => {
            this.closeRejectRequestModal();
        };

        window.confirmRejectRequest = () => {
            this.confirmRejectRequest();
        };

        window.closeViewRequestModal = () => {
            this.closeViewRequestModal();
        };

        // Handle reject form submission
        const rejectForm = document.getElementById('rejectRequestForm');
        if (rejectForm) {
            rejectForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.confirmRejectRequest();
            });
        }
    }

    setupBroadcasting() {
        if (typeof window.Echo !== 'undefined') {
            try {
                // Listen for new sticker requests
                window.Echo.channel('sticker-requests')
                    .listen('.sticker-request-created', (e) => {
                        this.handleNewRequest(e);
                    })
                    .listen('.sticker-request-updated', (e) => {
                        this.handleRequestUpdate(e);
                    });

                // Listen for admin notifications
                window.Echo.private('admin-notifications')
                    .listen('.sticker-request-created', (e) => {
                        this.showNotification('New sticker request received!', e.message, 'info');
                    })
                    .error((error) => {
                        console.log('Admin notifications channel auth failed (non-critical)');
                    });
            } catch (error) {
                console.log('Broadcasting setup failed (non-critical):', error);
            }
        }
    }

    setupNotifications() {
        // Request browser notification permission
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }

    async loadRequests(showLoading = true) {
        if (this.isLoading) return;
        
        this.isLoading = true;

        try {
            const params = new URLSearchParams({
                search: this.currentSearch,
                status: this.currentStatus,
                page: this.currentPage
            });

            const response = await fetch(`/stickers/requests-data?${params}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Failed to load requests');
            }

            const data = await response.json();
            this.renderRequests(data.data);
            this.renderPagination(data.pagination);
            
        } catch (error) {
            console.error('Error loading requests:', error);
            this.showError('Failed to load requests. Please try again.');
        } finally {
            this.isLoading = false;
        }
    }


    renderRequests(requests) {
        const tableBody = document.getElementById('requestsTableBody');
        if (!tableBody) return;

        if (requests.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="py-8 px-4 text-center text-[#706f6c] dark:text-[#A1A09A]">
                        No requests found.
                    </td>
                </tr>
            `;
            return;
        }

        tableBody.innerHTML = requests.map(request => this.renderRequestRow(request)).join('');
    }

    renderRequestRow(request) {
        const statusBadge = this.getStatusBadge(request.status);
        const actions = this.getActionButtons(request);
        
        // Generate avatar color
        const colors = ['#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#6366F1', '#8B5CF6', '#EC4899', '#14B8A6', '#F97316', '#06B6D4'];
        const firstLetter = (request.user.first_name || 'U').charAt(0).toUpperCase();
        const hash = firstLetter.charCodeAt(0);
        const avatarColor = colors[hash % colors.length];
        
        return `
            <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A] hover:bg-gray-50 dark:hover:bg-[#161615]" data-request-id="${request.id}">
                <td class="py-2 px-3">
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3 text-white font-semibold text-xs flex-shrink-0" style="background-color: ${avatarColor}">
                            ${firstLetter}
                        </div>
                        <div>
                            <div class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${request.user.first_name} ${request.user.last_name}</div>
                            <div class="text-xs text-[#706f6c] dark:text-[#A1A09A]">${request.user.email}</div>
                        </div>
                    </div>
                </td>
                <td class="py-2 px-3">
                    <div class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${request.vehicle.vehicle_type.name}</div>
                    <div class="text-xs text-[#706f6c] dark:text-[#A1A09A]">${request.vehicle.plate_no || 'No Plate'}</div>
                </td>
                <td class="py-2 px-3">
                    ${statusBadge}
                </td>
                <td class="py-2 px-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">${new Date(request.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</td>
                <td class="py-2 px-3">
                    <div class="flex items-center justify-center gap-2">
                        ${actions}
                    </div>
                </td>
            </tr>
        `;
    }

    getStatusBadge(status) {
        const badges = {
            pending: '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900 text-amber-800 dark:text-amber-200">Pending</span>',
            approved: '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">Approved</span>',
            rejected: '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200">Rejected</span>'
        };
        
        return badges[status] || `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200">${status.charAt(0).toUpperCase() + status.slice(1)}</span>`;
    }

    getActionButtons(request) {
        let buttons = `
            <button onclick="viewRequestDetails(${request.id})" class="btn-view" title="View">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
            </button>
        `;

        if (request.status === 'pending') {
            buttons += `
                <button onclick="approveRequest(${request.id})" class="inline-flex items-center justify-center w-8 h-8 rounded-sm bg-green-600 dark:bg-green-600 text-white hover:bg-green-700 dark:hover:bg-green-700 border border-green-600 dark:border-green-600 hover:border-green-700 dark:hover:border-green-700 transition-all" title="Approve">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                </button>
                <button onclick="rejectRequest(${request.id})" class="btn-delete" title="Reject">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            `;
        }

        return buttons;
    }

    renderPagination(pagination) {
        const container = document.getElementById('paginationContainer');
        if (!container || pagination.last_page <= 1) {
            if (container) container.innerHTML = '';
            return;
        }

        // Simple pagination for now - you can enhance this
        let paginationHtml = '<div class="flex justify-center space-x-2">';
        
        for (let i = 1; i <= pagination.last_page; i++) {
            const isActive = i === pagination.current_page;
            paginationHtml += `
                <button onclick="stickerRequestsManager.goToPage(${i})" 
                        class="px-3 py-2 text-sm ${isActive ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'} rounded">
                    ${i}
                </button>
            `;
        }
        
        paginationHtml += '</div>';
        container.innerHTML = paginationHtml;
    }

    goToPage(page) {
        this.currentPage = page;
        this.loadRequests();
    }

    approveRequest(id) {
        this.currentRequestId = id;
        document.getElementById('approveRequestModal').classList.remove('hidden');
    }

    async confirmApproveRequest() {
        if (!this.currentRequestId) return;

        try {
            const response = await fetch(`/stickers/requests/${this.currentRequestId}/approve`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            
            if (data.success) {
                this.closeApproveRequestModal();
                this.showNotification('Success', data.message, 'success');
                this.loadRequests();

                if (typeof window.switchTab === 'function') {
                    window.switchTab('payment');
                } else {
                    window.location.hash = '#payment';
                }

                if (typeof window.loadPayments === 'function') {
                    window.loadPayments();
                }
            } else {
                throw new Error(data.message || 'Failed to approve request');
            }
        } catch (error) {
            console.error('Error approving request:', error);
            this.showError('Failed to approve request. Please try again.');
        }
    }

    closeApproveRequestModal() {
        document.getElementById('approveRequestModal').classList.add('hidden');
        this.currentRequestId = null;
    }

    rejectRequest(id) {
        this.currentRequestId = id;
        document.getElementById('rejectRequestModal').classList.remove('hidden');
    }

    async confirmRejectRequest() {
        if (!this.currentRequestId) return;

        const reason = document.getElementById('rejection_reason').value.trim();
        if (!reason) {
            alert('Please provide a reason for rejection.');
            return;
        }

        try {
            const response = await fetch(`/stickers/requests/${this.currentRequestId}/reject`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ rejection_reason: reason })
            });

            const data = await response.json();
            
            if (data.success) {
                this.closeRejectRequestModal();
                this.showNotification('Success', data.message, 'success');
                this.loadRequests();
            } else {
                throw new Error(data.message || 'Failed to reject request');
            }
        } catch (error) {
            console.error('Error rejecting request:', error);
            this.showError('Failed to reject request. Please try again.');
        }
    }

    closeRejectRequestModal() {
        document.getElementById('rejectRequestModal').classList.add('hidden');
        document.getElementById('rejection_reason').value = '';
        this.currentRequestId = null;
    }

    viewRequestDetails(id) {
        document.getElementById('viewRequestModal').classList.remove('hidden');
        // Load request details via AJAX
        fetch(`/stickers/requests/${id}`)
            .then(response => response.json())
            .then(data => {
                const statusBadge = data.status === 'pending' ? 'bg-amber-100 dark:bg-amber-900 text-amber-800 dark:text-amber-200' :
                                   data.status === 'approved' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' :
                                   'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200';
                
                document.getElementById('viewRequestContent').innerHTML = `
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Left Side - Sticker Preview -->
                        <div class="lg:col-span-1">
                            <div class="bg-gray-50 dark:bg-[#161615] rounded-lg p-6 text-center sticky top-0">
                                <h4 class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Vehicle Sticker</h4>
                                <div class="inline-block bg-white dark:bg-[#1a1a1a] rounded-lg p-4 border-2 border-dashed border-[#e3e3e0] dark:border-[#3E3E3A] w-full max-w-xs">
                                    ${data.vehicle.sticker ? 
                                        `<img src="${data.vehicle.sticker}" alt="Vehicle Sticker" class="w-full h-auto rounded">` :
                                        `<div class="w-full h-32 flex items-center justify-center text-[#706f6c] dark:text-[#A1A09A]">
                                            <div class="text-center">
                                                <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                                </svg>
                                                <p class="text-sm">No Sticker Available</p>
                                            </div>
                                        </div>`
                                    }
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Side - Request Details -->
                        <div class="lg:col-span-2 space-y-6">
                            <!-- Request Information -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Request ID</label>
                                        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">#${data.id}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Status</label>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusBadge}">
                                            ${data.status.charAt(0).toUpperCase() + data.status.slice(1)}
                                        </span>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">User</label>
                                        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">${data.user.first_name} ${data.user.last_name}</p>
                                        <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">${data.user.email}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Submitted</label>
                                        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">${new Date(data.created_at).toLocaleDateString('en-US', { 
                                            year: 'numeric', month: 'long', day: 'numeric', 
                                            hour: '2-digit', minute: '2-digit' 
                                        })}</p>
                                    </div>
                                </div>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Vehicle Type</label>
                                        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">${data.vehicle.vehicle_type.name}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Plate Number</label>
                                        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">${data.vehicle.plate_no || 'Not specified'}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Vehicle Color</label>
                                        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">${data.vehicle.color || 'Not specified'}</p>
                                    </div>
                                    ${data.processed_by ? `
                                    <div>
                                        <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Processed By</label>
                                        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">${data.processed_by.first_name} ${data.processed_by.last_name}</p>
                                    </div>
                                    ` : ''}
                                </div>
                            </div>
                            
                            <!-- Request Details -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Reason for Request</label>
                                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] bg-gray-50 dark:bg-[#161615] p-3 rounded-lg">${data.reason || 'No reason provided'}</p>
                                </div>
                                ${data.additional_info ? `
                                <div>
                                    <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Additional Information</label>
                                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] bg-gray-50 dark:bg-[#161615] p-3 rounded-lg">${data.additional_info}</p>
                                </div>
                                ` : ''}
                                ${data.admin_notes ? `
                                <div>
                                    <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Admin Notes</label>
                                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg border border-blue-200 dark:border-blue-800">${data.admin_notes}</p>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                `;
            })
            .catch(error => {
                console.error('Error loading request details:', error);
                document.getElementById('viewRequestContent').innerHTML = `
                    <div class="text-center py-8">
                        <p class="text-red-600 dark:text-red-400">Error loading request details. Please try again.</p>
                    </div>
                `;
            });
    }

    closeViewRequestModal() {
        document.getElementById('viewRequestModal').classList.add('hidden');
    }

    handleNewRequest(event) {
        // Add new request to the top of the table
        this.loadRequests();
        this.showNotification('New Request', event.message, 'info');
        this.showBrowserNotification('New Sticker Request', event.message);
    }

    handleRequestUpdate(event) {
        // Update existing request in the table
        const row = document.querySelector(`tr[data-request-id="${event.id}"]`);
        if (row) {
            this.loadRequests(); // Refresh to show updated status
        }
    }

    showNotification(title, message, type = 'info') {
        // Create toast notification
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${this.getNotificationClasses(type)}`;
        toast.innerHTML = `
            <div class="flex items-center">
                <div class="flex-1">
                    <h4 class="font-medium">${title}</h4>
                    <p class="text-sm">${message}</p>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-gray-500 hover:text-gray-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        `;

        document.body.appendChild(toast);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, 5000);
    }

    getNotificationClasses(type) {
        const classes = {
            success: 'bg-green-100 border border-green-200 text-green-800',
            error: 'bg-red-100 border border-red-200 text-red-800',
            info: 'bg-blue-100 border border-blue-200 text-blue-800',
            warning: 'bg-yellow-100 border border-yellow-200 text-yellow-800'
        };
        return classes[type] || classes.info;
    }

    showBrowserNotification(title, message) {
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification(title, {
                body: message,
                icon: '/favicon.ico',
                badge: '/favicon.ico'
            });
        }
    }

    showError(message) {
        this.showNotification('Error', message, 'error');
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('requestsTableBody')) {
        window.stickerRequestsManager = new StickerRequestsManager();
    }
});
