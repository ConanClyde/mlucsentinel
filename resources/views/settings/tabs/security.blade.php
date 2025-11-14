<!-- Security Settings -->
<div id="content-security" class="settings-content hidden bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
    <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Security Settings</h3>
    <div class="space-y-6">
        <!-- Password Management Info -->
        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
            <div class="flex items-start">
                <x-heroicon-o-information-circle class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-3 mt-0.5" />
                <div>
                    <p class="text-sm font-medium text-blue-900 dark:text-blue-300">Password Management</p>
                    <p class="text-xs text-blue-700 dark:text-blue-400 mt-1">To change your password, please visit your <a href="{{ route('profile') }}" class="underline">Profile</a> page.</p>
                </div>
            </div>
        </div>

        <!-- Two-Factor Authentication -->
        <div class="border-t border-[#e3e3e0] dark:border-[#3E3E3A] pt-4">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Two-Factor Authentication</p>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Add an extra layer of security to your account</p>
                </div>
                @if($twoFactorEnabled)
                    <span class="px-3 py-1 text-xs font-medium text-green-700 bg-green-100 dark:bg-green-900/30 dark:text-green-400 rounded-full">Enabled</span>
                @else
                    <span class="px-3 py-1 text-xs font-medium text-gray-700 bg-gray-100 dark:bg-gray-800 dark:text-gray-400 rounded-full">Disabled</span>
                @endif
            </div>
            <div class="mt-3 flex gap-2">
                @if($twoFactorEnabled)
                    <button onclick="show2FARecoveryCodes()" class="btn btn-secondary text-xs">View Recovery Codes</button>
                    <button onclick="showDisable2FAModal()" class="btn btn-danger text-xs">Disable 2FA</button>
                @else
                    <button onclick="enable2FA()" class="btn btn-primary text-xs">Enable 2FA</button>
                @endif
            </div>
        </div>

        <!-- Login Activity -->
        <div class="border-t border-[#e3e3e0] dark:border-[#3E3E3A] pt-4">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Login Activity</p>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Recent login history and device information</p>
                </div>
                <button onclick="loadActivityLogs()" class="btn btn-secondary text-xs">
                    <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Refresh
                </button>
            </div>
            <div id="activity-logs-container" class="space-y-2 max-h-96 overflow-y-auto pr-2">
                <div class="text-center py-8 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    Loading activity logs...
                </div>
            </div>
        </div>
    </div>
</div>

