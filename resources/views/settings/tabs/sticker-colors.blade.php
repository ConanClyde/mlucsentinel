<!-- Sticker Colors Settings -->
<div id="content-sticker-colors" class="settings-content hidden bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
    <!-- Header with Add Button -->
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Sticker Color Palette</h3>
        <button onclick="openAddColorModal()" class="btn btn-primary text-sm">
            Add
            </button>
        </div>

    <!-- Colors Table -->
        <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-[#161615] border-y border-[#e3e3e0] dark:border-[#3E3E3A]">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Color Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Preview</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Hex Code</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
            <tbody id="palette-table-body" class="divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                    <!-- Populated by JS -->
                </tbody>
            </table>
    </div>
</div>
