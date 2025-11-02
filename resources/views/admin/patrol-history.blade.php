@extends('layouts.app')

@section('page-title', 'Patrol Monitor')

@section('content')
<div class="space-y-6">
    <!-- Filter Card -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
        <div class="flex flex-col md:flex-row gap-4 items-end">
            <!-- Search -->
            <div class="flex-1 md:flex-[2]">
                <label class="form-label">Search</label>
                <input type="text" id="search-input" class="form-input w-full" placeholder="Search by guard name, email, location...">
            </div>

            <!-- Location Filter -->
            <div class="flex-1">
                <label class="form-label">Location</label>
                <select id="location-filter" class="form-input w-full">
                    <option value="">All Locations</option>
                    @foreach($locations as $location)
                        <option value="{{ $location->id }}">{{ $location->short_code }} - {{ $location->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Start Date Filter -->
            <div class="flex-1">
                <label class="form-label">Start Date</label>
                <input type="date" id="start-date-filter" class="form-input w-full">
            </div>

            <!-- End Date Filter -->
            <div class="flex-1">
                <label class="form-label">End Date</label>
                <input type="date" id="end-date-filter" class="form-input w-full">
            </div>

            <!-- Reset Button -->
            <div class="flex-shrink-0">
                <button id="reset-filters" class="btn btn-secondary !h-[38px] px-6">Reset</button>
            </div>
        </div>
    </div>

    <!-- Patrol Logs Table -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Patrol History</h3>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Show:</span>
                    <select id="pagination-limit" class="form-input !h-[38px] !py-1 !px-3 text-sm">
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <button onclick="exportToCSV()" class="btn btn-csv">CSV</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
                        <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Date & Time</th>
                        <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Guard</th>
                        <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Location</th>
                        <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Code</th>
                        <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Notes</th>
                        <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">GPS</th>
                    </tr>
                </thead>
                <tbody id="patrolLogsTableBody">
                    @forelse($logs as $log)
                        <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A] hover:bg-gray-50 dark:hover:bg-[#161615]" 
                            data-id="{{ $log->id }}" 
                            data-guard-id="{{ $log->security_user_id }}" 
                            data-location-id="{{ $log->map_location_id }}" 
                            data-date="{{ $log->checked_in_at->format('Y-m-d') }}">
                            <td class="py-2 px-3">
                                <div class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                                    {{ $log->checked_in_at->format('M d, Y') }}
                                </div>
                                <div class="text-xs text-[#706f6c] dark:text-[#A1A09A]">
                                    {{ $log->checked_in_at->format('h:i A') }}
                                </div>
                            </td>
                            <td class="py-2 px-3">
                                <div class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                                    {{ $log->securityUser->name ?? 'N/A' }}
                                </div>
                                <div class="text-xs text-[#706f6c] dark:text-[#A1A09A]">
                                    {{ $log->securityUser->email ?? 'N/A' }}
                                </div>
                            </td>
                            <td class="py-2 px-3">
                                <div class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                                    {{ $log->mapLocation->name }}
                                </div>
                                <div class="text-xs text-[#706f6c] dark:text-[#A1A09A]">
                                    {{ $log->mapLocation->type->name ?? 'N/A' }}
                                </div>
                            </td>
                            <td class="py-2 px-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                    {{ $log->mapLocation->short_code }}
                                </span>
                            </td>
                            <td class="py-2 px-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">{{ $log->notes ?? '-' }}</td>
                            <td class="py-2 px-3">
                                @if($log->latitude && $log->longitude)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                                        Yes
                                    </span>
                                @else
                                    <span class="text-[#706f6c] dark:text-[#A1A09A]">No</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 px-4 text-center text-[#706f6c] dark:text-[#A1A09A]">
                                No patrol logs found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <div id="pagination-controls" class="flex items-center justify-between mt-6">
            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                Showing <span id="showing-start">1</span>-<span id="showing-end">10</span> of <span id="total-count">0</span> logs
            </p>
            <div class="flex space-x-2">
                <button id="prev-page" class="btn-pagination btn-paginationDisable" onclick="changePage(-1)">
                    <x-heroicon-o-chevron-left class="w-4 h-4" />
                </button>
                <div id="page-numbers" class="flex space-x-2"></div>
                <button id="next-page" class="btn-pagination btn-paginationArrow" onclick="changePage(1)">
                    <x-heroicon-o-chevron-right class="w-4 h-4" />
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let patrolLogs = @json($logs->items());

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const locationFilter = document.getElementById('location-filter');
    const startDateFilter = document.getElementById('start-date-filter');
    const endDateFilter = document.getElementById('end-date-filter');
    const resetButton = document.getElementById('reset-filters');
    const paginationLimit = document.getElementById('pagination-limit');

    let currentPage = 1;
    let itemsPerPage = 10;

    // Add event listeners for automatic filtering
    searchInput.addEventListener('input', function() {
        currentPage = 1;
        applyPagination();
    });

    locationFilter.addEventListener('change', function() {
        currentPage = 1;
        applyPagination();
    });

    startDateFilter.addEventListener('change', function() {
        currentPage = 1;
        applyPagination();
    });

    endDateFilter.addEventListener('change', function() {
        currentPage = 1;
        applyPagination();
    });

    resetButton.addEventListener('click', function() {
        searchInput.value = '';
        locationFilter.value = '';
        startDateFilter.value = '';
        endDateFilter.value = '';
        currentPage = 1;
        applyPagination();
    });

    paginationLimit.addEventListener('change', function() {
        itemsPerPage = parseInt(this.value);
        currentPage = 1;
        applyPagination();
    });

    function applyPagination() {
        const rows = document.querySelectorAll('#patrolLogsTableBody tr');
        let visibleCount = 0;
        let totalFiltered = 0;

        const searchTerm = searchInput.value.toLowerCase();
        const locationValue = locationFilter.value;
        const startDate = startDateFilter.value;
        const endDate = endDateFilter.value;

        // First pass: count total filtered rows
        rows.forEach((row) => {
            // Skip empty state row
            if (row.querySelector('td[colspan]')) {
                return;
            }

            const guardName = row.querySelector('td:nth-child(2) .text-sm.font-medium')?.textContent.toLowerCase() || '';
            const guardEmail = row.querySelector('td:nth-child(2) .text-xs')?.textContent.toLowerCase() || '';
            const locationName = row.querySelector('td:nth-child(3) .text-sm.font-medium')?.textContent.toLowerCase() || '';
            const locationId = row.getAttribute('data-location-id');
            const logDate = row.getAttribute('data-date');

            const matchesSearch = searchTerm === '' || 
                                guardName.includes(searchTerm) || 
                                guardEmail.includes(searchTerm) || 
                                locationName.includes(searchTerm);
            const matchesLocation = locationValue === '' || locationId === locationValue;
            const matchesStartDate = startDate === '' || logDate >= startDate;
            const matchesEndDate = endDate === '' || logDate <= endDate;

            if (matchesSearch && matchesLocation && matchesStartDate && matchesEndDate) {
                totalFiltered++;
            }
        });

        // Second pass: apply pagination
        rows.forEach((row) => {
            // Skip empty state row
            if (row.querySelector('td[colspan]')) {
                row.style.display = totalFiltered === 0 ? '' : 'none';
                return;
            }

            const guardName = row.querySelector('td:nth-child(2) .text-sm.font-medium')?.textContent.toLowerCase() || '';
            const guardEmail = row.querySelector('td:nth-child(2) .text-xs')?.textContent.toLowerCase() || '';
            const locationName = row.querySelector('td:nth-child(3) .text-sm.font-medium')?.textContent.toLowerCase() || '';
            const locationId = row.getAttribute('data-location-id');
            const logDate = row.getAttribute('data-date');

            const matchesSearch = searchTerm === '' || 
                                guardName.includes(searchTerm) || 
                                guardEmail.includes(searchTerm) || 
                                locationName.includes(searchTerm);
            const matchesLocation = locationValue === '' || locationId === locationValue;
            const matchesStartDate = startDate === '' || logDate >= startDate;
            const matchesEndDate = endDate === '' || logDate <= endDate;

            if (!matchesSearch || !matchesLocation || !matchesStartDate || !matchesEndDate) {
                row.style.display = 'none';
                return;
            }

            visibleCount++;
            const startIndex = (currentPage - 1) * itemsPerPage + 1;
            const endIndex = currentPage * itemsPerPage;

            if (visibleCount >= startIndex && visibleCount <= endIndex) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        updatePaginationControls(totalFiltered);
    }

    function updatePaginationControls(totalFiltered) {
        const totalPages = Math.ceil(totalFiltered / itemsPerPage);
        const start = totalFiltered === 0 ? 0 : (currentPage - 1) * itemsPerPage + 1;
        const end = Math.min(currentPage * itemsPerPage, totalFiltered);

        // Update showing text
        document.getElementById('showing-start').textContent = start;
        document.getElementById('showing-end').textContent = end;
        document.getElementById('total-count').textContent = totalFiltered;

        // Update prev/next buttons
        const prevBtn = document.getElementById('prev-page');
        const nextBtn = document.getElementById('next-page');

        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage === totalPages || totalPages === 0;

        prevBtn.className = currentPage === 1 ? 'btn-pagination btn-paginationDisable' : 'btn-pagination btn-paginationArrow';
        nextBtn.className = (currentPage === totalPages || totalPages === 0) ? 'btn-pagination btn-paginationDisable' : 'btn-pagination btn-paginationArrow';

        // Generate page numbers (show only 3 pages at a time)
        const pageNumbers = document.getElementById('page-numbers');
        pageNumbers.innerHTML = '';

        // Calculate which 3 pages to show
        let startPage = Math.max(1, currentPage - 1);
        let endPage = Math.min(totalPages, startPage + 2);

        // Adjust if we're near the end
        if (endPage - startPage < 2) {
            startPage = Math.max(1, endPage - 2);
        }

        for (let i = startPage; i <= endPage; i++) {
            const btn = document.createElement('button');
            btn.textContent = i;
            btn.className = i === currentPage ? 'btn-pagination btn-paginationActive' : 'btn-pagination btn-paginationNumber';
            btn.onclick = () => goToPage(i);
            pageNumbers.appendChild(btn);
        }
    }

    // Apply initial pagination on page load
    applyPagination();

    // Pagination navigation functions (global scope)
    window.changePage = function(direction) {
        const rows = document.querySelectorAll('#patrolLogsTableBody tr');
        let totalFiltered = 0;

        const searchTerm = searchInput.value.toLowerCase();
        const locationValue = locationFilter.value;
        const startDate = startDateFilter.value;
        const endDate = endDateFilter.value;

        rows.forEach((row) => {
            // Skip empty state row
            if (row.querySelector('td[colspan]')) {
                return;
            }

            const guardName = row.querySelector('td:nth-child(2) .text-sm.font-medium')?.textContent.toLowerCase() || '';
            const guardEmail = row.querySelector('td:nth-child(2) .text-xs')?.textContent.toLowerCase() || '';
            const locationName = row.querySelector('td:nth-child(3) .text-sm.font-medium')?.textContent.toLowerCase() || '';
            const locationId = row.getAttribute('data-location-id');
            const logDate = row.getAttribute('data-date');

            const matchesSearch = searchTerm === '' || 
                                guardName.includes(searchTerm) || 
                                guardEmail.includes(searchTerm) || 
                                locationName.includes(searchTerm);
            const matchesLocation = locationValue === '' || locationId === locationValue;
            const matchesStartDate = startDate === '' || logDate >= startDate;
            const matchesEndDate = endDate === '' || logDate <= endDate;

            if (matchesSearch && matchesLocation && matchesStartDate && matchesEndDate) {
                totalFiltered++;
            }
        });

        const totalPages = Math.ceil(totalFiltered / itemsPerPage);
        currentPage = Math.max(1, Math.min(currentPage + direction, totalPages));
        applyPagination();
    };

    window.goToPage = function(page) {
        currentPage = page;
        applyPagination();
    };
});

function exportToCSV() {
    const searchInput = document.getElementById('search-input');
    const locationFilter = document.getElementById('location-filter');
    const startDateFilter = document.getElementById('start-date-filter');
    const endDateFilter = document.getElementById('end-date-filter');

    const searchTerm = searchInput.value.toLowerCase();
    const locationValue = locationFilter.value;
    const startDate = startDateFilter.value;
    const endDate = endDateFilter.value;

    // Filter visible logs based on current filters
    const visibleLogs = patrolLogs.filter(log => {
        const logDate = new Date(log.checked_in_at).toISOString().split('T')[0];
        const guardName = (log.security_user?.name || '').toLowerCase();
        const guardEmail = (log.security_user?.email || '').toLowerCase();
        const locationName = (log.map_location?.name || '').toLowerCase();
        
        const matchesSearch = searchTerm === '' || 
                            guardName.includes(searchTerm) || 
                            guardEmail.includes(searchTerm) || 
                            locationName.includes(searchTerm);
        const matchesLocation = locationValue === '' || log.map_location_id == locationValue;
        const matchesStartDate = startDate === '' || logDate >= startDate;
        const matchesEndDate = endDate === '' || logDate <= endDate;

        return matchesSearch && matchesLocation && matchesStartDate && matchesEndDate;
    });

    if (visibleLogs.length === 0) {
        alert('No logs to export');
        return;
    }

    const headers = ['Check-in Time', 'Guard Name', 'Guard Email', 'Location Name', 'Location Code', 'Notes', 'GPS'];
    const rows = visibleLogs.map(log => {
        const checkinTime = new Date(log.checked_in_at).toLocaleString('en-US', {
            year: 'numeric', month: 'short', day: 'numeric',
            hour: '2-digit', minute: '2-digit'
        });
        const guardName = log.security_user?.name || 'N/A';
        const guardEmail = log.security_user?.email || 'N/A';
        const locationName = log.map_location?.name || 'N/A';
        const locationCode = log.map_location?.short_code || 'N/A';
        const notes = log.notes || '';
        const gps = (log.latitude && log.longitude) ? 'Yes' : 'No';

        return [checkinTime, guardName, guardEmail, locationName, locationCode, notes, gps].map(field => {
            const escaped = String(field).replace(/"/g, '""');
            return `"${escaped}"`;
        }).join(',');
    });

    const csv = [headers.join(','), ...rows].join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);

    link.setAttribute('href', url);
    link.setAttribute('download', `patrol_logs_${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';

    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
@endpush
