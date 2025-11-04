<!-- Fees Settings -->
<div id="content-fees" class="settings-content hidden bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
    <!-- Header with Live Updates Status -->
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Fee Management</h3>
        <div class="flex items-center gap-2">
            <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Live Updates:</span>
            <div id="feesConnectionStatus" class="w-3 h-3 rounded-full bg-red-500"></div>
        </div>
    </div>
    
    <!-- Fees Table -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-[#161615] border-y border-[#e3e3e0] dark:border-[#3E3E3A]">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Fee Name</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Description</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Amount (₱)</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody id="fees-table-body" class="divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                @forelse($fees as $fee)
                <tr class="hover:bg-gray-50 dark:hover:bg-[#2a2a2a] transition-colors" data-fee-id="{{ $fee->id }}">
                    <td class="px-4 py-3">
                        <span class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">{{ $fee->display_name }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">{{ $fee->description }}</span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <span class="text-sm font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">{{ number_format($fee->amount, 2) }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-2">
                            <button onclick="editFee({{ $fee->id }})" class="btn-edit" title="Edit Fee">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">
                        No fees found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

