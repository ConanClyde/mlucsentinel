<!-- Edit Fee Modal -->
<div id="edit-fee-modal" class="modal-backdrop hidden" onclick="if(event.target === this) closeEditFeeModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title">Edit Fee Amount</h2>
        </div>
        <form id="editFeeForm">
            <div class="modal-body">
                <input type="hidden" id="edit-fee-id">
                <div class="form-group mb-4">
                    <label class="form-label">Fee Name</label>
                    <input type="text" id="edit-fee-display-name" class="form-input bg-gray-100 dark:bg-[#2a2a2a]" readonly>
                </div>
                <div class="form-group mb-4">
                    <label class="form-label">Description</label>
                    <input type="text" id="edit-fee-description" class="form-input bg-gray-100 dark:bg-[#2a2a2a]" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Amount (₱) <span class="text-red-500">*</span></label>
                    <input type="number" id="edit-fee-amount" class="form-input" placeholder="0.00" step="0.01" min="0" max="99999999.99" required>
                    <p id="edit-fee-amount-error" class="text-red-500 text-xs mt-1 hidden"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeEditFeeModal()" class="btn btn-secondary">Cancel</button>
                <button type="button" id="update-fee-btn" onclick="updateFee()" class="btn btn-primary" disabled>Update Fee</button>
            </div>
        </form>
    </div>
</div>

