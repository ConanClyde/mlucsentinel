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
            <div class="relative inline-block">
                <button id="actionDropdownBtn" onclick="toggleActionDropdown()" class="btn btn-primary inline-flex items-center gap-2">
                    <span>Actions</span>
                    <x-heroicon-s-chevron-down class="w-4 h-4" />
                </button>
                <div id="actionDropdown" class="hidden absolute bottom-full right-0 mb-2 w-48 bg-white dark:bg-[#1a1a1a] rounded-lg shadow-lg border border-[#e3e3e0] dark:border-[#3E3E3A] z-50">
                    <button onclick="confirmPayment()" class="w-full text-left px-4 py-2 text-sm text-[#1b1b18] dark:text-[#EDEDEC] hover:bg-gray-50 dark:hover:bg-[#161615] rounded-t-lg inline-flex items-center gap-2">
                        <x-heroicon-s-check-circle class="w-4 h-4 text-green-600" />
                        <span>Confirm Payment</span>
                    </button>
                    <button onclick="cancelRequest()" class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-50 dark:hover:bg-[#161615] rounded-b-lg inline-flex items-center gap-2">
                        <x-heroicon-s-x-circle class="w-4 h-4" />
                        <span>Cancel Request</span>
                    </button>
                </div>
            </div>
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
            <button type="button" onclick="closeDeleteConfirmModal()" class="btn btn-secondary">Cancel</button>
            <button type="button" onclick="confirmDeletePayment()" class="btn btn-danger">Delete</button>
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
            <button onclick="printReceipt()" class="btn btn-primary">Print</button>
        </div>
    </div>
</div>

<!-- Delete Transaction Confirmation Modal -->
<div id="deleteTransactionModal" class="modal-backdrop hidden" onclick="if(event.target === this) closeDeleteTransactionModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title text-red-500 flex items-center gap-2">
                <x-heroicon-o-exclamation-triangle class="modal-icon-error" />
                Delete Transaction
            </h2>
        </div>
        <div class="modal-body">
            <p class="text-[#706f6c] dark:text-[#A1A09A]">Are you sure you want to delete this transaction? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button type="button" onclick="closeDeleteTransactionModal()" class="btn btn-secondary">Cancel</button>
            <button type="button" onclick="confirmDeleteTransaction()" class="btn btn-danger">Delete</button>
        </div>
    </div>
</div>

<!-- View Request Details Modal -->
<div id="viewRequestModal" class="modal-backdrop hidden z-[100]" onclick="if(event.target === this) closeViewRequestModal()">
    <div class="modal-container flex flex-col" style="max-width: 900px; width: 95%; max-height: 90vh;">
        <div class="modal-header flex-shrink-0 flex justify-between items-center">
            <h2 class="modal-title">Request Details</h2>
            <button onclick="closeViewRequestModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="modal-body flex-1 overflow-y-auto" id="viewRequestContent">
            <!-- Content will be loaded here -->
        </div>
        <div class="modal-footer flex-shrink-0">
            <button onclick="closeViewRequestModal()" class="btn btn-secondary">Close</button>
        </div>
    </div>
</div>

<!-- Approve Request Modal -->
<div id="approveRequestModal" class="modal-backdrop hidden" onclick="if(event.target === this) closeApproveRequestModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title text-green-600 dark:text-green-400 flex items-center gap-2">
                <svg class="modal-icon-success w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Approve Request
            </h2>
        </div>
        <div class="modal-body">
            <p class="text-[#1b1b18] dark:text-[#EDEDEC]">Are you sure you want to approve this sticker request?</p>
        </div>
        <div class="modal-footer">
            <button onclick="closeApproveRequestModal()" class="btn btn-secondary">Cancel</button>
            <button onclick="confirmApproveRequest()" class="btn btn-success">Approve Request</button>
        </div>
    </div>
</div>

<!-- Reject Request Modal -->
<div id="rejectRequestModal" class="modal-backdrop hidden" onclick="if(event.target === this) closeRejectRequestModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title text-red-500 flex items-center gap-2">
                <x-heroicon-o-exclamation-triangle class="modal-icon-error" />
                Reject Request
            </h2>
        </div>
        <form id="rejectRequestForm" method="POST">
            @csrf
            <div class="modal-body">
                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mb-4">Please provide a reason for rejecting this request.</p>
                <div class="form-group">
                    <label for="rejection_reason" class="form-label">Reason for Rejection</label>
                    <textarea id="rejection_reason" name="rejection_reason" rows="4" required
                        class="form-input"
                        placeholder="Please provide a reason for rejecting this request..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeRejectRequestModal()" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-danger">Reject Request</button>
            </div>
        </form>
    </div>
</div>
