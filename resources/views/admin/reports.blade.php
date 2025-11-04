@extends('layouts.app')

@section('page-title', 'Reports Management')


@section('content')
<div class="space-y-4 md:space-y-6" data-admin-role="{{ $adminRole ?? '' }}" data-user-id="{{ Auth::id() }}">
    <!-- Filter Card -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6">
        <div class="flex flex-col gap-3 md:gap-4">
            <!-- Search and Reset Row -->
            <div class="flex flex-col sm:flex-row gap-3 md:gap-4">
                <div class="flex-1">
                    <label class="form-label">Search</label>
                    <input type="text" id="search-input" class="form-input w-full" placeholder="Search by reporter, violator, plate number, or location...">
                </div>
                <div class="flex-shrink-0 sm:w-auto">
                    <label class="form-label opacity-0 hidden sm:block">Reset</label>
                    <button id="reset-filters" class="btn btn-secondary !h-[38px] w-full sm:w-auto px-6">Reset</button>
                </div>
            </div>

            <!-- Filters Row -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 md:gap-4">
                <!-- Status Filter -->
                <div class="w-full">
                    <label class="form-label">Status</label>
                    <select id="status-filter" class="form-input w-full">
                        <option value="">All Status</option>
                        <option value="pending" selected>Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>

                <!-- Violation Type Filter -->
                <div class="w-full">
                    <label class="form-label">Violation Type</label>
                    <select id="violation-filter" class="form-input w-full">
                        <option value="">All Types</option>
                        @foreach($violationTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Start Date -->
                <div class="w-full">
                    <label class="form-label">Start Date</label>
                    <input type="date" id="start-date-filter" class="form-input w-full">
                </div>

                <!-- End Date -->
                <div class="w-full">
                    <label class="form-label">End Date</label>
                    <input type="date" id="end-date-filter" class="form-input w-full">
                </div>

                @if(isset($adminRole))
                    @if($adminRole === 'Global Administrator')
                        <!-- Global Admin: User Type Filter -->
                        <div class="w-full">
                            <label class="form-label">User Type</label>
                            <select id="usertype-filter" class="form-input w-full">
                                <option value="">All Types</option>
                                <option value="student">Student</option>
                                <option value="staff">Staff</option>
                                <option value="security">Security</option>
                                <option value="stakeholder">Stakeholder</option>
                            </select>
                        </div>
                    @elseif(in_array($adminRole, ['Chancellor', 'Security']))
                        <!-- User Type Filter (Chancellor & Security only) -->
                        <div class="w-full">
                            <label class="form-label">User Type</label>
                            <select id="usertype-filter" class="form-input w-full">
                                <option value="">All Types</option>
                                <option value="staff">Staff</option>
                                <option value="security">Security</option>
                                <option value="stakeholder">Stakeholder</option>
                            </select>
                        </div>
                    @elseif($adminRole === 'SAS (Student Affairs & Services)')
                        <!-- College Filter (SAS only) -->
                        <div class="w-full">
                            <label class="form-label">College</label>
                            <select id="college-filter" class="form-input w-full">
                                <option value="">All Colleges</option>
                                @foreach($colleges as $college)
                                    <option value="{{ $college->id }}">{{ $college->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                @endif
                
                <!-- College Filter for Global Admin (appears when Student is selected) -->
                @if(isset($adminRole) && $adminRole === 'Global Administrator')
                    <div class="w-full hidden" id="college-filter-container">
                        <label class="form-label">College</label>
                        <select id="college-filter" class="form-input w-full">
                            <option value="">All Colleges</option>
                            @foreach($colleges as $college)
                                <option value="{{ $college->id }}">{{ $college->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Reports Table -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4 md:mb-6">
            <h3 class="text-base md:text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Violation Reports</h3>
            <div class="flex flex-wrap items-center gap-3 md:gap-4">
                <div class="flex items-center gap-2">
                    <span class="text-xs md:text-sm text-[#706f6c] dark:text-[#A1A09A]">Show:</span>
                    <select id="pagination-limit" class="form-input !h-[38px] !py-1 !px-3 text-xs md:text-sm">
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <button onclick="exportToCSV()" class="btn btn-csv !text-xs md:!text-sm">CSV</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
                        <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Report ID</th>
                        <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Reported By</th>
                        <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Violator</th>
                        <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Violation Type</th>
                        <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Location</th>
                        <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Status</th>
                        <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Date</th>
                        <th class="text-center py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="reportsTableBody">
                    @forelse($reports as $report)
                    <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A] hover:bg-gray-50 dark:hover:bg-[#161615]" 
                        data-report-id="{{ $report->id }}"
                        data-violation-type-id="{{ $report->violation_type_id }}"
                        data-reported-date="{{ $report->reported_at?->format('Y-m-d') }}">
                        <td class="py-2 px-3">
                            <span class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">#{{ $report->id }}</span>
                        </td>
                        <td class="py-2 px-3">
                            <div>
                                <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">{{ $report->reportedBy->first_name ?? '' }} {{ $report->reportedBy->last_name ?? '' }}</p>
                                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">{{ $report->reportedBy?->user_type?->label() ?? 'N/A' }}</p>
                            </div>
                        </td>
                        <td class="py-2 px-3" data-user-type="{{ $report->violatorVehicle->user->user_type?->value ?? '' }}" data-college-id="{{ $report->violatorVehicle->user->student->college_id ?? '' }}">
                            @if($report->violatorVehicle)
                                <div>
                                    <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">{{ $report->violatorVehicle->user->first_name ?? '' }} {{ $report->violatorVehicle->user->last_name ?? '' }}</p>
                                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">{{ $report->violatorVehicle->type->name ?? 'N/A' }} - {{ $report->violatorVehicle->plate_no ?? 'N/A' }}</p>
                                </div>
                            @else
                                <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Sticker: {{ $report->violator_sticker_number ?? 'N/A' }}</span>
                            @endif
                        </td>
                        <td class="py-2 px-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">{{ $report->violationType->name ?? 'N/A' }}</td>
                        <td class="py-2 px-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">{{ Str::limit($report->location, 30) }}</td>
                        <td class="py-2 px-3">
                            @php
                                $statusClasses = [
                                    'pending' => 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200',
                                    'approved' => 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200',
                                    'rejected' => 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200',
                                ];
                                $statusClass = $statusClasses[$report->status] ?? 'bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200';
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                                {{ ucwords(str_replace('_', ' ', $report->status)) }}
                            </span>
                        </td>
                        <td class="py-2 px-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">{{ $report->reported_at->format('M d, Y') }}</td>
                        <td class="py-2 px-3">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="viewReport({{ $report->id }})" class="btn-view" title="View Details">
                                    <x-heroicon-s-eye class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 px-4 text-center text-[#706f6c] dark:text-[#A1A09A]">
                            No reports found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <div id="pagination-controls" class="flex flex-col sm:flex-row items-center justify-between gap-3 mt-4 md:mt-6 px-4 md:px-6">
            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] text-center sm:text-left">
                Showing <span id="showing-start">1</span>-<span id="showing-end">10</span> of <span id="total-count">{{ $reports->total() }}</span> reports
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

<!-- View Report Modal -->
<div id="viewModal" class="modal-backdrop hidden" onclick="if(event.target === this) closeViewModal()">
    <div class="modal-container-wide">
        <div class="modal-header">
            <h2 class="modal-title">Report Details</h2>
        </div>
        <div class="modal-body max-h-[70vh] overflow-y-auto">
            <div id="viewModalContent">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
        <div class="modal-footer">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between w-full gap-3">
                <div class="flex items-center space-x-3 w-full sm:w-auto">
                    <label for="statusSelect" class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] whitespace-nowrap">Status:</label>
                    <div class="relative flex-1 sm:flex-initial">
                        <select id="statusSelect" onchange="updateReportStatus()" class="appearance-none bg-white dark:bg-[#2a2a2a] border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-lg px-4 py-2 pr-8 text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 hover:border-[#d1d5db] dark:hover:border-[#4b5563] min-w-[120px] w-full sm:w-auto">
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                            <svg class="w-4 h-4 text-[#6b7280] dark:text-[#9ca3af]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                <button onclick="closeViewModal()" class="btn btn-secondary px-4 py-2 rounded-lg font-medium text-sm transition-all duration-200 hover:shadow-md w-full sm:w-auto">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="modal-backdrop hidden">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title flex items-center gap-3">
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center flex-shrink-0">
                    <x-heroicon-o-check-circle class="w-6 h-6 text-green-600 dark:text-green-400" />
                </div>
                <span class="text-[#1b1b18] dark:text-[#EDEDEC]">Status Updated!</span>
            </h2>
        </div>
        <div class="modal-body">
            <p id="successMessage" class="text-[#706f6c] dark:text-[#A1A09A]"></p>
        </div>
        <div class="modal-footer">
            <button onclick="closeSuccessModal()" class="btn btn-primary">OK</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let reports = @json($reports->items() ?? []);
const mapLocations = @json($mapLocations ?? []);

document.addEventListener('DOMContentLoaded', function() {
    // Filter functionality
    const searchInput = document.getElementById('search-input');
    const statusFilter = document.getElementById('status-filter');
    const violationFilter = document.getElementById('violation-filter');
    const startDateFilter = document.getElementById('start-date-filter');
    const endDateFilter = document.getElementById('end-date-filter');
    const usertypeFilter = document.getElementById('usertype-filter');
    const collegeFilter = document.getElementById('college-filter');
    const resetButton = document.getElementById('reset-filters');
    
    searchInput.addEventListener('input', applyPagination);
    statusFilter.addEventListener('change', applyPagination);
    violationFilter.addEventListener('change', applyPagination);
    startDateFilter.addEventListener('change', applyPagination);
    endDateFilter.addEventListener('change', applyPagination);
    
    if (usertypeFilter) {
        usertypeFilter.addEventListener('change', function() {
            // For Global Admin: Show/hide college filter when Student is selected
            const collegeFilterContainer = document.getElementById('college-filter-container');
            if (collegeFilterContainer) {
                if (this.value === 'student') {
                    collegeFilterContainer.style.display = 'block';
                } else {
                    collegeFilterContainer.style.display = 'none';
                    // Reset college filter when hiding
                    if (collegeFilter) collegeFilter.value = '';
                }
            }
            applyPagination();
        });
    }
    if (collegeFilter) collegeFilter.addEventListener('change', applyPagination);

    resetButton.addEventListener('click', function() {
        searchInput.value = '';
        statusFilter.value = 'pending';
        violationFilter.value = '';
        startDateFilter.value = '';
        endDateFilter.value = '';
        if (usertypeFilter) usertypeFilter.value = '';
        if (collegeFilter) collegeFilter.value = '';
        
        // Hide college filter container for Global Admin
        const collegeFilterContainer = document.getElementById('college-filter-container');
        if (collegeFilterContainer) {
            collegeFilterContainer.style.display = 'none';
        }
        
        currentPage = 1;
        applyPagination();
    });

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
        const rows = document.querySelectorAll('#reportsTableBody tr');
        let visibleCount = 0;
        let totalFiltered = 0;
        
        const searchTerm = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value.toLowerCase();
        const violationValue = violationFilter.value;
        const startDate = startDateFilter.value;
        const endDate = endDateFilter.value;
        const usertypeValue = usertypeFilter ? usertypeFilter.value.toLowerCase() : '';
        const collegeValue = collegeFilter ? collegeFilter.value : '';
        
        // First pass: count total filtered rows
        rows.forEach((row) => {
            // Skip empty state row
            if (row.querySelector('td[colspan]')) {
                return;
            }
            
            const reporter = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
            const violator = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
            const violatorType = row.querySelector('td:nth-child(3)')?.dataset.userType?.toLowerCase() || '';
            const collegeId = row.querySelector('td:nth-child(3)')?.dataset.collegeId || '';
            const vehicle = row.querySelector('td:nth-child(4)')?.textContent.toLowerCase() || '';
            const location = row.querySelector('td:nth-child(5)')?.textContent.toLowerCase() || '';
            const statusBadge = row.querySelector('td:nth-child(6) span');
            const status = statusBadge?.textContent.trim().toLowerCase().replace(' ', '_') || '';
            const violationTypeId = row.dataset.violationTypeId || '';
            const reportedDate = row.dataset.reportedDate || '';
            
            const matchesSearch = reporter.includes(searchTerm) || violator.includes(searchTerm) || 
                                location.includes(searchTerm) || vehicle.includes(searchTerm);
            const matchesStatus = statusValue === '' || status.includes(statusValue);
            const matchesViolation = violationValue === '' || violationTypeId === violationValue;
            const matchesUsertype = usertypeValue === '' || violatorType.includes(usertypeValue);
            const matchesCollege = collegeValue === '' || collegeId === collegeValue;
            
            // Date range filtering
            let matchesDateRange = true;
            if (startDate && reportedDate) {
                matchesDateRange = matchesDateRange && reportedDate >= startDate;
            }
            if (endDate && reportedDate) {
                matchesDateRange = matchesDateRange && reportedDate <= endDate;
            }
            
            if (matchesSearch && matchesStatus && matchesViolation && matchesDateRange && matchesUsertype && matchesCollege) {
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
            
            const reporter = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
            const violator = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
            const violatorType = row.querySelector('td:nth-child(3)')?.dataset.userType?.toLowerCase() || '';
            const collegeId = row.querySelector('td:nth-child(3)')?.dataset.collegeId || '';
            const vehicle = row.querySelector('td:nth-child(4)')?.textContent.toLowerCase() || '';
            const location = row.querySelector('td:nth-child(5)')?.textContent.toLowerCase() || '';
            const statusBadge = row.querySelector('td:nth-child(6) span');
            const status = statusBadge?.textContent.trim().toLowerCase().replace(' ', '_') || '';
            const violationTypeId = row.dataset.violationTypeId || '';
            const reportedDate = row.dataset.reportedDate || '';
            
            const matchesSearch = reporter.includes(searchTerm) || violator.includes(searchTerm) || 
                                location.includes(searchTerm) || vehicle.includes(searchTerm);
            const matchesStatus = statusValue === '' || status.includes(statusValue);
            const matchesViolation = violationValue === '' || violationTypeId === violationValue;
            const matchesUsertype = usertypeValue === '' || violatorType.includes(usertypeValue);
            const matchesCollege = collegeValue === '' || collegeId === collegeValue;
            
            // Date range filtering
            let matchesDateRange = true;
            if (startDate && reportedDate) {
                matchesDateRange = matchesDateRange && reportedDate >= startDate;
            }
            if (endDate && reportedDate) {
                matchesDateRange = matchesDateRange && reportedDate <= endDate;
            }
            
            if (!matchesSearch || !matchesStatus || !matchesViolation || !matchesDateRange || !matchesUsertype || !matchesCollege) {
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
        
        document.getElementById('showing-start').textContent = start;
        document.getElementById('showing-end').textContent = end;
        document.getElementById('total-count').textContent = totalFiltered;
        
        const prevBtn = document.getElementById('prev-page');
        const nextBtn = document.getElementById('next-page');
        
        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage === totalPages || totalPages === 0;
        
        prevBtn.className = currentPage === 1 ? 'btn-pagination btn-paginationDisable' : 'btn-pagination btn-paginationArrow';
        nextBtn.className = (currentPage === totalPages || totalPages === 0) ? 'btn-pagination btn-paginationDisable' : 'btn-pagination btn-paginationArrow';
        
        const pageNumbers = document.getElementById('page-numbers');
        pageNumbers.innerHTML = '';
        
        let startPage = Math.max(1, currentPage - 1);
        let endPage = Math.min(totalPages, startPage + 2);
        
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

    applyPagination();
    
    window.changePage = function(direction) {
        const rows = document.querySelectorAll('#reportsTableBody tr');
        let totalFiltered = 0;
        
        rows.forEach((row) => {
            // Skip empty state row
            if (row.querySelector('td[colspan]')) {
                return;
            }
            
            const reporter = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
            const violator = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
            const location = row.querySelector('td:nth-child(5)')?.textContent.toLowerCase() || '';
            const statusBadge = row.querySelector('td:nth-child(6) span');
            const status = statusBadge?.textContent.trim().toLowerCase().replace(' ', '_') || '';
            
            const searchTerm = searchInput.value.toLowerCase();
            const statusValue = statusFilter.value.toLowerCase();
            
            const matchesSearch = reporter.includes(searchTerm) || violator.includes(searchTerm) || location.includes(searchTerm);
            const matchesStatus = statusValue === '' || status.includes(statusValue);
            
            if (matchesSearch && matchesStatus) {
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

function viewReport(id) {
    const report = reports.find(r => r.id === id);
    if (!report) return;
    
    // Populate modal content
    const modalContent = document.getElementById('viewModalContent');
    modalContent.innerHTML = `
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6">
            <!-- Left Column - Report Details -->
            <div class="space-y-3 md:space-y-4 lg:col-span-1">
                <div>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Report ID</p>
                    <p class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]">#${report.id}</p>
                </div>
                
                <div>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Reported By</p>
                    <p class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${report.reported_by ? report.reported_by.first_name + ' ' + report.reported_by.last_name : 'N/A'}</p>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">${report.reported_by ? report.reported_by.email : ''}</p>
                </div>
                
                <div>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Violator</p>
                    <p class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${report.violator_vehicle && report.violator_vehicle.user ? report.violator_vehicle.user.first_name + ' ' + report.violator_vehicle.user.last_name : 'N/A'}</p>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">${report.violator_vehicle && report.violator_vehicle.user ? report.violator_vehicle.user.email : ''}</p>
                </div>
                
                <div>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Vehicle Details</p>
                    <p class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                        ${report.violator_vehicle ? 
                            (report.violator_vehicle.plate_no || `${report.violator_vehicle.color}-${report.violator_vehicle.number}`) + 
                            (report.violator_vehicle.type ? ` (${report.violator_vehicle.type.name})` : '') 
                            : 'N/A'
                        }
                    </p>
                </div>
                
                <div>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Violation Type</p>
                    <p class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${report.violation_type ? report.violation_type.name : 'N/A'}</p>
                </div>
                
                <div>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Location</p>
                    <p class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${report.location || 'N/A'}</p>
                </div>
                
                <div>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Description</p>
                    <p class="font-medium text-[#1b1b18] dark:text-[#EDEDEC] break-words">${report.description || 'N/A'}</p>
                </div>
                
                <div>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Status</p>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                        report.status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' :
                        report.status === 'approved' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' :
                        report.status === 'rejected' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' :
                        'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'
                    }">
                        ${report.status ? report.status.charAt(0).toUpperCase() + report.status.slice(1).replace('_', ' ') : 'Unknown'}
                    </span>
                </div>
                
                <div>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Reported At</p>
                    <p class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${report.reported_at ? new Date(report.reported_at).toLocaleString() : 'N/A'}</p>
                </div>
                
                ${report.assigned_to ? `
                <div>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Assigned To</p>
                    <p class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${report.assigned_to.first_name} ${report.assigned_to.last_name}</p>
                </div>
                ` : ''}
                
                ${report.remarks ? `
                <div>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Remarks</p>
                    <p class="font-medium text-[#1b1b18] dark:text-[#EDEDEC] break-words">${report.remarks}</p>
                </div>
                ` : ''}
            </div>
            
            <!-- Right Column - Map and Evidence Image -->
            <div class="space-y-4 md:space-y-6 lg:col-span-1">
                <!-- Violation Location Map -->
                <div class="space-y-3">
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Violation Location</p>
                <div class="relative w-full rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-900 border border-[#e3e3e0] dark:border-[#3E3E3A]" id="report-detail-map-container">
                    <div class="relative overflow-hidden" id="report-detail-map-wrapper">
                        <img src="{{ asset('images/campus-map.svg') }}" alt="Campus Map" id="report-detail-map-image" class="w-full h-auto block" />
                        
                        <!-- Pin -->
                        <div id="report-detail-pin" class="absolute pointer-events-none hidden" style="transform-origin: 50% 100%; z-index: 20;">
                            <svg width="16" height="20" viewBox="0 0 32 40" fill="none" xmlns="http://www.w3.org/2000/svg" style="display: block; margin-left: -8px; margin-top: -20px;">
                                <path d="M16 0C9.373 0 4 5.373 4 12c0 8.4 12 28 12 28s12-19.6 12-28c0-6.627-5.373-12-12-12z" fill="#EA4335"/>
                                <circle cx="16" cy="12" r="4" fill="white"/>
                                <circle cx="16" cy="12" r="2" fill="#C5221F"/>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <!-- Evidence Image -->
                <div class="space-y-3">
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Evidence Image</p>
                    ${report.evidence_image ? 
                        `<img src="/storage/${report.evidence_image}" alt="Evidence" class="w-full h-auto max-h-64 md:max-h-80 object-contain rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A]">` :
                        '<div class="w-full h-48 bg-gray-100 dark:bg-gray-800 rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] flex items-center justify-center"><p class="text-[#706f6c] dark:text-[#A1A09A]">No evidence image</p></div>'
                    }
                </div>
            </div>
        </div>
    `;
    
    // Set current status in dropdown
    const statusSelect = document.getElementById('statusSelect');
    if (statusSelect) {
        statusSelect.value = report.status || 'pending';
    }
    
    // Store current report ID for status update
    window.currentReportId = id;
    
    document.getElementById('viewModal').classList.remove('hidden');
    
    // Initialize map after modal is shown
    setTimeout(() => {
        initializeReportDetailMap(report);
    }, 100);
}

// Initialize report detail map with location and pin
function initializeReportDetailMap(report) {
    const img = document.getElementById('report-detail-map-image');
    const container = document.getElementById('report-detail-map-container');
    const wrapper = document.getElementById('report-detail-map-wrapper');
    const pin = document.getElementById('report-detail-pin');
    
    console.log('🗺️ Initializing report map for report:', report.id);
    console.log('📍 Pin coordinates:', report.pin_x, report.pin_y);
    
    if (!img || !container || !wrapper || !pin) {
        console.error('❌ Map elements not found');
        return;
    }
    
    // Wait for image to load
    if (!img.complete || img.naturalHeight === 0) {
        console.log('⏳ Waiting for image to load...');
        img.onload = () => initializeReportDetailMap(report);
        return;
    }
    
    // Get aspect ratio
    const aspectRatio = img.naturalHeight / img.naturalWidth;
    container.style.aspectRatio = `${img.naturalWidth} / ${img.naturalHeight}`;
    
    console.log('📐 Container dimensions:', container.offsetWidth, 'x', container.offsetHeight);
    
    // Check if report has pin coordinates
    if (report.pin_x && report.pin_y) {
        console.log('✅ Report has pin coordinates');
        
        // Show pin at location
        pin.style.left = report.pin_x + '%';
        pin.style.top = report.pin_y + '%';
        pin.classList.remove('hidden');
        
        console.log('📌 Pin positioned at:', report.pin_x + '%', report.pin_y + '%');
        
        // Calculate zoom and pan to center on pin
        const zoomScale = 2.5; // 2.5x zoom for better view
        const pinX = parseFloat(report.pin_x);
        const pinY = parseFloat(report.pin_y);
        
        // Calculate offset to center the pin
        // Pin is at pinX%, pinY% - we want it at center (50%, 50%)
        const offsetX = 50 - pinX;
        const offsetY = 50 - pinY;
        
        // Convert percentage offset to pixels
        const containerWidth = container.offsetWidth;
        const containerHeight = container.offsetHeight;
        const panX = (offsetX / 100) * containerWidth * zoomScale;
        const panY = (offsetY / 100) * containerHeight * zoomScale;
        
        console.log('🎯 Zoom scale:', zoomScale);
        console.log('🔄 Pan offset:', panX, panY);
        
        // Apply zoom and pan transformation
        wrapper.style.transform = `translate(${panX}px, ${panY}px) scale(${zoomScale})`;
        wrapper.style.transformOrigin = 'center center';
        
        console.log('✅ Map centered on violation location');
    } else {
        console.log('⚠️ No pin coordinates found for this report');
        // No pin location, hide pin
        pin.classList.add('hidden');
        // Reset transform
        wrapper.style.transform = 'translate(0, 0) scale(1)';
    }
}

function closeViewModal() {
    document.getElementById('viewModal').classList.add('hidden');
}

async function updateReportStatus() {
    const reportId = window.currentReportId;
    const newStatus = document.getElementById('statusSelect').value;
    const remarks = document.getElementById('remarksInput')?.value || '';
    
    if (!reportId || !newStatus) {
        showErrorModal('Invalid report or status');
        return;
    }
    
    try {
        const response = await fetch(`/reports/${reportId}/status`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                status: newStatus,
                remarks: remarks
            })
        });
        
        if (response.ok) {
            const result = await response.json();
            
            // Update the report in the local array
            const reportIndex = reports.findIndex(r => r.id === reportId);
            if (reportIndex !== -1) {
                reports[reportIndex].status = newStatus;
            }
            
            // Update the table display
            updateTableDisplay();
            
            // Re-apply filters to hide/show rows based on current filter
            applyPagination();
            
            // Close view modal
            closeViewModal();
            
            // Show success modal
            const statusLabel = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
            showSuccessModal(`Report #${reportId} status updated to ${statusLabel}`);
        } else {
            const error = await response.json();
            showErrorModal('Error updating status: ' + (error.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error updating status:', error);
        showErrorModal('Error updating status. Please try again.');
    }
}

function showSuccessModal(message) {
    document.getElementById('successMessage').textContent = message;
    document.getElementById('successModal').classList.remove('hidden');
}

function closeSuccessModal() {
    document.getElementById('successModal').classList.add('hidden');
}

function showErrorModal(message) {
    alert(message);
}

function updateTableDisplay() {
    // Refresh the table to show updated status
    const rows = document.querySelectorAll('#reportsTableBody tr');
    rows.forEach(row => {
        const reportId = parseInt(row.dataset.reportId);
        const report = reports.find(r => r.id === reportId);
        if (report) {
            const statusCell = row.querySelector('td:nth-child(6)');
            if (statusCell) {
                const statusClass = report.status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' :
                                  report.status === 'approved' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' :
                                  report.status === 'rejected' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' :
                                  'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
                
                const statusText = report.status ? report.status.charAt(0).toUpperCase() + report.status.slice(1).replace('_', ' ') : 'Unknown';
                statusCell.innerHTML = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClass}">${statusText}</span>`;
            }
        }
    });
}

function renderTable() {
    const tbody = document.getElementById('reportsTableBody');
    tbody.innerHTML = '';
    
    if (reports.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="py-8 px-4 text-center text-[#706f6c] dark:text-[#A1A09A]">
                    No reports found.
                </td>
            </tr>
        `;
        return;
    }
    
    reports.forEach(report => {
        const row = document.createElement('tr');
        row.className = 'border-b border-[#e3e3e0] dark:border-[#3E3E3A] hover:bg-gray-50 dark:hover:bg-[#161615]';
        row.dataset.reportId = report.id;
        row.dataset.violationTypeId = report.violation_type_id || '';
        row.dataset.reportedDate = report.reported_at ? new Date(report.reported_at).toISOString().split('T')[0] : '';
        
        const statusClasses = {
            'pending': 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200',
            'approved': 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200',
            'rejected': 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200'
        };
        const statusClass = statusClasses[report.status] || 'bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200';
        const statusText = report.status ? report.status.charAt(0).toUpperCase() + report.status.slice(1).replace('_', ' ') : 'Unknown';
        
        row.innerHTML = `
            <td class="py-2 px-3">
                <span class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">#${report.id}</span>
            </td>
            <td class="py-2 px-3">
                <div>
                    <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${report.reported_by?.first_name || ''} ${report.reported_by?.last_name || ''}</p>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">${report.reported_by?.user_type || 'N/A'}</p>
                </div>
            </td>
            <td class="py-2 px-3" data-user-type="${report.violator_vehicle?.user?.user_type || ''}" data-college-id="${report.violator_vehicle?.user?.student?.college_id || ''}">
                ${report.violator_vehicle ? `
                    <div>
                        <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${report.violator_vehicle.user?.first_name || ''} ${report.violator_vehicle.user?.last_name || ''}</p>
                        <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">${report.violator_vehicle.type?.name || 'N/A'} - ${report.violator_vehicle.plate_no || 'N/A'}</p>
                    </div>
                ` : `<span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Sticker: ${report.violator_sticker_number || 'N/A'}</span>`}
            </td>
            <td class="py-2 px-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">${report.violation_type?.name || 'N/A'}</td>
            <td class="py-2 px-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">${report.location ? (report.location.length > 30 ? report.location.substring(0, 30) + '...' : report.location) : 'N/A'}</td>
            <td class="py-2 px-3">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClass}">${statusText}</span>
            </td>
            <td class="py-2 px-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">${report.reported_at ? new Date(report.reported_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'N/A'}</td>
            <td class="py-2 px-3">
                <div class="flex items-center justify-center gap-2">
                    <button onclick="viewReport(${report.id})" class="btn-view" title="View Details">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </button>
                </div>
            </td>
        `;
        
        tbody.appendChild(row);
    });
    
    // Apply pagination after rendering
    applyPagination();
}

function exportToCSV() {
    const searchInput = document.getElementById('search-input');
    const statusFilter = document.getElementById('status-filter');
    const violationFilter = document.getElementById('violation-filter');
    const startDateFilter = document.getElementById('start-date-filter');
    const endDateFilter = document.getElementById('end-date-filter');
    const usertypeFilter = document.getElementById('usertype-filter');
    const collegeFilter = document.getElementById('college-filter');
    
    const searchTerm = searchInput.value.toLowerCase();
    const statusValue = statusFilter.value.toLowerCase();
    const violationValue = violationFilter.value;
    const startDate = startDateFilter.value;
    const endDate = endDateFilter.value;
    const usertypeValue = usertypeFilter ? usertypeFilter.value.toLowerCase() : '';
    const collegeValue = collegeFilter ? collegeFilter.value : '';
    
    // Filter reports based on current filters
    const filteredReports = reports.filter(report => {
        const reporter = `${report.reported_by?.first_name || ''} ${report.reported_by?.last_name || ''}`.toLowerCase();
        const violator = report.violator_vehicle?.user ? 
            `${report.violator_vehicle.user.first_name} ${report.violator_vehicle.user.last_name}`.toLowerCase() : '';
        const vehicle = report.violator_vehicle ? 
            `${report.violator_vehicle.type?.name || ''} ${report.violator_vehicle.plate_no || ''}`.toLowerCase() : '';
        const location = (report.location || '').toLowerCase();
        const status = (report.status || '').toLowerCase();
        const violationTypeId = String(report.violation_type_id || '');
        const violatorType = report.violator_vehicle?.user?.user_type?.toLowerCase() || '';
        const collegeId = String(report.violator_vehicle?.user?.student?.college_id || '');
        const reportedDate = report.reported_at ? new Date(report.reported_at).toISOString().split('T')[0] : '';
        
        const matchesSearch = searchTerm === '' || reporter.includes(searchTerm) || 
                            violator.includes(searchTerm) || vehicle.includes(searchTerm) || 
                            location.includes(searchTerm);
        const matchesStatus = statusValue === '' || status.includes(statusValue);
        const matchesViolation = violationValue === '' || violationTypeId === violationValue;
        const matchesUsertype = usertypeValue === '' || violatorType.includes(usertypeValue);
        const matchesCollege = collegeValue === '' || collegeId === collegeValue;
        
        let matchesDateRange = true;
        if (startDate && reportedDate) {
            matchesDateRange = matchesDateRange && reportedDate >= startDate;
        }
        if (endDate && reportedDate) {
            matchesDateRange = matchesDateRange && reportedDate <= endDate;
        }
        
        return matchesSearch && matchesStatus && matchesViolation && matchesDateRange && 
               matchesUsertype && matchesCollege;
    });
    
    if (filteredReports.length === 0) {
        alert('No reports to export with current filters');
        return;
    }
    
    const csvData = [];
    csvData.push(['Report ID', 'Reported By', 'Reporter Type', 'Violator', 'Violator Type', 'Vehicle', 'Violation Type', 'Location', 'Status', 'Date']);

    filteredReports.forEach(report => {
        csvData.push([
            `#${report.id}`,
            `${report.reported_by?.first_name || ''} ${report.reported_by?.last_name || ''}`,
            report.reported_by?.user_type || 'N/A',
            report.violator_vehicle?.user ? `${report.violator_vehicle.user.first_name} ${report.violator_vehicle.user.last_name}` : 'N/A',
            report.violator_vehicle?.user?.user_type || 'N/A',
            report.violator_vehicle ? `${report.violator_vehicle.type?.name || 'N/A'} - ${report.violator_vehicle.plate_no || 'N/A'}` : `Sticker: ${report.violator_sticker_number || 'N/A'}`,
            report.violation_type?.name || 'N/A',
            report.location || 'N/A',
            report.status,
            new Date(report.reported_at).toLocaleDateString()
        ]);
    });

    const csvContent = csvData.map(row => row.map(cell => `"${cell}"`).join(',')).join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `reports_filtered_${new Date().toISOString().split('T')[0]}.csv`;
    link.click();
}

// Expose functions to global scope
window.viewReport = viewReport;
window.closeViewModal = closeViewModal;
window.exportToCSV = exportToCSV;

// Real-time updates using Laravel Echo
if (window.Echo) {
    window.Echo.channel('reports')
        .listen('.report.created', (event) => {
            console.log('New report received:', event.report);
            
            // Add the new report to the beginning of the array
            reports.unshift(event.report);
            
            // Re-render the table
            renderTable();
            
            // Show notification
            showNotification('New report received from ' + (event.report.reported_by?.first_name || 'Unknown'), event.report.id);
        })
        .listen('.report.status.updated', (event) => {
            console.log('Report status updated:', event.report);
            
            // Get current user ID
            const currentUserId = parseInt(document.querySelector('[data-user-id]')?.dataset.userId);
            
            // Check if current user is the one who updated the status
            const updatedById = event.report.updated_by?.id || event.report.updated_by;
            
            // Skip if current user is the actor
            if (currentUserId && updatedById && currentUserId === updatedById) {
                console.log('Skipping self-triggered notification');
                return;
            }
            
            // Find and update the report in the array
            const index = reports.findIndex(r => r.id === event.report.id);
            if (index !== -1) {
                const oldStatus = reports[index].status;
                reports[index] = event.report;
                
                // Update the table row
                updateTableDisplay();
                
                // Show notification for status change
                const statusLabel = event.report.status.charAt(0).toUpperCase() + event.report.status.slice(1).replace('_', ' ');
                showNotification(`Report #${event.report.id} status changed to ${statusLabel}`, event.report.id);
            }
        });
}

// Show notification function
function showNotification(message, reportId = null) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 bg-blue-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-fade-in cursor-pointer hover:bg-blue-600 transition-colors';
    notification.innerHTML = `
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
            </svg>
            <span>${message}</span>
        </div>
    `;
    
    // Add click handler to open modal if reportId is provided
    if (reportId) {
        notification.addEventListener('click', () => {
            notification.remove();
            viewReport(reportId);
        });
    }
    
    document.body.appendChild(notification);
    
    // Remove after 5 seconds
    setTimeout(() => {
        notification.classList.add('animate-fade-out');
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

// Check URL parameters on page load to open modal
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const reportId = urlParams.get('view');
    
    if (reportId) {
        // Wait for reports to be loaded
        setTimeout(() => {
            const report = reports.find(r => r.id == reportId);
            if (report) {
                viewReport(parseInt(reportId));
                // Remove query parameter from URL without reloading
                const newUrl = window.location.pathname;
                window.history.replaceState({}, '', newUrl);
            }
        }, 500);
    }
});

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (window.Echo) {
        window.Echo.leaveChannel('reports');
    }
});
</script>
@endpush
