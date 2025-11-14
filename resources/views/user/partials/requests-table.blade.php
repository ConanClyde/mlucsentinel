<div class="overflow-x-auto">
    <table class="w-full">
        <thead>
            <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
                <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Request</th>
                <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Vehicle</th>
                <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Reason</th>
                <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Status</th>
                <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Date</th>
                <th class="text-center py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $request)
            <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A] hover:bg-gray-50 dark:hover:bg-[#161615]" data-id="{{ $request->id }}">
                <td class="py-2 px-3">
                    <div class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">#{{ $request->id }}</div>
                </td>
                <td class="py-2 px-3">
                    <div class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">{{ $request->vehicle->vehicleType->name }}</div>
                    <div class="text-xs text-[#706f6c] dark:text-[#A1A09A]">{{ $request->vehicle->plate_no ?? 'No Plate' }}</div>
                </td>
                <td class="py-2 px-3">
                    <div class="text-sm text-[#1b1b18] dark:text-[#EDEDEC] max-w-xs truncate">{{ $request->reason }}</div>
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
                        <button onclick="openCancelModal({{ $request->id }})" class="btn-delete" title="Cancel">
                            <x-heroicon-s-trash class="w-4 h-4" />
                        </button>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="py-8 px-4 text-center text-[#706f6c] dark:text-[#A1A09A]">
                    No requests found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination Controls -->
@if($requests->hasPages())
<div class="flex items-center justify-between mt-6">
    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
        Showing {{ $requests->firstItem() ?? 0 }}-{{ $requests->lastItem() ?? 0 }} of {{ $requests->total() }} requests
    </p>
    <div class="flex space-x-2">
        @if($requests->onFirstPage())
        <button class="btn-pagination btn-paginationDisable" disabled>
            <x-heroicon-o-chevron-left class="w-4 h-4" />
        </button>
        @else
        <button onclick="loadPage({{ $requests->currentPage() - 1 }})" class="btn-pagination btn-paginationArrow">
            <x-heroicon-o-chevron-left class="w-4 h-4" />
        </button>
        @endif
        
        @foreach($requests->getUrlRange(1, $requests->lastPage()) as $page => $url)
            @if($page == $requests->currentPage())
            <button class="btn-pagination btn-paginationActive">{{ $page }}</button>
            @else
            <button onclick="loadPage({{ $page }})" class="btn-pagination">{{ $page }}</button>
            @endif
        @endforeach
        
        @if($requests->hasMorePages())
        <button onclick="loadPage({{ $requests->currentPage() + 1 }})" class="btn-pagination btn-paginationArrow">
            <x-heroicon-o-chevron-right class="w-4 h-4" />
        </button>
        @else
        <button class="btn-pagination btn-paginationDisable" disabled>
            <x-heroicon-o-chevron-right class="w-4 h-4" />
        </button>
        @endif
    </div>
</div>
@endif
