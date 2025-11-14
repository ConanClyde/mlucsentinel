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

        <!-- Filter Card -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6 mt-6">
            <div class="flex flex-col lg:flex-row gap-3 md:gap-4">
                <!-- Search -->
                <div class="flex-1">
                    <label class="form-label">Search</label>
                    <input type="text" id="requestSearch" class="form-input w-full" placeholder="Search by name, email, or plate number...">
                </div>

                <!-- Status Filter -->
                <div class="flex-1">
                    <label class="form-label">Status</label>
                    <select id="statusFilter" class="form-input w-full">
                        <option value="">All Status</option>
                        <option value="pending" selected>Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>

                <!-- Reset Button -->
                <div class="flex-shrink-0">
                    <label class="form-label opacity-0 hidden sm:block">Reset</label>
                    <button onclick="refreshRequests()" class="btn btn-secondary !h-[38px] w-full lg:w-auto px-6">Reset</button>
                </div>
            </div>
        </div>

        <!-- User Requests Table -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6 mt-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4 md:mb-6">
                <div class="flex items-center gap-3">
                    <h3 class="text-base md:text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">User Sticker Requests</h3>
                </div>
                <div class="flex flex-wrap items-center gap-3 md:gap-4">
                    <div class="flex items-center gap-2">
                        <span class="text-xs md:text-sm text-[#706f6c] dark:text-[#A1A09A]">Show:</span>
                        <select id="pagination-limit" class="form-input !h-[38px] !py-1 !px-3 text-xs md:text-sm">
                            <option value="10">10</option>
                            <option value="20" selected>20</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>
            </div>

            <div id="requestsTableContainer" class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
                            <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">User</th>
                            <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Vehicle</th>
                            <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Status</th>
                            <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Date</th>
                            <th class="text-center py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="requestsTableBody">
                        @forelse($stickerRequests as $request)
                        <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A] hover:bg-gray-50 dark:hover:bg-[#161615]" data-id="{{ $request->id }}">
                            <td class="py-2 px-3">
                                <div class="flex items-center">
                                    @php
                                        $colors = ['#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#6366F1', '#8B5CF6', '#EC4899', '#14B8A6', '#F97316', '#06B6D4'];
                                        $firstLetter = strtoupper(substr($request->user->first_name ?? 'U', 0, 1));
                                        $hash = ord($firstLetter);
                                        $avatarColor = $colors[$hash % count($colors)];
                                    @endphp
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3 text-white font-semibold text-xs flex-shrink-0" style="background-color: {{ $avatarColor }}">
                                        {{ strtoupper(substr($request->user->first_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">{{ $request->user->first_name }} {{ $request->user->last_name }}</div>
                                        <div class="text-xs text-[#706f6c] dark:text-[#A1A09A]">{{ $request->user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-2 px-3">
                                <div class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">{{ $request->vehicle->vehicleType->name }}</div>
                                <div class="text-xs text-[#706f6c] dark:text-[#A1A09A]">{{ $request->vehicle->plate_no ?? 'No Plate' }}</div>
                            </td>
                            <td class="py-2 px-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($request->status === 'pending') bg-amber-100 dark:bg-amber-900 text-amber-800 dark:text-amber-200
                                    @elseif($request->status === 'approved') bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200
                                    @else bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200
                                    @endif">
                                    {{ ucfirst($request->status) }}
                                </span>
                            </td>
                            <td class="py-2 px-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">{{ $request->created_at->format('M d, Y') }}</td>
                            <td class="py-2 px-3">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick="viewRequestDetails({{ $request->id }})" class="btn-view" title="View">
                                        <x-heroicon-s-eye class="w-4 h-4" />
                                    </button>
                                    @if($request->status === 'pending')
                                    <button onclick="approveRequest({{ $request->id }})" class="inline-flex items-center justify-center w-8 h-8 rounded-sm bg-green-600 dark:bg-green-600 text-white hover:bg-green-700 dark:hover:bg-green-700 border border-green-600 dark:border-green-600 hover:border-green-700 dark:hover:border-green-700 transition-all" title="Approve">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                    <button onclick="rejectRequest({{ $request->id }})" class="btn-delete" title="Reject">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-8 px-4 text-center text-[#706f6c] dark:text-[#A1A09A]">
                                No requests found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Controls -->
            <div id="paginationContainer" class="flex items-center justify-between mt-6">
                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    Showing <span id="showing-start">1</span>-<span id="showing-end">{{ $stickerRequests->count() }}</span> of <span id="total-count">{{ $stickerRequests->total() }}</span> requests
                </p>
                @if($stickerRequests->hasPages())
                <div class="flex space-x-2">
                    @if($stickerRequests->onFirstPage())
                    <button class="btn-pagination btn-paginationDisable" disabled>
                        <x-heroicon-o-chevron-left class="w-4 h-4" />
                    </button>
                    @else
                    <a href="{{ $stickerRequests->previousPageUrl() }}" class="btn-pagination btn-paginationArrow">
                        <x-heroicon-o-chevron-left class="w-4 h-4" />
                    </a>
                    @endif
                    
                    @foreach($stickerRequests->getUrlRange(1, $stickerRequests->lastPage()) as $page => $url)
                        @if($page == $stickerRequests->currentPage())
                        <button class="btn-pagination btn-paginationActive">{{ $page }}</button>
                        @else
                        <a href="{{ $url }}" class="btn-pagination">{{ $page }}</a>
                        @endif
                    @endforeach
                    
                    @if($stickerRequests->hasMorePages())
                    <a href="{{ $stickerRequests->nextPageUrl() }}" class="btn-pagination btn-paginationArrow">
                        <x-heroicon-o-chevron-right class="w-4 h-4" />
                    </a>
                    @else
                    <button class="btn-pagination btn-paginationDisable" disabled>
                        <x-heroicon-o-chevron-right class="w-4 h-4" />
                    </button>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
