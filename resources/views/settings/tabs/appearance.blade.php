<!-- Appearance Settings -->
<div id="content-appearance" class="settings-content bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
    <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Appearance Settings</h3>
    <div class="space-y-6">
        <!-- Theme Selection -->
        <div>
            <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Theme</label>
            <div class="flex items-center space-x-4">
                <button id="theme-light" onclick="setThemePreference('light')" class="theme-option flex items-center justify-center w-24 h-24 border-2 border-[#e3e3e0] dark:border-[#3E3E3A] rounded-lg hover:border-blue-500 transition-colors">
                    <div class="text-center">
                        <x-heroicon-o-sun class="w-8 h-8 mx-auto mb-2 text-yellow-500" />
                        <span class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Light</span>
                    </div>
                </button>
                <button id="theme-dark" onclick="setThemePreference('dark')" class="theme-option flex items-center justify-center w-24 h-24 border-2 border-[#e3e3e0] dark:border-[#3E3E3A] rounded-lg hover:border-blue-500 transition-colors">
                    <div class="text-center">
                        <x-heroicon-o-moon class="w-8 h-8 mx-auto mb-2 text-indigo-500" />
                        <span class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Dark</span>
                    </div>
                </button>
            </div>
        </div>

        <!-- Install App Section -->
        <div class="border-t border-[#e3e3e0] dark:border-[#3E3E3A] pt-6">
            <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Progressive Web App</label>
            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mb-4">Install MLUC Sentinel as an app on your device for quick access and offline functionality.</p>
            
            <!-- Install Button -->
            <button id="pwa-install-btn" style="display: none;" class="btn btn-primary flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Install App</span>
            </button>
            
            <!-- Already Installed Message -->
            <div id="pwa-installed-message" style="display: none;" class="flex items-center gap-2 text-sm text-green-600 dark:text-green-400">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>App is installed</span>
            </div>
            
            <!-- Not Available Message (for unsupported browsers) -->
            <div id="pwa-not-available" style="display: none;" class="text-xs text-[#706f6c] dark:text-[#A1A09A]">
                <svg class="w-4 h-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                App installation is not available on this browser. Try using Chrome, Edge, or Safari.
            </div>
        </div>
    </div>
</div>

