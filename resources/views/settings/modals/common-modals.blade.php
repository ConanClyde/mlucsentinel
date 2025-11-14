<!-- Success Modal -->
<div id="successModal" class="modal-backdrop hidden">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title text-green-600 flex items-center gap-2">
                <svg class="modal-icon-success w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span id="successTitle">Success!</span>
            </h2>
        </div>
        <div class="modal-body">
            <p id="successMessage">Operation completed successfully.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-success" onclick="closeSuccessModal()">Okay</button>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div id="errorModal" class="modal-backdrop hidden">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title text-red-500 flex items-center gap-2">
                <svg class="modal-icon-error w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Error
            </h2>
        </div>
        <div class="modal-body">
            <p id="errorMessage">An error occurred while processing your request. Please try again.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeErrorModal()">Close</button>
        </div>
    </div>
</div>

