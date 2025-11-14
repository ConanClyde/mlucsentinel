@extends('layouts.app')

@section('page-title', 'Pending Registrations')

@section('content')
<div class="space-y-4 md:space-y-6">
    <!-- Filter Card -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6">
        <div class="flex flex-col lg:flex-row gap-3 md:gap-4">
            <!-- Search -->
            <div class="flex-1">
                <label class="form-label">Search</label>
                <input type="text" id="search-input" class="form-input w-full" placeholder="Search by name or email...">
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

            <!-- User Type Filter -->
            <div class="flex-1">
                <label class="form-label">User Type</label>
                <select id="user-type-filter" class="form-input w-full">
                    <option value="">All Types</option>
                    <option value="student">Student</option>
                    <option value="staff">Staff</option>
                    <option value="stakeholder">Stakeholder</option>
                    <option value="security">Security</option>
                    <option value="reporter">Reporter</option>
                </select>
                </div>

            <!-- Reset Button -->
            <div class="flex-shrink-0">
                <label class="form-label opacity-0 hidden sm:block">Reset</label>
                <button id="reset-filters" class="btn btn-secondary !h-[38px] w-full lg:w-auto px-6">Reset</button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 md:gap-6">
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6" data-stat="pending">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900 rounded-lg flex items-center justify-center flex-shrink-0">
                    <x-heroicon-o-clock class="w-6 h-6 text-amber-600 dark:text-amber-400" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Pending</p>
                    <p class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">{{ $stats['pending'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6" data-stat="approved">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center flex-shrink-0">
                    <x-heroicon-o-check-circle class="w-6 h-6 text-green-600 dark:text-green-400" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Approved</p>
                    <p class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">{{ $stats['approved'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6" data-stat="rejected">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center flex-shrink-0">
                    <x-heroicon-o-x-circle class="w-6 h-6 text-red-600 dark:text-red-400" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Rejected</p>
                    <p class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">{{ $stats['rejected'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6" data-stat="total">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center flex-shrink-0">
                    <x-heroicon-o-users class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Total</p>
                    <p class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Registrations Table -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4 md:mb-6">
            <div class="flex items-center gap-3">
                <h3 class="text-base md:text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Registration Requests</h3>
                <div class="flex items-center gap-2">
                    <span class="text-xs md:text-sm text-[#706f6c] dark:text-[#A1A09A]">Live Updates:</span>
                    <div id="connectionStatus" class="w-3 h-3 rounded-full bg-red-500" title="Connection status"></div>
                </div>
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

            <div class="overflow-x-auto">
                <table class="w-full">
                <thead>
                    <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
                        <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">User</th>
                        <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Type</th>
                        <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Status</th>
                        <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Date</th>
                        <th class="text-center py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                <tbody id="registrationsTableBody">
                    @forelse($pendingRegistrations as $registration)
                    <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A] hover:bg-gray-50 dark:hover:bg-[#161615]" data-id="{{ $registration->id }}">
                        <td class="py-2 px-3">
                            <div class="flex items-center">
                                @php
                                    $colors = ['#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#6366F1', '#8B5CF6', '#EC4899', '#14B8A6', '#F97316', '#06B6D4'];
                                    $firstLetter = strtoupper(substr($registration->first_name ?? 'U', 0, 1));
                                    $hash = ord($firstLetter);
                                    $avatarColor = $colors[$hash % count($colors)];
                                @endphp
                                <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3 text-white font-semibold text-xs flex-shrink-0" style="background-color: {{ $avatarColor }}">
                                    {{ strtoupper(substr($registration->first_name, 0, 1)) }}
                                </div>
                                    <div>
                                    <div class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">{{ $registration->full_name }}</div>
                                    <div class="text-xs text-[#706f6c] dark:text-[#A1A09A]">{{ $registration->email }}</div>
                                        </div>
                                    </div>
                                </td>
                        <td class="py-2 px-3">
                            @if($registration->user_type === 'reporter' && $registration->reporterRole)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200">
                                    {{ $registration->reporterRole->name }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($registration->user_type === 'student') bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200
                                    @elseif($registration->user_type === 'staff') bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200
                                    @elseif($registration->user_type === 'security') bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200
                                    @elseif($registration->user_type === 'stakeholder') bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200
                                    @else bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200
                                        @endif">
                                        {{ ucfirst($registration->user_type) }}
                                    </span>
                                    @endif
                                </td>
                        <td class="py-2 px-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($registration->status === 'pending') bg-amber-100 dark:bg-amber-900 text-amber-800 dark:text-amber-200
                                @elseif($registration->status === 'approved') bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200
                                @else bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200
                                        @endif">
                                        {{ ucfirst($registration->status) }}
                                    </span>
                                </td>
                        <td class="py-2 px-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">{{ $registration->created_at->format('M d, Y') }}</td>
                        <td class="py-2 px-3">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="viewRegistration({{ $registration->id }})" class="btn-view" title="View">
                                    <x-heroicon-s-eye class="w-4 h-4" />
                                </button>
                                    @if($registration->status === 'pending')
                                    <button onclick="openApproveModal({{ $registration->id }})" class="inline-flex items-center justify-center w-8 h-8 rounded-sm bg-green-600 dark:bg-green-600 text-white hover:bg-green-700 dark:hover:bg-green-700 border border-green-600 dark:border-green-600 hover:border-green-700 dark:hover:border-green-700 transition-all" title="Approve">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                            </button>
                                    <button onclick="openRejectModal({{ $registration->id }})" class="btn-delete" title="Reject">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        </svg>
                                            </button>
                                    @else
                                    <button onclick="deleteRegistration({{ $registration->id }})" class="btn-delete" title="Delete">
                                        <x-heroicon-s-trash class="w-4 h-4" />
                                                </button>
                                            @endif
                                        </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 px-4 text-center text-[#706f6c] dark:text-[#A1A09A]">
                            No registrations found.
                                </td>
                            </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

        <!-- Pagination Controls -->
        <div id="pagination-controls" class="flex items-center justify-between mt-6">
            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                Showing <span id="showing-start">1</span>-<span id="showing-end">10</span> of <span id="total-count">0</span> registrations
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

<!-- View Modal -->
<div id="viewModal" class="modal-backdrop hidden" onclick="if(event.target === this) closeViewModal()">
    <div class="modal-container" style="max-width: 900px; width: 95%;">
        <div class="modal-header">
            <h2 class="modal-title">Registration Details</h2>
        </div>
        <div class="modal-body" id="viewModalContent">
            <!-- Content will be populated by JavaScript -->
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeViewModal()">Close</button>
            </div>
    </div>
</div>

<!-- Approve Confirmation Modal -->
<div id="approveModal" class="modal-backdrop hidden" onclick="if(event.target === this) closeApproveModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title text-green-600 dark:text-green-400 flex items-center gap-2">
                <svg class="modal-icon-success w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Approve Registration
            </h2>
        </div>
        <div class="modal-body">
            <p class="text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Are you sure you want to approve this registration? This will create a user account and vehicle record (if provided).</p>
        </div>
        <div class="modal-footer">
            <button onclick="closeApproveModal()" class="btn btn-secondary">Cancel</button>
            <button onclick="confirmApproveRegistration()" class="btn btn-success">Approve Registration</button>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="modal-backdrop hidden" onclick="if(event.target === this) closeRejectModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title text-red-500 flex items-center gap-2">
                <x-heroicon-o-exclamation-triangle class="modal-icon-error" />
                Reject Registration
            </h2>
        </div>
            <form id="rejectForm" method="POST">
                @csrf
            <div class="modal-body">
                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mb-4">Please provide a reason for rejecting this registration.</p>
                <div class="form-group">
                    <label for="rejection_reason" class="form-label">Reason for Rejection</label>
                    <textarea id="rejection_reason" name="rejection_reason" rows="4" required
                        class="form-input"
                        placeholder="Please provide a reason for rejecting this registration..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeRejectModal()" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-danger">Reject Registration</button>
                </div>
            </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal-backdrop hidden" onclick="if(event.target === this) closeDeleteModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title text-red-500 flex items-center gap-2">
                <x-heroicon-o-exclamation-triangle class="modal-icon-error" />
                Delete Registration
            </h2>
        </div>
        <div class="modal-body">
            <p id="deleteModalMessage" class="text-[#1b1b18] dark:text-[#EDEDEC]"></p>
        </div>
        <div class="modal-footer">
            <button onclick="closeDeleteModal()" class="btn btn-secondary">Cancel</button>
            <button onclick="confirmDeleteRegistration()" class="btn btn-danger">Delete</button>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    @keyframes fadeOut {
        from {
            opacity: 1;
        }
        to {
            opacity: 0;
        }
    }
    
    .animate-fade-out {
        animation: fadeOut 0.3s ease-out forwards;
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }
    
    .animate-fade-in {
        animation: fadeIn 0.3s ease-in;
    }
</style>
@endpush

@push('scripts')
<script>
// Pass pending registrations data to real-time handler
window.pendingRegistrationsData = @json($pendingRegistrations->items());

let deleteRegistrationId = null;
let registrations = @json($pendingRegistrations->items());

// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const statusFilter = document.getElementById('status-filter');
    const userTypeFilter = document.getElementById('user-type-filter');
    const resetButton = document.getElementById('reset-filters');
    const paginationLimit = document.getElementById('pagination-limit');
    let currentPage = 1;
    let itemsPerPage = 20;

    // Set initial pagination limit
    if (paginationLimit) {
        itemsPerPage = parseInt(paginationLimit.value) || 20;
        paginationLimit.value = '20'; // Set default
    }

    // Add event listeners - directly call applyPagination
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            currentPage = 1;
            applyPagination();
        });
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            currentPage = 1;
            applyPagination();
        });
    }
    
    if (userTypeFilter) {
        userTypeFilter.addEventListener('change', function() {
            currentPage = 1;
            applyPagination();
        });
    }

    if (resetButton) {
        resetButton.addEventListener('click', function() {
            if (searchInput) searchInput.value = '';
            if (statusFilter) statusFilter.value = '';
            if (userTypeFilter) userTypeFilter.value = '';
            if (paginationLimit) paginationLimit.value = '20';
            itemsPerPage = 20;
            currentPage = 1;
            applyPagination();
        });
    }

    if (paginationLimit) {
        paginationLimit.addEventListener('change', function() {
            itemsPerPage = parseInt(this.value);
            currentPage = 1;
            applyPagination();
        });
    }

    function applyPagination() {
        const rows = document.querySelectorAll('#registrationsTableBody tr');
        let visibleCount = 0;
        let totalFiltered = 0;
        
        const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
        const statusValue = statusFilter ? statusFilter.value : '';
        const userTypeValue = userTypeFilter ? userTypeFilter.value : '';
        
        // First pass: count total filtered rows
        rows.forEach((row) => {
            // Skip empty state row
            if (row.querySelector('td[colspan]')) {
                return;
            }
            
            const nameCell = row.querySelector('td:nth-child(1)');
            const name = nameCell ? nameCell.textContent.toLowerCase() : '';
            const email = nameCell ? nameCell.querySelector('.text-xs')?.textContent.toLowerCase() || '' : '';
            const statusBadge = row.querySelector('td:nth-child(4) span');
            const statusText = statusBadge ? statusBadge.textContent.trim().toLowerCase() : '';
            const userTypeBadge = row.querySelector('td:nth-child(2) span');
            const userTypeText = userTypeBadge ? userTypeBadge.textContent.trim().toLowerCase() : '';
            
            const registrationId = row.getAttribute('data-id');
            const registration = registrations.find(r => r.id == registrationId);
            
            const matchesSearch = !searchTerm || name.includes(searchTerm) || email.includes(searchTerm);
            const matchesStatus = !statusValue || statusText === statusValue.toLowerCase();
            const matchesUserType = !userTypeValue || (registration && registration.user_type === userTypeValue) || userTypeText.includes(userTypeValue);
            
            if (matchesSearch && matchesStatus && matchesUserType) {
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
            
            const nameCell = row.querySelector('td:nth-child(1)');
            const name = nameCell ? nameCell.textContent.toLowerCase() : '';
            const email = nameCell ? nameCell.querySelector('.text-xs')?.textContent.toLowerCase() || '' : '';
            const statusBadge = row.querySelector('td:nth-child(4) span');
            const statusText = statusBadge ? statusBadge.textContent.trim().toLowerCase() : '';
            const userTypeBadge = row.querySelector('td:nth-child(2) span');
            const userTypeText = userTypeBadge ? userTypeBadge.textContent.trim().toLowerCase() : '';
            
            const registrationId = row.getAttribute('data-id');
            const registration = registrations.find(r => r.id == registrationId);
            
            const matchesSearch = !searchTerm || name.includes(searchTerm) || email.includes(searchTerm);
            const matchesStatus = !statusValue || statusText === statusValue.toLowerCase();
            const matchesUserType = !userTypeValue || (registration && registration.user_type === userTypeValue) || userTypeText.includes(userTypeValue);

            if (!matchesSearch || !matchesStatus || !matchesUserType) {
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
        const showingStart = document.getElementById('showing-start');
        const showingEnd = document.getElementById('showing-end');
        const totalCount = document.getElementById('total-count');
        if (showingStart) showingStart.textContent = start;
        if (showingEnd) showingEnd.textContent = end;
        if (totalCount) totalCount.textContent = totalFiltered;
        
        // Update prev/next buttons
        const prevBtn = document.getElementById('prev-page');
        const nextBtn = document.getElementById('next-page');
        
        if (prevBtn) {
            prevBtn.disabled = currentPage === 1;
            prevBtn.className = currentPage === 1 ? 'btn-pagination btn-paginationDisable' : 'btn-pagination btn-paginationArrow';
        }
        
        if (nextBtn) {
            nextBtn.disabled = currentPage === totalPages || totalPages === 0;
            nextBtn.className = (currentPage === totalPages || totalPages === 0) ? 'btn-pagination btn-paginationDisable' : 'btn-pagination btn-paginationArrow';
        }
        
        // Generate page numbers (show only 3 pages at a time)
        const pageNumbers = document.getElementById('page-numbers');
        if (pageNumbers) {
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
    }

    // Apply initial pagination on page load
    applyPagination();
    
    // Pagination navigation functions (global scope)
    window.changePage = function(direction) {
        const rows = document.querySelectorAll('#registrationsTableBody tr');
        let totalFiltered = 0;
        
        const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
        const statusValue = statusFilter ? statusFilter.value : '';
        const userTypeValue = userTypeFilter ? userTypeFilter.value : '';
        
        rows.forEach((row) => {
            // Skip empty state row
            if (row.querySelector('td[colspan]')) {
                return;
            }
            
            const nameCell = row.querySelector('td:nth-child(1)');
            const name = nameCell ? nameCell.textContent.toLowerCase() : '';
            const email = nameCell ? nameCell.querySelector('.text-xs')?.textContent.toLowerCase() || '' : '';
            const statusBadge = row.querySelector('td:nth-child(4) span');
            const statusText = statusBadge ? statusBadge.textContent.trim().toLowerCase() : '';
            const userTypeBadge = row.querySelector('td:nth-child(2) span');
            const userTypeText = userTypeBadge ? userTypeBadge.textContent.trim().toLowerCase() : '';
            
            const registrationId = row.getAttribute('data-id');
            const registration = registrations.find(r => r.id == registrationId);
            
            const matchesSearch = !searchTerm || name.includes(searchTerm) || email.includes(searchTerm);
            const matchesStatus = !statusValue || statusText === statusValue.toLowerCase();
            const matchesUserType = !userTypeValue || (registration && registration.user_type === userTypeValue) || userTypeText.includes(userTypeValue);

            if (matchesSearch && matchesStatus && matchesUserType) {
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

// View Registration
function viewRegistration(id) {
    fetch(`/admin/pending-registrations/${id}`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        }
    })
    .then(response => response.json())
    .then(data => {
        const modal = document.getElementById('viewModal');
        const content = document.getElementById('viewModalContent');
        
        const registration = data.registration;
        const colors = ['#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#6366F1', '#8B5CF6', '#EC4899', '#14B8A6', '#F97316', '#06B6D4'];
        const firstLetter = registration.first_name.charAt(0).toUpperCase();
        const hash = firstLetter.charCodeAt(0);
        const avatarColor = colors[hash % colors.length];
        
        // Build vehicles HTML
        let vehiclesHtml = '';
        if (registration.pending_vehicles && registration.pending_vehicles.length > 0) {
            vehiclesHtml = '<div class="mt-2 space-y-2">';
            registration.pending_vehicles.forEach((vehicle, index) => {
                vehiclesHtml += `
                    <div class="bg-gray-50 dark:bg-[#1a1a1a] p-3 rounded border border-[#e3e3e0] dark:border-[#3E3E3A]">
                        <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Vehicle ${index + 1}</p>
                        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Type: ${vehicle.vehicle_type ? vehicle.vehicle_type.name : 'N/A'}</p>
                        ${vehicle.plate_no ? `<p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Plate Number: ${vehicle.plate_no}</p>` : ''}
                    </div>
                `;
            });
            vehiclesHtml += '</div>';
        } else {
            vehiclesHtml = '<p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">No vehicles registered</p>';
        }

        content.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <h3 class="text-base font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4 pb-2 border-b border-[#e3e3e0] dark:border-[#3E3E3A]">Personal Information</h3>
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="w-16 h-16 rounded-full flex items-center justify-center text-white font-bold text-xl" style="background-color: ${avatarColor}">
                                ${firstLetter}
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">${registration.first_name} ${registration.last_name}</h4>
                                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">${registration.email}</p>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <label class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">User Type</label>
                                <p class="text-[#1b1b18] dark:text-[#EDEDEC]">${registration.user_type.charAt(0).toUpperCase() + registration.user_type.slice(1)}${registration.reporter_role ? ' - ' + registration.reporter_role.name : ''}</p>
                            </div>
                            ${registration.student_id ? `
                            <div>
                                <label class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Student ID</label>
                                <p class="text-[#1b1b18] dark:text-[#EDEDEC]">${registration.student_id}</p>
                            </div>
                            ` : ''}
                            ${registration.program ? `
                            <div>
                                <label class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Program</label>
                                <p class="text-[#1b1b18] dark:text-[#EDEDEC]">${registration.program.name} (${registration.program.code})</p>
                            </div>
                            ` : ''}
                            ${registration.license_no ? `
                            <div>
                                <label class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">License Number</label>
                                <p class="text-[#1b1b18] dark:text-[#EDEDEC]">${registration.license_no}</p>
                            </div>
                            ` : ''}
                            ${registration.license_image ? `
                            <div>
                                <label class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">License Image</label>
                                <div class="mt-2">
                                    <img src="${registration.license_image}" alt="License" class="max-w-full h-auto rounded border border-[#e3e3e0] dark:border-[#3E3E3A]" style="max-height: 200px;">
                                </div>
                            </div>
                            ` : ''}
                            ${registration.user_type === 'stakeholder' && registration.stakeholder_type ? `
                            <div>
                                <label class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Stakeholder Type</label>
                                <p class="text-[#1b1b18] dark:text-[#EDEDEC]">${registration.stakeholder_type.name}</p>
                            </div>
                            ` : ''}
                            ${registration.user_type === 'stakeholder' && registration.guardian_evidence ? `
                            <div>
                                <label class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Guardian Evidence</label>
                                <div class="mt-2">
                                    ${registration.guardian_evidence.toLowerCase().endsWith('.pdf') ? 
                                        `<div class="flex items-center p-3 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded">
                                            <svg class="w-8 h-8 text-red-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                                            </svg>
                                            <div>
                                                <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Guardian Evidence PDF</p>
                                                <a href="/storage/${registration.guardian_evidence}" target="_blank" class="text-sm text-blue-600 hover:text-blue-800">View Document</a>
                                            </div>
                                        </div>` :
                                        `<img src="/storage/${registration.guardian_evidence}" alt="Guardian Evidence" class="max-w-full h-auto rounded border border-[#e3e3e0] dark:border-[#3E3E3A]" style="max-height: 200px;">`
                                    }
                                </div>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <h3 class="text-base font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4 pb-2 border-b border-[#e3e3e0] dark:border-[#3E3E3A]">Vehicle Information</h3>
                        ${vehiclesHtml}
                    </div>
                    
                    <div>
                        <h3 class="text-base font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4 pb-2 border-b border-[#e3e3e0] dark:border-[#3E3E3A]">Status & Review</h3>
                        <div class="space-y-3">
                            <div>
                                <label class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Status</label>
                                <p><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${registration.status === 'pending' ? 'bg-amber-100 dark:bg-amber-900 text-amber-800 dark:text-amber-200' : registration.status === 'approved' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200'}">
                                    ${registration.status.charAt(0).toUpperCase() + registration.status.slice(1)}
                                </span></p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Submitted</label>
                                <p class="text-[#1b1b18] dark:text-[#EDEDEC]">${new Date(registration.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' })}</p>
                            </div>
                            ${registration.reviewed_at ? `
                            <div>
                                <label class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Reviewed At</label>
                                <p class="text-[#1b1b18] dark:text-[#EDEDEC]">${new Date(registration.reviewed_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' })}</p>
                            </div>
                            ` : ''}
                            ${registration.reviewer ? `
                            <div>
                                <label class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Reviewed By</label>
                                <p class="text-[#1b1b18] dark:text-[#EDEDEC]">${registration.reviewer.first_name} ${registration.reviewer.last_name}</p>
                            </div>
                            ` : ''}
                            ${registration.rejection_reason ? `
                            <div>
                                <label class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Rejection Reason</label>
                                <p class="text-[#1b1b18] dark:text-[#EDEDEC] bg-red-50 dark:bg-red-900/20 p-3 rounded border border-red-200 dark:border-red-800">${registration.rejection_reason}</p>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="text-base font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4 pb-2 border-b border-[#e3e3e0] dark:border-[#3E3E3A]">Technical Details</h3>
                        <div class="space-y-3">
                            ${registration.ip_address ? `
                            <div>
                                <label class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">IP Address</label>
                                <p class="text-[#1b1b18] dark:text-[#EDEDEC] text-xs font-mono">${registration.ip_address}</p>
                            </div>
                            ` : ''}
                            ${registration.user_agent ? `
                            <div>
                                <label class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">User Agent</label>
                                <p class="text-[#1b1b18] dark:text-[#EDEDEC] text-xs">${registration.user_agent}</p>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        modal.classList.remove('hidden');
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to load registration details');
    });
}

function closeViewModal() {
    document.getElementById('viewModal').classList.add('hidden');
}

let approveRegistrationId = null;

// Approve Modal
function openApproveModal(id) {
    approveRegistrationId = id;
    document.getElementById('approveModal').classList.remove('hidden');
}

function closeApproveModal() {
    document.getElementById('approveModal').classList.add('hidden');
    approveRegistrationId = null;
}

function confirmApproveRegistration() {
    if (!approveRegistrationId) return;
    
        const form = document.createElement('form');
        form.method = 'POST';
    form.action = `/admin/pending-registrations/${approveRegistrationId}/approve`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
    csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfToken);
        
        document.body.appendChild(form);
        form.submit();
}

// Reject Modal
function openRejectModal(id) {
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    form.action = `/admin/pending-registrations/${id}/reject`;
    modal.classList.remove('hidden');
}

function closeRejectModal() {
    const modal = document.getElementById('rejectModal');
    modal.classList.add('hidden');
    document.getElementById('rejection_reason').value = '';
}

// Delete Registration
function deleteRegistration(id) {
    deleteRegistrationId = id;
    const modal = document.getElementById('deleteModal');
    document.getElementById('deleteModalMessage').textContent = 'Are you sure you want to delete this registration record? This action cannot be undone.';
    modal.classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    deleteRegistrationId = null;
}

function confirmDeleteRegistration() {
    if (!deleteRegistrationId) return;
    
        const form = document.createElement('form');
        form.method = 'POST';
    form.action = `/admin/pending-registrations/${deleteRegistrationId}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
    csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfToken);
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        form.appendChild(methodField);
        
        document.body.appendChild(form);
        form.submit();
    }
</script>

@vite(['resources/js/admin/pending-registrations-realtime.js'])
@endpush
