    <div id="requestContent" class="tab-content hidden">
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
            <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-6">Request New Sticker</h3>
            
            <form id="requestForm" class="space-y-6">
            <div>
                    <label class="form-label">Search User <span class="text-red-500">*</span></label>
                    <input type="text" id="userSearch" class="form-input" placeholder="Search by name or email..." autocomplete="off">
                    <div id="userSearchResults" class="mt-2 hidden bg-white dark:bg-[#1a1a1a] border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-lg shadow-lg max-h-64 overflow-y-auto"></div>
                </div>

                <div id="selectedUserInfo" class="hidden">
                    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                        <h4 class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Selected User</h4>
                        <p id="selectedUserName" class="text-sm text-[#706f6c] dark:text-[#A1A09A]"></p>
                    </div>
                </div>

                <div id="vehicleSelection" class="hidden">
                    <label class="form-label">Select Vehicle(s) <span class="text-red-500">*</span></label>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mb-4">Click on vehicles to select. You can select multiple vehicles.</p>
                    <div id="vehicleCards" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"></div>
                </div>

                <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                    <div class="flex items-start">
                        <x-heroicon-o-information-circle class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-3 mt-0.5 flex-shrink-0" />
                        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                            <strong>Note:</strong> Sticker fee is ₱{{ number_format(\App\Models\Fee::getAmount('sticker_fee', 15.00), 2) }} per request. Payment record will be created automatically.
                        </p>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" onclick="resetRequestForm()" class="btn btn-secondary">Reset</button>
                    <button type="submit" class="btn btn-primary">Create Request</button>
                </div>
            </form>
        </div>
    </div>
