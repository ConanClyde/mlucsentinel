<!-- Notification Settings -->
<div id="content-notifications" class="settings-content hidden bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
    <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Notification Settings</h3>
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Browser Notifications</p>
                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Show notifications in browser</p>
                <p id="notification-status" class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-1"></p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" id="browser-notifications-toggle" class="sr-only peer" onchange="toggleBrowserNotifications(this)">
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
            </label>
        </div>
    </div>
</div>

