<!-- Enable 2FA Modal - Step 1: QR Code -->
<div id="enable2FAModal" class="modal-backdrop hidden">
    <div class="modal-container max-w-md">
        <div class="modal-header">
            <h2 class="modal-title">Enable Two-Factor Authentication</h2>
        </div>
        <div class="modal-body">
            <div class="text-center mb-4">
                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mb-4">Scan this QR code with your authenticator app (Google Authenticator, Authy, etc.)</p>
                <div id="qr-code-container" class="flex justify-center mb-4">
                    <!-- QR Code will be inserted here -->
                </div>
                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mb-2">Or enter this code manually:</p>
                <code id="2fa-secret" class="px-3 py-2 bg-gray-100 dark:bg-gray-800 rounded text-sm font-mono"></code>
            </div>
            <div class="mt-6">
                <label class="form-label">Enter the 6-digit code from your app</label>
                <input type="text" id="2fa-verification-code" class="form-input text-center text-2xl tracking-widest" maxlength="6" placeholder="000000">
            </div>
        </div>
        <div class="modal-footer">
            <button onclick="closeEnable2FAModal()" class="btn btn-secondary">Cancel</button>
            <button onclick="confirm2FA()" class="btn btn-primary">Verify & Enable</button>
        </div>
    </div>
</div>

<!-- Recovery Codes Modal -->
<div id="recoveryCodesModal" class="modal-backdrop hidden">
    <div class="modal-container max-w-md">
        <div class="modal-header">
            <h2 class="modal-title text-green-600 flex items-center gap-2">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                Recovery Codes
            </h2>
        </div>
        <div class="modal-body">
            <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg mb-4">
                <p class="text-sm text-yellow-800 dark:text-yellow-300">
                    <strong>Important:</strong> Save these recovery codes in a safe place. You can use them to access your account if you lose your device.
                </p>
            </div>
            <div id="recovery-codes-list" class="grid grid-cols-2 gap-2 mb-4">
                <!-- Recovery codes will be inserted here -->
            </div>
            <button onclick="copyRecoveryCodes()" class="btn btn-secondary w-full mb-2">
                <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                Copy All Codes
            </button>
        </div>
        <div class="modal-footer">
            <button onclick="closeRecoveryCodesModal()" class="btn btn-primary">Done</button>
        </div>
    </div>
</div>

<!-- Disable 2FA Modal -->
<div id="disable2FAModal" class="modal-backdrop hidden" onclick="if(event.target === this) closeDisable2FAModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title text-red-500 flex items-center gap-2">
                <x-heroicon-o-exclamation-triangle class="modal-icon-error" />
                Disable Two-Factor Authentication
            </h2>
        </div>
        <div class="modal-body">
            <div class="mb-4">
                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mb-4">
                    <strong class="text-red-600 dark:text-red-400">Warning:</strong> Disabling 2FA will make your account less secure.
                </p>
                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    If you're sure you want to proceed, please enter your password below to confirm.
                </p>
            </div>
            <form id="disable2FAForm">
                <div class="form-group">
                    <label class="form-label">Enter your password to confirm</label>
                    <div class="relative">
                        <input type="password" id="disable-2fa-password" class="form-input pr-10" required placeholder="Enter your current password">
                        <button type="button" class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-[#706f6c] dark:text-[#A1A09A] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC] transition-colors" data-target="disable-2fa-password">
                            <x-heroicon-c-eye class="w-5 h-5 eye-icon" />
                            <x-heroicon-c-eye-slash class="w-5 h-5 eye-slash-icon hidden" />
                        </button>
                    </div>
                    <div id="disable-2fa-password-error" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" onclick="closeDisable2FAModal()" class="btn btn-secondary">Cancel</button>
            <button type="button" onclick="confirmDisable2FA()" class="btn btn-danger">Disable 2FA</button>
        </div>
    </div>
</div>

<!-- View Recovery Codes Modal (requires password) -->
<div id="viewRecoveryCodesModal" class="modal-backdrop hidden" onclick="if(event.target === this) closeViewRecoveryCodesModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title">View Recovery Codes</h2>
        </div>
        <div class="modal-body">
            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mb-4">
                Enter your password to view your recovery codes.
            </p>
            <form id="viewRecoveryCodesForm">
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="relative">
                        <input type="password" id="view-recovery-password" class="form-input pr-10" required placeholder="Enter your password">
                        <button type="button" class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-[#706f6c] dark:text-[#A1A09A] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC] transition-colors" data-target="view-recovery-password">
                            <x-heroicon-c-eye class="w-5 h-5 eye-icon" />
                            <x-heroicon-c-eye-slash class="w-5 h-5 eye-slash-icon hidden" />
                        </button>
                    </div>
                    <div id="view-recovery-password-error" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" onclick="closeViewRecoveryCodesModal()" class="btn btn-secondary">Cancel</button>
            <button type="button" onclick="confirmViewRecoveryCodes()" class="btn btn-primary">View Codes</button>
        </div>
    </div>
</div>
