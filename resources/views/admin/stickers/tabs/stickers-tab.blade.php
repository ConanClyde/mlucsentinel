    <div id="stickersContent" class="tab-content">
        <!-- Header with Count and Download Button -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 mb-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
                    Issued Stickers (<span id="stickersCount">0</span>)
                </h3>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Show:</span>
                        <select id="stickersPaginationLimit" class="form-input !h-[38px] !py-1 !px-3 text-sm">
                            <option value="24" selected>24</option>
                            <option value="48">48</option>
                            <option value="72">72</option>
                            <option value="96">96</option>
                        </select>
                    </div>
                    <button onclick="downloadAllStickers()" class="btn btn-primary">
                        Download
                    </button>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 mb-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="form-label">From Date</label>
                    <input type="date" id="stickersFromDate" class="form-input">
                </div>
                <div>
                    <label class="form-label">To Date</label>
                    <input type="date" id="stickersToDate" class="form-input">
                </div>
                <div>
                    <label class="form-label">Search</label>
                    <input type="text" id="stickersSearch" class="form-input" placeholder="Owner name or plate...">
                </div>
                <div class="flex items-end">
                    <button onclick="resetStickersFilters()" class="btn btn-secondary w-full">Reset</button>
                </div>
            </div>
        </div>

        <!-- Stickers Grid -->
        <div id="stickersGrid" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-4">
            <!-- Cards will be populated by JavaScript -->
        </div>

        <!-- Empty State -->
        <div id="stickersEmptyState" class="hidden text-center py-12">
            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                <x-heroicon-o-rectangle-stack class="w-8 h-8 text-gray-400" />
            </div>
            <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">No stickers issued yet</h3>
            <p class="text-[#706f6c] dark:text-[#A1A09A]">Stickers will appear here once payments are confirmed.</p>
        </div>

        <!-- Pagination Controls -->
        <div id="stickersPaginationControls" class="flex items-center justify-between mt-6">
            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                Showing <span id="stickersShowingStart">1</span>-<span id="stickersShowingEnd">0</span> of <span id="stickersTotalCount">0</span> stickers
            </p>
            <div class="flex space-x-2">
                <button id="stickersPrevPage" class="btn-pagination btn-paginationDisable" onclick="changeStickersPage(-1)">
                    <x-heroicon-o-chevron-left class="w-4 h-4" />
                </button>
                <div id="stickersPageNumbers" class="flex space-x-2"></div>
                <button id="stickersNextPage" class="btn-pagination btn-paginationArrow" onclick="changeStickersPage(1)">
                    <x-heroicon-o-chevron-right class="w-4 h-4" />
                </button>
            </div>
        </div>
    </div>
