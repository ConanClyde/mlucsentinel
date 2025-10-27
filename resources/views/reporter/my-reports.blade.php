@extends('layouts.app')

@section('page-title', 'My Reports')

@section('content')
<div class="space-y-6">
    <!-- Filter Card -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
        <div class="flex flex-col md:flex-row gap-4 items-end">
            <!-- Search -->
            <div class="flex-1 md:flex-[2]">
                <label class="form-label">Search</label>
                <input type="text" id="search-input" class="form-input w-full" placeholder="Search by violator, vehicle, or location...">
            </div>

            <!-- Status Filter -->
            <div class="flex-1">
                <label class="form-label">Status</label>
                <select id="status-filter" class="form-input w-full">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>

            <!-- Violation Type Filter -->
            <div class="flex-1">
                <label class="form-label">Violation Type</label>
                <select id="violation-filter" class="form-input w-full">
                    <option value="">All Types</option>
                    @php
                        $violationTypes = \App\Models\ViolationType::orderBy('name')->get();
                    @endphp
                    @foreach($violationTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Reset Button -->
            <div class="flex-shrink-0">
                <button id="reset-filters" class="btn btn-secondary !h-[38px] px-6">Reset</button>
            </div>
        </div>
    </div>

    <!-- Reports Table -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">My Reports</h3>
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

        @if($reports->isEmpty())
                <div class="text-center py-12">
                    <x-heroicon-o-document-text class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-600 mb-4" />
                    <p class="text-[#706f6c] dark:text-[#A1A09A] mb-4">No reports submitted yet</p>
                    <a href="{{ route('reporter.report-user') }}" class="btn btn-primary">Submit a Report</a>
                </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
                            <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Violation Type</th>
                            <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Violator</th>
                            <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Vehicle</th>
                            <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Location</th>
                            <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Date</th>
                            <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Status</th>
                            <th class="text-center py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reports as $report)
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200',
                                        'under_review' => 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200',
                                        'approved' => 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200',
                                        'rejected' => 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200',
                                        'resolved' => 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200',
                                        'dismissed' => 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200',
                                    ];
                                    $statusClass = $statusColors[$report->status] ?? 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200';
                                    
                                    $violatorName = $report->violatorVehicle && $report->violatorVehicle->user 
                                        ? $report->violatorVehicle->user->first_name . ' ' . $report->violatorVehicle->user->last_name
                                        : 'Unknown';
                                    
                                    $vehicleInfo = $report->violatorVehicle 
                                        ? ($report->violatorVehicle->plate_no ?: $report->violator_sticker_number)
                                        : 'N/A';
                            @endphp
                            
                            <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A] hover:bg-gray-50 dark:hover:bg-[#2a2a2a] transition-colors">
                                <td class="py-2 px-3 text-sm text-[#1b1b18] dark:text-[#EDEDEC]">
                                    {{ $report->violationType->name ?? 'N/A' }}
                                </td>
                                <td class="py-2 px-3 text-sm text-[#1b1b18] dark:text-[#EDEDEC]">
                                    {{ $violatorName }}
                                </td>
                                <td class="py-2 px-3 text-sm text-[#1b1b18] dark:text-[#EDEDEC]">
                                    {{ $vehicleInfo }}
                                </td>
                                <td class="py-2 px-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                    {{ $report->location }}
                                </td>
                                <td class="py-2 px-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                    {{ $report->reported_at->format('M d, Y') }}
                                </td>
                                <td class="py-2 px-3">
                                    <span class="px-2.5 py-1 text-xs font-medium {{ $statusClass }} rounded-full whitespace-nowrap">
                                        {{ ucfirst(str_replace('_', ' ', $report->status)) }}
                                    </span>
                                </td>
                                <td class="py-2 px-3">
                                    <div class="flex items-center justify-center gap-2">
                                        <button onclick="viewReport({{ $report->id }})" class="btn-view" title="View">
                                            <x-heroicon-s-eye class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination Controls -->
            <div id="pagination-controls" class="flex items-center justify-between mt-6">
                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    Showing <span id="showing-start">1</span>-<span id="showing-end">10</span> of <span id="total-count">0</span> reports
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
        @endif
    </div>
</div>

<!-- View Report Modal -->
<div id="viewModal" class="modal-backdrop hidden" onclick="if(event.target === this) closeViewModal()">
    <div class="modal-container-wide">
        <div class="modal-header flex justify-between items-center">
            <h2 class="modal-title">Report Details</h2>
            <button onclick="closeViewModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <x-heroicon-o-x-mark class="w-6 h-6" />
            </button>
        </div>
        <div class="modal-body">
            <div class="grid grid-cols-2 gap-6">
                <!-- Left Column -->
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Violation Type</p>
                        <p class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]" id="modal-violation-type"></p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Violator</p>
                        <p class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]" id="modal-violator"></p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Vehicle</p>
                        <p class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]" id="modal-vehicle"></p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Location</p>
                        <p class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]" id="modal-location"></p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Description</p>
                        <p class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]" id="modal-description"></p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Reported At</p>
                        <p class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]" id="modal-reported-at"></p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Status</p>
                        <span id="modal-status-badge"></span>
                    </div>
                </div>
                
                <!-- Right Column - Evidence Image -->
                <div id="modal-evidence-container">
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mb-2">Evidence Image</p>
                    <img id="modal-evidence" src="" alt="Evidence" class="w-full max-h-96 object-contain rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A]">
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button onclick="closeViewModal()" class="btn btn-secondary">Close</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const reports = @json($reports);

function viewReport(reportId) {
    const report = reports.find(r => r.id === reportId);
    if (!report) return;
    
    // Status colors
    const statusColors = {
        'pending': 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200',
        'approved': 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200',
        'rejected': 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200',
    };
    
    const statusClass = statusColors[report.status] || 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200';
    
    // Populate modal
    document.getElementById('modal-violation-type').textContent = report.violation_type?.name || 'N/A';
    document.getElementById('modal-status-badge').innerHTML = `<span class="px-2.5 py-1 text-xs font-medium ${statusClass} rounded-full">${report.status.charAt(0).toUpperCase() + report.status.slice(1)}</span>`;
    
    const violatorName = report.violator_vehicle?.user 
        ? `${report.violator_vehicle.user.first_name} ${report.violator_vehicle.user.last_name}`
        : 'Unknown';
    document.getElementById('modal-violator').textContent = violatorName;
    
    const vehicleInfo = report.violator_vehicle 
        ? (report.violator_vehicle.plate_no || report.violator_sticker_number)
        : 'N/A';
    document.getElementById('modal-vehicle').textContent = vehicleInfo;
    
    document.getElementById('modal-location').textContent = report.location;
    document.getElementById('modal-description').textContent = report.description;
    document.getElementById('modal-reported-at').textContent = new Date(report.reported_at).toLocaleString();
    
    // Evidence image
    if (report.evidence_image) {
        document.getElementById('modal-evidence').src = `/storage/${report.evidence_image}`;
        document.getElementById('modal-evidence-container').classList.remove('hidden');
    } else {
        document.getElementById('modal-evidence-container').classList.add('hidden');
    }
    
    // Show modal
    document.getElementById('viewModal').classList.remove('hidden');
}

function closeViewModal() {
    document.getElementById('viewModal').classList.add('hidden');
}

// Expose to global scope
window.viewReport = viewReport;
window.closeViewModal = closeViewModal;

// Pagination functionality
const paginationLimit = document.getElementById('pagination-limit');
let currentPage = 1;
let itemsPerPage = 10;

paginationLimit.addEventListener('change', function() {
    itemsPerPage = parseInt(this.value);
    currentPage = 1;
    applyPagination();
});

function applyPagination() {
    const tbody = document.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    // Calculate pagination
    const totalItems = rows.length;
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    
    // Show/hide rows based on current page
    rows.forEach((row, index) => {
        if (index >= startIndex && index < endIndex) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update pagination info
    document.getElementById('showing-start').textContent = totalItems > 0 ? startIndex + 1 : 0;
    document.getElementById('showing-end').textContent = Math.min(endIndex, totalItems);
    document.getElementById('total-count').textContent = totalItems;
    
    // Update pagination buttons
    updatePaginationButtons(totalPages);
}

function updatePaginationButtons(totalPages) {
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
if (document.querySelector('tbody')) {
    applyPagination();
}

// Pagination navigation functions (global scope)
window.changePage = function(direction) {
    const tbody = document.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const totalPages = Math.ceil(rows.length / itemsPerPage);
    
    currentPage += direction;
    if (currentPage < 1) currentPage = 1;
    if (currentPage > totalPages) currentPage = totalPages;
    
    applyPagination();
};

window.goToPage = function(page) {
    currentPage = page;
    applyPagination();
};
</script>
@endpush
