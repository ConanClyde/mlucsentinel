@extends('layouts.app')

@section('page-title', 'My Reports')

@section('content')
<div class="space-y-4 md:space-y-6" data-user-id="{{ Auth::id() }}">
    <!-- Filter Card -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6">
        <div class="flex flex-col gap-3 md:gap-4">
            <!-- Search -->
            <div class="w-full">
                <label class="form-label">Search</label>
                <input type="text" id="search-input" class="form-input w-full" placeholder="Search by violator, vehicle, or location...">
            </div>

            <!-- Filters Row -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 md:gap-4">
                <!-- Status Filter -->
                <div class="w-full">
                    <label class="form-label">Status</label>
                    <select id="status-filter" class="form-input w-full">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>

                <!-- Violation Type Filter -->
                <div class="w-full">
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
                <div class="w-full flex items-end">
                    <button id="reset-filters" class="btn btn-secondary w-full !h-[38px]">Reset</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reports Table -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6">
        <div class="flex items-center justify-between mb-4 md:mb-6">
            <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">My Reports</h3>
            <div class="flex items-center gap-2">
                <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Show:</span>
                <select id="pagination-limit" class="form-input !h-[38px] !py-1 !px-3 text-sm">
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
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
                                        'approved' => 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200',
                                        'rejected' => 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200',
                                    ];
                                    $statusClass = $statusColors[$report->status] ?? 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200';
                                    
                                    $violatorName = $report->violatorVehicle && $report->violatorVehicle->user 
                                        ? $report->violatorVehicle->user->first_name . ' ' . $report->violatorVehicle->user->last_name
                                        : 'Unknown';
                                    
                                    $vehicleInfo = $report->violatorVehicle 
                                        ? ($report->violatorVehicle->plate_no ?: $report->violator_sticker_number)
                                        : 'N/A';
                            @endphp
                            
                            <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A] hover:bg-gray-50 dark:hover:bg-[#2a2a2a] transition-colors" data-report-id="{{ $report->id }}">
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
        <div class="modal-body max-h-[70vh] overflow-y-auto" id="viewModalContent">
            <!-- Content will be dynamically populated -->
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
    
    // Show modal
    document.getElementById('viewModal').classList.remove('hidden');
    
    // Initialize map after modal is shown
    setTimeout(() => initializeReportDetailMap(report), 100);
}

function initializeReportDetailMap(report) {
    const img = document.getElementById('report-detail-map-image');
    const container = document.getElementById('report-detail-map-container');
    const wrapper = document.getElementById('report-detail-map-wrapper');
    const pin = document.getElementById('report-detail-pin');
    
    if (!img || !container || !wrapper || !pin) {
        console.error('❌ Map elements not found');
        return;
    }
    
    if (!img.complete || img.naturalHeight === 0) {
        img.onload = () => initializeReportDetailMap(report);
        return;
    }
    
    const aspectRatio = img.naturalHeight / img.naturalWidth;
    container.style.aspectRatio = `${img.naturalWidth} / ${img.naturalHeight}`;
    
    if (report.pin_x && report.pin_y) {
        pin.style.left = report.pin_x + '%';
        pin.style.top = report.pin_y + '%';
        pin.classList.remove('hidden');
        
        const zoomScale = 2.5;
        const pinX = parseFloat(report.pin_x);
        const pinY = parseFloat(report.pin_y);
        
        const offsetX = 50 - pinX;
        const offsetY = 50 - pinY;
        
        const containerWidth = container.offsetWidth;
        const containerHeight = container.offsetHeight;
        const panX = (offsetX / 100) * containerWidth * zoomScale;
        const panY = (offsetY / 100) * containerHeight * zoomScale;
        
        wrapper.style.transform = `translate(${panX}px, ${panY}px) scale(${zoomScale})`;
        wrapper.style.transformOrigin = 'center center';
    } else {
        pin.classList.add('hidden');
        wrapper.style.transform = 'translate(0, 0) scale(1)';
    }
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

// Real-time updates using Laravel Echo
if (window.Echo) {
    window.Echo.channel('reports')
        .listen('.report.status.updated', (event) => {
            console.log('Report status updated:', event.report);
            
            // Get current user ID
            const currentUserId = parseInt(document.querySelector('[data-user-id]')?.dataset.userId);
            
            // Check if current user is the one who updated the status
            const updatedById = event.report.updated_by?.id || event.report.updated_by;
            
            // Skip if current user is the actor (they're the one who changed it)
            if (currentUserId && updatedById && currentUserId === updatedById) {
                console.log('Skipping self-triggered notification');
                return;
            }
            
            // Check if this report belongs to the current user
            if (currentUserId && event.report.reported_by_id == currentUserId) {
                // Find and update the report in the array
                const index = reports.findIndex(r => r.id === event.report.id);
                if (index !== -1) {
                    const oldStatus = reports[index].status;
                    reports[index] = event.report;
                    
                    // Update the table row status
                    const row = document.querySelector(`tr[data-report-id="${event.report.id}"]`);
                    if (row) {
                        const statusCell = row.querySelector('td:nth-child(6)');
                        if (statusCell) {
                            const statusColors = {
                                'pending': 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200',
                                'approved': 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200',
                                'rejected': 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200',
                            };
                            const statusClass = statusColors[event.report.status] || 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200';
                            const statusText = event.report.status.charAt(0).toUpperCase() + event.report.status.slice(1);
                            statusCell.innerHTML = `<span class="px-2.5 py-1 text-xs font-medium ${statusClass} rounded-full whitespace-nowrap">${statusText}</span>`;
                        }
                    }
                    
                    // Show notification
                    const statusLabel = event.report.status.charAt(0).toUpperCase() + event.report.status.slice(1).replace('_', ' ');
                    showNotification(`Report #${event.report.id} status changed to ${statusLabel}`, event.report.id);
                }
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
