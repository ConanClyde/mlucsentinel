    <div id="paymentContent" class="tab-content hidden">
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4 md:mb-6">
                <h3 class="text-base md:text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Pending Payments</h3>
                <div class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-3 md:gap-4">
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 flex-1 sm:flex-initial">
                        <input type="text" id="paymentSearch" class="form-input !h-[38px] !py-1 !px-3 text-xs md:text-sm w-full sm:!w-64" placeholder="Search by user, reference, or vehicle..." autocomplete="off">
                        <button onclick="clearPaymentSearch()" class="btn btn-secondary !h-[38px] !py-1 !px-3 text-xs md:text-sm w-full sm:w-auto" title="Clear search">
                            Clear
                        </button>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs md:text-sm text-[#706f6c] dark:text-[#A1A09A]">Show:</span>
                        <select id="paymentPaginationLimit" class="form-input !h-[38px] !py-1 !px-3 text-xs md:text-sm">
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs md:text-sm text-[#706f6c] dark:text-[#A1A09A]">Live Updates:</span>
                        <div id="paymentConnectionStatus" class="w-3 h-3 rounded-full bg-red-500"></div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
                            <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Reference</th>
                            <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">User</th>
                            <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Vehicles</th>
                            <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Amount</th>
                            <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Request Date</th>
                            <th class="text-center py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="paymentTableBody">
                        @forelse($payments as $payment)
                        <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A] hover:bg-gray-50 dark:hover:bg-[#161615]" data-payment-id="{{ $payment->id }}">
                            <td class="py-2 px-3">
                                <span class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">{{ $payment->reference ?? 'N/A' }}</span>
                            </td>
                            <td class="py-2 px-3">
                                <div>
                                    <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">{{ $payment->user->first_name ?? '' }} {{ $payment->user->last_name ?? '' }}</p>
                                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">{{ $payment->user->user_type ?? 'N/A' }}</p>
                                </div>
                            </td>
                            <td class="py-2 px-3">
                                @if($payment->vehicle_count > 1)
                                    <p class="text-sm font-medium text-blue-600 dark:text-blue-400">{{ $payment->vehicle_count }} Vehicles</p>
                                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Click view to see all</p>
                                @else
                                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">{{ $payment->vehicle->type->name ?? 'N/A' }}</p>
                                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">{{ $payment->vehicle->plate_no ?? ($payment->vehicle ? $payment->vehicle->color . '-' . $payment->vehicle->number : '') }}</p>
                                @endif
                            </td>
                            <td class="py-2 px-3">
                                <span class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">₱{{ number_format($payment->amount, 2) }}</span>
                            </td>
                            <td class="py-2 px-3">
                                <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">{{ $payment->created_at->format('M d, Y') }}</span>
                            </td>
                            <td class="py-2 px-3">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick="showReceipt({{ $payment->id }})" class="btn-view" title="View Receipt">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                    <button onclick="deletePaymentRequest({{ $payment->id }})" class="btn-delete" title="Delete Request">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-8 px-4 text-center text-[#706f6c] dark:text-[#A1A09A]">
                                No pending payments
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Controls -->
            <div id="paymentPaginationControls" class="flex flex-col sm:flex-row items-center justify-between gap-3 mt-4 md:mt-6">
                <p class="text-xs md:text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    Showing <span id="paymentShowingStart">1</span>-<span id="paymentShowingEnd">10</span> of <span id="paymentTotalCount">0</span> payments
                </p>
                <div class="flex space-x-2">
                    <button id="paymentPrevPage" class="btn-pagination btn-paginationDisable" onclick="changePaymentPage(-1)">
                        <x-heroicon-o-chevron-left class="w-4 h-4" />
                    </button>
                    <div id="paymentPageNumbers" class="flex space-x-2"></div>
                    <button id="paymentNextPage" class="btn-pagination btn-paginationArrow" onclick="changePaymentPage(1)">
                        <x-heroicon-o-chevron-right class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </div>
    </div>
