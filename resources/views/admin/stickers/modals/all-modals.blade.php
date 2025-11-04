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
